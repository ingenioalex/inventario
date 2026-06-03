<?php
require_once __DIR__ . '/includes/init.php';

if (!empty($_SESSION['sesion_id'])) {
    redirect('mapa.php');
}

$titulo = 'Iniciar';
$subtitulo = 'Indique su nombre y area';
require __DIR__ . '/includes/cabecera.php';
?>
<section class="tarjeta">
  <h2>Iniciar</h2>
  <form method="post" action="<?= e(url('acciones.php')) ?>">
    <input type="hidden" name="action" value="iniciar_sesion">
    <label for="usuario">Su nombre</label>
    <input type="text" id="usuario" name="usuario" required autocomplete="name" placeholder="Ej: Juan Perez" autofocus>
    <label for="area">Area de trabajo</label>
    <input type="text" id="area" name="area" required autocomplete="organization" placeholder="Ej: Bodega">
    <button type="submit" class="btn-primario">Entrar</button>
  </form>
</section>
<?php
require __DIR__ . '/includes/pie.php';
