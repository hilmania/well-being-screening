import io, csv, base64, zipfile, re, math
from typing import Dict, Tuple, List, Optional

import numpy as np
import matplotlib
matplotlib.use("Agg")
import matplotlib.pyplot as plt
from flask import Flask, request, jsonify, send_file
from flask_cors import CORS

app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}})

# ----------------- Koordinat 10–20 (+ titik sintetis) -----------------
MONTAGE_XY: Dict[str, Tuple[float, float]] = {
    "Fp1": (-0.5,  0.95), "Fp2": ( 0.5,  0.95),
    "F7":  (-0.95, 0.55), "F3": (-0.5,  0.60), "Fz": (0.0, 0.65), "F4": (0.5, 0.60), "F8": (0.95, 0.55),
    "T7":  (-1.00, 0.00), "C3": (-0.6,  0.00), "Cz": (0.0, 0.00), "C4": (0.6, 0.00), "T8": (1.00, 0.00),
    "P7":  (-0.95,-0.55), "P3": (-0.5,-0.60), "Pz": (0.0,-0.65), "P4": (0.5,-0.60), "P8": (0.95,-0.55),
    "O1":  (-0.5,-0.95),  "Oz": (0.0,-1.00),  "O2": (0.5,-0.95),
}
def _mid(a, b):
    ax, ay = MONTAGE_XY[a]; bx, by = MONTAGE_XY[b]
    return ((ax + bx) / 2.0, (ay + by) / 2.0)

# 4 channel yang kita gunakan + titik sintetis
MONTAGE_XY["AF7"]  = _mid("Fp1", "F7")
MONTAGE_XY["AF8"]  = _mid("Fp2", "F8")
MONTAGE_XY["TP9"]  = _mid("T7",  "P7")
MONTAGE_XY["TP10"] = _mid("T8",  "P8")

CSV_COLS = ["RAW_TP9", "RAW_AF7", "RAW_AF8", "RAW_TP10"]

# ----------------- Parsing angka lokal (koma/titik) -----------------
_num_space = re.compile(r"[\s\u00A0]")

def parse_number_str(s: str) -> float:
    s = s.strip()
    s = _num_space.sub("", s)
    if "," in s and "." in s:
        if s.rfind(",") > s.rfind("."):
            s = s.replace(".", "")
            s = s.replace(",", ".")
        else:
            s = s.replace(",", "")
    elif "," in s:
        if s.count(",") == 1:
            s = s.replace(",", ".")
        else:
            parts = s.split(",")
            s = "".join(parts[:-1]) + "." + parts[-1]
    return float(s)

def try_float(s: str) -> Optional[float]:
    try: return parse_number_str(s)
    except Exception: return None

def _decode_bytes(b: bytes) -> str:
    try: return b.decode("utf-8")
    except: return b.decode("latin1", "ignore")

# ----------------- CSV dengan header -----------------
def read_csv_with_header(file_or_bytes) -> Tuple[List[str], List[List[str]]]:
    raw = file_or_bytes.read() if hasattr(file_or_bytes, "read") else file_or_bytes
    text = _decode_bytes(raw)

    dialect = None
    for delims in [";, \t", ",;\t", ",;\t "]:
        try:
            sample = text[:8192]
            dialect = csv.Sniffer().sniff(sample, delimiters=delims)
            break
        except Exception:
            continue
    if dialect is None:
        best_rows, best_header, best_score = [], [], -1
        for d in [",", ";", "\t", " "]:
            sio = io.StringIO(text)
            r = list(csv.reader(sio, delimiter=d))
            if not r: continue
            score = sum(len(x) for x in r)
            if score > best_score:
                best_rows, best_score = r, score
        header = [h.strip() for h in best_rows[0]]
        rows = [ [c for c in row] for row in best_rows[1:] ]
        return header, rows

    sio = io.StringIO(text)
    reader = csv.reader(sio, dialect)
    all_rows = list(reader)
    if not all_rows:
        return [], []
    header = [h.strip() for h in all_rows[0]]
    rows = [ [c for c in row] for row in all_rows[1:] ]
    return header, rows

# ----------------- IDW interpolation -----------------
def _idw_grid(x, y, z, power=2.0, grid_n=240):
    lin = np.linspace(-1.05, 1.05, grid_n)
    XX, YY = np.meshgrid(lin, lin)
    RR = np.sqrt(XX**2 + YY**2)
    mask = RR <= 1.0

    pts = np.stack([x, y], axis=1)
    G   = np.stack([XX.ravel(), YY.ravel()], 1)
    d2  = (G[:, None, 0] - pts[None, :, 0])**2 + (G[:, None, 1] - pts[None, :, 1])**2
    d   = np.sqrt(d2) + 1e-12

    w    = 1.0 / (d**power)
    Wsum = np.sum(w, axis=1)
    Z    = np.sum(w * z[None, :], axis=1) / Wsum
    ZZ   = Z.reshape(grid_n, grid_n)
    ZZ[~mask] = np.nan
    return XX, YY, ZZ

