<?php

require_once __DIR__ . '/../helpers/alert-helper.php';
require_once __DIR__ . '/../models/proveedor-perfil.php';
require_once __DIR__ . '/../models/proveedor-notificaciones.php';
require_once __DIR__ . '/../models/proveedor-pagos-facturacion.php';

// ===================================================================
// GUARD DE SESIÓN Y ROL
// ===================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']['id']) || ($_SESSION['user']['rol'] ?? '') !== 'proveedor') {
    mostrarSweetAlert('error', 'Acceso denegado', 'Solo proveedores pueden acceder a esta sección.', BASE_URL . '/login');
    exit();
}

// ===================================================================
// ROUTER INTERNO — Dispatch por método HTTP y acción
// ===================================================================

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'actualizar_perfil') {
            guardarPerfilProfesional();
        } elseif ($accion === 'actualizar_credenciales') {
            actualizarCredenciales();
        } elseif ($accion === 'actualizar_seguridad') {
            actualizarSeguridad();
        } elseif ($accion === 'cerrar_sesiones') {
            cerrarSesiones();
        } elseif ($accion === 'actualizar_disponibilidad') {
            guardarDisponibilidad();
        } elseif ($accion === 'guardar_notificaciones') {
            guardarNotificaciones();
        } elseif ($accion === 'guardar_pagos') {
            guardarPagos();
        } elseif ($accion === 'actualizar_politicas') {
            guardarPoliticas();
        } else {
            http_response_code(400);
            mostrarSweetAlert('error', 'Acción no válida', 'La acción POST solicitada no existe.');
            exit();
        }
        break;

    default:
        http_response_code(405);
        mostrarSweetAlert('error', 'Método no permitido', 'Esta ruta no acepta ese tipo de petición.');
        exit();
}

// ===================================================================
// FUNCIONES DEL CONTROLADOR
// ===================================================================

// -------------------------------------------------------------------
// PERFIL PROFESIONAL
// -------------------------------------------------------------------
function guardarPerfilProfesional()
{
    $idUsuario = (int)$_SESSION['user']['id'];

    $nombreComercial  = trim($_POST['nombre_comercial']   ?? '');
    $tipoProveedor    = trim($_POST['tipo_proveedor']     ?? '');
    $eslogan          = trim($_POST['eslogan']            ?? '');
    $descripcion      = trim($_POST['descripcion']        ?? '');
    $aniosExp         = trim($_POST['anios_experiencia']  ?? '');
    $ciudad           = trim($_POST['ciudad']             ?? '');
    $zona             = trim($_POST['zona']               ?? '');
    $telefonoContacto = trim($_POST['telefono_contacto']  ?? '');
    $whatsapp         = trim($_POST['whatsapp']           ?? '');
    $correoAlt        = trim($_POST['correo_alternativo'] ?? '');

    $idiomas    = $_POST['idiomas']    ?? [];
    $categorias = $_POST['categorias'] ?? [];

    if (
        empty($nombreComercial) || empty($tipoProveedor) ||
        empty($eslogan)         || empty($descripcion)   || empty($ciudad)
    ) {
        mostrarSweetAlert('error', 'Campos obligatorios', 'Nombre comercial, tipo, eslogan, descripción y ciudad son requeridos.', BASE_URL . '/proveedor/configuracion');
        exit();
    }

    if (empty($categorias)) {
        mostrarSweetAlert('error', 'Categoría requerida', 'Debes seleccionar al menos una categoría.', BASE_URL . '/proveedor/configuracion');
        exit();
    }

    $aniosExp      = ($aniosExp !== '' && is_numeric($aniosExp)) ? (int)$aniosExp : null;
    $idiomasCSV    = is_array($idiomas)    ? implode(',', $idiomas)    : '';
    $categoriasCSV = is_array($categorias) ? implode(',', $categorias) : '';

    $modelo       = new ProveedorPerfil();
    $perfilActual = $modelo->obtenerPerfilPorUsuario($idUsuario);
    $fotoFinal    = $perfilActual['foto'] ?? 'default_user.png';

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            mostrarSweetAlert('error', 'Formato no válido', 'Solo JPG, PNG o WEBP. Máx. 2MB.', BASE_URL . '/proveedor/configuracion');
            exit();
        }

        if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Imagen demasiado grande', 'La imagen no debe superar 2MB.', BASE_URL . '/proveedor/configuracion');
            exit();
        }

        $nuevoNombre = 'proveedor_' . $idUsuario . '_' . uniqid() . '.' . $ext;
        $destino     = BASE_PATH . '/public/uploads/usuarios/' . $nuevoNombre;

        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
            mostrarSweetAlert('error', 'Error al subir imagen', 'No se pudo guardar la imagen. Intenta nuevamente.', BASE_URL . '/proveedor/configuracion');
            exit();
        }

        $fotoFinal = $nuevoNombre;
    }

    $data = [
        'nombre_comercial'   => $nombreComercial,
        'tipo_proveedor'     => $tipoProveedor,
        'eslogan'            => $eslogan,
        'descripcion'        => $descripcion,
        'anios_experiencia'  => $aniosExp,
        'idiomas'            => $idiomasCSV,
        'categorias'         => $categoriasCSV,
        'ciudad'             => $ciudad,
        'zona'               => $zona,
        'foto'               => $fotoFinal,
        'telefono_contacto'  => $telefonoContacto,
        'whatsapp'           => $whatsapp,
        'correo_alternativo' => $correoAlt,
    ];

    $ok = $perfilActual
        ? $modelo->actualizarPerfil($idUsuario, $data)
        : $modelo->crearPerfil($idUsuario, $data);

    if ($ok) {
        mostrarSweetAlert('success', 'Perfil actualizado', 'Tu perfil profesional se guardó correctamente.', BASE_URL . '/proveedor/configuracion');
    } else {
        mostrarSweetAlert('error', 'Error al guardar', 'No se pudo guardar tu perfil. Intenta nuevamente.', BASE_URL . '/proveedor/configuracion');
    }
    exit();
}

