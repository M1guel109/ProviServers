<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
require_once BASE_PATH . '/app/controllers/CategoriaController.php';


$datos = mostrarCategorias();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Gestión de Categorías</title>

    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.dataTables.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardTable.css">
</head>

<body>
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_administrador.php';
    ?>


    <main class="contenido">
        <?php
        include_once __DIR__ . '/../../layouts/header_administrador.php';
        ?>

        <section id="titulo-principal" class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h1 class="mb-1">Gestión de Categorías</h1>
                <p class="text-muted mb-0">
                    Lista de todas las categorías de servicios registradas en la plataforma.
                </p>
            </div>

            <div class="d-flex gap-2 align-items-center mt-3">
                <a href="<?= BASE_URL ?>/admin/registrar-categoria" class="btn btn-success">
                    <i class="bi bi-plus-circle-fill"></i> Registrar Nueva
                </a>
                <a href="<?= BASE_URL ?>/admin/reporte?tipo=categorias" target="_blank" class="btn btn-primary">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Reporte PDF
                </a>
            </div>

            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol id="breadcrumb" class="breadcrumb mb-0"></ol>
            </nav>
        </section>


        <section id="tabla-arriba">
            <table id="tabla-1" class="display nowrap">
                <thead>
                    <tr>
                        <th>Ícono</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-servicios">
                    <?php if (!empty($datos)) : ?>

                        <?php foreach ($datos as $categoria) : ?>

                            <tr>
                                <td>
                                    <img src="<?= BASE_URL ?>/public/uploads/categorias/<?= $categoria['icono_url'] ?>"
                                        alt="Ícono de Categoría"
                                        width="40" height="40"
                                        style="border-radius: 50%; object-fit: cover;">
                                </td>

                                <td><?= $categoria['nombre'] ?></td>

                                <td><?= substr($categoria['descripcion'], 0, 80) . (strlen($categoria['descripcion']) > 80 ? '...' : '') ?></td>


                                <td>
                                    <div class="action-buttons">

                                        <!-- <a href="#" class="btn-action btn-view" title="Ver detalle">
                                            <i class="bi bi-eye"></i>
                                        </a> -->

                                        <a href="<?= BASE_URL ?>/admin/editar-categoria?id=<?= $categoria['id'] ?>" class="btn-action btn-edit" title="Editar categoría">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <a href="<?= BASE_URL ?>/admin/eliminar-categoria?accion=eliminar&id=<?= $categoria['id'] ?>" class="btn-action btn-delete" title="Eliminar categoría">
                                            <i class="bi bi-trash3"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">
                                <h2>No hay categorías registradas aún.</h2>
                                <p class="text-muted">Utiliza el botón "Registrar Nueva" para empezar.</p>
                            </td>
                        </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </section>

    </main>


    <footer>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.print.min.js"></script>


    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/app.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>

</body>

</html>