def _draw_head(ax):
    head = plt.Circle((0, 0), 1.02, fill=False, linewidth=2)
    ax.add_artist(head)
    ax.plot([-0.06, 0, 0.06], [1.02, 1.08, 1.02], linewidth=2)
    ax.plot([-1.04, -1.15, -1.04], [0.2, 0, -0.2], linewidth=2)
    ax.plot([ 1.04,  1.15,  1.04], [0.2, 0, -0.2], linewidth=2)

def _plot_single_topoplot(ax, values: Dict[str, float], title: str):
    """Plot single topoplot pada axes yang diberikan"""
    vals = dict(values)

    def safe_mean(arr):
        arr = [v for v in arr if v is not None]
        return (float(np.nanmean(arr)) if len(arr) > 0 else None)

    a = vals.get("AF7");  b = vals.get("AF8")
    c = vals.get("TP9");  d = vals.get("TP10")
    fz = safe_mean([a, b])
    pz = safe_mean([c, d])
    cz = safe_mean([a, b, c, d])
    if fz is not None: vals["Fz"] = fz
    if pz is not None: vals["Pz"] = pz
    if cz is not None: vals["Cz"] = cz

    xs, ys, zs, labels = [], [], [], []
    for ch, v in vals.items():
        if (ch in MONTAGE_XY) and (v is not None):
            x, y = MONTAGE_XY[ch]
            xs.append(x); ys.append(y); zs.append(float(v)); labels.append(ch)

    xs = np.array(xs); ys = np.array(ys); zs = np.array(zs)
    if xs.size < 3:
        ax.text(0.5, 0.5, "Insufficient data", ha="center", va="center", transform=ax.transAxes)
        ax.axis("off")
        return

    XX, YY, ZZ = _idw_grid(xs, ys, zs, power=2.0, grid_n=240)

    ax.set_aspect("equal")
    ax.axis("off")

    ax.imshow(ZZ, extent=[-1.05, 1.05, -1.05, 1.05], origin="lower", interpolation="bilinear")
    ax.contour(XX, YY, ZZ, levels=10, colors="k", linewidths=0.4, alpha=0.35)

    ax.scatter(xs, ys, s=18, c="k", zorder=3)
    for (x, y, lab) in zip(xs, ys, labels):
        ax.text(x, y, lab, fontsize=7, color="white", ha="center", va="center",
                bbox=dict(boxstyle="round,pad=0.2", fc="black", alpha=0.55, lw=0), zorder=4)

    _draw_head(ax)
    ax.set_title(title, fontsize=10, pad=8)

def make_combined_topoplot(segments_data: List[Tuple[str, Dict[str, float]]]) -> io.BytesIO:
    """
    Buat 1 gambar besar dengan 17 topoplot dalam grid 3 baris (6, 6, 5)
    segments_data: list of (name, values_dict)
    """
    n_plots = len(segments_data)
    
    # Layout: 3 rows
    # Row 1: 6 plots
    # Row 2: 6 plots  
    # Row 3: 5 plots
    rows = 3
    cols = 6
    
    fig = plt.figure(figsize=(18, 9), dpi=150)
    
    for idx, (name, vals) in enumerate(segments_data):
        # Hitung posisi
        if idx < 6:
            row, col = 0, idx
        elif idx < 12:
            row, col = 1, idx - 6
        else:
            row, col = 2, idx - 12
            
        ax = plt.subplot2grid((rows, cols), (row, col))
        _plot_single_topoplot(ax, vals, name)
    
    plt.tight_layout()
    
    buf = io.BytesIO()
    fig.savefig(buf, format="png", bbox_inches="tight")
    plt.close(fig)
    buf.seek(0)
    return buf

