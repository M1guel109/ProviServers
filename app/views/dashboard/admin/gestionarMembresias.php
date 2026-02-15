<?php
// Requerir la sesión de administrador
require_once BASE_PATH . '/app/helpers/session_admin.php';

// Enlazamos el controlador de Membresías
require_once BASE_PATH . '/app/controllers/membresiaController.php';

// Llamamos la función específica que consulta los datos de las membresías
// NOTA: Asumimos que la función mostrarMembresias) es la que llama al modelo.
$datos = mostrarMembresias();

// Función de ayuda para convertir 1/0 a Sí/No para mejor legibilidad en la exportación.
function toYesNo($value)
{
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/estilosTablas.css">

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
                                        <td>$ <?= number_format($membresia['costo'], 0, ',', '.') ?></td>
                                        <td><?= $membresia['duracion_dias'] ?></td>
                                        <td>
                                            <?php if ($membresia['estado'] === 'ACTIVO'): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 rounded-pill">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 rounded-pill">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= toYesNo($membresia['es_destacado']) ?></td>
                                        <td><?= $membresia['max_servicios_activos'] ?></td>
                                        <td><?= toYesNo($membresia['acceso_estadisticas_pro']) ?></td>
                                        <td><?= toYesNo($membresia['permite_videos']) ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <!-- Ver detalle -->
                                                <button type="button" class="btn-action btn-view text-primary border-0 bg-transparent"
                                                    title="Ver detalles"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalDetalleMembresia"
                                                    onclick="cargarDetalleMembresia(
                                                                    '<?= htmlspecialchars($membresia['tipo']) ?>',
                                                                    '<?= htmlspecialchars($membresia['descripcion']) ?>',
                                                                    '<?= $membresia['costo'] ?>',
                                                                    '<?= $membresia['duracion_dias'] ?>',
                                                                    '<?= $membresia['estado'] ?>',
                                                                    '<?= $membresia['max_servicios_activos'] ?>',
                                                                    <?= $membresia['es_destacado'] ?>,
                                                                    <?= $membresia['permite_videos'] ?>,
                                                                    <?= $membresia['acceso_estadisticas_pro'] ?>
                                                                )">
                                                    <i class="bi bi-eye"></i>
                                                </button>

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
                                            <td><?= $membresia['costo'] ?></td>
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
    <div class="modal fade" id="modalDetalleMembresia" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">

                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold text-primary" id="modal-titulo">Nombre del Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body p-4">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Costo del Plan</small>
                            <h2 class="mb-0 fw-bold text-dark" id="modal-costo">$ 0</h2>
                        </div>
                        <div id="modal-estado-badge">
                        </div>
                    </div>

                    <div class="mb-4 bg-primary bg-opacity-10 p-3 rounded-3 border border-primary border-opacity-10">
                        <small class="text-primary fw-bold mb-1 d-block"><i class="bi bi-info-circle me-1"></i> Descripción</small>
                        <p class="mb-0 text-secondary small" id="modal-descripcion" style="line-height: 1.5;">...</p>
                    </div>

                    <h6 class="fw-bold border-bottom pb-2 mb-3">Características del Plan</h6>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-2 border rounded bg-light">
                                <small class="text-muted d-block">Duración</small>
                                <strong id="modal-duracion" class="text-dark">---</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 border rounded bg-light">
                                <small class="text-muted d-block">Máx. Servicios</small>
                                <strong id="modal-servicios" class="text-dark">---</strong>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="bi bi-star-fill text-warning me-2"></i>Es Destacado</span>
                                <span id="check-destacado"></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="bi bi-camera-video-fill text-danger me-2"></i>Permite Videos</span>
                                <span id="check-videos"></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="bi bi-bar-chart-fill text-info me-2"></i>Estadísticas Pro</span>
                                <span id="check-stats"></span>
                            </li>
                        </ul>
                    </div>

                </div>

                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary w-100 rounded-3" data-bs-dismiss="modal">Cerrar</button>
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
        xintegrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- tu javaScript -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/app.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/membresias.js"></script>
</body>

</html>