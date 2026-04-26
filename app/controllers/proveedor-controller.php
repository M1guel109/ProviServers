<?php
require_once __DIR__ . '/../helpers/alert-helper.php';
require_once __DIR__ . '/../models/servicio.php';
require_once __DIR__ . '/../models/publicacion.php';
require_once __DIR__ . '/../models/solicitud.php';
require_once __DIR__ . '/../models/categoria.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ CORREGIDO: $method declarado una sola vez
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'actualizar') {
            actualizarServicio();
        } else {
            registrarServicio();
        }
        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';
        $id     = $_GET['id']     ?? null;

        // ✅ Acciones específicas (eliminada 'detalle' — eso va al cliente-controller)
        if ($accion === 'eliminar') {
            eliminarServicio($id);
        } elseif ($accion === 'pausar') {
            cambiarEstadoServicio($id, 0);  // ✅ unificado
        } elseif ($accion === 'reanudar') {
            cambiarEstadoServicio($id, 1);  // ✅ unificado
        } elseif ($accion === 'ver_solicitudes') {
            $listaSolicitudes = obtenerSolicitudesProveedor();
        } // Agregar al final del switch case 'GET'
        elseif ($accion === 'detalle') {
            obtenerDetallePublicacionJSON($_GET['id'] ?? null);
        } elseif ($id) {
            mostrarServicioId($id);
        } else {
            mostrarServicios();
        }
        break;

    default:
        http_response_code(405);
        echo "Método no permitido";
        break;
}

// =========================================================
// FUNCIONES
// =========================================================

function registrarServicio()
{
    if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'proveedor') {
        header('Location: ' . BASE_URL . '/login');
        exit();
    }

    $usuarioId = (int)($_SESSION['user']['id'] ?? 0);
    if ($usuarioId <= 0) {
        mostrarSweetAlert('error', 'Sesión inválida', 'No se encontró el usuario en sesión.');
        exit();
    }

    $nombre         = trim($_POST['nombre'] ?? '');
    $id_categoria   = (int)($_POST['id_categoria'] ?? 0);
    $precio         = $_POST['precio'] ?? '';
    $disponibilidad = isset($_POST['disponibilidad']) ? (int)$_POST['disponibilidad'] : null;
    $descripcion    = trim($_POST['descripcion'] ?? '');

    // Validaciones
    if ($nombre === '' || $id_categoria <= 0 || $precio === '' || !is_numeric($precio) || !in_array($disponibilidad, [0, 1], true)) {
        mostrarSweetAlert('error', 'Campos inválidos', 'Completa correctamente todos los campos obligatorios.');
        exit();
    }

    if ((float)$precio < 0) {
        mostrarSweetAlert('error', 'Precio inválido', 'El precio no puede ser menor que cero.');
        exit();
    }

    // Manejo de imagen
    $ruta_img = 'default_service.png';

    if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file      = $_FILES['imagen'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidas = ['png', 'jpg', 'jpeg'];

        if (!in_array($extension, $permitidas)) {
            mostrarSweetAlert('error', 'Extensión no permitida', 'Por favor carga una imagen PNG, JPG o JPEG.');
            exit();
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Imagen muy pesada', 'El peso de la imagen supera el límite de 2MB.');
            exit();
        }

        $ruta_img = uniqid('servicio_') . '.' . $extension;
        $destino  = BASE_PATH . '/public/uploads/servicios/' . $ruta_img;

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            mostrarSweetAlert('error', 'Error de imagen', 'No se pudo guardar la imagen.');
            exit();
        }
    }

    $objServicio = new Servicio();

    $dataServicio = [
        'nombre'        => $nombre,
        'descripcion'   => $descripcion,
        'precio'        => number_format((float)$precio, 2, '.', ''),
        'id_categoria'  => $id_categoria,
        'imagen'        => $ruta_img,
        'disponibilidad' => $disponibilidad,
    ];

    $resultado = $objServicio->registrar($dataServicio);

    // Reemplaza el bloque del if ($resultado === true) en registrarServicio()

    if ($resultado === true) {
        $servicioId = $objServicio->getUltimoIdInsertado();

        $publicacionCreada = false;
        $esVip             = false;

        try {
            $publicacionModel  = new Publicacion();
            $publicacionCreada = $publicacionModel->crearParaServicioDeProveedor(
                $usuarioId,
                $servicioId,
                [
                    'nombre'      => $nombre,
                    'descripcion' => $descripcion,
                    'precio'      => $dataServicio['precio']
                ]
            );

            // Verificar si el proveedor es VIP para personalizar el mensaje
            if ($publicacionCreada) {
                require_once BASE_PATH . '/app/models/publicacion.php';
                $db       = new Conexion();
                $conn     = $db->getConexion();
                $stmtVip  = $conn->prepare("SELECT auto_aprobacion_activa FROM proveedores WHERE usuario_id = :uid LIMIT 1");
                $stmtVip->execute([':uid' => $usuarioId]);
                $prov     = $stmtVip->fetch(PDO::FETCH_ASSOC);
                $esVip    = (int)($prov['auto_aprobacion_activa'] ?? 0) === 1;
            }
        } catch (Exception $e) {
            error_log("Error al crear publicación para el servicio {$servicioId}: " . $e->getMessage());
        }

        // ✅ Mensaje personalizado según reputación
        if ($esVip) {
            mostrarSweetAlert(
                'success',
                '¡Servicio publicado!',
                'Tu servicio fue aprobado automáticamente por tu historial de confianza.',
                BASE_URL . '/proveedor/listar-servicio'
            );
        } else {
            mostrarSweetAlert(
                'success',
                'Servicio registrado',
                'Tu servicio está en revisión. El equipo lo aprobará en las próximas horas.',
                BASE_URL . '/proveedor/listar-servicio'
            );
        }
    } else {
        mostrarSweetAlert('error', 'Error al registrar', 'No se pudo registrar el servicio. Intenta nuevamente.');
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

/**
 * Retorna las categorías disponibles — usado por la vista registrar-servicio.php
 */
function obtenerCategorias()
{
    $obj = new Categoria();
    return $obj->mostrar();
}

function actualizarServicio()
{
    // 1. Obtener y validar datos básicos
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
    $ruta_img = $_POST['imagen_actual'] ?? 'default_service.png'; // Valor por defecto

    // 2. Manejo de archivo (Solo si el usuario seleccionó uno nuevo)
    if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagen'];

        // Validación técnica de tipo MIME (más segura que solo extensión)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $permitidos = ['image/jpeg', 'image/png', 'image/jpg'];

        if (!in_array($mime, $permitidos)) {
            mostrarSweetAlert('error', 'Tipo no permitido', 'Solo se aceptan imágenes JPG o PNG.');
            exit();
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Archivo grande', 'El límite es 2MB.');
            exit();
        }

        // Generar nombre y mover
        $nuevo_nombre = uniqid('servicio_') . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $destino = BASE_PATH . '/public/uploads/servicios/' . $nuevo_nombre;

        if (move_uploaded_file($file['tmp_name'], $destino)) {
            // Borrar imagen vieja solo si logramos subir la nueva
            if ($ruta_img && $ruta_img !== 'default_service.png' && file_exists(BASE_PATH . '/public/uploads/servicios/' . $ruta_img)) {
                unlink(BASE_PATH . '/public/uploads/servicios/' . $ruta_img);
            }
            $ruta_img = $nuevo_nombre;
        } else {
            mostrarSweetAlert('error', 'Error servidor', 'No se pudo guardar la imagen.');
            exit();
        }
    }

    // 3. Ejecutar actualización
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
    // ✅ Validación de ID
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

    // Borrar imagen física si no es la default
    $img = $servicioActual['imagen'] ?? '';
    if ($img && $img !== 'default_service.png') {
        $ruta = BASE_PATH . '/public/uploads/servicios/' . $img;
        if (file_exists($ruta)) unlink($ruta);
    }

    if ($objServicio->eliminar($id)) {
        mostrarSweetAlert(
            'success',
            'Eliminado',
            'Servicio eliminado con éxito.',
            BASE_URL . '/proveedor/listar-servicio'
        );
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar el registro.');
    }
    exit();
}

