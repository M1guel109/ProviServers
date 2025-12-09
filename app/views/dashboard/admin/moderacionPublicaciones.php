<?php
// Requerir la sesión de administrador
require_once BASE_PATH . '/app/helpers/session_admin.php';

// Enlazamos el controlador de Servicios (Donde asumimos está mostrarServiciosPendientes)
require_once BASE_PATH . '/app/controllers/proveedorController.php';

// Llamamos la función específica que consulta los datos de los servicios pendientes
$datos = mostrarservicios();

// NOTA: Asumiendo que BASE_URL y otras variables globales están definidas.
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Moderación de Servicios</title>

    <!-- Css DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css">

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
    // Asumiendo que tienes un layout de sidebar para administrador
    include_once __DIR__ . '/../../layouts/sidebar_administrador.php';
    ?>


    <main class="contenido">
        <?php
        // Asumiendo que tienes un layout de header para administrador
        include_once __DIR__ . '/../../layouts/header_administrador.php';
        ?>

        <!-- Sección de Título y Breadcrumb -->
        <section id="titulo-principal" class="mb-4">
            <div class="row align-items-start">

                <div class="col-md-8 d-flex flex-column">

                    <div>
                        <h1 class="mb-1">Moderación de Servicios</h1>
                        <p class="text-muted mb-0">
                            Revisa y aprueba los servicios creados por los proveedores antes de su publicación.
                        </p>
                    </div>

                </div>

                <div class="col-md-4 d-flex justify-content-end align-items-start">

                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0 mt-2">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Moderación</li>
                        </ol>
                    </nav>

                </div>

            </div>
        </section>


        <!-- Contenido Principal: Tabla de Moderación -->
        <section id="tabla-moderacion" class="card shadow p-4">

            <div class="table-responsive">
                <table id="tabla" class="display nowrap table table-hover w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título del Servicio</th>
                            <th>Proveedor</th>
                            <th>Categoría</th>
                            <th>Fecha Creación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-servicios-moderacion">
                        <?php if (!empty($datos)) : ?>

                            <?php foreach ($datos as $servicio) : ?>

                                <tr>
                                    <td><?= htmlspecialchars($servicio['id']) ?></td>
                                    <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                                    <td><?= htmlspecialchars($servicio['proveedor_id']) ?></td>
                                    <td><?= htmlspecialchars($servicio['id_categoria']) ?></td>
                                    <td><?= htmlspecialchars($servicio['created_at']) ?></td>
                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            <!-- NOTA: 'disponibilidad' aquí probablemente se refiere al estado pendiente -->
                                            <i class="bi bi-clock"></i> Pendiente
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <!-- Enlace para ver detalle del servicio -->
                                            <a href="#"
                                                class="btn-action btn-view"
                                                title="Revisar detalles">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <!-- Enlace para Aprobar (abre modal) -->
                                            <a href="#"
                                                class="btn-action btn-approve"

                                                title="Aprobar Servicio">
                                                <i class="bi bi-check-circle"></i>
                                            </a>

                                            <!-- Enlace para Rechazar (abre modal) -->
                                            <a href="#"
                                                class="btn-action btn-reject"

            
                                                title="Rechazar Servicio">
                                                <i class="bi bi-x-circle"></i>
                                            </a>
                                        </div>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <h2>No hay servicios pendientes de moderación.</h2>
                                    <p>¡El control de calidad está al día!</p>
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>
            </div>

        </section>

    </main>

    <!-- Modal de Confirmación de Aprobación -->
    <div class="modal fade" id="confirmApproveModal" tabindex="-1" aria-labelledby="confirmApproveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="confirmApproveModalLabel"><i class="bi bi-check-circle-fill"></i> Confirmar Aprobación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea **aprobar** este servicio? Se hará visible inmediatamente para todos los usuarios.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <!-- Enlace de acción -->
                    <a id="modalConfirmApproveButton" href="#" class="btn btn-success">Sí, Aprobar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Rechazo -->
    <div class="modal fade" id="confirmRejectModal" tabindex="-1" aria-labelledby="confirmRejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmRejectModalLabel"><i class="bi bi-x-circle-fill"></i> Confirmar Rechazo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea **rechazar** este servicio? El proveedor será notificado.</p>
                    <div class="mb-3">
                        <label for="motivoRechazo" class="form-label">Motivo del Rechazo (Obligatorio)</label>
                        <textarea class="form-control" id="motivoRechazo" rows="3" placeholder="Indique claramente por qué se rechaza el servicio."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <!-- Botón de acción (requerirá JS para capturar el motivo) -->
                    <button id="modalConfirmRejectButton" type="button" class="btn btn-danger" disabled>Sí, Rechazar</button>
                </div>
            </div>
        </div>
    </div>


    <footer>
        <!-- Enlaces / Información -->
    </footer>

    <!-- Datatables -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>



    <!-- tu javaScript (asumiendo que estos archivos existen) -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/app.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
</body>

</html>