<?php
// app/controllers/proveedorDisponibilidadController.php

require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/ProveedorDisponibilidad.php';

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
$diasTrabajo = $_POST['dias_trabajo'] ?? [];
$horaInicio  = trim($_POST['hora_inicio'] ?? '');
$horaFin     = trim($_POST['hora_fin'] ?? '');

$atiendeFinesSemana = isset($_POST['atiende_fines_semana']) ? 1 : 0;
$atiendeFestivos    = isset($_POST['atiende_festivos']) ? 1 : 0;
$atencionUrgencias  = isset($_POST['atencion_urgencias']) ? 1 : 0;
$detalleUrgencias   = trim($_POST['detalle_urgencias'] ?? '');

$tipoZona   = $_POST['tipo_zona'] ?? 'ciudad';
$radioKm    = $_POST['radio_km'] ?? null;
$zonasTexto = trim($_POST['zonas_texto'] ?? '');

// 4. Validaciones básicas
$errores = [];

if (empty($diasTrabajo)) {
    $errores[] = 'Selecciona al menos un día de trabajo.';
}

if ($horaInicio === '' || $horaFin === '') {
    $errores[] = 'Debes indicar una hora de inicio y una hora de fin.';
} elseif ($horaInicio >= $horaFin) {
    $errores[] = 'La hora de inicio debe ser menor que la hora de fin.';
}

$tiposZonaPermitidos = ['ciudad', 'radio', 'varias_ciudades', 'remoto'];
if (!in_array($tipoZona, $tiposZonaPermitidos, true)) {
    $errores[] = 'Tipo de zona de servicio no válido.';
}

if ($tipoZona === 'radio') {
    if ($radioKm === '' || !is_numeric($radioKm) || (int)$radioKm <= 0) {
        $errores[] = 'Indica un radio en kilómetros válido mayor a cero.';
    }
}

// Si hay errores, avisamos y redirigimos de vuelta
if (!empty($errores)) {
    $mensajeErrores = implode('<br>', $errores);
    mostrarSweetAlert(
        'error',
        'Datos inválidos',
        $mensajeErrores,
        BASE_URL . '/proveedor/configuracion#disponibilidad'
    );
    exit();
}

// 5. Armar data para el modelo
$data = [
    'dias_trabajo'         => $diasTrabajo,
    'hora_inicio'          => $horaInicio,
    'hora_fin'             => $horaFin,
    'atiende_fines_semana' => $atiendeFinesSemana,
    'atiende_festivos'     => $atiendeFestivos,
    'atencion_urgencias'   => $atencionUrgencias,
    'detalle_urgencias'    => $detalleUrgencias,
    'tipo_zona'            => $tipoZona,
    'radio_km'             => $radioKm,
    'zonas_texto'          => $zonasTexto,
];

// 6. Guardar
$modelo = new ProveedorDisponibilidad();
$guardado = $modelo->guardarDesdeFormulario($usuarioId, $data);

if ($guardado) {
    mostrarSweetAlert(
        'success',
        'Disponibilidad actualizada',
        'Tu disponibilidad y zona de servicio se guardaron correctamente.',
        BASE_URL . '/proveedor/configuracion#disponibilidad'
    );
} else {
    mostrarSweetAlert(
        'error',
        'Error al guardar',
        'Ocurrió un problema al guardar tu disponibilidad. Inténtalo nuevamente.',
        BASE_URL . '/proveedor/configuracion#disponibilidad'
    );
}
