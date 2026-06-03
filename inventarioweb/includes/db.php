<?php

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function seed_distribucion(PDO $pdo): void
{
    $n = (int) $pdo->query('SELECT COUNT(*) FROM pallet_zonas')->fetchColumn();
    if ($n > 0) {
        return;
    }
    require_once __DIR__ . '/distribucion.php';

    $stmtCat = $pdo->prepare(
        'INSERT INTO zonas_catalogo (jerarquia, ubicacion, cantidad, rango_inicio, rango_fin, color_clase)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmtPal = $pdo->prepare(
        'INSERT INTO pallet_zonas (pallet, numero, jerarquia, ubicacion, color_clase, rango_inicio, rango_fin)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );

    foreach (ZONAS_RANGO as [$jer, $ubi, $color, $ini, $fin]) {
        $stmtCat->execute([$jer, $ubi, $fin - $ini + 1, $ini, $fin, $color]);
        for ($n = $ini; $n <= $fin; $n++) {
            $stmtPal->execute([pallet_codigo($n), $n, $jer, $ubi, $color, $ini, $fin]);
        }
    }
}

function init_tablas(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sesiones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario VARCHAR(120) NOT NULL,
            area VARCHAR(120) NOT NULL,
            fecha_inicio DATETIME NOT NULL,
            activa TINYINT DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS registros (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sesion_id INT NOT NULL,
            usuario VARCHAR(120) NOT NULL,
            fecha DATETIME NOT NULL,
            area VARCHAR(120) NOT NULL,
            pallet VARCHAR(3) NOT NULL,
            ean VARCHAR(64) NOT NULL,
            cantidad INT NOT NULL,
            numero_caja VARCHAR(32) NOT NULL,
            FOREIGN KEY (sesion_id) REFERENCES sesiones(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS pallet_zonas (
            pallet VARCHAR(3) PRIMARY KEY,
            numero INT NOT NULL UNIQUE,
            jerarquia VARCHAR(80) NOT NULL,
            ubicacion VARCHAR(80) NOT NULL,
            color_clase VARCHAR(32) NOT NULL,
            rango_inicio INT NOT NULL,
            rango_fin INT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS zonas_catalogo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            jerarquia VARCHAR(80) NOT NULL,
            ubicacion VARCHAR(80) NOT NULL,
            cantidad INT NOT NULL,
            rango_inicio INT NOT NULL,
            rango_fin INT NOT NULL,
            color_clase VARCHAR(32) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    seed_distribucion($pdo);
}
