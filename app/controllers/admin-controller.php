<?php

require_once __DIR__ . '/../helpers/alert-helper.php';
require_once __DIR__ . '/../helpers/notificaciones-helper.php';
require_once __DIR__ . '/../models/admin.php';
require_once __DIR__ . '/../models/categoria.php';
require_once __DIR__ . '/../models/membresia.php';
require_once __DIR__ . '/../models/suscripcion.php';
require_once __DIR__ . '/../models/moderacion.php';

// ===================================================================
// GUARD DE SESIÓN Y ROL
// ===================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']['id']) || ($_SESSION['user']['rol'] ?? '') !== 'admin') {
    mostrarSweetAlert('error', 'Acceso denegado', 'Solo administradores pueden acceder a esta sección.', BASE_URL . '/login');
    exit();
}

// ===================================================================
// ROUTER INTERNO — Dispatch por método HTTP y acción
// ===================================================================

$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

switch ($method) {

    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if (str_contains($uri, '/admin/guardar-categoria') || str_contains($uri, '/admin/actualizar-categoria')) {
            if ($accion === 'actualizar') { actualizarCategoria(); }
            else { registrarCategoria(); }
        } elseif ($accion === 'registrar_membresia') {
            registrarMembresia();
        } elseif ($accion === 'actualizar_membresia') {
            actualizarMembresia();
        } elseif ($accion === 'cambiar_estado_documento') {
            procesarEstadoDocumento();
        } elseif ($accion === 'actualizar') {
            actualizarUsuario();
        } else {
            registrarUsuario();
        }
        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';

        if ($accion === 'eliminar' && str_contains($uri, 'usuario')) {
            eliminarUsuario((int)($_GET['id'] ?? 0));
        } elseif ($accion === 'eliminar' && str_contains($uri, 'categoria')) {
            eliminarCategoria((int)($_GET['id'] ?? 0));
        } elseif ($accion === 'eliminar_membresia') {
            eliminarMembresia($_GET['id'] ?? null);
        } elseif ($accion === 'cancelar_suscripcion') {
            cancelarSuscripcion($_GET['id'] ?? null);
        } elseif ($accion === 'eliminar_suscripcion') {
            eliminarSuscripcion($_GET['id'] ?? null);
        } elseif ($accion === 'detalle_suscripcion_json') {
            obtenerDetalleJSON($_GET['id'] ?? null);
        } elseif ($accion === 'obtener_membresia_json') {
            $id = (int)($_GET['id'] ?? 0);
            if ($id) {
                header('Content-Type: application/json');
                echo json_encode(mostrarMembresiaId($id));
                exit;
            }
        }
        // Sin error para GET no reconocido — index.php llama funciones explícitas
        // (obtenerDetalleUsuarioAjax, obtenerDashboardStatsAjax, reportesPdfController, etc.)
        break;

    default:
        http_response_code(405);
        mostrarSweetAlert('error', 'Método no permitido', 'Esta ruta no acepta ese tipo de petición.');
        exit();
}

// ===================================================================
// FUNCIONES — USUARIOS (CRUD)
// ===================================================================

