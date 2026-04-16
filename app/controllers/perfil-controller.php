<?php
require_once __DIR__ . '/../helpers/alert-helper.php';
require_once __DIR__ . '/../models/perfil.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Protección de sesión
if (!isset($_SESSION['user']['id'])) {
    header("Location: /ProviServers/login");
    exit();
}

$idSesion = $_SESSION['user']['id'];
$rolSesion = $_SESSION['user']['rol'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $accion = $_POST['accion'] ?? '';
        if ($accion === 'actualizar-perfil') {
            actualizarPerfil();
        }
        break;
    case 'GET':
        // Se usa para cargar la vista del perfil
        $modelo = new Perfil();
        $miInfo = $modelo->obtenerPerfilCompleto($idSesion, $rolSesion);
        break;
}

function mostrarPerfilAdmin($id)
{
    $modelo = new Perfil();
    return $modelo->obtenerPerfilCompleto($id, 'admin');
}

function mostrarPerfilCliente($id)
{
    $modelo = new Perfil();
    return $modelo->obtenerPerfilCompleto($id, 'cliente');
}

function mostrarPerfilProveedor($id)
{
    $modelo = new Perfil();
    return $modelo->obtenerPerfilCompleto($id, 'proveedor');
}


function actualizarPerfil()
{
    global $idSesion, $rolSesion;

    // 1. Definir la ruta absoluta (usando la técnica que nos funcionó)
    $ruta_base = $_SERVER['DOCUMENT_ROOT'] . '/ProviServers/public/uploads/';
    $ruta_perfiles = $ruta_base . 'usuarios/';

    // Crear la carpeta si no existe
    if (!is_dir($ruta_perfiles)) mkdir($ruta_perfiles, 0777, true);

    $data = [
        'nombres'   => $_POST['nombres'] ?? '',
        'apellidos' => $_POST['apellidos'] ?? '',
        'email'     => $_POST['email'] ?? '',
        'telefono'  => $_POST['telefono'] ?? '',
        'ubicacion' => $_POST['ubicacion'] ?? '',
        'foto'      => $_POST['foto_actual'] ?? 'default_user.png'
    ];

    // 2. Adaptación del mapeo para procesar la foto (o más archivos)
    // El "key" es el name del input HTML, el "value" es el prefijo del nombre
    $mapeo_archivos = [
        'foto' => 'perfil'
    ];

    foreach ($mapeo_archivos as $input_name => $prefijo) {
        if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] === UPLOAD_ERR_OK) {

            $file = $_FILES[$input_name];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            // Validar extensiones de imagen
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) {

                // Nombre único: perfil_64a...png
                $nombre_final = $prefijo . '_' . uniqid() . '.' . $ext;
                $destino = $ruta_perfiles . $nombre_final;

                if (move_uploaded_file($file['tmp_name'], $destino)) {
                    // Si se subió bien, actualizamos el array $data
                    $data[$input_name] = $nombre_final;

                    // Borrar la foto anterior si no es la default
                    $fotoAnterior = $_POST['foto_actual'] ?? '';
                    if ($fotoAnterior !== 'default_user.png' && file_exists($ruta_perfiles . $fotoAnterior)) {
                        @unlink($ruta_perfiles . $fotoAnterior);
                    }
                }
            } else {
                mostrarSweetAlert('error', 'Formato no válido', "La foto debe ser JPG, PNG o WEBP.");
                exit();
            }
        }
    }

    // 3. Guardar en Base de Datos
    $modelo = new Perfil();
    if ($modelo->actualizarPerfil($idSesion, $rolSesion, $data)) {
        $_SESSION['user']['nombres'] = $data['nombres'];
        $_SESSION['user']['foto'] = $data['foto'];
        mostrarSweetAlert('success', '¡Actualizado!', 'Tu perfil se ha actualizado correctamente.', $_SERVER['HTTP_REFERER']);
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo guardar en la base de datos.');
    }
    exit();
}

function cambiarContrasenaUsuario()
{
    global $idSesion; // Solo necesitamos el ID del usuario

    $claveActual    = $_POST['clave_actual'] ?? '';
    $claveNueva     = $_POST['clave_nueva'] ?? '';
    $confirmarClave = $_POST['clave_confirmar'] ?? '';

    // Validación básica
    if (empty($claveActual) || empty($claveNueva)) {
        mostrarSweetAlert('error', 'Error', 'Todos los campos son obligatorios.');
        exit();
    }

    if ($claveNueva !== $confirmarClave) {
        mostrarSweetAlert('error', 'Error', 'La nueva contraseña no coincide con la confirmación.');
        exit();
    }

    $modelo = new Perfil();
    // Llamamos al modelo pasando los 3 parámetros que definimos arriba
    if ($modelo->cambiarContrasena($idSesion, $claveActual, $claveNueva)) {
        mostrarSweetAlert('success', '¡Éxito!', 'Contraseña actualizada correctamente.', $_SERVER['HTTP_REFERER']);
    } else {
        // Si el modelo devuelve false, es porque password_verify falló
        mostrarSweetAlert('error', 'Error', 'La contraseña actual es incorrecta.');
    }
    exit();
}
