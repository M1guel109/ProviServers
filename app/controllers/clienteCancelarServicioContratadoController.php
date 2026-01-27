<?php
// app/controllers/clienteCancelarServicioContratadoController.php

require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/ServicioContratado.php';

session_start();

$usuarioId  = (int)($_SESSION['user']['id'] ?? 0);
$contratoId = (int)($_POST['contrato_id'] ?? 0);

// Seguridad básica: solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/cliente/servicios-contratados');
    exit;
}

if ($usuarioId <= 0) {
    // Si tienes login para cliente, aquí podrías redirigir a login
    $_SESSION['flash_error'] = 'Debes iniciar sesión.';
    header('Location: ' . BASE_URL . '/login');
    exit;
}

if ($contratoId <= 0) {
    $_SESSION['flash_error'] = 'Solicitud inválida.';
    header('Location: ' . BASE_URL . '/cliente/servicios-contratados');
    exit;
}

$modelo = new ServicioContratado();

/**
 * Importante:
 * Este método ya valida:
 * - que el contrato pertenezca al usuario (via join con clientes.usuario_id)
 * - que el estado esté en ('pendiente','confirmado')
 * y si no cumple, no actualiza (rowCount = 0).
 */
$ok = $modelo->cancelarPorClienteUsuario($contratoId, $usuarioId);

if ($ok) {
    $_SESSION['flash_success'] = 'Servicio cancelado correctamente.';
} else {
    // Causa típica: no pertenece al cliente, o ya no estaba pendiente/confirmado
    $_SESSION['flash_error'] = 'No se pudo cancelar. Es posible que el servicio ya haya cambiado de estado.';
}

header('Location: ' . BASE_URL . '/cliente/servicios-contratados');
exit;
