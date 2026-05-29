<?php
// =======================================================================
// proveedor-controller.php — Controlador unificado del proveedor
// Absorbe: servicios CRUD, operacion, reseñas y perfil/configuración
// =======================================================================

require_once __DIR__ . '/../helpers/alert-helper.php';
require_once __DIR__ . '/../models/Servicio.php';
require_once __DIR__ . '/../models/Publicacion.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Solicitud.php';
require_once __DIR__ . '/../models/Necesidad.php';
require_once __DIR__ . '/../models/Cotizacion.php';
require_once __DIR__ . '/../models/ServicioContratado.php';
require_once __DIR__ . '/../models/Valoracion.php';
require_once __DIR__ . '/../models/ProveedorPerfil.php';
require_once __DIR__ . '/../models/ProveedorNotificaciones.php';
require_once __DIR__ . '/../models/ProveedorPagosFacturacion.php';

// =======================================================================
// GUARD DE SESIÓN Y ROL
// =======================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']['id']) || ($_SESSION['user']['rol'] ?? '') !== 'proveedor') {
    mostrarSweetAlert('error', 'Acceso denegado', 'Solo proveedores pueden acceder a esta sección.', BASE_URL . '/login');
    exit();
}

// =======================================================================
// ROUTER PRINCIPAL
// =======================================================================

$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

switch ($method) {

    case 'GET':
        $accion = $_GET['accion'] ?? '';
        $id     = $_GET['id']     ?? null;

        if (str_contains($uri, '/proveedor/contrato-pdf')) {
            generarComprobantePDFProveedor();
        } elseif (str_contains($uri, '/proveedor/oportunidades')) {
            mostrarOportunidades();
        } elseif (str_contains($uri, '/proveedor/resenas')) {
            mostrarResenasProveedor();
        } elseif (str_contains($uri, '/proveedor/en-proceso')) {
            mostrarEnProceso();
        } elseif ($accion === 'aceptar_solicitud') {
            aceptarSolicitud($id);
        } elseif ($accion === 'rechazar_solicitud') {
            rechazarSolicitud($id);
        } elseif ($accion === 'eliminar') {
            eliminarServicio($id);
        } elseif ($accion === 'pausar') {
            cambiarEstadoServicio($id, 0);
        } elseif ($accion === 'reanudar') {
            cambiarEstadoServicio($id, 1);
        } elseif ($accion === 'detalle') {
            obtenerDetallePublicacionJSON($id);
        } elseif ($id) {
            mostrarServicioId($id);
        } else {
            mostrarServicios();
        }
        break;

    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if (str_contains($uri, '/proveedor/resenas/responder')) {
            guardarRespuestaProveedor();
        } elseif (str_contains($uri, '/proveedor/promociones/crear')) {
            crearPromocion();
        } elseif (str_contains($uri, '/proveedor/promociones/eliminar')) {
            eliminarPromocion();
        } elseif (str_contains($uri, '/proveedor/oportunidades')) {
            enviarCotizacion();
        } elseif (str_contains($uri, '/proveedor/actualizar-estado') || $accion === 'actualizar_estado_servicio') {
            actualizarEstadoServicio();
        } elseif (str_contains($uri, '/proveedor/guardar-perfil-profesional') || $accion === 'actualizar_perfil') {
            guardarPerfilProfesional();
        } elseif (str_contains($uri, '/proveedor/actualizar-credenciales') || $accion === 'actualizar_credenciales') {
            actualizarCredenciales();
        } elseif (str_contains($uri, '/proveedor/actualizar-seguridad') || $accion === 'actualizar_seguridad') {
            actualizarSeguridad();
        } elseif (str_contains($uri, '/proveedor/cerrar-sesiones') || $accion === 'cerrar_sesiones') {
            cerrarSesiones();
        } elseif (str_contains($uri, '/proveedor/guardar-disponibilidad') || $accion === 'actualizar_disponibilidad') {
            guardarDisponibilidad();
        } elseif (str_contains($uri, '/proveedor/guardar-notificaciones') || $accion === 'guardar_notificaciones') {
            guardarNotificaciones();
        } elseif (str_contains($uri, '/proveedor/guardar-pagos') || $accion === 'guardar_pagos') {
            guardarPagos();
        } elseif (str_contains($uri, '/proveedor/guardar-politicas') || $accion === 'actualizar_politicas') {
            guardarPoliticas();
        } elseif ($accion === 'actualizar') {
            actualizarServicio();
        } else {
            registrarServicio();
        }
        break;

    default:
        http_response_code(405);
        mostrarSweetAlert('error', 'Método no permitido', 'Esta ruta no acepta ese tipo de petición.');
        exit();
}

// =======================================================================
// SECCIÓN 1: GESTIÓN DE SERVICIOS (CRUD)
// =======================================================================

function registrarServicio()
{
    $usuarioId = (int)($_SESSION['user']['id'] ?? 0);
    if ($usuarioId <= 0) {
        mostrarSweetAlert('error', 'Sesión inválida', 'No se encontró el usuario en sesión.');
        exit();
    }

    $bloqueo = verificarPerfilHabilitado($usuarioId);
    if ($bloqueo !== null) {
        mostrarSweetAlert('warning', 'Perfil incompleto', $bloqueo, BASE_URL . '/proveedor/configuracion');
        exit();
    }

    // Verificar límite de publicaciones del plan activo
    require_once BASE_PATH . '/app/helpers/plan-helper.php';
    if (!proveedorPuedePublicar($usuarioId)) {
        $plan = obtenerPlanActivoProveedor($usuarioId);
        $max  = $plan['max_servicios_activos'] ?? 3;
        mostrarSweetAlert(
            'warning',
            'Límite de publicaciones alcanzado',
            "Tu plan \"{$plan['nombre']}\" permite hasta {$max} publicaciones activas. Mejora tu membresía para publicar más.",
            BASE_URL . '/proveedor/membresia'
        );
        exit();
    }

    $nombre         = trim($_POST['nombre'] ?? '');
    $id_categoria   = (int)($_POST['id_categoria'] ?? 0);
    $precio         = $_POST['precio'] ?? '';
    $disponibilidad = isset($_POST['disponibilidad']) ? (int)$_POST['disponibilidad'] : null;
    $descripcion    = trim($_POST['descripcion'] ?? '');

    if ($nombre === '' || $id_categoria <= 0 || $precio === '' || !is_numeric($precio) || !in_array($disponibilidad, [0, 1], true)) {
        mostrarSweetAlert('error', 'Campos inválidos', 'Completa correctamente todos los campos obligatorios.');
        exit();
    }

    if ((float)$precio < 0) {
        mostrarSweetAlert('error', 'Precio inválido', 'El precio no puede ser menor que cero.');
        exit();
    }

    $ruta_img = 'default_service.png';

    if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file      = $_FILES['imagen'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, ['png', 'jpg', 'jpeg'])) {
            mostrarSweetAlert('error', 'Extensión no permitida', 'Por favor carga una imagen PNG, JPG o JPEG.');
            exit();
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Imagen muy pesada', 'El peso de la imagen supera el límite de 2MB.');
            exit();
        }

        $ruta_img = uniqid('servicio_') . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], BASE_PATH . '/public/uploads/servicios/' . $ruta_img)) {
            mostrarSweetAlert('error', 'Error de imagen', 'No se pudo guardar la imagen.');
            exit();
        }
    }

    $objServicio  = new Servicio();
    $dataServicio = [
        'nombre'         => $nombre,
        'descripcion'    => $descripcion,
        'precio'         => number_format((float)$precio, 2, '.', ''),
        'id_categoria'   => $id_categoria,
        'imagen'         => $ruta_img,
        'disponibilidad' => $disponibilidad,
    ];

    $resultado = $objServicio->registrar($dataServicio);

    if ($resultado === true) {
        $servicioId        = $objServicio->getUltimoIdInsertado();
        $publicacionCreada = false;
        $esVip             = false;

        try {
            $publicacionModel  = new Publicacion();
            $publicacionCreada = $publicacionModel->crearParaServicioDeProveedor(
                $usuarioId,
                $servicioId,
                ['nombre' => $nombre, 'descripcion' => $descripcion, 'precio' => $dataServicio['precio']]
            );

            if ($publicacionCreada) {
                $db      = new Conexion();
                $conn    = $db->getConexion();
                $stmt    = $conn->prepare("SELECT auto_aprobacion_activa FROM proveedores WHERE usuario_id = :uid LIMIT 1");
                $stmt->execute([':uid' => $usuarioId]);
                $prov    = $stmt->fetch(PDO::FETCH_ASSOC);
                $esVip   = (int)($prov['auto_aprobacion_activa'] ?? 0) === 1;
            }
        } catch (Exception $e) {
            error_log("Error al crear publicación para servicio {$servicioId}: " . $e->getMessage());
        }

        if ($esVip) {
            mostrarSweetAlert('success', '¡Servicio publicado!', 'Tu servicio fue aprobado automáticamente por tu historial de confianza.', BASE_URL . '/proveedor/listar-servicio');
        } else {
            mostrarSweetAlert('success', 'Servicio registrado', 'Tu servicio está en revisión. El equipo lo aprobará en las próximas horas.', BASE_URL . '/proveedor/listar-servicio');
        }
    } else {
        mostrarSweetAlert('error', 'Error al registrar', 'No se pudo registrar el servicio. Intenta nuevamente.');
    }
    exit();
}

