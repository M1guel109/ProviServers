<?php
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/Necesidad.php';

session_start();

if (!isset($_SESSION['user']['id']) || ($_SESSION['user']['rol'] ?? '') !== 'proveedor') {
    mostrarSweetAlert('error','Acceso denegado','Solo proveedores');
    exit;
}

$necesidadId = (int)($_POST['necesidad_id'] ?? 0);
$titulo = trim($_POST['titulo'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');
$precio = $_POST['precio'] ?? null;
$tiempo = trim($_POST['tiempo_estimado'] ?? '');

if ($necesidadId <= 0 || $titulo === '') {
    mostrarSweetAlert('error','Error','Datos incompletos');
    exit;
}

$model = new Necesidad();

$ok = $model->crearCotizacionParaNecesidad((int)$_SESSION['user']['id'], $necesidadId, [
    'titulo' => $titulo,
    'mensaje' => ($mensaje !== '' ? $mensaje : null),
    'precio' => ($precio !== '' ? $precio : null),
    'tiempo_estimado' => ($tiempo !== '' ? $tiempo : null),
]);

if ($ok) {
    mostrarSweetAlert('success','Oferta enviada','El cliente verá tu cotización.','/ProviServers/proveedor/necesidades?id='.$necesidadId);
} else {
    mostrarSweetAlert('error','Error','No se pudo enviar la oferta (¿ya ofertaste o necesidad cerrada?)');
}
exit;
