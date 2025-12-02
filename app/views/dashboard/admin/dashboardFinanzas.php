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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/finanzas/css/finanzas.css">
</head>

<body>
    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_administrador.php'; ?>

    <main class="contenido">
        <!-- HEADER -->
        <?php include_once __DIR__ . '/../../layouts/header_administrador.php'; ?>

        <!-- Título Principal -->
        <section id="titulo-principal">
            <h1>Gestión Financiera</h1>
            <p class="text-muted mb-3">
                Administra los ingresos por membresías, pagos de proveedores y el flujo financiero de la plataforma ProviServers.
            </p>
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Inicio</a></li>
                    <li class="breadcrumb-item">Administrador</li>
                    <li class="breadcrumb-item active" aria-current="page">Finanzas</li>
                </ol>
            </nav>
        </section>

        <!-- Cards Resumen Financiero -->
        <section class="cards-container">
            <!-- Ingresos Totales por Membresías -->
            <div class="finance-card">
                <div class="card-header">
                    <div class="card-icon icon-blue">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
                <div class="card-value">$2,845,320</div>
                <div class="card-label">Ingresos por Membresías</div>
                <div class="card-trend trend-positive">
                    <i class="bi bi-arrow-up"></i>
                    <span>+12.5% vs mes anterior</span>
                </div>
            </div>

            <!-- Membresías Activas -->
            <div class="finance-card">
                <div class="card-header">
                    <div class="card-icon icon-green">
                        <i class="bi bi-person-check"></i>
                    </div>
                </div>
                <div class="card-value">147</div>
                <div class="card-label">Membresías Activas</div>
                <div class="card-trend trend-positive">
                    <i class="bi bi-arrow-up"></i>
                    <span>+8 nuevas este mes</span>
                </div>
            </div>

            <!-- Pagos Pendientes -->
            <div class="finance-card">
                <div class="card-header">
                    <div class="card-icon icon-orange">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <div class="card-value">12</div>
                <div class="card-label">Pagos Pendientes</div>
                <div class="card-trend trend-negative">
                    <i class="bi bi-exclamation-circle"></i>
                    <span>Requieren verificación</span>
                </div>
            </div>

            <!-- Proyección Mensual -->
            <div class="finance-card">
                <div class="card-header">
                    <div class="card-icon icon-purple">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
                <div class="card-value">$3,120,000</div>
                <div class="card-label">Proyección Este Mes</div>
                <div class="card-trend trend-positive">
                    <i class="bi bi-arrow-up"></i>
                    <span>Basado en renovaciones</span>
                </div>
            </div>
        </section>

        <!-- Charts Section -->
        <section class="charts-section">
            <!-- Gráfico de Ingresos por Membresías -->
            <div class="chart-container">
                <div class="chart-header">
                    <h2 class="chart-title">Ingresos por Membresías</h2>
                    <select class="chart-select" id="periodoSelect">
                        <option value="mensual">Mensual</option>
                        <option value="trimestral">Trimestral</option>
                        <option value="anual">Anual</option>
                    </select>
                </div>
                <div class="chart-canvas">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>

            <!-- Distribución por Tipo de Membresía -->
            <div class="chart-container">
                <div class="chart-header">
                    <h2 class="chart-title">Distribución por Plan</h2>
                </div>
                <div class="chart-canvas">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </section>

        <!-- Tabla de Pagos Recientes -->
        <section class="transactions-section">
            <div class="section-header">
                <h2 class="chart-title">Pagos de Membresías Recientes</h2>
                <button class="btn-primary-proviservers" data-bs-toggle="modal" data-bs-target="#filtrosModal">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
            </div>

            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>ID Pago</th>
                        <th>Proveedor</th>
                        <th>Plan</th>
                        <th>Método</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#PAY-0001</td>
                        <td>Juan Pérez</td>
                        <td><span class="badge bg-primary">Premium</span></td>
                        <td>Transferencia</td>
                        <td class="amount-positive">$150,000</td>
                        <td>27 Nov 2025</td>
                        <td><span class="status-badge status-completado">Verificado</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#PAY-0002</td>
                        <td>María González</td>
                        <td><span class="badge bg-success">Basic</span></td>
                        <td>PSE</td>
                        <td class="amount-positive">$80,000</td>
                        <td>26 Nov 2025</td>
                        <td><span class="status-badge status-completado">Verificado</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#PAY-0003</td>
                        <td>Carlos Rodríguez</td>
                        <td><span class="badge bg-primary">Premium</span></td>
                        <td>Transferencia</td>
                        <td class="amount-positive">$150,000</td>
                        <td>25 Nov 2025</td>
                        <td><span class="status-badge status-pendiente">Pendiente</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-success" title="Verificar">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Rechazar">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#PAY-0004</td>
                        <td>Ana Martínez</td>
                        <td><span class="badge bg-success">Basic</span></td>
                        <td>PSE</td>
                        <td class="amount-positive">$80,000</td>
                        <td>24 Nov 2025</td>
                        <td><span class="status-badge status-completado">Verificado</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#PAY-0005</td>
                        <td>Luis Ramírez</td>
                        <td><span class="badge bg-secondary">Free → Basic</span></td>
                        <td>Efectivo</td>
                        <td class="amount-positive">$80,000</td>
                        <td>23 Nov 2025</td>
                        <td><span class="status-badge status-pendiente">Pendiente</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-success" title="Verificar">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Rechazar">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#PAY-0006</td>
                        <td>Patricia Silva</td>
                        <td><span class="badge bg-primary">Premium</span></td>
                        <td>Tarjeta</td>
                        <td class="amount-positive">$150,000</td>
                        <td>22 Nov 2025</td>
                        <td><span class="status-badge status-completado">Verificado</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Sección de Membresías por Vencer -->
        <section class="transactions-section">
            <div class="section-header">
                <h2 class="chart-title">
                    <i class="bi bi-exclamation-triangle text-warning"></i>
                    Membresías Próximas a Vencer
                </h2>
                <span class="badge bg-warning text-dark">8 proveedores</span>
            </div>

            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>Proveedor</th>
                        <th>Plan Actual</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Vencimiento</th>
                        <th>Días Restantes</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Roberto Sánchez</td>
                        <td><span class="badge bg-success">Basic</span></td>
                        <td>28 Oct 2025</td>
                        <td>28 Dic 2025</td>
                        <td><span class="text-warning fw-bold">5 días</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" title="Enviar recordatorio">
                                <i class="bi bi-envelope"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>Sandra López</td>
                        <td><span class="badge bg-primary">Premium</span></td>
                        <td>05 Nov 2025</td>
                        <td>05 Ene 2026</td>
                        <td><span class="text-warning fw-bold">12 días</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" title="Enviar recordatorio">
                                <i class="bi bi-envelope"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>Miguel Torres</td>
                        <td><span class="badge bg-success">Basic</span></td>
                        <td>15 Nov 2025</td>
                        <td>15 Ene 2026</td>
                        <td><span class="text-info fw-bold">22 días</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" title="Enviar recordatorio">
                                <i class="bi bi-envelope"></i>
                            </button>
                        </td>
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
    <script src="<?= BASE_URL ?>/public/assets/finanzas/js/finanzas.js"></script>
</body>

</html>