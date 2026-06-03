"""Distribucion de pallets 001-200 segun tabla HB 318."""

ZONAS_RANGO = [
    ("PGC COMESTIBLE", "BODEGA", "bodega-pgc", 1, 48),
    ("PERECIBLES", "C.CONGELADOS", "perecibles", 49, 49),
    ("PERECIBLES", "C.FLC", "perecibles", 50, 50),
    ("PERECIBLES", "TRAS. PANADERIA", "perecibles", 51, 51),
    ("NON FOOD", "PLATAFORMA", "non-food", 52, 67),
    ("PGC NO COMESTIBLE", "PLATAFORMA", "pgc-nc", 68, 83),
    ("MERMA", "TRASTIENDA", "merma-devol", 84, 84),
    ("DEVOLUCION", "TRASTIENDA", "merma-devol", 85, 85),
    ("C100 X TRABAJAR Y TRABAJADA", "PLATAFORMA", "plataforma-tabla", 86, 87),
    ("C.CERO X TRABAJAR Y TRABAJADA PGC", "PLATAFORMA", "plataforma-tabla", 88, 89),
    ("C.CERO X TRABAJAR Y TRABAJADA NF", "PLATAFORMA", "plataforma-tabla", 90, 91),
    ("DONACIONES", "PLATAFORMA", "plataforma-tabla", 92, 92),
    ("SERVICIO TECNICO", "PLATAFORMA", "plataforma-tabla", 93, 93),
    ("ADICIONALES", "ADICIONALES", "adicionales", 94, 200),
]

COLORES_ZONA = {
    "bodega-pgc": {"vacio": "#ffffff", "lleno": "#c8c8c8", "borde": "#666"},
    "perecibles": {"vacio": "#ffffff", "lleno": "#ffe08a", "borde": "#b8860b"},
    "non-food": {"vacio": "#ffffff", "lleno": "#ffb366", "borde": "#cc6600"},
    "pgc-nc": {"vacio": "#ffffff", "lleno": "#7eb8da", "borde": "#2a6a8a"},
    "merma-devol": {"vacio": "#ffffff", "lleno": "#7eb8da", "borde": "#2a6a8a"},
    "plataforma-tabla": {"vacio": "#ffffff", "lleno": "#d4e8f5", "borde": "#4a7a9a"},
    "adicionales": {"vacio": "#ffffff", "lleno": "#e0e0e0", "borde": "#888"},
}


def pallet_codigo(numero: int) -> str:
    return f"{numero:03d}"


def generar_filas_pallet_zonas():
    filas = []
    for jerarquia, ubicacion, color_clase, inicio, fin in ZONAS_RANGO:
        for n in range(inicio, fin + 1):
            filas.append(
                (
                    pallet_codigo(n),
                    n,
                    jerarquia,
                    ubicacion,
                    color_clase,
                    inicio,
                    fin,
                )
            )
    return filas
