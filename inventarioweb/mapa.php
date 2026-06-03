<?php
require_once __DIR__ . '/includes/init.php';
requiere_sesion();

$zonas = todas_zonas_pallets();
$estados = pallets_con_datos_sesion((int) $_SESSION['sesion_id']);
$palletActivo = $_SESSION['pallet_actual'] ?? null;

$titulo = 'Planograma';
$subtitulo = 'Mapa HB 318';
$extraHead = '<link rel="stylesheet" href="' . e(url('assets/css/mapa.css')) . '">';
$contenedorClass = 'contenedor contenedor-mapa-pagina';
require __DIR__ . '/includes/cabecera.php';
?>
<div class="mapa-pagina">
  <div class="chip-sesion">
    <span class="chip"><?= e($_SESSION['usuario']) ?></span>
    <span class="chip"><?= e($_SESSION['area']) ?></span>
  </div>
  <nav class="nav-sesion">
    <a href="<?= e(url('mapa.php')) ?>" class="activo">Mapa</a>
    <a href="<?= e(url('inventario.php')) ?>">Captura</a>
  </nav>
  <div class="planograma-wrap">
    <h2 class="planograma-titulo">PLANOGRAMA INV GENERAL HB 318</h2>
    <section class="mapa-seccion">
      <h3 class="mapa-seccion-titulo">Bodega PGC <span class="mapa-rango">1 – 48</span></h3>
      <div class="scroll-x-wrap">
        <div class="scroll-x-inner bodega-layout">
          <div class="grilla-col"><?php foreach (grilla_bodega_izq() as $fila): ?><div class="grilla-fila"><?php foreach ($fila as $n) { require __DIR__ . '/includes/celda_pallet.php'; } ?></div><?php endforeach; ?></div>
          <div class="bodega-centro"><?php foreach (grilla_bodega_centro() as $col): ?><div class="grilla-col"><?php foreach ($col as $n) { require __DIR__ . '/includes/celda_pallet.php'; } ?></div><?php endforeach; ?></div>
          <div class="grilla-col"><?php foreach (grilla_bodega_der() as $fila): ?><div class="grilla-fila"><?php foreach ($fila as $n) { require __DIR__ . '/includes/celda_pallet.php'; } ?></div><?php endforeach; ?></div>
        </div>
      </div>
      <p class="scroll-hint">Deslice horizontal para ver toda la bodega</p>
    </section>
    <section class="mapa-seccion mapa-seccion-compacta">
      <h3 class="mapa-seccion-titulo">Perecibles</h3>
      <div class="mapa-fila-3">
        <div class="mini-zona"><span class="mini-zona-lbl">C. Congelados</span><?php $numero=49; require __DIR__.'/includes/celda_pallet.php'; ?></div>
        <div class="mini-zona"><span class="mini-zona-lbl">C. FLC</span><?php $numero=50; require __DIR__.'/includes/celda_pallet.php'; ?></div>
        <div class="mini-zona mini-zona-wide"><span class="mini-zona-lbl">Tras. Panaderia</span><?php $numero=51; require __DIR__.'/includes/celda_pallet.php'; ?></div>
      </div>
    </section>
    <section class="mapa-seccion">
      <h3 class="mapa-seccion-titulo">Plataforma <span class="mapa-rango">52 – 83</span></h3>
      <div class="plataforma-grid">
        <div class="plataforma-bloque"><h4 class="plataforma-sub">Non Food</h4><?php foreach (grilla_plataforma_non_food() as $fila): ?><div class="grilla-fila"><?php foreach ($fila as $n) { require __DIR__.'/includes/celda_pallet.php'; } ?></div><?php endforeach; ?></div>
        <div class="plataforma-bloque"><h4 class="plataforma-sub">PGC J02</h4><?php foreach (grilla_plataforma_pgc_j02() as $fila): ?><div class="grilla-fila"><?php foreach ($fila as $n) { require __DIR__.'/includes/celda_pallet.php'; } ?></div><?php endforeach; ?></div>
      </div>
    </section>
    <section class="mapa-seccion mapa-seccion-compacta">
      <div class="mapa-fila-2">
        <div class="mini-zona"><span class="mini-zona-lbl">Merma</span><?php $numero=84; require __DIR__.'/includes/celda_pallet.php'; ?></div>
        <div class="mini-zona"><span class="mini-zona-lbl">Devolu.</span><?php $numero=85; require __DIR__.'/includes/celda_pallet.php'; ?></div>
      </div>
    </section>
    <section class="mapa-seccion">
      <h3 class="mapa-seccion-titulo">Plataforma especial <span class="mapa-rango">86 – 93</span></h3>
      <div class="tabla-cards"><?php foreach (TABLA_INFERIOR as $fila): ?><div class="tabla-card"><div class="tabla-card-titulo"><?= e($fila['titulo']) ?></div><div class="tabla-card-celdas"><?php foreach ($fila['nums'] as $n) { require __DIR__.'/includes/celda_pallet.php'; } ?></div></div><?php endforeach; ?></div>
    </section>
    <section class="mapa-seccion mapa-seccion-adicionales">
      <h3 class="mapa-seccion-titulo">Adicionales <span class="mapa-rango">94 – 200</span></h3>
      <div class="adicionales-grid"><?php for ($n = 94; $n <= 200; $n++) { require __DIR__.'/includes/celda_pallet.php'; } ?></div>
    </section>
  </div>
  <form method="post" action="<?= e(url('acciones.php')) ?>" onsubmit="return confirm('Salir?');">
    <input type="hidden" name="action" value="salir">
    <button type="submit" class="btn-salir">Salir</button>
  </form>
</div>
<?php
$scriptsExtra = '';
require __DIR__ . '/includes/pie.php';