# ----------------- Util segmenting -----------------
def build_segment_plan() -> List[Tuple[str, float]]:
    plan_text = [
        ("Netral", 2), ("Blank", 1),
        ("Netral", 2), ("Blank", 1),
        ("Netral", 2), ("Blank", 1),
        ("Control", 2), ("Blank", 1),
        ("Netral", 2), ("Blank", 1),
        ("Netral", 2), ("Blank", 1),
        ("Netral", 2), ("Blank", 1),
        ("Control", 2), ("Blank", 1),
        ("Netral", 2), ("Blank", 1),
        ("Netral", 2), ("Blank", 1),
        ("Netral", 2), ("Blank", 1),
        ("Control", 2), ("Blank", 1),
        ("Control", 2), ("Blank", 1),
        ("Netral", 2), ("Blank", 1),
        ("Netral", 2), ("Blank", 1),
        ("Netral", 2), ("Blank", 1),
        ("Control", 2), ("Blank", 1),
    ]
    return plan_text

def estimate_fs_from_time(header: List[str], rows: List[List[str]]) -> Optional[float]:
    lower = [h.lower() for h in header]
    candidates = ["time", "timestamp", "t"]
    idx = -1
    for cand in candidates:
        if cand in lower:
            idx = lower.index(cand)
            break
    if idx < 0:
        return None
    ts = []
    for r in rows[:2000]:
        if idx >= len(r): continue
        v = try_float(r[idx])
        if v is not None:
            ts.append(v)
    if len(ts) < 3:
        return None
    ts = np.asarray(ts, dtype=float)
    diffs = np.diff(ts)
    diffs = diffs[(diffs > 0) & np.isfinite(diffs)]
    if len(diffs) == 0:
        return None
    dt = float(np.median(diffs))
    if dt <= 0:
        return None
    fs = 1.0 / dt
    return fs

def series_from_csv(header: List[str], rows: List[List[str]]) -> Dict[str, np.ndarray]:
    hlower = [h.strip().lower() for h in header]
    def find_idx(name: str) -> int:
        nm = name.strip().lower()
        if nm in hlower:
            return hlower.index(nm)
        nm2 = nm.replace("_", " ").replace("-", " ")
        for i, hh in enumerate(hlower):
            if hh.replace("_", " ").replace("-", " ") == nm2:
                return i
        raise KeyError(f"Kolom '{name}' tidak ditemukan.")

    idx_tp9  = find_idx("RAW_TP9")
    idx_af7  = find_idx("RAW_AF7")
    idx_af8  = find_idx("RAW_AF8")
    idx_tp10 = find_idx("RAW_TP10")

    TP9, AF7, AF8, TP10 = [], [], [], []
    for r in rows:
        def get(r, i):
            if i >= len(r): return np.nan
            v = try_float(r[i])
            return v if v is not None else np.nan
        TP9.append(get(r, idx_tp9))
        AF7.append(get(r, idx_af7))
        AF8.append(get(r, idx_af8))
        TP10.append(get(r, idx_tp10))

    return {
        "TP9":  np.asarray(TP9, dtype=float),
        "AF7":  np.asarray(AF7, dtype=float),
        "AF8":  np.asarray(AF8, dtype=float),
        "TP10": np.asarray(TP10, dtype=float),
    }

def segment_indices(total_samples: int, fs: float, plan: List[Tuple[str, float]]) -> List[Tuple[str, int, int, float, float]]:
    segs = []
    t_cursor = 0.0
    s_cursor = 0
    for label, dur in plan:
        n = int(round(dur * fs))
        i0 = s_cursor
        i1 = min(total_samples, s_cursor + n)
        t0 = t_cursor
        t1 = t_cursor + dur
        if i0 >= total_samples:
            break
        segs.append((label, i0, i1, t0, t1))
        s_cursor += n
        t_cursor += dur
        if s_cursor >= total_samples:
            break
    return segs

def segment_means(sig: Dict[str, np.ndarray], i0: int, i1: int) -> Dict[str, float]:
    vals = {}
    for ch in ["AF7", "AF8", "TP9", "TP10"]:
        arr = sig[ch][i0:i1]
        if arr.size == 0:
            vals[ch] = None
            continue
        m = float(np.nanmean(arr)) if np.isfinite(arr).any() else None
        vals[ch] = m
    return vals

