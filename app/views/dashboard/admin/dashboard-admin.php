<?php
require_once BASE_PATH . '/app/helpers/session-admin.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Plataforma de servicios locales</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- tu css -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard.css">
</head>

<body>
    <!-- SIDEBAR (lateral izquierdo) -->


    <!-- AQUI VA EL INCLUDE DEL MENU -->

    <?php
    include_once __DIR__ . '/../../layouts/sidebar-administrador.php'
    ?>


    <main class="contenido">

        <!-- AQUI VA EL INCLUDE DEL HEADER -->
        <?php
        include_once __DIR__ . '/../../layouts/header-administrador.php'
        ?>

        <!--     Secciones -->
        <!-- titulo -->
        <section id="titulo-principal">
            <div class="row">
                <div class="col-md-8">
                    <h1>Panel Principal</h1>
                    <p class="text-muted mb-0">
                        Bienvenido al panel principal de administración. Aquí puedes gestionar usuarios, proveedores, clientes y las operaciones clave de la plataforma Proviservers.
                    </p>
                </div>
                <div class="col-md-4">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0"></ol>
                    </nav>
                </div>
            </div>
        </section>

        <!-- BARRA DE CONTROL DEL DASHBOARD -->
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <div id="hidden-widgets-container" class="d-none">
                <span class="small text-muted me-2">Widgets ocultos:</span>
                <div id="hidden-widgets-panel" class="d-inline"></div>
            </div>
            <button id="btn-restaurar-dashboard" class="btn btn-sm btn-outline-secondary ms-auto">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Restaurar configuración
            </button>
        </div>

        <div id="dashboard-grid">

        <!-- WIDGET: GRÁFICA -->
        <div class="widget-card mb-4" data-widget-id="grafica" data-label="Gráfica principal">
            <div class="d-flex justify-content-end gap-2 mb-1">
                <span class="widget-handle text-muted" style="cursor:grab;" title="Arrastrar"><i class="bi bi-grip-vertical fs-5"></i></span>
                <button class="btn-ocultar-widget btn btn-sm btn-link text-muted p-0" data-id="grafica" title="Ocultar">✕</button>
            </div>
        <section id="grafica-principal">
            <h2>Total servicios publicados</h2>
            <select id="periodo">
                <option value="mensual">Mensual</option>
                <option value="semanal">Semanal</option>
                <option value="anual">Anual</option>
            </select>
            <div id="chart"></div>
        </section>

        </section></div><!-- /widget grafica -->

        <!-- WIDGET: TARJETAS INFERIORES -->
        <div class="widget-card mb-4" data-widget-id="usuarios" data-label="Métricas y reportes">
            <div class="d-flex justify-content-end gap-2 mb-1">
                <span class="widget-handle text-muted" style="cursor:grab;" title="Arrastrar"><i class="bi bi-grip-vertical fs-5"></i></span>
                <button class="btn-ocultar-widget btn btn-sm btn-link text-muted p-0" data-id="usuarios" title="Ocultar">✕</button>
            </div>
        <section id="tarjetas-inferiores">
            <!-- tarjeta usuarios -->
            <div class="tarjeta">
                <h2>Usuarios</h2>
                <div id="chart-usuarios"></div>
                <div class="metricas">
                    <span class="valor">34,249</span>
                    <span class="valor">1,420</span>
                </div>
            </div>

            <!-- tarjeta servicio destacaddo -->
            <div class="tarjeta">
                <h3>Servicio Destacado</h3>
                <div class="servicio-imagen">
                    <img src="<?= BASE_URL ?>/public/assets/dashboard/img/imagen-servicio.png" alt="Foto Servicio">
                </div>
                <div class="servicio-nombre">Plomería</div>
            </div>

            <!-- tarjeta metricas -->
            <div class="tarjeta">
                <h2>Métricas de Servicios</h2>
                <div id="chart-nuevos-servicios"></div>
            </div>

        </section>


        </section></div><!-- /widget usuarios -->

        </div><!-- /#dashboard-grid -->
    </main>

    <footer>
        <!-- Enlaces / Información -->
    </footer>

    <!-- apexcharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- tu javaScript -->
    <script>const BASE_URL = "<?= BASE_URL ?>";</script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/dashboard-personalizable.js"></script>
</body>

</html>