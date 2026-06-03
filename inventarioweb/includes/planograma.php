<?php

function grilla_bodega_izq(): array
{
    return [[1, 5], [2, 6], [3, 7], [4, 8]];
}

function grilla_bodega_centro(): array
{
    $columnas = [];
    for ($col = 0; $col < 8; $col++) {
        $base = 9 + $col * 4;
        $columnas[] = [$base, $base + 1, $base + 2, $base + 3];
    }
    return $columnas;
}

function grilla_bodega_der(): array
{
    return [[41, 45], [42, 46], [43, 47], [44, 48]];
}

function grilla_plataforma_non_food(): array
{
    $filas = [];
    for ($r = 0; $r < 8; $r++) {
        $filas[] = [52 + $r, 60 + $r];
    }
    return $filas;
}

function grilla_plataforma_pgc_j02(): array
{
    $filas = [];
    for ($r = 0; $r < 8; $r++) {
        $filas[] = [68 + $r, 76 + $r];
    }
    return $filas;
}

function grilla_adicionales(): array
{
    $nums = range(94, 200);
    $filas = [];
    for ($i = 0; $i < count($nums); $i += 10) {
        $filas[] = array_slice($nums, $i, 10);
    }
    return $filas;
}

const TABLA_INFERIOR = [
    ['titulo' => 'C100 X TRABAJAR Y TRABAJADA', 'nums' => [86, 87]],
    ['titulo' => 'C.CERO X TRABAJAR Y TRABAJADA PGC', 'nums' => [88, 89]],
    ['titulo' => 'C.CERO X TRABAJAR Y TRABAJADA NF', 'nums' => [90, 91]],
    ['titulo' => 'DONACIONES', 'nums' => [92]],
    ['titulo' => 'SERVICIO TECNICO', 'nums' => [93]],
];
