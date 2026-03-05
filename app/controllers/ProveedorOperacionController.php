<?php
// Importamos las dependencias necesarias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/Necesidad.php';
require_once __DIR__ . '/../models/Cotizacion.php';
require_once __DIR__ . '/../models/Solicitud.php';
require_once __DIR__ . '/../models/ServicioContratado.php';
require_once __DIR__ . '/../models/Publicacion.php';

// 1. VALIDACIÓN GLOBAL DE SESIÓN Y ROL
session_start();

// Este controlador maneja acciones tanto de Cliente (Crear Solicitud) como de Proveedor.
// Por lo tanto, validamos la sesión general primero.
if (!isset($_SESSION['user']['id'])) {
    mostrarSweetAlert('error', 'Acceso denegado', 'Debes iniciar sesión para realizar esta acción.', '/ProviServers/login');
    exit();
}

$rolActual = $_SESSION['user']['rol'] ?? '';

// Capturamos el método de la solicitud
$method = $_SERVER['REQUEST_METHOD'];

// 2. ENRUTADOR PRINCIPAL (Switch)
switch ($method) {
    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'enviar_cotizacion') {
            enviarCotizacion();
        } 
        elseif ($accion === 'actualizar_estado_servicio') {
            actualizarEstadoServicio();
        } 
        elseif ($accion === 'guardar_solicitud_cliente') {
            guardarSolicitud(); // Esto lo hace el cliente hacia el proveedor
        }
        else {
            http_response_code(400);
            echo "Acción POST no válida";
        }
        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';

        if ($accion === 'mostrar_oportunidades') {
            mostrarOportunidades();
        }
        elseif ($accion === 'aceptar_solicitud') {
            aceptarSolicitud($_GET['id'] ?? null);
        }
        elseif ($accion === 'rechazar_solicitud') {
            rechazarSolicitud($_GET['id'] ?? null);
        }
        elseif ($accion === 'mostrar_servicios_contratados') {
            mostrarServiciosContratadosProveedor();
        }
        else {
            http_response_code(400);
            echo "Acción GET no válida";
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

/* ---------------------------------------------------
   SECCIÓN 1: OPORTUNIDADES Y COTIZACIONES (PROVEEDOR)
--------------------------------------------------- */

function mostrarOportunidades()
{
    if ($_SESSION['user']['rol'] !== 'proveedor') {
        mostrarSweetAlert('error', 'Acceso denegado', 'Solo proveedores pueden ver oportunidades.', '/ProviServers/login');
        exit();
    }

    $modelo = new Necesidad();
    $usuarioId = (int)$_SESSION['user']['id'];

    $filtros = [
        'busqueda'  => $_GET['q'] ?? '',
        'ciudad'    => $_GET['ciudad'] ?? '',
        'categoria' => $_GET['categoria'] ?? ''
    ];

    $necesidades = $modelo->obtenerOportunidades($usuarioId, $filtros);

    // Cargar la vista
    require_once BASE_PATH . '/app/views/dashboard/proveedor/oportunidades.php';
    exit();
}

function enviarCotizacion()
{
    if ($_SESSION['user']['rol'] !== 'proveedor') {
        mostrarSweetAlert('error', 'Acceso denegado', 'Solo proveedores pueden enviar cotizaciones.', '/ProviServers/login');
        exit();
    }

    $usuarioId   = (int)$_SESSION['user']['id'];
    $necesidadId = (int)($_POST['necesidad_id'] ?? 0);
    
    // Mapeo de datos (Combinación de ambas lógicas que me enviaste)
    $titulo  = trim($_POST['titulo'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');
    $precio  = $_POST['precio_oferta'] ?? ($_POST['precio'] ?? null); // Compatibilidad de names
    $tiempo  = trim($_POST['tiempo_estimado'] ?? '');

    // Validación
    if ($necesidadId <= 0 || $titulo === '' || empty($precio)) {
        mostrarSweetAlert('error', 'Datos incompletos', 'Asegúrate de llenar el título y el precio.', '/ProviServers/proveedor/oportunidades');
        exit();
    }

    $datos = [
        'titulo'          => $titulo,
        'precio'          => $precio,
        'tiempo_estimado' => $tiempo,
        'mensaje'         => $mensaje
    ];

    $modelo = new Cotizacion();
    $exito = $modelo->crearParaNecesidadPorProveedorUsuario($usuarioId, $necesidadId, $datos);

    if ($exito) {
        mostrarSweetAlert('success', 'Oferta enviada', 'El cliente verá tu cotización.', '/ProviServers/proveedor/oportunidades');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo enviar. Es posible que ya hayas cotizado o la necesidad esté cerrada.', '/ProviServers/proveedor/oportunidades');
    }
    exit();
}

/* ---------------------------------------------------
   SECCIÓN 2: GESTIÓN DE SERVICIOS EN CURSO (PROVEEDOR)
--------------------------------------------------- */

function mostrarServiciosContratadosProveedor()
{
    if ($_SESSION['user']['rol'] !== 'proveedor') {
        mostrarSweetAlert('error', 'Acceso denegado', 'Solo proveedores.', '/ProviServers/login');
        exit();
    }

    $usuarioId = (int)$_SESSION['user']['id'];
    $modelo = new ServicioContratado();
    $servicios = $modelo->listarPorProveedorUsuario($usuarioId);

    // Enviar a la vista (Asegúrate de requerir la vista correcta aquí)
    require_once BASE_PATH . '/app/views/dashboard/proveedor/servicios.php'; // Ajusta esta ruta si es diferente
    exit();
}

function actualizarEstadoServicio()
{
    // Esta función devuelve JSON para llamadas AJAX
    header('Content-Type: application/json');

    if ($_SESSION['user']['rol'] !== 'proveedor') {
        http_response_code(403);
        echo json_encode(['ok' => false, 'msg' => 'No autorizado. Solo proveedores.']);
        exit();
    }

    if (!isset($_POST['contrato_id'], $_POST['estado'])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'msg' => 'Datos incompletos']);
        exit();
    }

    $contratoId  = (int) $_POST['contrato_id'];
    $nuevoEstado = trim($_POST['estado']);
    $usuarioId   = (int) $_SESSION['user']['id'];

    $modelo = new ServicioContratado();

    // Validar propiedad del contrato
    if (!$modelo->contratoPerteneceAProveedor($contratoId, $usuarioId)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
        exit();
    }

    $ok = $modelo->actualizarEstado($contratoId, $nuevoEstado);

    if (!$ok) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'msg' => 'Estado no válido o error al actualizar']);
        exit();
    }

    echo json_encode(['ok' => true, 'estado' => $nuevoEstado]);
    exit();
}

