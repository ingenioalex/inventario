<?php

const ZONAS_RANGO = [
    ['PGC COMESTIBLE', 'BODEGA', 'bodega-pgc', 1, 48],
    ['PERECIBLES', 'C.CONGELADOS', 'perecibles', 49, 49],
    ['PERECIBLES', 'C.FLC', 'perecibles', 50, 50],
    ['PERECIBLES', 'TRAS. PANADERIA', 'perecibles', 51, 51],
    ['NON FOOD', 'PLATAFORMA', 'non-food', 52, 67],
    ['PGC NO COMESTIBLE', 'PLATAFORMA', 'pgc-nc', 68, 83],
    ['MERMA', 'TRASTIENDA', 'merma-devol', 84, 84],
    ['DEVOLUCION', 'TRASTIENDA', 'merma-devol', 85, 85],
    ['C100 X TRABAJAR Y TRABAJADA', 'PLATAFORMA', 'plataforma-tabla', 86, 87],
    ['C.CERO X TRABAJAR Y TRABAJADA PGC', 'PLATAFORMA', 'plataforma-tabla', 88, 89],
    ['C.CERO X TRABAJAR Y TRABAJADA NF', 'PLATAFORMA', 'plataforma-tabla', 90, 91],
    ['DONACIONES', 'PLATAFORMA', 'plataforma-tabla', 92, 92],
    ['SERVICIO TECNICO', 'PLATAFORMA', 'plataforma-tabla', 93, 93],
    ['ADICIONALES', 'ADICIONALES', 'adicionales', 94, 200],
];

function pallet_codigo(int $n): string
{
    return sprintf('%03d', $n);
}
