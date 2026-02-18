<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
require_once BASE_PATH . '/app/controllers/finanzaController.php';

// Cargamos los datos del controlador
$data = cargarDashboardFinanzas();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finanzas | Proviservers</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboardFinanzas.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar_administrador.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header_administrador.php'; ?>

        <section id="titulo-principal" class="mb-4">
            <h1 class="fw-bold text-dark">Gestión Financiera</h1>
            <p class="text-muted mb-0">Resumen de ingresos y estado de membresías.</p>
            <div class="col-md-4">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0"></ol>
                    </nav>
                </div>
        </section>

        <section class="cards-container mb-5">
            <div class="finance-card">
                <div class="card-header-icon">
                    <div class="card-icon icon-blue"><i class="bi bi-cash-stack"></i></div>
                </div>
                <div class="card-body-content">
                    <div class="card-value">$ <?= number_format($data['ingresos_totales'], 0, ',', '.') ?></div>
                    <div class="card-label">Ingresos Totales</div>
                </div>
            </div>

            <div class="finance-card">
                <div class="card-header-icon">
                    <div class="card-icon icon-green"><i class="bi bi-person-check"></i></div>
                </div>
                <div class="card-body-content">
                    <div class="card-value"><?= $data['membresias_activas'] ?></div>
                    <div class="card-label">Membresías Activas</div>
                </div>
            </div>

            <div class="finance-card">
                <div class="card-header-icon">
                    <div class="card-icon icon-orange"><i class="bi bi-clock-history"></i></div>
                </div>
                <div class="card-body-content">
                    <div class="card-value"><?= $data['pagos_pendientes'] ?></div>
                    <div class="card-label">Pagos Pendientes</div>
                </div>
                <?php if ($data['pagos_pendientes'] > 0): ?>
                    <div class="notification-dot"></div>
                <?php endif; ?>
            </div>

            <div class="finance-card">
                <div class="card-header-icon">
                    <div class="card-icon icon-purple"><i class="bi bi-graph-up-arrow"></i></div>
                </div>
                <div class="card-body-content">
                    <div class="card-value">$ <?= number_format($data['ingresos_totales'] * 1.10, 0, ',', '.') ?></div>
                    <div class="card-label">Proyección (+10%)</div>
                </div>
            </div>
        </section>

        <section class="charts-section row g-4 mb-5">
            <div class="col-lg-8">
                <div class="chart-container bg-white p-4 rounded-4 shadow-sm h-100">
                    <h5 class="chart-title fw-bold mb-4">Evolución de Ingresos</h5>
                    <div id="lineChart"></div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="chart-container bg-white p-4 rounded-4 shadow-sm h-100">
                    <h5 class="chart-title fw-bold mb-4">Distribución por Plan</h5>
                    <div id="pieChart" class="d-flex justify-content-center"></div>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-7">
                <section class="transactions-section bg-white p-4 rounded-4 shadow-sm h-100">
                    <h5 class="chart-title fw-bold mb-3">Pagos Recientes</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Proveedor</th>
                                    <th>Plan</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($data['ultimos_pagos'])): ?>
                                    <?php foreach ($data['ultimos_pagos'] as $pago): ?>
                                        <tr>
                                            <td class="fw-bold text-secondary"><?= $pago['proveedor'] ?></td>
                                            <td><span class="badge bg-light text-dark border"><?= $pago['plan'] ?></span></td>
                                            <td class="text-success fw-bold">$<?= number_format($pago['monto'], 0, ',', '.') ?></td>
                                            <td>
                                                <?php if ($pago['estado_pago'] == 'pagado'): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success">Pagado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning bg-opacity-10 text-warning">Pendiente</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No hay movimientos.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div class="col-lg-5">
                <section class="transactions-section bg-white p-4 rounded-4 shadow-sm h-100">
                    <h5 class="chart-title fw-bold mb-3 text-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Por Vencer (15 días)
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Proveedor</th>
                                    <th>Vence</th>
                                    <th>Días</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($data['membresias_vencer'])): ?>
                                    <?php foreach ($data['membresias_vencer'] as $ven): ?>
                                        <tr>
                                            <td><?= $ven['proveedor'] ?></td>
                                            <td class="text-danger fw-bold"><?= date('d/m', strtotime($ven['fecha_fin'])) ?></td>
                                            <td><span class="badge bg-danger rounded-pill"><?= $ven['dias_restantes'] ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-success py-3">Todo al día ✨</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>

    </main>


    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script>
        // Definimos la URL base para peticiones AJAX
        const BASE_URL = "<?= BASE_URL ?>";

        // Pasamos los datos de PHP a una variable global JS llamada 'dashboardData'
        // 'finanzas.js' leerá esta variable para dibujar los gráficos
        const dashboardData = {
            ingresos: <?= json_encode($data['chart_ingresos']) ?>,
            planes: <?= json_encode($data['chart_planes']) ?>
        };
    </script>

    <!-- tu javaScript -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/app.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>


    <!-- JavaScript de Finanzas -->
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/finanzas.js"></script>
</body>

</html>