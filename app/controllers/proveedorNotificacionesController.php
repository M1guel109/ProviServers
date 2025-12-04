<?php
// app/controllers/proveedorNotificacionesController.php

require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/ProveedorNotificaciones.php';

session_start();

// 1. Validar sesión y rol
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'proveedor') {
    header('Location: ' . BASE_URL . '/login');
    exit();
}

// 2. Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Método no permitido";
    exit();
}

$usuarioId = (int) ($_SESSION['user']['id'] ?? 0);

// 3. Capturar datos del formulario
$data = [
    'noti_solicitudes_nuevas' => $_POST['noti_solicitudes_nuevas'] ?? null,
    'noti_cambios_estado'     => $_POST['noti_cambios_estado'] ?? null,
    'noti_resenas'            => $_POST['noti_resenas'] ?? null,
    'noti_pagos'              => $_POST['noti_pagos'] ?? null,

    'canal_email'             => $_POST['canal_email'] ?? null,
    'canal_interna'           => $_POST['canal_interna'] ?? null,
    'canal_whatsapp'          => $_POST['canal_whatsapp'] ?? null,

    'resumen_diario'          => $_POST['resumen_diario'] ?? null,
    'resumen_semanal'         => $_POST['resumen_semanal'] ?? null,
];

// 4. Validaciones mínimas
$errores = [];

// Si quiere recibir algo, que al menos tenga un canal
$hayAlgunaNotificacion = !empty($data['noti_solicitudes_nuevas'])
    || !empty($data['noti_cambios_estado'])
    || !empty($data['noti_resenas'])
    || !empty($data['noti_pagos']);

$hayAlgúnCanal = !empty($data['canal_email'])
    || !empty($data['canal_interna'])
    || !empty($data['canal_whatsapp']);

if ($hayAlgunaNotificacion && !$hayAlgúnCanal) {
    $errores[] = 'Selecciona al menos un canal de envío (correo, notificaciones internas o WhatsApp).';
}

// Evitar seleccionar diario y semanal a la vez
if (!empty($data['resumen_diario']) && !empty($data['resumen_semanal'])) {
    $errores[] = 'Elige solo una opción de resumen (diario o semanal).';
}

if (!empty($errores)) {
    $mensaje = implode('<br>', $errores);
    mostrarSweetAlert(
        'error',
        'Configuración inválida',
        $mensaje,
        BASE_URL . '/proveedor/configuracion#notificaciones'
    );
    exit();
}

// 5. Guardar
$modelo = new ProveedorNotificaciones();
$ok = $modelo->guardarDesdeFormulario($usuarioId, $data);

if ($ok) {
    mostrarSweetAlert(
        'success',
        'Notificaciones actualizadas',
        'Tus preferencias de notificación se guardaron correctamente.',
        BASE_URL . '/proveedor/configuracion#notificaciones'
    );
} else {
    mostrarSweetAlert(
        'error',
        'Error al guardar',
        'Ocurrió un problema al guardar tus notificaciones. Inténtalo de nuevo.',
        BASE_URL . '/proveedor/configuracion#notificaciones'
    );
}