// -------------------------------------------------------------------
// REGISTRAR USUARIO
// -------------------------------------------------------------------
function registrarUsuario()
{
    $nombres   = trim($_POST['nombres']   ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    $email     = trim($_POST['email']     ?? '');
    $clave     = $_POST['clave']          ?? '';
    $telefono  = trim($_POST['telefono']  ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $rol       = trim($_POST['rol']       ?? '');

    $clave_final = !empty($clave) ? $clave : $documento;

    if (empty($nombres) || empty($apellidos) || empty($documento) || empty($email) || empty($clave_final) || empty($telefono) || empty($ubicacion) || empty($rol)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos obligatorios.');
        exit();
    }

    $foto = 'default_user.png';
    if (!empty($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
            mostrarSweetAlert('error', 'Formato inválido', 'La foto debe ser PNG, JPG o JPEG.');
            exit();
        }
        $nombre = uniqid('perfil_') . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], BASE_PATH . '/public/uploads/usuarios/' . $nombre);
        $foto = $nombre;
    }

    $categorias = [];
    $documentos = [];

    if ($rol === 'proveedor') {
        if (!empty($_POST['lista_categorias'])) {
            $categorias = explode(',', $_POST['lista_categorias']);
        }

        $cantidad = count($categorias);
        if ($cantidad < 1) {
            mostrarSweetAlert('error', 'Perfil incompleto', 'El proveedor debe tener asignada al menos 1 categoría.');
            exit();
        }
        if ($cantidad > 5) {
            mostrarSweetAlert('error', 'Límite excedido', 'El proveedor no puede tener más de 5 categorías.');
            exit();
        }

        $mapeo = [
            'doc-cedula'       => 'dni',
            'doc-foto'         => 'otro',
            'doc-antecedentes' => 'otro',
            'doc-certificado'  => 'certificado',
        ];

        foreach ($mapeo as $input => $tipo) {
            if (!empty($_FILES[$input]['tmp_name']) && $_FILES[$input]['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES[$input]['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'], true)) {
                    mostrarSweetAlert('error', 'Archivo inválido', "El documento {$tipo} debe ser PDF o imagen.");
                    exit();
                }
                $nombre = $tipo . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES[$input]['tmp_name'], BASE_PATH . '/public/uploads/documentos/' . $nombre)) {
                    $documentos[] = ['tipo' => $tipo, 'archivo' => $nombre];
                }
            }
        }
    }

    $estado = ($rol === 'proveedor') ? 1 : 2;

    $objUsuario = new Usuario();
    $resultado  = $objUsuario->registrar([
        'nombres'    => $nombres,
        'apellidos'  => $apellidos,
        'documento'  => $documento,
        'email'      => $email,
        'clave'      => $clave_final,
        'telefono'   => $telefono,
        'ubicacion'  => $ubicacion,
        'rol'        => $rol,
        'foto'       => $foto,
        'estado'     => $estado,
        'categorias' => $categorias,
        'documentos' => $documentos,
    ]);

    if ($resultado === true) {
        mostrarSweetAlert('success', '¡Registro exitoso!', 'El usuario ha sido creado correctamente.', BASE_URL . '/admin/consultar-usuarios');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo registrar. Verifica si el correo o documento ya existen.');
    }
    exit();
}

