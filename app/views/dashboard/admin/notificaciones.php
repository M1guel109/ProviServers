<?php
// Seguridad: solo admins
require_once BASE_PATH . '/app/helpers/session-admin.php';
require_once BASE_PATH . '/app/helpers/admin-notificaciones.php';

$notificaciones = obtenerNotificacionesAdmin();

// Helper de estilos (misma función del header)
function estiloNotif($tipo) {
    switch ($tipo) {
        case 'pago':          return ['icon' => 'bi-cash-coin',           'color' => 'text-success', 'bg' => 'bg-success-subtle'];
        case 'alerta':        return ['icon' => 'bi-exclamation-triangle', 'color' => 'text-warning', 'bg' => 'bg-warning-subtle'];
        case 'error':         return ['icon' => 'bi-x-circle',            'color' => 'text-danger',  'bg' => 'bg-danger-subtle'];
        case 'nuevo_usuario': return ['icon' => 'bi-person-plus',         'color' => 'text-primary', 'bg' => 'bg-primary-subtle'];
        default:              return ['icon' => 'bi-info-circle',         'color' => 'text-info',    'bg' => 'bg-info-subtle'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Notificaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard.css">
</head>
<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar-administrador.php'; ?>

    <main class="contenido">

        <?php include_once __DIR__ . '/../../layouts/header-administrador.php'; ?>

        <!-- Título -->
        <section id="titulo-principal">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Notificaciones</h1>
                    <p class="text-muted mb-0">Historial completo de alertas y eventos del sistema.</p>
                </div>
                <div class="col-md-4">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/admin/dashboard">Panel</a>
                            </li>
                            <li class="breadcrumb-item active">Notificaciones</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <!-- Lista de notificaciones -->
        <section class="mt-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">

                    <?php if (!empty($notificaciones)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($notificaciones as $notif): ?>
                                <?php
                                    $tipo   = $notif['tipo'] ?? 'info';
                                    $estilo = estiloNotif($tipo);
                                ?>
                                <li class="list-group-item d-flex align-items-start py-3 px-4">

                                    <!-- Ícono -->
                                    <div class="me-3 fs-5 <?= $estilo['color'] ?> <?= $estilo['bg'] ?> rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width: 42px; height: 42px;">
                                        <i class="bi <?= $estilo['icon'] ?>"></i>
                                    </div>

                                    <!-- Contenido -->
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
                        <!-- Estado vacío -->
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-bell-slash fs-1 d-block mb-3"></i>
                            <h5>Sin notificaciones</h5>
                            <p class="small">No hay alertas registradas en el sistema por ahora.</p>
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