# ----------------- ROUTE: 17 Topoplot dalam 1 Gambar -----------------
@app.post("/topoplot_17")
def topoplot_17():
    """
    Form-data:
      file : CSV dengan header, kolom wajib:
             RAW_TP9, RAW_AF7, RAW_AF8, RAW_TP10
             (opsional: Time)
      return : 'image' (default) | 'base64' | 'zip'

    Output:
      - image: 1 file PNG gabungan
      - base64: JSON dengan image base64
      - zip: ZIP berisi image + manifest + values + meta
    """
    try:
        ret = (request.form.get("return") or request.args.get("return") or "image").lower()
        fs_default = 256.0

        # Ambil CSV
        if "file" in request.files:
            header, rows = read_csv_with_header(request.files["file"])
        elif request.files:
            header, rows = read_csv_with_header(next(iter(request.files.values())))
        else:
            header, rows = read_csv_with_header(request.get_data())

        if not header or not rows:
            return jsonify({"error": "CSV kosong atau header tidak ditemukan."}), 400

        # Ambil sinyal
        signals = series_from_csv(header, rows)

        # Estimasi fs jika ada kolom Time
        fs_est = estimate_fs_from_time(header, rows)
        fs = fs_est if (fs_est and np.isfinite(fs_est) and fs_est > 0.5) else fs_default

        total_samples = len(rows)
        plan = build_segment_plan()
        segs = segment_indices(total_samples, fs, plan)

        # Filter hanya Netral & Control, ambil 17 pertama
        wanted = []
        for (label, i0, i1, t0, t1) in segs:
            if label.lower() in ("netral", "control"):
                wanted.append((label, i0, i1, t0, t1))
        if len(wanted) < 17:
            return jsonify({
                "error": f"Data terlalu pendek untuk 17 segmen Netral/Control. Hanya {len(wanted)} segmen ditemukan.",
                "fs_used": fs,
                "total_samples": total_samples
            }), 400

        wanted = wanted[:17]

        # Hitung mean per segmen
        segments_data = []
        manifest_out = io.StringIO()
        mv = csv.writer(manifest_out)
        mv.writerow(["idx", "label", "i0", "i1", "t0_sec", "t1_sec", "duration_sec"])

        values_out = io.StringIO()
        vv = csv.writer(values_out)
        vv.writerow(["idx", "label", "AF7", "AF8", "TP9", "TP10"])

        n_count, c_count = 0, 0
        for idx, (label, i0, i1, t0, t1) in enumerate(wanted, start=1):
            vals = segment_means(signals, i0, i1)

            vv.writerow([
                idx, label,
                "" if vals["AF7"]  is None else f"{vals['AF7']:.6f}",
                "" if vals["AF8"]  is None else f"{vals['AF8']:.6f}",
                "" if vals["TP9"]  is None else f"{vals['TP9']:.6f}",
                "" if vals["TP10"] is None else f"{vals['TP10']:.6f}",
            ])

            if label.lower() == "netral":
                n_count += 1
                name = f"N{n_count:02d}"
            else:
                c_count += 1
                name = f"C{c_count:02d}"

            segments_data.append((name, vals))
            mv.writerow([idx, label, i0, i1, f"{t0:.3f}", f"{t1:.3f}", f"{(t1-t0):.3f}"])

        # Buat 1 gambar gabungan
        combined_img = make_combined_topoplot(segments_data)

        meta_txt = (
            f"fs_used={fs:.6f} Hz\n"
            f"total_samples={total_samples}\n"
            f"segments_generated=17 (Netral={n_count}, Control={c_count})\n"
            f"channels_source=RAW_TP9,RAW_AF7,RAW_AF8,RAW_TP10 -> (TP9,AF7,AF8,TP10)\n"
            f"channel_map_topoplot=AF7,AF8,TP9,TP10 (+ Fz,Pz,Cz sintetis)\n"
        )

        values_csv = values_out.getvalue()
        manifest_csv = manifest_out.getvalue()

        if ret == "base64":
            b64 = base64.b64encode(combined_img.getvalue()).decode("ascii")
            return jsonify({
                "image": b64,
                "manifest_csv": manifest_csv,
                "values_csv": values_csv,
                "meta": meta_txt
            }), 200

        if ret == "zip":
            zip_buf = io.BytesIO()
            with zipfile.ZipFile(zip_buf, "w", zipfile.ZIP_DEFLATED) as zf:
                zf.writestr("topoplot_17_combined.png", combined_img.getvalue())
                zf.writestr("manifest.csv", manifest_csv.encode("utf-8"))
                zf.writestr("values.csv", values_csv.encode("utf-8"))
                zf.writestr("meta.txt", meta_txt.encode("utf-8"))
            zip_buf.seek(0)
            return send_file(zip_buf, mimetype="application/zip",
                             as_attachment=True, download_name="topoplot_17.zip")

        # default: return image
        return send_file(combined_img, mimetype="image/png",
                         as_attachment=True, download_name="topoplot_17_combined.png")

    except KeyError as ke:
        return jsonify({"error": f"Kolom CSV wajib tidak ditemukan: {str(ke)}"}), 400
    except Exception as e:
        return jsonify({"error": str(e)}), 400

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=8000, debug=True)
