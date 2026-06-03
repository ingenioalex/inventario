import os
import sqlite3
from contextlib import contextmanager
from datetime import datetime
from pathlib import Path

from distribucion_datos import generar_filas_pallet_zonas, pallet_codigo

DB_PATH = Path(os.environ.get("DATABASE_PATH", Path(__file__).parent / "inventario.db"))
DATABASE_URL = os.environ.get("DATABASE_URL", "").strip()
USE_POSTGRES = DATABASE_URL.startswith(("postgres://", "postgresql://"))


def _pg_url():
    if DATABASE_URL.startswith("postgres://"):
        return DATABASE_URL.replace("postgres://", "postgresql://", 1)
    return DATABASE_URL


@contextmanager
def get_db():
    if USE_POSTGRES:
        import psycopg2
        import psycopg2.extras

        conn = psycopg2.connect(_pg_url())
        conn.cursor_factory = psycopg2.extras.RealDictCursor
        try:
            yield conn
            conn.commit()
        finally:
            conn.close()
    else:
        conn = sqlite3.connect(DB_PATH)
        conn.row_factory = sqlite3.Row
        try:
            yield conn
            conn.commit()
        finally:
            conn.close()


def _row_val(row, key, default=0):
    if row is None:
        return default
    return row[key] if isinstance(row, dict) else row[key]


def _last_id(cur):
    if USE_POSTGRES:
        row = cur.fetchone()
        return row["id"] if row else None
    return cur.lastrowid


def _schema_sql():
    if USE_POSTGRES:
        return """
            CREATE TABLE IF NOT EXISTS sesiones (
                id SERIAL PRIMARY KEY,
                usuario TEXT NOT NULL,
                area TEXT NOT NULL,
                fecha_inicio TEXT NOT NULL,
                activa INTEGER DEFAULT 1
            );
            CREATE TABLE IF NOT EXISTS registros (
                id SERIAL PRIMARY KEY,
                sesion_id INTEGER NOT NULL,
                usuario TEXT NOT NULL,
                fecha TEXT NOT NULL,
                area TEXT NOT NULL,
                pallet TEXT NOT NULL,
                ean TEXT NOT NULL,
                cantidad INTEGER NOT NULL,
                numero_caja TEXT NOT NULL,
                FOREIGN KEY (sesion_id) REFERENCES sesiones(id)
            );
            CREATE TABLE IF NOT EXISTS pallet_zonas (
                pallet TEXT PRIMARY KEY,
                numero INTEGER NOT NULL UNIQUE,
                jerarquia TEXT NOT NULL,
                ubicacion TEXT NOT NULL,
                color_clase TEXT NOT NULL,
                rango_inicio INTEGER NOT NULL,
                rango_fin INTEGER NOT NULL
            );
            CREATE TABLE IF NOT EXISTS zonas_catalogo (
                id SERIAL PRIMARY KEY,
                jerarquia TEXT NOT NULL,
                ubicacion TEXT NOT NULL,
                cantidad INTEGER NOT NULL,
                rango_inicio INTEGER NOT NULL,
                rango_fin INTEGER NOT NULL,
                color_clase TEXT NOT NULL
            );
        """
    return """
        CREATE TABLE IF NOT EXISTS sesiones (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario TEXT NOT NULL,
            area TEXT NOT NULL,
            fecha_inicio TEXT NOT NULL,
            activa INTEGER DEFAULT 1
        );
        CREATE TABLE IF NOT EXISTS registros (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sesion_id INTEGER NOT NULL,
            usuario TEXT NOT NULL,
            fecha TEXT NOT NULL,
            area TEXT NOT NULL,
            pallet TEXT NOT NULL,
            ean TEXT NOT NULL,
            cantidad INTEGER NOT NULL,
            numero_caja TEXT NOT NULL,
            FOREIGN KEY (sesion_id) REFERENCES sesiones(id)
        );
        CREATE TABLE IF NOT EXISTS pallet_zonas (
            pallet TEXT PRIMARY KEY,
            numero INTEGER NOT NULL UNIQUE,
            jerarquia TEXT NOT NULL,
            ubicacion TEXT NOT NULL,
            color_clase TEXT NOT NULL,
            rango_inicio INTEGER NOT NULL,
            rango_fin INTEGER NOT NULL
        );
        CREATE TABLE IF NOT EXISTS zonas_catalogo (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            jerarquia TEXT NOT NULL,
            ubicacion TEXT NOT NULL,
            cantidad INTEGER NOT NULL,
            rango_inicio INTEGER NOT NULL,
            rango_fin INTEGER NOT NULL,
            color_clase TEXT NOT NULL
        );
    """


