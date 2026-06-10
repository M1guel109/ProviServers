<?php
require_once BASE_PATH . '/app/helpers/session-admin.php';
require_once BASE_PATH . '/app/models/finanza.php';

$modelo  = new Finanza();
$reporte = $modelo->obtenerReporteIngresos();

$g       = $reporte['global'];
$porMes  = $reporte['porMes'];
$porPlan = $reporte['porPlan'];
$recientes = $reporte['recientes'];

$fmt = fn($n) => '$' . number_format((float)$n, 0, ',', '.');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Reporte de Ingresos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/estilos-tablas.css">
</head>
<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar-administrador.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-administrador.php'; ?>

        <!-- Título -->
        <section id="titulo-principal">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Reporte de Ingresos por Membresías</h1>
                    <p class="text-muted mb-0">Ingresos generados por la venta de planes de membresía en la plataforma.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="<?= BASE_URL ?>/admin/reporte?tipo=ingresos"
                       target="_blank"
                       class="btn btn-primary">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Exportar PDF
                    </a>
                </div>
            </div>
        </section>

        <!-- KPIs -->
        <section class="mt-4">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.75rem;">Total pagos registrados</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format((int)$g['total_pagos']) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.75rem;">Ingresos confirmados</small>
                            <h2 class="fw-bold text-success mb-0"><?= $fmt($g['confirmado']) ?></h2>
                            <small class="text-muted"><?= number_format((int)$g['pagos_confirmados']) ?> pagos</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.75rem;">Monto pendiente</small>
                            <h2 class="fw-bold text-warning mb-0"><?= $fmt($g['pendiente']) ?></h2>
                            <small class="text-muted"><?= number_format((int)$g['pagos_pendientes']) ?> pagos</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.75rem;">Planes vendidos</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format(array_sum(array_column($porPlan, 'ventas'))) ?></h2>
                            <small class="text-muted"><?= count($porPlan) ?> planes distintos</small>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Gráfica + Por plan -->
        <div class="row g-4 mb-4">

            <!-- Gráfica de tendencia -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold border-0 pt-3">
                        <i class="bi bi-graph-up-arrow text-primary me-2"></i>Tendencia de ingresos por mes
                    </div>
                    <div class="card-body">
                        <?php if (!empty($porMes)): ?>
                            <div id="chartIngresos"></div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-bar-chart fs-2 d-block mb-2"></i>
                                Sin ingresos confirmados aún.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Por plan -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold border-0 pt-3">
                        <i class="bi bi-gem text-primary me-2"></i>Ingresos por plan de membresía
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($porPlan)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Plan</th>
                                            <th class="text-center">Ventas</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($porPlan as $plan): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($plan['plan']) ?></td>
                                                <td class="text-center"><?= (int)$plan['ventas'] ?></td>
                                                <td class="text-end text-success fw-bold"><?= $fmt($plan['total']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-gem fs-2 d-block mb-2"></i>
                                Sin ventas de membresías aún.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimos pagos -->
        <section class="mb-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold border-0 pt-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-clock-history text-primary me-2"></i>Últimos pagos de membresías</span>
                    <small class="text-muted fw-normal">Mostrando los 50 más recientes</small>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($recientes)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Proveedor</th>
                                        <th>Plan</th>
                                        <th>Método</th>
                                        <th class="text-end">Monto</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recientes as $p): ?>
                                        <tr>
                                            <td class="text-muted small">
                                                <?= $p['fecha_pago'] ? date('d/m/Y', strtotime($p['fecha_pago'])) : '—' ?>
                                            </td>
                                            <td><?= htmlspecialchars($p['proveedor']) ?></td>
                                            <td><?= htmlspecialchars($p['plan']) ?></td>
                                            <td class="text-muted small"><?= htmlspecialchars($p['metodo_pago'] ?? '—') ?></td>
                                            <td class="text-end fw-bold"><?= $fmt($p['monto']) ?></td>
                                            <td class="text-center">
                                                <?php if ($p['estado_pago'] === 'pagado'): ?>
                                                    <span class="badge bg-success">Pagado</span>
                                                <?php elseif ($p['estado_pago'] === 'pendiente'): ?>
                                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?= htmlspecialchars($p['estado_pago']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-receipt fs-2 d-block mb-2"></i>
                            Sin pagos registrados aún.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>

    <?php if (!empty($porMes)): ?>
    <script>
        (function () {
            const periodos = <?= json_encode(array_column(array_reverse($porMes), 'periodo')) ?>;
            const totales  = <?= json_encode(array_map(fn($m) => (float)$m['total'], array_reverse($porMes))) ?>;

            new ApexCharts(document.querySelector('#chartIngresos'), {
                chart: { type: 'area', height: 270, toolbar: { show: false }, zoom: { enabled: false } },
                series: [{ name: 'Ingresos confirmados', data: totales }],
                xaxis: { categories: periodos, labels: { style: { fontSize: '11px' } } },
                yaxis: { labels: { formatter: v => '$' + v.toLocaleString('es-CO') } },
                colors: ['#0d6efd'],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: false },
                tooltip: { y: { formatter: v => '$' + v.toLocaleString('es-CO') } },
            }).render();
        })();
    </script>
    <?php endif; ?>

</body>
</html>
