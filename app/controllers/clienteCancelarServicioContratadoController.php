<?php
require_once __DIR__ . '/../models/ServicioContratado.php';
session_start();

$usuarioId = (int)($_SESSION['user']['id'] ?? 0);
$contratoId = (int)($_POST['contrato_id'] ?? 0);

if ($usuarioId <= 0 || $contratoId <= 0) {
    header('Location: ' . BASE_URL . '/cliente/servicios-contratados');
    exit;
}

$modelo = new ServicioContratado();

// 1) Verifica que el contrato sea del cliente logueado
if (!$modelo->contratoPerteneceACliente($contratoId, $usuarioId)) {
    $_SESSION['flash_error'] = 'No tienes permisos para cancelar este servicio.';
    header('Location: ' . BASE_URL . '/cliente/servicios-contratados');
    exit;
}

// 2) Cancela solo si está pendiente o confirmado
$ok = $modelo->cancelarPorClienteUsuario($contratoId, $usuarioId);

$_SESSION['flash_success'] = $ok ? 'Servicio cancelado correctamente.' : 'No se pudo cancelar (quizá ya cambió de estado).';
header('Location: ' . BASE_URL . '/cliente/servicios-contratados');
exit;
