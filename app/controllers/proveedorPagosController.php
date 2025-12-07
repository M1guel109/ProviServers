<?php
// app/controllers/proveedorPagosController.php

require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/ProveedorPagosFacturacion.php';

session_start();

// 1. Validar sesión y rol
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'proveedor') {
    header('Location: ' . BASE_URL . '/login');
    exit();
}

// 2. Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Método no permitido";
    exit();
}

$usuarioId = (int) ($_SESSION['user']['id'] ?? 0);

// 3. Capturar datos del formulario
$data = [
    'tipo_documento'        => trim($_POST['tipo_documento']        ?? ''),
    'numero_documento'      => trim($_POST['numero_documento']      ?? ''),
    'razon_social'          => trim($_POST['razon_social']          ?? ''),
    'regimen_fiscal'        => trim($_POST['regimen_fiscal']        ?? ''),
    'direccion_facturacion' => trim($_POST['direccion_facturacion'] ?? ''),
    'ciudad_facturacion'    => trim($_POST['ciudad_facturacion']    ?? ''),
    'pais_facturacion'      => trim($_POST['pais_facturacion']      ?? ''),
    'correo_facturacion'    => trim($_POST['correo_facturacion']    ?? ''),
    'telefono_facturacion'  => trim($_POST['telefono_facturacion']  ?? ''),

    'banco'                 => trim($_POST['banco']                 ?? ''),
    'tipo_cuenta'           => trim($_POST['tipo_cuenta']           ?? ''),
    'numero_cuenta'         => trim($_POST['numero_cuenta']         ?? ''),
    'titular_cuenta'        => trim($_POST['titular_cuenta']        ?? ''),
    'identificacion_titular'=> trim($_POST['identificacion_titular']?? ''),
    'metodo_pago_preferido' => trim($_POST['metodo_pago_preferido'] ?? ''),
    'nota_metodo_pago'      => trim($_POST['nota_metodo_pago']      ?? ''),

    'frecuencia_liquidacion'=> trim($_POST['frecuencia_liquidacion']?? ''),
    'monto_minimo_retiro'   => trim($_POST['monto_minimo_retiro']   ?? ''),
    'acepta_factura_electronica' => $_POST['acepta_factura_electronica'] ?? null,
];

// 4. Validaciones básicas
$errores = [];

// Obligatorios de facturación
if ($data['tipo_documento'] === '') {
    $errores[] = 'Selecciona un tipo de documento de facturación.';
}
if ($data['numero_documento'] === '') {
    $errores[] = 'Ingresa el número de documento de facturación.';
}
if ($data['razon_social'] === '') {
    $errores[] = 'Ingresa el nombre o razón social para facturar.';
}
if ($data['direccion_facturacion'] === '' || $data['ciudad_facturacion'] === '' || $data['pais_facturacion'] === '') {
    $errores[] = 'Completa la dirección, ciudad y país de facturación.';
}
if ($data['correo_facturacion'] === '') {
    $errores[] = 'Ingresa un correo de facturación.';
} elseif (!filter_var($data['correo_facturacion'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El correo de facturación no tiene un formato válido.';
}

// Validar frecuencia de liquidación si se envía
$frecuenciasPermitidas = ['', 'semanal', 'quincenal', 'mensual'];
if (!in_array($data['frecuencia_liquidacion'], $frecuenciasPermitidas, true)) {
    $errores[] = 'La frecuencia de liquidación no es válida.';
}

// Validar monto mínimo si viene lleno
if ($data['monto_minimo_retiro'] !== '') {
    if (!is_numeric($data['monto_minimo_retiro']) || (float) $data['monto_minimo_retiro'] < 0) {
        $errores[] = 'El monto mínimo de retiro debe ser un número mayor o igual a 0.';
    }
}

// Opcional: si llenó banco o cuenta, exigir consistencia
if ($data['banco'] !== '' || $data['numero_cuenta'] !== '' || $data['tipo_cuenta'] !== '') {
    if ($data['banco'] === '' || $data['tipo_cuenta'] === '' || $data['numero_cuenta'] === '') {
        $errores[] = 'Si vas a registrar una cuenta bancaria, completa banco, tipo de cuenta y número de cuenta.';
    }
}

// Si hay errores, redirigimos con SweetAlert
if (!empty($errores)) {
    $mensaje = implode('<br>', $errores);
    mostrarSweetAlert(
        'error',
        'Datos inválidos',
        $mensaje,
        BASE_URL . '/proveedor/configuracion#pagos'
    );
    exit();
}

// 5. Guardar
$modelo = new ProveedorPagosFacturacion();
$ok = $modelo->guardarDesdeFormulario($usuarioId, $data);

if ($ok) {
    mostrarSweetAlert(
        'success',
        'Pagos y facturación actualizados',
        'Tu información de pagos y facturación se guardó correctamente.',
        BASE_URL . '/proveedor/configuracion#pagos'
    );
} else {
    mostrarSweetAlert(
        'error',
        'Error al guardar',
        'Ocurrió un problema al guardar tu configuración de pagos. Inténtalo nuevamente.',
        BASE_URL . '/proveedor/configuracion#pagos'
    );
}