function actualizarServicio()
{
    $id             = (int)($_POST['id'] ?? 0);
    $nombre         = trim($_POST['nombre'] ?? '');
    $id_categoria   = (int)($_POST['id_categoria'] ?? 0);
    $disponibilidad = $_POST['disponibilidad'] ?? '';
    $descripcion    = trim($_POST['descripcion'] ?? '');
    $precio         = $_POST['precio'] ?? '';

    if ($id <= 0 || $nombre === '' || $id_categoria <= 0 || $disponibilidad === '') {
        mostrarSweetAlert('error', 'Campos inválidos', 'Por favor, rellena todos los campos correctamente.');
        exit();
    }

    $objServicio = new Servicio();
    $ruta_img    = $_POST['imagen_actual'] ?? 'default_service.png';

    if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file    = $_FILES['imagen'];
        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $mime    = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/jpg'])) {
            mostrarSweetAlert('error', 'Tipo no permitido', 'Solo se aceptan imágenes JPG o PNG.');
            exit();
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Archivo grande', 'El límite es 2MB.');
            exit();
        }

        $nuevo_nombre = uniqid('servicio_') . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $destino      = BASE_PATH . '/public/uploads/servicios/' . $nuevo_nombre;

        if (move_uploaded_file($file['tmp_name'], $destino)) {
            if ($ruta_img && $ruta_img !== 'default_service.png' && file_exists(BASE_PATH . '/public/uploads/servicios/' . $ruta_img)) {
                unlink(BASE_PATH . '/public/uploads/servicios/' . $ruta_img);
            }
            $ruta_img = $nuevo_nombre;
        } else {
            mostrarSweetAlert('error', 'Error servidor', 'No se pudo guardar la imagen.');
            exit();
        }
    }

    $data = [
        'id'             => $id,
        'nombre'         => $nombre,
        'id_categoria'   => $id_categoria,
        'disponibilidad' => (int)$disponibilidad,
        'descripcion'    => $descripcion,
        'precio'         => number_format((float)$precio, 2, '.', ''),
        'imagen'         => $ruta_img,
    ];

    if ($objServicio->actualizar($data)) {
        mostrarSweetAlert('success', 'Actualizado', 'Servicio actualizado correctamente.', BASE_URL . '/proveedor/listar-servicio');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo actualizar en la base de datos.');
    }
    exit();
}

function eliminarServicio($id)
{
    $id = (int)$id;
    if ($id <= 0) {
        mostrarSweetAlert('error', 'Error', 'ID de servicio inválido.');
        exit();
    }

    $objServicio    = new Servicio();
    $servicioActual = $objServicio->mostrarId($id);

    if (!$servicioActual) {
        mostrarSweetAlert('error', 'No encontrado', 'El servicio no existe.');
        exit();
    }

    $img = $servicioActual['imagen'] ?? '';
    if ($img && $img !== 'default_service.png') {
        $ruta = BASE_PATH . '/public/uploads/servicios/' . $img;
        if (file_exists($ruta)) unlink($ruta);
    }

    if ($objServicio->eliminar($id)) {
        mostrarSweetAlert('success', 'Eliminado', 'Servicio eliminado con éxito.', BASE_URL . '/proveedor/listar-servicio');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar el registro.');
    }
    exit();
}

function cambiarEstadoServicio($id, int $estado)
{
    $id = (int)$id;
    if ($id <= 0) {
        mostrarSweetAlert('error', 'Error', 'ID de servicio inválido.');
        exit();
    }

    $objServicio = new Servicio();
    $datos       = $objServicio->obtenerDetalleCompleto($id);

    if (!$datos || ($datos['publicacion_estado'] ?? '') !== 'aprobado') {
        mostrarSweetAlert('warning', 'Acción no permitida', 'Solo puedes pausar o reanudar servicios aprobados.');
        exit();
    }

    $accionTxt = $estado === 1 ? 'reanudado' : 'pausado';
    $mensaje   = $estado === 1
        ? 'Tu servicio es visible nuevamente para los clientes.'
        : 'Tu servicio ya no es visible para los clientes.';

    if ($objServicio->cambiarDisponibilidad($id, $estado)) {
        mostrarSweetAlert('success', "Servicio {$accionTxt}", $mensaje, BASE_URL . '/proveedor/listar-servicio');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo actualizar el servicio.');
    }
    exit();
}

function mostrarServicios()
{
    $objServicio = new Servicio();
    return $objServicio->mostrar();
}

function mostrarServicioId($id)
{
    $objServicio = new Servicio();
    return $objServicio->mostrarId($id);
}

function obtenerCategorias()
{
    $obj = new Categoria();
    return $obj->mostrar();
}

function obtenerServiciosDelProveedor(int $usuarioId): array
{
    $obj = new Publicacion();
    return $obj->listarPorProveedorUsuario($usuarioId);
}

function obtenerSolicitudesProveedor()
{
    $usuarioId      = (int)$_SESSION['user']['id'];
    $solicitudModel = new Solicitud();
    return $solicitudModel->listarPorProveedor($usuarioId);
}

function obtenerDetallePublicacionJSON($id)
{
    header('Content-Type: application/json');
    $id = (int)$id;

    if ($id <= 0) {
        echo json_encode(['error' => 'ID no proporcionado']);
        exit();
    }

    $detalle = (new Publicacion())->obtenerDetallePublicacion($id);
    echo json_encode($detalle ?: ['error' => 'Publicación no encontrada']);
    exit();
}

// =======================================================================
// SECCIÓN 2: OPORTUNIDADES Y COTIZACIONES
// =======================================================================

