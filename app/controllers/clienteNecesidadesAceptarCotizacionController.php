<?php
require_once __DIR__ . '/../helpers/alert_helper.php';

// 1. CAMBIO IMPORTANTE: Importamos el modelo Cotizacion (donde pusimos la lógica nueva)
require_once __DIR__ . '/../models/Cotizacion.php'; 

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

// 2. CAMBIO IMPORTANTE: Instanciamos Cotizacion, no Necesidad
$model = new Cotizacion();

// 3. CAMBIO IMPORTANTE: Llamamos al método robusto que incluye el INSERT en servicios_contratados
// (Este es el método que arreglamos con el debug y la transacción)
$ok = $model->aceptarCotizacionParaClienteUsuario((int)$_SESSION['user']['id'], $cotizacionId);

if ($ok) {
    // Si todo salió bien, redirigimos a donde el cliente ve sus trabajos en curso
    mostrarSweetAlert('success', '¡Trato cerrado!', 'Has contratado el servicio correctamente.', '/ProviServers/cliente/servicios-contratados');
} else {
    mostrarSweetAlert('error', 'Error', 'No se pudo procesar la contratación. Intenta nuevamente.');
}
exit;