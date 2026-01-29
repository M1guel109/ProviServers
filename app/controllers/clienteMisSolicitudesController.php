<?php
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/Solicitud.php';

session_start();

$usuarioId = (int)($_SESSION['user']['id'] ?? 0);

$pendientes = [];
$aceptadas  = [];
$rechazadas = [];

if ($usuarioId > 0) {
    $modelo = new Solicitud();
    $rows = $modelo->listarPorCliente($usuarioId);

    foreach ($rows as $r) {
        $estado = $r['estado'] ?? 'pendiente';

        switch ($estado) {
            case 'aceptada':
                $aceptadas[] = $r;
                break;
            case 'rechazada':
                $rechazadas[] = $r;
                break;
            case 'pendiente':
            default:
                $pendientes[] = $r;
                break;
        }
    }
}

// Cargar vista
require BASE_PATH . '/app/views/dashboard/cliente/misSolicitudes.php';
