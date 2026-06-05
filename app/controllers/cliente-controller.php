﻿﻿<?php

require_once __DIR__ . '/../helpers/alert-helper.php';
require_once __DIR__ . '/../models/publicacion.php';
require_once __DIR__ . '/../models/servicio-contratado.php';
require_once __DIR__ . '/../models/valoracion.php';
require_once __DIR__ . '/../models/solicitud.php';
require_once __DIR__ . '/../models/necesidad.php';
require_once __DIR__ . '/../models/cotizacion.php';

// ===================================================================
// GUARD DE SESIÓN Y ROL
// ===================================================================

if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) session_start();
}

if (!isset($_SESSION['user']['id']) || ($_SESSION['user']['rol'] ?? '') !== 'cliente') {
    mostrarSweetAlert('error', 'Acceso denegado', 'Solo clientes pueden acceder a esta sección.', BASE_URL . '/login');
    exit();
}

// ===================================================================
// ROUTER INTERNO — Dispatch por método HTTP y URI
// ===================================================================

$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

switch ($method) {

    case 'GET':
        if (str_contains($uri, 'contrato-pdf')) {
            generarComprobantePDFCliente();
        } elseif (str_contains($uri, 'explorar')) {
            mostrarCatalogoPublico();
        } elseif (str_contains($uri, '/cliente/publicacion')) {
            mostrarDetallePublicacion();
        } elseif (str_contains($uri, 'mis-solicitudes') || ($_GET['accion'] ?? '') === 'ver_solicitudes') {
            mostrarSolicitudes();
        } elseif (str_contains($uri, 'servicios-contratados')) {
            mostrarServiciosContratados();
        } elseif (str_contains($uri, 'necesidades')) {
            mostrarNecesidades();
        }
        // Sin error para GET no reconocido — index.php puede llamar funciones explícitas
        break;

    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'calificar_servicio') {
            calificarServicio();
        } elseif ($accion === 'cancelar_servicio') {
            cancelarServicio();
        } elseif ($accion === 'guardar_solicitud_cliente' || str_contains($uri, 'guardar-solicitud')) {
            guardarSolicitud();
        } elseif ($accion === 'crear_necesidad') {
            crearNecesidad();
        } elseif ($accion === 'aceptar_cotizacion') {
            aceptarCotizacion();
        } else {
            http_response_code(400);
            mostrarSweetAlert('error', 'Acción no válida', 'La acción POST solicitada no existe.');
            exit();
        }
        break;

    default:
        http_response_code(405);
        mostrarSweetAlert('error', 'Método no permitido', 'Esta ruta no acepta ese tipo de petición.');
        exit();
}

// ===================================================================
// FUNCIONES — CATÁLOGO Y PUBLICACIONES
// ===================================================================

function mostrarCatalogoPublico()
{
    $busqueda    = trim($_GET['q']      ?? '');
    $categoriaId = isset($_GET['cat']) && $_GET['cat'] !== '' ? (int)$_GET['cat'] : null;
    $ciudad      = trim($_GET['ciudad'] ?? '');
    $precioMax   = isset($_GET['precio_max']) && $_GET['precio_max'] !== '' ? (float)$_GET['precio_max'] : null;
    $orden       = in_array($_GET['orden'] ?? '', ['precio_asc','precio_desc','valorados','recientes'], true)
                   ? $_GET['orden'] : 'recientes';

    $catActual = $categoriaId ?? '';

    $modelo        = new Publicacion();
    $publicaciones = $modelo->listarPublicasActivas(
        $busqueda ?: null,
        $categoriaId,
        $ciudad ?: null,
        $precioMax,
        $orden
    );

    require BASE_PATH . '/app/views/dashboard/cliente/explorar-servicios.php';
    exit();
}

function mostrarDetallePublicacion()
{
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id <= 0) {
        mostrarSweetAlert('error', 'Error', 'Identificador de publicación no válido.', BASE_URL . '/cliente/explorar-servicios');
        exit();
    }

    $modelo      = new Publicacion();
    $publicacion = $modelo->obtenerDetallePublicacion($id);

    if (!$publicacion) {
        mostrarSweetAlert('error', 'No encontrada', 'La publicación no existe o ya no está disponible.', BASE_URL . '/cliente/explorar-servicios');
        exit();
    }

    require BASE_PATH . '/app/views/dashboard/cliente/detalle-publicacion.php';
    exit();
}

// ===================================================================
// FUNCIONES — SERVICIOS CONTRATADOS
// ===================================================================

