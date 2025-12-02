<?php
// app/controllers/logoutController.php

require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../../config/config.php';

session_start();

// Limpiar todas las variables de sesión
$_SESSION = [];
session_unset();

// Destruir la sesión
session_destroy();

// Opcional: borrar la cookie de sesión (por seguridad extra)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Redirigir siempre al login principal
header('Location: ' . BASE_URL . '/login');
exit;
