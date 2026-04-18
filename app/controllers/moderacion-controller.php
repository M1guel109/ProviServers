<?php
// Dependencias
require_once __DIR__ . '/../helpers/alert-helper.php';
require_once __DIR__ . '/../models/moderacion.php';

$method = $_SERVER['REQUEST_METHOD'];

// =========================================================
// ROUTER
// =========================================================
if ($method === 'POST') {
    $accion = $_GET['accion'] ?? '';
    if ($accion === 'api_actualizar') {
        apiActualizarEstado();
    }
}

if ($method === 'GET') {
    $accion = $_GET['accion'] ?? '';
    if ($accion === 'api_detalle') {
        apiDetalleServicio();
    }
}

// =========================================================
// FUNCIONES DE LISTADO (para la vista)
// =========================================================

/**
 * Devuelve todos los servicios para la tabla de moderación.
 */
function mostrarServicios()
{
    $obj = new Moderacion();
    return $obj->listar();
}

// =========================================================
// FUNCIONES API — Retornan JSON
// =========================================================

function apiDetalleServicio()
{
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if (!$id) {
        echo json_encode(['error' => 'Falta el ID']);
        exit;
    }

    $modelo = new Moderacion();
    $datos  = $modelo->obtenerDetalle($id);

    if (!$datos) {
        echo json_encode(['error' => 'Servicio no encontrado']);
        exit;
    }

    echo json_encode([
        'id'                  => $datos['id'],
        'nombre'              => $datos['nombre'],
        'descripcion'         => $datos['descripcion'],
        'precio'              => $datos['precio'],
        'categoria'           => $datos['categoria_nombre'],
        'proveedor_nombre'    => $datos['proveedor_nombre'],
        'proveedor_tel'       => $datos['proveedor_telefono'] ?? 'N/A',
        'proveedor_email'     => $datos['proveedor_email'],
        'proveedor_ubicacion' => $datos['proveedor_ubicacion'] ?? 'No registrada',
        'foto'                => $datos['imagen'] ?? 'default_service.png',
        'estado'              => $datos['publicacion_estado'],
        'created_at'          => $datos['created_at']
    ]);
    exit;
}

function apiActualizarEstado()
{
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    $id     = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $estado = $_POST['estado'] ?? null;
    $motivo = trim($_POST['motivo'] ?? '');

    if (!$id || !$estado) {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
        exit;
    }

    $obj       = new Moderacion();
    $resultado = false;

    if ($estado === 'aprobado') {
        $resultado = $obj->aprobar($id);
    } elseif ($estado === 'rechazado') {
        if (empty($motivo)) {
            echo json_encode(['success' => false, 'error' => 'El motivo es obligatorio.']);
            exit;
        }
        $resultado = $obj->rechazar($id, $motivo);
    }

    echo json_encode(
        $resultado
            ? ['success' => true]
            : ['success' => false, 'error' => 'No se pudo actualizar la base de datos.']
    );
    exit;
}
