﻿﻿<?php

require_once __DIR__ . '/../helpers/alert-helper.php';
require_once __DIR__ . '/../models/publicacion.php';
require_once __DIR__ . '/../models/proveedor-perfil.php';
require_once __DIR__ . '/../models/servicio-contratado.php';
require_once __DIR__ . '/../models/valoracion.php';
require_once __DIR__ . '/../models/solicitud.php';
require_once __DIR__ . '/../models/necesidad.php';
require_once __DIR__ . '/../models/cotizacion.php';
require_once __DIR__ . '/../models/Notificacion.php';
require_once __DIR__ . '/../models/SeguimientoContrato.php';
require_once __DIR__ . '/../models/cliente-notificaciones.php';

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
        if (str_contains($uri, '/cliente/contrato/seguimiento')) {
            seguimientoContratoJSON('cliente');
        } elseif (str_contains($uri, '/cliente/mapa/datos')) {
            datosMapaJSON();
        } elseif (str_contains($uri, 'contrato-pdf')) {
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

        if (str_contains($uri, '/cliente/contrato/comentario')) {
            agregarComentarioContrato('cliente');
        } elseif ($accion === 'calificar_servicio') {
            calificarServicio();
        } elseif ($accion === 'cancelar_servicio') {
            cancelarServicio();
        } elseif ($accion === 'guardar_solicitud_cliente' || str_contains($uri, 'guardar-solicitud')) {
            guardarSolicitud();
        } elseif ($accion === 'crear_necesidad') {
            crearNecesidad();
        } elseif ($accion === 'aceptar_cotizacion') {
            aceptarCotizacion();
        } elseif ($accion === 'agregar_metodo_pago') {
            agregarMetodoPago();
        } elseif ($accion === 'predeterminado_metodo_pago') {
            marcarPredeterminado();
        } elseif ($accion === 'eliminar_metodo_pago') {
            eliminarMetodoPago();
        } elseif ($accion === 'tokenizar_tarjeta') {
            guardarTarjetaTokenizada();
        } elseif (str_contains($uri, '/cliente/guardar-notificaciones')) {
            guardarNotificacionesCliente();
        } elseif (str_contains($uri, '/cliente/resenas/responder')) {
            guardarRespuestaCliente();
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
    $busqueda        = trim($_GET['q']      ?? '');
    $categoriaId     = isset($_GET['cat']) && $_GET['cat'] !== '' ? (int)$_GET['cat'] : null;
    $ciudad          = trim($_GET['ciudad'] ?? '');
    $precioMax       = isset($_GET['precio_max']) && $_GET['precio_max'] !== '' ? (float)$_GET['precio_max'] : null;
    $orden           = in_array($_GET['orden'] ?? '', ['precio_asc','precio_desc','valorados','recientes'], true)
                       ? $_GET['orden'] : 'recientes';
    $soloOfertas     = isset($_GET['ofertas']) && $_GET['ofertas'] === '1';
    $calificacionMin = isset($_GET['estrellas']) && $_GET['estrellas'] !== ''
                       ? max(0, min(5, (float)$_GET['estrellas'])) : null;
    $lat             = isset($_GET['lat'])   && is_numeric($_GET['lat'])   ? (float)$_GET['lat']   : null;
    $lng             = isset($_GET['lng'])   && is_numeric($_GET['lng'])   ? (float)$_GET['lng']   : null;
    $radioKm         = isset($_GET['radio']) && is_numeric($_GET['radio']) ? (int)$_GET['radio']   : 10;

    $catActual = $categoriaId ?? '';

    $modelo        = new Publicacion();
    $publicaciones = $modelo->listarPublicasActivas(
        $busqueda ?: null,
        $categoriaId,
        $ciudad ?: null,
        $precioMax,
        $orden,
        $soloOfertas,
        $calificacionMin,
        $lat,
        $lng,
        ($lat !== null && $lng !== null) ? $radioKm : null
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

    $proveedorUsuarioId = (int)($publicacion['proveedor_usuario_id'] ?? 0);
    $perfilPublico = $proveedorUsuarioId > 0
        ? (new ProveedorPerfil())->obtenerPerfilPublicoProveedor($proveedorUsuarioId)
        : ['perfil' => [], 'politicas' => [], 'disponibilidad' => []];

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
        try {
            $db  = new Conexion();
            $pdo = $db->getConexion();
            $st  = $pdo->prepare("
                SELECT pr.usuario_id
                FROM servicios_contratados sc
                INNER JOIN proveedores pr ON sc.proveedor_id = pr.id
                WHERE sc.id = :id LIMIT 1
            ");
            $st->execute([':id' => $contratoId]);
            $provUid = $st->fetchColumn();
            if ($provUid) {
                Notificacion::crear(
                    (int)$provUid,
                    Notificacion::TIPO_CALIFICACION,
                    'Nueva calificación recibida',
                    'Un cliente calificó tu servicio con ' . $calificacion . ' estrella(s).',
                    BASE_URL . '/proveedor/mis-servicios'
                );
            }
        } catch (PDOException $e) {
            error_log('calificarServicio::notif: ' . $e->getMessage());
        }
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
        try {
            $db  = new Conexion();
            $pdo = $db->getConexion();
            $st  = $pdo->prepare("SELECT usuario_id FROM proveedores WHERE id = :id LIMIT 1");
            $st->execute([':id' => $proveedorId]);
            $provUid = $st->fetchColumn();
            if ($provUid) {
                Notificacion::crear(
                    (int)$provUid,
                    Notificacion::TIPO_SOLICITUD,
                    'Nueva solicitud de servicio',
                    'Recibiste una nueva solicitud para "' . $titulo . '".',
                    BASE_URL . '/proveedor/solicitudes'
                );
            }
        } catch (PDOException $e) {
            error_log('guardarSolicitud::notif: ' . $e->getMessage());
        }
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
        try {
            $db  = new Conexion();
            $pdo = $db->getConexion();
            $st  = $pdo->prepare("
                SELECT pr.usuario_id, c.titulo
                FROM cotizaciones c
                INNER JOIN proveedores pr ON c.proveedor_id = pr.id
                WHERE c.id = :id LIMIT 1
            ");
            $st->execute([':id' => $cotizacionId]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                Notificacion::crear(
                    (int)$row['usuario_id'],
                    Notificacion::TIPO_COTIZACION,
                    'Cotización aceptada',
                    '¡Tu cotización para "' . $row['titulo'] . '" fue aceptada! Ya tienes un nuevo servicio contratado.',
                    BASE_URL . '/proveedor/servicios-contratados'
                );
            }
        } catch (PDOException $e) {
            error_log('aceptarCotizacion::notif: ' . $e->getMessage());
        }
        mostrarSweetAlert('success', '¡Trato cerrado!', 'Has contratado el servicio correctamente.', BASE_URL . '/cliente/servicios-contratados');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo procesar la contratación. Intenta nuevamente.');
    }
    exit();
}

// ===================================================================
// MÉTODOS DE PAGO (#183 / #184)
// ===================================================================

function agregarMetodoPago(): void
{
    $uid    = (int)$_SESSION['user']['id'];
    $tipo   = trim($_POST['tipo']             ?? '');
    $alias  = trim($_POST['alias']            ?? '');
    $digitos = preg_replace('/\D/', '', $_POST['ultimos_digitos'] ?? '');
    $digitos = mb_substr($digitos, -4);

    $tiposValidos = ['tarjeta_credito', 'tarjeta_debito', 'pse', 'efectivo', 'mercadopago'];
    if (!in_array($tipo, $tiposValidos) || $alias === '') {
        mostrarSweetAlert('error', 'Datos inválidos', 'Completa todos los campos requeridos.', BASE_URL . '/cliente/metodos-pago');
        exit;
    }

    try {
        $db  = new Conexion();
        $pdo = $db->getConexion();

        $pdo->exec("CREATE TABLE IF NOT EXISTS metodos_pago (
            id              INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id      INT          NOT NULL,
            tipo            VARCHAR(30)  NOT NULL,
            alias           VARCHAR(100) NOT NULL,
            ultimos_digitos VARCHAR(4)   NULL,
            predeterminado  TINYINT(1)   NOT NULL DEFAULT 0,
            created_at      DATETIME     DEFAULT NOW()
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $count = (int)$pdo->prepare("SELECT COUNT(*) FROM metodos_pago WHERE usuario_id = :uid")
            ->execute([':uid' => $uid]) ? 0 : 0;
        $st = $pdo->prepare("SELECT COUNT(*) FROM metodos_pago WHERE usuario_id = :uid");
        $st->execute([':uid' => $uid]);
        $esPrimero = (int)$st->fetchColumn() === 0;

        $pdo->prepare("
            INSERT INTO metodos_pago (usuario_id, tipo, alias, ultimos_digitos, predeterminado, created_at)
            VALUES (:uid, :tipo, :alias, :dig, :pred, NOW())
        ")->execute([
            ':uid'  => $uid,
            ':tipo' => $tipo,
            ':alias'=> $alias,
            ':dig'  => $digitos ?: null,
            ':pred' => $esPrimero ? 1 : 0,
        ]);

        mostrarSweetAlert('success', 'Método agregado', 'Tu método de pago fue guardado correctamente.', BASE_URL . '/cliente/metodos-pago');
    } catch (PDOException $e) {
        error_log('agregarMetodoPago: ' . $e->getMessage());
        mostrarSweetAlert('error', 'Error', 'No se pudo guardar el método de pago.', BASE_URL . '/cliente/metodos-pago');
    }
    exit;
}

function marcarPredeterminado(): void
{
    $uid    = (int)$_SESSION['user']['id'];
    $metodoId = (int)($_POST['metodo_id'] ?? 0);
    if ($metodoId <= 0) {
        mostrarSweetAlert('error', 'Error', 'Método no válido.', BASE_URL . '/cliente/metodos-pago');
        exit;
    }
    try {
        $db  = new Conexion();
        $pdo = $db->getConexion();
        $pdo->prepare("UPDATE metodos_pago SET predeterminado = 0 WHERE usuario_id = :uid")->execute([':uid' => $uid]);
        $pdo->prepare("UPDATE metodos_pago SET predeterminado = 1 WHERE id = :id AND usuario_id = :uid")->execute([':id' => $metodoId, ':uid' => $uid]);
        mostrarSweetAlert('success', 'Actualizado', 'Método predeterminado actualizado.', BASE_URL . '/cliente/metodos-pago');
    } catch (PDOException $e) {
        error_log('marcarPredeterminado: ' . $e->getMessage());
        mostrarSweetAlert('error', 'Error', 'No se pudo actualizar.', BASE_URL . '/cliente/metodos-pago');
    }
    exit;
}

function eliminarMetodoPago(): void
{
    $uid      = (int)$_SESSION['user']['id'];
    $metodoId = (int)($_POST['metodo_id'] ?? 0);
    if ($metodoId <= 0) {
        mostrarSweetAlert('error', 'Error', 'Método no válido.', BASE_URL . '/cliente/metodos-pago');
        exit;
    }
    try {
        $db  = new Conexion();
        $pdo = $db->getConexion();

        // Obtener datos antes de eliminar para limpiar vault de MP si aplica
        $stGet = $pdo->prepare("SELECT mp_customer_id, mp_card_id FROM metodos_pago WHERE id = :id AND usuario_id = :uid LIMIT 1");
        $stGet->execute([':id' => $metodoId, ':uid' => $uid]);
        $metodo = $stGet->fetch(PDO::FETCH_ASSOC);

        $st = $pdo->prepare("DELETE FROM metodos_pago WHERE id = :id AND usuario_id = :uid");
        $st->execute([':id' => $metodoId, ':uid' => $uid]);
        if ($st->rowCount() > 0) {
            // Si tenía tarjeta tokenizada en MP, eliminarla del vault
            if ($metodo && $metodo['mp_customer_id'] && $metodo['mp_card_id']) {
                mpDeleteCard($metodo['mp_customer_id'], $metodo['mp_card_id']);
            }
            // Si era el predeterminado, asignar el siguiente disponible
            $pdo->prepare("
                UPDATE metodos_pago SET predeterminado = 1
                WHERE usuario_id = :uid ORDER BY created_at ASC LIMIT 1
            ")->execute([':uid' => $uid]);
            mostrarSweetAlert('success', 'Eliminado', 'El método de pago fue eliminado.', BASE_URL . '/cliente/metodos-pago');
        } else {
            mostrarSweetAlert('error', 'No encontrado', 'No se encontró el método de pago.', BASE_URL . '/cliente/metodos-pago');
        }
    } catch (PDOException $e) {
        error_log('eliminarMetodoPago: ' . $e->getMessage());
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar.', BASE_URL . '/cliente/metodos-pago');
    }
    exit;
}

// ===================================================================
// TOKENIZACIÓN DE TARJETAS — MercadoPago Cards API (#182 / #181)
// ===================================================================

function ensureMetodosPagoColumns(PDO $pdo): void
{
    $cols = [
        "ADD COLUMN mp_customer_id VARCHAR(60) NULL",
        "ADD COLUMN mp_card_id     VARCHAR(60) NULL",
        "ADD COLUMN marca          VARCHAR(30) NULL",
        "ADD COLUMN expiry_month   VARCHAR(2)  NULL",
        "ADD COLUMN expiry_year    VARCHAR(4)  NULL",
    ];
    foreach ($cols as $col) {
        try { $pdo->exec("ALTER TABLE metodos_pago $col"); } catch (PDOException $e) {}
    }
}

function guardarTarjetaTokenizada(): void
{
    $uid   = (int)$_SESSION['user']['id'];
    $token = trim($_POST['card_token']        ?? '');
    $pmId  = trim($_POST['payment_method_id'] ?? '');

    if (!$token) {
        mostrarSweetAlert('error', 'Token inválido', 'No se recibió la tokenización de la tarjeta.', BASE_URL . '/cliente/metodos-pago/agregar-tarjeta');
        exit;
    }

    try {
        $db  = new Conexion();
        $pdo = $db->getConexion();
        ensureMetodosPagoColumns($pdo);

        $stU = $pdo->prepare("SELECT email FROM usuarios WHERE id = :uid LIMIT 1");
        $stU->execute([':uid' => $uid]);
        $email = $stU->fetchColumn();

        if (!$email) {
            mostrarSweetAlert('error', 'Error', 'Usuario no encontrado.', BASE_URL . '/cliente/metodos-pago');
            exit;
        }

        $customerId = mpGetOrCreateCustomer($email);
        if (!$customerId) {
            mostrarSweetAlert('error', 'Error MP', 'No se pudo registrar el cliente en MercadoPago.', BASE_URL . '/cliente/metodos-pago/agregar-tarjeta');
            exit;
        }

        $card = mpSaveCard($customerId, $token);
        if (!$card) {
            mostrarSweetAlert('error', 'Tarjeta rechazada', 'No se pudo guardar la tarjeta. Verifica los datos e intenta de nuevo.', BASE_URL . '/cliente/metodos-pago/agregar-tarjeta');
            exit;
        }

        $cardId  = $card['id'];
        $last4   = $card['last_four_digits']         ?? '';
        $marca   = strtolower($card['payment_method']['id'] ?? $pmId);
        $expMes  = str_pad((string)($card['expiration_month'] ?? ''), 2, '0', STR_PAD_LEFT);
        $expAno  = (string)($card['expiration_year'] ?? '');
        $alias   = ucfirst($marca) . ' ••••' . $last4;

        // Evitar duplicados del mismo card_id
        $stChk = $pdo->prepare("SELECT id FROM metodos_pago WHERE mp_card_id = :cid AND usuario_id = :uid LIMIT 1");
        $stChk->execute([':cid' => $cardId, ':uid' => $uid]);
        if ($stChk->fetchColumn()) {
            mostrarSweetAlert('info', 'Ya registrada', 'Esta tarjeta ya está en tu cuenta.', BASE_URL . '/cliente/metodos-pago');
            exit;
        }

        $stCnt = $pdo->prepare("SELECT COUNT(*) FROM metodos_pago WHERE usuario_id = :uid");
        $stCnt->execute([':uid' => $uid]);
        $esPrimero = (int)$stCnt->fetchColumn() === 0;

        $pdo->prepare("
            INSERT INTO metodos_pago
                (usuario_id, tipo, alias, ultimos_digitos, predeterminado,
                 mp_customer_id, mp_card_id, marca, expiry_month, expiry_year, created_at)
            VALUES
                (:uid, 'tarjeta_credito', :alias, :dig, :pred,
                 :cust, :card, :marca, :expm, :expy, NOW())
        ")->execute([
            ':uid'   => $uid,
            ':alias' => $alias,
            ':dig'   => $last4,
            ':pred'  => $esPrimero ? 1 : 0,
            ':cust'  => $customerId,
            ':card'  => $cardId,
            ':marca' => $marca,
            ':expm'  => $expMes,
            ':expy'  => $expAno,
        ]);

        mostrarSweetAlert('success', 'Tarjeta guardada', 'Tu tarjeta fue registrada correctamente.', BASE_URL . '/cliente/metodos-pago');
    } catch (PDOException $e) {
        error_log('guardarTarjetaTokenizada: ' . $e->getMessage());
        mostrarSweetAlert('error', 'Error', 'No se pudo guardar la tarjeta.', BASE_URL . '/cliente/metodos-pago/agregar-tarjeta');
    }
    exit;
}

function mpGetOrCreateCustomer(string $email): ?string
{
    $ch = curl_init('https://api.mercadopago.com/v1/customers/search?email=' . urlencode($email));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . MP_ACCESS_TOKEN],
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $data = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!empty($data['results'][0]['id'])) {
        return $data['results'][0]['id'];
    }

    $ch = curl_init('https://api.mercadopago.com/v1/customers');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['email' => $email]),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . MP_ACCESS_TOKEN,
        ],
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $data = json_decode(curl_exec($ch), true);
    curl_close($ch);

    return $data['id'] ?? null;
}

