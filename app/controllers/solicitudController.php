<?php

require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/Solicitud.php';
require_once __DIR__ . '/../models/Publicacion.php';

session_start();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        guardarSolicitud();
        break;
    default:
        http_response_code(405);
        echo "Método no permitido";
        break;
}

/* ======================================================
   GUARDAR SOLICITUD (PRE-CONTRATO)
   ====================================================== */

function guardarSolicitud()
{
    // 🔐 Validar sesión
    if (!isset($_SESSION['user']['id'])) {
        mostrarSweetAlert('error', 'Acceso denegado', 'Debes iniciar sesión para solicitar un servicio');
        exit();
    }

    // 📥 Datos
    $clienteId     = (int) $_SESSION['user']['id'];
    $publicacionId = (int) ($_POST['publicacion_id'] ?? 0);
    $titulo        = trim($_POST['titulo'] ?? '');
    $descripcion   = trim($_POST['descripcion'] ?? '');
    $direccion     = trim($_POST['direccion'] ?? '');
    $ciudad        = trim($_POST['ciudad'] ?? '');
    $zona          = trim($_POST['zona'] ?? '');
    $fecha         = trim($_POST['fecha_preferida'] ?? '');
    $franja        = trim($_POST['franja_horaria'] ?? '');
    $presupuesto   = $_POST['presupuesto'] ?? null;

    // echo '<pre>';
    // var_dump($_POST);
    // exit;


    // 🧪 Validaciones
    if (!$publicacionId || !$titulo || !$descripcion || !$direccion || !$ciudad || !$fecha) {
        mostrarSweetAlert('error', 'Campos incompletos', 'Completa los campos obligatorios');
        exit();
    }

    // 🔎 Obtener proveedor desde publicación
    $pubModel = new Publicacion();
    $publicacion = $pubModel->obtenerPublicaActivaPorId($publicacionId);

    if (!$publicacion) {
        mostrarSweetAlert('error', 'Error', 'La publicación no existe');
        exit();
    }

    $proveedorId = (int) $publicacion['proveedor_id'];

    // 📦 Data principal
    $data = [
        'cliente_id'     => $clienteId,
        'proveedor_id'   => $proveedorId,
        'publicacion_id' => $publicacionId,
        'titulo'         => $titulo,
        'descripcion'    => $descripcion,
        'direccion'      => $direccion,
        'ciudad'         => $ciudad,
        'zona'           => $zona,
        'fecha_preferida' => $fecha,
        'franja_horaria' => $franja,
        'presupuesto_estimado'    => $presupuesto,
        'estado'         => 'pendiente'
    ];

    // 📎 Adjuntos
    $adjuntos = $_FILES['adjuntos'] ?? null;

    // 🧠 Guardar
    $solicitud = new Solicitud();
    $resultado = $solicitud->crear($data);

    // var_dump([
    //     'cliente_id'   => $clienteId,
    //     'proveedor_id' => $proveedorId,
    //     'publicacion'  => $publicacion
    // ]);
    // exit;

    $solicitud = new Solicitud();

    if ($solicitud->tieneSolicitudActiva($clienteId, $publicacionId)) {
        mostrarSweetAlert(
            'warning',
            'Solicitud ya enviada',
            'Ya tienes una solicitud activa para este servicio'
        );
        exit;
    }



    if ($resultado === true) {
        mostrarSweetAlert(
            'success',
            'Solicitud enviada',
            'El proveedor recibirá tu solicitud.',
            '/ProviServers/cliente/explorar-servicios'
        );
    } else {
        mostrarSweetAlert(
            'error',
            'Error',
            'No se pudo enviar la solicitud'
        );
    }

    exit();
}
