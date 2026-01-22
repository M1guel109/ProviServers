<?php
// app/controllers/clienteServiciosContratadosController.php

require_once __DIR__ . '/../helpers/alert_helper.php';
// Si tienes un helper de sesión específico para cliente, úsalo aquí:
// require_once __DIR__ . '/../helpers/session_cliente.php';

require_once __DIR__ . '/../models/Solicitud.php';

session_start();

$usuarioId = $_SESSION['user']['id'] ?? null;

$serviciosEnCurso      = [];
$serviciosProgramados  = [];
$serviciosCompletados  = [];
$serviciosCancelados   = [];

if ($usuarioId) {
    $modelo = new Solicitud();
    $contratos = $modelo->listarContratosPorClienteUsuario((int)$usuarioId);

    $hoy = date('Y-m-d');

    foreach ($contratos as $c) {
        $estado    = $c['estado_contrato'] ?? 'pendiente';
        $fechaRef  = $c['fecha_ejecucion'] ?: $c['fecha_preferida'] ?: $c['fecha_solicitud'];

        switch ($estado) {
            case 'finalizado':
                $serviciosCompletados[] = $c;
                break;

            case 'cancelado':
                $serviciosCancelados[] = $c;
                break;

            case 'pendiente':
            case 'confirmado':
            case 'en_proceso':
            default:
                // Regla simple:
                // - Si está pendiente/confirmado y la fecha es futura => Programado
                // - Si está en_proceso o la fecha ya pasó/igual => En curso
                if ($fechaRef && $fechaRef > $hoy && in_array($estado, ['pendiente','confirmado'], true)) {
                    $serviciosProgramados[] = $c;
                } else {
                    $serviciosEnCurso[] = $c;
                }
                break;
        }
    }
}

// Finalmente cargamos la vista
require BASE_PATH . '/app/views/dashboard/cliente/serviciosContratados.php';
