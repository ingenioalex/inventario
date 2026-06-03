import os
from datetime import datetime
from pathlib import Path

import entorno as env

from flask import (
    Flask,
    flash,
    redirect,
    render_template,
    request,
    send_file,
    session,
    url_for,
)

import database as db
import planograma as pl

app = Flask(__name__)
app.secret_key = os.environ.get("SECRET_KEY", "solo-desarrollo-local-cambiar")
if not os.environ.get("SECRET_KEY") and os.environ.get("FLASK_DEBUG", "1") != "1":
    import warnings
    warnings.warn("Defina SECRET_KEY en el hosting (Render → Environment).")
EXPORTS_DIR = Path(__file__).parent / "exportaciones"


def normalizar_pallet(valor: str) -> str | None:
    limpio = valor.strip()
    if not limpio.isdigit():
        return None
    n = int(limpio)
    if n < 1 or n > 200:
        return None
    return f"{n:03d}"


def pallets_disponibles():
    return [f"{i:03d}" for i in range(1, 201)]


def requiere_sesion():
    if not session.get("sesion_id"):
        return redirect(url_for("index"))
    return None


def contexto_mapa():
    zonas_raw = db.todas_zonas_pallets()
    zonas = {k: dict(v) for k, v in zonas_raw.items()}
    estados = db.pallets_con_datos_sesion(session["sesion_id"])
    return {
        "zonas": zonas,
        "estados": estados,
        "pallet_activo": session.get("pallet_actual"),
        "usuario": session.get("usuario"),
        "area": session.get("area"),
        "ultimo_txt": session.get("ultimo_txt"),
        "bodega_izq": pl.grilla_bodega_izq(),
        "bodega_centro": pl.grilla_bodega_centro(),
        "bodega_der": pl.grilla_bodega_der(),
        "plataforma_nf": pl.grilla_plataforma_non_food(),
        "plataforma_pgc": pl.grilla_plataforma_pgc_j02(),
        "tabla_inferior": pl.TABLA_INFERIOR,
        "adicionales": pl.grilla_adicionales(),
    }


def armar_archivo_txt(registros, pallet: str, usuario: str, area: str) -> bytes:
    lineas = [
        "INVENTARIO - PALLET",
        f"Pallet: {pallet}",
        f"Usuario: {usuario}",
        f"Area: {area}",
        f"Fecha exportacion: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}",
        f"Total lineas: {len(registros)}",
        "-" * 72,
        f"{'usuario':<20} {'fecha':<20} {'area':<12} {'pallet':<8} "
        f"{'ean':<16} {'cant':<6} {'caja':<10}",
        "-" * 72,
    ]
    for r in registros:
        lineas.append(
            f"{r['usuario']:<20} {r['fecha']:<20} {r['area']:<12} {r['pallet']:<8} "
            f"{r['ean']:<16} {r['cantidad']:<6} {r['numero_caja']:<10}"
        )
    lineas.append("-" * 72)
    lineas.append("FIN DEL ARCHIVO")
    return ("\n".join(lineas) + "\n").encode("utf-8")


def guardar_txt_en_disco(contenido: bytes, pallet: str, sesion_id: int) -> Path:
    EXPORTS_DIR.mkdir(exist_ok=True)
    stamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    nombre = f"pallet_{pallet}_sesion{sesion_id}_{stamp}.txt"
    ruta = EXPORTS_DIR / nombre
    ruta.write_bytes(contenido)
    return ruta


@app.before_request
def init():
    db.init_db()


@app.route("/")
def index():
    if session.get("sesion_id"):
        return redirect(url_for("mapa"))
    return render_template("inicio.html")


@app.route("/sesion", methods=["POST"])
def iniciar_sesion():
    usuario = request.form.get("usuario", "").strip()
    area = request.form.get("area", "").strip()
    if not usuario or not area:
        flash("Indique usuario y area.", "error")
        return redirect(url_for("index"))
    sesion_id = db.crear_sesion(usuario, area)
    session["sesion_id"] = sesion_id
    session["usuario"] = usuario
    session["area"] = area
    session["pallet_actual"] = None
    flash(f"Sesion iniciada: {usuario} ({area})", "ok")
    return redirect(url_for("mapa"))


@app.route("/mapa")
def mapa():
    redir = requiere_sesion()
    if redir:
        return redir
    return render_template("mapa.html", **contexto_mapa())


