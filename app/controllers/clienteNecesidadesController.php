<?php
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/Necesidad.php';

session_start();

if (!isset($_SESSION['user']['id'])) {
    mostrarSweetAlert('error', 'Acceso denegado', 'Debes iniciar sesión');
    exit();
}

if (($_SESSION['user']['rol'] ?? '') !== 'cliente') {
    mostrarSweetAlert('error', 'Acceso denegado', 'Solo clientes');
    exit();
}

$usuarioId = (int)$_SESSION['user']['id'];
$model = new Necesidad();

$path = strtok($_SERVER['REQUEST_URI'], '?'); // por si usas querystring
$path = str_replace('/ProviServers', '', $path); // ajusta prefijo si aplica

// POST: crear necesidad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/cliente/necesidades/crear') {

    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $zona = trim($_POST['zona'] ?? '');
    $fecha = trim($_POST['fecha_preferida'] ?? '');
    $franja = trim($_POST['franja_horaria'] ?? '');
    $presupuesto = $_POST['presupuesto_estimado'] ?? null;

    if (!$titulo || !$descripcion || !$direccion || !$ciudad || !$fecha) {
        mostrarSweetAlert('error', 'Campos incompletos', 'Completa los obligatorios');
        exit();
    }

    $franjasValidas = ['manana','tarde','noche'];
    if ($franja !== '' && !in_array($franja, $franjasValidas, true)) {
        $franja = '';
    }

    $ok = $model->crearParaClienteUsuario($usuarioId, [
        'titulo' => $titulo,
        'descripcion' => $descripcion,
        'direccion' => $direccion,
        'ciudad' => $ciudad,
        'zona' => $zona,
        'fecha_preferida' => $fecha,
        'franja_horaria' => ($franja === '' ? null : $franja),
        'presupuesto_estimado' => $presupuesto,
    ]);

    if ($ok) {
        mostrarSweetAlert('success', 'Necesidad publicada', 'Tu necesidad quedó abierta para recibir ofertas.', '/ProviServers/cliente/necesidades');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo publicar la necesidad.');
    }
    exit();
}

// POST: aceptar cotización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/cliente/necesidades/aceptar-cotizacion') {

    $cotizacionId = (int)($_POST['cotizacion_id'] ?? 0);
    if ($cotizacionId <= 0) {
        mostrarSweetAlert('error', 'Error', 'Cotización inválida');
        exit();
    }

    $ok = $model->aceptarCotizacion($usuarioId, $cotizacionId);

    if ($ok) {
        mostrarSweetAlert('success', 'Cotización aceptada', 'Se cerró la necesidad y se rechazaron las demás pendientes.', '/ProviServers/cliente/necesidades');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo aceptar (verifica que esté pendiente y la necesidad abierta).');
    }
    exit();
}

// GET: listado + detalle
$estado = $_GET['estado'] ?? null; // abierta|cerrada|cancelada
$misNecesidades = $model->listarPorClienteUsuario($usuarioId, $estado);

$detalle = null;
$cotizaciones = [];

$nid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($nid > 0) {
    $detalle = $model->obtenerDetallePorClienteUsuario($usuarioId, $nid);
    if ($detalle) {
        $cotizaciones = $model->listarCotizacionesDeNecesidadParaCliente($usuarioId, $nid);
    }
}

require BASE_PATH . '/app/views/dashboard/cliente/necesidades.php';
