<?php
require_once __DIR__ . '/includes/init.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'iniciar_sesion':
        $usuario = trim($_POST['usuario'] ?? '');
        $area = trim($_POST['area'] ?? '');
        if ($usuario === '' || $area === '') {
            flash('error', 'Indique usuario y area.');
            redirect('index.php');
        }
        $id = crear_sesion($usuario, $area);
        $_SESSION['sesion_id'] = $id;
        $_SESSION['usuario'] = $usuario;
        $_SESSION['area'] = $area;
        $_SESSION['pallet_actual'] = null;
        flash('ok', "Sesion iniciada: $usuario ($area)");
        redirect('mapa.php');

    case 'abrir_pallet':
        requiere_sesion();
        $pallet = normalizar_pallet($_GET['pallet'] ?? $_POST['pallet'] ?? '');
        if (!$pallet) {
            flash('error', 'Pallet invalido.');
            redirect('mapa.php');
        }
        $_SESSION['pallet_actual'] = $pallet;
        flash('ok', 'Pallet ' . (int) $pallet . ' abierto.');
        redirect('inventario.php');

    case 'seleccionar_pallet':
        requiere_sesion();
        $pallet = normalizar_pallet($_POST['pallet'] ?? '');
        if (!$pallet) {
            flash('error', 'Pallet invalido.');
            redirect('inventario.php');
        }
        $_SESSION['pallet_actual'] = $pallet;
        flash('ok', 'Pallet ' . (int) $pallet . ' listo.');
        redirect('inventario.php');

    case 'agregar_registro':
        requiere_sesion();
        $pallet = $_SESSION['pallet_actual'] ?? null;
        if (!$pallet) {
            flash('error', 'Seleccione un pallet.');
            redirect('mapa.php');
        }
        $ean = trim($_POST['ean'] ?? '');
        $cantidad = (int) ($_POST['cantidad'] ?? 0);
        $caja = trim($_POST['numero_caja'] ?? '');
        if ($ean === '' || $cantidad < 1 || $caja === '') {
            flash('error', 'Complete EAN, cantidad y caja.');
            redirect('inventario.php');
        }
        agregar_registro(
            (int) $_SESSION['sesion_id'],
            $_SESSION['usuario'],
            $_SESSION['area'],
            $pallet,
            $ean,
            $cantidad,
            $caja
        );
        $total = contar_registros_pallet((int) $_SESSION['sesion_id'], $pallet);
        flash('ok', "Guardado. Total lineas: $total");
        redirect('inventario.php');

    case 'actualizar_registro':
        requiere_sesion();
        $id = (int) ($_POST['registro_id'] ?? 0);
        $reg = obtener_registro($id, (int) $_SESSION['sesion_id']);
        if (!$reg) {
            flash('error', 'Registro no encontrado.');
            redirect('mapa.php');
        }
        $ean = trim($_POST['ean'] ?? '');
        $cantidad = (int) ($_POST['cantidad'] ?? 0);
        $caja = trim($_POST['numero_caja'] ?? '');
        if ($ean === '' || $cantidad < 1 || $caja === '') {
            flash('error', 'Datos invalidos.');
            redirect('inventario.php');
        }
        actualizar_registro($id, (int) $_SESSION['sesion_id'], $ean, $cantidad, $caja);
        $_SESSION['pallet_actual'] = $reg['pallet'];
        flash('ok', 'Linea actualizada.');
        redirect('inventario.php');

    case 'eliminar_registro':
        requiere_sesion();
        $id = (int) ($_POST['registro_id'] ?? 0);
        $reg = obtener_registro($id, (int) $_SESSION['sesion_id']);
        if (!$reg) {
            flash('error', 'Registro no encontrado.');
            redirect('mapa.php');
        }
        eliminar_registro($id, (int) $_SESSION['sesion_id']);
        $_SESSION['pallet_actual'] = $reg['pallet'];
        flash('ok', 'Linea eliminada.');
        redirect('inventario.php');

    case 'cerrar_pallet':
        requiere_sesion();
        $pallet = $_SESSION['pallet_actual'] ?? null;
        if (!$pallet) {
            flash('error', 'No hay pallet activo.');
            redirect('mapa.php');
        }
        $_SESSION['pallet_actual'] = null;
        flash('ok', 'Pallet ' . (int) $pallet . ' cerrado.');
        redirect('mapa.php');

    case 'generar_txt':
        requiere_sesion();
        $pallet = $_SESSION['pallet_actual'] ?? $_POST['pallet'] ?? '';
        $codigo = normalizar_pallet($pallet);
        if (!$codigo) {
            flash('error', 'Pallet invalido.');
            redirect('mapa.php');
        }
        $regs = registros_por_pallet((int) $_SESSION['sesion_id'], $codigo);
        if (!$regs) {
            flash('error', 'Sin lineas para exportar.');
            redirect('inventario.php');
        }
        $txt = armar_txt($regs, $codigo, $_SESSION['usuario'], $_SESSION['area']);
        $_SESSION['ultimo_txt'] = guardar_txt($txt, $codigo, (int) $_SESSION['sesion_id']);
        flash('ok', 'TXT listo: ' . count($regs) . ' lineas.');
        redirect('descargar.php');

    case 'cambiar_pallet':
        requiere_sesion();
        $_SESSION['pallet_actual'] = null;
        flash('ok', 'Seleccione otro pallet en el mapa.');
        redirect('mapa.php');

    case 'salir':
        if (!empty($_SESSION['sesion_id'])) {
            cerrar_sesion_db((int) $_SESSION['sesion_id']);
        }
        session_destroy();
        session_start();
        flash('ok', 'Sesion finalizada.');
        redirect('index.php');

    default:
        redirect('index.php');
}
