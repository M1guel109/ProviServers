<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/finanza.php';

// Capturamos en una variable el método o solicitud hecha al servidor
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $accion = $_POST['accion'] ?? '';

        // Aquí podrías agregar lógica para filtros por fecha si usas un form POST
        if ($accion === 'filtrar_rango') {
            // Ejemplo: filtrarFinanzasPorFecha();
        } 
        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';

        // API AJAX: Para recargar gráficos o datos sin refrescar la página
        if ($accion === 'api_data') {
            enviarDatosFinancierosJSON();
            exit; // Importante detener el script aquí para JSON
        }

        // Si no hay acción específica, el archivo simplemente se carga
        // y deja disponibles las funciones para que la vista las use.
        break;

    default:
        // Si intentan usar PUT o DELETE sin configurar
        // http_response_code(405);
        break;
}

// ==========================================================
// FUNCIONES DEL MÓDULO FINANZAS
// ==========================================================

/**
 * Función Principal: Carga toda la data necesaria para el Dashboard.
 * Se llama desde la vista (index.php)
 */
function cargarDashboardFinanzas()
{
    $obj = new Finanza();

    // Empaquetamos toda la información en un array asociativo
    $data = [
        // 1. Tarjetas Superiores (KPIs)
        'ingresos_totales'   => $obj->obtenerIngresosTotales(),
        'membresias_activas' => $obj->contarMembresiasActivas(),
        'pagos_pendientes'   => $obj->contarPagosPendientes(),
        
        // 2. Tablas de Datos
        'ultimos_pagos'      => $obj->obtenerUltimosPagos(6), // Límite de 6 filas
        'membresias_vencer'  => $obj->obtenerMembresiasPorVencer(),
        
        // 3. Datos para Gráficos (ApexCharts)
        'chart_ingresos'     => $obj->obtenerIngresosPorMes(),
        'chart_planes'       => $obj->obtenerDistribucionPlanes()
    ];

    return $data;
}

/**
 * Función API: Retorna los mismos datos en formato JSON.
 * Útil si quieres agregar un botón de "Actualizar datos" con JS.
 */
function enviarDatosFinancierosJSON()
{
    // Reutilizamos la lógica de carga
    $datos = cargarDashboardFinanzas();

    if ($datos) {
        header('Content-Type: application/json');
        echo json_encode($datos);
    } else {
        echo json_encode(['error' => 'No se pudieron cargar los datos financieros']);
    }
}
?>