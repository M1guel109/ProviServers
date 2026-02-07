<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
// enlazamos la dependencia,en este caso el controlador que tiene la funcion de consulatar los datos
require_once BASE_PATH . '/app/controllers/adminController.php';

// llamamos la funcion especifica que exite en dicho controlador
$datos = mostrarUsuarios();


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Plataforma de servicios locales</title>

    <!-- Css DataTables Export -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.dataTables.css">


    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- tu css -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardTable.css">
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

        <!--     Secciones -->
        <!-- titulo -->
        <!-- Desde la vista en el btn(hipervinculo) que creo para generar el reporte pdf dejo una ruta generica por rol   -->
        <!--  y le agrego la variable tipo para definir el reporte que quiero generar ejplo el el siguiente a   -->
        <!-- <a href="<?= BASE_URL ?>/admin/reporte?tipo=usuarios" target="_blank" class="btn btn-primary mt-3">
                <i class="bi bi-file-earmark-pdf-fill"></i> Generar Reporte PDF
            </a> -->
        <section id="titulo-principal">
            <div class="row align-items-start">

                <div class="col-md-8 d-flex flex-column">

                    <div>
                        <h1 class="mb-1">Usuarios</h1>
                        <p class="text-muted mb-0">
                            Aquí puedes ver todos los usuarios registrados. Usa las acciones disponibles para editar, eliminar o
                            revisar información de cada usuario.
                        </p>
                    </div>

                    <a href="<?= BASE_URL ?>/admin/reporte?tipo=usuarios" target="_blank" class="btn btn-primary mt-3 w-auto" style="width: fit-content;">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Generar Reporte PDF
                    </a>

                </div>

                <div class="col-md-4 d-flex justify-content-end align-items-start">

                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0 mt-2"></ol>
                    </nav>

                </div>

            </div>
        </section>


        <!-- Tabla arriba -->
        <section id="tabla-arriba">

            <ul class="nav nav-tabs mb-3" id="tablaTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tabla-tab" data-bs-toggle="tab" data-bs-target="#tabla-pane" type="button" role="tab" aria-controls="tabla-pane" aria-selected="true">
                        <i class="bi bi-table"></i> Datos y Acciones
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="acciones-tab" data-bs-toggle="tab" data-bs-target="#acciones-pane" type="button" role="tab" aria-controls="acciones-pane" aria-selected="false">
                        <i class="bi bi-box-arrow-in-right"></i> Opciones de Exportación
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="tablaTabsContent">

                <div class="tab-pane fade show active" id="tabla-pane" role="tabpanel" aria-labelledby="tabla-tab">

                    <table id="tabla" class="display nowrap">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nombre completo</th>
                                <th>Correo electrónico</th>
                                <th>Teléfono</th>
                                <th>Ubicación</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-servicios">
                            <?php if (!empty($datos)) : ?>

                                <?php foreach ($datos as $usuario) : ?>

                                    <tr>
                                        <td><img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $usuario['foto'] ?>" alt="Foto del usuario" width="50" height="50" style="border-radius: 50%;"></td>
                                        <td><?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?></td>
                                        <td><?= $usuario['email'] ?></td>
                                        <td><?= $usuario['telefono'] ?></td>
                                        <td><?= $usuario['ubicacion'] ?></td>
                                        <td><?= $usuario['rol'] ?></td>
                                        <td>
                                            <?php
                                            // Determina la clase de estilo basada en el valor de 'estado'
                                            $estado_clase = '';
                                            if ($usuario['estado'] === 'Activo') {
                                                $estado_clase = 'status-activo';
                                            } elseif ($usuario['estado'] === 'Inactivo') {
                                                $estado_clase = 'status-inactivo';
                                            }
                                            // Si quieres usar una clase preexistente para ejemplo:
                                            // $estado_clase = ($usuario['estado'] === 'Activo') ? 'status-completed' : 'status-pending';
                                            ?>
                                            <span class="status-badge <?= $estado_clase ?>">
                                                <?= $usuario['estado'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <!-- Uso de las clases personalizadas para los botones -->
                                            <div class="action-buttons">
                                                <!-- Botón para ver detalle del servicio (Revisar) -->
                                                <button type="button"
                                                    class="btn-action btn-view"
                                                    title="Revisar detalles"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalDetalleUsuario"
                                                    onclick="cargarDetalleUsuario(<?= $usuario['id'] ?>)">
                                                    <i class="bi bi-eye"></i>
                                                </button>

                                                <a href="<?= BASE_URL ?>/admin/editar-usuario?id=<?= $usuario['id'] ?>" class="btn-action btn-edit" title="Editar usuario">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <a href="<?= BASE_URL ?>/admin/eliminar-usuario?accion=eliminar&id=<?= $usuario['id'] ?>" class="btn-action btn-delete" title="Eliminar usuario">
                                                    <i class="bi bi-trash3"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td>
                                        <h2>No hay usuarios registrados</h2>
                                    </td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>

                </div>

                <div class="tab-pane fade" id="acciones-pane" role="tabpanel" aria-labelledby="acciones-tab">
                    <div id="botones-exportacion-container" class="p-3">
                        <p class="text-muted">Use las opciones a continuación para copiar, imprimir o exportar los datos de la tabla.</p>

                        <table id="tabla-1" class="display nowrap">
                            <thead>
                                <tr>
                                    <th>Nombre completo</th>
                                    <th>Correo electrónico</th>
                                    <th>Teléfono</th>
                                    <th>Ubicación</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-servicios">
                                <?php if (!empty($datos)) : ?>

                                    <?php foreach ($datos as $usuario) : ?>

                                        <tr>
                                            <td><?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?></td>
                                            <td><?= $usuario['email'] ?></td>
                                            <td><?= $usuario['telefono'] ?></td>
                                            <td><?= $usuario['ubicacion'] ?></td>
                                            <td><?= $usuario['rol'] ?></td>
                                            <td>
                                                <?php
                                                // Determina la clase de estilo basada en el valor de 'estado'
                                                $estado_clase = '';
                                                if ($usuario['estado'] === 'Activo') {
                                                    $estado_clase = 'status-activo';
                                                } elseif ($usuario['estado'] === 'Inactivo') {
                                                    $estado_clase = 'status-inactivo';
                                                }
                                                // Si quieres usar una clase preexistente para ejemplo:
                                                // $estado_clase = ($usuario['estado'] === 'Activo') ? 'status-completed' : 'status-pending';
                                                ?>
                                                <span class="status-badge <?= $estado_clase ?>">
                                                    <?= $usuario['estado'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td>
                                            <h2>No hay usuarios registrados</h2>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>

    </main>
    <div class="modal fade" id="modalDetalleUsuario" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalDetalleLabel">
                        <i class="bi bi-person-lines-fill me-2"></i>Detalle del Usuario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div id="loader-detalle" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>

                    <div id="contenido-detalle" class="d-none">

                        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                            <div class="position-relative">
                                <img id="modal-foto" src="" alt="Foto" class="rounded-circle border border-3 border-white shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                                <span id="modal-estado-badge" class="position-absolute bottom-0 start-100 translate-middle p-2 border border-light rounded-circle bg-success">
                                    <span class="visually-hidden">Estado</span>
                                </span>
                            </div>
                            <div class="ms-4">
                                <h3 id="modal-nombre" class="mb-0 fw-bold text-dark"></h3>
                                <p id="modal-rol" class="text-muted mb-0 text-uppercase small fw-bold"></p>
                                <small id="modal-id" class="text-muted fst-italic"></small>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded h-100">
                                    <h6 class="text-primary fw-bold mb-3"><i class="bi bi-envelope-at me-2"></i>Contacto</h6>
                                    <p class="mb-1"><strong>Email:</strong> <span id="modal-email"></span></p>
                                    <p class="mb-1"><strong>Teléfono:</strong> <span id="modal-telefono"></span></p>
                                    <p class="mb-0"><strong>Ubicación:</strong> <span id="modal-ubicacion"></span></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded h-100">
                                    <h6 class="text-primary fw-bold mb-3"><i class="bi bi-shield-lock me-2"></i>Cuenta</h6>
                                    <p class="mb-1"><strong>Documento:</strong> <span id="modal-documento"></span></p>
                                    <p class="mb-1"><strong>Estado:</strong> <span id="modal-estado-texto"></span></p>
                                    <p class="mb-0"><strong>Registrado:</strong> <span id="modal-fecha"></span></p>
                                </div>
                            </div>
                        </div>

                        <div id="seccion-proveedor" class="mt-4 d-none">
                            <h6 class="border-bottom pb-2 mb-3 fw-bold text-dark">Información de Proveedor</h6>

                            <div class="mb-3">
                                <p class="fw-bold mb-2 small text-uppercase text-muted">Categorías / Habilidades:</p>
                                <div id="modal-categorias" class="d-flex flex-wrap gap-2">
                                </div>
                            </div>

                            <div>
                                <p class="fw-bold mb-2 small text-uppercase text-muted">Documentos Adjuntos:</p>
                                <div id="modal-documentos" class="list-group list-group-flush border rounded">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>

            </div>
        </div>
    </div>



    <footer>
        <!-- Enlaces / Información -->
    </footer>

    <!-- Datatables export -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.print.min.js"></script>


    <!-- apexcharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- tu javaScript -->
     <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/detalleUsuario.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <!-- <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/app.js"></script> -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
</body>

</html>