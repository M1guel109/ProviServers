<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/servicio.php';
require_once __DIR__ . '/../models/Publicacion.php';

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
    // 0. Validar que haya sesión y que sea proveedor
    if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'proveedor') {
        header('Location: ' . BASE_URL . '/login');
        exit();
    }

    $usuarioId = (int) ($_SESSION['user']['id'] ?? 0);
    if ($usuarioId <= 0) {
        mostrarSweetAlert('error', 'Sesión inválida', 'No se encontró el usuario en sesión.');
        exit();
    }

    // 1. Capturamos los datos desde el formulario
    $nombre         = $_POST['nombre']        ?? '';
    $id_categoria   = $_POST['id_categoria']  ?? '';
    $disponibilidad = $_POST['disponibilidad']?? '';
    $descripcion    = $_POST['descripcion']   ?? '';

    // 2. Validamos los campos obligatorios
    if (empty($nombre) || empty($id_categoria) || $disponibilidad === '') {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos obligatorios.');
        exit();
    }

    // 3. Lógica para cargar imagen
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

        move_uploaded_file($file['tmp_name'], $destino);
    } else {
        $ruta_img = "default_service.png";
    }

    // 4. Instanciamos la clase Servicio (modelo)
    $objServicio = new Servicio();

    $dataServicio = [
        'nombre'         => $nombre,
        'id_categoria'   => $id_categoria,
        'disponibilidad' => $disponibilidad,
        'descripcion'    => $descripcion,
        'imagen'         => $ruta_img,
        // Ojo: aquí NO guardamos proveedor_id en servicios, lo ligaremos vía publicaciones
    ];

    // 5. Enviamos la data al método "registrar()" del modelo
    $resultado = $objServicio->registrar($dataServicio);

    if ($resultado === true) {
        // 5.1. Obtenemos el ID del servicio recién creado
        $servicioId = $objServicio->getUltimoIdInsertado();

        // 5.2. Creamos la publicación ligada a este servicio y a este proveedor
        try {
            $publicacionModel = new Publicacion();

            $publicacionModel->crearParaServicioDeProveedor(
                $usuarioId,
                $servicioId,
                [
                    'nombre'      => $nombre,
                    'descripcion' => $descripcion,
                    // Más adelante puedes agregar un campo "precio" en el formulario
                    // y pasarlo aquí:
                    // 'precio'   => $_POST['precio'] ?? 0,
                    // 'ubicacion'=> '...' (si quieres usar ciudad/zona del proveedor)
                    // 'estado'   => 'pendiente' // si usas el nuevo enum con moderación
                ]
            );
        } catch (Exception $e) {
            // No rompemos el flujo del alta de servicio, solo dejamos log del error
            error_log("Error al crear publicación para el servicio {$servicioId}: " . $e->getMessage());
        }

        // 5.3. Respuesta al usuario
        mostrarSweetAlert(
            'success',
            'Servicio registrado con éxito',
            'Se ha creado un nuevo servicio y su publicación. Quedará visible cuando el administrador lo apruebe (según el flujo que definan).',
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
    $disponibilidad= $_POST['disponibilidad']?? '';
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
        'disponibilidad'=> $disponibilidad,
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
