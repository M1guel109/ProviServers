<?php
require_once BASE_PATH . '/app/helpers/session-cliente.php';
require_once BASE_PATH . '/app/models/ServicioContratado.php';
require_once BASE_PATH . '/config/database.php';

$uid      = (int)($_SESSION['user']['id'] ?? 0);
$scModel  = new ServicioContratado();
$contratos = $scModel->listarPorClienteUsuario($uid);
$historial = array_values(array_filter($contratos, fn($c) => $c['estado'] === 'finalizado'));

// Estadísticas rápidas
$totalGastado   = 0.0;
$totalServicios = count($historial);

// Obtener datos de pagos reales desde pagos_servicios
$pagosMap = [];
try {
    $db2 = new Conexion();
    $pdo2 = $db2->getConexion();

    $stCl = $pdo2->prepare("SELECT id FROM clientes WHERE usuario_id = :uid LIMIT 1");
    $stCl->execute([':uid' => $uid]);
    $clienteId = (int)($stCl->fetchColumn() ?: 0);

    if ($clienteId > 0) {
        $stPag = $pdo2->prepare("
            SELECT servicio_contratado_id, monto, liberado, fecha_liberacion, created_at AS fecha_pago
            FROM pagos_servicios WHERE cliente_id = :cid
        ");
        $stPag->execute([':cid' => $clienteId]);
        foreach ($stPag->fetchAll(PDO::FETCH_ASSOC) as $p) {
            $pagosMap[(int)$p['servicio_contratado_id']] = $p;
            $totalGastado += (float)$p['monto'];
        }
    }
} catch (PDOException $e) { /* pagos_servicios puede no existir aún */ }

$ultimaFecha = !empty($historial)
    ? ($historial[0]['fecha_ejecucion'] ?? $historial[0]['fecha_solicitud'] ?? null)
    : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Historial de Pagos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-cliente.css">
</head>
<body>
    <?php
    $currentPage = 'historial';
    include_once __DIR__ . '/../../layouts/sidebar-cliente.php';
    ?>
    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-cliente.php'; ?>

        <section id="historial-servicios" class="p-3 p-md-4">

            <!-- Encabezado -->
            <div class="section-hero mb-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/cliente/dashboard">Inicio</a></li>
                        <li class="breadcrumb-item active">Historial</li>
                    </ol>
                </nav>
                <h1>Historial de Pagos</h1>
                <p class="text-muted">Registro completo de todos tus servicios contratados y pagados.</p>
            </div>

            <!-- Estadísticas -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <div class="fs-1 fw-bold text-primary"><?= $totalServicios ?></div>
                        <div class="small text-muted">Servicios completados</div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <div class="fs-4 fw-bold text-success">
                            $<?= number_format($totalGastado, 0, ',', '.') ?>
                        </div>
                        <div class="small text-muted">Total pagado</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm text-center p-3">
                        <div class="fw-bold text-secondary" style="font-size:1.1rem;">
                            <?= $ultimaFecha ? date('d M Y', strtotime($ultimaFecha)) : '—' ?>
                        </div>
                        <div class="small text-muted">Último servicio</div>
                    </div>
                </div>
            </div>

            <!-- Tabla de historial -->
            <?php if (!empty($historial)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <span class="fw-bold">Mis servicios</span>
                    <span class="badge bg-secondary"><?= $totalServicios ?> registros</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Servicio</th>
                                <th>Proveedor</th>
                                <th>Fecha</th>
                                <th class="text-end">Monto</th>
                                <th class="text-center">Pago</th>
                                <th class="text-center">Calificación</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($historial as $sc):
                            $contratoId = (int)($sc['contrato_id'] ?? 0);
                            $titulo = $sc['servicio_nombre'] ?? $sc['solicitud_titulo'] ?? $sc['cotizacion_titulo'] ?? 'Servicio';
                            $proveedor = $sc['proveedor_nombre'] ?? '—';
                            $monto = (float)($sc['monto'] ?? 0);

                            $fechaRaw = $sc['fecha_ejecucion'] ?? $sc['solicitud_fecha_preferida'] ?? $sc['fecha_solicitud'] ?? null;
                            $fechaTexto = $fechaRaw ? date('d M Y', strtotime($fechaRaw)) : '—';

                            $pago = $pagosMap[$contratoId] ?? null;
                            $montoPagado = $pago ? (float)$pago['monto'] : null;
                            $fechaPago   = $pago ? date('d M Y', strtotime($pago['fecha_pago'])) : null;
                            $liberado    = $pago ? (bool)$pago['liberado'] : false;

                            $calif   = (int)($sc['mi_calificacion'] ?? 0);
                            $yaValoró = (int)($sc['tiene_valoracion'] ?? 0) === 1;
                        ?>
                        <tr>
                            <td>
                                <span class="fw-semibold"><?= htmlspecialchars($titulo) ?></span>
                            </td>
                            <td class="text-muted small"><?= htmlspecialchars($proveedor) ?></td>
                            <td class="text-muted small"><?= $fechaTexto ?></td>
                            <td class="text-end fw-semibold">
                                <?php if ($montoPagado !== null): ?>
                                    <span class="text-success">$<?= number_format($montoPagado, 0, ',', '.') ?></span>
                                <?php elseif ($monto > 0): ?>
                                    <span class="text-muted">$<?= number_format($monto, 0, ',', '.') ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($pago): ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Pagado
                                    </span>
                                    <?php if ($fechaPago): ?>
                                        <div class="text-muted" style="font-size:0.7rem;"><?= $fechaPago ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Sin registro</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($yaValoró && $calif > 0): ?>
                                    <span class="text-warning">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?= $i <= $calif ? '-fill' : '' ?>" style="font-size:.8rem;"></i>
                                        <?php endfor; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">Sin calificar</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <?php if ($contratoId > 0): ?>
                                    <a href="<?= BASE_URL ?>/cliente/contrato-pdf?id=<?= $contratoId ?>"
                                       class="btn btn-outline-secondary btn-sm" target="_blank"
                                       title="Descargar comprobante">
                                        <i class="bi bi-file-pdf"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?= BASE_URL ?>/cliente/explorar"
                                       class="btn btn-outline-primary btn-sm"
                                       title="Contratar de nuevo">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-clock-history text-muted" style="font-size:3.5rem;"></i>
                <h5 class="mt-3 text-muted">Aún no tienes servicios completados</h5>
                <p class="text-muted small">Cuando finalices un servicio aparecerá aquí con su comprobante.</p>
                <a href="<?= BASE_URL ?>/cliente/explorar" class="btn btn-primary mt-2">
                    <i class="bi bi-search me-1"></i> Explorar servicios
                </a>
            </div>
            <?php endif; ?>

        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
</body>
</html>
