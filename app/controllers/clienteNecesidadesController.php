<?php
require_once __DIR__ . '/../models/Necesidad.php';
session_start();

$usuarioId = $_SESSION['user']['id'] ?? null;
if (!$usuarioId) { header('Location: '.BASE_URL.'/login'); exit; }

$model = new Necesidad();

$necesidadId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$misNecesidades = $model->listarPorClienteUsuario((int)$usuarioId);

$detalle = [];
$cotizaciones = [];
if ($necesidadId > 0) {
    $detalle = $model->obtenerPorIdParaCliente((int)$usuarioId, $necesidadId);
    if ($detalle) {
        $cotizaciones = $model->listarCotizacionesDeNecesidadParaCliente((int)$usuarioId, $necesidadId);
    }
}

require BASE_PATH . '/app/views/dashboard/cliente/necesidades.php';
