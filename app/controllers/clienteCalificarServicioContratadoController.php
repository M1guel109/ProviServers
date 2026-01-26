<?php
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/Valoracion.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/cliente/servicios-contratados');
    exit;
}

$usuarioId   = (int)($_SESSION['user']['id'] ?? 0);
$contratoId  = (int)($_POST['contrato_id'] ?? 0);
$calificacion = (int)($_POST['calificacion'] ?? 0);
$comentario  = isset($_POST['comentario']) ? (string)$_POST['comentario'] : null;

if ($usuarioId <= 0 || $contratoId <= 0) {
    $_SESSION['flash_error'] = 'Solicitud inválida.';
    header('Location: ' . BASE_URL . '/cliente/servicios-contratados');
    exit;
}

$modelo = new Valoracion();
$ok = $modelo->crearPorClienteUsuario($contratoId, $usuarioId, $calificacion, $comentario);

if ($ok) {
    $_SESSION['flash_success'] = '¡Gracias! Tu calificación fue registrada.';
} else {
    $_SESSION['flash_error'] = 'No se pudo calificar. Verifica que el servicio esté finalizado y no lo hayas calificado antes.';
}

header('Location: ' . BASE_URL . '/cliente/servicios-contratados');
exit;