@app.route("/pallet/<pallet>")
def accion_pallet(pallet):
    redir = requiere_sesion()
    if redir:
        return redir
    codigo = normalizar_pallet(pallet)
    if not codigo:
        flash("Pallet invalido.", "error")
        return redirect(url_for("mapa"))
    session["pallet_actual"] = codigo
    flash(f"Pallet {int(codigo)} abierto.", "ok")
    return redirect(url_for("inventario"))


@app.route("/editar/<pallet>")
def editar_pallet(pallet):
    redir = requiere_sesion()
    if redir:
        return redir
    codigo = normalizar_pallet(pallet)
    if not codigo:
        flash("Pallet invalido.", "error")
        return redirect(url_for("mapa"))
    session["pallet_actual"] = codigo
    return redirect(url_for("inventario"))


@app.route("/inventario")
def inventario():
    redir = requiere_sesion()
    if redir:
        return redir
    pallet = session.get("pallet_actual")
    total = 0
    zona_pallet = None
    registros = []
    pallet_display = None
    if pallet:
        total = db.contar_registros_pallet(session["sesion_id"], pallet)
        zona_pallet = db.obtener_zona_pallet(pallet)
        registros = db.registros_por_pallet(session["sesion_id"], pallet)
        pallet_display = str(int(pallet))
    return render_template(
        "inventario.html",
        pallets=pallets_disponibles(),
        pallet_actual=pallet,
        pallet_display=pallet_display,
        usuario=session.get("usuario"),
        area=session.get("area"),
        total_lineas=total,
        ultimo_txt=session.get("ultimo_txt"),
        zona_pallet=zona_pallet,
        registros=registros,
    )


@app.route("/pallet", methods=["POST"])
def seleccionar_pallet():
    redir = requiere_sesion()
    if redir:
        return redir
    pallet = normalizar_pallet(request.form.get("pallet", ""))
    if not pallet:
        flash("Pallet invalido. Use un numero del 001 al 200.", "error")
        return redirect(url_for("inventario"))
    session["pallet_actual"] = pallet
    flash(f"Pallet {int(pallet)} listo.", "ok")
    return redirect(url_for("inventario"))


@app.route("/registro", methods=["POST"])
def agregar_registro():
    redir = requiere_sesion()
    if redir:
        return redir
    pallet = session.get("pallet_actual")
    if not pallet:
        flash("Seleccione un pallet antes de registrar.", "error")
        return redirect(url_for("mapa"))

    ean = request.form.get("ean", "").strip()
    cantidad_raw = request.form.get("cantidad", "").strip()
    numero_caja = request.form.get("numero_caja", "").strip()
    if not ean:
        flash("Ingrese el codigo EAN o de barras.", "error")
        return redirect(url_for("inventario"))
    if not cantidad_raw.isdigit() or int(cantidad_raw) < 1:
        flash("La cantidad debe ser un numero mayor a 0.", "error")
        return redirect(url_for("inventario"))
    if not numero_caja:
        flash("Ingrese el numero de caja.", "error")
        return redirect(url_for("inventario"))

    db.agregar_registro(
        session["sesion_id"],
        session["usuario"],
        session["area"],
        pallet,
        ean,
        int(cantidad_raw),
        numero_caja,
    )
    total = db.contar_registros_pallet(session["sesion_id"], pallet)
    flash(f"Guardado. Total lineas: {total}", "ok")
    return redirect(url_for("inventario"))


@app.route("/registro/<int:registro_id>/actualizar", methods=["POST"])
def actualizar_registro(registro_id):
    redir = requiere_sesion()
    if redir:
        return redir
    reg = db.obtener_registro(registro_id, session["sesion_id"])
    if not reg:
        flash("Registro no encontrado.", "error")
        return redirect(url_for("mapa"))
    ean = request.form.get("ean", "").strip()
    cantidad_raw = request.form.get("cantidad", "").strip()
    numero_caja = request.form.get("numero_caja", "").strip()
    if not ean or not numero_caja or not cantidad_raw.isdigit() or int(cantidad_raw) < 1:
        flash("Datos invalidos.", "error")
        return redirect(url_for("inventario"))
    db.actualizar_registro(
        registro_id, session["sesion_id"], ean, int(cantidad_raw), numero_caja
    )
    session["pallet_actual"] = reg["pallet"]
    flash("Linea actualizada.", "ok")
    return redirect(url_for("inventario"))


@app.route("/registro/<int:registro_id>/eliminar", methods=["POST"])
def eliminar_registro(registro_id):
    redir = requiere_sesion()
    if redir:
        return redir
    reg = db.obtener_registro(registro_id, session["sesion_id"])
    if not reg:
        flash("Registro no encontrado.", "error")
        return redirect(url_for("mapa"))
    pallet = reg["pallet"]
    db.eliminar_registro(registro_id, session["sesion_id"])
    session["pallet_actual"] = pallet
    flash("Linea eliminada.", "ok")
    return redirect(url_for("inventario"))


