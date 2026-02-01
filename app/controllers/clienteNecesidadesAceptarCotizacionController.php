<?php
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/Necesidad.php';

session_start();

if (!isset($_SESSION['user']['id']) || ($_SESSION['user']['rol'] ?? '') !== 'cliente') {
    mostrarSweetAlert('error','Acceso denegado','Solo clientes');
    exit;
}

$cotizacionId = (int)($_POST['cotizacion_id'] ?? 0);
if ($cotizacionId <= 0) {
    mostrarSweetAlert('error','Error','Cotización inválida');
    exit;
}

$model = new Necesidad();
$ok = $model->aceptarCotizacion((int)$_SESSION['user']['id'], $cotizacionId);

if ($ok) {
    mostrarSweetAlert('success','Cotización aceptada','Se creó el servicio contratado.','/ProviServers/cliente/servicios-contratados');
} else {
    mostrarSweetAlert('error','Error','No se pudo aceptar la cotización');
}
exit;
