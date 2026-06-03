<?php

session_start();

$configFile = dirname(__DIR__) . '/config.php';
if (!is_file($configFile)) {
    die('Falta config.php — copie config.php.example y configure MySQL (ver README_MIARROBA.md).');
}
require_once $configFile;
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/funciones.php';
require_once __DIR__ . '/planograma.php';

try {
    init_tablas(db());
} catch (PDOException $ex) {
    die('Error de base de datos: ' . e($ex->getMessage()) . ' — Revise config.php e importe database.sql.');
}

if (!is_dir(dirname(__DIR__) . '/exportaciones')) {
    mkdir(dirname(__DIR__) . '/exportaciones', 0755, true);
}
