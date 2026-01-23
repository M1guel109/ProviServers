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
// Solo ejecutar lógica automática si NO estamos siendo incluidos desde una vista para obtener datos
if (basename($_SERVER['PHP_SELF']) == 'index.php' && $_SERVER['REQUEST_URI'] == '/ProviServers/proveedor/actualizar-estado') {
    // Aquí sí dejamos que el switch corra
    switch ($method) {
        case 'GET':
            mostrarServiciosContratadosProveedor();
            break;

        case 'POST':
            actualizarEstadoServicio();
            break;


        default:
            http_response_code(405);
            echo "Método no permitido";
            break;
    }
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

function actualizarEstadoServicio()
{
    header('Content-Type: application/json');

    if (!isset($_POST['contrato_id'], $_POST['estado'])) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'msg' => 'Datos incompletos'
        ]);
        return;
    }

    $contratoId  = (int) $_POST['contrato_id'];
    $nuevoEstado = trim($_POST['estado']);
    $usuarioId   = $_SESSION['user']['id'];

    $modelo = new ServicioContratado();

    // 🔐 Validar propiedad del contrato
    if (!$modelo->contratoPerteneceAProveedor($contratoId, $usuarioId)) {
        http_response_code(403);
        echo json_encode([
            'ok' => false,
            'msg' => 'No autorizado'
        ]);
        return;
    }

    $ok = $modelo->actualizarEstado($contratoId, $nuevoEstado);

    if (!$ok) {
        http_response_code(422);
        echo json_encode([
            'ok' => false,
            'msg' => 'Estado no válido o error al actualizar'
        ]);
        return;
    }

    echo json_encode([
        'ok'     => true,
        'estado' => $nuevoEstado
    ]);
}
