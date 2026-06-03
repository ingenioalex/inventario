<?php
require_once __DIR__ . '/includes/init.php';
requiere_sesion();

$palletActual = $_SESSION['pallet_actual'] ?? null;
$totalLineas = 0;
$zonaPallet = null;
$registros = [];
$palletDisplay = null;

if ($palletActual) {
    $totalLineas = contar_registros_pallet((int) $_SESSION['sesion_id'], $palletActual);
    $zonaPallet = obtener_zona_pallet($palletActual);
    $registros = registros_por_pallet((int) $_SESSION['sesion_id'], $palletActual);
    $palletDisplay = (string) (int) $palletActual;
}

$titulo = 'Captura';
$subtitulo = $palletActual ? "Pallet $palletDisplay" : 'Escanear productos';
$extraHead = '<link rel="stylesheet" href="' . e(url('assets/css/escaner.css')) . '">';
$contenedorClass = 'contenedor' . ($palletActual ? ' con-barra-fija' : '');
require __DIR__ . '/includes/cabecera.php';
?>
<nav class="nav-sesion">
  <a href="<?= e(url('mapa.php')) ?>">Mapa</a>
  <a href="<?= e(url('inventario.php')) ?>" class="activo">Captura</a>
</nav>
<div class="chip-sesion">
  <span class="chip"><?= e($_SESSION['usuario']) ?></span>
  <span class="chip"><?= e($_SESSION['area']) ?></span>
</div>
<?php if (!empty($_SESSION['ultimo_txt'])): ?>
<div class="bloque-descarga">
  <p>Archivo listo</p>
  <a href="<?= e(url('descargar.php')) ?>" class="btn btn-descargar">Descargar TXT</a>
</div>
<?php endif; ?>

<?php if ($palletActual): ?>
<div class="resumen-pallet">
  <div>
    <div class="chip chip-pallet">Pallet <span class="num-grande"><?= e($palletDisplay) ?></span></div>
    <?php if ($zonaPallet): ?>
    <div style="font-size:0.85rem;color:var(--suave);margin-top:0.25rem"><?= e($zonaPallet['jerarquia']) ?></div>
    <?php endif; ?>
  </div>
  <div class="contador">
    <div class="n"><?= $totalLineas ?></div>
    <div class="lbl">lineas</div>
  </div>
</div>
<section class="tarjeta">
  <h2>Nueva linea</h2>
  <form method="post" action="<?= e(url('acciones.php')) ?>" id="form-registro">
    <input type="hidden" name="action" value="agregar_registro">
    <?php
    $eanId = 'ean';
    $eanName = 'ean';
    $eanValue = '';
    $eanAutofocus = true;
    require __DIR__ . '/includes/vista_campo_ean.php';
    ?>
    <div class="fila">
      <div><label for="cantidad">Cant.</label><input type="number" id="cantidad" name="cantidad" required min="1" value="1"></div>
      <div><label for="numero_caja">Caja</label><input type="text" id="numero_caja" name="numero_caja" required placeholder="Nro"></div>
    </div>
    <button type="submit" class="btn-primario">+ Agregar</button>
  </form>
</section>
<?php require __DIR__ . '/includes/vista_lista_registros.php'; ?>
<form method="post" action="<?= e(url('acciones.php')) ?>">
  <input type="hidden" name="action" value="cambiar_pallet">
  <button type="submit" class="btn-secundario">Cambiar pallet</button>
</form>
<?php
require __DIR__ . '/includes/vista_barra_pallet.php';
require __DIR__ . '/includes/vista_modal_escaner.php';
else: ?>
<p class="aviso-mapa">Elija un pallet en el <a href="<?= e(url('mapa.php')) ?>">mapa</a></p>
<section class="tarjeta">
  <h2>Abrir pallet</h2>
  <form method="post" action="<?= e(url('acciones.php')) ?>">
    <input type="hidden" name="action" value="seleccionar_pallet">
    <label for="pallet">Numero (1 a 200)</label>
    <select id="pallet" name="pallet" required>
      <option value="">-- Seleccione --</option>
      <?php for ($i = 1; $i <= 200; $i++): ?>
      <option value="<?= sprintf('%03d', $i) ?>"><?= $i ?></option>
      <?php endfor; ?>
    </select>
    <button type="submit" class="btn-primario">Abrir</button>
  </form>
</section>
<?php endif; ?>

<form method="post" action="<?= e(url('acciones.php')) ?>" onsubmit="return confirm('Salir?');">
  <input type="hidden" name="action" value="salir">
  <button type="submit" class="btn-salir">Salir</button>
</form>
<?php
$scriptsExtra = '';
if ($palletActual) {
    $scriptsExtra = '<script src="' . e(url('assets/vendor/html5-qrcode.min.js')) . '"></script>'
        . '<script src="' . e(url('assets/js/escaner.js')) . '"></script>'
        . '<script>const f=document.getElementById("form-registro");if(f){const e=document.getElementById("ean");f.addEventListener("submit",()=>setTimeout(()=>{e.value="";e.focus();},80));}</script>';
}
require __DIR__ . '/includes/pie.php';
