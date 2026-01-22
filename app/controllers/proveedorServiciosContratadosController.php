<?php
// Dependencias
require_once __DIR__ . '/../helpers/notificaciones_helper.php';
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/servicioContratado.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// 🔐 Validar sesión
if (
    !isset($_SESSION['user']['id']) ||
    !isset($_SESSION['user']['rol']) ||
    $_SESSION['user']['rol'] !== 'proveedor'
) {
    mostrarSweetAlert(
        'error',
        'Acceso denegado',
        'Debes iniciar sesión como proveedor',
        '/ProviServers/login'
    );
    exit();
}


$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        mostrarServiciosContratadosProveedor();
        break;

    default:
        http_response_code(405);
        echo "Método no permitido";
        break;
}

/* ======================================================
   FUNCIONES
   ====================================================== */

function mostrarServiciosContratadosProveedor()
{
    $usuarioId = $_SESSION['user']['id'];

    $modelo = new ServicioContratado();
    $servicios = $modelo->listarPorProveedorUsuario($usuarioId);

    // 👉 esta variable queda disponible para la vista
    return $servicios;
}
