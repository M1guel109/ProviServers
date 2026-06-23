<?php
require_once BASE_PATH . '/app/helpers/session-cliente.php';
require_once BASE_PATH . '/config/database.php';

$uid     = (int)$_SESSION['user']['id'];
$pagos   = [];
$kpis    = ['total' => 0, 'monto_total' => 0, 'aprobados' => 0, 'pendientes' => 0];

// Filtros GET
$filtroEstado = $_GET['estado']  ?? '';
$filtroDesde  = $_GET['desde']   ?? '';
$filtroHasta  = $_GET['hasta']   ?? '';

try {
    $db  = new Conexion();
    $pdo = $db->getConexion();

    // Obtener cliente_id desde usuario
    $stCl = $pdo->prepare("SELECT id FROM clientes WHERE usuario_id = :uid LIMIT 1");
    $stCl->execute([':uid' => $uid]);
    $clienteId = (int)$stCl->fetchColumn();

    if ($clienteId > 0) {
        $where = "WHERE ps.cliente_id = :cid";
        $params = [':cid' => $clienteId];

        if ($filtroEstado !== '') {
            $where   .= " AND ps.mp_status = :estado";
            $params[':estado'] = $filtroEstado;
        }
        if ($filtroDesde !== '') {
            $where   .= " AND DATE(ps.created_at) >= :desde";
            $params[':desde'] = $filtroDesde;
        }
        if ($filtroHasta !== '') {
            $where   .= " AND DATE(ps.created_at) <= :hasta";
            $params[':hasta'] = $filtroHasta;
        }

        $st = $pdo->prepare("
            SELECT
                ps.id,
                ps.monto,
                ps.mp_status,
                ps.mp_payment_id,
                ps.metodo,
                ps.liberado,
                ps.created_at,
                COALESCE(cot.titulo, sol.titulo, sv.nombre, 'Servicio')      AS servicio_nombre,
                CONCAT(prov.nombres, ' ', prov.apellidos)                   AS proveedor_nombre
            FROM pagos_servicios ps
            INNER JOIN proveedores prov ON ps.proveedor_id = prov.id
            LEFT JOIN servicios_contratados sc ON ps.servicio_contratado_id = sc.id
            LEFT JOIN cotizaciones cot         ON sc.cotizacion_id          = cot.id
            LEFT JOIN solicitudes sol           ON sc.solicitud_id           = sol.id
            LEFT JOIN servicios sv              ON sc.servicio_id            = sv.id
            $where
            ORDER BY ps.created_at DESC
        ");
        $st->execute($params);
        $pagos = $st->fetchAll(PDO::FETCH_ASSOC);

        // KPIs (sin filtros de fecha/estado para mostrar totales reales)
        $stK = $pdo->prepare("
            SELECT
                COUNT(*)                                         AS total,
                COALESCE(SUM(monto), 0)                          AS monto_total,
                SUM(mp_status = 'approved')                      AS aprobados,
                SUM(mp_status IN ('pending','in_process'))       AS pendientes
            FROM pagos_servicios WHERE cliente_id = :cid
        ");
        $stK->execute([':cid' => $clienteId]);
        $kpis = $stK->fetch(PDO::FETCH_ASSOC) ?: $kpis;
    }
} catch (PDOException $e) {
    error_log('historial-pagos cliente: ' . $e->getMessage());
}

function badgePagoCliente(string $status): string {
    return match($status) {
        'approved'              => '<span class="badge bg-success">Aprobado</span>',
        'pending', 'in_process' => '<span class="badge bg-warning text-dark">Pendiente</span>',
        'refunded'              => '<span class="badge bg-info text-dark">Reembolsado</span>',
        'charged_back'          => '<span class="badge bg-secondary">Contracargo</span>',
        'rejected'              => '<span class="badge bg-danger">Rechazado</span>',
        default                 => '<span class="badge bg-light text-dark">' . htmlspecialchars($status) . '</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Historial de pagos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-cliente.css">
</head>
<body>
<?php
$currentPage = 'historial-pagos';
include_once __DIR__ . '/../../layouts/sidebar-cliente.php';
?>
<main class="contenido">
    <?php include_once __DIR__ . '/../../layouts/header-cliente.php'; ?>

    <section id="titulo-principal" class="section-hero mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-1">Historial de pagos</h1>
                <p class="text-muted mb-0">Todos los pagos que has realizado en la plataforma.</p>
            </div>
            <div class="col-md-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 justify-content-md-end">
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>/cliente/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                        </li>
                        <li class="breadcrumb-item active">Historial de pagos</li>
                    </ol>
                </nav>
            </div>
        </div>
    </section>

    <!-- KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body py-3">
                    <div class="fs-4 fw-bold text-primary"><?= (int)$kpis['total'] ?></div>
                    <div class="text-muted small">Total pagos</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body py-3">
                    <div class="fs-4 fw-bold text-success">$<?= number_format((float)$kpis['monto_total'], 0, ',', '.') ?></div>
                    <div class="text-muted small">Total pagado</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body py-3">
                    <div class="fs-4 fw-bold text-success"><?= (int)$kpis['aprobados'] ?></div>
                    <div class="text-muted small">Aprobados</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body py-3">
                    <div class="fs-4 fw-bold text-warning"><?= (int)$kpis['pendientes'] ?></div>
                    <div class="text-muted small">Pendientes</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/cliente/historial-pagos" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Estado</label>
                    <select name="estado" class="form-select form-select-sm">
                        <option value="">Todos los estados</option>
                        <option value="approved"    <?= $filtroEstado === 'approved'    ? 'selected' : '' ?>>Aprobado</option>
                        <option value="pending"     <?= $filtroEstado === 'pending'     ? 'selected' : '' ?>>Pendiente</option>
                        <option value="refunded"    <?= $filtroEstado === 'refunded'    ? 'selected' : '' ?>>Reembolsado</option>
                        <option value="charged_back"<?= $filtroEstado === 'charged_back'? 'selected' : '' ?>>Contracargo</option>
                        <option value="rejected"    <?= $filtroEstado === 'rejected'    ? 'selected' : '' ?>>Rechazado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Desde</label>
                    <input type="date" name="desde" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($filtroDesde) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Hasta</label>
                    <input type="date" name="hasta" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($filtroHasta) ?>">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-funnel me-1"></i>Filtrar
                    </button>
                    <a href="<?= BASE_URL ?>/cliente/historial-pagos" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tabla-historial-pagos" class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Servicio</th>
                            <th>Proveedor</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Referencia MP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagos as $p): ?>
                        <tr>
                            <td class="text-nowrap small text-muted">
                                <?= date('d/m/Y', strtotime($p['created_at'])) ?>
                            </td>
                            <td><?= htmlspecialchars($p['servicio_nombre']) ?></td>
                            <td class="small"><?= htmlspecialchars($p['proveedor_nombre']) ?></td>
                            <td class="fw-semibold text-nowrap">
                                $<?= number_format((float)$p['monto'], 0, ',', '.') ?>
                            </td>
                            <td><?= badgePagoCliente($p['mp_status']) ?></td>
                            <td class="small text-muted">
                                <?= $p['mp_payment_id'] ? '#' . htmlspecialchars((string)$p['mp_payment_id']) : '—' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (empty($pagos)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                    <p class="mb-0">No se encontraron pagos con los filtros aplicados.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
<script>
    $('#tabla-historial-pagos').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pageLength: 15,
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: [5] }],
    });
</script>
</body>
</html>
