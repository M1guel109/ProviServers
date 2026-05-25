<?php

require_once __DIR__ . '/../helpers/alert-helper.php';
require_once __DIR__ . '/../models/Mensajeria.php';

// ===================================================================
// GUARD DE SESIÓN Y ROL
// ===================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_rolMensajes = $_SESSION['user']['rol'] ?? '';

if (!isset($_SESSION['user']['id']) || !in_array($_rolMensajes, ['cliente', 'proveedor'], true)) {
    mostrarSweetAlert('error', 'Acceso denegado', 'Debes iniciar sesión para acceder a mensajes.', BASE_URL . '/login');
    exit();
}

// ===================================================================
// ROUTER INTERNO — Dispatch por método HTTP y URI
// ===================================================================

$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

switch ($method) {

    case 'GET':
        if (str_contains($uri, '/mensajes/abrir')) {
            abrirConversacion();
        } elseif (str_contains($uri, '/mensajes/ver')) {
            verConversacion();
        } elseif (str_contains($uri, '/mensajes/poll')) {
            obtenerMensajesNuevos();
        } else {
            mostrarInbox();
        }
        break;

    case 'POST':
        $accion = $_POST['accion'] ?? '';
        if ($accion === 'enviar' || str_contains($uri, '/mensajes/enviar')) {
            enviarMensaje();
        } else {
            http_response_code(400);
            mostrarSweetAlert('error', 'Acción no válida', 'La acción POST solicitada no existe.');
            exit();
        }
        break;

    default:
        http_response_code(405);
        mostrarSweetAlert('error', 'Método no permitido', 'Esta ruta no acepta ese tipo de petición.');
        exit();
}

// ===================================================================
// FUNCIONES DEL CONTROLADOR
// ===================================================================

function mostrarInbox()
{
    $uid    = (int)$_SESSION['user']['id'];
    $modelo = new Mensajeria();

    $conversaciones = $modelo->listarInbox($uid);
    $vista          = resolverVistaRol('inbox');

    require $vista;
    exit();
}

function abrirConversacion()
{
    $uid  = (int)$_SESSION['user']['id'];
    $tipo = $_GET['tipo'] ?? '';
    $id   = (int)($_GET['id'] ?? 0);

    if ($id <= 0 || !in_array($tipo, ['solicitud', 'cotizacion'], true)) {
        mostrarSweetAlert('error', 'Parámetros inválidos', 'Los parámetros solicitados no son válidos.', BASE_URL . '/cliente/mensajes');
        exit();
    }

    try {
        $modelo = new Mensajeria();
        $convId = ($tipo === 'solicitud')
            ? $modelo->getOrCreateFromSolicitud($id, $uid)
            : $modelo->getOrCreateFromCotizacion($id, $uid);

        header('Location: ' . BASE_URL . '/mensajes/ver?id=' . $convId);
        exit();
    } catch (Exception $e) {
        error_log('Error en abrirConversacion -> ' . $e->getMessage());
        mostrarSweetAlert('error', 'Error', 'No se pudo abrir la conversación. Verifica que tengas acceso.', BASE_URL . '/cliente/mensajes');
        exit();
    }
}

function verConversacion()
{
    $uid    = (int)$_SESSION['user']['id'];
    $convId = (int)($_GET['id'] ?? 0);

    if ($convId <= 0) {
        mostrarSweetAlert('error', 'Conversación inválida', 'La conversación solicitada no existe.', BASE_URL . '/cliente/mensajes');
        exit();
    }

    $modelo = new Mensajeria();

    if (!$modelo->usuarioTieneAcceso($convId, $uid)) {
        mostrarSweetAlert('error', 'Acceso denegado', 'No tienes acceso a esta conversación.', BASE_URL . '/cliente/mensajes');
        exit();
    }

    $conversacion = $modelo->obtenerConversacionPorId($convId);
    if (!$conversacion) {
        mostrarSweetAlert('error', 'Error', 'No existe la conversación.', BASE_URL . '/cliente/mensajes');
        exit();
    }

    $tema          = $modelo->obtenerTema($convId);
    $otroUsuarioId = $modelo->obtenerOtroUsuarioId($conversacion, $uid);
    $mensajes      = $modelo->listarMensajesPorConversacion($convId, 80);
    $modelo->marcarMensajesLeidos($convId, $uid);

    $vista = resolverVistaRol('chat');

    require $vista;
    exit();
}

function enviarMensaje()
{
    header('Content-Type: application/json; charset=utf-8');

    $uid    = (int)$_SESSION['user']['id'];
    $convId = (int)($_POST['conversacion_id'] ?? 0);
    $texto  = trim($_POST['mensaje'] ?? '');

    if ($convId <= 0 || empty($texto)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
        exit;
    }

    $modelo       = new Mensajeria();
    $conversacion = $modelo->obtenerConversacionPorId($convId);

    if (!$conversacion || !$modelo->usuarioTieneAcceso($convId, $uid)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
        exit;
    }

    $receptorId = $modelo->obtenerOtroUsuarioId($conversacion, $uid);
    $msgId      = $modelo->crearMensaje($convId, $uid, $receptorId, $texto);

    echo json_encode(['ok' => true, 'id' => $msgId]);
    exit;
}

function obtenerMensajesNuevos()
{
    header('Content-Type: application/json; charset=utf-8');

    $uid    = (int)$_SESSION['user']['id'];
    $convId = (int)($_GET['id'] ?? 0);
    $after  = $_GET['after'] ?? null;

    if ($convId <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Parámetros inválidos']);
        exit;
    }

    $modelo = new Mensajeria();

    if (!$modelo->usuarioTieneAcceso($convId, $uid)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
        exit;
    }

    $nuevos = $modelo->listarMensajesNuevos($convId, $after);
    if (!empty($nuevos)) {
        $modelo->marcarMensajesLeidos($convId, $uid);
    }

    echo json_encode(['ok' => true, 'mensajes' => $nuevos]);
    exit;
}

// ===================================================================
// UTILIDAD — Resolver vista según rol del usuario
// ===================================================================

function resolverVistaRol(string $nombre): string
{
    $rol = $_SESSION['user']['rol'] ?? '';

    $rutas = [
        'cliente'   => BASE_PATH . "/app/views/dashboard/cliente/mensajes/{$nombre}.php",
        'proveedor' => BASE_PATH . "/app/views/dashboard/proveedor/mensajes/{$nombre}.php",
    ];

    if (!isset($rutas[$rol])) {
        mostrarSweetAlert('error', 'Rol no permitido', 'Tu rol no tiene acceso a esta sección.', BASE_URL . '/login');
        exit();
    }

    if (!file_exists($rutas[$rol])) {
        mostrarSweetAlert('error', 'Vista no encontrada', "La vista {$nombre}.php no existe para tu rol.", BASE_URL . '/login');
        exit();
    }

    return $rutas[$rol];
}
