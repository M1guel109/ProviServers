<?php
require_once BASE_PATH . '/app/helpers/session_proveedor.php';

// Datos de ejemplo (luego vendrán del controlador)
$finanzas = [
    'balance_actual' => 8450000,
    'ingresos_mes' => 3240000,
    'gastos_mes' => 1250000,
    'ganancias_mes' => 1990000,
    'proximos_pagos' => 850000,
    'facturas_pendientes' => 12,
    'comisiones_plataforma' => 324000,
    'retenciones' => 162000
];

$transacciones_recientes = [
    ['fecha' => '2025-03-08', 'concepto' => 'Pago por servicio - Plomería', 'cliente' => 'Carlos López', 'monto' => 180000, 'tipo' => 'ingreso', 'estado' => 'completado'],
    ['fecha' => '2025-03-07', 'concepto' => 'Comisión plataforma', 'cliente' => 'Proviservers', 'monto' => 18000, 'tipo' => 'gasto', 'estado' => 'completado'],
    ['fecha' => '2025-03-07', 'concepto' => 'Pago por servicio - Electricidad', 'cliente' => 'Ana Gómez', 'monto' => 250000, 'tipo' => 'ingreso', 'estado' => 'completado'],
    ['fecha' => '2025-03-06', 'concepto' => 'Compra de materiales', 'cliente' => 'Ferretería XYZ', 'monto' => 85000, 'tipo' => 'gasto', 'estado' => 'pendiente'],
    ['fecha' => '2025-03-05', 'concepto' => 'Pago por servicio - Limpieza', 'cliente' => 'María Pérez', 'monto' => 150000, 'tipo' => 'ingreso', 'estado' => 'completado'],
    ['fecha' => '2025-03-04', 'concepto' => 'Pago por servicio - Pintura', 'cliente' => 'Juan Rodríguez', 'monto' => 320000, 'tipo' => 'ingreso', 'estado' => 'completado'],
    ['fecha' => '2025-03-03', 'concepto' => 'Retención de impuestos', 'cliente' => 'DIAN', 'monto' => 45000, 'tipo' => 'gasto', 'estado' => 'completado'],
];

$facturas = [
    ['id' => 'FAC-2025-001', 'cliente' => 'Carlos López', 'fecha' => '2025-03-08', 'monto' => 180000, 'estado' => 'pagada'],
    ['id' => 'FAC-2025-002', 'cliente' => 'Ana Gómez', 'fecha' => '2025-03-07', 'monto' => 250000, 'estado' => 'pagada'],
    ['id' => 'FAC-2025-003', 'cliente' => 'María Pérez', 'fecha' => '2025-03-05', 'monto' => 150000, 'estado' => 'pagada'],
    ['id' => 'FAC-2025-004', 'cliente' => 'Juan Rodríguez', 'fecha' => '2025-03-04', 'monto' => 320000, 'estado' => 'pagada'],
    ['id' => 'FAC-2025-005', 'cliente' => 'Pedro Sánchez', 'fecha' => '2025-03-02', 'monto' => 95000, 'estado' => 'pendiente'],
];

$ingresos_meses = [
    'meses' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
    'valores' => [1850000, 2100000, 2450000, 2300000, 2680000, 2900000]
];

