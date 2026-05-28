<?php
require_once BASE_PATH . '/app/helpers/session-cliente.php';
require_once BASE_PATH . '/app/models/ServicioContratado.php';

$uid      = (int)($_SESSION['user']['id'] ?? 0);
$scModel  = new ServicioContratado();
$contratos = $scModel->listarPorClienteUsuario($uid);
$historial = array_values(array_filter($contratos, fn($c) => $c['estado'] === 'finalizado'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Historial de Servicios</title>
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

        <section id="historial-servicios">
            <div class="container">
                <div class="section-hero mb-4">
                    <p class="breadcrumb">Inicio &gt; Historial</p>
                    <h1><i class="bi bi-clock-history text-primary me-2"></i>Historial de Servicios</h1>
                    <p>Consulta todos los servicios que has contratado y completado en el pasado.</p>
                </div>

                <?php if (!empty($historial)): ?>
                <div class="row gy-4">
                    <?php foreach ($historial as $sc):
                        $titulo =
                            $sc['servicio_nombre']
                            ?? $sc['publicacion_titulo_cotizacion']
                            ?? $sc['publicacion_titulo_solicitud']
                            ?? $sc['cotizacion_titulo']
                            ?? $sc['solicitud_titulo']
                            ?? $sc['necesidad_titulo']
                            ?? 'Servicio completado';

                        $fecha =
                            $sc['fecha_ejecucion']
                            ?? $sc['necesidad_fecha_preferida']
                            ?? $sc['solicitud_fecha_preferida']
                            ?? $sc['fecha_solicitud']
                            ?? null;

                        $monto =
                            $sc['cotizacion_precio']
                            ?? $sc['solicitud_presupuesto_estimado']
                            ?? $sc['necesidad_presupuesto_estimado']
                            ?? null;

                        $calif    = $sc['mi_calificacion'] ?? null;
                        $yaValoro = (int)($sc['tiene_valoracion'] ?? 0) === 1;
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card service-item shadow-sm border-0 rounded-3 h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="fw-semibold mb-1"><?= htmlspecialchars($titulo) ?></h5>
                                <p class="text-muted mb-1 small">
                                    <strong>Prov.</strong> <?= htmlspecialchars($sc['proveedor_nombre'] ?? 'Proveedor') ?>
                                </p>
                                <?php if ($fecha): ?>
                                <p class="text-muted mb-2 small">
                                    <i class="bi bi-calendar-check"></i>
                                    <?= date('d M Y', strtotime($fecha)) ?>
                                </p>
                                <?php endif; ?>
                                <?php if (is_numeric($monto)): ?>
                                <p class="text-muted mb-2 small">
                                    <i class="bi bi-currency-dollar"></i>
                                    $<?= number_format((float)$monto, 0, ',', '.') ?>
                                </p>
                                <?php endif; ?>
                                <?php if ($yaValoro && is_numeric($calif)): ?>
                                <div class="mb-2">
                                    <?php $n = (int)round((float)$calif);
                                    for ($i = 0; $i < 5; $i++): ?>
                                        <i class="bi bi-star<?= $i < $n ? '-fill' : '' ?> text-warning small"></i>
                                    <?php endfor; ?>
                                </div>
                                <?php endif; ?>
                                <div class="d-flex gap-2 mt-auto">
                                    <a href="<?= BASE_URL ?>/cliente/servicios-contratados"
                                       class="btn btn-outline-primary flex-fill btn-sm">Ver detalles</a>
                                    <a href="<?= BASE_URL ?>/cliente/explorar"
                                       class="btn btn-outline-primary flex-fill btn-sm">Contratar de nuevo</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-clock-history text-muted" style="font-size:3rem;"></i>
                    <p class="text-muted mt-3">No tienes servicios completados aún.</p>
                    <a href="<?= BASE_URL ?>/cliente/explorar" class="btn btn-primary mt-2">Explorar servicios</a>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/dashboard-cliente.js"></script>
</body>
</html>
