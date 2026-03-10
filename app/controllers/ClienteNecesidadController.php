<?php
// Importamos las dependencias necesarias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/Necesidad.php';
require_once __DIR__ . '/../models/Cotizacion.php';

// 1. VALIDACIÓN GLOBAL DE SESIÓN Y ROL
// Como todo en este controlador es para el cliente, validamos desde el principio.
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

        if ($accion === 'crear_necesidad') {
            crearNecesidad();
        } elseif ($accion === 'aceptar_cotizacion') {
            aceptarCotizacion();
        } else {
            http_response_code(400);
            echo "Acción POST no válida";
        }
        break;

    case 'GET':
        // Si hay una acción específica por GET, la manejamos, sino mostramos la vista principal
        $accion = $_GET['accion'] ?? '';

        // Por defecto, siempre cargaremos la vista de necesidades si es GET
        mostrarNecesidades();
        break;

    default:
        http_response_code(405);
        echo "Método no permitido";
        break;
}

// ======================================================================
// 3. FUNCIONES DEL CONTROLADOR
// ======================================================================

function crearNecesidad()
{
    if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'cliente') {
        mostrarSweetAlert(
            'error',
            'Acceso denegado',
            'Solo clientes pueden publicar necesidades.',
            '/ProviServers/login'
        );
        exit();
    }

    $usuarioId = (int)$_SESSION['user']['id'];

    // Datos del formulario
    $categoria      = trim($_POST['categoria'] ?? '');
    $categoriaOtro  = trim($_POST['categoria_otro'] ?? '');
    $titulo         = trim($_POST['titulo'] ?? '');
    $descripcion    = trim($_POST['descripcion'] ?? '');
    $direccion      = trim($_POST['direccion'] ?? '');
    $ciudad         = trim($_POST['ciudad'] ?? '');
    $zona           = trim($_POST['zona'] ?? '');
    $fecha          = trim($_POST['fecha_preferida'] ?? '');
    $franja         = trim($_POST['franja_horaria'] ?? '');
    $hora           = trim($_POST['hora_preferida'] ?? '');
    $presupuesto    = $_POST['presupuesto_estimado'] ?? null;

    // Validación de obligatorios
    if (
        $categoria === '' ||
        $titulo === '' ||
        $descripcion === '' ||
        $direccion === '' ||
        $ciudad === '' ||
        $fecha === '' ||
        $franja === ''
    ) {
        mostrarSweetAlert(
            'error',
            'Campos incompletos',
            'Completa todos los campos obligatorios.',
            '/ProviServers/cliente/necesidades'
        );
        exit();
    }

    // Si selecciona "Otros", debe escribir la categoría
    if ($categoria === 'Otros' && $categoriaOtro === '') {
        mostrarSweetAlert(
            'error',
            'Categoría incompleta',
            'Debes especificar la categoría.',
            '/ProviServers/cliente/necesidades'
        );
        exit();
    }

    // Validar franja
    $franjasValidas = ['mañana', 'tarde', 'noche'];
    if (!in_array($franja, $franjasValidas, true)) {
        mostrarSweetAlert(
            'error',
            'Franja inválida',
            'Selecciona una franja horaria válida.',
            '/ProviServers/cliente/necesidades'
        );
        exit();
    }

    // Normalizar hora
    if ($hora === '') {
        switch ($franja) {
            case 'mañana':
                $hora = '09:00:00';
                break;
            case 'tarde':
                $hora = '15:00:00';
                break;
            case 'noche':
                $hora = '19:00:00';
                break;
        }
    } else {
        if (preg_match('/^\d{2}:\d{2}$/', $hora)) {
            $hora .= ':00';
        }

        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $hora)) {
            mostrarSweetAlert(
                'error',
                'Hora inválida',
                'Ingresa una hora válida.',
                '/ProviServers/cliente/necesidades'
            );
            exit();
        }
    }

    // Normalizar presupuesto
    if ($presupuesto === '' || $presupuesto === null) {
        $presupuesto = null;
    } else {
        $presupuesto = (float)$presupuesto;
        if ($presupuesto < 0) {
            $presupuesto = 0;
        }
    }

    // Insertar necesidad
    $model = new Necesidad();

    $ok = $model->crearParaClienteUsuario($usuarioId, [
        'servicio_id'          => null,
        'titulo'               => $titulo,
        'descripcion'          => $descripcion,
        'direccion'            => $direccion,
        'ciudad'               => $ciudad,
        'zona'                 => ($zona !== '' ? $zona : null),
        'fecha_preferida'      => $fecha,
        'franja_horaria'       => $franja,
        'hora_preferida'       => $hora,
        'presupuesto_estimado' => $presupuesto,
    ]);

    if ($ok) {
        mostrarSweetAlert(
            'success',
            'Necesidad publicada',
            'Los proveedores podrán enviarte ofertas.',
            '/ProviServers/cliente/necesidades'
        );
    } else {
        mostrarSweetAlert(
            'error',
            'Error',
            'No se pudo publicar la necesidad. Intenta nuevamente.',
            '/ProviServers/cliente/necesidades'
        );
    }

    exit();
}
function aceptarCotizacion()
{
    $usuarioId = (int)$_SESSION['user']['id'];
    $cotizacionId = (int)($_POST['cotizacion_id'] ?? 0);

    if ($cotizacionId <= 0) {
        mostrarSweetAlert('error', 'Error', 'Cotización inválida.');
        exit();
    }

    // Instanciamos el modelo robusto de Cotización
    $model = new Cotizacion();

    // Llamamos al método robusto que incluye el INSERT en servicios_contratados
    $ok = $model->aceptarCotizacionParaClienteUsuario($usuarioId, $cotizacionId);

    // Respuesta
    if ($ok) {
        mostrarSweetAlert('success', '¡Trato cerrado!', 'Has contratado el servicio correctamente.', '/ProviServers/cliente/servicios-contratados');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo procesar la contratación. Intenta nuevamente.');
    }
    exit();
}

function mostrarNecesidades()
{
    $usuarioId = (int)$_SESSION['user']['id'];
    $model = new Necesidad();

    // Variables para la vista
    $estado = $_GET['estado'] ?? null; // abierta | cerrada | cancelada
    $misNecesidades = $model->listarPorClienteUsuario($usuarioId, $estado);
    $detalle = null;
    $cotizaciones = [];

    // Si se solicita ver el detalle de una necesidad específica
    $nid = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($nid > 0) {
        $detalle = $model->obtenerDetallePorClienteUsuario($usuarioId, $nid);

        if ($detalle) {
            $cotizaciones = $model->listarCotizacionesDeNecesidadParaCliente($usuarioId, $nid);
        }
    }

    // Cargar la vista pasándole las variables
    require BASE_PATH . '/app/views/dashboard/cliente/necesidades.php';
    exit();
}
