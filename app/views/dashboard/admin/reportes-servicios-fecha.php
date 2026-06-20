<?php
require_once BASE_PATH . '/app/helpers/session-admin.php';
require_once BASE_PATH . '/app/models/servicio-contratado.php';

// Filtros desde GET
$desde      = $_GET['desde']      ?? '';
$hasta      = $_GET['hasta']      ?? '';
$estado     = isset($_GET['estado']) && $_GET['estado'] !== '' ? $_GET['estado'] : null;
$agrupacion = $_GET['agrupacion'] ?? 'mes';

$modelo  = new ServicioContratado();
$reporte = $modelo->obtenerReportePorFecha(
    $desde ?: null,
    $hasta ?: null,
    $estado,
    $agrupacion
);

$global     = $reporte['global'];
$porPeriodo = $reporte['porPeriodo'];
$detalle    = $reporte['detalle'];

$total       = (int)($global['total']       ?? 0);
$finalizados = (int)($global['finalizados'] ?? 0);
$enProceso   = (int)($global['en_proceso']  ?? 0);
$pendientes  = (int)($global['pendientes']  ?? 0);
$confirmados = (int)($global['confirmados'] ?? 0);
$cancelados  = (int)($global['cancelados']  ?? 0);

$agrupacionLabels = ['dia' => 'Día', 'semana' => 'Semana', 'mes' => 'Mes'];

$pdfQuery = http_build_query(array_filter([
    'tipo'       => 'serviciosFecha',
    'desde'      => $desde,
    'hasta'      => $hasta,
    'estado'     => $estado ?? '',
    'agrupacion' => $agrupacion,
]));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Reporte de Servicios por Fecha</title>
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
                    <h1>Reporte de Servicios por Fecha</h1>
                    <p class="text-muted mb-0">Servicios contratados en la plataforma filtrados por rango de fecha y estado.</p>
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
                    <form method="GET" action="<?= BASE_URL ?>/admin/reportes-servicios-fecha" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Desde</label>
                            <input type="date" name="desde" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($desde) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Hasta</label>
                            <input type="date" name="hasta" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($hasta) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Estado</label>
                            <select name="estado" class="form-select form-select-sm">
                                <option value="">Todos los estados</option>
                                <option value="pendiente"          <?= $estado === 'pendiente'          ? 'selected' : '' ?>>Pendiente</option>
                                <option value="confirmado"         <?= $estado === 'confirmado'         ? 'selected' : '' ?>>Confirmado</option>
                                <option value="en_proceso"         <?= $estado === 'en_proceso'         ? 'selected' : '' ?>>En proceso</option>
                                <option value="finalizado"         <?= $estado === 'finalizado'         ? 'selected' : '' ?>>Finalizado</option>
                                <option value="cancelado"          <?= $estado === 'cancelado'          ? 'selected' : '' ?>>Cancelado</option>
                                <option value="cancelado_cliente"  <?= $estado === 'cancelado_cliente'  ? 'selected' : '' ?>>Cancelado por cliente</option>
                                <option value="cancelado_proveedor"<?= $estado === 'cancelado_proveedor'? 'selected' : '' ?>>Cancelado por proveedor</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Agrupar por</label>
                            <select name="agrupacion" class="form-select form-select-sm">
                                <option value="dia"    <?= $agrupacion === 'dia'    ? 'selected' : '' ?>>Día</option>
                                <option value="semana" <?= $agrupacion === 'semana' ? 'selected' : '' ?>>Semana</option>
                                <option value="mes"    <?= $agrupacion === 'mes'    ? 'selected' : '' ?>>Mes</option>
                            </select>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-search me-1"></i>Aplicar filtros
                            </button>
                            <a href="<?= BASE_URL ?>/admin/reportes-servicios-fecha" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Limpiar
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
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Total contratos</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($total) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Finalizados</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($finalizados) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">En proceso</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($enProceso) ?></h2>
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
                    <div class="card border-0 shadow-sm h-100 border-start border-secondary border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Confirmados</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($confirmados) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Cancelados</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($cancelados) ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-4 mt-1">

            <!-- Por período -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold border-0 pt-3">
                        <i class="bi bi-calendar3 text-primary me-2"></i>
                        Contratos por <?= $agrupacionLabels[$agrupacion] ?? 'período' ?>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($porPeriodo)): ?>
                            <?php $maxPer = max(array_column($porPeriodo, 'total')) ?: 1; ?>
                            <div class="p-3">
                                <?php foreach ($porPeriodo as $per): ?>
                                    <?php $pct = round(($per['total'] / $maxPer) * 100); ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="small fw-semibold"><?= htmlspecialchars($per['periodo']) ?></span>
                                            <span class="text-muted small"><?= (int)$per['total'] ?> contratos</span>
                                        </div>
                                        <div class="progress" style="height:8px;">
                                            <div class="progress-bar bg-primary" style="width:<?= $pct ?>%"></div>
                                        </div>
                                        <div class="d-flex gap-3 mt-1">
                                            <span class="text-success small"><i class="bi bi-check-circle-fill me-1"></i><?= (int)$per['finalizados'] ?> fin.</span>
                                            <span class="text-danger small"><i class="bi bi-x-circle-fill me-1"></i><?= (int)$per['cancelados'] ?> cancel.</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                                Sin datos para el período seleccionado.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tabla detalle -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold border-0 pt-3 d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-list-ul text-primary me-2"></i>Detalle de contratos</span>
                        <small class="text-muted fw-normal">Máx. 200 registros</small>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($detalle)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" style="font-size:.85rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Servicio</th>
                                            <th>Cliente</th>
                                            <th>Proveedor</th>
                                            <th class="text-center">Estado</th>
                                            <th>F. solicitud</th>
                                            <th>F. ejecución</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($detalle as $sc): ?>
                                            <?php
                                                $badgeClass = match($sc['estado']) {
                                                    'finalizado'          => 'bg-success',
                                                    'en_proceso'          => 'bg-info text-dark',
                                                    'confirmado'          => 'bg-secondary',
                                                    'pendiente'           => 'bg-warning text-dark',
                                                    'cancelado',
                                                    'cancelado_cliente',
                                                    'cancelado_proveedor' => 'bg-danger',
                                                    default               => 'bg-light text-dark',
                                                };
                                                $estadoLabel = match($sc['estado']) {
                                                    'cancelado_cliente'   => 'Canc. cliente',
                                                    'cancelado_proveedor' => 'Canc. proveedor',
                                                    default               => ucfirst(str_replace('_', ' ', $sc['estado'])),
                                                };
                                            ?>
                                            <tr>
                                                <td class="text-muted"><?= (int)$sc['contrato_id'] ?></td>
                                                <td><?= htmlspecialchars($sc['servicio_nombre']) ?></td>
                                                <td class="text-muted"><?= htmlspecialchars($sc['cliente_nombre']) ?></td>
                                                <td class="text-muted"><?= htmlspecialchars($sc['proveedor_nombre']) ?></td>
                                                <td class="text-center">
                                                    <span class="badge <?= $badgeClass ?>"><?= $estadoLabel ?></span>
                                                </td>
                                                <td class="text-muted small">
                                                    <?= $sc['fecha_solicitud'] ? date('d/m/Y', strtotime($sc['fecha_solicitud'])) : '—' ?>
                                                </td>
                                                <td class="text-muted small">
                                                    <?= $sc['fecha_ejecucion'] ? date('d/m/Y', strtotime($sc['fecha_ejecucion'])) : '—' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No hay contratos con los filtros seleccionados.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>

</body>
</html>
