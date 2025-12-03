<?php
// Requerir la sesión de administrador
require_once BASE_PATH . '/app/helpers/session_admin.php';

// Enlazamos el controlador de Membresías
require_once BASE_PATH . '/app/controllers/membresiaController.php';

// Llamamos la función específica que consulta los datos de las membresías
// NOTA: Asumimos que la función mostrarMembresias) es la que llama al modelo.
$datos = mostrarMembresias();

// Función de ayuda para convertir 1/0 a Sí/No para mejor legibilidad en la exportación.
function toYesNo($value) {
    return ($value == 1) ? 'Sí' : 'No';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Membresías Activas</title>

    <!-- Css DataTables Export -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.dataTables.css">


    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- tu css -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardTable.css">

    <!-- Iconos de Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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

        <!--     Secciones -->
        <section id="titulo-principal">
            <div class="row align-items-start">

                <div class="col-md-8 d-flex flex-column">

                    <div>
                        <h1 class="mb-1">Gestión de Membresías</h1>
                        <p class="text-muted mb-0">
                            Listado de todas las membresías activas e inactivas de los clientes.
                        </p>
                    </div>

                    <a href="<?= BASE_URL ?>/admin/reporte?tipo=membresias" target="_blank" class="btn btn-primary mt-3 w-auto" style="width: fit-content;">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Generar Reporte PDF
                    </a>

                </div>

                <div class="col-md-4 d-flex justify-content-end align-items-start">

                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0 mt-2">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Membresías</li>
                        </ol>
                    </nav>

                </div>

            </div>
        </section>


        <!-- Tabla de Datos -->
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
                                <!-- ENCABEZADOS CORREGIDOS PARA MOSTRAR DATOS REALES DE LA MEMBRESÍA -->
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Costo</th>
                                <th>Duración (días)</th>
                                <th>Estado</th>
                                <th>Destacado</th>
                                <th>Máx. Servicios</th>
                                <th>Estadísticas Pro</th>
                                <th>Videos</th>
                                <!-- <th>Creado</th> -->
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-membresias">
                            <?php if (!empty($datos)) : ?>

                                <?php foreach ($datos as $membresia) : ?>

                                    <tr>
                                        <!-- DATOS CORREGIDOS A LAS CLAVES DEVUELTAS POR EL MODELO -->

                                        <td><?= $membresia['tipo'] ?></td>
                                        <td><?= $membresia['descripcion'] ?></td>
                                        <td><?= $membresia['costo'] ?></td>
                                        <td><?= $membresia['duracion_dias'] ?></td>
                                        <td><?= $membresia['estado'] ?></td>
                                        <td><?= toYesNo($membresia['es_destacado']) ?></td>
                                        <td><?= $membresia['max_servicios_activos'] ?></td>
                                        <td><?= toYesNo($membresia['acceso_estadisticas_pro']) ?></td>
                                        <td><?= toYesNo($membresia['permite_videos']) ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <!-- Ver detalle -->
                                                <a href="#" class="btn-action btn-view" title="Ver detalle de la membresía">
                                                    <i class="bi bi-eye"></i>
                                                </a>

                                                <!-- Editar membresía (Podrías querer editar las fechas o el estado) -->
                                                <a href="<?= BASE_URL ?>/admin/editar-membresia?id=<?= $membresia['id'] ?>" class="btn-action btn-edit" title="Editar membresía">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <!-- Eliminar membresía -->
                                                <a href="<?= BASE_URL ?>/admin/eliminar-membresia?accion=eliminar&id=<?= $membresia['id'] ?>" class="btn-action btn-delete" title="Eliminar membresía">
                                                    <i class="bi bi-trash3"></i>
                                                </a>
                                            </div>

                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <h2>No hay membresías registradas o activas</h2>
                                    </td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>

                </div>

                <div class="tab-pane fade" id="acciones-pane" role="tabpanel" aria-labelledby="acciones-tab">
                    <div id="botones-exportacion-container" class="p-3">
                        <p class="text-muted">Use las opciones a continuación para copiar, imprimir o exportar los datos de la tabla.</p>

                        <!-- Tabla para Exportación (corregida para tener los mismos campos que la tabla principal) -->
                        <table id="tabla-1" class="display nowrap">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Costo</th>
                                    <th>Duración (días)</th>
                                    <th>Estado</th>
                                    <th>Destacado</th>
                                    <th>Máx. Servicios</th>
                                    <th>Estadísticas Pro</th>
                                    <th>Videos</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-servicios-export">
                                <?php if (!empty($datos)) : ?>
                                    <?php foreach ($datos as $membresia) : ?>
                                        <tr>
                                            <!-- DATOS DE PLANES DE MEMBRESÍA PARA EXPORTAR -->
                                            <td><?= $membresia['tipo'] ?></td>
                                            <td><?= $membresia['descripcion'] ?></td>
                                            <td><?= $membresia['costo']?></td>
                                            <td><?= $membresia['duracion_dias'] ?></td>
                                            <td><?= $membresia['estado'] ?></td>
                                            <!-- Conversión de booleano a texto para mejor legibilidad en el reporte -->
                                            <td><?= toYesNo($membresia['es_destacado']) ?></td>
                                            <td><?= $membresia['max_servicios_activos'] ?></td>
                                            <td><?= toYesNo($membresia['acceso_estadisticas_pro']) ?></td>
                                            <td><?= toYesNo($membresia['permite_videos']) ?></td>
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
        xintegrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- tu javaScript -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/app.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
</body>

</html>