def init_db():
    with get_db() as conn:
        if USE_POSTGRES:
            cur = conn.cursor()
            for stmt in _schema_sql().strip().split(";"):
                s = stmt.strip()
                if s:
                    cur.execute(s)
        else:
            conn.executescript(_schema_sql())
        seed_distribucion(conn)


def seed_distribucion(conn):
    cur = conn.cursor()
    cur.execute("SELECT COUNT(*) AS n FROM pallet_zonas")
    if _row_val(cur.fetchone(), "n") > 0:
        return
    from distribucion_datos import ZONAS_RANGO

    ph = "%s" if USE_POSTGRES else "?"
    for jerarquia, ubicacion, color_clase, inicio, fin in ZONAS_RANGO:
        cur.execute(
            f"""INSERT INTO zonas_catalogo
               (jerarquia, ubicacion, cantidad, rango_inicio, rango_fin, color_clase)
               VALUES ({ph}, {ph}, {ph}, {ph}, {ph}, {ph})""",
            (jerarquia, ubicacion, fin - inicio + 1, inicio, fin, color_clase),
        )
    for pallet, numero, jerarquia, ubicacion, color_clase, ri, rf in generar_filas_pallet_zonas():
        cur.execute(
            f"""INSERT INTO pallet_zonas
               (pallet, numero, jerarquia, ubicacion, color_clase, rango_inicio, rango_fin)
               VALUES ({ph}, {ph}, {ph}, {ph}, {ph}, {ph}, {ph})""",
            (pallet, numero, jerarquia, ubicacion, color_clase, ri, rf),
        )


def crear_sesion(usuario: str, area: str) -> int:
    ph = "%s" if USE_POSTGRES else "?"
    fecha = datetime.now().isoformat(timespec="seconds")
    with get_db() as conn:
        cur = conn.cursor()
        if USE_POSTGRES:
            cur.execute(
                f"INSERT INTO sesiones (usuario, area, fecha_inicio) VALUES ({ph}, {ph}, {ph}) RETURNING id",
                (usuario.strip(), area.strip(), fecha),
            )
        else:
            cur.execute(
                f"INSERT INTO sesiones (usuario, area, fecha_inicio) VALUES ({ph}, {ph}, {ph})",
                (usuario.strip(), area.strip(), fecha),
            )
        return _last_id(cur)


def obtener_sesion(sesion_id: int):
    ph = "%s" if USE_POSTGRES else "?"
    with get_db() as conn:
        cur = conn.cursor()
        cur.execute(
            f"SELECT * FROM sesiones WHERE id = {ph} AND activa = 1", (sesion_id,)
        )
        return cur.fetchone()


def agregar_registro(
    sesion_id: int,
    usuario: str,
    area: str,
    pallet: str,
    ean: str,
    cantidad: int,
    numero_caja: str,
) -> int:
    ph = "%s" if USE_POSTGRES else "?"
    fecha = datetime.now().isoformat(timespec="seconds")
    vals = (
        sesion_id,
        usuario,
        fecha,
        area,
        pallet,
        ean.strip(),
        cantidad,
        numero_caja.strip(),
    )
    with get_db() as conn:
        cur = conn.cursor()
        if USE_POSTGRES:
            cur.execute(
                f"""INSERT INTO registros
                   (sesion_id, usuario, fecha, area, pallet, ean, cantidad, numero_caja)
                   VALUES ({ph}, {ph}, {ph}, {ph}, {ph}, {ph}, {ph}, {ph}) RETURNING id""",
                vals,
            )
        else:
            cur.execute(
                f"""INSERT INTO registros
                   (sesion_id, usuario, fecha, area, pallet, ean, cantidad, numero_caja)
                   VALUES ({ph}, {ph}, {ph}, {ph}, {ph}, {ph}, {ph}, {ph})""",
                vals,
            )
        return _last_id(cur)