// -------------------------------------------------------------------
// ACTUALIZAR USUARIO
// -------------------------------------------------------------------
function actualizarUsuario()
{
    $id           = trim($_POST['id']        ?? '');
    $nombres      = trim($_POST['nombres']   ?? '');
    $apellidos    = trim($_POST['apellidos'] ?? '');
    $documento    = trim($_POST['documento'] ?? '');
    $email        = trim($_POST['email']     ?? '');
    $telefono     = trim($_POST['telefono']  ?? '');
    $ubicacion    = trim($_POST['ubicacion'] ?? '');
    $rol          = trim($_POST['rol']       ?? '');
    $nuevo_estado = trim($_POST['estado']    ?? '');
    $nueva_clave  = $_POST['clave']          ?? '';

    if (empty($id) || empty($nombres) || empty($apellidos) || empty($documento) || empty($email) || empty($telefono) || empty($ubicacion) || empty($rol) || empty($nuevo_estado)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos obligatorios.');
        exit();
    }

    $foto_actual = $_POST['foto_actual'] ?? '';
    $foto_final  = $foto_actual;

    if (!empty($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            mostrarSweetAlert('error', 'Formato inválido', 'La foto de perfil debe ser PNG, JPG o JPEG.');
            exit();
        }
        $nombre  = uniqid('user_') . '.' . $ext;
        $destino = BASE_PATH . '/public/uploads/usuarios/';
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino . $nombre)) {
            $foto_final = $nombre;
            if (!empty($foto_actual) && $foto_actual !== 'default_user.png' && file_exists($destino . $foto_actual)) {
                unlink($destino . $foto_actual);
            }
        }
    }

    $categorias = [];
    $documentos = [];

    if ($rol === 'proveedor') {
        if (!empty($_POST['lista_categorias'])) {
            $categorias = explode(',', $_POST['lista_categorias']);
        }
        if (count($categorias) < 1) {
            mostrarSweetAlert('error', 'Perfil incompleto', 'El proveedor debe tener asignada al menos 1 categoría.');
            exit();
        }

        $mapeo = [
            'doc-cedula'       => 'dni',
            'doc-foto'         => 'otro',
            'doc-antecedentes' => 'otro',
            'doc-certificado'  => 'certificado',
        ];

        foreach ($mapeo as $input => $tipo) {
            if (isset($_FILES[$input]['tmp_name']) && $_FILES[$input]['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES[$input]['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'], true)) {
                    mostrarSweetAlert('error', 'Archivo inválido', "El documento {$tipo} debe ser PDF o imagen.");
                    exit();
                }
                $nombre = $tipo . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES[$input]['tmp_name'], BASE_PATH . '/public/uploads/documentos/' . $nombre)) {
                    $documentos[] = ['tipo' => $tipo, 'archivo' => $nombre];
                }
            }
        }
    }

    $objUsuario     = new Usuario();
    $datos_anterior = $objUsuario->mostrarId($id);
    $estado_anterior = $datos_anterior['estado_id'] ?? null;

    $resultado = $objUsuario->actualizar([
        'id'               => $id,
        'nombres'          => $nombres,
        'apellidos'        => $apellidos,
        'documento'        => $documento,
        'email'            => $email,
        'telefono'         => $telefono,
        'ubicacion'        => $ubicacion,
        'rol'              => $rol,
        'foto_perfil'      => $foto_final,
        'estado'           => $nuevo_estado,
        'clave'            => !empty($nueva_clave) ? $nueva_clave : null,
        'categorias'       => $categorias,
        'documentos_nuevos'=> $documentos,
    ]);

    if ($resultado === true) {
        if ($estado_anterior !== null && $rol === 'proveedor' && (int)$estado_anterior === 1 && (int)$nuevo_estado === 2) {
            if (function_exists('enviarCorreoProveedorActivado')) {
                enviarCorreoProveedorActivado($email, $nombres);
            }
        }
        mostrarSweetAlert('success', 'Actualización exitosa', 'El usuario ha sido modificado correctamente.', BASE_URL . '/admin/consultar-usuarios');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo actualizar la base de datos. Intenta nuevamente.');
    }
    exit();
}

// -------------------------------------------------------------------
// ELIMINAR USUARIO
// -------------------------------------------------------------------
function eliminarUsuario(int $id)
{
    if (!$id) {
        mostrarSweetAlert('error', 'ID inválido', 'No se proporcionó un ID de usuario válido.', BASE_URL . '/admin/consultar-usuarios');
        exit();
    }

    $objUsuario = new Usuario();
    $respuesta  = $objUsuario->eliminar($id);

    if ($respuesta === 'eliminado') {
        mostrarSweetAlert('success', 'Eliminado', 'El usuario no tenía historial y fue borrado permanentemente.', BASE_URL . '/admin/consultar-usuarios');
    } elseif ($respuesta === 'desactivado') {
        mostrarSweetAlert('warning', 'Usuario desactivado', 'El usuario tiene historial de servicios. Fue desactivado para impedir su acceso.', BASE_URL . '/admin/consultar-usuarios');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo procesar la solicitud.', BASE_URL . '/admin/consultar-usuarios');
    }
    exit();
}

// -------------------------------------------------------------------
// FUNCIONES DE LECTURA (usadas por AJAX endpoints y reportes)
// -------------------------------------------------------------------
function mostrarUsuarios()
{
    $modelo = new Usuario();
    return $modelo->mostrar();
}

function mostrarUsuarioId($id)
{
    $modelo = new Usuario();
    return $modelo->mostrarId($id);
}

// -------------------------------------------------------------------
// AJAX — Detalle completo de usuario
// -------------------------------------------------------------------
function obtenerDetalleUsuarioAjax()
{
    if (!isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID no proporcionado']);
        exit;
    }

    $modelo = new Usuario();
    $datos  = $modelo->obtenerDetalleCompleto((int)$_GET['id']);

    header('Content-Type: application/json');
    echo json_encode($datos ?: ['error' => 'Usuario no encontrado']);
    exit;
}

