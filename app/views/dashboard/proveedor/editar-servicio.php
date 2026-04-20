<?php
require_once BASE_PATH . '/app/helpers/session-proveedor.php';
// ✅ CORREGIDO: kebab-case correcto
require_once BASE_PATH . '/app/controllers/proveedor-controller.php';

// Validar ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . '/proveedor/listar-servicio');
    exit;
}

$id       = (int)$_GET['id'];
$servicio = mostrarServicioId($id);

if (!$servicio) {
    header('Location: ' . BASE_URL . '/proveedor/listar-servicio');
    exit;
}

// ✅ CORREGIDO: categorías vienen del controlador, no del modelo directo
$categorias = obtenerCategorias();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Editar Servicio</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/registrar-servicio.css">
</head>
<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar-proveedor.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-proveedor.php'; ?>

        <section id="titulo-principal"
                 class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h1 class="mb-1">Actualizar Servicio</h1>
                <p class="text-muted mb-0">
                    Modifica la información de tu servicio publicado en la plataforma.
                </p>
            </div>
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 mt-2">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/proveedor/dashboard">Panel</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/proveedor/listar-servicio">Mis Servicios</a>
                    </li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </nav>
        </section>

        <section id="formulario-servicio">
            <div class="contenedor-formulario">
                <!-- ✅ CORREGIDO: enctype para subir imagen -->
                <form action="<?= BASE_URL ?>/proveedor/guardar-servicio"
                      method="POST"
                      id="form-servicio"
                      enctype="multipart/form-data">

                    <!-- Campos ocultos -->
                    <input type="hidden" name="id"     value="<?= (int)$servicio['id'] ?>">
                    <input type="hidden" name="accion" value="actualizar">
                    <!-- ✅ CORREGIDO: imagen_actual para no perder la imagen si no se cambia -->
                    <input type="hidden" name="imagen_actual"
                           value="<?= htmlspecialchars($servicio['imagen'] ?? 'default_service.png') ?>">

                    <div class="row g-3">

                        <!-- IMAGEN ACTUAL -->
                        <div class="col-12 d-flex justify-content-center">
                            <div class="seccion-foto">
                                <div class="tarjeta-foto">
                                    <div class="foto-servicio">
                                        <img src="<?= BASE_URL ?>/public/uploads/servicios/<?= htmlspecialchars($servicio['imagen'] ?? 'default_service.png') ?>"
                                             alt="Imagen del servicio"
                                             id="foto-preview">
                                    </div>
                                    <label for="foto-input" class="btn-agregar-foto">
                                        <i class="bi bi-camera"></i> Cambiar imagen
                                    </label>
                                    <input type="file" id="foto-input" name="imagen"
                                           accept="image/*" style="display:none;">
                                </div>
                                <div class="form-text text-center mt-2">
                                    Imagen actual. Deja vacío para mantenerla.
                                </div>
                            </div>
                        </div>

                        <!-- NOMBRE -->
                        <div class="col-md-12">
                            <label for="nombre" class="form-label">
                                Nombre del servicio <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                   required maxlength="100"
                                   value="<?= htmlspecialchars($servicio['nombre'] ?? '') ?>">
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
                                    <option value="<?= (int)$categoria['id'] ?>"
                                        <?= $categoria['id'] == $servicio['id_categoria'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($categoria['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- ✅ CORREGIDO: campo precio que faltaba -->
                        <div class="col-md-6">
                            <label for="precio" class="form-label">
                                Precio (COP) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="precio" name="precio"
                                       min="0" step="0.01" required
                                       value="<?= htmlspecialchars($servicio['precio'] ?? '0') ?>">
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
                                           name="disponibilidad" id="disponible" value="1"
                                           <?= (int)($servicio['disponibilidad'] ?? 1) === 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="disponible">
                                        <i class="bi bi-check-circle-fill text-success"></i> Disponible
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio"
                                           name="disponibilidad" id="no-disponible" value="0"
                                           <?= (int)($servicio['disponibilidad'] ?? 1) === 0 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="no-disponible">
                                        <i class="bi bi-x-circle-fill text-danger"></i> No disponible
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- DESCRIPCIÓN -->
                        <div class="col-md-12">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion"
                                      rows="4" maxlength="500"
                                      placeholder="Describe detalladamente tu servicio..."><?= htmlspecialchars($servicio['descripcion'] ?? '') ?></textarea>
                            <div class="form-text">
                                Máximo 500 caracteres.
                                <span id="contador-descripcion">0/500</span>
                            </div>
                        </div>

                        <!-- BOTONES -->
                        <div class="col-12 mt-4">
                            <div class="d-flex gap-2 justify-content-center justify-content-md-end">
                                <a href="<?= BASE_URL ?>/proveedor/listar-servicio"
                                   class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Volver
                                </a>
                                <button type="submit" class="btn btn-primary px-4" id="btn-guardar">
                                    <i class="bi bi-check-circle"></i> Actualizar Servicio
                                </button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </section>
    </main>

    <footer></footer>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>const BASE_URL = "<?= BASE_URL ?>";</script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
    <!-- Reutilizamos el JS de registrar — tiene preview de imagen y contador -->
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/registrar-servicio.js"></script>

</body>
</html>