function obtenerSolicitudesProveedor()
{
    if (!isset($_SESSION['user']['id'])) return [];
    $usuarioId     = (int)$_SESSION['user']['id'];
    $solicitudModel = new Solicitud();
    return $solicitudModel->listarPorProveedor($usuarioId);
}

/**
 * Retorna los servicios/publicaciones del proveedor logueado.
 * Usada por mis-servicios.php
 */
function obtenerServiciosDelProveedor(int $usuarioId): array
{
    require_once BASE_PATH . '/app/models/publicacion.php';
    $obj = new Publicacion();
    return $obj->listarPorProveedorUsuario($usuarioId);
}

/**
 * ✅ Función unificada para pausar y reanudar un servicio.
 * @param int $id ID del servicio
 * @param int $estado 1 = reanudar, 0 = pausar
 */
function cambiarEstadoServicio($id, int $estado)
{
    $id = (int)$id;
    if ($id <= 0) {
        mostrarSweetAlert('error', 'Error', 'ID de servicio inválido.');
        exit();
    }

    $objServicio = new Servicio();
    $datos       = $objServicio->obtenerDetalleCompleto($id);

    // Solo se puede actuar sobre servicios aprobados
    if (!$datos || ($datos['publicacion_estado'] ?? '') !== 'aprobado') {
        mostrarSweetAlert(
            'warning',
            'Acción no permitida',
            'Solo puedes pausar o reanudar servicios aprobados.'
        );
        exit();
    }

    $accionTxt = $estado === 1 ? 'reanudado' : 'pausado';
    $mensaje   = $estado === 1
        ? 'Tu servicio es visible nuevamente para los clientes.'
        : 'Tu servicio ya no es visible para los clientes.';

    if ($objServicio->cambiarDisponibilidad($id, $estado)) {
        mostrarSweetAlert(
            'success',
            "Servicio {$accionTxt}",
            $mensaje,
            BASE_URL . '/proveedor/listar-servicio'
        );
    } else {
        mostrarSweetAlert('error', 'Error', "No se pudo actualizar el servicio.");
    }
    exit();
}

/**
 * Retorna los datos de una publicación en formato JSON para el modal de detalle del cliente.
 */
function obtenerDetallePublicacionJSON($id)
{
    header('Content-Type: application/json');

    $id = (int)$id;
    if ($id <= 0) {
        echo json_encode(['error' => 'ID no proporcionado']);
        exit();
    }

    $objPublicacion = new Publicacion();
    $detalle        = $objPublicacion->obtenerDetallePublicacion($id);

    if ($detalle) {
        echo json_encode($detalle);
    } else {
        echo json_encode(['error' => 'Publicación no encontrada']);
    }
    exit();
}