<?php
require_once BASE_PATH . '/app/helpers/session-admin.php';
require_once BASE_PATH . '/app/models/categoria.php';

// Validar ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . '/admin/consultar-categorias');
    exit;
}

$id           = (int) $_GET['id'];
$objCategoria = new Categoria();
$categoria    = $objCategoria->mostrarId($id);

if (!$categoria) {
    header('Location: ' . BASE_URL . '/admin/consultar-categorias');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Editar Categoría</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/registrar-usuario.css">
</head>
<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar-administrador.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-administrador.php'; ?>

        <section id="titulo-principal">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-1">Editar Categoría</h1>
                    <p class="text-muted mb-0">Modifica los datos y el ícono de la categoría.</p>
                </div>
                <div class="col-md-4">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/admin/dashboard">Panel Principal</a>
                            </li>
                            <!-- ✅ CORREGIDO: ruta correcta -->
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/admin/consultar-categorias">Categorías</a>
                            </li>
                            <li class="breadcrumb-item active">Editar</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <section id="formulario-categorias">
            <div class="contenedor-formulario">
                <form action="<?= BASE_URL ?>/admin/actualizar-categoria"
                      method="POST"
                      class="formulario-usuario"
                      enctype="multipart/form-data">

                    <!-- Campos ocultos -->
                    <input type="hidden" name="accion"          value="actualizar">
                    <input type="hidden" name="id"              value="<?= (int)$categoria['id'] ?>">
                    <input type="hidden" name="icono_url_actual" value="<?= htmlspecialchars($categoria['icono_url'] ?? 'default_icon.png') ?>">

                    <div class="row g-3">

                        <!-- ÍCONO ACTUAL -->
                        <div class="seccion-foto">
                            <div class="tarjeta-foto">
                                <div class="foto-perfil">
                                    <img src="<?= BASE_URL ?>/public/uploads/categorias/<?= htmlspecialchars($categoria['icono_url'] ?? 'default_icon.png') ?>"
                                         alt="Ícono actual"
                                         id="foto-preview">
                                </div>
                                <label for="icono-input" class="btn-agregar-foto">
                                    <i class="bi bi-image"></i> Cambiar Ícono
                                </label>
                                <input type="file" id="icono-input" name="icono_url"
                                       accept=".jpg,.png,.jpeg,.svg,.webp"
                                       style="display:none;">
                            </div>
                        </div>

                        <!-- NOMBRE -->
                        <div class="col-md-12">
                            <label for="nombre" class="form-label">
                                Nombre de la Categoría <span class="text-danger">*</span>
                            </label>
                            <!-- ✅ htmlspecialchars en value -->
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                   required
                                   value="<?= htmlspecialchars($categoria['nombre'] ?? '') ?>">
                        </div>

                        <!-- DESCRIPCIÓN -->
                        <div class="col-md-12">
                            <label for="descripcion" class="form-label">
                                Descripción <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="descripcion" name="descripcion"
                                      rows="4" required><?= htmlspecialchars($categoria['descripcion'] ?? '') ?></textarea>
                        </div>

                        <!-- SUBMIT -->
                        <div class="text-center mt-4">
                            <a href="<?= BASE_URL ?>/admin/consultar-categorias"
                               class="btn btn-outline-secondary me-2">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary px-5">
                                Actualizar Categoría
                            </button>
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
    <!-- ✅ Sin apexcharts ni dashboard.js -->
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/categoria.js"></script>

</body>
</html>