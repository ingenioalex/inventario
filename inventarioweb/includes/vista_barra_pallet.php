<?php
/** @var string|null $palletActual */
/** @var int $totalLineas */
/** @var string $palletDisplay */
if ($palletActual && $totalLineas > 0): ?>
<div class="barra-pallet-fija">
  <form method="post" action="<?= e(url('acciones.php')) ?>" class="barra-form">
    <input type="hidden" name="action" value="generar_txt">
    <input type="hidden" name="pallet" value="<?= e($palletActual) ?>">
    <button type="submit" class="btn-txt">Generar TXT</button>
  </form>
  <form method="post" action="<?= e(url('acciones.php')) ?>" class="barra-form"
        onsubmit="return confirm('Cerrar pallet <?= e($palletDisplay) ?>?');">
    <input type="hidden" name="action" value="cerrar_pallet">
    <button type="submit" class="btn-cerrar-pallet">Cerrar pallet</button>
  </form>
</div>
<?php elseif ($palletActual): ?>
<div class="barra-pallet-fija barra-solo-cerrar">
  <form method="post" action="<?= e(url('acciones.php')) ?>" class="barra-form barra-form-full">
    <input type="hidden" name="action" value="cerrar_pallet">
    <button type="submit" class="btn-secundario">Salir de este pallet</button>
  </form>
</div>
<?php endif; ?>
