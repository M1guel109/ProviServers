<?php

// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/RecoveryPass.php';

$email = $_POST['correo'] ?? '';
// $asunto = $_POST['asunto'] ?? '';
// $mensaje = $_POST['mensaje'] ?? '';

// Validamos lo campos que son obligatorios
if (empty($email)) {
    mostrarSweetAlert('error', 'Campos vacío', 'Por favor completa el campo.');
    exit();
}

$objModelo = new recoveryPass();
$resultado = $objModelo->recuperarClave($email);

// Agregar sweetAlert de envio o no envio del correo
// Si la respuesta del modelo es verdadera confirmamos el registro y redireccionamos ,si es falsa notificamosy redireccionamos
if ($resultado === true) {
    mostrarSweetAlert('success', 'Nueva clave generada', 'Se ha enviado una nueva contraseña a tu correo electronico.', '/ProviServers/login');
} else {
    mostrarSweetAlert('error', 'Usuario no encontrado', 'Verifique su correo electronico e Intente nuevamente.');
}
exit();
