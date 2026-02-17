<?php
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/Suscripcion.php';

// Capturamos el método
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Variable para acciones de botones normales (cancelar/eliminar)
    $accion = $_GET['accion'] ?? '';

    // 1. Lógica para acciones de botones
    if ($accion === 'cancelar') {
        cancelarSuscripcion($_GET['id'] ?? null);
    } elseif ($accion === 'eliminar') {
        eliminarSuscripcion($_GET['id'] ?? null);
    }

    // 2. Lógica para la API del Modal (AJAX)
    // Nota: El JS envía 'action=detalle', por eso verificamos $_GET['action']
    if (isset($_GET['action']) && $_GET['action'] == 'detalle') {
        obtenerDetalleJSON($_GET['id'] ?? null); // Usamos null safe operator
        exit; // ¡Importante! Detener aquí para que no se imprima nada más que el JSON
    }

    // Si no entra en ninguana anterior, el script sigue y la vista usa listarSuscripciones()
}

// ==========================================================
// FUNCIONES CRUD Y LÓGICA
// ==========================================================

function listarSuscripciones()
{
    $obj = new Suscripcion();
    return $obj->listarTodas();
}

function cancelarSuscripcion($id)
{
    if (!$id) {
        mostrarSweetAlert('error', 'Error', 'ID inválido.');
        exit;
    }

    $obj = new Suscripcion();

    if ($obj->cancelar($id)) {
        mostrarSweetAlert(
            'success',
            'Suscripción Cancelada',
            'El proveedor ya no tendrá acceso a los beneficios del plan.',
            '/ProviServers/admin/consultar-suscripciones'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo cancelar la suscripción.');
    }
    exit;
}

function eliminarSuscripcion($id)
{
    if (!$id) {
        mostrarSweetAlert('error', 'Error', 'ID inválido.');
        exit;
    }

    $obj = new Suscripcion();
    if ($obj->eliminar($id)) {
        mostrarSweetAlert('success', 'Eliminado', 'Registro eliminado correctamente.', '/ProviServers/admin/consultar-suscripciones');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar el registro.');
    }
    exit;
}

// ==========================================================
// FUNCIÓN API JSON (Para el Modal)
// ==========================================================

function obtenerDetalleJSON($id)
{
    // Validación básica
    if (!$id) {
        echo json_encode(['error' => 'ID no proporcionado']);
        return;
    }

    $obj = new Suscripcion();
    // Asegúrate de que esta función 'obtenerPorId' exista en tu modelo Suscripcion.php
    $dato = $obj->obtenerPorId($id); 

    if ($dato) {
        // Mapeamos los datos de la BD al formato JSON que espera el JS
        $response = [
            'id'               => $dato['id'],
            'estado'           => $dato['estado'], // 'activa', 'vencida', 'cancelada'
            'nombre_proveedor' => $dato['nombre_proveedor'],
            'email'            => $dato['email'],
            'telefono'         => $dato['telefono'] ?? 'N/A', // Prevenir nulos
            'ubicacion'        => $dato['ubicacion'] ?? 'N/A',
            'foto_proveedor'   => $dato['foto_proveedor'] ?? null,
            'nombre_plan'      => $dato['nombre_plan'],
            'costo'            => $dato['costo'],
            'fecha_inicio'     => date('d/m/Y', strtotime($dato['fecha_inicio'])),
            'fecha_fin'        => date('d/m/Y', strtotime($dato['fecha_fin'])),
            'fecha_fin_raw'    => $dato['fecha_fin'] // Formato YYYY-MM-DD para cálculo de días en JS
        ];
        
        // Headers correctos para JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Suscripción no encontrada']);
    }
}
?>