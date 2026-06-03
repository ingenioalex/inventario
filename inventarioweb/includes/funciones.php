<?php

require_once __DIR__ . '/distribucion.php';

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim(BASE_PATH, '/');
    if ($path === '') {
        return ($base === '' ? '' : $base) . '/';
    }
    return ($base === '' ? '' : $base . '/') . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function flash(string $tipo, string $msg): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $msg];
}

function obtener_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function requiere_sesion(): void
{
    if (empty($_SESSION['sesion_id'])) {
        redirect('index.php');
    }
}

function normalizar_pallet(string $valor): ?string
{
    $limpio = trim($valor);
    if (!ctype_digit($limpio)) {
        return null;
    }
    $n = (int) $limpio;
    if ($n < 1 || $n > 200) {
        return null;
    }
    return sprintf('%03d', $n);
}

function crear_sesion(string $usuario, string $area): int
{
    $pdo = db();
    $stmt = $pdo->prepare(
        'INSERT INTO sesiones (usuario, area, fecha_inicio, activa) VALUES (?, ?, NOW(), 1)'
    );
    $stmt->execute([trim($usuario), trim($area)]);
    return (int) $pdo->lastInsertId();
}

function cerrar_sesion_db(int $id): void
{
    $pdo = db();
    $pdo->prepare('UPDATE sesiones SET activa = 0 WHERE id = ?')->execute([$id]);
}

function agregar_registro(
    int $sesionId,
    string $usuario,
    string $area,
    string $pallet,
    string $ean,
    int $cantidad,
    string $numeroCaja
): void {
    $pdo = db();
    $stmt = $pdo->prepare(
        'INSERT INTO registros (sesion_id, usuario, fecha, area, pallet, ean, cantidad, numero_caja)
         VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $sesionId, $usuario, $area, $pallet,
        trim($ean), $cantidad, trim($numeroCaja),
    ]);
}

function registros_por_pallet(int $sesionId, string $pallet): array
{
    $stmt = db()->prepare(
        'SELECT * FROM registros WHERE sesion_id = ? AND pallet = ? ORDER BY id'
    );
    $stmt->execute([$sesionId, $pallet]);
    return $stmt->fetchAll();
}

function contar_registros_pallet(int $sesionId, string $pallet): int
{
    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM registros WHERE sesion_id = ? AND pallet = ?'
    );
    $stmt->execute([$sesionId, $pallet]);
    return (int) $stmt->fetchColumn();
}

function pallets_con_datos_sesion(int $sesionId): array
{
    $stmt = db()->prepare(
        'SELECT pallet, COUNT(*) AS n FROM registros WHERE sesion_id = ? GROUP BY pallet'
    );
    $stmt->execute([$sesionId]);
    $out = [];
    foreach ($stmt->fetchAll() as $r) {
        $out[$r['pallet']] = (int) $r['n'];
    }
    return $out;
}

function todas_zonas_pallets(): array
{
    $rows = db()->query('SELECT * FROM pallet_zonas ORDER BY numero')->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        $out[$r['pallet']] = $r;
    }
    return $out;
}

function obtener_zona_pallet(string $pallet): ?array
{
    $stmt = db()->prepare('SELECT * FROM pallet_zonas WHERE pallet = ?');
    $stmt->execute([$pallet]);
    $r = $stmt->fetch();
    return $r ?: null;
}

function obtener_registro(int $id, int $sesionId): ?array
{
    $stmt = db()->prepare('SELECT * FROM registros WHERE id = ? AND sesion_id = ?');
    $stmt->execute([$id, $sesionId]);
    $r = $stmt->fetch();
    return $r ?: null;
}

function actualizar_registro(int $id, int $sesionId, string $ean, int $cantidad, string $caja): void
{
    $stmt = db()->prepare(
        'UPDATE registros SET ean = ?, cantidad = ?, numero_caja = ?, fecha = NOW()
         WHERE id = ? AND sesion_id = ?'
    );
    $stmt->execute([trim($ean), $cantidad, trim($caja), $id, $sesionId]);
}

function eliminar_registro(int $id, int $sesionId): void
{
    $stmt = db()->prepare('DELETE FROM registros WHERE id = ? AND sesion_id = ?');
    $stmt->execute([$id, $sesionId]);
}

function armar_txt(array $registros, string $pallet, string $usuario, string $area): string
{
    $lineas = [
        'INVENTARIO - PALLET',
        "Pallet: $pallet",
        "Usuario: $usuario",
        "Area: $area",
        'Fecha exportacion: ' . date('Y-m-d H:i:s'),
        'Total lineas: ' . count($registros),
        "\t",
        'EAN; CANTIDAD; CAJA',
    ];
    foreach ($registros as $r) {
        $lineas[] = $r['ean'] . '; ' . $r['cantidad'] . '; ' . $r['numero_caja'];
    }
    return implode("\n", $lineas) . "\n";
}

function guardar_txt(string $contenido, string $pallet, int $sesionId): string
{
    $dir = dirname(__DIR__) . '/exportaciones';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $nombre = sprintf('pallet_%s_sesion%d_%s.txt', $pallet, $sesionId, date('Ymd_His'));
    file_put_contents($dir . '/' . $nombre, $contenido);
    return $nombre;
}

function es_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
}
