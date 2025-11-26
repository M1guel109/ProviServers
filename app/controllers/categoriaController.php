<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/categoria.php';

// Capturamos en una variable el método o solicitud hecha al servidor
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'actualizar') {
            actualizarCategoria();
        } else {
            registrarCategoria();
        }

        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';

        if ($accion === 'eliminar') {
            eliminarCategoria($_GET['id']);
        }

        if (isset($_GET['id'])) {
            mostrarCategoriaId($_GET['id']);
        } else {
            mostrarCategorias();
        }

        break;

    default:
        http_response_code(405);
        echo "Metodo no permitido";
        break;
}


// Funciones del CRUD

function registrarCategoria()
{
    // Ruta donde se guardarán los iconos (IGUAL QUE TU CONTROLADOR USUARIO)
    $ruta_destino = BASE_PATH . '/public/uploads/categorias/';

    // Capturamos los datos desde el formulario
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';

    // Validamos campos obligatorios
    if (empty($nombre) || empty($descripcion)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos');
        exit();
    }

    // Lógica de carga del icono
    $ruta_icono = null;

    if (!empty($_FILES['icono_url']) && $_FILES['icono_url']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['icono_url'];

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidas = ['png', 'jpg', 'jpeg', 'svg'];

        if (!in_array($extension, $permitidas)) {
            mostrarSweetAlert('error', 'Extensión no permitida', 'Por favor cargue un archivo válido');
            exit();
        }

        // Máximo 512KB
        if ($file['size'] > 512 * 1024) {
            mostrarSweetAlert('error', 'Error al cargar el ícono', 'El ícono supera el límite permitido');
            exit();
        }

        $ruta_icono = uniqid('cat_') . '.' . $extension;

        // MOVEMOS EL ARCHIVO
        $destino = $ruta_destino . $ruta_icono;
        move_uploaded_file($file['tmp_name'], $destino);
    } else {
        mostrarSweetAlert('error', 'Ícono requerido', 'Debe subir un ícono para la categoría');
        exit();
    }

    // Instanciamos la clase
    $objCategoria = new Categoria();
    $data = [
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'icono_url' => $ruta_icono
    ];

    $resultado = $objCategoria->registrar($data);

    if ($resultado === true) {
        mostrarSweetAlert('success', 'Categoría registrada', 'Se ha creado una nueva categoría', '/ProviServers/admin/registrar-categoria');
    } else {
        mostrarSweetAlert('error', 'Error al registrar', 'No se pudo registrar la categoría');
    }

    exit();
}

function mostrarCategorias()
{
    $resultado = new Categoria();
    $categorias = $resultado->mostrar();

    return $categorias;
}

function mostrarCategoriaId($id)
{
    $objCategoria = new Categoria();
    $categoria = $objCategoria->mostrarId($id);

    return $categoria;
}

function actualizarCategoria()
{
    // $id = $_POST['id'] ?? '';
    // $nombre = $_POST['nombre'] ?? '';
    // $descripcion = $_POST['descripcion'] ?? '';

    // if (empty($id) || empty($nombre) || empty($descripcion)) {
    //     mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos');
    //     exit();
    // }

    // $ruta_destino = BASE_PATH . '/public/uploads/categorias/';
    // $ruta_icono = null;

    // // Si suben un nuevo icono
    // if (!empty($_FILES['icono_url']) && $_FILES['icono_url']['error'] === UPLOAD_ERR_OK) {
    //     $file = $_FILES['icono_url'];
    //     $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    //     $permitidas = ['png', 'jpg', 'jpeg', 'svg'];

    //     if (!in_array($extension, $permitidas)) {
    //         mostrarSweetAlert('error', 'Extensión no permitida', 'Por favor cargue un archivo válido');
    //         exit();
    //     }

    //     if ($file['size'] > 512 * 1024) {
    //         mostrarSweetAlert('error', 'Error al cargar el ícono', 'El ícono supera el límite permitido');
    //         exit();
    //     }

    //     $ruta_icono = uniqid('cat_') . '.' . $extension;
    //     move_uploaded_file($file['tmp_name'], $ruta_destino . $ruta_icono);
    // }

    // $objCategoria = new Categoria();
    // $data = [
    //     'id' => $id,
    //     'nombre' => $nombre,
    //     'descripcion' => $descripcion,
    //     'icono_url' => $ruta_icono
    // ];

    // $resultado = $objCategoria->actualizar($data);

    // if ($resultado === true) {
    //     mostrarSweetAlert('success', 'Categoría actualizada', 'Datos actualizados correctamente', '/ProviServers/admin/categorias');
    // } else {
    //     mostrarSweetAlert('error', 'Error al actualizar', 'No se pudo actualizar la categoría');
    // }

    // exit();
}

function eliminarCategoria($id)
{
    $objCategoria = new Categoria();
    $respuesta = $objCategoria->eliminar($id);

    if ($respuesta === true) {
        mostrarSweetAlert('success', 'Categoría eliminada', 'Se ha eliminado la categoría', '/ProviServers/admin/consultar-categorias');
    } else {
        mostrarSweetAlert('error', 'Error al eliminar', 'No se pudo eliminar la categoría');
    }
}
