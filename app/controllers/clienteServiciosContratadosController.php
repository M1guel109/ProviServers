<?php
// app/controllers/clienteServiciosContratadosController.php

require_once __DIR__ . '/../helpers/alert_helper.php';
// Si tienes un helper de sesión específico para cliente, úsalo aquí:
// require_once __DIR__ . '/../helpers/session_cliente.php';

require_once __DIR__ . '/../models/ServicioContratado.php';

session_start();

$usuarioId = $_SESSION['user']['id'] ?? null;

$serviciosEnCurso      = [];
$serviciosProgramados  = [];
$serviciosCompletados  = [];
$serviciosCancelados   = [];

if ($usuarioId) {
    $modelo = new ServicioContratado();
    $contratos = $modelo->listarPorClienteUsuario((int)$usuarioId);

    $hoy = date('Y-m-d');

    foreach ($contratos as $c) {
        $estado = $c['estado'] ?? 'pendiente';

        switch ($estado) {
            case 'finalizado':
                $serviciosCompletados[] = $c;
                break;

            case 'cancelado_cliente':
            case 'cancelado_proveedor':
                $serviciosCancelados[] = $c;
                break;

            case 'pendiente':
            case 'confirmado':
                $serviciosProgramados[] = $c;
                break;

            case 'en_proceso':
            default:
                $serviciosEnCurso[] = $c;
                break;
        }
    }
}

// Finalmente cargamos la vista
require BASE_PATH . '/app/views/dashboard/cliente/serviciosContratados.php';
