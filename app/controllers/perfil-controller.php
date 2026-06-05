﻿﻿<?php

require_once __DIR__ . '/../helpers/alert-helper.php';
require_once __DIR__ . '/../models/Perfil.php';

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

    $data = [
        'nombres'   => trim($_POST['nombres']   ?? ''),
        'apellidos' => trim($_POST['apellidos'] ?? ''),
        'email'     => trim($_POST['email']     ?? ''),
        'telefono'  => trim($_POST['telefono']  ?? ''),
        'ubicacion' => trim($_POST['ubicacion'] ?? ''),
        'foto'      => $_POST['foto_actual']    ?? 'default_user.png',
    ];

    if (!empty($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            mostrarSweetAlert('error', 'Formato no válido', 'La foto debe ser JPG, PNG o WEBP.');
            exit();
        }

        $ruta_perfiles = BASE_PATH . '/public/uploads/usuarios/';
        if (!is_dir($ruta_perfiles)) mkdir($ruta_perfiles, 0777, true);

        $nombre_final = 'perfil_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_perfiles . $nombre_final)) {
            $fotoAnterior = $_POST['foto_actual'] ?? '';
            if ($fotoAnterior !== 'default_user.png' && file_exists($ruta_perfiles . $fotoAnterior)) {
                @unlink($ruta_perfiles . $fotoAnterior);
            }
            $data['foto'] = $nombre_final;
        }
    }

    $redirect = resolverRedirectPerfil($rol);
    $modelo   = new Perfil();

    if ($modelo->actualizarPerfil($id, $rol, $data)) {
        mostrarSweetAlert('success', '¡Actualizado!', 'Tu perfil se ha actualizado correctamente.', $redirect);
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo guardar en la base de datos.');
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
