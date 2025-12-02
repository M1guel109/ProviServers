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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/registrarUsuario.css">
</head>

<body>
    <!-- SIDEBAR (lateral izquierdo) -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_administrador.php';
    ?>


    <main class="contenido">
        <?php
        include_once __DIR__ . '/../../layouts/header_administrador.php';
        ?>


        <!-- Secciones -->
        <!-- titulo -->
        <section id="titulo-principal">
            <div class="row">

                <div class="col-md-8">
                    <h1 class="mb-1">Registrar Usuario</h1>
                    <p class="text-muted mb-0">
                        Registra nuevos servicios para que los usuarios puedan contratarlos dentro de la plataforma.
                    </p>
                </div>

                <div class="col-md-4">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0"></ol>
                    </nav>
                </div>

            </div>
        </section>

        <!-- Formualior admin -->
        <section id="formulario-usuarios">
            <div class="contenedor-formulario">
                <!-- Formulario -->
                <form action="<?= BASE_URL ?>/admin/guardar-usuario" method="post" class="formulario-usuario" enctype="multipart/form-data">
                    <div class="row g-3">
                        <!-- Foto de perfil -->
                        <div class="seccion-foto">
                            <div class="tarjeta-foto">
                                <div class="foto-perfil">
                                    <img src="<?= BASE_URL ?>/public/uploads/usuarios/default_user.png" alt="Foto de perfil" id="foto-preview">
                                </div>
                                <label for="foto-input" class="btn-agregar-foto">Agregar foto</label>
                                <input type="file" id="foto-input" accept=".jpg, .png, .jpeg" style="display: none;" name="foto">
                            </div>
                        </div>

                        <!-- Nombre -->
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="nombre" name="nombres"
                                placeholder="Ej:Luis Miguel" required>
                        </div>

                        <!-- Apellidos -->
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="nombre" name="apellidos"
                                placeholder="Ej:Lozano Pérez" required>
                        </div>

                        <!-- Documento -->
                        <div class="col-md-6">
                            <label for="documento" class="form-label">Número de Documento</label>
                            <input type="text" class="form-control" id="documento" name="documento"
                                placeholder="Ej: 1012345678" required>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Ej: juan@gmail.com" required>
                        </div>

                        <!-- Contraseña -->
                        <div class="col-md-6">
                            <label for="clave" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="clave" name="clave" placeholder="Dejar vacío para usar el Documento como clave temporal">
                        </div>

                        <!-- Teléfono -->
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono"
                                placeholder="Ej: 3204567890" required>
                        </div>

                        <!-- Ubicación -->
                        <div class="col-md-6">
                            <label for="ubicacion" class="form-label">Ubicación</label>
                            <input type="text" class="form-control" id="ubicacion" name="ubicacion"
                                placeholder="Ej: Fusagasugá, Cundinamarca" required>
                        </div>

                        <!-- Rol -->
                        <div class="col-md-6">
                            <label for="rol" class="form-label">Rol</label>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="">Seleccionar...</option>
                                <option value="admin">Administrador</option>
                                <option value="proveedor">Proveedor</option>
                                <option value="cliente">Cliente</option>
                            </select>
                        </div>

                        <!-- Botón -->
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary px-4">Agregar Usuario</button>
                        </div>

                    </div>
                </form>
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