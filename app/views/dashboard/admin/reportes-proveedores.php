<?php
require_once BASE_PATH . '/app/helpers/session-admin.php';
require_once BASE_PATH . '/app/models/proveedor-perfil.php';

$nivelConfianza = isset($_GET['nivel_confianza']) && $_GET['nivel_confianza'] !== '' ? $_GET['nivel_confianza'] : null;
$verificado     = isset($_GET['verificado'])     && $_GET['verificado']     !== '' ? (int)$_GET['verificado'] : null;
$calMin         = isset($_GET['cal_min'])        && $_GET['cal_min']        !== '' ? (float)$_GET['cal_min'] : null;
$calMax         = isset($_GET['cal_max'])        && $_GET['cal_max']        !== '' ? (float)$_GET['cal_max'] : null;

$modelo  = new ProveedorPerfil();
$reporte = $modelo->obtenerReporteProveedores($nivelConfianza, $verificado, $calMin, $calMax);

$global       = $reporte['global'];
$porNivel     = $reporte['porNivel'];
$porCal       = $reporte['porCalificacion'];
$porCategoria = $reporte['porCategoria'];
$detalle      = $reporte['detalle'];

$total        = (int)($global['total']             ?? 0);
$verificados  = (int)($global['verificados']        ?? 0);
$noVerif      = (int)($global['no_verificados']     ?? 0);
$promCal      = (float)($global['prom_calificacion'] ?? 0);
$expertos     = (int)($global['expertos']           ?? 0);
$confiables   = (int)($global['confiables']         ?? 0);

$pdfQuery = http_build_query(array_filter([
    'tipo'           => 'proveedores',
    'nivel_confianza' => $nivelConfianza ?? '',
    'verificado'     => $verificado !== null ? (string)$verificado : '',
    'cal_min'        => $calMin !== null ? (string)$calMin : '',
    'cal_max'        => $calMax !== null ? (string)$calMax : '',
]));