@app.route("/cerrar-pallet", methods=["POST"])
def cerrar_pallet():
    redir = requiere_sesion()
    if redir:
        return redir
    pallet = session.get("pallet_actual")
    if not pallet:
        flash("No hay pallet activo.", "error")
        return redirect(url_for("mapa"))
    session["pallet_actual"] = None
    flash(f"Pallet {int(pallet)} cerrado. Puede abrir otro en el mapa.", "ok")
    return redirect(url_for("mapa"))


@app.route("/generar-txt", methods=["POST"], endpoint="generar_txt")
def generar_txt_pallet():
    redir = requiere_sesion()
    if redir:
        return redir
    pallet = session.get("pallet_actual") or request.form.get("pallet", "")
    codigo = normalizar_pallet(pallet) if pallet else None
    if not codigo:
        flash("Seleccione un pallet con registros.", "error")
        return redirect(url_for("mapa"))

    registros = db.registros_por_pallet(session["sesion_id"], codigo)
    if not registros:
        flash(f"Pallet {int(codigo)} sin lineas para exportar.", "error")
        return redirect(url_for("inventario"))

    contenido = armar_archivo_txt(
        registros, codigo, session["usuario"], session["area"]
    )
    ruta = guardar_txt_en_disco(contenido, codigo, session["sesion_id"])
    session["ultimo_txt"] = ruta.name
    flash(
        f"TXT listo: {len(registros)} lineas del pallet {int(codigo)}.",
        "ok",
    )
    return redirect(url_for("inventario"))


@app.route("/descargar-txt")
def descargar_txt():
    redir = requiere_sesion()
    if redir:
        return redir
    nombre = session.get("ultimo_txt")
    if not nombre:
        flash("No hay archivo para descargar.", "error")
        return redirect(url_for("mapa"))
    ruta = EXPORTS_DIR / nombre
    if not ruta.is_file():
        flash("Archivo no encontrado.", "error")
        return redirect(url_for("mapa"))
    return send_file(
        ruta,
        as_attachment=True,
        download_name=nombre,
        mimetype="text/plain; charset=utf-8",
    )


@app.route("/cambiar-pallet", methods=["POST"])
def cambiar_pallet():
    redir = requiere_sesion()
    if redir:
        return redir
    session["pallet_actual"] = None
    flash("Seleccione otro pallet en el planograma.", "ok")
    return redirect(url_for("mapa"))


@app.route("/salir", methods=["POST"])
def salir():
    if session.get("sesion_id"):
        db.cerrar_sesion(session["sesion_id"])
    session.clear()
    flash("Sesion finalizada.", "ok")
    return redirect(url_for("index"))


@app.context_processor
def variables_globales():
    url_render = os.environ.get("RENDER_EXTERNAL_URL", "").rstrip("/")
    return {
        "es_https": request.is_secure,
        "es_render": env.es_render(),
        "es_wifi_local": not env.es_render(),
        "url_wifi_celular": env.url_wifi_celular(),
        "url_render": url_render or None,
    }


if __name__ == "__main__":
    db.init_db()
    EXPORTS_DIR.mkdir(exist_ok=True)
    port = int(os.environ.get("PORT", 5000))
    debug = os.environ.get("FLASK_DEBUG", "1") == "1"
    ssl = env.ssl_context_local()
    ip_wifi = env.ip_wifi_local()
    url_wifi = env.url_wifi_celular()

    print("\n  === INVENTARIO HB 318 ===")
    if env.es_render():
        print("  Modo: Render (nube) — use gunicorn en produccion")
    elif ssl:
        print("  Modo: Wi-Fi local (HTTPS)")
        print(f"  PC:       https://127.0.0.1:{port}")
        if url_wifi:
            print(f"  Celular:  {url_wifi}")
        print("  Misma red Wi-Fi. Acepte certificado si el navegador avisa.")
    else:
        print("  Modo: local sin HTTPS (camara en celular NO funcionara)")
        print(f"  PC: http://127.0.0.1:{port}")
        if url_wifi:
            print(f"  Celular con camara: {url_wifi}")
        print("  Ejecute: ./iniciar_wifi.sh\n")
    print("  Modo nube: despliegue en Render (ver DEPLOY_NUBE.md)\n")

    app.run(host="0.0.0.0", port=port, debug=debug, ssl_context=ssl)
