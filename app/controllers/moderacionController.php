<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/moderacion.php';

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