function mostrarServiciosContratados()
{
    $usuarioId = (int)$_SESSION['user']['id'];

    $serviciosEnCurso     = [];
    $serviciosProgramados = [];
    $serviciosCompletados = [];
    $serviciosCancelados  = [];

    $modelo    = new ServicioContratado();
    $contratos = $modelo->listarPorClienteUsuario($usuarioId) ?: [];

    // Marcar cuáles ya tienen pago registrado (tabla puede no existir aún)
    $pagadosIds = [];
    try {
        $dbPag = new Conexion();
        $pdoPag = $dbPag->getConexion();
        $stPag = $pdoPag->query("SELECT servicio_contratado_id FROM pagos_servicios");
        $pagadosIds = array_column($stPag->fetchAll(PDO::FETCH_ASSOC), 'servicio_contratado_id');
    } catch (PDOException $e) { /* tabla aún no existe */ }

    foreach ($contratos as &$c) {
        $c['ya_pagado'] = in_array((int)$c['contrato_id'], $pagadosIds, true) ? 1 : 0;
    }
    unset($c);

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
            default:
                $serviciosEnCurso[] = $c;
                break;
        }
    }

    require BASE_PATH . '/app/views/dashboard/cliente/servicios-contratados.php';
    exit();
}

function calificarServicio()
{
    $usuarioId    = (int)$_SESSION['user']['id'];
    $contratoId   = (int)($_POST['contrato_id']  ?? 0);
    $calificacion = (int)($_POST['calificacion'] ?? 0);
    $comentario   = isset($_POST['comentario']) ? trim((string)$_POST['comentario']) : null;

    if ($contratoId <= 0) {
        mostrarSweetAlert('error', 'Solicitud inválida', 'No se enviaron los datos correctos.', BASE_URL . '/cliente/servicios-contratados');
        exit();
    }

    $modelo = new Valoracion();
    $ok     = $modelo->crearPorClienteUsuario($contratoId, $usuarioId, $calificacion, $comentario);

    if ($ok) {
        mostrarSweetAlert('success', '¡Gracias!', 'Tu calificación fue registrada exitosamente.', BASE_URL . '/cliente/servicios-contratados');
    } else {
        mostrarSweetAlert('error', 'Error al calificar', 'No se pudo calificar. Verifica que el servicio esté finalizado y no lo hayas calificado antes.', BASE_URL . '/cliente/servicios-contratados');
    }
    exit();
}

function cancelarServicio()
{
    $usuarioId  = (int)$_SESSION['user']['id'];
    $contratoId = (int)($_POST['contrato_id'] ?? 0);

    if ($contratoId <= 0) {
        mostrarSweetAlert('error', 'Solicitud inválida', 'ID de contrato no válido.', BASE_URL . '/cliente/servicios-contratados');
        exit();
    }

    $modelo = new ServicioContratado();
    $ok     = $modelo->cancelarPorClienteUsuario($contratoId, $usuarioId);

    if ($ok) {
        mostrarSweetAlert('success', 'Servicio cancelado', 'El servicio ha sido cancelado correctamente.', BASE_URL . '/cliente/servicios-contratados');
    } else {
        mostrarSweetAlert('error', 'Error al cancelar', 'No se pudo cancelar. Es posible que el servicio ya haya cambiado de estado.', BASE_URL . '/cliente/servicios-contratados');
    }
    exit();
}

// ===================================================================
// FUNCIONES — SOLICITUDES DIRECTAS
// ===================================================================

function mostrarSolicitudes()
{
    $usuarioId = (int)$_SESSION['user']['id'];
    $estado    = $_GET['estado'] ?? 'pendiente';

    $estadosValidos = ['pendiente', 'aceptada', 'rechazada', 'cancelada'];
    if (!in_array($estado, $estadosValidos, true)) {
        $estado = 'pendiente';
    }

    $solicitudId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $modelo      = new Solicitud();
    $contadores  = $modelo->contarPorEstadoClienteUsuario($usuarioId);
    $solicitudes = $modelo->listarPorClienteUsuarioYEstado($usuarioId, $estado);

    $detalle = [];
    if ($solicitudId > 0) {
        $detalle = $modelo->obtenerDetallePorClienteUsuario($usuarioId, $solicitudId);
    }

    require_once BASE_PATH . '/app/views/dashboard/cliente/mis-solicitudes.php';
    exit();
}

