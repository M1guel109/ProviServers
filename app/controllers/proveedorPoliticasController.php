<?php
// app/controllers/proveedorPoliticasController.php

require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/ProveedorPoliticasServicio.php';

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
$tipoCancelacion        = $_POST['tipo_cancelacion']        ?? 'moderada';
$descripcionCancelacion = trim($_POST['descripcion_cancelacion'] ?? '');

$permiteReprogramar     = isset($_POST['permite_reprogramar']) ? 1 : 0;
$horasMinReprogramacion = trim($_POST['horas_min_reprogramacion'] ?? '');

$cobraVisita            = isset($_POST['cobra_visita']) ? 1 : 0;
$valorVisita            = trim($_POST['valor_visita'] ?? '');

$ofreceGarantia         = isset($_POST['ofrece_garantia']) ? 1 : 0;
$diasGarantia           = trim($_POST['dias_garantia'] ?? '');
$detallesGarantia       = trim($_POST['detalles_garantia'] ?? '');

$soloContactoPorPlataforma = isset($_POST['solo_contacto_por_plataforma']) ? 1 : 0;
$tiempoRespuestaPromedio   = trim($_POST['tiempo_respuesta_promedio'] ?? '');
$otrasCondiciones          = trim($_POST['otras_condiciones'] ?? '');

// 4. Validaciones básicas
$errores = [];

// Tipo de cancelación
$tiposCancelacionPermitidos = ['flexible', 'moderada', 'estricta'];
if (!in_array($tipoCancelacion, $tiposCancelacionPermitidos, true)) {
    $errores[] = 'El tipo de política de cancelación no es válido.';
}

// Si permite reprogramar y se indica horas, validar número
if ($permiteReprogramar && $horasMinReprogramacion !== '') {
    if (!is_numeric($horasMinReprogramacion) || (int)$horasMinReprogramacion < 0) {
        $errores[] = 'Las horas mínimas para reprogramar deben ser un número mayor o igual a 0.';
    }
}

// Si cobra visita, validar valor
if ($cobraVisita) {
    if ($valorVisita === '' || !is_numeric($valorVisita) || (float)$valorVisita <= 0) {
        $errores[] = 'Si cobras visita, indica un valor válido mayor a 0.';
    }
}

// Si ofrece garantía, validar días
if ($ofreceGarantia) {
    if ($diasGarantia === '' || !is_numeric($diasGarantia) || (int)$diasGarantia <= 0) {
        $errores[] = 'Si ofreces garantía, indica un número de días mayor a 0.';
    }
}

// Tiempo de respuesta opcional pero con un máximo razonable de longitud
if (strlen($tiempoRespuestaPromedio) > 50) {
    $errores[] = 'El tiempo de respuesta promedio es demasiado largo. Usa una frase corta (ej: "24 horas").';
}

// Si hay errores, redirigimos con SweetAlert
if (!empty($errores)) {
    $mensaje = implode('<br>', $errores);
    mostrarSweetAlert(
        'error',
        'Datos inválidos',
        $mensaje,
        BASE_URL . '/proveedor/configuracion#politicas'
    );
    exit();
}

// 5. Armar el array para el modelo
$data = [
    'tipo_cancelacion'           => $tipoCancelacion,
    'descripcion_cancelacion'    => $descripcionCancelacion,
    'permite_reprogramar'        => $permiteReprogramar,
    'horas_min_reprogramacion'   => $horasMinReprogramacion,
    'cobra_visita'               => $cobraVisita,
    'valor_visita'               => $valorVisita,
    'ofrece_garantia'            => $ofreceGarantia,
    'dias_garantia'              => $diasGarantia,
    'detalles_garantia'          => $detallesGarantia,
    'solo_contacto_por_plataforma' => $soloContactoPorPlataforma,
    'tiempo_respuesta_promedio'  => $tiempoRespuestaPromedio,
    'otras_condiciones'          => $otrasCondiciones,
];

// 6. Guardar
$modelo = new ProveedorPoliticasServicio();
$ok = $modelo->guardarDesdeFormulario($usuarioId, $data);

if ($ok) {
    mostrarSweetAlert(
        'success',
        'Políticas actualizadas',
        'Tus políticas de servicio se guardaron correctamente.',
        BASE_URL . '/proveedor/configuracion#politicas'
    );
} else {
    mostrarSweetAlert(
        'error',
        'Error al guardar',
        'Ocurrió un problema al guardar tus políticas. Inténtalo nuevamente.',
        BASE_URL . '/proveedor/configuracion#politicas'
    );
}
