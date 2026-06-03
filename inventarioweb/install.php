<?php
/**
 * Ejecute UNA VEZ tras subir archivos: https://su-dominio.com/inventarioweb/install.php
 * Luego BORRE este archivo por seguridad.
 */
require_once __DIR__ . '/includes/init.php';

echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Instalar</title></head><body style="font-family:sans-serif;padding:2rem">';
echo '<h1>Instalacion inventario</h1>';
try {
    init_tablas(db());
    $n = (int) db()->query('SELECT COUNT(*) FROM pallet_zonas')->fetchColumn();
    echo '<p style="color:green">OK. Tablas listas. Pallets en mapa: <strong>' . $n . '</strong></p>';
    echo '<p><a href="' . e(url('index.php')) . '">Ir a la aplicacion</a></p>';
    echo '<p><strong>Importante:</strong> elimine install.php del servidor.</p>';
} catch (Throwable $ex) {
    echo '<p style="color:red">Error: ' . e($ex->getMessage()) . '</p>';
}
echo '</body></html>';
