<?php
require_once __DIR__ . '/../models/Necesidad.php';
session_start();

if (!isset($_SESSION['user']['id']) || ($_SESSION['user']['rol'] ?? '') !== 'proveedor') {
    header('Location: '.BASE_URL.'/login'); exit;
}

$usuarioIdProv = (int)$_SESSION['user']['id'];
$model = new Necesidad();

$necesidadId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$necesidades = $model->listarAbiertasParaProveedor($usuarioIdProv);

$detalle = [];
$yaOferto = false;
if ($necesidadId > 0) {
    $detalle = $model->obtenerPorIdParaProveedor($usuarioIdProv, $necesidadId);
    $yaOferto = $model->yaOferto($usuarioIdProv, $necesidadId);
}

require BASE_PATH . '/app/views/dashboard/proveedor/necesidades.php';
