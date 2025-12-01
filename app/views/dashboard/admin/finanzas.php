<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finanzas - ProviServers</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo">
            <svg width="180" height="40" viewBox="0 0 180 40" fill="none">
                <text x="0" y="28" font-family="Roboto" font-size="24" font-weight="700" fill="#0066FF">PROVISERVERS</text>
            </svg>
        </div>
        
        <nav>
            <div class="menu-item">
                <i class="bi bi-speedometer2"></i>
                <span>Panel Principal</span>
            </div>
            <div class="menu-item">
                <i class="bi bi-people"></i>
                <span>Usuarios</span>
            </div>
            <div class="menu-item">
                <i class="bi bi-grid"></i>
                <span>Categorías de Servicios</span>
            </div>
            <div class="menu-item">
                <i class="bi bi-file-text"></i>
                <span>Reportes</span>
            </div>
            <div class="menu-item">
                <i class="bi bi-graph-up"></i>
                <span>Estadísticas</span>
            </div>
            <div class="menu-item active">
                <i class="bi bi-cash-coin"></i>
                <span>Finanzas</span>
            </div>
            <div class="menu-item">
                <i class="bi bi-calendar3"></i>
                <span>Calendario</span>
            </div>
            <div class="menu-item">
                <i class="bi bi-receipt"></i>
                <span>Facturación</span>
            </div>
            <div class="menu-item">
                <i class="bi bi-megaphone"></i>
                <span>Marketing</span>
            </div>
            <div class="menu-item">
                <i class="bi bi-plug"></i>
                <span>Integraciones</span>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="header-title">
                <h1>Finanzas</h1>
                <div class="breadcrumb">
                    <span>Inicio</span>
                    <span>></span>
                    <span>ProviServers</span>
                    <span>></span>
                    <span>Admin</span>
                    <span>></span>
                    <span>Finanzas</span>
                </div>
            </div>
        </div>

        <!-- Cards Resumen -->
        <div class="cards-container">
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
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
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
        </div>

        <!-- Transactions Table -->
        <div class="transactions-section">
            <div class="section-header">
                <h2 class="chart-title">Transacciones Recientes</h2>
                <button class="btn-primary-proviservers">
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
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>