<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/moderacion.php';
require_once __DIR__ . '/../models/servicio.php';

// Detectamos método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// =========================================================
// 1. ROUTER (Manejo de peticiones)
// =========================================================

// A. Peticiones AJAX POST (Desde el JavaScript)
if ($method === 'POST') {
    // Verificamos si es una actualización de estado
    $accion = $_GET['accion'] ?? '';
    if ($accion === 'api_actualizar') {
        apiActualizarEstado();
    }
}

// B. Peticiones GET (Cargas de página o API de lectura)
if ($method === 'GET') {
    $accion = $_GET['accion'] ?? '';

    // API JSON: Ver detalle
    if ($accion === 'api_detalle') {
        apiDetalleServicio();
    }
    
    // // Acciones Legacy (por URL directa, si se usaran)
    // if ($accion === 'aprobar') {
    //     aprobarServicio($_GET['id']);
    // }
    // if ($accion === 'rechazar') {
    //     rechazarServicio($_GET['id'], $_GET['motivo'] ?? '');
    // }
}


/* =========================================================
    FUNCIONES API (RETORNAN JSON)
========================================================= */

function apiActualizarEstado() {
    // Limpiamos buffer y definimos cabecera JSON
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    $id = $_POST['id'] ?? null;
    $estado = $_POST['estado'] ?? null;
    $motivo = $_POST['motivo'] ?? '';

    if (!$id || !$estado) {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
        exit;
    }

    $obj = new Moderacion();
    $resultado = false;

    if ($estado === 'aprobado') {
        $resultado = $obj->aprobar($id);
    } elseif ($estado === 'rechazado') {
        if (empty(trim($motivo))) {
            echo json_encode(['success' => false, 'error' => 'El motivo es obligatorio para rechazar.']);
            exit;
        }
        $resultado = $obj->rechazar($id, $motivo);
    }

    if ($resultado === true) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se pudo actualizar la base de datos.']);
    }
    exit;
}

function apiDetalleServicio() {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');

    $id = $_GET['id'] ?? null;

    if (!$id) {
        echo json_encode(['error' => 'Falta el ID']);
        exit;
    }

    $modelo = new Servicio();
    $datos = $modelo->obtenerDetalleCompleto($id);

    if (empty($datos) || isset($datos['error'])) {
        echo json_encode(['error' => 'Servicio no encontrado']);
        exit;
    }

    // Mapeo seguro
    $respuesta = [
        'id'               => $datos['id'],
        'nombre'           => $datos['nombre'],
        'descripcion'      => $datos['descripcion'],
        'precio'           => $datos['precio'],
        'categoria'        => $datos['categoria_nombre'],
        'proveedor_nombre' => $datos['proveedor_nombre'],
        'proveedor_tel'    => $datos['proveedor_telefono'] ?? $datos['proveedor_tel'] ?? 'N/A', // Múltiples intentos
        'proveedor_email'  => $datos['proveedor_email'],
        'proveedor_ubicacion' => $datos['proveedor_ubicacion'] ?? 'No registrada',
        'foto'             => $datos['imagen'] ?? 'default_service.png',
        'estado'           => $datos['publicacion_estado'],
        'created_at'       => $datos['created_at']
    ];

    echo json_encode($respuesta);
    exit;
}

// /* =========================================================
//     FUNCIONES LEGACY (Por si acaso)
// ========================================================= */
// function aprobarServicio($id) { /* ... tu código anterior ... */ }
// function rechazarServicio($id, $motivo) { /* ... tu código anterior ... */ }
?>