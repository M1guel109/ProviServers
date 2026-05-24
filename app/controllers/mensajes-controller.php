<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert-helper.php';
require_once __DIR__ . '/../models/conversacion.php';
require_once __DIR__ . '/../models/mensaje.php';

session_start();

// 1. VALIDACIÓN GLOBAL DE SESIÓN
if (!isset($_SESSION['user']['id'])) {
    http_response_code(403);
    mostrarSweetAlert('error', 'Acceso denegado', 'Debes iniciar sesión para acceder a mensajes.', BASE_URL . '/login');
    exit();
}

// Capturamos el método de la solicitud
$method = $_SERVER['REQUEST_METHOD'];

// 2. ENRUTADOR PRINCIPAL (Switch)
switch ($method) {
    case 'GET':
        $accion = $_GET['accion'] ?? 'inbox';

        if ($accion === 'inbox') {
            mostrarInbox();
        } elseif ($accion === 'abrir') {
            abrirConversacion();
        } elseif ($accion === 'ver') {
            verConversacion();
        } elseif ($accion === 'poll') {
            obtenerMensajesNuevos();
        } else {
            http_response_code(400);
            echo "Acción GET no válida";
        }
        break;

    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'enviar') {
            enviarMensaje();
        } else {
            http_response_code(400);
            echo "Acción POST no válida";
        }
        break;

    default:
        http_response_code(405);
        echo "Método no permitido";
        break;
}

// ======================================================================
// 3. FUNCIONES DEL CONTROLADOR
// ======================================================================

function mostrarInbox()
{
    // 1. Obtener ID del usuario autenticado
    $uid = (int)$_SESSION['user']['id'];

    // 2. Instanciar modelo y obtener conversaciones
    $convModel = new Conversacion();
    $conversaciones = $convModel->listarInbox($uid);

    // 3. Cargar vista correspondiente al rol
    $vistaPerfil = obtenerVistaDeRol('inbox');

    require $vistaPerfil;
}

function abrirConversacion()
{
    // 1. Validación de parámetros
    $uid  = (int)$_SESSION['user']['id'];
    $tipo = $_GET['tipo'] ?? '';
    $id   = (int)($_GET['id'] ?? 0);

    if ($id <= 0 || !in_array($tipo, ['solicitud', 'cotizacion'], true)) {
        mostrarSweetAlert('error', 'Parámetros inválidos', 'Los parámetros solicitados no son válidos.', BASE_URL . '/cliente/mensajes');
        exit();
    }

    try {
        // 2. Obtener o crear conversación según el tipo
        $convModel = new Conversacion();
        $convId = ($tipo === 'solicitud')
            ? $convModel->getOrCreateFromSolicitud($id, $uid)
            : $convModel->getOrCreateFromCotizacion($id, $uid);

        // 3. Redirigir a la vista de conversación
        header("Location: " . BASE_URL . "/mensajes/ver?id=" . $convId);
        exit();
    } catch (Exception $e) {
        mostrarSweetAlert('error', 'Error', 'No se pudo abrir la conversación: ' . $e->getMessage(), BASE_URL . '/cliente/mensajes');
        exit();
    }
}

function verConversacion()
{
    // 1. Validación de parámetros
    $uid    = (int)$_SESSION['user']['id'];
    $convId = (int)($_GET['id'] ?? 0);

    if ($convId <= 0) {
        mostrarSweetAlert('error', 'Conversación inválida', 'La conversación solicitada no existe.', BASE_URL . '/cliente/mensajes');
        exit();
    }

    // 2. Instanciar modelos
    $convModel = new Conversacion();
    $msgModel  = new Mensaje();

    // 3. Validar acceso del usuario a la conversación
    if (!$convModel->usuarioTieneAcceso($convId, $uid)) {
        mostrarSweetAlert('error', 'Acceso denegado', 'No tienes acceso a esta conversación.', BASE_URL . '/cliente/mensajes');
        exit();
    }

    // 4. Obtener datos de la conversación
    $conversacion = $convModel->obtenerPorId($convId);
    if (!$conversacion) {
        mostrarSweetAlert('error', 'Error', 'No existe la conversación.', BASE_URL . '/cliente/mensajes');
        exit();
    }

    // 5. Obtener tema de la conversación
    $tema = $convModel->obtenerTema($convId);
    $otroUsuarioId = $convModel->obtenerOtroUsuarioId($conversacion, $uid);

    // 6. Obtener mensajes y marcar como leídos
    $mensajes = $msgModel->listarPorConversacion($convId, 80);
    $msgModel->marcarLeidos($convId, $uid);

    // 7. Cargar vista correspondiente al rol
    $vistaPerfil = obtenerVistaDeRol('chat');

    require $vistaPerfil;
}

function enviarMensaje()
{
    header('Content-Type: application/json; charset=utf-8');

    // 1. Validación de entrada
    $uid    = (int)$_SESSION['user']['id'];
    $convId = (int)($_POST['conversacion_id'] ?? 0);
    $texto  = trim($_POST['mensaje'] ?? '');

    if ($convId <= 0 || empty($texto)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
        return;
    }

    // 2. Instanciar modelo y validar acceso
    $convModel = new Conversacion();
    $msgModel  = new Mensaje();

    $conversacion = $convModel->obtenerPorId($convId);
    if (!$conversacion || !$convModel->usuarioTieneAcceso($convId, $uid)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
        return;
    }

    // 3. Obtener ID del receptor
    $receptorId = $convModel->obtenerOtroUsuarioId($conversacion, $uid);

    // 4. Crear mensaje en BD
    $msgId = $msgModel->crear($convId, $uid, $receptorId, $texto);

    // 5. Retornar respuesta
    echo json_encode(['ok' => true, 'id' => $msgId]);
}

function obtenerMensajesNuevos()
{
    header('Content-Type: application/json; charset=utf-8');

    // 1. Validación de parámetros
    $uid    = (int)$_SESSION['user']['id'];
    $convId = (int)($_GET['id'] ?? 0);
    $after  = $_GET['after'] ?? null;

    if ($convId <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Parámetros inválidos']);
        return;
    }

    // 2. Validar acceso del usuario
    $convModel = new Conversacion();
    if (!$convModel->usuarioTieneAcceso($convId, $uid)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
        return;
    }

    // 3. Obtener nuevos mensajes y marcar como leídos
    $msgModel = new Mensaje();
    $nuevos = $msgModel->listarNuevos($convId, $after);

    if (!empty($nuevos)) {
        $msgModel->marcarLeidos($convId, $uid);
    }

    // 4. Retornar respuesta
    echo json_encode(['ok' => true, 'mensajes' => $nuevos]);
}

// ======================================================================
// 4. FUNCIONES UTILITARIAS
// ======================================================================

function obtenerVistaDeRol($nombre)
{
    // Obtener rol del usuario autenticado
    $rol = $_SESSION['user']['rol'] ?? '';

    // Determinar ruta según rol
    if ($rol === 'cliente') {
        $ruta = BASE_PATH . "/app/views/dashboard/cliente/mensajes/{$nombre}.php";
    } elseif ($rol === 'proveedor') {
        $ruta = BASE_PATH . "/app/views/dashboard/proveedor/mensajes/{$nombre}.php";
    } else {
        mostrarSweetAlert('error', 'Rol no permitido', 'Tu rol no tiene acceso a esta sección.', BASE_URL . '/login');
        exit();
    }

    // Validar que la vista existe
    if (!file_exists($ruta)) {
        mostrarSweetAlert('error', 'Vista no encontrada', "La vista {$nombre}.php no existe para tu rol.", BASE_URL . '/login');
        exit();
    }

    return $ruta;
}