$nivelLabels = ['nuevo' => 'Nuevo', 'validado' => 'Validado', 'confiable' => 'Confiable', 'experto' => 'Experto'];
$nivelColors = ['nuevo' => 'secondary', 'validado' => 'info', 'confiable' => 'primary', 'experto' => 'success'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Reporte de Proveedores</title>
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
                    <h1>Reporte de Proveedores</h1>
                    <p class="text-muted mb-0">Métricas de proveedores registrados en la plataforma.</p>
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
                    <form method="GET" action="<?= BASE_URL ?>/admin/reportes-proveedores" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Nivel de confianza</label>
                            <select name="nivel_confianza" class="form-select form-select-sm">
                                <option value="">Todos los niveles</option>
                                <?php foreach ($nivelLabels as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $nivelConfianza === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Verificado</label>
                            <select name="verificado" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="1" <?= $verificado === 1 ? 'selected' : '' ?>>Verificado</option>
                                <option value="0" <?= $verificado === 0 ? 'selected' : '' ?>>No verificado</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Cal. mínima</label>
                            <input type="number" name="cal_min" min="0" max="5" step="0.5"
                                   class="form-control form-control-sm"
                                   value="<?= $calMin !== null ? $calMin : '' ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Cal. máxima</label>
                            <input type="number" name="cal_max" min="0" max="5" step="0.5"
                                   class="form-control form-control-sm"
                                   value="<?= $calMax !== null ? $calMax : '' ?>">
                        </div>
                        <div class="col-md-2 d-flex gap-2 align-items-end">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-search me-1"></i>Filtrar
                            </button>
                            <a href="<?= BASE_URL ?>/admin/reportes-proveedores" class="btn btn-sm btn-outline-secondary">
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
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Total proveedores</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($total) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Verificados</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($verificados) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">No verificados</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($noVerif) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Cal. promedio</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($promCal, 2) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Expertos</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($expertos) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100 border-start border-secondary border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Confiables</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($confiables) ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-4 mt-1">

            <!-- Por nivel -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold border-0 pt-3">
                        <i class="bi bi-shield-check text-primary me-2"></i>Por nivel de confianza
                    </div>
                    <div class="card-body">
                        <?php if (!empty($porNivel)):
                            $maxNivel = max(array_column($porNivel, 'total')) ?: 1; ?>
                            <?php foreach ($porNivel as $n): ?>
                                <?php $pct = round(($n['total'] / $maxNivel) * 100); ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small fw-semibold"><?= $nivelLabels[$n['nivel']] ?? ucfirst($n['nivel']) ?></span>
                                        <span class="badge bg-<?= $nivelColors[$n['nivel']] ?? 'secondary' ?>"><?= (int)$n['total'] ?></span>
                                    </div>
                                    <div class="progress" style="height:8px;">
                                        <div class="progress-bar bg-<?= $nivelColors[$n['nivel']] ?? 'secondary' ?>"
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

            <!-- Por calificación -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold border-0 pt-3">
                        <i class="bi bi-star-fill text-warning me-2"></i>Por rango de calificación
                    </div>
                    <div class="card-body">
                        <?php if (!empty($porCal)):
                            $maxCal = max(array_column($porCal, 'total')) ?: 1; ?>
                            <?php foreach ($porCal as $r): ?>
                                <?php $pct = round(($r['total'] / $maxCal) * 100); ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small fw-semibold">★ <?= $r['rango'] ?></span>
                                        <span class="text-muted small"><?= (int)$r['total'] ?> proveedores</span>
                                    </div>
                                    <div class="progress" style="height:8px;">
                                        <div class="progress-bar bg-warning" style="width:<?= $pct ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-4">Sin datos.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Por categoría -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold border-0 pt-3">
                        <i class="bi bi-tags-fill text-primary me-2"></i>Por categoría (publicaciones aprobadas)
                    </div>
                    <div class="card-body">
                        <?php if (!empty($porCategoria)):
                            $maxCat = max(array_column($porCategoria, 'proveedores')) ?: 1; ?>
                            <?php foreach ($porCategoria as $cat): ?>
                                <?php $pct = round(($cat['proveedores'] / $maxCat) * 100); ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small fw-semibold"><?= htmlspecialchars($cat['categoria']) ?></span>
                                        <span class="text-muted small"><?= (int)$cat['proveedores'] ?></span>
                                    </div>
                                    <div class="progress" style="height:8px;">
                                        <div class="progress-bar bg-primary" style="width:<?= $pct ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-4">Sin publicaciones aprobadas.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- Tabla detalle -->
        <section class="mt-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold border-0 pt-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people-fill text-primary me-2"></i>Detalle de proveedores</span>
                    <small class="text-muted fw-normal">Máx. 200 registros</small>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($detalle)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size:.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Ubicación</th>
                                        <th class="text-center">Nivel</th>
                                        <th class="text-center">Verificado</th>
                                        <th class="text-center">Cal.</th>
                                        <th class="text-center">Pub. aprobadas</th>
                                        <th class="text-center">Contratos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detalle as $p): ?>
                                        <?php
                                            $nivelBadge = match($p['nivel_confianza']) {
                                                'experto'   => 'bg-success',
                                                'confiable' => 'bg-primary',
                                                'validado'  => 'bg-info text-dark',
                                                default     => 'bg-secondary',
                                            };
                                        ?>
                                        <tr>
                                            <td class="text-muted"><?= (int)$p['id'] ?></td>
                                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                                            <td class="text-muted"><?= htmlspecialchars($p['ubicacion'] ?? '—') ?></td>
                                            <td class="text-center">
                                                <span class="badge <?= $nivelBadge ?>">
                                                    <?= $nivelLabels[$p['nivel_confianza']] ?? ucfirst($p['nivel_confianza']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($p['verificado']): ?>
                                                    <i class="bi bi-patch-check-fill text-success"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-dash-circle text-muted"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center fw-semibold"><?= number_format((float)$p['calificacion_promedio'], 2) ?></td>
                                            <td class="text-center"><?= (int)$p['publicaciones_aprobadas_count'] ?></td>
                                            <td class="text-center"><?= (int)$p['total_contratos'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-people fs-2 d-block mb-2"></i>
                            No hay proveedores con los filtros seleccionados.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>

</body>
</html>
