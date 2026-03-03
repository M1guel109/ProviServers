<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/Publicacion.php';

session_start();

// Validación de sesión (Opcional, pero recomendada si esto está dentro del dashboard del cliente)
if (!isset($_SESSION['user']['id'])) {
    mostrarSweetAlert('error', 'Acceso denegado', 'Debes iniciar sesión para explorar los servicios.', '/ProviServers/login');
    exit();
}

// Capturamos el método de la solicitud
$method = $_SERVER['REQUEST_METHOD'];

// ENRUTADOR PRINCIPAL (Switch)
switch ($method) {
    case 'GET':
        // Si no se envía acción, por defecto mostramos el catálogo
        $accion = $_GET['accion'] ?? 'explorar';

        if ($accion === 'detalle') {
            mostrarDetallePublicacion();
        } else {
            mostrarCatalogoPublico();
        }
        break;

    default:
        http_response_code(405);
        echo "Método no permitido";
        break;
}

// ======================================================================
// FUNCIONES DEL CONTROLADOR
// ======================================================================

function mostrarCatalogoPublico()
{
    // Filtros desde la URL (GET)
    $busqueda    = $_GET['q']   ?? null;
    $categoriaId = isset($_GET['cat']) && $_GET['cat'] !== '' ? (int) $_GET['cat'] : null;

    $publicacionModel = new Publicacion();
    $publicaciones    = $publicacionModel->listarPublicasActivas($busqueda, $categoriaId);

    // Cargamos la vista del dashboard cliente
    require BASE_PATH . '/app/views/dashboard/cliente/explorarServicios.php';
    exit();
}

function mostrarDetallePublicacion()
{
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($id <= 0) {
        mostrarSweetAlert('error', 'Error', 'Identificador de publicación no válido.', '/ProviServers/cliente/explorar-servicios'); // Ajusta la redirección si es necesario
        exit();
    }

    $pubModel    = new Publicacion();
    $publicacion = $pubModel->obtenerPublicaActivaPorId($id);

    if (!$publicacion) {
        mostrarSweetAlert('error', 'No encontrada', 'La publicación no existe o ya no está disponible.', '/ProviServers/cliente/explorar-servicios');
        exit();
    }

    // Cargamos la vista de detalle
    require BASE_PATH . '/app/views/dashboard/cliente/detallePublicacion.php';
    exit();
}