/* ---------------------------------------------------
   SECCIÓN 3: SOLICITUDES DIRECTAS (CLIENTE -> PROVEEDOR)
--------------------------------------------------- */

function guardarSolicitud()
{
    // Esto lo ejecuta el CLIENTE
    if ($_SESSION['user']['rol'] !== 'cliente') {
        mostrarSweetAlert('error', 'Acceso denegado', 'Solo clientes pueden solicitar servicios.', '/ProviServers/login');
        exit();
    }

    $clienteId     = (int) $_SESSION['user']['id'];
    $publicacionId = (int) ($_POST['publicacion_id'] ?? 0);
    $titulo        = trim($_POST['titulo'] ?? '');
    $descripcion   = trim($_POST['descripcion'] ?? '');
    $direccion     = trim($_POST['direccion'] ?? '');
    $ciudad        = trim($_POST['ciudad'] ?? '');
    $zona          = trim($_POST['zona'] ?? '');
    $fecha         = trim($_POST['fecha_preferida'] ?? '');
    $franja        = trim($_POST['franja_horaria'] ?? '');
    


    if (!$publicacionId || !$titulo || !$descripcion || !$direccion || !$ciudad || !$fecha) {
        mostrarSweetAlert('error', 'Campos incompletos', 'Completa los campos obligatorios.');
        exit();
    }

    $pubModel = new Publicacion();
    $publicacion = $pubModel->obtenerPublicaActivaPorId($publicacionId);

    if (!$publicacion) {
        mostrarSweetAlert('error', 'Error', 'La publicación no existe o no está activa.');
        exit();
    }

    $proveedorId = (int) $publicacion['proveedor_id'];

    $solicitudModel = new Solicitud();
    if ($solicitudModel->tieneSolicitudActiva($clienteId, $publicacionId)) {
        mostrarSweetAlert('warning', 'Solicitud ya enviada', 'Ya tienes una solicitud activa para este servicio.');
        exit();
    }

    // Procesar adjuntos
    $adjuntos_guardados = [];
    if (!empty($_FILES['adjuntos']) && !empty($_FILES['adjuntos']['name'][0])) {
        $ruta_base = BASE_PATH . '/public/uploads/solicitudes/';
        if (!is_dir($ruta_base)) mkdir($ruta_base, 0755, true);

        $permitidas = ['pdf', 'png', 'jpg', 'jpeg'];
        $max_size   = 5 * 1024 * 1024; // 5MB

        foreach ($_FILES['adjuntos']['name'] as $i => $nombre_original) {
            if ($_FILES['adjuntos']['error'][$i] !== UPLOAD_ERR_OK) continue;

            $ext  = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
            $size = $_FILES['adjuntos']['size'][$i];
            $tipo = $_FILES['adjuntos']['type'][$i];
            $tmp  = $_FILES['adjuntos']['tmp_name'][$i];

            if (!in_array($ext, $permitidas)) {
                mostrarSweetAlert('error', 'Archivo no permitido', "El archivo {$nombre_original} no es válido.");
                exit();
            }

            if ($size > $max_size) {
                mostrarSweetAlert('error', 'Archivo muy grande', "El archivo {$nombre_original} supera 5MB.");
                exit();
            }

            $nombre_final = uniqid('sol_') . '.' . $ext;
            $destino = $ruta_base . $nombre_final;

            if (!move_uploaded_file($tmp, $destino)) {
                mostrarSweetAlert('error', 'Error al subir archivo', "No se pudo guardar {$nombre_original}.");
                exit();
            }

            $adjuntos_guardados[] = [
                'archivo'      => $nombre_final,
                'tipo_archivo' => $tipo,
                'tamano'       => $size
            ];
        }
    }

    $data = [
        'cliente_id'           => $clienteId,
        'proveedor_id'         => $proveedorId,
        'publicacion_id'       => $publicacionId,
        'titulo'               => $titulo,
        'descripcion'          => $descripcion,
        'direccion'            => $direccion,
        'ciudad'               => $ciudad,
        'zona'                 => $zona,
        'fecha_servicio'       => $fecha,
        'franja_horaria'       => $franja,
        'adjuntos'             => $adjuntos_guardados
    ];

    try {
        $resultado = $solicitudModel->crear($data);

        if ($resultado === true) {
            mostrarSweetAlert('success', 'Solicitud enviada', 'El proveedor recibirá tu solicitud.', '/ProviServers/cliente/explorar-servicios');
        } else {
            mostrarSweetAlert('error', 'Error', 'No se pudo enviar la solicitud.');
        }
    } catch (Throwable $e) {
        mostrarSweetAlert('error', 'Error técnico', 'Mensaje: ' . $e->getMessage());
    }
    exit();
}

