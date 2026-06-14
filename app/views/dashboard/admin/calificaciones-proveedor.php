<?php
require_once BASE_PATH . '/app/helpers/session-admin.php';
require_once BASE_PATH . '/app/models/valoracion.php';

$modelo      = new Valoracion();
$proveedores = $modelo->listarProveedores();

$proveedorId  = isset($_GET['proveedor_id']) && $_GET['proveedor_id'] !== '' ? (int)$_GET['proveedor_id'] : null;
$desde        = $_GET['desde']        ?? '';
$hasta        = $_GET['hasta']        ?? '';
$calFiltro    = isset($_GET['calificacion']) && $_GET['calificacion'] !== '' ? (int)$_GET['calificacion'] : null;

$reporte = null;
$resumen = [];
$detalle = [];

if ($proveedorId) {
    $reporte = $modelo->obtenerCalificacionesProveedorFiltradas(
        $proveedorId,
        $desde ?: null,
        $hasta ?: null,
        $calFiltro
    );
    $resumen = $reporte['resumen'];
    $detalle = $reporte['detalle'];
}

$total    = (int)($resumen['total']   ?? 0);
$promedio = (float)($resumen['promedio'] ?? 0);
$dist = [
    5 => (int)($resumen['cinco']  ?? 0),
    4 => (int)($resumen['cuatro'] ?? 0),
    3 => (int)($resumen['tres']   ?? 0),
    2 => (int)($resumen['dos']    ?? 0),
    1 => (int)($resumen['uno']    ?? 0),
];

function estrellas(int $n): string {
    $s = '';
    for ($i = 1; $i <= 5; $i++) {
        $s .= $i <= $n
            ? '<i class="bi bi-star-fill text-warning"></i>'
            : '<i class="bi bi-star text-secondary"></i>';
    }
    return $s;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Calificaciones de Proveedor</title>
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
                <div class="col">
                    <h1>Calificaciones por Proveedor</h1>
                    <p class="text-muted mb-0">Consulta el historial de valoraciones recibidas por un proveedor.</p>
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
                    <form method="GET" action="<?= BASE_URL ?>/admin/calificaciones-proveedor" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Proveedor</label>
                            <select name="proveedor_id" class="form-select form-select-sm" required>
                                <option value="">-- Seleccionar proveedor --</option>
                                <?php foreach ($proveedores as $p): ?>
                                    <option value="<?= (int)$p['id'] ?>"
                                        <?= $proveedorId === (int)$p['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Desde</label>
                            <input type="date" name="desde" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($desde) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Hasta</label>
                            <input type="date" name="hasta" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($hasta) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Puntuación</label>
                            <select name="calificacion" class="form-select form-select-sm">
                                <option value="">Todas</option>
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <option value="<?= $i ?>" <?= $calFiltro === $i ? 'selected' : '' ?>>
                                        <?= $i ?> estrella<?= $i > 1 ? 's' : '' ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2 align-items-end">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-search me-1"></i>Filtrar
                            </button>
                            <a href="<?= BASE_URL ?>/admin/calificaciones-proveedor" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <?php if ($proveedorId && $reporte !== null): ?>

        <!-- KPIs -->
        <section class="mt-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Total reseñas</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($total) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Promedio</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($promedio, 1) ?> <span class="fs-6 text-warning"><i class="bi bi-star-fill"></i></span></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold d-block mb-2" style="font-size:.72rem;">Distribución</small>
                            <?php
                            $maxDist = max($dist) ?: 1;
                            foreach ($dist as $stars => $cnt):
                                $pct = round(($cnt / $maxDist) * 100);
                            ?>
                            <div class="d-flex align-items-center gap-2 mb-1" style="font-size:.8rem;">
                                <span style="width:60px;"><?= $stars ?> <i class="bi bi-star-fill text-warning"></i></span>
                                <div class="progress flex-grow-1" style="height:8px;">
                                    <div class="progress-bar bg-warning" style="width:<?= $pct ?>%"></div>
                                </div>
                                <span style="width:25px; text-align:right;" class="text-muted"><?= $cnt ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tabla detalle -->
        <section class="mt-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold border-0 pt-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-chat-square-text-fill text-primary me-2"></i>Historial de valoraciones</span>
                    <small class="text-muted fw-normal">Máx. 200 registros</small>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($detalle)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="font-size:.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Servicio</th>
                                        <th class="text-center">Puntuación</th>
                                        <th>Comentario</th>
                                        <th>Respuesta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detalle as $v): ?>
                                        <tr>
                                            <td class="text-muted small"><?= date('d/m/Y', strtotime($v['fecha'])) ?></td>
                                            <td><?= htmlspecialchars($v['cliente_nombre']) ?></td>
                                            <td class="text-muted small"><?= htmlspecialchars($v['servicio_nombre']) ?></td>
                                            <td class="text-center" style="font-size:.75rem;">
                                                <?= estrellas((int)$v['calificacion']) ?>
                                            </td>
                                            <td><?= $v['comentario'] ? htmlspecialchars($v['comentario']) : '<span class="text-muted">—</span>' ?></td>
                                            <td><?= $v['respuesta_proveedor'] ? htmlspecialchars($v['respuesta_proveedor']) : '<span class="text-muted">—</span>' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-star fs-2 d-block mb-2"></i>
                            No hay valoraciones con los filtros seleccionados.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php elseif ($proveedorId === null): ?>
        <div class="text-center text-muted py-5 mt-4">
            <i class="bi bi-person-badge fs-2 d-block mb-2"></i>
            Selecciona un proveedor para ver sus calificaciones.
        </div>
        <?php endif; ?>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>

</body>
</html>