function mostrarOportunidades()
{
    $usuarioId = (int)$_SESSION['user']['id'];
    $filtros   = [
        'busqueda'  => trim($_GET['q']         ?? ''),
        'ciudad'    => trim($_GET['ciudad']    ?? ''),
        'categoria' => trim($_GET['categoria'] ?? ''),
    ];

    $necesidades             = (new Necesidad())->obtenerOportunidades($usuarioId, $filtros);
    $publicacionesProveedor  = (new Cotizacion())->obtenerPublicacionesAprobadasPorProveedorUsuario($usuarioId);

    require_once BASE_PATH . '/app/views/dashboard/proveedor/oportunidades.php';
    exit();
}

function enviarCotizacion()
{
    $usuarioId = (int)$_SESSION['user']['id'];

    $bloqueo = verificarPerfilHabilitado($usuarioId);
    if ($bloqueo !== null) {
        mostrarSweetAlert('warning', 'Perfil incompleto', $bloqueo, BASE_URL . '/proveedor/oportunidades');
        exit();
    }

    $necesidadId   = (int)($_POST['necesidad_id']   ?? 0);
    $publicacionId = (int)($_POST['publicacion_id'] ?? 0);
    $titulo        = trim($_POST['titulo']           ?? '');
    $mensaje       = trim($_POST['mensaje']          ?? '');
    $precio        = $_POST['precio_oferta'] ?? ($_POST['precio'] ?? null);
    $tiempo        = trim($_POST['tiempo_estimado']  ?? '');

    if ($necesidadId <= 0 || $publicacionId <= 0 || $titulo === '' || $precio === null || $precio === '') {
        mostrarSweetAlert('error', 'Datos incompletos', 'Debes seleccionar una publicación, escribir un título y definir un precio.', BASE_URL . '/proveedor/oportunidades');
        exit();
    }

    $datos = [
        'publicacion_id'  => $publicacionId,
        'titulo'          => $titulo,
        'precio'          => $precio,
        'tiempo_estimado' => $tiempo,
        'mensaje'         => $mensaje,
    ];

    $exito = (new Cotizacion())->crearParaNecesidadPorProveedorUsuario($usuarioId, $necesidadId, $datos);

    if ($exito) {
        mostrarSweetAlert('success', 'Oferta enviada', 'El cliente verá tu cotización.', BASE_URL . '/proveedor/oportunidades');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo enviar. Verifica que la necesidad siga abierta y que la publicación seleccionada sea tuya y esté aprobada.', BASE_URL . '/proveedor/oportunidades');
    }
    exit();
}

// =======================================================================
// SECCIÓN 3: SERVICIOS EN CURSO
// =======================================================================

function mostrarEnProceso()
{
    $usuarioId = (int)$_SESSION['user']['id'];
    $servicios = (new ServicioContratado())->listarPorProveedorUsuario($usuarioId);
    $stats     = [
        'en_proceso' => count($servicios),
        'para_hoy'   => 0,
        'vencen'     => 0,
        'promedio'   => 0,
    ];
    require BASE_PATH . '/app/views/dashboard/proveedor/en-proceso.php';
    exit();
}

