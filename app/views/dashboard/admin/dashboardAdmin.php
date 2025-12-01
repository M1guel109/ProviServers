<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
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
    include_once __DIR__ . '/../../layouts/sidebar_administrador.php'
    ?>


    <main class="contenido">

        <!-- AQUI VA EL INCLUDE DEL HEADER -->
        <?php
        include_once __DIR__ . '/../../layouts/header_administrador.php'
        ?>

        <!--     Secciones -->
        <!-- titulo -->
        <section id="titulo-principal">

                <h1>Panel Principal</h1>
                <p class="text-muted mb-0">
                    Bienvenido al panel principal de administración. Aquí puedes gestionar usuarios, proveedores, clientes y las operaciones clave de la plataforma Proviservers.


            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol id="breadcrumb" class="breadcrumb mb-0"></ol>
            </nav>
        </section>

        <!-- Grafica Principal -->
        <section id="grafica-principal">
            <h2>Total servicios publicados</h2>
            <select id="periodo">
                <option value="mensual">Mensual</option>
                <option value="semanal">Semanal</option>
                <option value="anual">Anual</option>
            </select>
            <div id="chart"></div>
        </section>

        <!-- tarjetas inferiores -->
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
                    <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/imagen-servicio.png" alt="Foto Servicio">
                </div>
                <div class="servicio-nombre">Plomería</div>
            </div>

            <!-- tarjeta metricas -->
            <div class="tarjeta">
                <h2>Métricas de Servicios</h2>
                <div id="chart-nuevos-servicios"></div>
            </div>

        </section>


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
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
</body>

</html>