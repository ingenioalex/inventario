<?php
/**
 * @var int $numero
 * @var array $zonas
 * @var array $estados
 * @var string|null $palletActivo
 */
$codigo = sprintf('%03d', $numero);
$zona = $zonas[$codigo] ?? ['color_clase' => 'bodega-pgc', 'jerarquia' => '', 'ubicacion' => ''];
$count = $estados[$codigo] ?? 0;
$lleno = $count > 0;
$clase = $zona['color_clase'];
$activo = ($palletActivo ?? '') === $codigo ? ' activo' : '';
$title = $zona['jerarquia'] . ' - ' . $zona['ubicacion'] . ($lleno ? " ($count lineas)" : ' (vacio)');
?>
<a href="<?= e(url('acciones.php?action=abrir_pallet&pallet=' . $codigo)) ?>"
   class="celda-pallet zona-<?= e($clase) ?><?= $lleno ? ' lleno' : '' ?><?= $activo ?>"
   title="<?= e($title) ?>"><?= (int) $numero ?></a>
