<?php
require_once BASE_PATH . '/app/helpers/session-admin.php';
require_once BASE_PATH . '/config/database.php';

$estadoFiltro = $_GET['estado'] ?? 'todos';
$pagos        = [];
$kpis         = ['total' => 0, 'por_liberar' => 0, 'liberados' => 0, 'disputas' => 0, 'reembolsos' => 0];

try {
    $db  = new Conexion();
    $pdo = $db->getConexion();

    // KPIs
    $stKpi = $pdo->query("
        SELECT
            COALESCE(SUM(monto), 0)                                                  AS total,
            COALESCE(SUM(CASE WHEN mp_status = 'approved' AND liberado = 0 THEN monto ELSE 0 END), 0) AS por_liberar,
            COALESCE(SUM(CASE WHEN mp_status = 'approved' AND liberado = 1 THEN monto ELSE 0 END), 0) AS liberados,
            COALESCE(SUM(CASE WHEN mp_status IN ('charged_back','in_process')   THEN monto ELSE 0 END), 0) AS disputas,
            COALESCE(SUM(CASE WHEN mp_status IN ('refunded','rejected')         THEN monto ELSE 0 END), 0) AS reembolsos
        FROM pagos_servicios
    ");
    $kpis = $stKpi->fetch(PDO::FETCH_ASSOC);

    // Listado con filtro
    $whereClause = match($estadoFiltro) {
        'por_liberar' => "WHERE ps.mp_status = 'approved' AND ps.liberado = 0",
        'liberados'   => "WHERE ps.mp_status = 'approved' AND ps.liberado = 1",
        'disputas'    => "WHERE ps.mp_status IN ('charged_back','in_process')",
        'reembolsos'  => "WHERE ps.mp_status IN ('refunded','rejected')",
        default       => ''
    };

    $pagos = $pdo->query("
        SELECT
            ps.id,
            ps.monto,
            ps.mp_payment_id,
            ps.mp_status,
            ps.metodo,
            ps.liberado,
            ps.fecha_liberacion,
            ps.created_at,
            CONCAT(cl.nombres, ' ', cl.apellidos) AS cliente_nombre,
            CONCAT(pr.nombres, ' ', pr.apellidos) AS proveedor_nombre,
            s.nombre AS servicio_nombre
        FROM pagos_servicios ps
        INNER JOIN clientes cl    ON ps.cliente_id  = cl.id
        INNER JOIN proveedores pr ON ps.proveedor_id = pr.id
        INNER JOIN servicios_contratados sc ON ps.servicio_contratado_id = sc.id
        INNER JOIN servicios s    ON sc.servicio_id  = s.id
        $whereClause
        ORDER BY ps.created_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('admin/pagos.php: ' . $e->getMessage());
}

$fmt = fn($v) => '$' . number_format((float)$v, 0, ',', '.');

function badgePago(string $status, int $liberado): string {
    if ($status === 'approved' && $liberado) return '<span class="badge bg-success">Liberado</span>';
    if ($status === 'approved')               return '<span class="badge bg-warning text-dark">Por liberar</span>';
    return match($status) {
        'pending'      => '<span class="badge bg-secondary">Pendiente</span>',
        'in_process'   => '<span class="badge bg-info text-dark">En proceso</span>',
        'charged_back' => '<span class="badge bg-danger">Disputa</span>',
        'refunded'     => '<span class="badge bg-primary">Reembolsado</span>',
        'rejected'     => '<span class="badge bg-dark">Rechazado</span>',
        'cancelled'    => '<span class="badge bg-dark">Cancelado</span>',
        default        => '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>',
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Gestión de pagos</title>

    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-admin.css">
</head>
<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar-administrador.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-administrador.php'; ?>

        <section id="titulo-principal" class="section-hero mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-1">Gestión de Pagos</h1>
                    <p class="text-muted mb-0">Supervisa los pagos de servicios, libera fondos y gestiona disputas.</p>
                </div>
                <div class="col-md-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/admin/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                            </li>
                            <li class="breadcrumb-item active">Pagos</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <!-- KPIs -->
        <section class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3">
                            <i class="bi bi-cash-stack fs-4 text-success"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total recaudado</div>
                            <div class="fw-bold fs-5"><?= $fmt($kpis['total']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                            <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Por liberar</div>
                            <div class="fw-bold fs-5"><?= $fmt($kpis['por_liberar']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="bi bi-check2-circle fs-4 text-primary"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Liberados</div>
                            <div class="fw-bold fs-5"><?= $fmt($kpis['liberados']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                            <i class="bi bi-exclamation-triangle fs-4 text-danger"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Disputas / Reembolsos</div>
                            <div class="fw-bold fs-5"><?= $fmt($kpis['disputas'] + $kpis['reembolsos']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filtros de estado -->
        <section class="mb-3 d-flex flex-wrap gap-2">
            <?php
            $tabs = [
                'todos'       => ['label' => 'Todos',        'icon' => 'bi-list-ul'],
                'por_liberar' => ['label' => 'Por liberar',  'icon' => 'bi-hourglass-split'],
                'liberados'   => ['label' => 'Liberados',    'icon' => 'bi-check2-circle'],
                'disputas'    => ['label' => 'Disputas',     'icon' => 'bi-shield-exclamation'],
                'reembolsos'  => ['label' => 'Reembolsos',   'icon' => 'bi-arrow-counterclockwise'],
            ];
            foreach ($tabs as $key => $tab):
                $active = $estadoFiltro === $key ? 'btn-primary' : 'btn-outline-secondary';
            ?>
                <a href="<?= BASE_URL ?>/admin/pagos?estado=<?= $key ?>"
                   class="btn btn-sm <?= $active ?>">
                    <i class="bi <?= $tab['icon'] ?> me-1"></i><?= $tab['label'] ?>
                </a>
            <?php endforeach; ?>
        </section>

        <!-- Tabla de pagos -->
        <section class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($pagos)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No hay pagos en esta categoría.
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table id="tabla-pagos" class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Proveedor</th>
                                <th>Servicio</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagos as $p): ?>
                            <tr>
                                <td class="text-muted small"><?= $p['id'] ?></td>
                                <td class="small"><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                                <td><?= htmlspecialchars($p['cliente_nombre']) ?></td>
                                <td><?= htmlspecialchars($p['proveedor_nombre']) ?></td>
                                <td class="small text-muted"><?= htmlspecialchars($p['servicio_nombre']) ?></td>
                                <td class="fw-semibold"><?= $fmt($p['monto']) ?></td>
                                <td class="small text-capitalize"><?= htmlspecialchars($p['metodo'] ?? '—') ?></td>
                                <td><?= badgePago($p['mp_status'] ?? '', (int)$p['liberado']) ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <?php if (($p['mp_status'] ?? '') === 'approved' && !$p['liberado']): ?>
                                            <form method="POST" action="<?= BASE_URL ?>/admin/pagos/liberar"
                                                  onsubmit="return confirm('¿Liberar este pago al proveedor?')">
                                                <input type="hidden" name="accion"   value="liberar_pago">
                                                <input type="hidden" name="pago_id"  value="<?= $p['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="bi bi-check2-circle"></i> Liberar
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if (in_array($p['mp_status'] ?? '', ['charged_back', 'in_process'])): ?>
                                            <form method="POST" action="<?= BASE_URL ?>/admin/pagos/reembolsar"
                                                  onsubmit="return confirm('¿Marcar este pago como reembolsado?')">
                                                <input type="hidden" name="accion"   value="reembolsar_pago">
                                                <input type="hidden" name="pago_id"  value="<?= $p['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Reembolsar
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if (!in_array($p['mp_status'] ?? '', ['approved', 'charged_back', 'in_process'])): ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const t = document.getElementById('tabla-pagos');
            if (t) new DataTable(t, { pageLength: 20, language: { url: '//cdn.datatables.net/plug-ins/2.0.0/i18n/es-MX.json' } });
        });
    </script>
</body>
</html>