function actualizarEstadoServicio()
{
    header('Content-Type: application/json; charset=utf-8');

    $contratoId  = (int)($_POST['contrato_id'] ?? 0);
    $nuevoEstado = trim($_POST['estado_actual'] ?? $_POST['estado'] ?? '');

    if ($contratoId <= 0 || $nuevoEstado === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
        exit();
    }

    $estadosPermitidos = ['pendiente', 'confirmado', 'en_proceso', 'finalizado', 'cancelado', 'cancelado_cliente', 'cancelado_proveedor'];

    if (!in_array($nuevoEstado, $estadosPermitidos, true)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Estado no válido.']);
        exit();
    }

    $usuarioId = (int)$_SESSION['user']['id'];
    $modelo    = new ServicioContratado();

    if (!$modelo->contratoPerteneceAProveedor($contratoId, $usuarioId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No autorizado para modificar este servicio.']);
        exit();
    }

    // Bloquear avance si el servicio tiene precio y el cliente no ha pagado
    if (in_array($nuevoEstado, ['en_proceso', 'finalizado'], true)) {
        try {
            $dbPago  = new Conexion();
            $pdoPago = $dbPago->getConexion();

            // Obtener precio real de la publicación
            $stPrecio = $pdoPago->prepare("
                SELECT COALESCE(cot.precio, pub_sol.precio, sv.precio, 0) AS monto
                FROM servicios_contratados sc
                LEFT JOIN cotizaciones cot      ON sc.cotizacion_id    = cot.id
                LEFT JOIN solicitudes sol        ON sc.solicitud_id     = sol.id
                LEFT JOIN publicaciones pub_sol  ON sol.publicacion_id  = pub_sol.id
                LEFT JOIN servicios sv           ON sc.servicio_id      = sv.id
                WHERE sc.id = :id LIMIT 1
            ");
            $stPrecio->execute([':id' => $contratoId]);
            $monto = (float)($stPrecio->fetchColumn() ?: 0);

            if ($monto > 0) {
                $stPago = $pdoPago->prepare("
                    SELECT COUNT(*) FROM pagos_servicios
                    WHERE servicio_contratado_id = :id
                ");
                $stPago->execute([':id' => $contratoId]);
                if ((int)$stPago->fetchColumn() === 0) {
                    http_response_code(402);
                    echo json_encode([
                        'success' => false,
                        'message' => 'El cliente aún no ha realizado el pago. El servicio no puede avanzar hasta que se complete.',
                    ]);
                    exit();
                }
            }
        } catch (PDOException $e) {
            // pagos_servicios puede no existir aún — se permite continuar
            error_log('actualizarEstadoServicio::checkPago: ' . $e->getMessage());
        }
    }

    if (!$modelo->actualizarEstado($contratoId, $nuevoEstado)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estado del servicio.']);
        exit();
    }

    // Al finalizar: marcar el pago como liberado (escrow → proveedor)
    if ($nuevoEstado === 'finalizado') {
        try {
            $db  = new Conexion();
            $pdo = $db->getConexion();
            $pdo->prepare("
                UPDATE pagos_servicios
                SET liberado = 1, fecha_liberacion = NOW()
                WHERE servicio_contratado_id = :id AND liberado = 0
            ")->execute([':id' => $contratoId]);
        } catch (PDOException $e) {
            error_log('actualizarEstadoServicio::liberarPago: ' . $e->getMessage());
        }
    }

    echo json_encode([
        'success' => true,
        'message' => $nuevoEstado === 'finalizado'
            ? 'El servicio fue marcado como finalizado y el pago fue liberado al proveedor.'
            : 'El estado del servicio fue actualizado correctamente.',
        'estado'  => $nuevoEstado,
    ]);
    exit();
}

// =====================================================================
// PROMOCIONES
// =====================================================================

// =====================================================================
// GEOCODIFICACIÓN — Nominatim (OpenStreetMap, gratuito)
// =====================================================================

function geocodificarCiudad(string $ciudad): array
{
    if (empty(trim($ciudad))) return [null, null];

    try {
        $query = urlencode($ciudad . ', Colombia');
        $url   = "https://nominatim.openstreetmap.org/search?q={$query}&format=json&limit=1&countrycodes=co";

        $ctx = stream_context_create(['http' => [
            'timeout' => 5,
            'header'  => "User-Agent: ProviServers/1.0 (miguelozano913@gmail.com)\r\n",
        ]]);

        $response = @file_get_contents($url, false, $ctx);
        if ($response === false) return [null, null];

        $data = json_decode($response, true);
        if (!empty($data[0]['lat']) && !empty($data[0]['lon'])) {
            return [(float)$data[0]['lat'], (float)$data[0]['lon']];
        }
    } catch (Exception $e) {
        error_log('geocodificarCiudad: ' . $e->getMessage());
    }

    return [null, null];
}

function crearPromocion(): void
{
    $uid            = (int)$_SESSION['user']['id'];
    $publicacionId  = (int)($_POST['publicacion_id']       ?? 0);
    $descuento      = (int)($_POST['porcentaje_descuento'] ?? 0);
    $fechaInicio    = trim($_POST['fecha_inicio'] ?? '');
    $fechaFin       = trim($_POST['fecha_fin']    ?? '');

    if ($publicacionId <= 0 || $descuento < 1 || $descuento > 100 || !$fechaInicio || !$fechaFin) {
        mostrarSweetAlert('error', 'Datos incompletos', 'Completa todos los campos correctamente.', BASE_URL . '/proveedor/promociones');
        exit;
    }

    if ($fechaFin <= $fechaInicio) {
        mostrarSweetAlert('error', 'Fechas inválidas', 'La fecha de fin debe ser posterior a la de inicio.', BASE_URL . '/proveedor/promociones');
        exit;
    }

    try {
        $db  = new Conexion();
        $pdo = $db->getConexion();

        $stProv = $pdo->prepare("SELECT id FROM proveedores WHERE usuario_id = :uid LIMIT 1");
        $stProv->execute([':uid' => $uid]);
        $proveedorId = (int)($stProv->fetchColumn() ?: 0);

        if (!$proveedorId) {
            mostrarSweetAlert('error', 'Error', 'No se encontró tu perfil de proveedor.', BASE_URL . '/proveedor/promociones');
            exit;
        }

        // Verificar que la publicación pertenece a este proveedor
        $stCheck = $pdo->prepare("SELECT id FROM publicaciones WHERE id = :pid AND proveedor_id = :prov LIMIT 1");
        $stCheck->execute([':pid' => $publicacionId, ':prov' => $proveedorId]);
        if (!$stCheck->fetchColumn()) {
            mostrarSweetAlert('error', 'No autorizado', 'La publicación no te pertenece.', BASE_URL . '/proveedor/promociones');
            exit;
        }

        // Crear tabla si no existe
        $pdo->exec("CREATE TABLE IF NOT EXISTS promociones (
            id                   INT AUTO_INCREMENT PRIMARY KEY,
            proveedor_id         INT  NOT NULL,
            publicacion_id       INT  NULL,
            porcentaje_descuento INT  NOT NULL DEFAULT 0,
            fecha_inicio         DATE NOT NULL,
            fecha_fin            DATE NOT NULL,
            created_at           DATETIME DEFAULT NOW()
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->prepare("
            INSERT INTO promociones (proveedor_id, publicacion_id, porcentaje_descuento, fecha_inicio, fecha_fin, created_at)
            VALUES (:prov, :pub, :desc, :inicio, :fin, NOW())
        ")->execute([
            ':prov'  => $proveedorId,
            ':pub'   => $publicacionId,
            ':desc'  => $descuento,
            ':inicio'=> $fechaInicio,
            ':fin'   => $fechaFin,
        ]);

        mostrarSweetAlert('success', 'Promoción creada', 'Tu promoción fue creada correctamente.', BASE_URL . '/proveedor/promociones');
    } catch (PDOException $e) {
        error_log('crearPromocion: ' . $e->getMessage());
        mostrarSweetAlert('error', 'Error', 'No se pudo crear la promoción.', BASE_URL . '/proveedor/promociones');
    }
    exit;
}

function eliminarPromocion(): void
{
    $uid     = (int)$_SESSION['user']['id'];
    $promoId = (int)($_POST['promo_id'] ?? 0);

    if ($promoId <= 0) {
        mostrarSweetAlert('error', 'Error', 'Promoción no válida.', BASE_URL . '/proveedor/promociones');
        exit;
    }

    try {
        $db  = new Conexion();
        $pdo = $db->getConexion();

        $stProv = $pdo->prepare("SELECT id FROM proveedores WHERE usuario_id = :uid LIMIT 1");
        $stProv->execute([':uid' => $uid]);
        $proveedorId = (int)($stProv->fetchColumn() ?: 0);

        $st = $pdo->prepare("DELETE FROM promociones WHERE id = :id AND proveedor_id = :prov");
        $st->execute([':id' => $promoId, ':prov' => $proveedorId]);

        if ($st->rowCount() > 0) {
            mostrarSweetAlert('success', 'Eliminada', 'La promoción fue eliminada.', BASE_URL . '/proveedor/promociones');
        } else {
            mostrarSweetAlert('error', 'No encontrada', 'No se pudo eliminar la promoción.', BASE_URL . '/proveedor/promociones');
        }
    } catch (PDOException $e) {
        error_log('eliminarPromocion: ' . $e->getMessage());
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar.', BASE_URL . '/proveedor/promociones');
    }
    exit;
}

function aceptarSolicitud($id)
{
    if (!$id) {
        mostrarSweetAlert('error', 'Error', 'Solicitud inválida.');
        exit();
    }

    $proveedorUsuarioId = (int)$_SESSION['user']['id'];
    $modelo             = new Solicitud();

    try {
        $resultado = $modelo->aceptar((int)$id, $proveedorUsuarioId);

        if ($resultado) {
            mostrarSweetAlert('success', 'Solicitud aceptada', 'El servicio se marcó como en proceso.', BASE_URL . '/proveedor/nuevas-solicitudes');
        } else {
            mostrarSweetAlert('error', 'Error', 'No se pudo aceptar la solicitud.', BASE_URL . '/proveedor/nuevas-solicitudes');
        }
    } catch (Throwable $e) {
        error_log('aceptarSolicitud error: ' . $e->getMessage());
        mostrarSweetAlert('error', 'Error técnico', 'Ocurrió un problema al procesar la solicitud.', BASE_URL . '/proveedor/nuevas-solicitudes');
    }
    exit();
}

function rechazarSolicitud($id)
{
    if (!$id) {
        mostrarSweetAlert('error', 'Error', 'Solicitud inválida.');
        exit();
    }

    $proveedorUsuarioId = (int)$_SESSION['user']['id'];
    $modelo             = new Solicitud();

    try {
        $resultado = $modelo->rechazar((int)$id, $proveedorUsuarioId);

        if ($resultado) {
            mostrarSweetAlert('success', 'Solicitud rechazada', 'La solicitud fue rechazada.', BASE_URL . '/proveedor/nuevas-solicitudes');
        } else {
            mostrarSweetAlert('error', 'Error', 'No se pudo rechazar la solicitud.', BASE_URL . '/proveedor/nuevas-solicitudes');
        }
    } catch (Throwable $e) {
        error_log('rechazarSolicitud error: ' . $e->getMessage());
        mostrarSweetAlert('error', 'Error técnico', 'Ocurrió un problema al procesar la solicitud.', BASE_URL . '/proveedor/nuevas-solicitudes');
    }
    exit();
}

// =======================================================================
// SECCIÓN 4: RESEÑAS
// =======================================================================

function mostrarResenasProveedor()
{
    $usuarioId      = (int)$_SESSION['user']['id'];
    $modelo         = new Valoracion();
    $resenas        = $modelo->obtenerResenasPorProveedor($usuarioId);
    $totalResenas   = count($resenas);
    $promedio       = 0;
    $conteoEstrellas = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

    if ($totalResenas > 0) {
        $sumaEstrellas = 0;
        foreach ($resenas as $r) {
            $calif = (int)$r['calificacion'];
            $sumaEstrellas += $calif;
            if (isset($conteoEstrellas[$calif])) {
                $conteoEstrellas[$calif]++;
            }
        }
        $promedio = round($sumaEstrellas / $totalResenas, 1);
    }

    $porcentajes = [];
    foreach ($conteoEstrellas as $estrella => $cantidad) {
        $porcentajes[$estrella] = ($totalResenas > 0) ? round(($cantidad / $totalResenas) * 100) : 0;
    }

    require BASE_PATH . '/app/views/dashboard/proveedor/resenas.php';
    exit();
}

function guardarRespuestaProveedor()
{
    $idResena       = (int)($_POST['id_valoracion']    ?? 0);
    $textoRespuesta = trim($_POST['texto_respuesta']   ?? '');
    $usuarioId      = (int)$_SESSION['user']['id'];

    if (!$idResena || empty($textoRespuesta)) {
        mostrarSweetAlert('warning', 'Datos incompletos', 'Escribe una respuesta antes de enviar.', BASE_URL . '/proveedor/resenas');
        exit();
    }

    $exito = (new Valoracion())->responderResena($idResena, $usuarioId, $textoRespuesta);

    if ($exito) {
        mostrarSweetAlert('success', 'Respuesta enviada', 'Tu respuesta fue publicada exitosamente.', BASE_URL . '/proveedor/resenas');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo guardar la respuesta. Intenta nuevamente.', BASE_URL . '/proveedor/resenas');
    }
    exit();
}

// =======================================================================
// SECCIÓN 5: PERFIL Y CONFIGURACIÓN
// =======================================================================

function guardarPerfilProfesional()
{
    $idUsuario = (int)$_SESSION['user']['id'];

    // Cargar perfil existente para usar como fallback en campos vacíos
    $modelo       = new ProveedorPerfil();
    $perfilActual = $modelo->obtenerPerfilPorUsuario($idUsuario) ?: [];

    // Para cada campo: usar el valor del POST si viene, o el que ya estaba en BD
    $nombreComercial  = trim($_POST['nombre_comercial']   ?? '') ?: ($perfilActual['nombre_comercial']   ?? '');
    $tipoProveedor    = trim($_POST['tipo_proveedor']     ?? '') ?: ($perfilActual['tipo_proveedor']     ?? '');
    $eslogan          = trim($_POST['eslogan']            ?? '') ?: ($perfilActual['eslogan']            ?? '');
    $descripcion      = trim($_POST['descripcion']        ?? '') ?: ($perfilActual['descripcion']        ?? '');
    $aniosExp         = trim($_POST['anios_experiencia']  ?? '') ?: ($perfilActual['anios_experiencia']  ?? '');
    $ciudad           = trim($_POST['ciudad']             ?? '') ?: ($perfilActual['ciudad']             ?? '');
    $zona             = trim($_POST['zona']               ?? '') ?: ($perfilActual['zona']               ?? '');
    $telefonoContacto = trim($_POST['telefono_contacto']  ?? '') ?: ($perfilActual['telefono_contacto']  ?? '');
    $whatsapp         = trim($_POST['whatsapp']           ?? '') ?: ($perfilActual['whatsapp']           ?? '');
    $correoAlt        = trim($_POST['correo_alternativo'] ?? '') ?: ($perfilActual['correo_alternativo'] ?? '');

    $idiomasPost  = $_POST['idiomas']    ?? null;
    $categoriasPost = $_POST['categorias'] ?? null;
    $idiomas    = $idiomasPost    ?? (array)($perfilActual['idiomas']    ?? []);
    $categorias = $categoriasPost ?? (array)($perfilActual['categorias'] ?? []);

    // Solo bloquear si siguen faltando campos requeridos incluso con fallback de BD
    if (empty($nombreComercial) || empty($tipoProveedor) || empty($eslogan) || empty($descripcion) || empty($ciudad)) {
        mostrarSweetAlert('error', 'Campos obligatorios', 'Nombre comercial, tipo, eslogan, descripción y ciudad son requeridos.', BASE_URL . '/proveedor/configuracion');
        exit();
    }

    if (empty($categorias)) {
        mostrarSweetAlert('error', 'Categoría requerida', 'Debes seleccionar al menos una categoría.', BASE_URL . '/proveedor/configuracion');
        exit();
    }
    $fotoFinal    = $perfilActual['foto'] ?? 'default_user.png';

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            mostrarSweetAlert('error', 'Formato no válido', 'Solo JPG, PNG o WEBP. Máx. 2MB.', BASE_URL . '/proveedor/configuracion');
            exit();
        }

        if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Imagen demasiado grande', 'La imagen no debe superar 2MB.', BASE_URL . '/proveedor/configuracion');
            exit();
        }

        $nuevoNombre = 'proveedor_' . $idUsuario . '_' . uniqid() . '.' . $ext;
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], BASE_PATH . '/public/uploads/usuarios/' . $nuevoNombre)) {
            mostrarSweetAlert('error', 'Error al subir imagen', 'No se pudo guardar la imagen. Intenta nuevamente.', BASE_URL . '/proveedor/configuracion');
            exit();
        }

        $fotoFinal = $nuevoNombre;
    }

    // Geocodificar ciudad si cambió (o si aún no tiene coordenadas)
    $latitud  = $perfilActual['latitud']  ?? null;
    $longitud = $perfilActual['longitud'] ?? null;
    $ciudadAnterior = $perfilActual['ciudad'] ?? '';
    if ($ciudad !== $ciudadAnterior || ($latitud === null && $longitud === null)) {
        [$latitud, $longitud] = geocodificarCiudad($ciudad);
    }

    $data = [
        'nombre_comercial'   => $nombreComercial,
        'tipo_proveedor'     => $tipoProveedor,
        'eslogan'            => $eslogan,
        'descripcion'        => $descripcion,
        'anios_experiencia'  => ($aniosExp !== '' && is_numeric($aniosExp)) ? (int)$aniosExp : null,
        'idiomas'            => is_array($idiomas)    ? implode(',', $idiomas)    : '',
        'categorias'         => is_array($categorias) ? implode(',', $categorias) : '',
        'ciudad'             => $ciudad,
        'zona'               => $zona,
        'latitud'            => $latitud,
        'longitud'           => $longitud,
        'foto'               => $fotoFinal,
        'telefono_contacto'  => $telefonoContacto,
        'whatsapp'           => $whatsapp,
        'correo_alternativo' => $correoAlt,
    ];

    $ok = $perfilActual ? $modelo->actualizarPerfil($idUsuario, $data) : $modelo->crearPerfil($idUsuario, $data);

    if ($ok) {
        mostrarSweetAlert('success', 'Perfil actualizado', 'Tu perfil profesional se guardó correctamente.', BASE_URL . '/proveedor/configuracion');
    } else {
        mostrarSweetAlert('error', 'Error al guardar', 'No se pudo guardar tu perfil. Intenta nuevamente.', BASE_URL . '/proveedor/configuracion');
    }
    exit();
}

