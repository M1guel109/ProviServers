<?php
require_once BASE_PATH . '/app/helpers/session-admin.php';
require_once BASE_PATH . '/app/models/admin.php';

$desde    = $_GET['desde']   ?? '';
$hasta    = $_GET['hasta']   ?? '';
$rol      = isset($_GET['rol'])      && $_GET['rol']      !== '' ? $_GET['rol']      : null;
$estadoId = isset($_GET['estado_id']) && $_GET['estado_id'] !== '' ? (int)$_GET['estado_id'] : null;

$modelo  = new Usuario();
$reporte = $modelo->obtenerReporteUsuarios($desde ?: null, $hasta ?: null, $rol, $estadoId);

$global      = $reporte['global'];
$crecimiento = $reporte['crecimiento'];
$porEstado   = $reporte['porEstado'];
$detalle     = $reporte['detalle'];

$total       = (int)($global['total']       ?? 0);
$clientes    = (int)($global['clientes']    ?? 0);
$proveedores = (int)($global['proveedores'] ?? 0);
$admins      = (int)($global['admins']      ?? 0);
$activos     = (int)($global['activos']     ?? 0);
$pendientes  = (int)($global['pendientes']  ?? 0);
$suspendidos = (int)($global['suspendidos'] ?? 0);
$inactivos   = (int)($global['inactivos']   ?? 0);

$chartLabels     = json_encode(array_column($crecimiento, 'label'));
$chartTotal      = json_encode(array_map('intval', array_column($crecimiento, 'total')));
$chartClientes   = json_encode(array_map('intval', array_column($crecimiento, 'clientes')));
$chartProveedores = json_encode(array_map('intval', array_column($crecimiento, 'proveedores')));

$pdfQuery = http_build_query(array_filter([
    'tipo'      => 'usuarios',
    'desde'     => $desde,
    'hasta'     => $hasta,
    'rol'       => $rol ?? '',
    'estado_id' => $estadoId !== null ? (string)$estadoId : '',
]));

