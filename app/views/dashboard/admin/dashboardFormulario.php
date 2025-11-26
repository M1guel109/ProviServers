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
    <link rel="stylesheet" href=" <?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- tu css -->
    <link rel="stylesheet" href=" <?= BASE_URL ?>/public/assets/dashBoard/css/dashboardFormulario.css">
</head>

<body>
    <!-- SIDEBAR (lateral izquierdo) -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_administrador.php'
    ?>




    <main class="contenido">
        <!-- HEADER -->
        <?php
        include_once __DIR__ . '/../../layouts/header_administrador.php'
        ?>



        <!--     Secciones -->
        <!-- titulo -->
        <section id="titulo-principal">
            <h1>Agregar Administradores</h1>
        </section>

        <!-- Formualior admin -->
        <section id="formulario">
            <!-- foto admin -->
            <div class="contenedor-formulario">
                <!-- Sección Foto de Perfil -->
                <div class="seccion-foto">
                    <div class="tarjeta-foto">
                        <div class="foto-perfil">
                            <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/foto-nuevo-admin.png" alt="Foto de perfil"
                                id="foto-preview">
                        </div>
                        <label for="foto-input" class="btn-agregar-foto">
                            Agregar foto
                        </label>
                        <input type="file" name="foto" id="foto-input" accept="image/*" style="display: none;">
                    </div>
                </div>
            </div>

            <!-- Formulario -->
            <form action="#" method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Nombres</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej:Kevin" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apellidos</label>
                        <input type="text" name="apellido" class="form-control" placeholder="Ej:Fleming" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="correo" class="form-control" placeholder="Ej:jaskolski.brent@yahoo.com" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Número de teléfono</label>
                        <input type="text" name="telefono" class="form-control" placeholder="Ej:546-933-2772" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Rol o nivel de acceso</label>
                        <select name="rol" class="form-select">
                            <option value="">Seleccionar...</option>
                            <option>Administrador</option>
                            <option>Editor</option>
                            <option>Soporte</option>
                        </select>

                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Género</label>
                        <select name="genero" class="form-select">
                            <option value="">Seleccionar...</option>
                            <option>Masculino</option>
                            <option>Femenino</option>
                            <option>Otro</option>
                        </select>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </div>
            </form>

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
</body>

</html>