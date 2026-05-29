<?php
require_once BASE_PATH . '/app/helpers/session-proveedor.php';
require_once BASE_PATH . '/app/models/ServicioContratado.php';
require_once BASE_PATH . '/config/database.php';

$uid     = (int)($_SESSION['user']['id'] ?? 0);
$scModel = new ServicioContratado();
$resumen = $scModel->obtenerResumenDashboardProveedor($uid);

$serviciosCompletados = 0;
$clientesNuevosMes    = 0;
$ingresosAnuales      = 0.0;
$ingresos_meses       = ['meses' => [], 'valores' => []];
$servicios_categorias = ['categorias' => [], 'valores' => []];

try {
    $db  = new Conexion();
    $pdo = $db->getConexion();

    // Servicios completados totales
    $st = $pdo->prepare("
        SELECT COUNT(*) FROM servicios_contratados sc
        INNER JOIN proveedores p ON p.id = sc.proveedor_id
        WHERE p.usuario_id = ? AND sc.estado = 'finalizado'
    ");
    $st->execute([$uid]);
    $serviciosCompletados = (int)$st->fetchColumn();

    // Clientes distintos este mes
    $st = $pdo->prepare("
        SELECT COUNT(DISTINCT sc.cliente_id)
        FROM servicios_contratados sc
        INNER JOIN proveedores p ON p.id = sc.proveedor_id
        WHERE p.usuario_id = ?
          AND DATE_FORMAT(sc.created_at, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
    ");
    $st->execute([$uid]);
    $clientesNuevosMes = (int)$st->fetchColumn();

    // Ingresos anuales
    $st = $pdo->prepare("
        SELECT COALESCE(SUM(COALESCE(cot.precio, pub_sol.precio, 0)), 0)
        FROM servicios_contratados sc
        INNER JOIN proveedores p ON p.id = sc.proveedor_id
        LEFT JOIN cotizaciones cot    ON cot.id = sc.cotizacion_id
        LEFT JOIN solicitudes sol     ON sol.id  = sc.solicitud_id
        LEFT JOIN publicaciones pub_sol ON sol.publicacion_id = pub_sol.id
        WHERE p.usuario_id = ? AND sc.estado = 'finalizado'
          AND YEAR(COALESCE(sc.fecha_ejecucion, sc.created_at)) = YEAR(CURDATE())
    ");
    $st->execute([$uid]);
    $ingresosAnuales = (float)$st->fetchColumn();

    // Ingresos por mes (últimos 12 meses) para gráfica
    $st = $pdo->prepare("
        SELECT DATE_FORMAT(COALESCE(sc.fecha_ejecucion, sc.modified_at, sc.created_at), '%b') AS mes,
               DATE_FORMAT(COALESCE(sc.fecha_ejecucion, sc.modified_at, sc.created_at), '%Y-%m') AS mes_key,
               COALESCE(SUM(COALESCE(cot.precio, pub_sol.precio, 0)), 0) AS total
        FROM servicios_contratados sc
        INNER JOIN proveedores p ON p.id = sc.proveedor_id
        LEFT JOIN cotizaciones cot ON cot.id = sc.cotizacion_id
        LEFT JOIN solicitudes sol  ON sol.id  = sc.solicitud_id
        WHERE p.usuario_id = :uid AND sc.estado = 'finalizado'
          AND COALESCE(sc.fecha_ejecucion, sc.created_at) >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
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

    // Contratos por categoría de servicio para gráfica
    $st = $pdo->prepare("
        SELECT COALESCE(cat.nombre, 'Sin categoría') AS categoria, COUNT(sc.id) AS total
        FROM servicios_contratados sc
        INNER JOIN proveedores p ON p.id = sc.proveedor_id
        INNER JOIN servicios sv  ON sv.id  = sc.servicio_id
        LEFT  JOIN categorias cat ON cat.id = sv.id_categoria
        WHERE p.usuario_id = :uid
        GROUP BY cat.id, cat.nombre
        ORDER BY total DESC
        LIMIT 6
    ");
    $st->execute([':uid' => $uid]);
    $catRows = $st->fetchAll(PDO::FETCH_ASSOC);
    if ($catRows) {
        $servicios_categorias = [
            'categorias' => array_column($catRows, 'categoria'),
            'valores'    => array_map('intval', array_column($catRows, 'total')),
        ];
    }
} catch (PDOException $e) {
    error_log('estadisticas.php: ' . $e->getMessage());
}

$estadisticas = [
    'ingresos_mes'             => $resumen['ingresos_mes'],
    'servicios_mes'            => $serviciosCompletados,
    'clientes_nuevos'          => $clientesNuevosMes,
    'calificacion_promedio'    => $resumen['calificacion_promedio'] ?? 0,
    'servicios_completados'    => $serviciosCompletados,
    'tasa_aceptacion'          => 0,
    'tiempo_respuesta_promedio'=> 0,
    'ingresos_anuales'         => $ingresosAnuales,
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-Proveedor.css">

    <!-- CSS Específico -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/estadisticas.css">
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
                                <small class="text-muted mt-2 d-block">Total acumulado</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card shadow-sm p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small">Clientes este mes</span>
                                    <h3 class="fw-bold mt-1"><?= $estadisticas['clientes_nuevos'] ?></h3>
                                </div>
                                <div class="bg-primary-light p-3 rounded-circle">
                                    <i class="bi bi-people-fill text-primary fs-3"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted mt-2 d-block">Clientes distintos atendidos</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card shadow-sm p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small">Calificación promedio</span>
                                    <h3 class="fw-bold mt-1">
                                        <?= $estadisticas['calificacion_promedio']
                                            ? number_format($estadisticas['calificacion_promedio'], 1)
                                            : 'N/A' ?>
                                    </h3>
                                </div>
                                <div class="bg-warning-light p-3 rounded-circle">
                                    <i class="bi bi-star-fill text-warning fs-3"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted mt-2 d-block">Promedio de tus valoraciones</small>
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
                            <?php if (!empty($servicios_categorias['categorias'])): ?>
                                <?php foreach ($servicios_categorias['categorias'] as $i => $cat): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="fw-bold"><?= htmlspecialchars($cat) ?></span>
                                    <span class="badge bg-primary rounded-pill"><?= $servicios_categorias['valores'][$i] ?? 0 ?></span>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted small py-2">Sin datos de categorías aún.</p>
                            <?php endif; ?>
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
                                <h5 class="fw-bold"><?= $estadisticas['servicios_completados'] ?></h5>
                                <small class="text-muted">Servicios completados</small>
                            </div>
                            <div class="col-md-3">
                                <h5 class="fw-bold"><?= $estadisticas['clientes_nuevos'] ?></h5>
                                <small class="text-muted">Clientes atendidos</small>
                            </div>
                            <div class="col-md-3">
                                <h5 class="fw-bold">
                                    <?= $estadisticas['calificacion_promedio']
                                        ? number_format($estadisticas['calificacion_promedio'], 1)
                                        : 'N/A' ?>
                                </h5>
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

    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/estadisticas.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
        // Pasar datos de PHP a JavaScript
        const datosIngresos = <?= json_encode($ingresos_meses) ?>;
        const datosCategorias = <?= json_encode($servicios_categorias) ?>;
    </script>
</body>

</html>