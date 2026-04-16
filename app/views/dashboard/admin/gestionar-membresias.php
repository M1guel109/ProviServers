<?php
require_once BASE_PATH . '/app/helpers/session-admin.php';
require_once BASE_PATH . '/app/controllers/membresia-controller.php';

$datos = mostrarMembresias();

function toYesNo($value) {
    return ($value == 1) ? 'Sí' : 'No';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Gestión de Membresías</title>

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
            <div class="row align-items-start">
                <div class="col-md-8 d-flex flex-column">
                    <div>
                        <h1 class="mb-1">Gestión de Membresías</h1>
                        <p class="text-muted mb-0">
                            Listado de todos los planes de membresía de la plataforma.
                        </p>
                    </div>
                    <a href="<?= BASE_URL ?>/admin/reporte?tipo=membresias"
                       target="_blank"
                       class="btn btn-primary mt-3"
                       style="width: fit-content;">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Generar Reporte PDF
                    </a>
                </div>
                <div class="col-md-4 d-flex justify-content-end align-items-start">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 mt-2">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/admin/dashboard">Panel Principal</a>
                            </li>
                            <li class="breadcrumb-item active">Membresías</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <section id="tabla-arriba">

            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab"
                            data-bs-target="#tabla-pane" type="button">
                        <i class="bi bi-table"></i> Datos y Acciones
                    </button>
                </li>
                <li class="nav-item">
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
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Costo</th>
                                    <th>Duración (días)</th>
                                    <th>Estado</th>
                                    <th>Destacado</th>
                                    <th>Máx. Servicios</th>
                                    <th>Stats Pro</th>
                                    <th>Videos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($datos)) : ?>
                                    <?php foreach ($datos as $membresia) : ?>
                                        <tr>
                                            <!-- ✅ htmlspecialchars en todos los datos -->
                                            <td><?= htmlspecialchars($membresia['tipo']) ?></td>
                                            <td><?= htmlspecialchars(
                                                substr($membresia['descripcion'] ?? '', 0, 60) .
                                                (strlen($membresia['descripcion'] ?? '') > 60 ? '...' : '')
                                            ) ?></td>
                                            <td>$ <?= number_format((float)$membresia['costo'], 0, ',', '.') ?></td>
                                            <td><?= (int)$membresia['duracion_dias'] ?></td>
                                            <td>
                                                <?php if ($membresia['estado'] === 'ACTIVO'): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 rounded-pill">
                                                        Activo
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 rounded-pill">
                                                        Inactivo
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= toYesNo($membresia['es_destacado']) ?></td>
                                            <td><?= (int)$membresia['max_servicios_activos'] ?></td>
                                            <td><?= toYesNo($membresia['acceso_estadisticas_pro']) ?></td>
                                            <td><?= toYesNo($membresia['permite_videos']) ?></td>
                                            <td>
                                                <div class="action-buttons">

                                                    <!-- Ver detalle -->
                                                    <button type="button"
                                                            class="btn-action btn-view"
                                                            title="Ver detalles"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalDetalleMembresia"
                                                            onclick="cargarDetalleMembresia(
                                                                '<?= htmlspecialchars($membresia['tipo']) ?>',
                                                                '<?= htmlspecialchars($membresia['descripcion']) ?>',
                                                                '<?= (float)$membresia['costo'] ?>',
                                                                '<?= (int)$membresia['duracion_dias'] ?>',
                                                                '<?= $membresia['estado'] ?>',
                                                                '<?= (int)$membresia['max_servicios_activos'] ?>',
                                                                <?= (int)$membresia['es_destacado'] ?>,
                                                                <?= (int)$membresia['permite_videos'] ?>,
                                                                <?= (int)$membresia['acceso_estadisticas_pro'] ?>
                                                            )">
                                                        <i class="bi bi-eye"></i>
                                                    </button>

                                                    <!-- Editar -->
                                                    <a href="<?= BASE_URL ?>/admin/editar-membresia?id=<?= (int)$membresia['id'] ?>"
                                                       class="btn-action btn-edit"
                                                       title="Editar membresía">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    <!-- ✅ CORREGIDO: confirmación antes de eliminar -->
                                                    <button type="button"
                                                            class="btn-action btn-delete"
                                                            title="Eliminar membresía"
                                                            onclick="confirmarEliminacion('<?= BASE_URL ?>/admin/eliminar-membresia?accion=eliminar&id=<?= (int)$membresia['id'] ?>')">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>

                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">
                                            No hay membresías registradas.
                                        </td>
                                    </tr>
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
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Costo</th>
                                    <th>Duración (días)</th>
                                    <th>Estado</th>
                                    <th>Destacado</th>
                                    <th>Máx. Servicios</th>
                                    <th>Stats Pro</th>
                                    <th>Videos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($datos)) : ?>
                                    <?php foreach ($datos as $membresia) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($membresia['tipo']) ?></td>
                                            <td><?= htmlspecialchars($membresia['descripcion'] ?? '') ?></td>
                                            <td><?= number_format((float)$membresia['costo'], 0, ',', '.') ?></td>
                                            <td><?= (int)$membresia['duracion_dias'] ?></td>
                                            <td><?= htmlspecialchars($membresia['estado']) ?></td>
                                            <td><?= toYesNo($membresia['es_destacado']) ?></td>
                                            <td><?= (int)$membresia['max_servicios_activos'] ?></td>
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

        <!-- MODAL DETALLE MEMBRESÍA -->
        <div class="modal fade" id="modalDetalleMembresia" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">

                    <div class="modal-header bg-light border-bottom-0">
                        <h5 class="modal-title fw-bold text-primary" id="modal-titulo">
                            Nombre del Plan
                        </h5>
                        <button type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size:0.7rem;">
                                    Costo del Plan
                                </small>
                                <h2 class="mb-0 fw-bold text-dark" id="modal-costo">$ 0</h2>
                            </div>
                            <div id="modal-estado-badge"></div>
                        </div>

                        <div class="mb-4 bg-primary bg-opacity-10 p-3 rounded-3">
                            <small class="text-primary fw-bold mb-1 d-block">
                                <i class="bi bi-info-circle me-1"></i> Descripción
                            </small>
                            <p class="mb-0 text-secondary small" id="modal-descripcion"></p>
                        </div>

                        <h6 class="fw-bold border-bottom pb-2 mb-3">Características</h6>

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

                        <ul class="list-group list-group-flush small mt-3">
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

                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-secondary w-100 rounded-3"
                                data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

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
    <!-- ✅ Sin apexcharts, dashboard.js ni app.js -->
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/membresias.js"></script>

</body>
</html>