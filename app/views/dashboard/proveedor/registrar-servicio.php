<?php
require_once BASE_PATH . '/app/helpers/session-proveedor.php';
// ✅ CORREGIDO: categorías vienen del controlador, no del modelo directo
require_once BASE_PATH . '/app/controllers/proveedor-controller.php';
$categorias = obtenerCategorias();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Registrar Servicio</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/registrar-servicio.css">
</head>
<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar-proveedor.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-proveedor.php'; ?>

        <section id="titulo-principal" class="section-hero mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-1">Registrar Servicio</h1>
                    <p class="text-muted mb-0">Completa todos los campos para publicar tu servicio en la plataforma.</p>
                </div>
                <div class="col-md-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/proveedor/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Registrar Servicio</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <section id="formulario-servicio">
            <div class="contenedor-formulario">
                <form action="<?= BASE_URL ?>/proveedor/guardar-servicio"
                      method="POST"
                      id="form-servicio"
                      enctype="multipart/form-data">

                    <div class="row g-3">

                        <!-- IMAGEN -->
                        <div class="col-12 d-flex justify-content-center">
                            <div class="seccion-foto">
                                <div class="tarjeta-foto">
                                    <div class="foto-servicio">
                                        <img src="<?= BASE_URL ?>/public/assets/dashboard/img/imagen-servicio.png"
                                             alt="Imagen del servicio"
                                             id="foto-preview">
                                    </div>
                                    <label for="foto-input" class="btn-agregar-foto">
                                        <i class="bi bi-camera"></i> Agregar imagen
                                    </label>
                                    <input type="file" id="foto-input" name="imagen"
                                           accept="image/*" style="display:none;">
                                </div>
                                <div class="form-text text-center mt-2">
                                    Sube una imagen representativa (PNG, JPG, máx 2MB).
                                </div>
                            </div>
                        </div>

                        <!-- NOMBRE -->
                        <div class="col-md-12">
                            <label for="nombre" class="form-label">
                                Nombre del servicio <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                   placeholder="Ej: Reparación de tuberías residenciales"
                                   required maxlength="100">
                            <div class="form-text">Máximo 100 caracteres.</div>
                        </div>

                        <!-- CATEGORÍA -->
                        <div class="col-md-6">
                            <label for="id_categoria" class="form-label">
                                Categoría <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="id_categoria" name="id_categoria" required>
                                <option value="">Seleccionar categoría...</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= (int)$categoria['id'] ?>">
                                        <?= htmlspecialchars($categoria['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- PRECIO -->
                        <div class="col-md-6">
                            <label for="precio" class="form-label">
                                Precio (COP) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="precio" name="precio"
                                       placeholder="Ej: 50000" min="0" step="0.01" required>
                            </div>
                        </div>

                        <!-- DISPONIBILIDAD -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Disponibilidad <span class="text-danger">*</span>
                            </label>
                            <div class="pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio"
                                           name="disponibilidad" id="disponible" value="1" checked>
                                    <label class="form-check-label" for="disponible">
                                        <i class="bi bi-check-circle text-success"></i> Disponible
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio"
                                           name="disponibilidad" id="no-disponible" value="0">
                                    <label class="form-check-label" for="no-disponible">
                                        <i class="bi bi-x-circle text-danger"></i> No disponible
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- DESCRIPCIÓN -->
                        <div class="col-md-12">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion"
                                      rows="4"
                                      placeholder="Describe detalladamente tu servicio, materiales, tiempo estimado, etc."
                                      maxlength="500"></textarea>
                            <div class="form-text">
                                Máximo 500 caracteres.
                                <span id="contador-descripcion">0/500</span>
                            </div>
                        </div>

                        <!-- SUBMIT -->
                        <div class="col-12 mt-4">
                            <div class="d-flex gap-2 justify-content-center justify-content-md-end">
                                <a href="<?= BASE_URL ?>/proveedor/listar-servicio"
                                   class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Volver
                                </a>
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

    <footer></footer>

    <!-- ✅ SweetAlert primero -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>const BASE_URL = "<?= BASE_URL ?>";</script>
    <!-- ✅ Sin dashboard.js -->
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/registrar-servicio.js"></script>

</body>
</html>