// -------------------------------------------------------------------
// CREDENCIALES (stub — PASO 2)
// -------------------------------------------------------------------
function actualizarCredenciales()
{
    mostrarSweetAlert('info', 'En construcción', 'Esta función estará disponible en el siguiente paso.', BASE_URL . '/proveedor/configuracion#cuenta');
    exit();
}

// -------------------------------------------------------------------
// SEGURIDAD (stub — PASO 2)
// -------------------------------------------------------------------
function actualizarSeguridad()
{
    mostrarSweetAlert('info', 'En construcción', 'Esta función estará disponible en el siguiente paso.', BASE_URL . '/proveedor/configuracion#cuenta');
    exit();
}

// -------------------------------------------------------------------
// CERRAR SESIONES (stub — PASO 2)
// -------------------------------------------------------------------
function cerrarSesiones()
{
    mostrarSweetAlert('info', 'En construcción', 'Esta función estará disponible en el siguiente paso.', BASE_URL . '/proveedor/configuracion#cuenta');
    exit();
}

// -------------------------------------------------------------------
// DISPONIBILIDAD (stub — PASO 3)
// -------------------------------------------------------------------
function guardarDisponibilidad()
{
    mostrarSweetAlert('info', 'En construcción', 'Esta función estará disponible en el siguiente paso.', BASE_URL . '/proveedor/configuracion#disponibilidad');
    exit();
}

// -------------------------------------------------------------------
// POLÍTICAS DE SERVICIO (stub — PASO 3)
// -------------------------------------------------------------------
function guardarPoliticas()
{
    mostrarSweetAlert('info', 'En construcción', 'Esta función estará disponible en el siguiente paso.', BASE_URL . '/proveedor/configuracion#politicas');
    exit();
}

// -------------------------------------------------------------------
// NOTIFICACIONES (stub — PASO 4)
// -------------------------------------------------------------------
function guardarNotificaciones()
{
    mostrarSweetAlert('info', 'En construcción', 'Esta función estará disponible en el siguiente paso.', BASE_URL . '/proveedor/configuracion#notificaciones');
    exit();
}

// -------------------------------------------------------------------
// PAGOS Y FACTURACIÓN (stub — PASO 4)
// -------------------------------------------------------------------
function guardarPagos()
{
    mostrarSweetAlert('info', 'En construcción', 'Esta función estará disponible en el siguiente paso.', BASE_URL . '/proveedor/configuracion#pagos');
    exit();
}