// -------------------------------------------------------------------
// AJAX — Cambiar estado de documento
// -------------------------------------------------------------------
function procesarEstadoDocumento()
{
    $id_doc      = $_POST['id_doc']      ?? null;
    $nuevo_estado = $_POST['nuevo_estado'] ?? null;

    header('Content-Type: application/json');

    if ($id_doc && $nuevo_estado) {
        $modelo = new Usuario();
        echo json_encode(['success' => $modelo->actualizarEstadoDocumento($id_doc, $nuevo_estado)]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    }
    exit;
}

// -------------------------------------------------------------------
// AJAX — Estadísticas del dashboard
// -------------------------------------------------------------------
function obtenerDashboardStatsAjax()
{
    $periodo = $_GET['periodo'] ?? 'mensual';
    if (!in_array($periodo, ['mensual', 'semanal', 'anual'], true)) {
        $periodo = 'mensual';
    }

    $modelo = new Usuario();

    header('Content-Type: application/json');
    echo json_encode([
        'grafica'  => $modelo->obtenerEstadisticasGrafica($periodo),
        'metricas' => $modelo->obtenerMetricasUsuarios(),
    ]);
    exit;
}

// ===================================================================
// FUNCIONES — CATEGORÍAS (CRUD)
// ===================================================================

// -------------------------------------------------------------------
// REGISTRAR CATEGORÍA
// -------------------------------------------------------------------
function registrarCategoria()
{
    $nombre      = trim($_POST['nombre']      ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (empty($nombre) || empty($descripcion)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos.');
        exit();
    }

    $objCategoria = new Categoria();

    if ($objCategoria->existeNombre($nombre)) {
        mostrarSweetAlert('warning', 'Duplicado', 'Ya existe una categoría con ese nombre.');
        exit();
    }

    $icono = 'default_icon.png';
    if (!empty($_FILES['icono_url']['tmp_name']) && $_FILES['icono_url']['error'] === UPLOAD_ERR_OK) {
        $resultado = procesarImagenCategoria($_FILES['icono_url']);
        if ($resultado['status'] === false) {
            mostrarSweetAlert('error', 'Error de imagen', $resultado['msg']);
            exit();
        }
        $icono = $resultado['nombre'];
    }

    if ($objCategoria->registrar(['nombre' => $nombre, 'descripcion' => $descripcion, 'icono_url' => $icono])) {
        mostrarSweetAlert('success', 'Categoría creada', 'La categoría fue registrada correctamente.', BASE_URL . '/admin/consultar-categorias');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo guardar en la base de datos.');
    }
    exit();
}

// -------------------------------------------------------------------
// ACTUALIZAR CATEGORÍA
// -------------------------------------------------------------------
function actualizarCategoria()
{
    $id          = trim($_POST['id']          ?? '');
    $nombre      = trim($_POST['nombre']      ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (empty($id) || empty($nombre)) {
        mostrarSweetAlert('error', 'Error', 'Faltan datos obligatorios.');
        exit();
    }

    $objCategoria = new Categoria();
    $actual       = $objCategoria->mostrarId($id);

    if (!$actual) {
        mostrarSweetAlert('error', 'Error', 'La categoría no existe.');
        exit();
    }

    if ($objCategoria->existeNombre($nombre, $id)) {
        mostrarSweetAlert('warning', 'Duplicado', 'Ese nombre ya está en uso por otra categoría.');
        exit();
    }

    $icono = $actual['icono_url'];

    if (!empty($_FILES['icono_url']['tmp_name']) && $_FILES['icono_url']['error'] === UPLOAD_ERR_OK) {
        $resultado = procesarImagenCategoria($_FILES['icono_url']);
        if ($resultado['status'] === false) {
            mostrarSweetAlert('error', 'Error de imagen', $resultado['msg']);
            exit();
        }
        if ($icono !== 'default_icon.png' && file_exists(BASE_PATH . '/public/uploads/categorias/' . $icono)) {
            unlink(BASE_PATH . '/public/uploads/categorias/' . $icono);
        }
        $icono = $resultado['nombre'];
    }

    if ($objCategoria->actualizar(['id' => $id, 'nombre' => $nombre, 'descripcion' => $descripcion, 'icono_url' => $icono])) {
        mostrarSweetAlert('success', 'Categoría actualizada', 'La categoría fue editada correctamente.', BASE_URL . '/admin/consultar-categorias');
    } else {
        mostrarSweetAlert('error', 'Error', 'Fallo al actualizar en la base de datos.');
    }
    exit();
}

// -------------------------------------------------------------------
// ELIMINAR CATEGORÍA
// -------------------------------------------------------------------
function eliminarCategoria(int $id)
{
    if (!$id) {
        mostrarSweetAlert('error', 'ID inválido', 'No se proporcionó un ID válido.', BASE_URL . '/admin/consultar-categorias');
        exit();
    }

    $objCategoria = new Categoria();

    if ($objCategoria->tieneServicios($id)) {
        mostrarSweetAlert('warning', 'No se puede eliminar', 'Esta categoría tiene servicios asociados. Elimínalos primero.', BASE_URL . '/admin/consultar-categorias');
        exit();
    }

    $imagen = $objCategoria->obtenerImagen($id);

    if ($objCategoria->eliminar($id)) {
        if ($imagen && $imagen !== 'default_icon.png') {
            $ruta = BASE_PATH . '/public/uploads/categorias/' . $imagen;
            if (file_exists($ruta)) unlink($ruta);
        }
        mostrarSweetAlert('success', 'Categoría eliminada', 'La categoría fue eliminada correctamente.', BASE_URL . '/admin/consultar-categorias');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar la categoría.', BASE_URL . '/admin/consultar-categorias');
    }
    exit();
}

// -------------------------------------------------------------------
// HELPER — Procesamiento de imagen de categoría
// -------------------------------------------------------------------
function procesarImagenCategoria(array $file): array
{
    $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $permitidas = ['png', 'jpg', 'jpeg', 'svg', 'webp'];

    if (!in_array($ext, $permitidas, true)) {
        return ['status' => false, 'msg' => 'Formato no permitido (jpg, png, svg, webp).'];
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        return ['status' => false, 'msg' => 'La imagen no debe superar 2MB.'];
    }

    $nombre = uniqid('cat_') . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], BASE_PATH . '/public/uploads/categorias/' . $nombre)) {
        return ['status' => true, 'nombre' => $nombre];
    }
    return ['status' => false, 'msg' => 'Error al mover el archivo al servidor.'];
}

