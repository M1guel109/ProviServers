<?php
require_once BASE_PATH . '/app/helpers/session-admin.php';
// ✅ CORREGIDO: usa moderacion-controller, no proveedor-controller
require_once BASE_PATH . '/app/controllers/moderacion-controller.php';

$datos = mostrarServicios();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Moderación de Servicios</title>

    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.dataTables.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/estilos-tablas.css">

    <style>
        .btn-approve:hover { border-color: #0066ff; color: #0066ff; background-color: #d1e7dd; }
        .btn-reject:hover  { border-color: #dc3545; color: #dc3545; background-color: #f8d7da; }
    </style>
</head>
<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar-administrador.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-administrador.php'; ?>

        <section id="titulo-principal">
            <div class="row align-items-start">
                <div class="col-md-8">
                    <h1 class="mb-1">Moderación de Servicios</h1>
                    <p class="text-muted mb-0">
                        Revisa y aprueba los servicios antes de su publicación.
                    </p>
                </div>
                <div class="col-md-4 d-flex justify-content-end align-items-start">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 mt-2">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/admin/dashboard">Panel Principal</a>
                            </li>
                            <li class="breadcrumb-item active">Moderación</li>
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
                        <i class="bi bi-table"></i> Listado Principal
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
                                        <?php
                                        $estado      = strtolower($servicio['publicacion_estado'] ?? 'pendiente');
                                        $badgeClass  = 'bg-warning text-dark';
                                        $icon        = 'bi-clock';
                                        if ($estado === 'aprobado')  { $badgeClass = 'bg-success'; $icon = 'bi-check-circle'; }
                                        if ($estado === 'rechazado') { $badgeClass = 'bg-danger';  $icon = 'bi-x-circle'; }
                                        ?>
                                        <tr>
                                            <td><?= (int)$servicio['id'] ?></td>
                                            <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                                            <td><?= htmlspecialchars($servicio['proveedor_nombre']) ?></td>
                                            <td><?= htmlspecialchars($servicio['categoria_nombre']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($servicio['created_at'])) ?></td>
                                            <td>
                                                <span class="badge <?= $badgeClass ?>">
                                                    <i class="bi <?= $icon ?>"></i>
                                                    <?= ucfirst($estado) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">

                                                    <button type="button"
                                                            class="btn-action btn-view"
                                                            title="Ver Detalle"
                                                            data-id="<?= (int)$servicio['id'] ?>">
                                                        <i class="bi bi-eye"></i>
                                                    </button>

                                                    <?php if ($estado !== 'aprobado') : ?>
                                                        <button type="button"
                                                                class="btn-action btn-approve"
                                                                title="Aprobar"
                                                                data-id="<?= (int)$servicio['id'] ?>">
                                                            <i class="bi bi-check-circle"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($estado !== 'rechazado') : ?>
                                                        <button type="button"
                                                                class="btn-action btn-reject"
                                                                title="Rechazar"
                                                                data-id="<?= (int)$servicio['id'] ?>">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    <?php endif; ?>

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
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Proveedor</th>
                                    <th>Categoría</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($datos)) : ?>
                                    <?php foreach ($datos as $servicio) : ?>
                                        <tr>
                                            <td><?= (int)$servicio['id'] ?></td>
                                            <!-- ✅ htmlspecialchars en tabla de exportación -->
                                            <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                                            <td><?= htmlspecialchars($servicio['proveedor_nombre']) ?></td>
                                            <td><?= htmlspecialchars($servicio['categoria_nombre']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($servicio['created_at'])) ?></td>
                                            <td><?= htmlspecialchars($servicio['publicacion_estado'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>

        <!-- MODAL DETALLE SERVICIO -->
        <div class="modal fade" id="modalDetalleServicio" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">

                    <div class="modal-header bg-white border-bottom">
                        <div>
                            <h5 class="modal-title fw-bold text-dark" id="modal-titulo">Cargando...</h5>
                            <span id="modal-categoria" class="badge bg-light text-secondary border mt-1">
                                Categoría
                            </span>
                        </div>
                        <button type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body p-0">

                        <!-- Loader -->
                        <div id="loader-detalle" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>

                        <!-- Contenido -->
                        <div id="contenido-detalle" class="d-none">
                            <div class="row g-0">

                                <!-- Columna izquierda: imagen + precio -->
                                <div class="col-md-5 bg-light border-end d-flex flex-column align-items-center justify-content-center p-3">
                                    <img id="modal-foto-servicio" src="" alt="Foto Servicio"
                                         class="img-fluid rounded shadow-sm"
                                         style="max-height:280px; object-fit:cover; width:100%;">
                                    <div class="mt-3 text-center">
                                        <span id="modal-estado-badge"
                                              class="badge rounded-pill px-4 py-2 fs-6 shadow-sm">
                                        </span>
                                    </div>
                                    <div class="mt-4 w-100 text-center">
                                        <h4 class="text-success fw-bold mb-0" id="modal-precio"></h4>
                                        <small class="text-muted">Precio base</small>
                                    </div>
                                </div>

                                <!-- Columna derecha: datos -->
                                <div class="col-md-7 p-4">

                                    <h6 class="text-uppercase text-muted fw-bold small mb-2">
                                        Descripción del Servicio
                                    </h6>
                                    <div class="p-3 bg-light rounded border mb-4"
                                         style="max-height:150px; overflow-y:auto;">
                                        <p id="modal-descripcion"
                                           class="text-secondary mb-0"
                                           style="white-space:pre-line; font-size:0.95rem;"></p>
                                    </div>

                                    <div class="card border-primary mb-3">
                                        <div class="card-header bg-primary text-white py-1">
                                            <small>
                                                <i class="bi bi-person-vcard me-1"></i>
                                                Datos del Proveedor
                                            </small>
                                        </div>
                                        <div class="card-body py-2">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="bg-light rounded-circle p-2 me-2 text-primary">
                                                    <i class="bi bi-person-fill fs-5"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold" id="modal-proveedor"></h6>
                                                    <small class="text-muted">Propietario</small>
                                                </div>
                                            </div>
                                            <ul class="list-unstyled small mb-0 border-top pt-2">
                                                <li class="mb-1">
                                                    <i class="bi bi-envelope text-muted me-2"></i>
                                                    <span id="modal-proveedor-email">---</span>
                                                </li>
                                                <li class="mb-1">
                                                    <i class="bi bi-telephone text-muted me-2"></i>
                                                    <span id="modal-proveedor-tel">---</span>
                                                </li>
                                                <li>
                                                    <i class="bi bi-geo-alt text-muted me-2"></i>
                                                    <span id="modal-proveedor-ubicacion">---</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <small class="text-muted fst-italic">
                                            Publicado el: <span id="modal-fecha"></span>
                                        </small>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer bg-light justify-content-between">
                        <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Cerrar</button>
                        <div id="modal-acciones-footer" class="d-none">
                            <button type="button" class="btn btn-outline-danger me-2 btn-modal-reject">
                                <i class="bi bi-x-circle"></i> Rechazar
                            </button>
                            <button type="button" class="btn btn-success btn-modal-approve">
                                <i class="bi bi-check-circle"></i> Aprobar
                            </button>
                        </div>
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
    <!-- ✅ Sin apexcharts ni dashboard.js -->
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
    <!-- ✅ CORREGIDO: kebab-case estricto -->
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/moderacion-servicio.js"></script>

</body>
</html>