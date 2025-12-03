<?php
// app/controllers/proveedorPerfilController.php

require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/ProveedorPerfil.php';

session_start();

// Solo debe entrar por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Método no permitido";
    exit();
}

// Validamos sesión y rol
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'proveedor') {
    mostrarSweetAlert(
        'error',
        'Sesión inválida',
        'Tu sesión ha expirado o no tienes permisos.',
        '/ProviServers/login'
    );
    exit();
}

$idUsuario = $_SESSION['user']['id'] ?? null;

if (!$idUsuario) {
    mostrarSweetAlert(
        'error',
        'Sesión inválida',
        'No se pudo identificar al usuario. Inicia sesión nuevamente.',
        '/ProviServers/login'
    );
    exit();
}

// 1. Capturamos y saneamos datos del formulario
$nombreComercial  = trim($_POST['nombre_comercial'] ?? '');
$tipoProveedor    = trim($_POST['tipo_proveedor'] ?? '');
$eslogan          = trim($_POST['eslogan'] ?? '');
$descripcion      = trim($_POST['descripcion'] ?? '');
$aniosExp         = trim($_POST['anios_experiencia'] ?? '');
$ciudad           = trim($_POST['ciudad'] ?? '');
$zona             = trim($_POST['zona'] ?? '');
$telefonoContacto = trim($_POST['telefono_contacto'] ?? '');
$whatsapp         = trim($_POST['whatsapp'] ?? '');
$correoAlt        = trim($_POST['correo_alternativo'] ?? '');

// campos múltiples
$idiomasSeleccionados    = $_POST['idiomas']    ?? [];
$categoriasSeleccionadas = $_POST['categorias'] ?? [];

// 2. Validar campos obligatorios
$errores = [];

if ($nombreComercial === '') {
    $errores[] = 'El nombre comercial es obligatorio.';
}

if ($tipoProveedor === '') {
    $errores[] = 'Debes seleccionar el tipo de proveedor.';
}

if ($eslogan === '') {
    $errores[] = 'El eslogan es obligatorio.';
}

if ($descripcion === '') {
    $errores[] = 'La descripción profesional es obligatoria.';
}

if ($ciudad === '') {
    $errores[] = 'La ciudad principal es obligatoria.';
}

if (empty($categoriasSeleccionadas)) {
    $errores[] = 'Debes seleccionar al menos una categoría principal.';
}

// Si hay errores, mostramos alerta y redirigimos de vuelta
if (!empty($errores)) {
    $mensaje = implode('<br>', $errores);
    mostrarSweetAlert(
        'error',
        'Faltan datos',
        $mensaje,
        '/ProviServers/proveedor/configuracion'
    );
    exit();
}

// 3. Normalizar datos opcionales
$aniosExp = ($aniosExp !== '' && is_numeric($aniosExp)) ? (int) $aniosExp : null;

// Convertimos arrays a CSV para guardar (por ahora)
$idiomasCSV     = is_array($idiomasSeleccionados)    ? implode(',', $idiomasSeleccionados)    : '';
$categoriasCSV  = is_array($categoriasSeleccionadas) ? implode(',', $categoriasSeleccionadas) : '';

// 4. Obtenemos perfil actual (si existe)
$modeloPerfil = new ProveedorPerfil();
$perfilActual = $modeloPerfil->obtenerPerfilPorUsuario($idUsuario);

// Por defecto usamos la foto actual o una por defecto
$fotoFinal = $perfilActual['foto'] ?? 'default_user.png';

// 5. Procesar imagen (foto/logo) si viene una nueva
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath  = $_FILES['foto']['tmp_name'];
    $fileName     = $_FILES['foto']['name'];
    $fileSize     = $_FILES['foto']['size'];

    $fileNameCmps = explode('.', $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($fileExtension, $extensionesPermitidas)) {
        mostrarSweetAlert(
            'error',
            'Formato no permitido',
            'Solo se permiten imágenes JPG, JPEG, PNG o WEBP.',
            '/ProviServers/proveedor/configuracion'
        );
        exit();
    }

    // 2MB = 2 * 1024 * 1024
    if ($fileSize > 2 * 1024 * 1024) {
        mostrarSweetAlert(
            'error',
            'Imagen demasiado grande',
            'La imagen no debe superar los 2MB.',
            '/ProviServers/proveedor/configuracion'
        );
        exit();
    }

    // Ruta destino
    $uploadDir = BASE_PATH . '/public/uploads/usuarios/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Nombre nuevo único
    $nuevoNombre = 'proveedor_' . $idUsuario . '_' . time() . '.' . $fileExtension;
    $destPath    = $uploadDir . $nuevoNombre;

    if (!move_uploaded_file($fileTmpPath, $destPath)) {
        mostrarSweetAlert(
            'error',
            'Error al subir imagen',
            'Ocurrió un error al guardar la imagen. Intenta nuevamente.',
            '/ProviServers/proveedor/configuracion'
        );
        exit();
    }

    // Guardamos solo el nombre de archivo
    $fotoFinal = $nuevoNombre;
}

// 6. Armar array de datos para BD
$data = [
    'nombre_comercial'    => $nombreComercial,
    'tipo_proveedor'      => $tipoProveedor,
    'eslogan'             => $eslogan,
    'descripcion'         => $descripcion,
    'anios_experiencia'   => $aniosExp,
    'idiomas'             => $idiomasCSV,
    'categorias'          => $categoriasCSV,
    'ciudad'              => $ciudad,
    'zona'                => $zona,
    'foto'                => $fotoFinal,
    'telefono_contacto'   => $telefonoContacto,
    'whatsapp'            => $whatsapp,
    'correo_alternativo'  => $correoAlt,
];

// 7. Insertar o actualizar según exista ya el perfil
try {
    if ($perfilActual) {
        $ok = $modeloPerfil->actualizarPerfil($idUsuario, $data);
    } else {
        $ok = $modeloPerfil->crearPerfil($idUsuario, $data);
    }

    if (!$ok) {
        mostrarSweetAlert(
            'error',
            'Error al guardar',
            'No se pudo guardar tu perfil profesional. Intenta nuevamente.',
            '/ProviServers/proveedor/configuracion'
        );
        exit();
    }

    mostrarSweetAlert(
        'success',
        'Perfil actualizado',
        'Tu perfil profesional se ha guardado correctamente.',
        '/ProviServers/proveedor/configuracion'
    );
    exit();

} catch (Exception $e) {
    error_log("Error en proveedorPerfilController -> " . $e->getMessage());
    mostrarSweetAlert(
        'error',
        'Error inesperado',
        'Ocurrió un problema al guardar tu perfil. Intenta más tarde.',
        '/ProviServers/proveedor/configuracion'
    );
    exit();
}
