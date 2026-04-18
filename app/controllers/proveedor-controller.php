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

        // ✅ CORREGIDO: elseif para evitar múltiples ejecuciones
        if ($accion === 'eliminar') {
            eliminarServicio($_GET['id'] ?? null);
        } elseif ($accion === 'ver_solicitudes') {
            $listaSolicitudes = obtenerSolicitudesProveedor();
        } elseif (isset($_GET['id'])) {
            mostrarServicioId($_GET['id']);
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
    $id             = (int)($_POST['id'] ?? 0);
    $nombre         = trim($_POST['nombre'] ?? '');
    $id_categoria   = (int)($_POST['id_categoria'] ?? 0);
    $disponibilidad = $_POST['disponibilidad'] ?? '';
    $descripcion    = trim($_POST['descripcion'] ?? '');
    $precio         = $_POST['precio'] ?? '';

    if ($id <= 0 || $nombre === '' || $id_categoria <= 0 || $disponibilidad === '') {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos obligatorios.');
        exit();
    }

    // Manejo de imagen en edición
    $ruta_img = $_POST['imagen_actual'] ?? null;

    if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file      = $_FILES['imagen'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidas = ['png', 'jpg', 'jpeg'];

        if (in_array($extension, $permitidas) && $file['size'] <= 2 * 1024 * 1024) {
            $nuevo_nombre = uniqid('servicio_') . '.' . $extension;
            $destino      = BASE_PATH . '/public/uploads/servicios/' . $nuevo_nombre;

            if (move_uploaded_file($file['tmp_name'], $destino)) {
                // Borrar imagen anterior si no es la default
                if ($ruta_img && $ruta_img !== 'default_service.png') {
                    $ruta_anterior = BASE_PATH . '/public/uploads/servicios/' . $ruta_img;
                    if (file_exists($ruta_anterior)) unlink($ruta_anterior);
                }
                $ruta_img = $nuevo_nombre;
            }
        }
    }

    $objServicio = new Servicio();
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
        // ✅ CORREGIDO: BASE_URL
        mostrarSweetAlert('success', 'Servicio actualizado', 'Los datos se actualizaron correctamente.', BASE_URL . '/proveedor/listar-servicio');
    } else {
        mostrarSweetAlert('error', 'Error al actualizar', 'No se pudo actualizar el servicio. Intenta nuevamente.');
    }

    exit();
}

function eliminarServicio($id)
{
    if (!$id) {
        mostrarSweetAlert('error', 'Error', 'ID inválido.');
        exit();
    }

    $objServicio = new Servicio();
    $respuesta   = $objServicio->eliminar((int)$id);

    if ($respuesta === true) {
        // ✅ CORREGIDO: BASE_URL
        mostrarSweetAlert('success', 'Eliminación exitosa', 'El servicio ha sido eliminado.', BASE_URL . '/proveedor/listar-servicio');
    } else {
        mostrarSweetAlert('error', 'Error al eliminar', 'No se pudo eliminar el servicio.');
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
