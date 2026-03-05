<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/servicio.php';
require_once __DIR__ . '/../models/Publicacion.php';
require_once __DIR__ . '/../models/solicitud.php';

// Iniciar sesión para poder leer $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Capturamos en una variable el método o solicitud hecha al servidor
$method = $_SERVER['REQUEST_METHOD'];

// Capturamos en una variable el método o solicitud hecha al servidor
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'actualizar') {
            actualizarServicio();
        } else {
            // Por defecto asumimos que es registro
            registrarServicio();
        }

        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';

        if ($accion === 'eliminar') {
            eliminarServicio($_GET['id']);
        }

        // Si la URL pide explícitamente solicitudes (puedes ajustar este parámetro según tu router)
        if ($accion === 'ver_solicitudes') {
            $listaSolicitudes = obtenerSolicitudesProveedor();
            // Aquí no hacemos return, usualmente aquí se incluiría la vista o se pasaría la variable
        }

        if (isset($_GET['id'])) {
            mostrarServicioId($_GET['id']);
        } else {
            mostrarServicios();
        }

        break;

    default:
        http_response_code(405);
        echo "Metodo no permitido";
        break;
}

/* ============================
 *   FUNCIONES DEL CRUD
 * ============================ */

function registrarServicio()
{
    if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'proveedor') {
        header('Location: ' . BASE_URL . '/login');
        exit();
    }

    $usuarioId = (int) ($_SESSION['user']['id'] ?? 0);
    if ($usuarioId <= 0) {
        mostrarSweetAlert('error', 'Sesión inválida', 'No se encontró el usuario en sesión.');
        exit();
    }

    $nombre         = trim($_POST['nombre'] ?? '');
    $id_categoria   = (int) ($_POST['id_categoria'] ?? 0);
    $precio         = $_POST['precio'] ?? '';
    $disponibilidad = isset($_POST['disponibilidad']) ? (int) $_POST['disponibilidad'] : null;
    $descripcion    = trim($_POST['descripcion'] ?? '');

    if ($nombre === '' || $id_categoria <= 0 || $precio === '' || !is_numeric($precio) || !in_array($disponibilidad, [0, 1], true)) {
        mostrarSweetAlert('error', 'Campos inválidos', 'Completa correctamente todos los campos obligatorios.');
        exit();
    }

    if ((float)$precio < 0) {
        mostrarSweetAlert('error', 'Precio inválido', 'El precio no puede ser menor que cero.');
        exit();
    }

    $ruta_img = null;

    if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagen'];

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidas = ['png', 'jpg', 'jpeg'];

        if (!in_array($extension, $permitidas)) {
            mostrarSweetAlert('error', 'Extensión no permitida', 'Por favor, carga una imagen PNG, JPG o JPEG.');
            exit();
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Error al cargar la imagen', 'El peso de la imagen supera el límite de 2MB.');
            exit();
        }

        $ruta_img = uniqid('servicio_') . '.' . $extension;
        $destino = BASE_PATH . "/public/uploads/servicios/" . $ruta_img;

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            mostrarSweetAlert('error', 'Error de imagen', 'No se pudo guardar la imagen.');
            exit();
        }
    } else {
        $ruta_img = "default_service.png";
    }

    $objServicio = new Servicio();

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
        $servicioId = $objServicio->getUltimoIdInsertado();

        try {
            $publicacionModel = new Publicacion();

            $publicacionCreada = $publicacionModel->crearParaServicioDeProveedor(
                $usuarioId,
                $servicioId,
                [
                    'nombre'      => $nombre,
                    'descripcion' => $descripcion,
                    'precio'      => $dataServicio['precio']
                ]
            );

            if (!$publicacionCreada) {
                error_log("No se pudo crear la publicación del servicio {$servicioId}");
            }
        } catch (Exception $e) {
            error_log("Error al crear publicación para el servicio {$servicioId}: " . $e->getMessage());
        }

        mostrarSweetAlert(
            'success',
            'Servicio registrado con éxito',
            'Se ha creado un nuevo servicio y su publicación.',
            '/ProviServers/proveedor/registrar-servicio'
        );
    } else {
        mostrarSweetAlert(
            'error',
            'Error al registrar',
            'No se pudo registrar el servicio. Intenta nuevamente.'
        );
    }

    exit();
}

function mostrarServicios()
{
    $objServicio = new Servicio();
    $servicios = $objServicio->mostrar();

    return $servicios;
}

function mostrarServicioId($id)
{
    $objServicio = new Servicio();
    $servicio = $objServicio->mostrarId($id);

    return $servicio;
}

function actualizarServicio()
{
    // Aquí puedes adaptar según el formulario que uses para editar servicios
    $id            = $_POST['id']            ?? '';
    $nombre        = $_POST['nombre']        ?? '';
    $id_categoria  = $_POST['id_categoria']  ?? '';
    $disponibilidad = $_POST['disponibilidad'] ?? '';
    $descripcion   = $_POST['descripcion']   ?? '';

    if (empty($id) || empty($nombre) || empty($id_categoria) || $disponibilidad === '') {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos obligatorios.');
        exit();
    }

    $objServicio = new Servicio();
    $data = [
        'id'            => $id,
        'nombre'        => $nombre,
        'id_categoria'  => $id_categoria,
        'disponibilidad' => $disponibilidad,
        'descripcion'   => $descripcion,
        // 'imagen'      => ...,  // si luego manejas actualización de imagen
    ];

    $resultado = $objServicio->actualizar($data);

    if ($resultado === true) {
        mostrarSweetAlert(
            'success',
            'Servicio actualizado con éxito',
            'Los datos del servicio se han actualizado correctamente.',
            '/ProviServers/proveedor/listar-servicio'
        );
    } else {
        mostrarSweetAlert(
            'error',
            'Error al actualizar',
            'No se pudo actualizar el servicio. Intenta nuevamente.'
        );
    }

    exit();
}

function eliminarServicio($id)
{
    $objServicio = new Servicio();
    $respuesta = $objServicio->eliminar($id);

    if ($respuesta === true) {
        mostrarSweetAlert(
            'success',
            'Eliminación exitosa',
            'Se ha eliminado el servicio.',
            '/ProviServers/proveedor/listar-servicio'
        );
    } else {
        mostrarSweetAlert(
            'error',
            'Error al eliminar',
            'No se pudo eliminar el servicio. Intenta nuevamente.'
        );
    }
}

function obtenerSolicitudesProveedor()
{
    // Verificamos sesión
    if (!isset($_SESSION['user']['id'])) {
        return [];
    }

    $usuarioId = (int)$_SESSION['user']['id'];
    $solicitudModel = new Solicitud();

    // Llamamos al modelo que corregimos anteriormente
    return $solicitudModel->listarPorProveedor($usuarioId);
}