$estadoLabels = [
    'activo'     => ['label' => 'Activo',      'badge' => 'success'],
    'pendiente'  => ['label' => 'Pendiente',   'badge' => 'warning text-dark'],
    'suspendido' => ['label' => 'Suspendido',  'badge' => 'danger'],
    'inactivo'   => ['label' => 'Inactivo',    'badge' => 'secondary'],
    'bloqueado'  => ['label' => 'Bloqueado',   'badge' => 'dark'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Reporte de Usuarios</title>
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

        <section id="titulo-principal">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Reporte de Usuarios</h1>
                    <p class="text-muted mb-0">Métricas y crecimiento de usuarios registrados en la plataforma.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="<?= BASE_URL ?>/admin/reporte?<?= htmlspecialchars($pdfQuery) ?>"
                       target="_blank"
                       class="btn btn-primary">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Exportar PDF
                    </a>
                </div>
            </div>
        </section>

        <!-- Filtros -->
        <section class="mt-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold border-0 pt-3">
                    <i class="bi bi-funnel-fill text-primary me-2"></i>Filtros
                </div>
                <div class="card-body">
                    <form method="GET" action="<?= BASE_URL ?>/admin/reportes-usuarios" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Desde</label>
                            <input type="date" name="desde" class="form-control form-control-sm" value="<?= htmlspecialchars($desde) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Hasta</label>
                            <input type="date" name="hasta" class="form-control form-control-sm" value="<?= htmlspecialchars($hasta) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Rol</label>
                            <select name="rol" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="cliente"   <?= $rol === 'cliente'   ? 'selected' : '' ?>>Cliente</option>
                                <option value="proveedor" <?= $rol === 'proveedor' ? 'selected' : '' ?>>Proveedor</option>
                                <option value="admin"     <?= $rol === 'admin'     ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Estado</label>
                            <select name="estado_id" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="1" <?= $estadoId === 1 ? 'selected' : '' ?>>Pendiente</option>
                                <option value="2" <?= $estadoId === 2 ? 'selected' : '' ?>>Activo</option>
                                <option value="3" <?= $estadoId === 3 ? 'selected' : '' ?>>Suspendido</option>
                                <option value="4" <?= $estadoId === 4 ? 'selected' : '' ?>>Inactivo</option>
                                <option value="5" <?= $estadoId === 5 ? 'selected' : '' ?>>Bloqueado</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2 align-items-end">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-search me-1"></i>Filtrar
                            </button>
                            <a href="<?= BASE_URL ?>/admin/reportes-usuarios" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- KPIs -->
        <section class="mt-4">
            <div class="row g-3">
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Total</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($total) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Clientes</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($clientes) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Proveedores</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($proveedores) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Activos</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($activos) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Pendientes</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($pendientes) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Suspendidos</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($suspendidos) ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-4 mt-1">

            <!-- Gráfica de crecimiento -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold border-0 pt-3">
                        <i class="bi bi-graph-up text-primary me-2"></i>Crecimiento de registros
                    </div>
                    <div class="card-body">
                        <div id="chartCrecimiento"></div>
                    </div>
                </div>
            </div>

            <!-- Por estado -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold border-0 pt-3">
                        <i class="bi bi-bar-chart-fill text-primary me-2"></i>Por estado
                    </div>
                    <div class="card-body">
                        <?php if (!empty($porEstado)):
                            $maxEst = max(array_column($porEstado, 'total')) ?: 1; ?>
                            <?php foreach ($porEstado as $e): ?>
                                <?php
                                    $pct  = round(($e['total'] / $maxEst) * 100);
                                    $info = $estadoLabels[$e['estado']] ?? ['label' => ucfirst($e['estado']), 'badge' => 'secondary'];
                                ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small fw-semibold"><?= $info['label'] ?></span>
                                        <span class="badge bg-<?= $info['badge'] ?>"><?= (int)$e['total'] ?></span>
                                    </div>
                                    <div class="progress" style="height:8px;">
                                        <div class="progress-bar bg-<?= explode(' ', $info['badge'])[0] ?>"
                                             style="width:<?= $pct ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-4">Sin datos.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- Tabla detalle -->
        <section class="mt-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold border-0 pt-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people-fill text-primary me-2"></i>Detalle de usuarios</span>
                    <small class="text-muted fw-normal">Máx. 200 registros</small>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($detalle)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size:.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Email</th>
                                        <th class="text-center">Rol</th>
                                        <th class="text-center">Estado</th>
                                        <th>Registro</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detalle as $u): ?>
                                        <?php
                                            $info = $estadoLabels[$u['estado']] ?? ['label' => ucfirst($u['estado']), 'badge' => 'secondary'];
                                            $rolBadge = match($u['rol']) {
                                                'admin'     => 'bg-dark',
                                                'proveedor' => 'bg-success',
                                                default     => 'bg-info text-dark',
                                            };
                                        ?>
                                        <tr>
                                            <td class="text-muted"><?= (int)$u['id'] ?></td>
                                            <td><?= htmlspecialchars($u['email']) ?></td>
                                            <td class="text-center">
                                                <span class="badge <?= $rolBadge ?>"><?= ucfirst($u['rol']) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-<?= $info['badge'] ?>"><?= $info['label'] ?></span>
                                            </td>
                                            <td class="text-muted small"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-people fs-2 d-block mb-2"></i>
                            No hay usuarios con los filtros seleccionados.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
    <script>
        (function () {
            const labels     = <?= $chartLabels ?>;
            const total      = <?= $chartTotal ?>;
            const clientes   = <?= $chartClientes ?>;
            const proveedores = <?= $chartProveedores ?>;

            if (!labels.length) return;

            new ApexCharts(document.getElementById('chartCrecimiento'), {
                chart: { type: 'area', height: 280, toolbar: { show: false }, zoom: { enabled: false } },
                series: [
                    { name: 'Total',      data: total },
                    { name: 'Clientes',   data: clientes },
                    { name: 'Proveedores', data: proveedores },
                ],
                xaxis: { categories: labels, labels: { style: { fontSize: '11px' } } },
                yaxis: { labels: { formatter: v => Math.round(v) } },
                colors: ['#0066ff', '#17a2b8', '#28a745'],
                fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: false },
                legend: { position: 'top' },
                tooltip: { x: { show: true } },
                grid: { borderColor: '#f0f0f0' },
            }).render();
        })();
    </script>

</body>
</html>
