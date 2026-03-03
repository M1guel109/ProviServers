<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/ServicioContratado.php';
require_once __DIR__ . '/../models/Valoracion.php';
require_once __DIR__ . '/../models/Solicitud.php';

// 1. VALIDACIÓN GLOBAL DE SESIÓN Y ROL
session_start();

if (!isset($_SESSION['user']['id']) || ($_SESSION['user']['rol'] ?? '') !== 'cliente') {
    mostrarSweetAlert('error', 'Acceso denegado', 'Solo clientes pueden acceder a esta sección.', '/ProviServers/login');
    exit();
}

// Capturamos el método de la solicitud
$method = $_SERVER['REQUEST_METHOD'];

// 2. ENRUTADOR PRINCIPAL (Switch)
switch ($method) {
    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'calificar_servicio') {
            calificarServicio();
        } 
        elseif ($accion === 'cancelar_servicio') {
            cancelarServicio();
        } 
        else {
            http_response_code(400);
            echo "Acción POST no válida";
        }
        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';
        
        if ($accion === 'ver_solicitudes') {
            mostrarSolicitudes();
        } 
        else {
            // Por defecto, si no hay acción o es 'ver_servicios', mostramos los contratados
            mostrarServiciosContratados();
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

function mostrarServiciosContratados()
{
    $usuarioId = (int)$_SESSION['user']['id'];

    $serviciosEnCurso      = [];
    $serviciosProgramados  = [];
    $serviciosCompletados  = [];
    $serviciosCancelados   = [];

    if ($usuarioId > 0) {
        $modelo    = new ServicioContratado();
        $contratos = $modelo->listarPorClienteUsuario($usuarioId) ?: [];

        foreach ($contratos as $c) {
            $estado = $c['estado'] ?? 'pendiente';

            switch ($estado) {
                case 'finalizado':
                    $serviciosCompletados[] = $c;
                    break;

                case 'cancelado_cliente':
                case 'cancelado_proveedor':
                case 'cancelado': 
                    $serviciosCancelados[] = $c;
                    break;

                case 'pendiente':
                case 'confirmado':
                    $serviciosProgramados[] = $c;
                    break;

                case 'en_proceso':
                default:
                    $serviciosEnCurso[] = $c;
                    break;
            }
        }
    }

    // Finalmente cargamos la vista
    require BASE_PATH . '/app/views/dashboard/cliente/serviciosContratados.php';
    exit();
}

function calificarServicio()
{
    $usuarioId    = (int)$_SESSION['user']['id'];
    $contratoId   = (int)($_POST['contrato_id'] ?? 0);
    $calificacion = (int)($_POST['calificacion'] ?? 0);
    $comentario   = isset($_POST['comentario']) ? trim((string)$_POST['comentario']) : null;

    if ($usuarioId <= 0 || $contratoId <= 0) {
        mostrarSweetAlert('error', 'Solicitud inválida', 'No se enviaron los datos correctos.', '/ProviServers/cliente/servicios-contratados');
        exit();
    }

    $modelo = new Valoracion();
    $ok = $modelo->crearPorClienteUsuario($contratoId, $usuarioId, $calificacion, $comentario);

    if ($ok) {
        mostrarSweetAlert('success', '¡Gracias!', 'Tu calificación fue registrada exitosamente.', '/ProviServers/cliente/servicios-contratados');
    } else {
        mostrarSweetAlert('error', 'Error al calificar', 'No se pudo calificar. Verifica que el servicio esté finalizado y no lo hayas calificado antes.', '/ProviServers/cliente/servicios-contratados');
    }
    exit();
}

function cancelarServicio()
{
    $usuarioId  = (int)$_SESSION['user']['id'];
    $contratoId = (int)($_POST['contrato_id'] ?? 0);

    if ($contratoId <= 0) {
        mostrarSweetAlert('error', 'Solicitud inválida', 'ID de contrato no válido.', '/ProviServers/cliente/servicios-contratados');
        exit();
    }

    $modelo = new ServicioContratado();

    // El método ya valida que pertenezca al usuario y esté en estado correcto
    $ok = $modelo->cancelarPorClienteUsuario($contratoId, $usuarioId);

    if ($ok) {
        mostrarSweetAlert('success', 'Servicio cancelado', 'El servicio ha sido cancelado correctamente.', '/ProviServers/cliente/servicios-contratados');
    } else {
        mostrarSweetAlert('error', 'Error al cancelar', 'No se pudo cancelar. Es posible que el servicio ya haya cambiado de estado.', '/ProviServers/cliente/servicios-contratados');
    }
    exit();
}

function mostrarSolicitudes()
{
    $usuarioId = (int)$_SESSION['user']['id'];

    $estado = $_GET['estado'] ?? 'pendiente';
    $estadosValidos = ['pendiente', 'aceptada', 'rechazada', 'cancelada'];
    if (!in_array($estado, $estadosValidos, true)) {
        $estado = 'pendiente';
    }

    $solicitudId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    $model = new Solicitud();

    $contadores = $model->contarPorEstadoClienteUsuario($usuarioId);
    $solicitudes = $model->listarPorClienteUsuarioYEstado($usuarioId, $estado);

    $detalle = [];
    if ($solicitudId > 0) {
        $detalle = $model->obtenerDetallePorClienteUsuario($usuarioId, $solicitudId);
    }

    // Cargar vista
    require_once BASE_PATH . '/app/views/dashboard/cliente/misSolicitudes.php';
    exit();
}