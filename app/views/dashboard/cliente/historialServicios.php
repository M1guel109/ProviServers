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
    <?php include_once __DIR__ . '/../../layouts/sidebar_cliente.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido">
    <section id="historial-servicios">
        <div class="section-hero">
        <h1>Historial de Servicios </h1>
        <p>Consulta todos los servicios que has contratado y completado en el pasado.</p>
        </div>

        <div class="section-content">
        <div class="services-grid">
            <!-- Ejemplo de servicio completado -->
            <div class="service-item">
            <div class="service-content">
                <span class="badge bg-success mb-2">Completado</span>
                <h3>Limpieza Residencial</h3>
                <p><strong>Proveedor:</strong> Ana Gómez</p>
                <p><strong>Fecha:</strong> 12 Nov 2024</p>
                <div class="d-flex gap-2">
                <a href="#" class="btn-modern-outline flex-fill">Ver detalles</a>
                <a href="#" class="btn-modern flex-fill">Contratar de nuevo</a>
                </div>
            </div>
            </div>

            <div class="service-item">
            <div class="service-content">
                <span class="badge bg-success mb-2">Completado</span>
                <h3>Electricidad</h3>
                <p><strong>Proveedor:</strong> Luis Martínez</p>
                <p><strong>Fecha:</strong> 5 Oct 2024</p>
                <div class="d-flex gap-2">
                <a href="#" class="btn-modern-outline flex-fill">Ver detalles</a>
                <a href="#" class="btn-modern flex-fill">Contratar de nuevo</a>
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