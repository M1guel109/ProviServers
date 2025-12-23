<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Mi Cuenta</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <!-- Estilos específicos de cliente -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardCliente.css">
</head>
<body>
    <!-- SIDEBAR -->
    <?php 
    $currentPage = 'historial';
    include_once __DIR__ . '/../../layouts/sidebar_cliente.php'; 
    ?>
    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido">

    <!-- HEADER -->
    <?php include_once __DIR__ . '/../../layouts/header_cliente.php'; ?>

        <section id="historial-servicios">
        <div class="container">
            <div class="section-hero mb-4">
            <p class="breadcrumb">Inicio > Historial</p>
            <h1><i class="bi text-primary"></i>Historial de Servicios</h1>
            <p>Consulta todos los servicios que has contratado y completado en el pasado.</p>
            </div>

            <div class="row gy-4">
            <!-- Servicio completado -->
            <div class="col-lg-4 col-md-6">
                <div class="card service-item shadow-sm border-0 rounded-3 h-100">
                <div class="card-body">
                    <h5 class="fw-semibold">Limpieza Residencial</h5>
                    <p class="text-muted mb-1"><strong>Prov.</strong> Ana Gómez</p>
                    <p class="text-muted mb-3"><i class="bi bi-calendar-check"></i> 12 Nov 2024</p>
                    <div class="d-flex gap-2">
                    <a href="#" class="btn btn-outline-primary flex-fill btn-sm">Ver detalles</a>
                    <a href="#" class="btn btn-outline-primary flex-fill btn-sm">Contratar de nuevo</a>
                    </div>
                </div>
                </div>
            </div>

            <!-- Servicio completado -->
            <div class="col-lg-4 col-md-6">
                <div class="card service-item shadow-sm border-0 rounded-3 h-100">
                <div class="card-body">
                    <h5 class="fw-semibold">Electricidad</h5>
                    <p class="text-muted mb-1"><strong>Prov.</strong> Luis Martínez</p>
                    <p class="text-muted mb-3"><i class="bi bi-calendar-check"></i> 5 Oct 2024</p>
                    <div class="d-flex gap-2">
                    <a href="#" class="btn btn-outline-primary flex-fill btn-sm">Ver detalles</a>
                    <a href="#" class="btn btn-outline-primary flex-fill btn-sm">Contratar de nuevo</a>
                    </div>
                </div>
                </div>
            </div>

            <!-- Ejemplo adicional -->
            <div class="col-lg-4 col-md-6">
                <div class="card service-item shadow-sm border-0 rounded-3 h-100">
                <div class="card-body">
                    <h5 class="fw-semibold">Transporte Local</h5>
                    <p class="text-muted mb-1"><strong>Prov.</strong> Juan Pérez</p>
                    <p class="text-muted mb-3"><i class="bi bi-calendar-x"></i> 20 Sep 2024</p>
                    <div class="d-flex gap-2">
                    <a href="#" class="btn btn-outline-primary flex-fill btn-sm">Ver detalles</a>
                    <a href="#" class="btn btn-outline-primary flex-fill btn-sm">Contratar de nuevo</a>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </div>
        </section>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JS propio -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboardCliente.js"></script>
</body>
</html>