function mpSaveCard(string $customerId, string $token): ?array
{
    $ch = curl_init("https://api.mercadopago.com/v1/customers/{$customerId}/cards");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['token' => $token]),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . MP_ACCESS_TOKEN,
        ],
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);
    if ($code === 201 && isset($data['id'])) {
        return $data;
    }
    error_log("mpSaveCard [HTTP $code]: " . substr($response, 0, 300));
    return null;
}

function mpDeleteCard(string $customerId, string $cardId): void
{
    $ch = curl_init("https://api.mercadopago.com/v1/customers/{$customerId}/cards/{$cardId}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . MP_ACCESS_TOKEN],
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

// =======================================================================
// SEGUIMIENTO DE CONTRATO
// =======================================================================

function seguimientoContratoJSON(string $rol): void
{
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');

    $contratoId = (int)($_GET['id'] ?? 0);
    $usuarioId  = (int)$_SESSION['user']['id'];

    if ($contratoId <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'ID de contrato inválido.']);
        exit();
    }

    if (!SeguimientoContrato::contratoEsDeUsuario($contratoId, $usuarioId, $rol)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'message' => 'Acceso denegado.']);
        exit();
    }

    $historial = SeguimientoContrato::listarPorContrato($contratoId);
    echo json_encode(['ok' => true, 'data' => $historial]);
    exit();
}