def registros_por_pallet(sesion_id: int, pallet: str):
    ph = "%s" if USE_POSTGRES else "?"
    with get_db() as conn:
        cur = conn.cursor()
        cur.execute(
            f"""SELECT * FROM registros
               WHERE sesion_id = {ph} AND pallet = {ph}
               ORDER BY id""",
            (sesion_id, pallet),
        )
        return cur.fetchall()


def contar_registros_pallet(sesion_id: int, pallet: str) -> int:
    ph = "%s" if USE_POSTGRES else "?"
    with get_db() as conn:
        cur = conn.cursor()
        cur.execute(
            f"SELECT COUNT(*) AS n FROM registros WHERE sesion_id = {ph} AND pallet = {ph}",
            (sesion_id, pallet),
        )
        return _row_val(cur.fetchone(), "n")


def pallets_con_datos_sesion(sesion_id: int) -> dict[str, int]:
    ph = "%s" if USE_POSTGRES else "?"
    with get_db() as conn:
        cur = conn.cursor()
        cur.execute(
            f"""SELECT pallet, COUNT(*) AS n FROM registros
               WHERE sesion_id = {ph} GROUP BY pallet""",
            (sesion_id,),
        )
        rows = cur.fetchall()
    return {r["pallet"]: r["n"] for r in rows}


def obtener_zona_pallet(pallet: str):
    ph = "%s" if USE_POSTGRES else "?"
    with get_db() as conn:
        cur = conn.cursor()
        cur.execute(f"SELECT * FROM pallet_zonas WHERE pallet = {ph}", (pallet,))
        return cur.fetchone()


def zona_por_numero(numero: int):
    return obtener_zona_pallet(pallet_codigo(numero))


def todas_zonas_pallets() -> dict[str, dict]:
    with get_db() as conn:
        cur = conn.cursor()
        cur.execute("SELECT * FROM pallet_zonas ORDER BY numero")
        rows = cur.fetchall()
    return {r["pallet"]: dict(r) for r in rows}


def catalogo_zonas():
    with get_db() as conn:
        cur = conn.cursor()
        cur.execute("SELECT * FROM zonas_catalogo ORDER BY rango_inicio")
        return cur.fetchall()


def obtener_registro(registro_id: int, sesion_id: int):
    ph = "%s" if USE_POSTGRES else "?"
    with get_db() as conn:
        cur = conn.cursor()
        cur.execute(
            f"SELECT * FROM registros WHERE id = {ph} AND sesion_id = {ph}",
            (registro_id, sesion_id),
        )
        return cur.fetchone()


def actualizar_registro(
    registro_id: int,
    sesion_id: int,
    ean: str,
    cantidad: int,
    numero_caja: str,
):
    ph = "%s" if USE_POSTGRES else "?"
    with get_db() as conn:
        cur = conn.cursor()
        cur.execute(
            f"""UPDATE registros SET ean = {ph}, cantidad = {ph}, numero_caja = {ph},
               fecha = {ph} WHERE id = {ph} AND sesion_id = {ph}""",
            (
                ean.strip(),
                cantidad,
                numero_caja.strip(),
                datetime.now().isoformat(timespec="seconds"),
                registro_id,
                sesion_id,
            ),
        )


def eliminar_registro(registro_id: int, sesion_id: int):
    ph = "%s" if USE_POSTGRES else "?"
    with get_db() as conn:
        cur = conn.cursor()
        cur.execute(
            f"DELETE FROM registros WHERE id = {ph} AND sesion_id = {ph}",
            (registro_id, sesion_id),
        )


def cerrar_sesion(sesion_id: int):
    ph = "%s" if USE_POSTGRES else "?"
    with get_db() as conn:
        cur = conn.cursor()
        cur.execute(f"UPDATE sesiones SET activa = 0 WHERE id = {ph}", (sesion_id,))


def motor_bd():
    return "postgresql" if USE_POSTGRES else "sqlite"
