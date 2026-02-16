<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Registrar Categoría</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- tu css (asumo que puedes reutilizar el de registro de usuario o crear uno específico) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/registrarUsuario.css">
</head>

<body>
    <!-- SIDEBAR (lateral izquierdo) -->
    <?php
    // Usamos el sidebar que ya actualizamos con el submenú
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
                    <h1 class="mb-1">Registrar Categoría</h1>
                    <p class="text-muted mb-0">
                        Registra nuevas categorías de servicios para la plataforma.
                    </p>
                </div>

                <div class="col-md-4">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Panel Principal</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Registrar Categoría</li>
                        </ol>
                    </nav>
                </div>

            </div>
        </section>

        <!-- Formulario de Categoría -->
        <section id="formulario-categorias">
            <div class="contenedor-formulario">
                <!-- El action apunta al controlador que creamos antes -->
                <form action="<?= BASE_URL ?>/admin/guardar-categoria" method="post" class="formulario-usuario" enctype="multipart/form-data">
                    <div class="row g-3">

                        <!-- Ícono/Imagen de Categoría (icono_url) -->
                        <div class="seccion-foto">
                            <div class="tarjeta-foto">
                                <!-- Reemplazamos la imagen de perfil por un placeholder de ícono -->
                                <div class="foto-perfil">
                                    <!-- Placeholder para el ícono de la categoría. Asumimos un ícono por defecto. -->
                                    <img src="<?= BASE_URL ?>/public/uploads/categorias/default_icon.png" alt="Icono de Categoría" id="foto-preview" width="50">
                                </div>
                                <label for="icono-input" class="btn-agregar-foto">Subir Ícono</label>
                                <!-- El name debe coincidir con cómo lo esperas en el controlador, ej: 'icono_url' -->
                                <input type="file" id="icono-input" accept=".jpg, .png, .jpeg, .svg" style="display: none;" name="icono_url">
                            </div>
                        </div>

                        <!-- Nombre de la Categoría (nombre) -->
                        <div class="col-md-12">
                            <label for="nombre" class="form-label">Nombre de la Categoría</label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                placeholder="Ej: Plomería, Electricidad" required>
                        </div>

                        <!-- Descripción (descripcion) -->
                        <div class="col-md-12">
                            <label for="descripcion" class="form-label">Descripción Detallada</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="4"
                                placeholder="Describe brevemente qué tipo de servicios abarca esta categoría (Ej: Reparación, instalación y mantenimiento de sistemas de agua potable y drenaje)." required></textarea>
                        </div>

                        <!-- Botón -->
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success px-4">Guardar Categoría</button>
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

    <!-- tu javaScript para manejo de formularios y dashboard -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/categoria.js"></script>

</body>

</html>