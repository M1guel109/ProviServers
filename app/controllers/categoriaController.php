<?php
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/categoria.php';

$method = $_SERVER['REQUEST_METHOD'];

// Router
if ($method === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'actualizar') {
        actualizarCategoria();
    } else {
        registrarCategoria();
    }
} elseif ($method === 'GET') {
    $accion = $_GET['accion'] ?? '';
    
    if ($accion === 'eliminar') {
        eliminarCategoria($_GET['id']);
    } elseif (isset($_GET['id'])) {
        // Validación AJAX vs Normal
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $cat = mostrarCategoriaId($_GET['id']);
            echo json_encode($cat);
            exit;
        }
        return mostrarCategoriaId($_GET['id']);
    } else {
        mostrarCategorias();
    }
} else {
    http_response_code(405);
    echo "Método no permitido";
}

// ====================================================================
// FUNCIONES CRUD
// ====================================================================

function registrarCategoria() {
    $ruta_destino = BASE_PATH . '/public/uploads/categorias/';
    
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (empty($nombre) || empty($descripcion)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos');
        exit();
    }

    $objCategoria = new Categoria();

    // Validar duplicados
    if ($objCategoria->existeNombre($nombre)) {
        mostrarSweetAlert('warning', 'Duplicado', 'Ya existe una categoría con ese nombre.');
        exit();
    }

    // Imagen por defecto
    $ruta_icono = 'default_icon.png'; 

    if (!empty($_FILES['icono_url']) && $_FILES['icono_url']['error'] === UPLOAD_ERR_OK) {
        $procesar = procesarImagen($_FILES['icono_url'], $ruta_destino);
        if ($procesar['status'] === false) {
            mostrarSweetAlert('error', 'Error de Imagen', $procesar['msg']);
            exit();
        }
        $ruta_icono = $procesar['nombre'];
    }

    $data = [
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'icono_url' => $ruta_icono
    ];

    if ($objCategoria->registrar($data)) {
        mostrarSweetAlert('success', 'Éxito', 'Categoría creada correctamente', '/ProviServers/admin/consultar-categorias');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo guardar en la base de datos');
    }
    exit();
}

function actualizarCategoria() {
    $id = $_POST['id'] ?? '';
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (empty($id) || empty($nombre)) {
        mostrarSweetAlert('error', 'Error', 'Faltan datos obligatorios');
        exit();
    }

    $objCategoria = new Categoria();
    $ruta_destino = BASE_PATH . '/public/uploads/categorias/';

    $actual = $objCategoria->mostrarId($id);
    if (!$actual) {
        mostrarSweetAlert('error', 'Error', 'La categoría no existe');
        exit();
    }

    // Validar duplicado (excluyendo la actual)
    if ($objCategoria->existeNombre($nombre, $id)) {
        mostrarSweetAlert('warning', 'Duplicado', 'Ese nombre ya está en uso por otra categoría.');
        exit();
    }

    $nombre_imagen = $actual['icono_url']; // Mantener la anterior por defecto

    if (!empty($_FILES['icono_url']) && $_FILES['icono_url']['error'] === UPLOAD_ERR_OK) {
        $procesar = procesarImagen($_FILES['icono_url'], $ruta_destino);
        if ($procesar['status'] === false) {
            mostrarSweetAlert('error', 'Error de Imagen', $procesar['msg']);
            exit();
        }
        
        $nombre_imagen = $procesar['nombre'];

        // BORRAR IMAGEN VIEJA (Limpieza de servidor)
        if ($actual['icono_url'] !== 'default_icon.png' && file_exists($ruta_destino . $actual['icono_url'])) {
            unlink($ruta_destino . $actual['icono_url']);
        }
    }

    $data = [
        'id' => $id,
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'icono_url' => $nombre_imagen
    ];

    if ($objCategoria->actualizar($data)) {
        mostrarSweetAlert('success', 'Actualizado', 'Categoría editada correctamente', '/ProviServers/admin/consultar-categorias');
    } else {
        mostrarSweetAlert('error', 'Error', 'Fallo al actualizar en BD');
    }
    exit();
}

// --- FUNCIÓN DE ELIMINAR BLINDADA ---
function eliminarCategoria($id) {
    $objCategoria = new Categoria();
    
    // 1. SEGURIDAD: Verificar si hay servicios hijos
    if($objCategoria->tieneServicios($id)) { 
        mostrarSweetAlert('warning', 'No se puede eliminar', 'Esta categoría tiene servicios asociados. Elimínalos primero.'); 
        exit(); 
    }

    // 2. Obtener imagen para borrarla después
    $imagen = $objCategoria->obtenerImagen($id);

    // 3. Eliminar de BD
    if ($objCategoria->eliminar($id)) {
        // Borrar archivo físico si no es el default
        if ($imagen && $imagen !== 'default_icon.png') {
            $ruta_imagen = BASE_PATH . '/public/uploads/categorias/' . $imagen;
            if (file_exists($ruta_imagen)) {
                unlink($ruta_imagen);
            }
        }

        mostrarSweetAlert('success', 'Eliminado', 'Categoría eliminada', '/ProviServers/admin/consultar-categorias');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar la categoría.');
    }
    exit();
}

// Helper para imágenes
function procesarImagen($file, $destino) {
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $permitidas = ['png', 'jpg', 'jpeg', 'svg', 'webp'];

    if (!in_array($extension, $permitidas)) return ['status' => false, 'msg' => 'Formato no permitido (solo jpg, png, svg, webp)'];
    if ($file['size'] > 2 * 1024 * 1024) return ['status' => false, 'msg' => 'La imagen es muy pesada (Máx 2MB)'];

    $nuevo_nombre = uniqid('cat_') . '.' . $extension;
    
    if (move_uploaded_file($file['tmp_name'], $destino . $nuevo_nombre)) {
        return ['status' => true, 'nombre' => $nuevo_nombre];
    }
    return ['status' => false, 'msg' => 'Error al mover el archivo al servidor'];
}

// Funciones de lectura
function mostrarCategorias() {
    $obj = new Categoria();
    return $obj->mostrar();
}
function mostrarCategoriaId($id) {
    $obj = new Categoria();
    return $obj->mostrarId($id);
}
?>