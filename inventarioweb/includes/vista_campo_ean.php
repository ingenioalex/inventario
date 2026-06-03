<?php
/** @var string $eanId */
/** @var string $eanName */
/** @var string $eanValue */
/** @var bool $eanAutofocus */
$eanId = $eanId ?? 'ean';
$eanName = $eanName ?? 'ean';
$eanValue = $eanValue ?? '';
$eanAutofocus = $eanAutofocus ?? false;
?>
<div class="ean-campo" data-ean-wrapper>
  <label for="<?= e($eanId) ?>">Codigo EAN / barras</label>
  <div class="ean-fila">
    <input type="text" id="<?= e($eanId) ?>" name="<?= e($eanName) ?>" class="ean-input" value="<?= e($eanValue) ?>"
           required inputmode="numeric" autocomplete="off" placeholder="Escanee o escriba"
           <?= $eanAutofocus ? 'autofocus' : '' ?>>
    <button type="button" class="btn-camara" data-escanear aria-label="Escanear codigo con camara" title="Escanear">
      <span class="btn-camara-icono" aria-hidden="true">📷</span>
    </button>
  </div>
  <p class="ean-ayuda" data-ean-ayuda hidden>Toque el icono <strong>📷</strong> para usar la camara</p>
</div>
