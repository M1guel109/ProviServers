<?php
require_once BASE_PATH . '/app/helpers/session-proveedor.php';
require_once BASE_PATH . '/config/database.php';

$uid = (int)($_SESSION['user']['id'] ?? 0);

// ── Filtros GET ───────────────────────────────────────────────────────
$filtroEstado  = trim($_GET['estado']   ?? '');
$filtroDesde   = trim($_GET['desde']    ?? '');
$filtroHasta   = trim($_GET['hasta']    ?? '');
$filtroCliente = trim($_GET['cliente']  ?? '');

$contratos = [];
$kpis = ['total' => 0, 'completados' => 0, 'cancelados' => 0, 'calificacion' => null, 'ingresos' => 0.0];

try {
    $db  = new Conexion();
    $pdo = $db->getConexion();

    $stProv = $pdo->prepare("SELECT id FROM proveedores WHERE usuario_id = :uid LIMIT 1");
    $stProv->execute([':uid' => $uid]);
    $proveedorId = (int)($stProv->fetchColumn() ?: 0);

    if ($proveedorId > 0) {
        $where  = "WHERE sc.proveedor_id = :pid";
        $params = [':pid' => $proveedorId];

        if ($filtroEstado !== '') {
            $where .= " AND sc.estado = :estado";
            $params[':estado'] = $filtroEstado;
        }
        if ($filtroDesde !== '') {
            $where .= " AND DATE(sc.created_at) >= :desde";
            $params[':desde'] = $filtroDesde;
        }
        if ($filtroHasta !== '') {
            $where .= " AND DATE(sc.created_at) <= :hasta";
            $params[':hasta'] = $filtroHasta;
        }
        if ($filtroCliente !== '') {
            $where .= " AND CONCAT(cl.nombres, ' ', cl.apellidos) LIKE :cliente";
            $params[':cliente'] = '%' . $filtroCliente . '%';
        }

        $st = $pdo->prepare("
            SELECT
                sc.id                                                           AS contrato_id,
                sc.estado,
                sc.created_at,
                sc.fecha_ejecucion,
                COALESCE(cot.titulo, sol.titulo, sv.nombre, 'Servicio')         AS titulo,
                CONCAT(cl.nombres, ' ', cl.apellidos)                           AS cliente_nombre,
                COALESCE(cot.precio, pub_sol.precio, sv.precio, 0)              AS precio_base,
                ps.monto                                                        AS monto_pagado,
                ps.mp_status                                                    AS pago_estado,
                ps.liberado,
                cal.puntaje                                                     AS calificacion
            FROM servicios_contratados sc
            INNER JOIN clientes cl          ON sc.cliente_id        = cl.id
            LEFT JOIN cotizaciones cot      ON sc.cotizacion_id     = cot.id
            LEFT JOIN solicitudes sol       ON sc.solicitud_id      = sol.id
            LEFT JOIN publicaciones pub_sol ON sol.publicacion_id   = pub_sol.id
            LEFT JOIN servicios sv          ON sc.servicio_id       = sv.id
            LEFT JOIN pagos_servicios ps    ON ps.servicio_contratado_id = sc.id
            LEFT JOIN calificaciones cal    ON cal.servicio_contratado_id = sc.id
            $where
            ORDER BY sc.created_at DESC
        ");
        $st->execute($params);
        $contratos = $st->fetchAll(PDO::FETCH_ASSOC);

        // KPIs (siempre sobre todos los contratos del proveedor)
        $stKpi = $pdo->prepare("
            SELECT
                COUNT(*)                                                              AS total,
                SUM(sc.estado = 'finalizado')                                         AS completados,
                SUM(sc.estado IN ('cancelado','cancelado_cliente','cancelado_proveedor')) AS cancelados,
                ROUND(AVG(cal.puntaje), 1)                                            AS calificacion,
                COALESCE(SUM(ps.monto), 0)                                            AS ingresos
            FROM servicios_contratados sc
            LEFT JOIN pagos_servicios ps    ON ps.servicio_contratado_id = sc.id
            LEFT JOIN calificaciones cal    ON cal.servicio_contratado_id = sc.id
            WHERE sc.proveedor_id = :pid
        ");
        $stKpi->execute([':pid' => $proveedorId]);
        $kpis = $stKpi->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log('completadas.php: ' . $e->getMessage());
}

function badgeContratoP(string $estado): string {
    return match($estado) {
        'pendiente'           => '<span class="badge bg-secondary">Pendiente</span>',
        'confirmado'          => '<span class="badge bg-info text-dark">Confirmado</span>',
        'en_proceso'          => '<span class="badge bg-primary">En proceso</span>',
        'finalizado'          => '<span class="badge bg-success">Completado</span>',
        'cancelado',
        'cancelado_cliente',
        'cancelado_proveedor' => '<span class="badge bg-danger">Cancelado</span>',
        default               => '<span class="badge bg-light text-dark">' . htmlspecialchars($estado) . '</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Historial de contrataciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-proveedor.css">
</head>
<body>
<?php
$currentPage = 'completadas';
include_once __DIR__ . '/../../layouts/sidebar-proveedor.php';
?>
<main class="contenido">
    <?php include_once __DIR__ . '/../../layouts/header-proveedor.php'; ?>

    <section id="titulo-principal" class="section-hero mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-1">Historial de contrataciones</h1>
                <p class="text-muted mb-0">Todos tus servicios contratados, en cualquier estado.</p>
            </div>
            <div class="col-md-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 justify-content-md-end">
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>/proveedor/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                        </li>
                        <li class="breadcrumb-item active">Historial</li>
                    </ol>
                </nav>
            </div>
        </div>
    </section>

    <div class="container-fluid px-4 pb-5">

        <!-- KPIs -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fs-2 fw-bold text-primary"><?= (int)$kpis['total'] ?></div>
                    <div class="small text-muted">Total contratos</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fs-2 fw-bold text-success"><?= (int)$kpis['completados'] ?></div>
                    <div class="small text-muted">Completados</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fs-2 fw-bold text-warning">
                        <?= $kpis['calificacion'] !== null ? number_format((float)$kpis['calificacion'], 1) : '—' ?>
                        <?php if ($kpis['calificacion'] !== null): ?>
                            <i class="bi bi-star-fill fs-5"></i>
                        <?php endif; ?>
                    </div>
                    <div class="small text-muted">Calificación promedio</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center p-3">
                    <div class="fs-4 fw-bold text-success">$<?= number_format((float)$kpis['ingresos'], 0, ',', '.') ?></div>
                    <div class="small text-muted">Ingresos totales</div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="<?= BASE_URL ?>/proveedor/completadas" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Estado</label>
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Todos los estados</option>
                            <option value="pendiente"   <?= $filtroEstado === 'pendiente'   ? 'selected' : '' ?>>Pendiente</option>
                            <option value="confirmado"  <?= $filtroEstado === 'confirmado'  ? 'selected' : '' ?>>Confirmado</option>
                            <option value="en_proceso"  <?= $filtroEstado === 'en_proceso'  ? 'selected' : '' ?>>En proceso</option>
                            <option value="finalizado"  <?= $filtroEstado === 'finalizado'  ? 'selected' : '' ?>>Completado</option>
                            <option value="cancelado"   <?= $filtroEstado === 'cancelado'   ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Desde</label>
                        <input type="date" name="desde" class="form-control form-control-sm" value="<?= htmlspecialchars($filtroDesde) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Hasta</label>
                        <input type="date" name="hasta" class="form-control form-control-sm" value="<?= htmlspecialchars($filtroHasta) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Cliente</label>
                        <input type="text" name="cliente" class="form-control form-control-sm"
                               placeholder="Nombre del cliente"
                               value="<?= htmlspecialchars($filtroCliente) ?>">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                        <a href="<?= BASE_URL ?>/proveedor/completadas" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($contratos)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-clipboard-x fs-1"></i>
                        <p class="mt-3">No se encontraron contrataciones con los filtros seleccionados.</p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table id="tabla-historial" class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Servicio</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th class="text-center">Estado</th>
                                <th class="text-end">Monto</th>
                                <th class="text-center">Calificación</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($contratos as $c): ?>
                            <tr>
                                <td class="text-muted small"><?= str_pad($c['contrato_id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($c['titulo']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($c['cliente_nombre']) ?></td>
                                <td class="text-muted small">
                                    <?= $c['created_at'] ? date('d/m/Y', strtotime($c['created_at'])) : '—' ?>
                                </td>
                                <td class="text-center"><?= badgeContratoP($c['estado']) ?></td>
                                <td class="text-end fw-semibold">
                                    <?php $monto = $c['monto_pagado'] ?? $c['precio_base']; ?>
                                    <?= $monto > 0 ? '$' . number_format((float)$monto, 0, ',', '.') : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($c['calificacion'] !== null): ?>
                                        <span class="text-warning fw-semibold">
                                            <?= number_format((float)$c['calificacion'], 1) ?>
                                            <i class="bi bi-star-fill small"></i>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>const BASE_URL = "<?= BASE_URL ?>";</script>
<script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
<script>
$(document).ready(function () {
    $('#tabla-historial').DataTable({
        order: [[3, 'desc']],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        columnDefs: [{ orderable: false, targets: [6] }]
    });
});
</script>
</body>
</html>