function agregarComentarioContrato(string $rol): void
{
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');

    $contratoId = (int)($_POST['contrato_id'] ?? 0);
    $comentario = trim($_POST['comentario']   ?? '');
    $usuarioId  = (int)$_SESSION['user']['id'];

    if ($contratoId <= 0 || $comentario === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Datos incompletos.']);
        exit();
    }

    if (!SeguimientoContrato::contratoEsDeUsuario($contratoId, $usuarioId, $rol)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'message' => 'Acceso denegado.']);
        exit();
    }

    $archivoPath = null;
    if (!empty($_FILES['archivo']['tmp_name'])) {
        $archivoPath = SeguimientoContrato::subirArchivo($_FILES['archivo']);
        if ($archivoPath === null) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => 'Archivo no válido (máx 5 MB, formatos: pdf, jpg, png, doc, docx, txt).']);
            exit();
        }
    }

    $ok = SeguimientoContrato::registrar(
        contratoId:     $contratoId,
        usuarioId:      $usuarioId,
        rol:            $rol,
        comentario:     $comentario,
        archivoAdjunto: $archivoPath
    );

    echo json_encode(['ok' => $ok, 'message' => $ok ? 'Comentario registrado.' : 'Error al guardar.']);
    exit();
}

// =======================================================================
// PREFERENCIAS DE NOTIFICACIONES
// =======================================================================

