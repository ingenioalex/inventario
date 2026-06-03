"""Layout del planograma HB 318 (numeros visibles como en imagen)."""


def etiqueta(numero: int) -> str:
    return str(numero)


def grilla_bodega_izq():
    return [[1, 5], [2, 6], [3, 7], [4, 8]]


def grilla_bodega_centro():
    columnas = []
    for col in range(8):
        base = 9 + col * 4
        columnas.append([base, base + 1, base + 2, base + 3])
    return columnas


def grilla_bodega_der():
    return [[41, 45], [42, 46], [43, 47], [44, 48]]


def grilla_plataforma_non_food():
    filas = []
    for r in range(8):
        filas.append([52 + r, 60 + r])
    return filas


def grilla_plataforma_pgc_j02():
    filas = []
    for r in range(8):
        filas.append([68 + r, 76 + r])
    return filas


def grilla_adicionales():
    nums = list(range(94, 201))
    filas = []
    cols = 10
    for i in range(0, len(nums), cols):
        filas.append(nums[i : i + cols])
    return filas


TABLA_INFERIOR = [
    {"titulo": "C100 X TRABAJAR Y TRABAJADA", "nums": [86, 87]},
    {"titulo": "C.CERO X TRABAJAR Y TRABAJADA PGC", "nums": [88, 89]},
    {"titulo": "C.CERO X TRABAJAR Y TRABAJADA NF", "nums": [90, 91]},
    {"titulo": "DONACIONES", "nums": [92]},
    {"titulo": "SERVICIO TECNICO", "nums": [93]},
]