function aceptarSolicitud($id)
{
    if ($_SESSION['user']['rol'] !== 'proveedor') {
        mostrarSweetAlert('error', 'Acceso denegado', 'Solo proveedores.', '/ProviServers/login');
        exit();
    }

    if (!$id) {
        mostrarSweetAlert('error', 'Error', 'Solicitud inválida');
        exit();
    }

    $proveedorUsuarioId = (int) $_SESSION['user']['id'];
    $modelo = new Solicitud();

    try {
        $resultado = $modelo->aceptar((int)$id, $proveedorUsuarioId);

        if ($resultado) {
            mostrarSweetAlert('success', 'Solicitud aceptada', 'El servicio se marcó como en proceso.', '/ProviServers/proveedor/nuevas_solicitudes');
        } else {
            mostrarSweetAlert('error', 'Error', 'No se pudo aceptar la solicitud.');
        }
    } catch (Throwable $e) {
        mostrarSweetAlert('error', 'Error técnico', 'Mensaje: ' . $e->getMessage());
    }
    exit();
}

function rechazarSolicitud($id)
{
    if ($_SESSION['user']['rol'] !== 'proveedor') {
        mostrarSweetAlert('error', 'Acceso denegado', 'Solo proveedores.', '/ProviServers/login');
        exit();
    }

    if (!$id) {
        mostrarSweetAlert('error', 'Error', 'Solicitud inválida');
        exit();
    }

    $proveedorUsuarioId = (int) $_SESSION['user']['id'];
    $modelo = new Solicitud();

    try {
        $resultado = $modelo->rechazar((int)$id, $proveedorUsuarioId);

        if ($resultado) {
            mostrarSweetAlert('success', 'Solicitud rechazada', 'La solicitud fue rechazada.', '/ProviServers/proveedor/solicitudes');
        } else {
            mostrarSweetAlert('error', 'Error', 'No se pudo rechazar la solicitud.');
        }
    } catch (Throwable $e) {
        mostrarSweetAlert('error', 'Error técnico', 'Mensaje: ' . $e->getMessage());
    }
    exit();
}