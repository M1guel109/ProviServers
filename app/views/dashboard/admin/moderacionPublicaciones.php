<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
require_once BASE_PATH . '/app/controllers/proveedorController.php';

// Obtener datos
$datos = mostrarservicios();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Moderación de Servicios</title>

    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.dataTables.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/consultarUsuarios.css">
    <style>
        .btn-approve:hover { border-color: #198754; color: #198754; background-color: #d1e7dd; }
        .btn-reject:hover { border-color: #dc3545; color: #dc3545; background-color: #f8d7da; }
    </style>
</head>

<body>
    
    <?php include_once __DIR__ . '/../../layouts/sidebar_administrador.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header_administrador.php'; ?>

        <section id="titulo-principal">
            <div class="row align-items-start">
                <div class="col-md-8 d-flex flex-column">
                    <div>
                        <h1 class="mb-1">Moderación de Servicios</h1>
                        <p class="text-muted mb-0">
                            Revisa y aprueba los servicios creados por los proveedores antes de su publicación.
                        </p>
                    </div>
                    <a href="<?= BASE_URL ?>/admin/reporte?tipo=servicios" target="_blank" class="btn btn-primary mt-3 w-auto" style="width: fit-content;">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Reporte de Servicios
                    </a>
                </div>
                <div class="col-md-4 d-flex justify-content-end align-items-start">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0 mt-2"></ol>
                    </nav>
                </div>
            </div>
        </section>

        <section id="tabla-arriba">
            
            <ul class="nav nav-tabs mb-3" id="tablaTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tabla-tab" data-bs-toggle="tab" data-bs-target="#tabla-pane" type="button" role="tab">
                        <i class="bi bi-table"></i> Listado Principal
                    </button>
                </li>
                <!-- <li class="nav-item" role="presentation">
                    <button class="nav-link" id="acciones-tab" data-bs-toggle="tab" data-bs-target="#acciones-pane" type="button" role="tab">
                        <i class="bi bi-box-arrow-in-right"></i> Exportar Datos
                    </button>
                </li> -->
            </ul>

            <div class="tab-content" id="tablaTabsContent">
                
                <div class="tab-pane fade show active" id="tabla-pane" role="tabpanel">
                    <div class="table-container">
                        <table id="tabla" class="display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Proveedor</th>
                                    <th>Categoría</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($datos)) : ?>
                                    <?php foreach ($datos as $servicio) : ?>
                                        <tr>
                                            <td><?= $servicio['id'] ?></td>
                                            <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                                            <td><?= htmlspecialchars($servicio['proveedor_nombre']) ?></td>
                                            <td><?= htmlspecialchars($servicio['categoria_nombre']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($servicio['created_at'])) ?></td>
                                            <td>
                                                <?php
                                                $estado = strtolower($servicio['publicacion_estado'] ?? 'pendiente');
                                                $badgeClass = 'bg-warning text-dark';
                                                $icon = 'bi-clock';
                                                
                                                if ($estado === 'aprobado') { $badgeClass = 'bg-success'; $icon = 'bi-check-circle'; }
                                                if ($estado === 'rechazado') { $badgeClass = 'bg-danger'; $icon = 'bi-x-circle'; }
                                                ?>
                                                <span class="badge <?= $badgeClass ?>">
                                                    <i class="bi <?= $icon ?>"></i> <?= ucfirst($estado) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn-action btn-view" title="Ver Detalle"
                                                            data-id="<?= $servicio['id'] ?>">
                                                        <i class="bi bi-eye"></i>
                                                    </button>

                                                    <button type="button" class="btn-action btn-approve" title="Aprobar"
                                                            data-id="<?= $servicio['id'] ?>">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>

                                                    <button type="button" class="btn-action btn-reject" title="Rechazar"
                                                            data-id="<?= $servicio['id'] ?>">
                                                        <i class="bi bi-x-circle"></i>
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

                <!-- <div class="tab-pane fade" id="acciones-pane" role="tabpanel">
                    <div class="p-3">
                        <div class="alert alert-light border">
                            <i class="bi bi-info-circle me-2"></i> 
                            Esta tabla está optimizada para copiar y exportar datos rápidamente.
                        </div>
                        <table id="tabla-1" class="display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Proveedor</th>
                                    <th>Categoría</th>
                                    <th>Fecha Creación</th>
                                    <th>Estado Actual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($datos)) : ?>
                                    <?php foreach ($datos as $servicio) : ?>
                                        <tr>
                                            <td><?= $servicio['id'] ?></td>
                                            <td><?= $servicio['nombre'] ?></td>
                                            <td><?= $servicio['proveedor_nombre'] ?></td>
                                            <td><?= $servicio['categoria_nombre'] ?></td>
                                            <td><?= $servicio['created_at'] ?></td>
                                            <td><?= $servicio['publicacion_estado'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div> -->

            </div>
        </section>
    </main>

    <div class="modal fade" id="modalDetalleServicio" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Detalle del Servicio</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="loader-detalle" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>
                    </div>
                    <div id="contenido-detalle" class="d-none">
                        <div class="row">
                            <div class="col-md-5 mb-3 text-center">
                                <img id="modal-foto-servicio" src="" alt="Foto" class="img-fluid rounded shadow-sm border" style="max-height: 250px; object-fit: cover;">
                                <div class="mt-3"><span id="modal-estado-badge" class="badge rounded-pill px-3 py-2 fs-6"></span></div>
                            </div>
                            <div class="col-md-7">
                                <h3 id="modal-titulo" class="fw-bold text-dark mb-2"></h3>
                                <p class="text-muted small mb-3"><i class="bi bi-person-circle me-1"></i> Por: <span id="modal-proveedor" class="fw-bold"></span></p>
                                <div class="p-3 bg-light rounded border mb-3">
                                    <h5 class="text-success fw-bold mb-0"><i class="bi bi-cash-coin me-2"></i> <span id="modal-precio"></span></h5>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold text-muted small text-uppercase">Categoría:</label>
                                    <p id="modal-categoria" class="mb-0"></p>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold text-muted small text-uppercase">Descripción:</label>
                                    <p id="modal-descripcion" class="text-secondary" style="white-space: pre-line;"></p>
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

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.print.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>const BASE_URL = "<?= BASE_URL ?>";</script>
    
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/moderacionServicio.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>

</body>
</html>