function guardarSolicitud()
{
    $clienteId     = (int)$_SESSION['user']['id'];
    $publicacionId = (int)($_POST['publicacion_id'] ?? 0);
    $descripcion   = trim($_POST['descripcion']    ?? '');
    $direccion     = trim($_POST['direccion']       ?? '');
    $ciudad        = trim($_POST['ciudad']          ?? '');
    $zona          = trim($_POST['zona']            ?? '');
    $fecha         = trim($_POST['fecha_preferida'] ?? '');
    $franja        = trim($_POST['franja_horaria']  ?? '');

    if (!$publicacionId || !$descripcion || !$direccion || !$ciudad || !$fecha) {
        mostrarSweetAlert('error', 'Campos incompletos', 'Completa los campos obligatorios.');
        exit();
    }

    $pubModel    = new Publicacion();
    $publicacion = $pubModel->obtenerDetallePublicacion($publicacionId);

    if (!$publicacion) {
        mostrarSweetAlert('error', 'Error', 'La publicación no existe o no está activa.');
        exit();
    }

    $titulo      = $publicacion['titulo'] ?? $publicacion['servicio_nombre'] ?? 'Solicitud de servicio';
    $proveedorId = (int)$publicacion['proveedor_id'];

    $solicitudModel = new Solicitud();
    if ($solicitudModel->tieneSolicitudActiva($clienteId, $publicacionId)) {
        mostrarSweetAlert('warning', 'Solicitud ya enviada', 'Ya tienes una solicitud activa para este servicio.');
        exit();
    }

    $adjuntos_guardados = [];
    if (!empty($_FILES['adjuntos']['name'][0])) {
        $ruta_base = BASE_PATH . '/public/uploads/solicitudes/';
        if (!is_dir($ruta_base)) mkdir($ruta_base, 0755, true);

        $permitidas = ['pdf', 'png', 'jpg', 'jpeg'];
        $max_size   = 5 * 1024 * 1024;

        foreach ($_FILES['adjuntos']['name'] as $i => $nombre_original) {
            if ($_FILES['adjuntos']['error'][$i] !== UPLOAD_ERR_OK) continue;

            $ext  = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
            $size = $_FILES['adjuntos']['size'][$i];
            $tipo = $_FILES['adjuntos']['type'][$i];
            $tmp  = $_FILES['adjuntos']['tmp_name'][$i];

            if (!in_array($ext, $permitidas, true)) {
                mostrarSweetAlert('error', 'Archivo no permitido', "El archivo {$nombre_original} no es válido.");
                exit();
            }
            if ($size > $max_size) {
                mostrarSweetAlert('error', 'Archivo muy grande', "El archivo {$nombre_original} supera 5MB.");
                exit();
            }
            $nombre_final = uniqid('sol_') . '.' . $ext;
            if (!move_uploaded_file($tmp, $ruta_base . $nombre_final)) {
                mostrarSweetAlert('error', 'Error al subir archivo', "No se pudo guardar {$nombre_original}.");
                exit();
            }
            $adjuntos_guardados[] = ['archivo' => $nombre_final, 'tipo_archivo' => $tipo, 'tamano' => $size];
        }
    }

    $resultado = $solicitudModel->crear([
        'cliente_id'     => $clienteId,
        'proveedor_id'   => $proveedorId,
        'publicacion_id' => $publicacionId,
        'titulo'         => $titulo,
        'descripcion'    => $descripcion,
        'direccion'      => $direccion,
        'ciudad'         => $ciudad,
        'zona'           => $zona,
        'fecha_servicio' => $fecha,
        'franja_horaria' => $franja,
        'adjuntos'       => $adjuntos_guardados,
    ]);

    if ($resultado === true) {
        mostrarSweetAlert('success', 'Solicitud enviada', 'El proveedor recibirá tu solicitud.', BASE_URL . '/cliente/explorar-servicios');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo enviar la solicitud. Intenta nuevamente.');
    }
    exit();
}

// ===================================================================
// FUNCIONES — NECESIDADES Y COTIZACIONES
// ===================================================================

function mostrarNecesidades()
{
    $usuarioId      = (int)$_SESSION['user']['id'];
    $estado         = $_GET['estado'] ?? null;
    $nid            = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    $modelo         = new Necesidad();
    $misNecesidades = $modelo->listarPorClienteUsuario($usuarioId, $estado);
    $detalle        = null;
    $cotizaciones   = [];

    if ($nid > 0) {
        $detalle = $modelo->obtenerDetallePorClienteUsuario($usuarioId, $nid);
        if ($detalle) {
            $cotizaciones = $modelo->listarCotizacionesDeNecesidadParaCliente($usuarioId, $nid);
        }
    }

    require BASE_PATH . '/app/views/dashboard/cliente/necesidades.php';
    exit();
}

