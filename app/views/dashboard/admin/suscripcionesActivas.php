<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
require_once BASE_PATH . '/app/controllers/SuscripcionController.php';

// Asumimos que esta función llama al modelo con el JOIN que te puse arriba
$suscripciones = listarSuscripciones();

// Helper para calcular días restantes visualmente
function calcularDiasRestantes($fecha_fin)
{
    $hoy = new DateTime();
    $fin = new DateTime($fecha_fin);
    $diferencia = $hoy->diff($fin);

    if ($fin < $hoy) return -1; // Vencida
    return $diferencia->days; // Días que faltan
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Suscripciones</title>

    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.dataTables.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/estilosTablas.css">
</head>

<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar_administrador.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header_administrador.php'; ?>

        <section id="titulo-principal">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-1">Suscripciones Activas</h1>
                    <p class="text-muted mb-0">
                        Monitorea los planes contratados por los proveedores y sus fechas de vencimiento.
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0 justify-content-end">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Inicio</a></li>
                            <li class="breadcrumb-item active">Suscripciones</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <section id="tabla-arriba" class="mt-4">

            <ul class="nav nav-tabs mb-3" id="tablaTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabla-pane">
                        <i class="bi bi-credit-card-2-front"></i> Listado General
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#acciones-pane">
                        <i class="bi bi-file-earmark-arrow-down"></i> Exportar
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tabla-pane">

                    <div class="table-responsive">
                        <table id="tabla" class="display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Proveedor</th>
                                    <th>Plan Contratado</th>
                                    <th>Inicio</th>
                                    <th>Vencimiento</th>
                                    <th>Estado</th>
                                    <th>Días Restantes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($suscripciones)) : ?>
                                    <?php foreach ($suscripciones as $sub) : ?>
                                        <?php
                                        $dias = calcularDiasRestantes($sub['fecha_fin']);

                                        // Convertimos a mayúsculas lo que viene de la BD para asegurar la comparación
                                        // Y verificamos si es 'ACTIVA' (que es como suele estar en tu enum 'activa')
                                        $estado_normalizado = strtoupper($sub['estado']);

                                        // Ahora comparamos contra 'ACTIVA' (femenino, singular)
                                        $es_activo = ($estado_normalizado === 'ACTIVA' && $dias >= 0);
                                        ?>
                                        <tr>
                                            <td class="fw-bold text-primary">
                                                <i class="bi bi-person-circle me-1 text-muted"></i>
                                                <?= htmlspecialchars($sub['nombre_proveedor']) ?>
                                            </td>

                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?= htmlspecialchars($sub['nombre_plan']) ?>
                                                </span>
                                            </td>

                                            <td><?= date('d/m/Y', strtotime($sub['fecha_inicio'])) ?></td>
                                            <td class="fw-bold"><?= date('d/m/Y', strtotime($sub['fecha_fin'])) ?></td>

                                            <td>
                                                <?php if ($es_activo): ?>
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 rounded-pill">Activa</span>
                                                <?php elseif ($estado_normalizado === 'CANCELADA'): ?>
                                                    <span class="badge bg-dark bg-opacity-10 text-dark border border-dark px-3 rounded-pill">Cancelada</span>
                                                <?php elseif ($dias < 0): ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 rounded-pill">Vencida</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-3 rounded-pill"><?= ucfirst($sub['estado']) ?></span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <?php if ($dias < 0): ?>
                                                    <span class="text-danger fw-bold">Expirado</span>
                                                <?php elseif ($dias <= 5): ?>
                                                    <span class="text-warning fw-bold">⚠️ <?= $dias ?> días</span>
                                                <?php else: ?>
                                                    <span class="text-success"><?= $dias ?> días</span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button"
                                                        class="btn-action btn-view"
                                                        onclick="cargarDetalleSuscripcion(<?= $sub['id'] ?>)">
                                                        <i class="bi bi-eye"></i>
                                                    </button>

                                                    <?php if ($es_activo): ?>
                                                        <button type="button"
                                                            class="btn-action btn-delete text-danger border-0 bg-transparent"
                                                            title="Cancelar Suscripción"
                                                            onclick="confirmarCancelacion(<?= $sub['id'] ?>)">
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

                <div class="tab-pane fade" id="acciones-pane">
                    <div class="p-4 text-center text-muted border rounded bg-light">
                        <i class="bi bi-table display-4 mb-3 d-block"></i>
                        <p>Utiliza los botones de la tabla principal para exportar a Excel, PDF o Imprimir.</p>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <div class="modal fade" id="modalDetalleSuscripcion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-credit-card-2-front-fill me-2"></i>Detalle de la Suscripción
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body p-0">

                    <div id="loader-sub" class="text-center py-5">
                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Obteniendo datos...</p>
                    </div>

                    <div id="contenido-sub" class="d-none">

                        <div class="bg-light p-4 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="position-relative">
                                    <img id="modal-sub-foto" src="" alt="Foto"
                                        class="rounded-circle border border-3 border-white shadow-sm"
                                        style="width: 80px; height: 80px; object-fit: cover;">
                                </div>
                                <div class="ms-4">
                                    <h4 id="modal-sub-proveedor" class="mb-0 fw-bold text-dark"></h4>
                                    <div class="d-flex align-items-center mt-1">
                                        <i class="bi bi-envelope me-2 text-muted"></i>
                                        <span id="modal-sub-email" class="text-muted"></span>
                                    </div>
                                </div>
                                <div class="ms-auto text-end">
                                    <span id="modal-sub-estado-badge" class="badge rounded-pill px-3 py-2 fs-6"></span>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="row g-4">

                                <div class="col-md-6">
                                    <div class="card h-100 border-0 shadow-sm bg-white border border-light">
                                        <div class="card-body">
                                            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                                                <i class="bi bi-star-fill me-2"></i>Plan Contratado
                                            </h6>

                                            <div class="mb-3">
                                                <small class="text-muted d-block text-uppercase" style="font-size: 0.75rem;">Nombre del Plan</small>
                                                <span id="modal-sub-plan" class="fs-5 fw-bold text-dark"></span>
                                            </div>

                                            <div class="row">
                                                <div class="col-6">
                                                    <small class="text-muted d-block text-uppercase" style="font-size: 0.75rem;">Costo</small>
                                                    <span id="modal-sub-costo" class="fw-bold text-success"></span>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block text-uppercase" style="font-size: 0.75rem;">ID Referencia</small>
                                                    <span id="modal-sub-id" class="font-monospace text-secondary"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card h-100 border-0 shadow-sm bg-light">
                                        <div class="card-body">
                                            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                                                <i class="bi bi-calendar-check-fill me-2"></i>Vigencia
                                            </h6>

                                            <ul class="list-unstyled mb-0">
                                                <li class="mb-3 d-flex justify-content-between">
                                                    <span class="text-muted">Fecha Inicio:</span>
                                                    <strong id="modal-sub-inicio"></strong>
                                                </li>
                                                <li class="mb-3 d-flex justify-content-between">
                                                    <span class="text-muted">Fecha Fin:</span>
                                                    <strong id="modal-sub-fin"></strong>
                                                </li>
                                                <li class="mt-3 pt-2 border-top text-center">
                                                    <small class="text-muted d-block mb-1">Tiempo Restante</small>
                                                    <span id="modal-sub-restante" class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm"></span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

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
    <script>
        const BASE_URL = "<?= BASE_URL ?>"; // Esto imprime: "http://localhost/ProviServers"
    </script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/membresias.js"></script>


</body>

</html>