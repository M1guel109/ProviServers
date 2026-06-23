<?php
require_once BASE_PATH . '/app/helpers/session-proveedor.php';
require_once BASE_PATH . '/app/models/valoracion.php';

$clienteId = isset($_GET['cliente_id']) && $_GET['cliente_id'] !== '' ? (int)$_GET['cliente_id'] : null;

$modelo  = new Valoracion();
$datos   = $clienteId ? $modelo->obtenerCalificacionesCliente($clienteId) : null;

$clienteInfo = $datos['cliente']  ?? [];
$resumen     = $datos['resumen']  ?? [];
$detalle     = $datos['detalle']  ?? [];

$total        = (int)($resumen['total']        ?? 0);
$promedioDado = (float)($resumen['promedio_dado'] ?? 0);

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
    <title>ProviServers | Calificaciones del Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/estilos-tablas.css">
</head>
<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar-proveedor.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-proveedor.php'; ?>

        <section id="titulo-principal">
            <div class="row align-items-center">
                <div class="col">
                    <h1>Historial de Calificaciones del Cliente</h1>
                    <p class="text-muted mb-0">Consulta las valoraciones que este cliente ha dejado a otros proveedores.</p>
                </div>
                <div class="col-auto">
                    <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>
        </section>

        <?php if ($clienteId && $datos !== null): ?>

            <?php if (empty($clienteInfo)): ?>
                <div class="alert alert-warning mt-4">Cliente no encontrado.</div>
            <?php else: ?>

                <!-- Info del cliente -->
                <section class="mt-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3">
                            <?php if (!empty($clienteInfo['foto'])): ?>
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($clienteInfo['foto']) ?>"
                                     class="rounded-circle" width="56" height="56"
                                     style="object-fit:cover;" alt="Foto cliente">
                            <?php else: ?>
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                                     style="width:56px;height:56px;">
                                    <i class="bi bi-person-fill text-white fs-4"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h5 class="mb-0 fw-bold"><?= htmlspecialchars($clienteInfo['nombre'] ?? '—') ?></h5>
                                <small class="text-muted">Cliente ID #<?= $clienteId ?></small>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- KPIs -->
                <section class="mt-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                                <div class="card-body">
                                    <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Valoraciones dadas</small>
                                    <h2 class="fw-bold text-dark mb-0"><?= number_format($total) ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                                <div class="card-body">
                                    <small class="text-muted text-uppercase fw-bold" style="font-size:.72rem;">Puntuación promedio que da</small>
                                    <h2 class="fw-bold text-dark mb-0">
                                        <?= $total > 0 ? number_format($promedioDado, 1) : '—' ?>
                                        <?php if ($total > 0): ?>
                                            <span class="fs-6 text-warning"><i class="bi bi-star-fill"></i></span>
                                        <?php endif; ?>
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Tabla -->
                <section class="mt-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white fw-bold border-0 pt-3">
                            <i class="bi bi-chat-square-text-fill text-primary me-2"></i>Valoraciones dejadas por el cliente
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($detalle)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" style="font-size:.85rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Proveedor evaluado</th>
                                                <th>Servicio</th>
                                                <th class="text-center">Puntuación</th>
                                                <th>Comentario</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($detalle as $v): ?>
                                                <tr>
                                                    <td class="text-muted small"><?= date('d/m/Y', strtotime($v['fecha'])) ?></td>
                                                    <td><?= htmlspecialchars($v['proveedor_nombre']) ?></td>
                                                    <td class="text-muted small"><?= htmlspecialchars($v['servicio_nombre']) ?></td>
                                                    <td class="text-center" style="font-size:.75rem;">
                                                        <?= estrellas((int)$v['calificacion']) ?>
                                                    </td>
                                                    <td><?= $v['comentario'] ? htmlspecialchars($v['comentario']) : '<span class="text-muted">—</span>' ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-star fs-2 d-block mb-2"></i>
                                    Este cliente aún no ha dejado valoraciones.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

            <?php endif; ?>

        <?php else: ?>
            <div class="text-center text-muted py-5 mt-4">
                <i class="bi bi-person-x fs-2 d-block mb-2"></i>
                No se especificó un cliente. Accede desde el detalle de una solicitud.
            </div>
        <?php endif; ?>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>

</body>
</html>
