<?php
require_once BASE_PATH . '/app/helpers/session-admin.php';
require_once BASE_PATH . '/app/models/valoracion.php';

$modelo  = new Valoracion();
$reporte = $modelo->obtenerReporteCalificaciones();

$global         = $reporte['global'];
$topProveedores = $reporte['topProveedores'];
$recientes      = $reporte['recientes'];

$total   = (int)($global['total']   ?? 0);
$promedio = (float)($global['promedio'] ?? 0);
$dist = [
    5 => (int)($global['cinco']  ?? 0),
    4 => (int)($global['cuatro'] ?? 0),
    3 => (int)($global['tres']   ?? 0),
    2 => (int)($global['dos']    ?? 0),
    1 => (int)($global['uno']    ?? 0),
];
$positivas = $dist[5] + $dist[4];
$bajas     = $dist[1] + $dist[2];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Reporte de Calificaciones</title>
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
                    <h1>Reporte de Calificaciones</h1>
                    <p class="text-muted mb-0">Valoraciones registradas por clientes sobre los servicios de la plataforma.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="<?= BASE_URL ?>/admin/reporte?tipo=calificaciones"
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
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.75rem;">Total valoraciones</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($total) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.75rem;">Promedio global</small>
                            <h2 class="fw-bold text-dark mb-0">
                                <?= number_format($promedio, 1) ?>
                                <span class="text-warning fs-5">★</span>
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.75rem;">Positivas (4-5 ★)</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($positivas) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.75rem;">Bajas (1-2 ★)</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($bajas) ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-4">

            <!-- Distribución por estrellas -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold border-0 pt-3">
                        <i class="bi bi-bar-chart-fill text-primary me-2"></i>Distribución por estrellas
                    </div>
                    <div class="card-body">
                        <?php foreach ([5, 4, 3, 2, 1] as $estrella): ?>
                            <?php
                                $cant = $dist[$estrella];
                                $pct  = $total > 0 ? round(($cant / $total) * 100) : 0;
                            ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-warning fw-bold">
                                        <?= str_repeat('★', $estrella) ?><?= str_repeat('☆', 5 - $estrella) ?>
                                    </span>
                                    <span class="text-muted small"><?= $cant ?> (<?= $pct ?>%)</span>
                                </div>
                                <div class="progress" style="height:10px;">
                                    <div class="progress-bar bg-warning" style="width:<?= $pct ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Top proveedores -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold border-0 pt-3">
                        <i class="bi bi-trophy-fill text-warning me-2"></i>Top proveedores por calificación
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($topProveedores)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Proveedor</th>
                                            <th class="text-center">Promedio</th>
                                            <th class="text-center">Valoraciones</th>
                                            <th class="text-center">Nivel</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($topProveedores as $i => $prov): ?>
                                            <?php
                                                $p = (float)$prov['promedio'];
                                                if ($p >= 4.0) {
                                                    $badge = '<span class="badge bg-success">Excelente</span>';
                                                } elseif ($p >= 3.0) {
                                                    $badge = '<span class="badge bg-warning text-dark">Regular</span>';
                                                } else {
                                                    $badge = '<span class="badge bg-danger">Bajo</span>';
                                                }
                                            ?>
                                            <tr>
                                                <td class="text-muted"><?= $i + 1 ?></td>
                                                <td><?= htmlspecialchars($prov['proveedor']) ?></td>
                                                <td class="text-center text-warning fw-bold">
                                                    <?= number_format($p, 1) ?> ★
                                                </td>
                                                <td class="text-center"><?= (int)$prov['total'] ?></td>
                                                <td class="text-center"><?= $badge ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-star fs-2 d-block mb-2"></i>
                                No hay valoraciones registradas aún.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimas valoraciones -->
        <section class="mt-4 mb-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold border-0 pt-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-clock-history text-primary me-2"></i>Últimas valoraciones</span>
                    <small class="text-muted fw-normal">Mostrando las 50 más recientes</small>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($recientes)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Proveedor</th>
                                        <th>Servicio</th>
                                        <th class="text-center">Calificación</th>
                                        <th>Comentario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recientes as $val): ?>
                                        <tr>
                                            <td class="text-muted small">
                                                <?= date('d/m/Y', strtotime($val['created_at'])) ?>
                                            </td>
                                            <td><?= htmlspecialchars($val['cliente']) ?></td>
                                            <td><?= htmlspecialchars($val['proveedor']) ?></td>
                                            <td><?= htmlspecialchars($val['servicio']) ?></td>
                                            <td class="text-center text-warning fw-bold">
                                                <?= (int)$val['calificacion'] ?> ★
                                            </td>
                                            <td class="text-muted small">
                                                <?= htmlspecialchars($val['comentario'] ?? '—') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-chat-square-text fs-2 d-block mb-2"></i>
                            No hay valoraciones registradas aún.
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
