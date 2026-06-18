<?php
require_once BASE_PATH . '/app/helpers/session-cliente.php';
require_once BASE_PATH . '/app/models/cotizacion.php';

$uid          = (int)($_SESSION['user']['id'] ?? 0);
$cotizacionId = (int)($_GET['id'] ?? 0);

if ($cotizacionId <= 0) {
    header('Location: ' . BASE_URL . '/cliente/necesidades');
    exit;
}

$cotizacion = (new Cotizacion())->obtenerDetalleParaClienteUsuario($uid, $cotizacionId);

if (!$cotizacion) {
    header('Location: ' . BASE_URL . '/cliente/necesidades');
    exit;
}

$proveedorFoto = $cotizacion['proveedor_foto'] ?: 'default_user.png';
$precio        = $cotizacion['precio'] !== null ? (float)$cotizacion['precio'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Confirmar contratación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-cliente.css">
</head>
<body>
<?php
$currentPage = 'necesidades';
include_once __DIR__ . '/../../layouts/sidebar-cliente.php';
?>
<main class="contenido">
    <?php include_once __DIR__ . '/../../layouts/header-cliente.php'; ?>

    <section id="titulo-principal" class="section-hero mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-1">Confirmar contratación</h1>
                <p class="text-muted mb-0">Revisa los detalles antes de confirmar.</p>
            </div>
            <div class="col-md-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 justify-content-md-end">
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>/cliente/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>/cliente/necesidades">Mis necesidades</a>
                        </li>
                        <li class="breadcrumb-item active">Confirmar</li>
                    </ol>
                </nav>
            </div>
        </div>
    </section>

    <div class="container-fluid px-4 pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-xl-6">

                <!-- Tarjeta resumen -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-file-earmark-check text-primary me-2"></i>
                            Resumen de la cotización
                        </h5>
                    </div>
                    <div class="card-body px-4 pt-3">

                        <!-- Proveedor -->
                        <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded">
                            <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= htmlspecialchars($proveedorFoto) ?>"
                                 alt="Proveedor"
                                 class="rounded-circle"
                                 style="width:52px;height:52px;object-fit:cover;">
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($cotizacion['proveedor_nombre']) ?></div>
                                <div class="text-muted small">Proveedor del servicio</div>
                            </div>
                        </div>

                        <!-- Detalle de la cotización -->
                        <dl class="row g-0 mb-0">

                            <dt class="col-5 text-muted small py-2 border-bottom">Servicio</dt>
                            <dd class="col-7 py-2 border-bottom fw-semibold mb-0">
                                <?= htmlspecialchars($cotizacion['servicio_nombre'] ?: $cotizacion['titulo']) ?>
                            </dd>

                            <dt class="col-5 text-muted small py-2 border-bottom">Título de la oferta</dt>
                            <dd class="col-7 py-2 border-bottom mb-0">
                                <?= htmlspecialchars($cotizacion['titulo']) ?>
                            </dd>

                            <dt class="col-5 text-muted small py-2 border-bottom">Tu necesidad</dt>
                            <dd class="col-7 py-2 border-bottom mb-0">
                                <?= htmlspecialchars($cotizacion['necesidad_titulo']) ?>
                            </dd>

                            <?php if (!empty($cotizacion['mensaje'])): ?>
                            <dt class="col-5 text-muted small py-2 border-bottom">Descripción del proveedor</dt>
                            <dd class="col-7 py-2 border-bottom mb-0 small text-muted">
                                <?= nl2br(htmlspecialchars($cotizacion['mensaje'])) ?>
                            </dd>
                            <?php endif; ?>

                            <?php if (!empty($cotizacion['tiempo_estimado'])): ?>
                            <dt class="col-5 text-muted small py-2 border-bottom">Tiempo estimado</dt>
                            <dd class="col-7 py-2 border-bottom mb-0">
                                <?= htmlspecialchars($cotizacion['tiempo_estimado']) ?>
                            </dd>
                            <?php endif; ?>

                            <dt class="col-5 text-muted small py-2">Precio acordado</dt>
                            <dd class="col-7 py-2 mb-0">
                                <?php if ($precio !== null && $precio > 0): ?>
                                    <span class="fs-5 fw-bold text-success">
                                        $<?= number_format($precio, 0, ',', '.') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">A convenir</span>
                                <?php endif; ?>
                            </dd>

                        </dl>
                    </div>
                </div>

                <!-- Aviso -->
                <div class="alert alert-warning d-flex gap-2 align-items-start mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
                    <div class="small">
                        Al confirmar, aceptas formalmente la cotización y se generará el contrato de servicio.
                        Las demás ofertas para esta necesidad serán rechazadas automáticamente.
                    </div>
                </div>

                <!-- Acciones -->
                <div class="d-flex gap-3">
                    <a href="<?= BASE_URL ?>/cliente/necesidades"
                       class="btn btn-outline-secondary flex-fill">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                    <form method="POST"
                          action="<?= BASE_URL ?>/cliente/necesidades/aceptar-cotizacion"
                          class="flex-fill">
                        <input type="hidden" name="accion" value="aceptar_cotizacion">
                        <input type="hidden" name="cotizacion_id" value="<?= $cotizacionId ?>">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-1"></i> Confirmar contratación
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script>const BASE_URL = "<?= BASE_URL ?>";</script>
<script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
</body>
</html>
