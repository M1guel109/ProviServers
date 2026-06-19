﻿﻿<?php

require_once __DIR__ . '/../helpers/alert-helper.php';
require_once __DIR__ . '/../models/perfil.php';

// ===================================================================
// GUARD DE SESIÓN
// ===================================================================

if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) session_start();
}

if (!isset($_SESSION['user']['id'])) {
    mostrarSweetAlert('error', 'Acceso denegado', 'Debes iniciar sesión para continuar.', BASE_URL . '/login');
    exit();
}

// ===================================================================
// ROUTER INTERNO — Dispatch por método HTTP y URI
// ===================================================================

$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

switch ($method) {

    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'actualizar-perfil') {
            actualizarPerfil();
        } elseif (str_contains($uri, 'cambiar-email')) {
            cambiarEmail();
        } elseif (str_contains($uri, 'cambiar-clave')) {
            cambiarContrasena();
        } else {
            http_response_code(400);
            mostrarSweetAlert('error', 'Acción no válida', 'La acción POST solicitada no existe.');
            exit();
        }
        break;

    case 'GET':
        // Falls through — index.php llama mostrarPerfilAdmin/Cliente/Proveedor explícitamente
        break;

    default:
        http_response_code(405);
        mostrarSweetAlert('error', 'Método no permitido', 'Esta ruta no acepta ese tipo de petición.');
        exit();
}

// ===================================================================
// FUNCIONES DE LECTURA — usadas explícitamente desde index.php
// ===================================================================

function mostrarPerfilAdmin(int $id): ?array
{
    return (new Perfil())->obtenerPerfilCompleto($id, 'admin');
}

function mostrarPerfilCliente(int $id): ?array
{
    return (new Perfil())->obtenerPerfilCompleto($id, 'cliente');
}

function mostrarPerfilProveedor(int $id): ?array
{
    return (new Perfil())->obtenerPerfilCompleto($id, 'proveedor');
}

// ===================================================================
// FUNCIONES DE ESCRITURA
// ===================================================================

function actualizarPerfil()
{
    $id  = (int)$_SESSION['user']['id'];
    $rol = $_SESSION['user']['rol'] ?? '';

    $nombres   = trim($_POST['nombres']   ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $telefono  = trim($_POST['telefono']  ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');

    if (empty($nombres)) {
        mostrarSweetAlert('error', 'Campo requerido', 'El nombre es obligatorio.', resolverRedirectPerfil($rol));
        exit();
    }

    $fotoFinal = $_POST['foto_actual'] ?? 'default_user.png';

    if (!empty($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            mostrarSweetAlert('error', 'Formato no válido', 'La foto debe ser JPG, PNG o WEBP. Máx 2MB.');
            exit();
        }

        if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Imagen muy grande', 'La foto no debe superar 2MB.');
            exit();
        }

        $ruta_perfiles = BASE_PATH . '/public/uploads/usuarios/';
        $nombre_final  = 'perfil_' . $id . '_' . uniqid() . '.' . $ext;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_perfiles . $nombre_final)) {
            $fotoAnterior = $_POST['foto_actual'] ?? '';
            if ($fotoAnterior !== 'default_user.png' && file_exists($ruta_perfiles . $fotoAnterior)) {
                @unlink($ruta_perfiles . $fotoAnterior);
            }
            $fotoFinal = $nombre_final;
        }
    }

    $data = [
        'nombres'   => $nombres,
        'apellidos' => $apellidos,
        'telefono'  => $telefono,
        'ubicacion' => $ubicacion,
        'foto'      => $fotoFinal,
    ];

    $redirect = resolverRedirectPerfil($rol);
    $modelo   = new Perfil();

    if ($modelo->actualizarPerfil($id, $rol, $data)) {
        $_SESSION['user']['foto'] = $fotoFinal;
        mostrarSweetAlert('success', '¡Actualizado!', 'Tu perfil se ha actualizado correctamente.', $redirect);
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo guardar en la base de datos.');
    }
    exit();
}

function cambiarEmail()
{
    $id  = (int)$_SESSION['user']['id'];
    $rol = $_SESSION['user']['rol'] ?? '';

    $emailNuevo    = trim($_POST['email_nuevo']    ?? '');
    $emailConfirma = trim($_POST['email_confirma'] ?? '');
    $claveActual   = $_POST['clave_actual']        ?? '';

    if (empty($emailNuevo) || empty($claveActual)) {
        mostrarSweetAlert('error', 'Campos requeridos', 'Ingresa el nuevo correo y tu contraseña actual.', resolverRedirectPerfil($rol));
        exit();
    }

    if (!filter_var($emailNuevo, FILTER_VALIDATE_EMAIL)) {
        mostrarSweetAlert('error', 'Correo inválido', 'El formato del correo no es válido.', resolverRedirectPerfil($rol));
        exit();
    }

    if ($emailNuevo !== $emailConfirma) {
        mostrarSweetAlert('error', 'Correos no coinciden', 'El nuevo correo y su confirmación deben ser iguales.', resolverRedirectPerfil($rol));
        exit();
    }

    $resultado = (new Perfil())->cambiarEmail($id, $claveActual, $emailNuevo);

    $redirect = resolverRedirectPerfil($rol);
    switch ($resultado) {
        case 'ok':
            $_SESSION['user']['email'] = $emailNuevo;
            mostrarSweetAlert('success', 'Correo actualizado', 'Tu correo se cambió correctamente.', $redirect);
            break;
        case 'clave_incorrecta':
            mostrarSweetAlert('error', 'Contraseña incorrecta', 'La contraseña actual ingresada no es correcta.', $redirect);
            break;
        case 'email_duplicado':
            mostrarSweetAlert('error', 'Correo en uso', 'Ese correo ya está registrado en otra cuenta.', $redirect);
            break;
        default:
            mostrarSweetAlert('error', 'Error inesperado', 'No se pudo actualizar el correo. Intenta nuevamente.', $redirect);
    }
    exit();
}

function cambiarContrasena()
{
    $id  = (int)$_SESSION['user']['id'];
    $rol = $_SESSION['user']['rol'] ?? '';

    $claveActual    = $_POST['clave_actual']    ?? '';
    $claveNueva     = $_POST['clave_nueva']     ?? '';
    $confirmarClave = $_POST['clave_confirmar'] ?? '';

    if (empty($claveActual) || empty($claveNueva)) {
        mostrarSweetAlert('error', 'Error', 'Todos los campos son obligatorios.');
        exit();
    }

    if ($claveNueva !== $confirmarClave) {
        mostrarSweetAlert('error', 'Error', 'La nueva contraseña no coincide con la confirmación.');
        exit();
    }

    $redirect = resolverRedirectPerfil($rol);
    $modelo   = new Perfil();

    if ($modelo->cambiarContrasena($id, $claveActual, $claveNueva)) {
        mostrarSweetAlert('success', '¡Éxito!', 'Contraseña actualizada correctamente.', $redirect);
    } else {
        mostrarSweetAlert('error', 'Contraseña incorrecta', 'La contraseña actual ingresada no es correcta.');
    }
    exit();
}

// ===================================================================
// UTILIDAD
// ===================================================================

function resolverRedirectPerfil(string $rol): string
{
    return match ($rol) {
        'admin'     => BASE_URL . '/admin/perfil',
        'cliente'   => BASE_URL . '/cliente/perfil',
        'proveedor' => BASE_URL . '/proveedor/configuracion',
        default     => BASE_URL . '/login',
    };
}
