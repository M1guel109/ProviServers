<?php
require_once BASE_PATH . '/app/helpers/session-admin.php';
require_once BASE_PATH . '/app/models/publicacion.php';
require_once BASE_PATH . '/app/models/categoria.php';
require_once BASE_PATH . '/config/database.php';

// Filtros desde GET
$categoriaId = isset($_GET['categoria_id']) && $_GET['categoria_id'] !== '' ? (int)$_GET['categoria_id'] : null;
$estado      = isset($_GET['estado'])       && $_GET['estado']       !== '' ? $_GET['estado']       : null;
$proveedorId = isset($_GET['proveedor_id']) && $_GET['proveedor_id'] !== '' ? (int)$_GET['proveedor_id'] : null;
$desde       = $_GET['desde'] ?? '';
$hasta       = $_GET['hasta'] ?? '';

$modelo  = new Publicacion();
$reporte = $modelo->obtenerReporteServiciosOfrecidos(
    $categoriaId,
    $estado,
    $proveedorId,
    $desde ?: null,
    $hasta ?: null
);

$global        = $reporte['global'];
$publicaciones = $reporte['publicaciones'];
$porCategoria  = $reporte['porCategoria'];

$total            = (int)($global['total']            ?? 0);
$aprobados        = (int)($global['aprobados']        ?? 0);
$pendientes       = (int)($global['pendientes']       ?? 0);
$rechazados       = (int)($global['rechazados']       ?? 0);
$totalSolicitudes = (int)($global['total_solicitudes'] ?? 0);
$totalContratos   = (int)($global['total_contratos']   ?? 0);

// Listas para los selects de filtro
$catModelo  = new Categoria();
$categorias = $catModelo->mostrar();

$db          = new Conexion();
$pdo         = $db->getConexion();
$stProv      = $pdo->query("SELECT id, CONCAT(nombres, ' ', apellidos) AS nombre FROM proveedores ORDER BY nombres");
$proveedores = $stProv->fetchAll(PDO::FETCH_ASSOC);

// Construir query string para el botón de exportar PDF
$pdfQuery = http_build_query(array_filter([
    'tipo'         => 'serviciosOfrecidos',
    'categoria_id' => $categoriaId ?? '',
    'estado'       => $estado ?? '',
    'proveedor_id' => $proveedorId ?? '',
    'desde'        => $desde,
    'hasta'        => $hasta,
]));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Reporte de Servicios Ofrecidos</title>
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
                    <h1>Reporte de Servicios Ofrecidos</h1>
                    <p class="text-muted mb-0">Publicaciones de servicios registradas en la plataforma, con métricas de solicitudes y contratos.</p>
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
                    <form method="GET" action="<?= BASE_URL ?>/admin/reportes-servicios" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Categoría</label>
                            <select name="categoria_id" class="form-select form-select-sm">
                                <option value="">Todas las categorías</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= (int)$cat['id'] ?>"
                                        <?= $categoriaId === (int)$cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Estado</label>
                            <select name="estado" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="aprobado"  <?= $estado === 'aprobado'  ? 'selected' : '' ?>>Aprobado</option>
                                <option value="pendiente" <?= $estado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="rechazado" <?= $estado === 'rechazado' ? 'selected' : '' ?>>Rechazado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Proveedor</label>
                            <select name="proveedor_id" class="form-select form-select-sm">
                                <option value="">Todos los proveedores</option>
                                <?php foreach ($proveedores as $prov): ?>
                                    <option value="<?= (int)$prov['id'] ?>"
                                        <?= $proveedorId === (int)$prov['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($prov['nombre']) ?>
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
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-search me-1"></i>Aplicar filtros
                            </button>
                            <a href="<?= BASE_URL ?>/admin/reportes-servicios" class="btn btn-sm btn-outline-secondary">
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
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Total publicaciones</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($total) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Aprobadas</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($aprobados) ?></h2>
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
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Rechazadas</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($rechazados) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Solicitudes recibidas</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($totalSolicitudes) ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm h-100 border-start border-secondary border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Contratos generados</small>
                            <h2 class="fw-bold text-dark mb-0"><?= number_format($totalContratos) ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-4 mt-1">

            <!-- Por categoría -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold border-0 pt-3">
                        <i class="bi bi-grid-fill text-primary me-2"></i>Publicaciones por categoría
                    </div>
                    <div class="card-body">
                        <?php if (!empty($porCategoria)): ?>
                            <?php
                                $maxCat = max(array_column($porCategoria, 'total')) ?: 1;
                            ?>
                            <?php foreach ($porCategoria as $cat): ?>
                                <?php $pct = round(($cat['total'] / $maxCat) * 100); ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small fw-semibold"><?= htmlspecialchars($cat['categoria']) ?></span>
                                        <span class="text-muted small"><?= (int)$cat['total'] ?></span>
                                    </div>
                                    <div class="progress" style="height:8px;">
                                        <div class="progress-bar bg-primary" style="width:<?= $pct ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-grid fs-2 d-block mb-2"></i>
                                Sin datos.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tabla detalle -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold border-0 pt-3 d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-list-ul text-primary me-2"></i>Detalle de publicaciones</span>
                        <small class="text-muted fw-normal">Máx. 200 registros</small>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($publicaciones)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" style="font-size:.85rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Título</th>
                                            <th>Proveedor</th>
                                            <th>Categoría</th>
                                            <th class="text-end">Precio</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Solicitudes</th>
                                            <th class="text-center">Contratos</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($publicaciones as $pub): ?>
                                            <?php
                                                $badgeClass = match($pub['estado']) {
                                                    'aprobado'  => 'bg-success',
                                                    'pendiente' => 'bg-warning text-dark',
                                                    'rechazado' => 'bg-danger',
                                                    default     => 'bg-secondary',
                                                };
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($pub['titulo'] ?? $pub['servicio_nombre']) ?></td>
                                                <td class="text-muted"><?= htmlspecialchars($pub['proveedor_nombre']) ?></td>
                                                <td class="text-muted"><?= htmlspecialchars($pub['categoria_nombre'] ?? '—') ?></td>
                                                <td class="text-end">$<?= number_format((float)$pub['precio'], 0, ',', '.') ?></td>
                                                <td class="text-center">
                                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($pub['estado']) ?></span>
                                                </td>
                                                <td class="text-center fw-bold text-info"><?= (int)$pub['solicitudes'] ?></td>
                                                <td class="text-center fw-bold text-secondary"><?= (int)$pub['contratos'] ?></td>
                                                <td class="text-muted small">
                                                    <?= date('d/m/Y', strtotime($pub['created_at'])) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No hay publicaciones con los filtros seleccionados.
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
