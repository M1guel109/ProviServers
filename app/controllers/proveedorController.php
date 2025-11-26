<?php
// Asegúrate de que tu helper esté disponible
require_once __DIR__ . '/../helpers/alert_helper.php';
// Requerimos el modelo de servicio (el que hace la doble inserción)
require_once __DIR__ . '/../models/Servicio.php'; 
// Requerimos FileUploader para manejar la subida de imágenes (si existe como helper)
// Si no tienes un FileUploader, la lógica de subida está incrustada aquí.

// Iniciamos la sesión para poder acceder al ID del proveedor
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


// Capturamos en una variale el metodo o solicitud hecha al servidor
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // No necesitamos verificar 'accion' si esta ruta es solo para guardar el servicio
        guardarServicio();
        break;
        
    // (Otros casos como GET para mostrar servicios se omiten por ahora)
    
    default:
        http_response_code(405);
        echo "Método no permitido";
        break;
}

// ----------------------------------------------------
// FUNCIONES DEL CRUD PARA SERVICIOS
// ----------------------------------------------------

function guardarServicio()
{
    // 1. Validar Sesión y Obtener ID del Proveedor
    
    // **IMPORTANTE:** Asumimos que el ID del registro de la tabla `proveedores` 
    // está disponible en $_SESSION['user']['id_proveedor'] y que el rol es 'proveedor'.
    $proveedor_id = $_SESSION['user']['id_proveedor'] ?? null; 
    
    if (empty($proveedor_id) || ($_SESSION['user']['rol'] ?? '') !== 'proveedor') {
        mostrarSweetAlert('error', 'Acceso denegado', 'Necesitas ser un proveedor activo para registrar servicios.', BASE_URL . '/login');
        exit();
    }


    // 2. Capturar y sanear datos del formulario de servicio
    $nombre = $_POST['nombre'] ?? '';
    $id_categoria = $_POST['id_categoria'] ?? '';
    // La disponibilidad viene como '1' (disponible) o '0' (no disponible)
    $disponibilidad = $_POST['disponibilidad'] ?? '1'; 
    $descripcion = $_POST['descripcion'] ?? null; 

    // 3. Validar campos obligatorios
    if (empty($nombre) || empty($id_categoria)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'El nombre y la categoría del servicio son obligatorios.');
        exit();
    }

    // 4. Lógica para cargar la imagen del servicio (Adaptada del controlador de usuarios)
    $ruta_img = "default_servicio.png"; 

    if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagen'];

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidas = ['png', 'jpg', 'jpeg'];

        if (!in_array($extension, $permitidas)) {
            mostrarSweetAlert('error', 'Extension no permitida', 'Por favor, cargue un archivo con una extensión permitida.');
            exit();
        }

        if ($file['size'] > 2 * 1024 * 1024) { // Max 2MB
            mostrarSweetAlert('error', 'Error al cargar la foto ', 'El peso de la foto supera el limite de 2MB');
            exit();
        }

        $ruta_img = uniqid('servicio_') . '.' . $extension;

        // Definimos el destino (cambiado de /usuarios/ a /servicios/)
        $destino = BASE_PATH . "/public/uploads/servicios/" . $ruta_img; 

        move_uploaded_file($file['tmp_name'], $destino);
    }

    // 5. Preparar data
    $data = [
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        // **IMPORTANTE**: Convertir a entero.
        'id_categoria' => (int)$id_categoria, 
        'imagen' => $ruta_img,
        'disponibilidad' => (int)$disponibilidad
    ];

    // 6. POO - Instanciamos la clase Servicio
    // Asumimos que tu modelo se llama 'Servicio'
    $objServicio = new Servicio(); 
    
    // Llamada al método registrar, pasando la data y el ID del proveedor
    // El modelo debe usar este $proveedor_id para crear la publicación
    $resultado = $objServicio->registrar($data, $proveedor_id);

    // 7. Manejo de respuesta
    if ($resultado === true) {
        mostrarSweetAlert('success', 'Registro exitoso', 'Tu servicio ha sido registrado y publicado.', BASE_URL . '/proveedor/mis-servicios');
    } else {
        mostrarSweetAlert('error', 'Error al registrar', 'No se pudo registrar el servicio. Intenta nuevamente.');
    }

    exit();
}