// ===================================================================
// FUNCIONES — MEMBRESÍAS, SUSCRIPCIONES, MODERACIÓN, REPORTES
// Implementadas en PASO 2 y PASO 3
// ===================================================================

function registrarMembresia()
{
    mostrarSweetAlert('info', 'En construcción', 'Esta función estará disponible en el siguiente paso.', BASE_URL . '/admin/consultar-membresias');
    exit();
}

function actualizarMembresia()
{
    mostrarSweetAlert('info', 'En construcción', 'Esta función estará disponible en el siguiente paso.', BASE_URL . '/admin/consultar-membresias');
    exit();
}

function eliminarMembresia($id)
{
    mostrarSweetAlert('info', 'En construcción', 'Esta función estará disponible en el siguiente paso.', BASE_URL . '/admin/consultar-membresias');
    exit();
}

function mostrarMembresias() { return []; }
function mostrarMembresiaId($id) { return null; }

function cancelarSuscripcion($id)
{
    mostrarSweetAlert('info', 'En construcción', 'Esta función estará disponible en el siguiente paso.', BASE_URL . '/admin/consultar-suscripciones');
    exit();
}

function eliminarSuscripcion($id)
{
    mostrarSweetAlert('info', 'En construcción', 'Esta función estará disponible en el siguiente paso.', BASE_URL . '/admin/consultar-suscripciones');
    exit();
}

function obtenerDetalleJSON($id)
{
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Función en construcción.']);
    exit;
}

function mostrarServicios() { return []; }

function apiDetalleServicio()
{
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Función en construcción.']);
    exit;
}

function apiActualizarEstado()
{
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Función en construcción.']);
    exit;
}

function reportesPdfController()
{
    mostrarSweetAlert('info', 'En construcción', 'Los reportes PDF estarán disponibles en el siguiente paso.');
    exit();
}
