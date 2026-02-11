<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/moderacion.php';
require_once __DIR__ . '/../models/servicio.php';

// Detectamos método HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        $accion = $_GET['accion'] ?? '';

        if ($accion === 'aprobar') {
            aprobarServicio($_GET['id']);
        }

        if ($accion === 'rechazar') {
            rechazarServicio($_GET['id'], $_GET['motivo'] ?? '');
        }

        // mostrarServiciosPendientes();
        break;

    default:
        http_response_code(405);
        echo "Método no permitido";
        break;
}



/* =========================================================
    FUNCIONES DEL CONTROLADOR
========================================================= */

// function mostrarServiciosPendientes()
// {
//     $obj = new Moderacion();
//     return $obj->obtenerPendientes(); // se usa en la vista
// }


function aprobarServicio($id)
{
    if (empty($id)) {
        mostrarSweetAlert('error', 'ID inválido', 'No se recibió un ID válido.');
        exit();
    }

    $obj = new Moderacion();

    $resultado = $obj->aprobar($id);

    if ($resultado === true) {
        mostrarSweetAlert(
            'success',
            'Servicio aprobado',
            'La publicación ahora está visible para los usuarios.',
            '/ProviServers/admin/consultar-servicios'
        );
    } else {
        mostrarSweetAlert(
            'error',
            'Error al aprobar',
            'No se pudo aprobar la publicación. Intenta nuevamente.'
        );
    }

    exit();
}



function rechazarServicio($id, $motivo)
{
    if (empty($id)) {
        mostrarSweetAlert('error', 'ID inválido', 'No se recibió un ID válido.');
        exit();
    }

    if (empty(trim($motivo))) {
        mostrarSweetAlert('warning', 'Motivo requerido', 'Debes ingresar el motivo del rechazo.');
        exit();
    }

    $obj = new Moderacion();

    $resultado = $obj->rechazar($id, $motivo);

    if ($resultado === true) {
        mostrarSweetAlert(
            'success',
            'Servicio rechazado',
            'El proveedor será notificado del motivo.',
            '/ProviServers/admin/consultar-servicios'
        );
    } else {
        mostrarSweetAlert(
            'error',
            'Error al rechazar',
            'No se pudo rechazar el servicio. Intenta nuevamente.'
        );
    }

    exit();
}

function obtenerDetalleServicio()
{
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo json_encode(['error' => 'ID faltante']);
        exit;
    }

    $objServicio = new Servicio();
    
    // USA LA NUEVA FUNCIÓN AQUÍ 👇
    $datos = $objServicio->obtenerDetalleCompleto($id); 

    if ($datos) {
        // Mapeo para asegurar que el JS reciba los nombres que espera
        $respuesta = [
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'],
            'precio' => $datos['precio'] ?? 0, // Asegura que precio exista si tu tabla lo tiene
            'categoria' => $datos['categoria_nombre'],
            'proveedor_nombre' => $datos['proveedor_nombre'],
            'foto' => $datos['imagen'], // Tu modelo usa 'imagen', el JS espera esto
            'estado' => $datos['publicacion_estado'] ?? 'pendiente'
        ];
        echo json_encode($respuesta);
    } else {
        echo json_encode(['error' => 'Servicio no encontrado']);
    }
    exit;
}