function actualizarCredenciales()
{
    $idUsuario         = (int)$_SESSION['user']['id'];
    $emailNuevo        = trim($_POST['email_nuevo']        ?? '');
    $emailConfirmacion = trim($_POST['email_confirmacion'] ?? '');
    $claveActual       = $_POST['clave_actual']            ?? '';
    $nuevaClave        = $_POST['nueva_clave']             ?? '';
    $confirmarClave    = $_POST['confirmar_clave']         ?? '';
    $cambios           = [];

    if (!empty($emailNuevo)) {
        if (!filter_var($emailNuevo, FILTER_VALIDATE_EMAIL)) {
            mostrarSweetAlert('error', 'Correo inválido', 'Ingresa un correo electrónico válido.', BASE_URL . '/proveedor/configuracion#cuenta');
            exit();
        }
        if ($emailNuevo !== $emailConfirmacion) {
            mostrarSweetAlert('error', 'Correos no coinciden', 'El nuevo correo y su confirmación deben ser iguales.', BASE_URL . '/proveedor/configuracion#cuenta');
            exit();
        }
        $cambios['email'] = $emailNuevo;
    }

    if (!empty($nuevaClave)) {
        if (strlen($nuevaClave) < 8) {
            mostrarSweetAlert('error', 'Contraseña muy corta', 'La nueva contraseña debe tener al menos 8 caracteres.', BASE_URL . '/proveedor/configuracion#cuenta');
            exit();
        }
        if ($nuevaClave !== $confirmarClave) {
            mostrarSweetAlert('error', 'Contraseñas no coinciden', 'La nueva contraseña y su confirmación deben ser iguales.', BASE_URL . '/proveedor/configuracion#cuenta');
            exit();
        }
        $cambios['clave'] = $nuevaClave;
    }

    if (empty($cambios)) {
        mostrarSweetAlert('info', 'Sin cambios', 'No enviaste ningún dato para actualizar.', BASE_URL . '/proveedor/configuracion#cuenta');
        exit();
    }

    if (empty($claveActual)) {
        mostrarSweetAlert('error', 'Contraseña requerida', 'Debes ingresar tu contraseña actual para confirmar los cambios.', BASE_URL . '/proveedor/configuracion#cuenta');
        exit();
    }

    $resultado = (new ProveedorPerfil())->actualizarCredenciales($idUsuario, $claveActual, $cambios);

    switch ($resultado) {
        case 'ok':
            if (!empty($cambios['email'])) $_SESSION['user']['email'] = $cambios['email'];
            mostrarSweetAlert('success', 'Credenciales actualizadas', 'Tus datos de acceso se guardaron correctamente.', BASE_URL . '/proveedor/configuracion#cuenta');
            break;
        case 'clave_incorrecta':
            mostrarSweetAlert('error', 'Contraseña incorrecta', 'La contraseña actual ingresada no es correcta.', BASE_URL . '/proveedor/configuracion#cuenta');
            break;
        case 'email_duplicado':
            mostrarSweetAlert('error', 'Correo en uso', 'El correo ingresado ya está registrado en otra cuenta.', BASE_URL . '/proveedor/configuracion#cuenta');
            break;
        case 'sin_cambios':
            mostrarSweetAlert('info', 'Sin cambios', 'No se detectaron cambios para guardar.', BASE_URL . '/proveedor/configuracion#cuenta');
            break;
        default:
            mostrarSweetAlert('error', 'Error inesperado', 'Ocurrió un problema al guardar. Intenta nuevamente.', BASE_URL . '/proveedor/configuracion#cuenta');
    }
    exit();
}