$gastos_meses = [
    'meses' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
    'valores' => [650000, 720000, 1250000, 840000, 950000, 1100000]
];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Finanzas</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Estilos Globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-Proveedor.css">

    <!-- CSS Específico -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/finanzas.css">
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
                    <h1>Finanzas</h1>
                    <p class="text-muted mb-0">
                        Controla tus ingresos, gastos y visualiza el estado financiero de tu negocio.
                    </p>
                </div>
                <div class="col-md-4">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/proveedor/dashboard">Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Finanzas</li>
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
                        <select class="form-select form-select-sm w-auto" id="periodo-finanzas">
                            <option value="semana">Última semana</option>
                            <option value="mes" selected>Último mes</option>
                            <option value="trimestre">Último trimestre</option>
                            <option value="año">Último año</option>
                        </select>
                        <button class="btn btn-primary btn-sm" id="aplicar-filtro">
                            <i class="bi bi-funnel"></i> Aplicar
                        </button>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalExportar">
                            <i class="bi bi-download"></i> Exportar
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- TARJETAS DE RESUMEN FINANCIERO -->
        <section class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="tarjeta-financiera">
                    <div class="icono-wrapper bg-primary-light">
                        <i class="bi bi-wallet2 icono-financiero text-primary"></i>
                    </div>
                    <div class="financiera-contenido">
                        <span class="financiera-etiqueta">Balance actual</span>
                        <span class="financiera-valor">$<?= number_format($finanzas['balance_actual'], 0, ',', '.') ?></span>
                        <span class="financiera-tendencia positiva">
                            <i class="bi bi-arrow-up"></i> +12% vs mes anterior
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="tarjeta-financiera">
                    <div class="icono-wrapper bg-success-light">
                        <i class="bi bi-cash-stack icono-financiero text-success"></i>
                    </div>
                    <div class="financiera-contenido">
                        <span class="financiera-etiqueta">Ingresos del mes</span>
                        <span class="financiera-valor">$<?= number_format($finanzas['ingresos_mes'], 0, ',', '.') ?></span>
                        <span class="financiera-tendencia positiva">
                            <i class="bi bi-arrow-up"></i> +8% vs mes anterior
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="tarjeta-financiera">
                    <div class="icono-wrapper bg-danger-light">
                        <i class="bi bi-cart icono-financiero text-danger"></i>
                    </div>
                    <div class="financiera-contenido">
                        <span class="financiera-etiqueta">Gastos del mes</span>
                        <span class="financiera-valor">$<?= number_format($finanzas['gastos_mes'], 0, ',', '.') ?></span>
                        <span class="financiera-tendencia negativa">
                            <i class="bi bi-arrow-up"></i> +15% vs mes anterior
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="tarjeta-financiera">
                    <div class="icono-wrapper bg-warning-light">
                        <i class="bi bi-graph-up-arrow icono-financiero text-warning"></i>
                    </div>
                    <div class="financiera-contenido">
                        <span class="financiera-etiqueta">Ganancias netas</span>
                        <span class="financiera-valor">$<?= number_format($finanzas['ganancias_mes'], 0, ',', '.') ?></span>
                        <span class="financiera-tendencia positiva">
                            <i class="bi bi-arrow-up"></i> +5% vs mes anterior
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- GRÁFICAS Y ESTADÍSTICAS -->
        <section class="row g-4 mb-4">
            <!-- Gráfica de ingresos vs gastos -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-bar-chart-line me-2 text-primary"></i>Ingresos vs Gastos
                        </h6>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary active" data-chart="bar">Barras</button>
                            <button class="btn btn-outline-secondary" data-chart="line">Línea</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="chartIngresosGastos" style="width:100%; max-height:300px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de resumen rápido -->
            <div class="col-lg-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card shadow-sm p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small">Comisiones plataforma</span>
                                    <h4 class="fw-bold mt-1">$<?= number_format($finanzas['comisiones_plataforma'], 0, ',', '.') ?></h4>
                                </div>
                                <div class="bg-primary-light p-3 rounded-circle">
                                    <i class="bi bi-percent text-primary fs-3"></i>
                                </div>
                            </div>
                            <small class="text-muted mt-2">10% de tus ingresos</small>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card shadow-sm p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small">Facturas pendientes</span>
                                    <h4 class="fw-bold mt-1"><?= $finanzas['facturas_pendientes'] ?></h4>
                                </div>
                                <div class="bg-warning-light p-3 rounded-circle">
                                    <i class="bi bi-file-text text-warning fs-3"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Monto total: <span class="fw-bold text-dark">$<?= number_format($finanzas['proximos_pagos'], 0, ',', '.') ?></span></small>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card shadow-sm p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small">Retenciones</span>
                                    <h4 class="fw-bold mt-1">$<?= number_format($finanzas['retenciones'], 0, ',', '.') ?></h4>
                                </div>
                                <div class="bg-danger-light p-3 rounded-circle">
                                    <i class="bi bi-file-earmark-text text-danger fs-3"></i>
                                </div>
                            </div>
                            <small class="text-muted mt-2">Próximo pago: 15 de abril</small>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- TABLAS DE TRANSACCIONES Y FACTURAS -->
        <section class="row g-4">
            <!-- Transacciones recientes -->
            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-arrow-left-right me-2 text-primary"></i>Transacciones recientes
                        </h6>
                        <a href="#" class="btn btn-link btn-sm text-primary">Ver todas</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Concepto</th>
                                        <th>Cliente</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transacciones_recientes as $trans): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($trans['fecha'])) ?></td>
                                        <td><?= htmlspecialchars($trans['concepto']) ?></td>
                                        <td><?= htmlspecialchars($trans['cliente']) ?></td>
                                        <td class="<?= $trans['tipo'] === 'ingreso' ? 'text-success' : 'text-danger' ?>">
                                            <?= $trans['tipo'] === 'ingreso' ? '+' : '-' ?>$<?= number_format($trans['monto'], 0, ',', '.') ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $trans['estado'] === 'completado' ? 'bg-success' : 'bg-warning' ?>">
                                                <?= ucfirst($trans['estado']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Facturas recientes -->
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-receipt me-2 text-primary"></i>Facturas recientes
                        </h6>
                        <a href="#" class="btn btn-link btn-sm text-primary">Ver todas</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>N° Factura</th>
                                        <th>Cliente</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($facturas as $fac): ?>
                                    <tr>
                                        <td><small><?= $fac['id'] ?></small></td>
                                        <td><?= htmlspecialchars($fac['cliente']) ?></td>
                                        <td>$<?= number_format($fac['monto'], 0, ',', '.') ?></td>
                                        <td>
                                            <span class="badge <?= $fac['estado'] === 'pagada' ? 'bg-success' : 'bg-warning' ?>">
                                                <?= ucfirst($fac['estado']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" title="Descargar PDF">
                                                <i class="bi bi-file-pdf"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- RESUMEN DE INGRESOS POR CATEGORÍA -->
        <section class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-pie-chart me-2 text-primary"></i>Ingresos por categoría de servicio
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <canvas id="chartIngresosCategorias" style="width:100%; max-height:250px;"></canvas>
                            </div>
                            <div class="col-md-4">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span><i class="bi bi-droplet me-2 text-primary"></i>Plomería</span>
                                        <span class="fw-bold">$1,250,000</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span><i class="bi bi-lightning-charge me-2 text-warning"></i>Electricidad</span>
                                        <span class="fw-bold">$980,000</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span><i class="bi bi-brush me-2 text-success"></i>Pintura</span>
                                        <span class="fw-bold">$720,000</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span><i class="bi bi-tree me-2 text-info"></i>Jardinería</span>
                                        <span class="fw-bold">$450,000</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span><i class="bi bi-house me-2 text-secondary"></i>Limpieza</span>
                                        <span class="fw-bold">$380,000</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- MODAL EXPORTAR REPORTES -->
    <div class="modal fade" id="modalExportar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-download me-2"></i>Exportar reporte financiero
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted mb-3">Selecciona el formato y período para exportar:</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Formato</label>
                        <select class="form-select">
                            <option value="pdf">PDF - Documento</option>
                            <option value="excel">Excel - Hoja de cálculo</option>
                            <option value="csv">CSV - Datos simples</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Período</label>
                        <select class="form-select">
                            <option value="mes">Este mes</option>
                            <option value="trimestre">Último trimestre</option>
                            <option value="año">Último año</option>
                            <option value="todo">Todo el historial</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Incluir</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="incluirTransacciones" checked>
                            <label class="form-check-label" for="incluirTransacciones">Transacciones</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="incluirFacturas" checked>
                            <label class="form-check-label" for="incluirFacturas">Facturas</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="incluirGraficas" checked>
                            <label class="form-check-label" for="incluirGraficas">Gráficas resumen</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success">
                        <i class="bi bi-download me-2"></i>Exportar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/finanzas.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
        // Pasar datos de PHP a JavaScript
        const datosIngresos = <?= json_encode($ingresos_meses) ?>;
        const datosGastos = <?= json_encode($gastos_meses) ?>;
    </script>
</body>

</html>