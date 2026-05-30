<?php
require_once BASE_PATH . '/app/helpers/session-cliente.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Favoritos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-cliente.css">
</head>
<body>
    <?php
    $currentPage = 'favoritos';
    include_once __DIR__ . '/../../layouts/sidebar-cliente.php';
    ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-cliente.php'; ?>

        <section id="favoritos">
            <div class="container">
                <div id="titulo-principal" class="section-hero mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="mb-1"><i class="bi bi-heart-fill text-danger me-2"></i>Mis Favoritos</h1>
                            <p class="text-muted mb-0">Tus proveedores preferidos, guardados para contratarlos fácilmente.</p>
                        </div>
                        <div class="col-md-4">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 justify-content-md-end">
                                    <li class="breadcrumb-item">
                                        <a href="<?= BASE_URL ?>/cliente/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">Favoritos</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <div class="text-center py-5">
                    <i class="bi bi-heart text-muted" style="font-size:3rem;"></i>
                    <h5 class="mt-3 text-muted fw-semibold">Aún no tienes favoritos guardados</h5>
                    <p class="text-muted small">Explora proveedores y guarda los que más te gusten para encontrarlos rápidamente.</p>
                    <a href="<?= BASE_URL ?>/cliente/explorar" class="btn btn-primary mt-2">
                        <i class="bi bi-search me-2"></i>Explorar proveedores
                    </a>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/dashboard-cliente.js"></script>
</body>
</html>