function actualizarSeguridad()
{
    $idUsuario = (int)$_SESSION['user']['id'];
    $data      = [
        'alerta_solicitudes'   => isset($_POST['alerta_solicitudes']) ? 1 : 0,
        'alerta_resenas'       => isset($_POST['alerta_resenas'])     ? 1 : 0,
        'alerta_pagos'         => isset($_POST['alerta_pagos'])       ? 1 : 0,
        'canal_notificaciones' => $_POST['canal_notificaciones']      ?? 'ambos',
        'tiempo_sesion'        => (int)($_POST['tiempo_sesion']       ?? 60),
    ];

    $ok = (new ProveedorPerfil())->guardarSeguridad($idUsuario, $data);

    if ($ok) {
        mostrarSweetAlert('success', 'Preferencias guardadas', 'Tus preferencias de seguridad se actualizaron correctamente.', BASE_URL . '/proveedor/configuracion#cuenta');
    } else {
        mostrarSweetAlert('error', 'Error al guardar', 'No se pudieron guardar tus preferencias. Intenta nuevamente.', BASE_URL . '/proveedor/configuracion#cuenta');
    }
    exit();
}

function cerrarSesiones()
{
    $_SESSION = [];
    session_unset();
    session_destroy();

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    mostrarSweetAlert('success', 'Sesión cerrada', 'Tu sesión se cerró correctamente.', BASE_URL . '/login');
    exit();
}

function guardarDisponibilidad()
{
    $idUsuario          = (int)$_SESSION['user']['id'];
    $diasTrabajo        = $_POST['dias_trabajo']       ?? [];
    $horaInicio         = trim($_POST['hora_inicio']   ?? '');
    $horaFin            = trim($_POST['hora_fin']      ?? '');
    $atiendeFinesSemana = isset($_POST['atiende_fines_semana']) ? 1 : 0;
    $atiendeFestivos    = isset($_POST['atiende_festivos'])     ? 1 : 0;
    $atencionUrgencias  = isset($_POST['atencion_urgencias'])   ? 1 : 0;
    $detalleUrgencias   = trim($_POST['detalle_urgencias']     ?? '');
    $tipoZona           = $_POST['tipo_zona']          ?? 'ciudad';
    $radioKm            = $_POST['radio_km']           ?? '';
    $zonasTexto         = trim($_POST['zonas_texto']   ?? '');

    if (empty($diasTrabajo)) {
        mostrarSweetAlert('error', 'Días requeridos', 'Selecciona al menos un día de trabajo.', BASE_URL . '/proveedor/configuracion#disponibilidad');
        exit();
    }

    if (empty($horaInicio) || empty($horaFin)) {
        mostrarSweetAlert('error', 'Horario requerido', 'Debes indicar una hora de inicio y una hora de fin.', BASE_URL . '/proveedor/configuracion#disponibilidad');
        exit();
    }

    if ($horaInicio >= $horaFin) {
        mostrarSweetAlert('error', 'Horario inválido', 'La hora de inicio debe ser menor que la hora de fin.', BASE_URL . '/proveedor/configuracion#disponibilidad');
        exit();
    }

    if (!in_array($tipoZona, ['ciudad', 'radio', 'varias_ciudades', 'remoto'], true)) {
        mostrarSweetAlert('error', 'Zona inválida', 'El tipo de zona de servicio seleccionado no es válido.', BASE_URL . '/proveedor/configuracion#disponibilidad');
        exit();
    }

    if ($tipoZona === 'radio' && ($radioKm === '' || !is_numeric($radioKm) || (int)$radioKm <= 0)) {
        mostrarSweetAlert('error', 'Radio inválido', 'Indica un radio en kilómetros mayor a cero.', BASE_URL . '/proveedor/configuracion#disponibilidad');
        exit();
    }

    $data = [
        'dias_trabajo'         => $diasTrabajo,
        'hora_inicio'          => $horaInicio,
        'hora_fin'             => $horaFin,
        'atiende_fines_semana' => $atiendeFinesSemana,
        'atiende_festivos'     => $atiendeFestivos,
        'atencion_urgencias'   => $atencionUrgencias,
        'detalle_urgencias'    => $detalleUrgencias,
        'tipo_zona'            => $tipoZona,
        'radio_km'             => $radioKm,
        'zonas_texto'          => $zonasTexto,
    ];

    $ok = (new ProveedorPerfil())->guardarDisponibilidad($idUsuario, $data);

    if ($ok) {
        mostrarSweetAlert('success', 'Disponibilidad actualizada', 'Tu horario y zona de cobertura se guardaron correctamente.', BASE_URL . '/proveedor/configuracion#disponibilidad');
    } else {
        mostrarSweetAlert('error', 'Error al guardar', 'No se pudo guardar tu disponibilidad. Intenta nuevamente.', BASE_URL . '/proveedor/configuracion#disponibilidad');
    }
    exit();
}

