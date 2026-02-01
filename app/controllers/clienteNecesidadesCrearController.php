<?php
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/Necesidad.php';

session_start();

if (!isset($_SESSION['user']['id']) || ($_SESSION['user']['rol'] ?? '') !== 'cliente') {
    mostrarSweetAlert('error','Acceso denegado','Solo clientes','/ProviServers/login');
    exit;
}

$usuarioId = (int)$_SESSION['user']['id'];

$servicioId = (int)($_POST['servicio_id'] ?? 0);
$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$ciudad = trim($_POST['ciudad'] ?? '');
$zona = trim($_POST['zona'] ?? '');
$fecha = trim($_POST['fecha_preferida'] ?? '');
$hora = trim($_POST['hora_preferida'] ?? '');
$presupuesto = $_POST['presupuesto_estimado'] ?? null;

if (!$servicioId || !$titulo || !$descripcion || !$direccion || !$ciudad || !$fecha) {
    mostrarSweetAlert('error','Campos incompletos','Completa los obligatorios');
    exit;
}

$model = new Necesidad();
$ok = $model->crearPorClienteUsuario($usuarioId, [
    'servicio_id' => $servicioId,
    'titulo' => $titulo,
    'descripcion' => $descripcion,
    'direccion' => $direccion,
    'ciudad' => $ciudad,
    'zona' => $zona,
    'fecha_preferida' => $fecha,
    'hora_preferida' => ($hora !== '' ? $hora : null),
    'presupuesto_estimado' => ($presupuesto !== '' ? $presupuesto : null),
]);

if ($ok) {
    mostrarSweetAlert('success','Necesidad publicada','Los proveedores podrán enviarte ofertas.','/ProviServers/cliente/necesidades');
} else {
    mostrarSweetAlert('error','Error','No se pudo publicar la necesidad');
}
exit;