function guardarNotificacionesCliente(): void
{
    $idUsuario = (int)$_SESSION['user']['id'];
    $data = [
        'noti_cambios_estado'    => isset($_POST['noti_cambios_estado'])    ? 1 : 0,
        'noti_nueva_cotizacion'  => isset($_POST['noti_nueva_cotizacion'])  ? 1 : 0,
        'noti_recordatorio_pago' => isset($_POST['noti_recordatorio_pago']) ? 1 : 0,
        'noti_resenas'           => isset($_POST['noti_resenas'])           ? 1 : 0,
        'canal_email'            => isset($_POST['canal_email'])            ? 1 : 0,
        'canal_interna'          => isset($_POST['canal_interna'])          ? 1 : 0,
        'resumen_diario'         => isset($_POST['resumen_diario'])         ? 1 : 0,
        'resumen_semanal'        => isset($_POST['resumen_semanal'])        ? 1 : 0,
    ];

    $ok = (new ClienteNotificaciones())->guardarDesdeFormulario($idUsuario, $data);

    if ($ok) {
        mostrarSweetAlert('success', 'Preferencias guardadas', 'Tus preferencias de notificación se actualizaron correctamente.', BASE_URL . '/cliente/notificaciones?filtro=preferencias');
    } else {
        mostrarSweetAlert('error', 'Error al guardar', 'No se pudieron guardar tus preferencias. Intenta nuevamente.', BASE_URL . '/cliente/notificaciones?filtro=preferencias');
    }
    exit();
}

// =======================================================================
// RESPONDER RESEÑA (como cliente evaluado)
// =======================================================================

function guardarRespuestaCliente(): void
{
    $idResena       = (int)($_POST['id_valoracion']  ?? 0);
    $textoRespuesta = trim($_POST['texto_respuesta'] ?? '');
    $usuarioId      = (int)$_SESSION['user']['id'];

    if (!$idResena || empty($textoRespuesta)) {
        mostrarSweetAlert('warning', 'Datos incompletos', 'Escribe una respuesta antes de enviar.', BASE_URL . '/cliente/historial-servicios');
        exit();
    }

    $ok = (new Valoracion())->responderComoCliente($idResena, $usuarioId, $textoRespuesta);

    if ($ok) {
        mostrarSweetAlert('success', 'Respuesta enviada', 'Tu respuesta fue publicada exitosamente.', BASE_URL . '/cliente/historial-servicios');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo guardar la respuesta. Es posible que ya hayas respondido antes.', BASE_URL . '/cliente/historial-servicios');
    }
    exit();
}