function guardarPoliticas()
{
    $idUsuario              = (int)$_SESSION['user']['id'];
    $tipoCancelacion        = $_POST['tipo_cancelacion']              ?? 'moderada';
    $descripcionCancelacion = trim($_POST['descripcion_cancelacion']  ?? '');
    $permiteReprogramar     = isset($_POST['permite_reprogramar'])    ? 1 : 0;
    $horasMinReprogramacion = trim($_POST['horas_min_reprogramacion'] ?? '');
    $cobraVisita            = isset($_POST['cobra_visita'])           ? 1 : 0;
    $valorVisita            = trim($_POST['valor_visita']             ?? '');
    $ofreceGarantia         = isset($_POST['ofrece_garantia'])        ? 1 : 0;
    $diasGarantia           = trim($_POST['dias_garantia']           ?? '');
    $detallesGarantia       = trim($_POST['detalles_garantia']       ?? '');
    $soloContactoPlataforma = isset($_POST['solo_contacto_por_plataforma']) ? 1 : 0;
    $tiempoRespuesta        = trim($_POST['tiempo_respuesta_promedio'] ?? '');
    $otrasCondiciones       = trim($_POST['otras_condiciones']        ?? '');

    if (!in_array($tipoCancelacion, ['flexible', 'moderada', 'estricta'], true)) {
        mostrarSweetAlert('error', 'Política inválida', 'El tipo de política de cancelación seleccionado no es válido.', BASE_URL . '/proveedor/configuracion#politicas');
        exit();
    }

    if ($permiteReprogramar && $horasMinReprogramacion !== '' && (!is_numeric($horasMinReprogramacion) || (int)$horasMinReprogramacion < 0)) {
        mostrarSweetAlert('error', 'Horas inválidas', 'Las horas mínimas para reprogramar deben ser un número igual o mayor a cero.', BASE_URL . '/proveedor/configuracion#politicas');
        exit();
    }

    if ($cobraVisita && ($valorVisita === '' || !is_numeric($valorVisita) || (float)$valorVisita <= 0)) {
        mostrarSweetAlert('error', 'Valor de visita requerido', 'Si cobras por visita, indica un valor válido mayor a cero.', BASE_URL . '/proveedor/configuracion#politicas');
        exit();
    }

    if ($ofreceGarantia && ($diasGarantia === '' || !is_numeric($diasGarantia) || (int)$diasGarantia <= 0)) {
        mostrarSweetAlert('error', 'Días de garantía requeridos', 'Si ofreces garantía, indica un número de días mayor a cero.', BASE_URL . '/proveedor/configuracion#politicas');
        exit();
    }

    if (strlen($tiempoRespuesta) > 50) {
        mostrarSweetAlert('error', 'Texto demasiado largo', 'El tiempo de respuesta promedio no puede superar 50 caracteres.', BASE_URL . '/proveedor/configuracion#politicas');
        exit();
    }

    $data = [
        'tipo_cancelacion'             => $tipoCancelacion,
        'descripcion_cancelacion'      => $descripcionCancelacion,
        'permite_reprogramar'          => $permiteReprogramar,
        'horas_min_reprogramacion'     => $horasMinReprogramacion,
        'cobra_visita'                 => $cobraVisita,
        'valor_visita'                 => $valorVisita,
        'ofrece_garantia'              => $ofreceGarantia,
        'dias_garantia'                => $diasGarantia,
        'detalles_garantia'            => $detallesGarantia,
        'solo_contacto_por_plataforma' => $soloContactoPlataforma,
        'tiempo_respuesta_promedio'    => $tiempoRespuesta,
        'otras_condiciones'            => $otrasCondiciones,
    ];

    $ok = (new ProveedorPerfil())->guardarPoliticas($idUsuario, $data);

    if ($ok) {
        mostrarSweetAlert('success', 'Políticas actualizadas', 'Tus políticas de servicio se guardaron correctamente.', BASE_URL . '/proveedor/configuracion#politicas');
    } else {
        mostrarSweetAlert('error', 'Error al guardar', 'No se pudieron guardar tus políticas. Intenta nuevamente.', BASE_URL . '/proveedor/configuracion#politicas');
    }
    exit();
}

function guardarNotificaciones()
{
    $idUsuario = (int)$_SESSION['user']['id'];
    $data      = [
        'noti_solicitudes_nuevas' => isset($_POST['noti_solicitudes_nuevas']) ? 1 : 0,
        'noti_cambios_estado'     => isset($_POST['noti_cambios_estado'])     ? 1 : 0,
        'noti_resenas'            => isset($_POST['noti_resenas'])            ? 1 : 0,
        'noti_pagos'              => isset($_POST['noti_pagos'])              ? 1 : 0,
        'canal_email'             => isset($_POST['canal_email'])             ? 1 : 0,
        'canal_interna'           => isset($_POST['canal_interna'])           ? 1 : 0,
        'canal_whatsapp'          => isset($_POST['canal_whatsapp'])          ? 1 : 0,
        'resumen_diario'          => isset($_POST['resumen_diario'])          ? 1 : 0,
        'resumen_semanal'         => isset($_POST['resumen_semanal'])         ? 1 : 0,
    ];

    $ok = (new ProveedorNotificaciones())->guardarDesdeFormulario($idUsuario, $data);

    if ($ok) {
        mostrarSweetAlert('success', 'Notificaciones actualizadas', 'Tus preferencias de notificación se guardaron correctamente.', BASE_URL . '/proveedor/configuracion#notificaciones');
    } else {
        mostrarSweetAlert('error', 'Error al guardar', 'No se pudieron guardar tus preferencias. Intenta nuevamente.', BASE_URL . '/proveedor/configuracion#notificaciones');
    }
    exit();
}

