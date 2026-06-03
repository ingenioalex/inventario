<?php
/** @var string $titulo */
/** @var string $subtitulo */
/** @var string $extraHead */
/** @var string $contenedorClass */
$titulo = $titulo ?? 'Inventario';
$subtitulo = $subtitulo ?? '';
$extraHead = $extraHead ?? '';
$contenedorClass = $contenedorClass ?? 'contenedor';
$esHttps = es_https();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($titulo) ?></title>
  <link rel="stylesheet" href="<?= e(url('assets/css/estilo.css')) ?>">
  <?= $extraHead ?>
</head>
<body data-es-render="0" data-url-wifi="" data-url-render="">
  <div class="<?= e($contenedorClass) ?>">
    <header class="header-app">
      <h1>Inventario</h1>
      <p><?= e($subtitulo) ?></p>
    </header>
    <?php $flash = obtener_flash(); if ($flash): ?>
    <div class="alertas">
      <div class="alerta <?= e($flash['tipo']) ?>"><?= e($flash['msg']) ?></div>
    </div>
    <?php endif; ?>
