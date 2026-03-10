<?php
require_once BASE_PATH . '/app/helpers/session_proveedor.php';

// Aquí iría la lógica para obtener datos reales desde el controlador
// Por ahora usamos datos de ejemplo
$estadisticas = [
    'ingresos_mes' => 2450000,
    'servicios_mes' => 48,
    'clientes_nuevos' => 12,
    'calificacion_promedio' => 4.8,
    'servicios_completados' => 156,
    'tasa_aceptacion' => 92,
    'tiempo_respuesta_promedio' => 2.5,
    'ingresos_anuales' => 18350000
];

$ingresos_meses = [
    'meses' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
    'valores' => [1850000, 2100000, 1950000, 2300000, 2450000, 2680000, 2520000, 2780000, 2650000, 2900000, 2850000, 3100000]
];

$servicios_categorias = [
    'categorias' => ['Plomería', 'Electricidad', 'Limpieza', 'Pintura', 'Jardinería', 'Carpintería'],
    'valores' => [42, 38, 25, 18, 15, 12]
];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Estadísticas</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Estilos Globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboard-Proveedor.css">

    <!-- CSS Específico -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/estadisticas.css">
</head>

<body>
    <!-- Sidebar Proveedor -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_proveedor.php'; ?>

    <main class="contenido">
        <!-- Header Proveedor -->
        <?php include_once __DIR__ . '/../../layouts/header_proveedor.php'; ?>

        <!-- TÍTULO CON BREADCRUMB -->
        <section id="titulo-principal">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Estadísticas</h1>
                    <p class="text-muted mb-0">
                        Visualiza el rendimiento de tu negocio, ingresos, servicios y más.
                    </p>
                </div>
                <div class="col-md-4">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/proveedor/dashboard">Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Estadísticas</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <!-- FILTRO DE PERÍODO -->
        <section class="filtros-container mb-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="mb-0 fw-bold">Período de análisis</h6>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <select class="form-select form-select-sm w-auto" id="periodo-estadisticas">
                            <option value="semana">Última semana</option>
                            <option value="mes" selected>Último mes</option>
                            <option value="trimestre">Último trimestre</option>
                            <option value="año">Último año</option>
                        </select>
                        <button class="btn btn-primary btn-sm" id="aplicar-filtro">
                            <i class="bi bi-funnel"></i> Aplicar
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- TARJETAS DE ESTADÍSTICAS PRINCIPALES -->
        <section class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="tarjeta-estadistica">
                    <div class="icono-wrapper bg-primary-light">
                        <i class="bi bi-cash-coin icono-estadistica text-primary"></i>
                    </div>
                    <div class="estadistica-contenido">
                        <span class="estadistica-valor">$<?= number_format($estadisticas['ingresos_mes'], 0, ',', '.') ?></span>
                        <span class="estadistica-etiqueta">Ingresos del mes</span>
                        <span class="estadistica-tendencia positiva">
                            <i class="bi bi-arrow-up"></i> 12% vs mes anterior
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="tarjeta-estadistica">
                    <div class="icono-wrapper bg-success-light">
                        <i class="bi bi-briefcase icono-estadistica text-success"></i>
                    </div>
                    <div class="estadistica-contenido">
                        <span class="estadistica-valor"><?= $estadisticas['servicios_mes'] ?></span>
                        <span class="estadistica-etiqueta">Servicios realizados</span>
                        <span class="estadistica-tendencia positiva">
                            <i class="bi bi-arrow-up"></i> 8 nuevos
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="tarjeta-estadistica">
                    <div class="icono-wrapper bg-warning-light">
                        <i class="bi bi-star icono-estadistica text-warning"></i>
                    </div>
                    <div class="estadistica-contenido">
                        <span class="estadistica-valor"><?= $estadisticas['calificacion_promedio'] ?></span>
                        <span class="estadistica-etiqueta">Calificación promedio</span>
                        <span class="estadistica-tendencia positiva">
                            <i class="bi bi-arrow-up"></i> +0.2 este mes
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="tarjeta-estadistica">
                    <div class="icono-wrapper bg-info-light">
                        <i class="bi bi-people icono-estadistica text-info"></i>
                    </div>
                    <div class="estadistica-contenido">
                        <span class="estadistica-valor"><?= $estadisticas['clientes_nuevos'] ?></span>
                        <span class="estadistica-etiqueta">Clientes nuevos</span>
                        <span class="estadistica-tendencia positiva">
                            <i class="bi bi-arrow-up"></i> +3 esta semana
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- GRÁFICAS PRINCIPALES -->
        <section class="row g-4 mb-4">
            <!-- Gráfica de ingresos -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-graph-up me-2 text-primary"></i>Ingresos mensuales
                        </h6>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary active" data-chart="line">Línea</button>
                            <button class="btn btn-outline-secondary" data-chart="bar">Barras</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="chartIngresos" style="width:100%; max-height:300px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de KPIs secundarios -->
            <div class="col-lg-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card shadow-sm p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small">Servicios completados</span>
                                    <h3 class="fw-bold mt-1"><?= $estadisticas['servicios_completados'] ?></h3>
                                </div>
                                <div class="bg-success-light p-3 rounded-circle">
                                    <i class="bi bi-check-circle-fill text-success fs-3"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: 85%"></div>
                                </div>
                                <small class="text-muted mt-2 d-block">85% de meta mensual</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card shadow-sm p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small">Tasa de aceptación</span>
                                    <h3 class="fw-bold mt-1"><?= $estadisticas['tasa_aceptacion'] ?>%</h3>
                                </div>
                                <div class="bg-primary-light p-3 rounded-circle">
                                    <i class="bi bi-hand-thumbs-up-fill text-primary fs-3"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: <?= $estadisticas['tasa_aceptacion'] ?>%"></div>
                                </div>
                                <small class="text-muted mt-2 d-block">+5% vs mes anterior</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card shadow-sm p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small">Tiempo respuesta</span>
                                    <h3 class="fw-bold mt-1"><?= $estadisticas['tiempo_respuesta_promedio'] ?> hrs</h3>
                                </div>
                                <div class="bg-warning-light p-3 rounded-circle">
                                    <i class="bi bi-clock-fill text-warning fs-3"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: 70%"></div>
                                </div>
                                <small class="text-muted mt-2 d-block">Meta: 2 horas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- GRÁFICAS SECUNDARIAS -->
        <section class="row g-4">
            <!-- Distribución por categorías -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-pie-chart me-2 text-primary"></i>Servicios por categoría
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="chartCategorias" style="width:100%; max-height:300px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Últimas actividades / Top servicios -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-trophy me-2 text-primary"></i>Servicios más solicitados
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <span class="fw-bold">Plomería</span>
                                    <br>
                                    <small class="text-muted">Reparaciones y mantenimiento</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">42</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <span class="fw-bold">Electricidad</span>
                                    <br>
                                    <small class="text-muted">Instalaciones y reparaciones</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">38</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <span class="fw-bold">Limpieza</span>
                                    <br>
                                    <small class="text-muted">Hogar y oficinas</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">25</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <span class="fw-bold">Pintura</span>
                                    <br>
                                    <small class="text-muted">Interiores y exteriores</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">18</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <span class="fw-bold">Jardinería</span>
                                    <br>
                                    <small class="text-muted">Mantenimiento de áreas verdes</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">15</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- RESUMEN ANUAL -->
        <section class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-calendar-check me-2 text-primary"></i>Resumen anual
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h5 class="fw-bold">$<?= number_format($estadisticas['ingresos_anuales'], 0, ',', '.') ?></h5>
                                <small class="text-muted">Ingresos totales</small>
                            </div>
                            <div class="col-md-3">
                                <h5 class="fw-bold">156</h5>
                                <small class="text-muted">Servicios completados</small>
                            </div>
                            <div class="col-md-3">
                                <h5 class="fw-bold">48</h5>
                                <small class="text-muted">Clientes nuevos</small>
                            </div>
                            <div class="col-md-3">
                                <h5 class="fw-bold">4.9</h5>
                                <small class="text-muted">Calificación promedio</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/estadisticas.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
        // Pasar datos de PHP a JavaScript
        const datosIngresos = <?= json_encode($ingresos_meses) ?>;
        const datosCategorias = <?= json_encode($servicios_categorias) ?>;
    </script>
</body>

</html>