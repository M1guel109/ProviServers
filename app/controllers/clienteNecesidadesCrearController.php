<?php
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/Necesidad.php';

session_start();

if (!isset($_SESSION['user']['id']) || ($_SESSION['user']['rol'] ?? '') !== 'cliente') {
    mostrarSweetAlert('error','Acceso denegado','Solo clientes','/ProviServers/login');
    exit;
}

$usuarioId = (int)$_SESSION['user']['id'];

// Datos del formulario
$titulo       = trim($_POST['titulo'] ?? '');
$descripcion  = trim($_POST['descripcion'] ?? '');
$direccion    = trim($_POST['direccion'] ?? '');
$ciudad       = trim($_POST['ciudad'] ?? '');
$zona         = trim($_POST['zona'] ?? '');
$fecha        = trim($_POST['fecha_preferida'] ?? '');

$franja       = trim($_POST['franja_horaria'] ?? ''); // obligatorio (vista)
$hora         = trim($_POST['hora_preferida'] ?? ''); // opcional

$presupuesto  = $_POST['presupuesto_estimado'] ?? null;

// Validación obligatorios
if (!$titulo || !$descripcion || !$direccion || !$ciudad || !$fecha || !$franja) {
    mostrarSweetAlert('error','Campos incompletos','Completa los obligatorios');
    exit;
}

// Validación fuerte franja
$franjasValidas = ['mañana', 'tarde', 'noche'];
if (!in_array($franja, $franjasValidas, true)) {
    mostrarSweetAlert('error','Franja inválida','Selecciona una franja horaria válida');
    exit;
}

// Normalizar hora: si no viene, asignamos una referencial por franja
if ($hora === '') {
    switch ($franja) {
        case 'mañana': $hora = '09:00:00'; break;
        case 'tarde':  $hora = '15:00:00'; break;
        case 'noche':  $hora = '19:00:00'; break;
        default:       $hora = null;       break;
    }
} else {
    // input time normalmente llega "HH:MM". Lo convertimos a "HH:MM:SS"
    if (preg_match('/^\d{2}:\d{2}$/', $hora)) {
        $hora .= ':00';
    }
    // Validación simple formato TIME
    if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $hora)) {
        mostrarSweetAlert('error','Hora inválida','Ingresa una hora válida');
        exit;
    }
}

// Normalizar presupuesto
if ($presupuesto === '' || $presupuesto === null) {
    $presupuesto = null;
} else {
    $presupuesto = (float)$presupuesto;
    if ($presupuesto < 0) $presupuesto = 0;
}

$model = new Necesidad();

$ok = $model->crearParaClienteUsuario($usuarioId, [
    // servicio_id NO se pide: se insertará NULL
    'servicio_id'          => null,
    'titulo'               => $titulo,
    'descripcion'          => $descripcion,
    'direccion'            => $direccion,
    'ciudad'               => $ciudad,
    'zona'                 => ($zona !== '' ? $zona : null),
    'fecha_preferida'      => $fecha,
    'hora_preferida'       => $hora, // mapeada desde franja o definida por usuario
    'presupuesto_estimado' => $presupuesto,
]);

if ($ok) {
    mostrarSweetAlert('success','Necesidad publicada','Los proveedores podrán enviarte ofertas.','/ProviServers/cliente/necesidades');
} else {
    mostrarSweetAlert('error','Error','No se pudo publicar la necesidad');
}
exit;
