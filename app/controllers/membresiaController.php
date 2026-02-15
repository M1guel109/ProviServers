<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/Membresia.php';

// Capturamos el método de solicitud
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'actualizar') {
            actualizarMembresia();
        } else {
            registrarMembresia();
        }
        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';

        if ($accion === 'eliminar') {
            eliminarMembresia($_GET['id'] ?? null);
        } elseif (isset($_GET['id'])) {
            // Retorna JSON si se pide por AJAX o carga vista si es necesario
            // Por ahora asumimos uso interno
            $datos = mostrarMembresiaId($_GET['id']);
            echo json_encode($datos);
        } else {
            // Retornar array para la vista
            return mostrarMembresias();
        }
        break;

    default:
        http_response_code(405);
        echo "Método no permitido";
        break;
}

// ==========================================================
// FUNCIONES CRUD
// ==========================================================

function registrarMembresia()
{
    // 1. CAPTURA DE DATOS
    $tipo          = trim($_POST['tipo'] ?? '');
    $costo         = $_POST['costo'] ?? '';
    $duracion      = $_POST['duracion_dias'] ?? '';
    $descripcion   = trim($_POST['descripcion'] ?? '');
    $max_servicios = $_POST['max_servicios_activos'] ?? '';
    $orden_visual  = $_POST['orden_visual'] ?? null; // Puede ser NULL

    // Manejo de Checkboxes (Si no vienen en POST, son 0 o INACTIVO)
    $es_destacado  = isset($_POST['es_destacado']) ? 1 : 0;
    $permite_videos = isset($_POST['permite_videos']) ? 1 : 0;
    $acceso_stats  = isset($_POST['acceso_estadisticas_pro']) ? 1 : 0;

    // El estado en tu BD es ENUM('ACTIVO','INACTIVO')
    // El checkbox envía "ACTIVO" si está marcado, o nada si no.
    $estado = isset($_POST['estado']) ? 'ACTIVO' : 'INACTIVO';

    // 2. VALIDACIÓN
    if (empty($tipo) || $costo === '' || empty($duracion) || empty($descripcion)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa Tipo, Costo, Duración y Descripción.');
        exit;
    }

    if (!is_numeric($costo) || !is_numeric($duracion) || !is_numeric($max_servicios)) {
        mostrarSweetAlert('error', 'Formato inválido', 'Costo, Duración y Máx Servicios deben ser números.');
        exit;
    }

    // Limpieza de orden visual
    if ($orden_visual === '') {
        $orden_visual = null;
    }

    // 3. PREPARAR DATOS PARA EL MODELO
    $objMembresia = new Membresia();

    $data = [
        'tipo'                    => $tipo,
        'costo'                   => (float)$costo,
        'duracion_dias'           => (int)$duracion,
        'descripcion'             => $descripcion,
        'max_servicios_activos'   => (int)$max_servicios,
        'orden_visual'            => $orden_visual, // Puede ser null
        'acceso_estadisticas_pro' => $acceso_stats,
        'permite_videos'          => $permite_videos,
        'es_destacado'            => $es_destacado,
        'estado'                  => $estado
    ];

    // 4. GUARDAR
    $resultado = $objMembresia->registrar($data);

    if ($resultado) {
        mostrarSweetAlert('success', 'Membresía creada', 'El plan ha sido registrado correctamente.', '/ProviServers/admin/consultar-membresias');
    } else {
        mostrarSweetAlert('error', 'Error al registrar', 'No se pudo guardar en la base de datos.');
    }
    exit;
}

function actualizarMembresia()
{
    // Captura ID y valida
    $id = $_POST['id'] ?? null;
    if (!$id) {
        mostrarSweetAlert('error', 'Error', 'Identificador de membresía no válido.');
        exit;
    }

    // 1. CAPTURA DE DATOS (Igual que registrar)
    $tipo          = trim($_POST['tipo'] ?? '');
    $costo         = $_POST['costo'] ?? '';
    $duracion      = $_POST['duracion_dias'] ?? '';
    $descripcion   = trim($_POST['descripcion'] ?? '');
    $max_servicios = $_POST['max_servicios_activos'] ?? '';
    $orden_visual  = $_POST['orden_visual'] ?? null;

    // Checkboxes
    $es_destacado  = isset($_POST['es_destacado']) ? 1 : 0;
    $permite_videos = isset($_POST['permite_videos']) ? 1 : 0;
    $acceso_stats  = isset($_POST['acceso_estadisticas_pro']) ? 1 : 0;
    $estado        = isset($_POST['estado']) ? 'ACTIVO' : 'INACTIVO';

    // 2. VALIDACIÓN
    if (empty($tipo) || $costo === '' || empty($duracion)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Faltan datos obligatorios.');
        exit;
    }

    if ($orden_visual === '') $orden_visual = null;

    // 3. ACTUALIZAR
    $obj = new Membresia();
    $data = [
        'id'                      => $id,
        'tipo'                    => $tipo,
        'costo'                   => (float)$costo,
        'duracion_dias'           => (int)$duracion,
        'descripcion'             => $descripcion,
        'max_servicios_activos'   => (int)$max_servicios,
        'orden_visual'            => $orden_visual,
        'acceso_estadisticas_pro' => $acceso_stats,
        'permite_videos'          => $permite_videos,
        'es_destacado'            => $es_destacado,
        'estado'                  => $estado
    ];

    if ($obj->actualizar($data)) {
        mostrarSweetAlert('success', 'Actualizado', 'La membresía se actualizó correctamente.', '/ProviServers/admin/consultar-membresias');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudieron guardar los cambios.');
    }
    exit;
}

function eliminarMembresia($id)
{
    if (!$id) {
        mostrarSweetAlert('error', 'Error', 'ID inválido.');
        exit;
    }

    $obj = new Membresia();

    // 1. SEGURIDAD: Verificar si hay proveedores usando este plan
    if ($obj->tieneProveedores($id)) {
        mostrarSweetAlert(
            'warning',
            'No se puede eliminar',
            'Esta membresía está asignada a proveedores activos. No se puede borrar.',
            '/ProviServers/admin/consultar-membresias' // Redirige a la lista
        );
        exit;
    }

    // 2. ELIMINAR
    if ($obj->eliminar($id)) {
        mostrarSweetAlert('success', 'Eliminado', 'La membresía ha sido eliminada.', '/ProviServers/admin/consultar-membresias');
    } else {
        mostrarSweetAlert('error', 'Error', 'Ocurrió un error al intentar eliminar.');
    }
    exit;
}

function mostrarMembresias()
{
    $obj = new Membresia();
    return $obj->mostrar();
}

function mostrarMembresiaId($id)
{
    $obj = new Membresia();
    return $obj->mostrarId($id);
}
