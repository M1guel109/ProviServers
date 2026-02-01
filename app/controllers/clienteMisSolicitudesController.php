<?php
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/Solicitud.php';

session_start();

// 🔐 Solo clientes logueados
if (!isset($_SESSION['user']['id'])) {
    mostrarSweetAlert('error', 'Acceso denegado', 'Debes iniciar sesión.');
    exit;
}
if (isset($_SESSION['user']['rol']) && $_SESSION['user']['rol'] !== 'cliente') {
    mostrarSweetAlert('error', 'Acceso denegado', 'Solo los clientes pueden ver esta sección.');
    exit;
}

$usuarioId = (int) $_SESSION['user']['id'];

$estado = $_GET['estado'] ?? 'pendiente';
$estadosValidos = ['pendiente', 'aceptada', 'rechazada', 'cancelada'];
if (!in_array($estado, $estadosValidos, true)) {
    $estado = 'pendiente';
}

$solicitudId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$model = new Solicitud();

$contadores = $model->contarPorEstadoClienteUsuario($usuarioId);
$solicitudes = $model->listarPorClienteUsuarioYEstado($usuarioId, $estado);

$detalle = [];
if ($solicitudId > 0) {
    $detalle = $model->obtenerDetallePorClienteUsuario($usuarioId, $solicitudId);
}

// ✅ Cargar vista
require_once BASE_PATH . '/app/views/dashboard/cliente/misSolicitudes.php';
