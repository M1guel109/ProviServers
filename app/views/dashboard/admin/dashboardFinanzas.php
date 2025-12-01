<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finanzas | Proviservers</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- CSS de estilos globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- CSS de Finanzas -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboardFinanzas.css">
</head>

<body>
    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_administrador.php'; ?>

    <main class="contenido">
        <!-- HEADER -->
        <?php include_once __DIR__ . '/../../layouts/header_administrador.php'; ?>

        <!-- Título Principal -->
        <section id="titulo-principal">
            <h1>Finanzas</h1>
            <p class="text-muted mb-0">
                Gestiona y visualiza los ingresos, gastos y transacciones financieras de la plataforma Proviservers.
            </p>
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/administrador/dashboard">Inicio</a></li>
                    <li class="breadcrumb-item">ProviServers</li>
                    <li class="breadcrumb-item">Admin</li>
                    <li class="breadcrumb-item active" aria-current="page">Finanzas</li>
                </ol>
            </nav>
        </section>

        <!-- Cards Resumen -->
        <section class="cards-container">
            <div class="finance-card">
                <div class="card-header">
                    <div class="card-icon icon-blue">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
                <div class="card-value">$2,845,320</div>
                <div class="card-label">Ingresos Totales</div>
                <div class="card-trend trend-positive">
                    <i class="bi bi-arrow-up"></i>
                    <span>+12.5% vs mes anterior</span>
                </div>
            </div>

            <div class="finance-card">
                <div class="card-header">
                    <div class="card-icon icon-green">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
                <div class="card-value">$1,234,500</div>
                <div class="card-label">Ganancias Netas</div>
                <div class="card-trend trend-positive">
                    <i class="bi bi-arrow-up"></i>
                    <span>+8.3% vs mes anterior</span>
                </div>
            </div>

            <div class="finance-card">
                <div class="card-header">
                    <div class="card-icon icon-orange">
                        <i class="bi bi-arrow-down-circle"></i>
                    </div>
                </div>
                <div class="card-value">$895,420</div>
                <div class="card-label">Gastos Operativos</div>
                <div class="card-trend trend-negative">
                    <i class="bi bi-arrow-down"></i>
                    <span>-3.2% vs mes anterior</span>
                </div>
            </div>

            <div class="finance-card">
                <div class="card-header">
                    <div class="card-icon icon-purple">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <div class="card-value">$456,780</div>
                <div class="card-label">Pagos Pendientes</div>
                <div class="card-trend trend-positive">
                    <i class="bi bi-arrow-up"></i>
                    <span>124 transacciones</span>
                </div>
            </div>
        </section>

        <!-- Charts Section -->
        <section class="charts-section">
            <div class="chart-container">
                <div class="chart-header">
                    <h2 class="chart-title">Flujo de Ingresos y Gastos</h2>
                    <select class="chart-select" id="periodoSelect">
                        <option value="mensual">Mensual</option>
                        <option value="semanal">Semanal</option>
                        <option value="anual">Anual</option>
                    </select>
                </div>
                <div class="chart-canvas">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>

            <div class="chart-container">
                <div class="chart-header">
                    <h2 class="chart-title">Distribución de Gastos</h2>
                </div>
                <div class="chart-canvas">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </section>

        <!-- Transactions Table -->
        <section class="transactions-section">
            <div class="section-header">
                <h2 class="chart-title">Transacciones Recientes</h2>
                <button class="btn-primary-proviservers" data-bs-toggle="modal" data-bs-target="#nuevaTransaccionModal">
                    <i class="bi bi-plus-circle"></i> Nueva Transacción
                </button>
            </div>

            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#TXN-0001</td>
                        <td>27 Nov 2025</td>
                        <td>Pago de servicio - Instalación eléctrica</td>
                        <td>Ingreso</td>
                        <td class="amount-positive">+$350,000</td>
                        <td><span class="status-badge status-completado">Completado</span></td>
                    </tr>
                    <tr>
                        <td>#TXN-0002</td>
                        <td>26 Nov 2025</td>
                        <td>Compra de materiales</td>
                        <td>Gasto</td>
                        <td class="amount-negative">-$125,500</td>
                        <td><span class="status-badge status-completado">Completado</span></td>
                    </tr>
                    <tr>
                        <td>#TXN-0003</td>
                        <td>25 Nov 2025</td>
                        <td>Pago de servicio - Plomería</td>
                        <td>Ingreso</td>
                        <td class="amount-positive">+$280,000</td>
                        <td><span class="status-badge status-pendiente">Pendiente</span></td>
                    </tr>
                    <tr>
                        <td>#TXN-0004</td>
                        <td>24 Nov 2025</td>
                        <td>Nómina empleados</td>
                        <td>Gasto</td>
                        <td class="amount-negative">-$450,000</td>
                        <td><span class="status-badge status-completado">Completado</span></td>
                    </tr>
                    <tr>
                        <td>#TXN-0005</td>
                        <td>23 Nov 2025</td>
                        <td>Servicio cancelado</td>
                        <td>Reembolso</td>
                        <td class="amount-negative">-$180,000</td>
                        <td><span class="status-badge status-cancelado">Cancelado</span></td>
                    </tr>
                    <tr>
                        <td>#TXN-0006</td>
                        <td>22 Nov 2025</td>
                        <td>Pago de servicio - Construcción</td>
                        <td>Ingreso</td>
                        <td class="amount-positive">+$520,000</td>
                        <td><span class="status-badge status-completado">Completado</span></td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>

    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- JavaScript de Finanzas -->
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/finanzas.js"></script>
</body>

</html>