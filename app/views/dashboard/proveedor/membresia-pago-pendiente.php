<?php require_once BASE_PATH . '/app/helpers/session-proveedor.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Pago pendiente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-Proveedor.css">
</head>
<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar-proveedor.php'; ?>
    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-proveedor.php'; ?>
        <div class="d-flex flex-column align-items-center justify-content-center py-5">
            <div class="card shadow border-0 rounded-4 p-5 text-center" style="max-width:480px;">
                <div class="mb-4">
                    <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex p-4">
                        <i class="bi bi-clock-fill text-warning" style="font-size:3rem;"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-2">Pago en revisión</h2>
                <p class="text-muted mb-4">MercadoPago está verificando tu pago. Una vez confirmado, tu plan se activará automáticamente. Recibirás una notificación.</p>
                <a href="<?= BASE_URL ?>/proveedor/membresia" class="btn btn-warning mb-2">
                    <i class="bi bi-gem me-2"></i>Ver mi membresía
                </a>
                <a href="<?= BASE_URL ?>/proveedor/dashboard" class="btn btn-outline-secondary">
                    Ir al panel
                </a>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
