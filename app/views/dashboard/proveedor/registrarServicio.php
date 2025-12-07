<?php
require_once BASE_PATH . '/app/helpers/session_proveedor.php';

// Cargar modelos
require_once BASE_PATH . '/app/models/servicio.php';
require_once BASE_PATH . '/app/models/categoria.php';

$categoriaModel = new Categoria();
$categorias = $categoriaModel->mostrar();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Registrar Servicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/registrar-servicio.css">
</head>

<body>
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_proveedor.php';
    ?>

    <main class="contenido">

        <?php
        include_once __DIR__ . '/../../layouts/header_proveedor.php';
        ?>


        <section id="titulo-principal" class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h1 class="mb-1">Registrar Servicio</h1>
                <p class="text-muted mb-0">
                    Registra tu nuevo servicio para que sea visible en la plataforma bueno.
                </p>
            </div>

            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol id="breadcrumb" class="breadcrumb mb-0"></ol>
            </nav>
        </section>

        <section id="formulario-servicio">
            <div class="contenedor-formulario">
                <form action="<?= BASE_URL ?>/proveedor/guardar-servicio" method="post" id="form-servicio" enctype="multipart/form-data">
                    <div class="row g-3">

                        <div class="col-12 d-flex justify-content-center">
                            <div class="seccion-foto">
                                <div class="tarjeta-foto">
                                    <div class="foto-servicio">
                                        <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/imagen-servicio.png" alt="Imagen del servicio"
                                            id="foto-preview">
                                    </div>
                                    <label for="foto-input" class="btn-agregar-foto">
                                        <i class="bi bi-camera"></i> Agregar imagen
                                    </label>
                                    <input type="file" id="foto-input" name="imagen" accept="image/*" style="display: none;">
                                </div>
                                <div class="form-text text-center mt-2">Sube una imagen representativa del servicio.</div>
                            </div>
                        </div>

                        <input type="hidden" id="servicio_id" name="servicio_id">

                        <div class="col-md-12">
                            <label for="nombre" class="form-label">Nombre del servicio *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                placeholder="Ej: Reparación de tuberías residenciales" required maxlength="100">
                            <div class="form-text">Máximo 100 caracteres</div>
                        </div>

                        <div class="col-md-6">
                            <label for="id_categoria" class="form-label">Categoría *</label>
                            <select class="form-select" id="id_categoria" name="id_categoria" required>
                                <option value="">Seleccionar categoría...</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= htmlspecialchars($categoria['id']) ?>">
                                        <?= htmlspecialchars($categoria['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Disponibilidad *</label>
                            <div class="disponibilidad-opciones pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="disponibilidad" id="disponible" value="1" checked>
                                    <label class="form-check-label" for="disponible">
                                        <i> Disponible
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="disponibilidad" id="no-disponible" value="0">
                                    <label class="form-check-label" for="no-disponible">
                                        <i> No disponible
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="4"
                                placeholder="Describe detalladamente en qué consiste tu servicio, materiales que utilizas, tiempo estimado, etc." maxlength="500"></textarea>
                            <div class="form-text">
                                Máximo 500 caracteres. <span id="contador-descripcion">0/500</span>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <div class="d-flex gap-2 justify-content-center justify-content-md-end">
                                <button type="button" class="btn btn-secondary" id="btn-cancelar">
                                    <i class="bi bi-arrow-left"></i> Volver
                                </button>
                                <button type="submit" class="btn btn-primary px-4" id="btn-guardar">
                                    <i class="bi bi-check-circle"></i> Guardar Servicio
                                </button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/registrar-servicio.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
</body>

</html>
