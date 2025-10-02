import io, csv, base64, zipfile, re
from typing import Dict, Tuple, List

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

# 4 channel utama dari file mentah:
MONTAGE_XY["AF7"]  = _mid("Fp1", "F7")
MONTAGE_XY["AF8"]  = _mid("Fp2", "F8")
MONTAGE_XY["TP9"]  = _mid("T7",  "P7")   # ~T5
MONTAGE_XY["TP10"] = _mid("T8",  "P8")   # ~T6

DEFAULT_LAYOUT = ["AF7", "AF8", "TP9", "TP10"]

# ----------------- Parsing angka lokal (koma/titik) -----------------
_num_space = re.compile(r"[\s\u00A0]")  # spasi & NBSP

def parse_number_str(s: str) -> float:
    s = s.strip()
    s = _num_space.sub("", s)
    if "," in s and "." in s:
        # tanda terakhir = desimal
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

def try_float(s: str):
    try:
        return parse_number_str(s)
    except Exception:
        return None

# ----------------- Gambar kepala -----------------
def _draw_head(ax):
    head = plt.Circle((0, 0), 1.02, fill=False, linewidth=2)
    ax.add_artist(head)
    ax.plot([-0.06, 0, 0.06], [1.02, 1.08, 1.02], linewidth=2)  # hidung
    ax.plot([-1.04, -1.15, -1.04], [0.2, 0, -0.2], linewidth=2) # telinga kiri
    ax.plot([ 1.04,  1.15,  1.04], [0.2, 0, -0.2], linewidth=2) # telinga kanan

# ----------------- IDW interpolation agar merata satu kepala -----------------
def _idw_grid(x, y, z, power=2.0, grid_n=240):
    lin = np.linspace(-1.05, 1.05, grid_n)
    XX, YY = np.meshgrid(lin, lin)
    RR = np.sqrt(XX**2 + YY**2)
    mask = RR <= 1.0

    pts = np.stack([x, y], axis=1)              # (N,2)
    G   = np.stack([XX.ravel(), YY.ravel()], 1) # (M,2)
    d2  = (G[:, None, 0] - pts[None, :, 0])**2 + (G[:, None, 1] - pts[None, :, 1])**2
    d   = np.sqrt(d2) + 1e-12

    w    = 1.0 / (d**power)         # (M,N)
    Wsum = np.sum(w, axis=1)
    Z    = np.sum(w * z[None, :], axis=1) / Wsum
    ZZ   = Z.reshape(grid_n, grid_n)
    ZZ[~mask] = np.nan
    return XX, YY, ZZ

# ----------------- Topoplot (pakai titik sintetis + IDW) -----------------
def make_topoplot(values: Dict[str, float], dpi: int = 150) -> io.BytesIO:
    vals = dict(values)

    # safe mean untuk handle None
    def safe_mean(arr):
        arr = [v for v in arr if v is not None]
        return (float(np.nanmean(arr)) if len(arr) > 0 else None)

    # sintetis (Fz, Pz, Cz)
    a = vals.get("AF7");  b = vals.get("AF8")
    c = vals.get("TP9");  d = vals.get("TP10")
    fz = safe_mean([a, b])
    pz = safe_mean([c, d])
    cz = safe_mean([a, b, c, d])
    if fz is not None: vals["Fz"] = fz
    if pz is not None: vals["Pz"] = pz
    if cz is not None: vals["Cz"] = cz

    # kumpulkan titik valid
    xs, ys, zs, labels = [], [], [], []
    for ch, v in vals.items():
        if (ch in MONTAGE_XY) and (v is not None):
            x, y = MONTAGE_XY[ch]
            xs.append(x); ys.append(y); zs.append(float(v)); labels.append(ch)

    xs = np.array(xs); ys = np.array(ys); zs = np.array(zs)
    if xs.size < 3:
        raise ValueError("Minimal 3 channel valid diperlukan untuk topoplot.")

    # Interpolasi IDW ke grid bulat
    XX, YY, ZZ = _idw_grid(xs, ys, zs, power=2.0, grid_n=240)

    # Plot
    fig = plt.figure(figsize=(4.6, 4.6), dpi=dpi)
    ax  = fig.add_axes([0.02, 0.02, 0.96, 0.96])
    ax.set_aspect("equal"); ax.axis("off")

    ax.imshow(ZZ, extent=[-1.05, 1.05, -1.05, 1.05], origin="lower", interpolation="bilinear")
    ax.contour(XX, YY, ZZ, levels=10, colors="k", linewidths=0.4, alpha=0.35)

    ax.scatter(xs, ys, s=18, c="k", zorder=3)
    for (x, y, lab) in zip(xs, ys, labels):
        ax.text(x, y, lab, fontsize=8, color="white", ha="center", va="center",
                bbox=dict(boxstyle="round,pad=0.2", fc="black", alpha=0.55, lw=0), zorder=4)

    _draw_head(ax)
    fig.tight_layout()

    buf = io.BytesIO()
    fig.savefig(buf, format="png")
    plt.close(fig)
    buf.seek(0)
    return buf

# ----------------- CSV helpers -----------------
def _decode_bytes(b: bytes) -> str:
    try: return b.decode("utf-8")
    except: return b.decode("latin1", "ignore")

