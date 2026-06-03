<?php /** @var array $registros */ ?>
<?php if (!empty($registros)): ?>
<section class="lista-registros">
  <h2 class="lista-titulo">Lineas guardadas (<?= count($registros) ?>)</h2>
  <p class="lista-ayuda">Toque una linea para editarla o eliminarla</p>
  <?php foreach ($registros as $i => $r): ?>
  <details class="linea-card">
    <summary class="linea-resumen">
      <span class="linea-indice">#<?= $i + 1 ?></span>
      <span class="linea-datos">
        <span class="linea-ean"><?= e($r['ean']) ?></span>
        <span class="linea-meta">Cant: <strong><?= (int) $r['cantidad'] ?></strong> · Caja: <strong><?= e($r['numero_caja']) ?></strong></span>
      </span>
    </summary>
    <div class="linea-detalle">
      <form method="post" action="<?= e(url('acciones.php')) ?>">
        <input type="hidden" name="action" value="actualizar_registro">
        <input type="hidden" name="registro_id" value="<?= (int) $r['id'] ?>">
        <?php
        $eanId = 'ean-' . $r['id'];
        $eanName = 'ean';
        $eanValue = $r['ean'];
        $eanAutofocus = false;
        require __DIR__ . '/vista_campo_ean.php';
        ?>
        <div class="fila">
          <div>
            <label>Cantidad</label>
            <input type="number" name="cantidad" value="<?= (int) $r['cantidad'] ?>" min="1" required>
          </div>
          <div>
            <label>N. caja</label>
            <input type="text" name="numero_caja" value="<?= e($r['numero_caja']) ?>" required>
          </div>
        </div>
        <button type="submit" class="btn-primario">Guardar cambios</button>
      </form>
      <form method="post" action="<?= e(url('acciones.php')) ?>" onsubmit="return confirm('Eliminar esta linea?');">
        <input type="hidden" name="action" value="eliminar_registro">
        <input type="hidden" name="registro_id" value="<?= (int) $r['id'] ?>">
        <button type="submit" class="btn-peligro">Eliminar</button>
      </form>
    </div>
  </details>
  <?php endforeach; ?>
</section>
<?php endif; ?>
