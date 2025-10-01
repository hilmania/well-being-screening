import io
import csv
from typing import Dict, Tuple

import numpy as np
import matplotlib
matplotlib.use("Agg")  # headless
import matplotlib.pyplot as plt
import matplotlib.tri as mtri
from flask import Flask, request, jsonify, send_file

app = Flask(__name__)

# ---------- Koordinat sederhana sistem 10-20 ----------
MONTAGE_XY: Dict[str, Tuple[float, float]] = {
    "Fp1": (-0.5,  0.95), "Fp2": ( 0.5,  0.95),
    "F7":  (-0.95, 0.55), "F3": (-0.5,  0.6), "Fz": (0.0, 0.65), "F4": (0.5, 0.6), "F8": (0.95, 0.55),
    "T7":  (-1.0,  0.0),  "C3": (-0.6,  0.0), "Cz": (0.0, 0.0),  "C4": (0.6, 0.0),  "T8": (1.0,  0.0),
    "P7":  (-0.95,-0.55), "P3": (-0.5,-0.6), "Pz": (0.0,-0.65), "P4": (0.5,-0.6), "P8": (0.95,-0.55),
    "O1":  (-0.5,-0.95),  "Oz": (0.0,-1.0),  "O2": (0.5,-0.95)
}

# ---------- Util gambar kepala ----------
def _draw_head(ax):
    head = plt.Circle((0, 0), 1.02, fill=False, linewidth=2)
    ax.add_artist(head)
    ax.plot([-0.06, 0, 0.06], [1.02, 1.08, 1.02], linewidth=2)   # hidung
    ax.plot([-1.04, -1.15, -1.04], [0.2, 0, -0.2], linewidth=2)  # telinga kiri
    ax.plot([ 1.04,  1.15,  1.04], [0.2, 0, -0.2], linewidth=2)  # telinga kanan

# ---------- Bikin topoplot dari dict channel->nilai ----------
def make_topoplot(values: Dict[str, float], dpi: int = 150) -> io.BytesIO:
    xs, ys, zs, labels = [], [], [], []
    for ch, val in values.items():
        if ch in MONTAGE_XY:
            try:
                v = float(val)
            except Exception:
                continue
            x, y = MONTAGE_XY[ch]
            xs.append(x); ys.append(y); zs.append(v); labels.append(ch)

    if len(xs) < 3:
        raise ValueError("Minimal 3 channel valid diperlukan untuk topoplot.")

    xs = np.array(xs); ys = np.array(ys); zs = np.array(zs)
    tri = mtri.Triangulation(xs, ys)
    xmid = xs[tri.triangles].mean(axis=1)
    ymid = ys[tri.triangles].mean(axis=1)
    tri.set_mask((xmid**2 + ymid**2) > 1.0)  # mask luar kepala

    fig = plt.figure(figsize=(4, 4), dpi=dpi)
    ax = fig.add_axes([0.02, 0.02, 0.96, 0.96])
    ax.set_aspect('equal'); ax.set_xlim(-1.2, 1.2); ax.set_ylim(-1.2, 1.2); ax.axis('off')

    ax.tricontourf(tri, zs, levels=100)
    ax.tricontour(tri, zs, levels=7, linewidths=0.6, colors='k', alpha=0.35)

    ax.scatter(xs, ys, s=18, c='k')
    for (x, y, lab) in zip(xs, ys, labels):
        ax.text(x, y, lab, fontsize=7, ha='center', va='center', color='white',
                bbox=dict(boxstyle='round,pad=0.2', fc='black', alpha=0.5, lw=0))

    _draw_head(ax)
    fig.tight_layout()

    buf = io.BytesIO()
    fig.savefig(buf, format='png')
    plt.close(fig)
    buf.seek(0)
    return buf

# ---------- Parser CSV: terima bytes ATAU file ----------
def parse_csv_to_values(file_or_bytes) -> Dict[str, float]:
    if hasattr(file_or_bytes, "read"):
        raw = file_or_bytes.read()
    else:
        raw = file_or_bytes  # diasumsikan bytes

    # decode
    try:
        text = raw.decode("utf-8")
    except Exception:
        text = raw.decode("latin1")

    sio = io.StringIO(text)

    # sniff delimiter
    try:
        dialect = csv.Sniffer().sniff(sio.read(2048))
        sio.seek(0)
    except Exception:
        dialect = csv.excel

    rows = list(csv.reader(sio, dialect))
    if not rows:
        raise ValueError("CSV kosong.")

    # Coba format WIDE (header=channel, baris pertama=nilai)
    if len(rows) >= 2 and len(rows[0]) >= 2:
        header = [h.strip() for h in rows[0]]
        header_known = sum(1 for h in header if h in MONTAGE_XY)
        if header_known >= 2:
            first = rows[1] if len(rows) > 1 else []
            values = {}
            for h, v in zip(header, first):
                if h in MONTAGE_XY and v.strip() != "":
                    values[h] = float(v)
            if values:
                return values

    # Coba format LONG/TIDY: channel,value (2 kolom)
    values = {}
    # deteksi header
    start = 0
    if rows and any(k.lower() in [c.strip().lower() for c in rows[0]] for k in ("channel", "chan", "electrode")):
        start = 1
    for r in rows[start:]:
        if len(r) < 2:
            continue
        ch = r[0].strip()
        try:
            val = float(r[1])
        except Exception:
            continue
        values[ch] = val

    if not values:
        raise ValueError("Format CSV tidak dikenali. Pakai format wide atau long (channel,value).")
    return values

# ---------- ROUTES ----------
@app.get("/")
def root():
    return jsonify({"status": "ok", "message": "EEG Topoplot CSV API"}), 200

@app.get("/routes")
def routes():
    # untuk debugging: lihat semua route
    return jsonify({"routes": [str(r) for r in app.url_map.iter_rules()]}), 200

@app.post("/topoplot_csv")
def topoplot_csv():
    """
    TERIMA:
      1) form-data:  file=<CSV>, (opsional) return=base64
      2) raw text/csv: body=CSV, header Content-Type: text/csv
         (opsional) query ?return=base64
    BALAS:
      - image/png (default)
      - JSON { png_base64: ... } jika return=base64
    """
    try:
        # prefer form field; fallback ke query param
        ret = (request.form.get("return") or request.args.get("return") or "image").lower()

        if "file" in request.files:
            values = parse_csv_to_values(request.files["file"])
        else:
            # raw body (text/csv)
            values = parse_csv_to_values(request.get_data())

        buf = make_topoplot(values)

        if ret == "base64":
            import base64
            b64 = base64.b64encode(buf.getvalue()).decode("ascii")
            return jsonify({"png_base64": b64}), 200

        return send_file(buf, mimetype="image/png", as_attachment=False, download_name="topoplot.png")
    except Exception as e:
        return jsonify({"error": str(e)}), 400

# ---------- MAIN ----------
if __name__ == "__main__":
    # Jalankan lokal: python app.py
    app.run(host="0.0.0.0", port=8000, debug=True)
