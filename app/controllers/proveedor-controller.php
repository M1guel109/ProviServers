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
        $id = $_GET['id'] ?? null;

        // 1. Primero las acciones específicas
        if ($accion === 'pausar') {
            pausarServicio($id);
        } elseif ($accion === 'eliminar') {
            eliminarServicio($id);
        } elseif ($accion === 'ver_solicitudes') {
            $listaSolicitudes = obtenerSolicitudesProveedor();
        } elseif ($accion === 'reanudar') {
            reanudarServicio($_GET['id'] ?? null);
        }
        // 2. Solo después las acciones genéricas si no hubo una acción específica
        elseif ($id) {
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
    $objServicio = new Servicio();

    // 1. OBTENER INFORMACIÓN DEL SERVICIO ANTES DE BORRAR
    $servicioActual = $objServicio->mostrarId($id);


    // 3. BORRAR EL ARCHIVO FÍSICO (Si no es el default)
    $img_a_borrar = $servicioActual['imagen'] ?? '';
    if ($img_a_borrar && $img_a_borrar !== 'default_service.png') {
        $ruta_archivo = BASE_PATH . '/public/uploads/servicios/' . $img_a_borrar;
        if (file_exists($ruta_archivo)) {
            unlink($ruta_archivo);
        }
    }

    // 4. ELIMINAR REGISTRO EN BASE DE DATOS
    if ($objServicio->eliminar($id)) {
        mostrarSweetAlert('success', 'Eliminado', 'Servicio eliminado con éxito.', BASE_URL . '/proveedor/listar-servicio');
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

function pausarServicio($id)
{
    $id = (int)$id;
    $objServicio = new Servicio();

    // 1. Obtener datos para verificar el estado de la publicación
    $datosServicio = $objServicio->obtenerDetalleCompleto($id);

    // 2. Validación de seguridad y lógica de negocio
    // Solo permitimos pausar si la publicación está "aprobado"
    if (!$datosServicio || $datosServicio['publicacion_estado'] !== 'aprobado') {
        mostrarSweetAlert('error', 'Acción no permitida', 'Solo puedes pausar servicios que ya han sido aprobados.');
        exit();
    }

    // 3. Ejecutar la pausa (ponemos disponibilidad en 0)
    if ($objServicio->cambiarDisponibilidad($id, 0)) {
        mostrarSweetAlert('success', 'Servicio pausado', 'Tu servicio ya no es visible para los clientes.', BASE_URL . '/proveedor/listar-servicio');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo pausar el servicio.');
    }
    exit();
}


function reanudarServicio($id) {
    $id = (int)$id;
    $objServicio = new Servicio();

    // 1. Obtener datos para verificar el estado de la publicación
    $datosServicio = $objServicio->obtenerDetalleCompleto($id);

    // 2. Validación de seguridad y lógica de negocio
    // Solo permitimos reanudar si la publicación está "aprobado"
    if (!$datosServicio || $datosServicio['publicacion_estado'] !== 'aprobado') {
        mostrarSweetAlert('error', 'Acción no permitida', 'Solo puedes reanudar servicios que ya han sido aprobados.');
        exit();
    }

    // 3. Ejecutar la reanudación (ponemos disponibilidad en 1)
    if ($objServicio->reanudarDisponibilidad($id)) {
        mostrarSweetAlert('success', 'Servicio reanudado', 'Tu servicio es visible nuevamente para los clientes.', BASE_URL . '/proveedor/listar-servicio');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo reanudar el servicio.');
    }
    exit();
}