<?php
require_once BASE_PATH . '/app/helpers/session-cliente.php';
require_once BASE_PATH . '/app/helpers/notificaciones-cliente.php';

$notificaciones = obtenerNotificacionesCliente((int)($_SESSION['user']['id'] ?? 0));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Notificaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-cliente.css">
</head>
<body>

    <?php
    $currentPage = 'notificaciones';
    include_once __DIR__ . '/../../layouts/sidebar-cliente.php';
    ?>

    <main class="contenido">

        <?php include_once __DIR__ . '/../../layouts/header-cliente.php'; ?>

        <section class="p-3">
            <div id="titulo-principal" class="section-hero mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-1">Notificaciones</h1>
                        <p class="text-muted mb-0">Historial de alertas y eventos relacionados con tus servicios.</p>
                    </div>
                    <div class="col-md-4">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 justify-content-md-end">
                                <li class="breadcrumb-item">
                                    <a href="<?= BASE_URL ?>/cliente/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Notificaciones</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">

                    <?php if (!empty($notificaciones)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($notificaciones as $notif): ?>
                                <?php $estilo = estiloNotificacion($notif['tipo'] ?? 'info'); ?>
                                <li class="list-group-item d-flex align-items-start py-3 px-4">

                                    <div class="me-3 fs-5 <?= $estilo['color'] ?> <?= $estilo['bg'] ?> rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:42px; height:42px;">
                                        <i class="bi <?= $estilo['icon'] ?>"></i>
                                    </div>

                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h6 class="mb-1 fw-bold text-dark">
                                                <?= htmlspecialchars($notif['titulo']) ?>
                                            </h6>
                                            <small class="text-muted ms-3 flex-shrink-0">
                                                <i class="bi bi-clock me-1"></i>
                                                <?= htmlspecialchars($notif['hora']) ?>
                                            </small>
                                        </div>
                                        <p class="mb-0 text-secondary small">
                                            <?= htmlspecialchars($notif['mensaje']) ?>
                                        </p>
                                    </div>

                                </li>
                            <?php endforeach; ?>
                        </ul>

                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-bell-slash fs-1 d-block mb-3"></i>
                            <h5>Sin notificaciones</h5>
                            <p class="small">No tienes alertas pendientes en este momento.</p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>const BASE_URL = "<?= BASE_URL ?>";</script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
</body>
</html>
