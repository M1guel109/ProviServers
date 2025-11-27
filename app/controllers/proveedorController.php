<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/servicio.php';

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
    // Capturamos los datos desde el formulario
    $nombre         = $_POST['nombre']        ?? '';
    $id_categoria   = $_POST['id_categoria']  ?? '';
    $disponibilidad = $_POST['disponibilidad']?? '';
    $descripcion    = $_POST['descripcion']   ?? '';

    // Validamos los campos obligatorios
    if (empty($nombre) || empty($id_categoria) || $disponibilidad === '') {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos obligatorios.');
        exit();
    }

    // Aquí podrías capturar el id del proveedor logueado si lo manejas en sesión
    // session_start();
    // $id_proveedor = $_SESSION['user']['id'];

    // Lógica para cargar imagen
    $ruta_img = null;

    // Validamos si se envió o no la imagen desde el formulario
    if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagen'];

        // Obtenemos la extensión del archivo
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Definimos las extensiones permitidas
        $permitidas = ['png', 'jpg', 'jpeg'];

        // Validamos que la extensión esté dentro de las permitidas
        if (!in_array($extension, $permitidas)) {
            mostrarSweetAlert('error', 'Extensión no permitida', 'Por favor, carga una imagen PNG, JPG o JPEG.');
            exit();
        }

        // Validamos el tamaño máximo 2MB
        if ($file['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Error al cargar la imagen', 'El peso de la imagen supera el límite de 2MB.');
            exit();
        }

        // Definimos el nombre del archivo
        $ruta_img = uniqid('servicio_') . '.' . $extension;

        // Definimos el destino donde moveremos el archivo
        $destino = BASE_PATH . "/public/uploads/servicios/" . $ruta_img;

        // Movemos el archivo al destino
        move_uploaded_file($file['tmp_name'], $destino);

    } else {
        // Imagen por defecto para el servicio
        $ruta_img = "default_service.png";
    }

    // Instanciamos la clase Servicio (modelo)
    $objServicio = new Servicio();

    $data = [
        'nombre'         => $nombre,
        'id_categoria'   => $id_categoria,
        'disponibilidad' => $disponibilidad,
        'descripcion'    => $descripcion,
        'imagen'         => $ruta_img,
        // 'id_proveedor' => $id_proveedor,
    ];

    // Enviamos la data al método "registrar()" del modelo
    $resultado = $objServicio->registrar($data);

    // Si la respuesta del modelo es verdadera, confirmamos
    if ($resultado === true) {
        mostrarSweetAlert(
            'success',
            'Servicio registrado con éxito',
            'Se ha creado un nuevo servicio.',
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