function crearNecesidad()
{
    $usuarioId     = (int)$_SESSION['user']['id'];
    $categoria     = trim($_POST['categoria']           ?? '');
    $categoriaOtro = trim($_POST['categoria_otro']      ?? '');
    $titulo        = trim($_POST['titulo']              ?? '');
    $descripcion   = trim($_POST['descripcion']         ?? '');
    $direccion     = trim($_POST['direccion']           ?? '');
    $ciudad        = trim($_POST['ciudad']              ?? '');
    $zona          = trim($_POST['zona']                ?? '');
    $fecha         = trim($_POST['fecha_preferida']     ?? '');
    $franja        = trim($_POST['franja_horaria']      ?? '');
    $hora          = trim($_POST['hora_preferida']      ?? '');
    $presupuesto   = $_POST['presupuesto_estimado']     ?? null;

    if ($categoria === '' || $titulo === '' || $descripcion === '' || $direccion === '' || $ciudad === '' || $fecha === '' || $franja === '') {
        mostrarSweetAlert('error', 'Campos incompletos', 'Completa todos los campos obligatorios.', BASE_URL . '/cliente/necesidades');
        exit();
    }

    if ($categoria === 'Otros' && $categoriaOtro === '') {
        mostrarSweetAlert('error', 'Categoría incompleta', 'Debes especificar la categoría.', BASE_URL . '/cliente/necesidades');
        exit();
    }

    $franjasValidas = ['mañana', 'tarde', 'noche'];
    if (!in_array($franja, $franjasValidas, true)) {
        mostrarSweetAlert('error', 'Franja inválida', 'Selecciona una franja horaria válida.', BASE_URL . '/cliente/necesidades');
        exit();
    }

    if ($hora === '') {
        $hora = match ($franja) {
            'mañana' => '09:00:00',
            'tarde'  => '15:00:00',
            default  => '19:00:00',
        };
    } else {
        if (preg_match('/^\d{2}:\d{2}$/', $hora)) {
            $hora .= ':00';
        }
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $hora)) {
            mostrarSweetAlert('error', 'Hora inválida', 'Ingresa una hora válida.', BASE_URL . '/cliente/necesidades');
            exit();
        }
    }

    $presupuesto = ($presupuesto === '' || $presupuesto === null) ? null : max(0.0, (float)$presupuesto);

    $modelo = new Necesidad();
    $ok     = $modelo->crearParaClienteUsuario($usuarioId, [
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
        mostrarSweetAlert('success', 'Necesidad publicada', 'Los proveedores podrán enviarte ofertas.', BASE_URL . '/cliente/necesidades');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo publicar la necesidad. Intenta nuevamente.', BASE_URL . '/cliente/necesidades');
    }
    exit();
}

function generarComprobantePDFCliente()
{
    $usuarioId   = (int)$_SESSION['user']['id'];
    // Acepta ?id=, ?contrato_id= o ?solicitud_id=
    $contratoId  = (int)($_GET['id'] ?? $_GET['contrato_id'] ?? 0);
    $solicitudId = (int)($_GET['solicitud_id'] ?? 0);

    $scModel = new ServicioContratado();

    // Resolver contrato_id desde solicitud_id si viene por esa vía
    if ($contratoId <= 0 && $solicitudId > 0) {
        $contratoId = $scModel->obtenerContratoIdPorSolicitud($solicitudId, $usuarioId) ?? 0;
    }

    if ($contratoId <= 0) {
        mostrarSweetAlert('error', 'No encontrado', 'El comprobante no existe o no tienes acceso.', BASE_URL . '/cliente/servicios-contratados');
        exit();
    }

    $contrato = $scModel->obtenerDetalleParaPDF($contratoId, $usuarioId, 'cliente');

    if (empty($contrato)) {
        mostrarSweetAlert('error', 'Acceso denegado', 'No tienes permiso para ver este comprobante.', BASE_URL . '/cliente/servicios-contratados');
        exit();
    }

    require_once BASE_PATH . '/app/helpers/pdf-helper.php';
    ob_start();
    require BASE_PATH . '/app/views/pdf/comprobante-contrato-pdf.php';
    $html = ob_get_clean();

    $filename = 'comprobante-contrato-' . str_pad($contratoId, 6, '0', STR_PAD_LEFT) . '.pdf';
    generarPDF($html, $filename, false);
    exit();
}

function aceptarCotizacion()
{
    $usuarioId    = (int)$_SESSION['user']['id'];
    $cotizacionId = (int)($_POST['cotizacion_id'] ?? 0);

    if ($cotizacionId <= 0) {
        mostrarSweetAlert('error', 'Error', 'Cotización inválida.');
        exit();
    }

    $modelo = new Cotizacion();
    $ok     = $modelo->aceptarCotizacionParaClienteUsuario($usuarioId, $cotizacionId);

    if ($ok) {
        mostrarSweetAlert('success', '¡Trato cerrado!', 'Has contratado el servicio correctamente.', BASE_URL . '/cliente/servicios-contratados');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo procesar la contratación. Intenta nuevamente.');
    }
    exit();
}
