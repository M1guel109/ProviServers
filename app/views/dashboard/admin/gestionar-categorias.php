<?php
require_once BASE_PATH . '/app/helpers/session-admin.php';
require_once BASE_PATH . '/app/controllers/admin-controller.php';

$datos = mostrarCategorias();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Gestión de Categorías</title>

    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.dataTables.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/estilos-tablas.css">
</head>
<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar-administrador.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-administrador.php'; ?>

        <section id="titulo-principal">
            <div class="row">
                <div class="col-md-8">
                    <h1 class="mb-1">Gestión de Categorías</h1>
                    <p class="text-muted mb-0">
                        Lista de todas las categorías de servicios registradas en la plataforma.
                    </p>
                </div>
                <div class="col-md-4">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/admin/dashboard">Panel Principal</a>
                            </li>
                            <li class="breadcrumb-item active">Gestión de Categorías</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <section id="tabla-arriba">

            <ul class="nav nav-tabs mb-3" id="tablaTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab"
                            data-bs-target="#tabla-pane" type="button">
                        <i class="bi bi-table"></i> Datos y Acciones
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab"
                            data-bs-target="#acciones-pane" type="button">
                        <i class="bi bi-box-arrow-in-right"></i> Exportar Datos
                    </button>
                </li>
            </ul>

            <div class="tab-content">

                <!-- TABLA PRINCIPAL -->
                <div class="tab-pane fade show active" id="tabla-pane">
                    <div class="table-container">
                        <table id="tabla" class="display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Ícono</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($datos)) : ?>
                                    <?php foreach ($datos as $categoria) : ?>
                                        <tr>
                                            <td>
                                                <img src="<?= BASE_URL ?>/public/uploads/categorias/<?= htmlspecialchars($categoria['icono_url'] ?? 'default_icon.png') ?>"
                                                     alt="Ícono"
                                                     width="40" height="40"
                                                     style="border-radius:50%; object-fit:cover;">
                                            </td>
                                            <!-- ✅ htmlspecialchars en todos los datos -->
                                            <td><?= htmlspecialchars($categoria['nombre']) ?></td>
                                            <td>
                                                <?= htmlspecialchars(
                                                    substr($categoria['descripcion'] ?? '', 0, 80) .
                                                    (strlen($categoria['descripcion'] ?? '') > 80 ? '...' : '')
                                                ) ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="<?= BASE_URL ?>/admin/editar-categoria?id=<?= (int)$categoria['id'] ?>"
                                                       class="btn-action btn-edit"
                                                       title="Editar categoría">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <!-- ✅ CORREGIDO: confirmación antes de eliminar -->
                                                    <button type="button"
                                                            class="btn-action btn-delete"
                                                            title="Eliminar categoría"
                                                            onclick="confirmarEliminacion('<?= BASE_URL ?>/admin/eliminar-categoria?accion=eliminar&id=<?= (int)$categoria['id'] ?>')">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TABLA EXPORTACIÓN -->
                <div class="tab-pane fade" id="acciones-pane">
                    <div class="p-3">
                        <div class="alert alert-light border">
                            <i class="bi bi-info-circle me-2"></i>
                            Tabla optimizada para exportar datos.
                        </div>
                        <table id="tabla-1" class="display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($datos)) : ?>
                                    <?php foreach ($datos as $categoria) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($categoria['nombre']) ?></td>
                                            <td><?= htmlspecialchars($categoria['descripcion'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <footer></footer>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.print.min.js"></script>
    <!-- ✅ SweetAlert primero -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>const BASE_URL = "<?= BASE_URL ?>";</script>
    <!-- ✅ Sin apexcharts ni dashboard.js -->
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/categoria.js"></script>

</body>
</html>