def read_rows_any(file_or_bytes) -> List[List[str]]:
    """
    Baca CSV tanpa header. Deteksi delimiter:
    1) csv.Sniffer
    2) fallback ke [',',';','\\t']
    3) fallback regex split
    """
    raw = file_or_bytes.read() if hasattr(file_or_bytes, "read") else file_or_bytes
    text = _decode_bytes(raw)

    # 1) sniffer
    try:
        sample = text[:8192]
        dialect = csv.Sniffer().sniff(sample, delimiters=";, \t")
        sio = io.StringIO(text)
        rows = [r for r in csv.reader(sio, dialect) if any(c.strip() for c in r)]
        if rows:
            return rows
    except Exception:
        pass

    # 2) kandidat delimiter umum
    best_rows, best_score = [], -1
    for d in [",", ";", "\t"]:
        sio = io.StringIO(text)
        rows = [r for r in csv.reader(sio, delimiter=d) if any(c.strip() for c in r)]
        score = sum(len(r) for r in rows)
        if score > best_score:
            best_rows, best_score = rows, score
    if best_rows:
        return best_rows

    # 3) regex fallback
    rows = []
    for line in text.splitlines():
        parts = re.split(r"[;,|\t]| {2,}", line.strip())
        if any(p.strip() for p in parts):
            rows.append(parts)
    return rows

def parse_row_to_4nums_strict(row: List[str]) -> List[float]:
    """
    Ambil TEPAT 4 angka dari kolom 1..4 pada baris.
    Kalau ada yang tak bisa diparse → None (bukan 0).
    """
    vals = []
    for cell in (row + ["", "", "", ""])[:4]:
        v = try_float(cell)
        vals.append(v if v is not None else None)
    return vals  # len 4

def make_values_for_row(rows: List[List[str]], row_1based: int) -> Dict[str, float]:
    """
    Ambil 4 angka dari baris terpilih (1-based) → map ke AF7,AF8,TP9,TP10.
    Minimal 3 angka valid; jika kurang, coba isi dari baris berikutnya.
    """
    idx = max(0, min(len(rows) - 1, row_1based - 1))
    vals = parse_row_to_4nums_strict(rows[idx])

    valid_cnt = sum(1 for v in vals if v is not None)
    if valid_cnt < 3:
        j = idx + 1
        while valid_cnt < 3 and j < len(rows):
            more = parse_row_to_4nums_strict(rows[j])
            for k in range(4):
                if vals[k] is None and more[k] is not None:
                    vals[k] = more[k]
            valid_cnt = sum(1 for v in vals if v is not None)
            j += 1

    if valid_cnt < 3:
        raise ValueError(f"Baris ke-{row_1based} tidak memiliki cukup angka (>=3) untuk topoplot.")

    mapped = {ch: float(v) for ch, v in zip(DEFAULT_LAYOUT, vals) if v is not None}
    return mapped

def make_labeled_csv_all_rows(rows: List[List[str]]) -> str:
    """
    Hasilkan CSV berlabel dengan header AF7,AF8,TP9,TP10
    dan jumlah baris = jumlah baris file mentah.
    Tiap baris: 4 kolom pertama diparse; kolom gagal → kosong (""), tidak jadi 0.
    """
    out = io.StringIO()
    w = csv.writer(out)
    w.writerow(DEFAULT_LAYOUT)  # header

    for r in rows:
        vals = parse_row_to_4nums_strict(r)  # len 4, float/None
        row_out = []
        for v in vals:
            row_out.append("" if v is None else f"{float(v):.6f}")
        w.writerow(row_out)

    return out.getvalue()

# ----------------- ROUTE -----------------
@app.post("/topoplot_csv_label")
def topoplot_csv_label():
    """
    Form-data:
      file   : CSV tanpa header (4 kolom angka per baris)
      return : 'zip' (default) | 'base64'
      dpi    : int (default 150)
      row    : baris 1-based dipakai untuk topoplot (default 1)

    Output:
      - zip   : topoplot.png + labeled.csv (semua baris)
      - base64: JSON { image_base64, labeled_csv, row_used }
    """
    try:
        ret = (request.form.get("return") or request.args.get("return") or "zip").lower()
        dpi = int(request.form.get("dpi") or request.args.get("dpi") or 150)
        row_param = request.form.get("row") or request.args.get("row")
        row_1based = int(row_param) if row_param else 2

        # ambil CSV
        if "file" in request.files:
            rows = read_rows_any(request.files["file"])
        elif request.files:
            rows = read_rows_any(next(iter(request.files.values())))
        else:
            rows = read_rows_any(request.get_data())

        if not rows:
            return jsonify({"error": "CSV kosong."}), 400

        # nilai untuk topoplot
        values = make_values_for_row(rows, row_1based=row_1based)

        # gambar (IDW)
        buf_img = make_topoplot(values, dpi=dpi)

        # CSV berlabel untuk SEMUA baris
        labeled_csv_str = make_labeled_csv_all_rows(rows)
        labeled_csv_bytes = labeled_csv_str.encode("utf-8")

        if ret == "base64":
            img_b64 = base64.b64encode(buf_img.getvalue()).decode("ascii")
            return jsonify({
                "image_base64": img_b64,
                "labeled_csv": labeled_csv_str,
                "row_used": row_1based
            }), 200

        # default: ZIP
        zip_buf = io.BytesIO()
        with zipfile.ZipFile(zip_buf, "w", zipfile.ZIP_DEFLATED) as zf:
            zf.writestr("topoplot.png", buf_img.getvalue())
            zf.writestr("labeled.csv", labeled_csv_bytes)
            zf.writestr("meta.txt", f"row_used={row_1based}\nchannels={','.join(DEFAULT_LAYOUT)}\n")
        zip_buf.seek(0)
        return send_file(zip_buf, mimetype="application/zip",
                         as_attachment=True, download_name="topoplot_and_labeled.zip")
    except Exception as e:
        return jsonify({"error": str(e)}), 400

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=8000, debug=True)