function guardarPagos()
{
    $idUsuario             = (int)$_SESSION['user']['id'];
    $tipoDocumento         = trim($_POST['tipo_documento']         ?? '');
    $numeroDocumento       = trim($_POST['numero_documento']       ?? '');
    $razonSocial           = trim($_POST['razon_social']           ?? '');
    $regimenFiscal         = trim($_POST['regimen_fiscal']         ?? '');
    $direccionFacturacion  = trim($_POST['direccion_facturacion']  ?? '');
    $ciudadFacturacion     = trim($_POST['ciudad_facturacion']     ?? '');
    $paisFacturacion       = trim($_POST['pais_facturacion']       ?? 'Colombia');
    $correoFacturacion     = trim($_POST['correo_facturacion']     ?? '');
    $telefonoFacturacion   = trim($_POST['telefono_facturacion']   ?? '');
    $banco                 = trim($_POST['banco']                  ?? '');
    $tipoCuenta            = trim($_POST['tipo_cuenta']            ?? '');
    $numeroCuenta          = trim($_POST['numero_cuenta']          ?? '');
    $titularCuenta         = trim($_POST['titular_cuenta']         ?? '');
    $identificacionTitular = trim($_POST['identificacion_titular'] ?? '');
    $metodoPagoPreferido   = trim($_POST['metodo_pago_preferido']  ?? '');
    $notaMetodoPago        = trim($_POST['nota_metodo_pago']       ?? '');
    $frecuenciaLiquidacion    = trim($_POST['frecuencia_liquidacion']      ?? '');
    $montoMinimoRetiro        = trim($_POST['monto_minimo_retiro']         ?? '');
    $aceptaFacturaElectronica = isset($_POST['acepta_factura_electronica']) ? 1 : 0;

    if (
        empty($tipoDocumento) || empty($numeroDocumento) || empty($razonSocial) ||
        empty($direccionFacturacion) || empty($ciudadFacturacion) || empty($paisFacturacion) ||
        empty($correoFacturacion)
    ) {
        mostrarSweetAlert('error', 'Campos obligatorios', 'Completa todos los campos requeridos de facturación.', BASE_URL . '/proveedor/configuracion#pagos');
        exit();
    }

    if (!filter_var($correoFacturacion, FILTER_VALIDATE_EMAIL)) {
        mostrarSweetAlert('error', 'Correo inválido', 'El correo de facturación no tiene un formato válido.', BASE_URL . '/proveedor/configuracion#pagos');
        exit();
    }

    if ($montoMinimoRetiro !== '' && (!is_numeric($montoMinimoRetiro) || (float)$montoMinimoRetiro < 0)) {
        mostrarSweetAlert('error', 'Monto inválido', 'El monto mínimo de retiro debe ser un número igual o mayor a cero.', BASE_URL . '/proveedor/configuracion#pagos');
        exit();
    }

    $data = [
        'tipo_documento'             => $tipoDocumento,
        'numero_documento'           => $numeroDocumento,
        'razon_social'               => $razonSocial,
        'regimen_fiscal'             => $regimenFiscal        ?: null,
        'direccion_facturacion'      => $direccionFacturacion,
        'ciudad_facturacion'         => $ciudadFacturacion,
        'pais_facturacion'           => $paisFacturacion,
        'correo_facturacion'         => $correoFacturacion,
        'telefono_facturacion'       => $telefonoFacturacion  ?: null,
        'banco'                      => $banco                ?: null,
        'tipo_cuenta'                => $tipoCuenta           ?: null,
        'numero_cuenta'              => $numeroCuenta         ?: null,
        'titular_cuenta'             => $titularCuenta        ?: null,
        'identificacion_titular'     => $identificacionTitular ?: null,
        'metodo_pago_preferido'      => $metodoPagoPreferido  ?: null,
        'nota_metodo_pago'           => $notaMetodoPago       ?: null,
        'frecuencia_liquidacion'     => $frecuenciaLiquidacion ?: null,
        'monto_minimo_retiro'        => $montoMinimoRetiro !== '' ? $montoMinimoRetiro : null,
        'acepta_factura_electronica' => $aceptaFacturaElectronica,
    ];

    $ok = (new ProveedorPagosFacturacion())->guardarDesdeFormulario($idUsuario, $data);

    if ($ok) {
        mostrarSweetAlert('success', 'Facturación guardada', 'Tu información de pagos y facturación se guardó correctamente.', BASE_URL . '/proveedor/configuracion#pagos');
    } else {
        mostrarSweetAlert('error', 'Error al guardar', 'No se pudo guardar tu información de pagos. Intenta nuevamente.', BASE_URL . '/proveedor/configuracion#pagos');
    }
    exit();
}

// =======================================================================
// SECCIÓN 6: VALIDACIÓN DE PERFIL HABILITADO
// =======================================================================

/**
 * Verifica que el proveedor cumpla los requisitos mínimos para operar.
 * Devuelve null si todo está OK, o el mensaje de bloqueo si no.
 */
function verificarPerfilHabilitado(int $usuarioId): ?string
{
    try {
        $db  = new Conexion();
        $pdo = $db->getConexion();

        // 1. Cuenta activa
        $stmt = $pdo->prepare(
            "SELECT u.estado_id, ue.nombre AS estado_nombre
             FROM usuarios u
             LEFT JOIN usuario_estados ue ON u.estado_id = ue.id
             WHERE u.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $usuarioId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || (int)$user['estado_id'] !== 2) {
            $estado = $user['estado_nombre'] ?? 'pendiente';
            return "Tu cuenta está en estado «{$estado}». El administrador debe activarla antes de que puedas operar.";
        }

        // 2. Al menos una categoría
        $stmt2 = $pdo->prepare(
            "SELECT COUNT(*) FROM proveedor_categorias pc
             JOIN proveedores p ON pc.proveedor_id = p.id
             WHERE p.usuario_id = :uid"
        );
        $stmt2->execute([':uid' => $usuarioId]);
        if ((int)$stmt2->fetchColumn() === 0) {
            return "Debes tener al menos una categoría/habilidad registrada en tu perfil profesional.";
        }

        // 3. Al menos un documento aprobado
        $stmt3 = $pdo->prepare(
            "SELECT COUNT(*) FROM documentos_proveedor dp
             JOIN proveedores p ON dp.proveedor_id = p.id
             WHERE p.usuario_id = :uid AND dp.estado = 'aprobado'"
        );
        $stmt3->execute([':uid' => $usuarioId]);
        if ((int)$stmt3->fetchColumn() === 0) {
            return "Necesitas al menos un documento aprobado por el administrador para publicar o cotizar servicios.";
        }

        return null;
    } catch (PDOException $e) {
        error_log("Error en verificarPerfilHabilitado: " . $e->getMessage());
        return "No se pudo verificar el estado de tu perfil. Intenta nuevamente.";
    }
}

// =======================================================================
// SECCIÓN 7: DASHBOARD
// =======================================================================

function mostrarDashboardProveedor()
{
    $usuarioId = (int)$_SESSION['user']['id'];

    $servicioModel  = new ServicioContratado();
    $solicitudModel = new Solicitud();

    $resumen               = $servicioModel->obtenerResumenDashboardProveedor($usuarioId);
    $solicitudesPendientes = $solicitudModel->contarPendientesProveedor($usuarioId);
    $serviciosRecientes    = $servicioModel->obtenerServiciosRecientesProveedor($usuarioId, 4);
    $resenasRecientes      = $servicioModel->obtenerResenasRecientesProveedor($usuarioId, 5);
    $proximasCitas         = $servicioModel->obtenerProximasCitasProveedor($usuarioId, 5);

    require_once BASE_PATH . '/app/views/dashboard/proveedor/dashboard-proveedor.php';
    exit();
}

// ===================================================================
// FUNCIÓN — COMPROBANTE PDF DE CONTRATO
// ===================================================================

function generarComprobantePDFProveedor()
{
    $usuarioId  = (int)$_SESSION['user']['id'];
    $contratoId = isset($_GET['contrato_id']) ? (int)$_GET['contrato_id'] : 0;

    if ($contratoId <= 0) {
        mostrarSweetAlert('error', 'No encontrado', 'El comprobante no existe.', BASE_URL . '/proveedor/nuevas-solicitudes');
        exit();
    }

    $scModel  = new ServicioContratado();
    $contrato = $scModel->obtenerDetalleParaPDF($contratoId, $usuarioId, 'proveedor');

    if (empty($contrato)) {
        mostrarSweetAlert('error', 'Acceso denegado', 'No tienes permiso para ver este comprobante.', BASE_URL . '/proveedor/nuevas-solicitudes');
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
