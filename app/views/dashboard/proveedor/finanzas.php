<?php
require_once BASE_PATH . '/app/helpers/session-proveedor.php';
require_once BASE_PATH . '/config/database.php';

$uid = (int)($_SESSION['user']['id'] ?? 0);

$ingresosMes     = 0.0;
$ingresosAnuales = 0.0;
$contratos       = [];
$ingresos_meses  = ['meses' => [], 'valores' => []];
$gastos_meses    = ['meses' => [], 'valores' => []];

try {
    $db  = new Conexion();
    $pdo = $db->getConexion();

    // Ingresos del mes actual
    $st = $pdo->prepare("
        SELECT COALESCE(SUM(COALESCE(cot.precio, sol.presupuesto_estimado, 0)), 0)
        FROM servicios_contratados sc
        INNER JOIN proveedores p ON p.id = sc.proveedor_id
        LEFT JOIN cotizaciones cot ON cot.id = sc.cotizacion_id
        LEFT JOIN solicitudes sol  ON sol.id  = sc.solicitud_id
        WHERE p.usuario_id = ?
          AND sc.estado = 'finalizado'
          AND DATE_FORMAT(COALESCE(sc.fecha_ejecucion, sc.modified_at, sc.created_at), '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
    ");
    $st->execute([$uid]);
    $ingresosMes = (float)$st->fetchColumn();

    // Ingresos totales del año
    $st = $pdo->prepare("
        SELECT COALESCE(SUM(COALESCE(cot.precio, sol.presupuesto_estimado, 0)), 0)
        FROM servicios_contratados sc
        INNER JOIN proveedores p ON p.id = sc.proveedor_id
        LEFT JOIN cotizaciones cot ON cot.id = sc.cotizacion_id
        LEFT JOIN solicitudes sol  ON sol.id  = sc.solicitud_id
        WHERE p.usuario_id = ? AND sc.estado = 'finalizado'
          AND YEAR(COALESCE(sc.fecha_ejecucion, sc.created_at)) = YEAR(CURDATE())
    ");
    $st->execute([$uid]);
    $ingresosAnuales = (float)$st->fetchColumn();

    // Contratos recientes para tabla de transacciones y facturas
    $st = $pdo->prepare("
        SELECT sc.id AS contrato_id,
               COALESCE(sc.fecha_ejecucion, sc.modified_at, sc.created_at) AS fecha,
               CONCAT(cl.nombres, ' ', cl.apellidos) AS cliente,
               sv.nombre AS servicio,
               COALESCE(cot.precio, sol.presupuesto_estimado, 0) AS monto,
               sc.estado
        FROM servicios_contratados sc
        INNER JOIN proveedores p ON p.id = sc.proveedor_id
        INNER JOIN servicios sv  ON sv.id = sc.servicio_id
        INNER JOIN clientes cl   ON cl.id = sc.cliente_id
        LEFT JOIN cotizaciones cot ON cot.id = sc.cotizacion_id
        LEFT JOIN solicitudes sol  ON sol.id  = sc.solicitud_id
        WHERE p.usuario_id = :uid
        ORDER BY sc.created_at DESC
        LIMIT 10
    ");
    $st->execute([':uid' => $uid]);
    $contratos = $st->fetchAll(PDO::FETCH_ASSOC);

    // Ingresos mensuales (últimos 6 meses) para gráfica
    $st = $pdo->prepare("
        SELECT DATE_FORMAT(COALESCE(sc.fecha_ejecucion, sc.created_at), '%b') AS mes,
               DATE_FORMAT(COALESCE(sc.fecha_ejecucion, sc.created_at), '%Y-%m') AS mes_key,
               COALESCE(SUM(COALESCE(cot.precio, sol.presupuesto_estimado, 0)), 0) AS total
        FROM servicios_contratados sc
        INNER JOIN proveedores p ON p.id = sc.proveedor_id
        LEFT JOIN cotizaciones cot ON cot.id = sc.cotizacion_id
        LEFT JOIN solicitudes sol  ON sol.id  = sc.solicitud_id
        WHERE p.usuario_id = :uid AND sc.estado = 'finalizado'
          AND COALESCE(sc.fecha_ejecucion, sc.created_at) >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY mes_key, mes
        ORDER BY mes_key ASC
    ");
    $st->execute([':uid' => $uid]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    if ($rows) {
        $ingresos_meses = [
            'meses'  => array_column($rows, 'mes'),
            'valores' => array_map('floatval', array_column($rows, 'total')),
        ];
    }
} catch (PDOException $e) {
    error_log('finanzas.php: ' . $e->getMessage());
}

$finanzas = [
    'balance_actual'       => $ingresosAnuales,
    'ingresos_mes'         => $ingresosMes,
    'gastos_mes'           => 0,
    'ganancias_mes'        => $ingresosMes,
    'proximos_pagos'       => 0,
    'facturas_pendientes'  => count(array_filter($contratos, fn($c) => $c['estado'] !== 'finalizado')),
    'comisiones_plataforma'=> 0,
    'retenciones'          => 0,
];

$transacciones_recientes = array_map(fn($c) => [
    'fecha'   => $c['fecha'] ? substr($c['fecha'], 0, 10) : date('Y-m-d'),
    'concepto'=> 'Servicio: ' . ($c['servicio'] ?? ''),
    'cliente' => $c['cliente'] ?? '',
    'monto'   => (float)($c['monto'] ?? 0),
    'tipo'    => 'ingreso',
    'estado'  => $c['estado'] === 'finalizado' ? 'completado' : 'pendiente',
], $contratos);

$facturas = array_map(fn($c) => [
    'id'     => 'CTR-' . str_pad($c['contrato_id'], 4, '0', STR_PAD_LEFT),
    'cliente'=> $c['cliente'] ?? '',
    'fecha'  => $c['fecha'] ? substr($c['fecha'], 0, 10) : date('Y-m-d'),
    'monto'  => (float)($c['monto'] ?? 0),
    'estado' => $c['estado'] === 'finalizado' ? 'pagada' : 'pendiente',
], $contratos);
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
    <?php include_once __DIR__ . '/../../layouts/sidebar-proveedor.php'; ?>

    <main class="contenido">
        <!-- Header Proveedor -->
        <?php include_once __DIR__ . '/../../layouts/header-proveedor.php'; ?>

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