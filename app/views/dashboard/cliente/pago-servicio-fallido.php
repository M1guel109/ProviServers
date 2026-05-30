<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Pago Fallido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-cliente.css">
</head>
<body>
<?php
$currentPage = 'servicios-contratados';
include_once __DIR__ . '/../../layouts/sidebar-cliente.php';
?>
<main class="contenido">
    <?php include_once __DIR__ . '/../../layouts/header-cliente.php'; ?>

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/cliente/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/cliente/servicios-contratados">Servicios</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Estado de pago</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-center" style="min-height:65vh;">
        <div class="text-center" style="max-width:480px;">
            <div class="mb-4">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10"
                      style="width:100px;height:100px;">
                    <i class="bi bi-x-circle-fill text-danger" style="font-size:3rem;"></i>
                </span>
            </div>
            <h2 class="fw-bold text-danger mb-2">Pago fallido</h2>
            <p class="text-muted mb-4">
                No se pudo procesar tu pago. Verifica tu método de pago e intenta de nuevo.
                El servicio permanece activo y puedes reintentar cuando quieras.
            </p>
            <div class="d-flex flex-column gap-2">
                <a href="<?= BASE_URL ?>/cliente/servicios-contratados"
                   class="btn btn-danger btn-lg">
                    <i class="bi bi-arrow-repeat me-2"></i>Intentar de nuevo
                </a>
                <a href="<?= BASE_URL ?>/cliente/dashboard"
                   class="btn btn-outline-secondary">
                    Ir al inicio
                </a>
            </div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
</body>
</html>
