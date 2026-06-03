<?php
require_once __DIR__ . '/includes/init.php';
requiere_sesion();

$nombre = $_SESSION['ultimo_txt'] ?? '';
if ($nombre === '' || preg_match('/[^a-zA-Z0-9._-]/', $nombre)) {
    flash('error', 'No hay archivo para descargar.');
    redirect('inventario.php');
}

$ruta = __DIR__ . '/exportaciones/' . $nombre;
if (!is_file($ruta)) {
    flash('error', 'Archivo no encontrado.');
    redirect('inventario.php');
}

header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="' . basename($nombre) . '"');
readfile($ruta);
exit;
