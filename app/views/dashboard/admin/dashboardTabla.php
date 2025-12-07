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
                                                <a href="<?= BASE_URL ?>/admin/revisar-servicio?id=<?= $servicio['id_servicio'] ?>" class="btn-action btn-view" title="Revisar detalles">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                <!-- Botón de Aprobar -->
                                                <button type="button" class="btn-action btn-approve"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmApproveModal"
                                                    data-id="<?= htmlspecialchars($servicio['id_servicio']) ?>"
                                                    title="Aprobar Servicio">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>

                                                <!-- Botón de Rechazar -->
                                                <button type="button" class="btn-action btn-reject"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmRejectModal"
                                                    data-id="<?= htmlspecialchars($servicio['id_servicio']) ?>"
                                                    title="Rechazar Servicio">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
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
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/app.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
</body>

</html>