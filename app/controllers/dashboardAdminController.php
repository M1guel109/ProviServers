<?php
require_once __DIR__ . '/../models/DashboardAdmin.php';

class DashboardController {
    private $model;

    public function __construct() {
        $this->model = new DashboardModel();
    }

    // Esta función la llamas desde AJAX/Fetch en tu JS
    public function obtenerDatosAjax() {
        // Importante: Decirle al navegador que esto es JSON
        header('Content-Type: application/json');

        // Validar sesión si es necesario
        // if (!isset($_SESSION['user'])) { echo json_encode(['error' => 'Auth']); exit; }

        $anioActual = date('Y');

        // 1. Obtener datos crudos del modelo
        $usuarios = $this->model->obtenerConteoUsuarios();
        $serviciosRaw = $this->model->obtenerMetricasServicios($anioActual);
        $servicioTop = $this->model->obtenerServicioTop();

        // 2. Procesar datos para ApexCharts (Array de 12 meses rellenos con 0)
        $dataGrafica = array_fill(0, 12, 0); 
        
        foreach ($serviciosRaw as $dato) {
            // Restamos 1 al mes porque los arrays en programación empiezan en 0
            // Enero (Mes 1) -> Posición 0
            $indice = intval($dato['mes']) - 1;
            $dataGrafica[$indice] = intval($dato['total']);
        }

        // 3. Devolver respuesta limpia
        echo json_encode([
            'success' => true,
            'tarjetas' => [
                'clientes' => $usuarios['total_clientes'],
                'proveedores' => $usuarios['total_proveedores']
            ],
            'grafica_principal' => $dataGrafica, // Array simple [10, 20, 5, 0...]
            'servicio_destacado' => $servicioTop
        ]);
        exit; // Detener ejecución para que no se imprima nada más
    }
}