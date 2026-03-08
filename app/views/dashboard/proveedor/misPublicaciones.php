<?php
// Validar sesión de proveedor
require_once BASE_PATH . '/app/helpers/session_proveedor.php';

// Modelo de publicaciones
require_once BASE_PATH . '/app/models/Publicacion.php';

$usuarioId = $_SESSION['user']['id'] ?? null;
$publicaciones = [];

if ($usuarioId) {
    $publicacionModel = new Publicacion();
    $publicaciones = $publicacionModel->listarPorProveedorUsuario((int)$usuarioId);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Mis publicaciones</title>

    <!-- Css DataTables Export -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.dataTables.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- css de tablas / dashboard -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/publicaciones.css">
</head>

<body>
    <!-- SIDEBAR (lateral izquierdo) -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_proveedor.php';
    ?>

    <main class="contenido">
        <?php
        include_once __DIR__ . '/../../layouts/header_proveedor.php';
        ?>

        <!-- Título principal -->
        <section id="titulo-principal" class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h1 class="mb-1">Mis publicaciones</h1>
                <p class="text-muted mb-0">
                    Aquí ves el estado de las publicaciones que los clientes podrán encontrar en la plataforma:
                    pendientes de aprobación, activas, pausadas o rechazadas.
                </p>
            </div>

            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol id="breadcrumb" class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/proveedor/dashboard">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Mis publicaciones</li>
                </ol>
            </nav>
        </section>


        <section id="cards-publicaciones" class="mt-3">

            <?php if (empty($publicaciones)) : ?>
                <div class="empty-state">
                    <h5 class="text-muted mb-1">Todavía no tienes publicaciones</h5>
                    <p class="text-muted mb-0" style="font-size: 0.95rem;">
                        Crea un servicio desde
                        <a href="<?= BASE_URL ?>/proveedor/registrar-servicio">“Registrar servicio”</a>
                        y se generará una publicación pendiente de aprobación.
                    </p>
                </div>
            <?php else : ?>

                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">

                    <?php foreach ($publicaciones as $pub) : ?>
                        <?php
                        // Estado real de la publicación
                        $estado = $pub['estado'] ?? $pub['estado_publicacion'] ?? 'pendiente';

                        switch ($estado) {
                            case 'pendiente':
                                $badgeClass  = 'bg-warning text-dark';
                                $estadoTexto = 'Pendiente de aprobación';
                                break;

                            case 'aprobado':
                                $badgeClass  = 'bg-success';
                                $estadoTexto = 'Publicada';
                                break;

                            case 'rechazado':
                                $badgeClass  = 'bg-danger';
                                $estadoTexto = 'Rechazada';
                                break;

                            default:
                                $badgeClass  = 'bg-secondary';
                                $estadoTexto = ucfirst((string)$estado);
                                break;
                        }

                        $titulo = trim((string)($pub['titulo'] ?? ''));
                        $servName = (string)($pub['servicio_nombre'] ?? 'Sin nombre');
                        $catName  = (string)($pub['categoria_nombre'] ?? 'Sin categoría');

                        if ($titulo === '') {
                            $titulo = $servName !== '' ? $servName : 'Servicio ofertado';
                        }

                        $tituloShort = $titulo;
                        if (mb_strlen($tituloShort) > 60) {
                            $tituloShort = mb_substr($tituloShort, 0, 60) . '...';
                        }

                        $precioValor = isset($pub['precio']) ? (float)$pub['precio'] : 0;
                        $precio = number_format($precioValor, 2, ',', '.');

                        $fechaRaw = $pub['fecha_publicacion'] ?? $pub['publicacion_created_at'] ?? $pub['created_at'] ?? '';
                        if (!empty($fechaRaw) && strtotime($fechaRaw)) {
                            $fecha = date('d/m/Y h:i A', strtotime($fechaRaw));
                        } else {
                            $fecha = 'Sin fecha';
                        }

                        $motivoRechazo = trim((string)($pub['motivo_rechazo'] ?? ''));
                        $servicioId = (int)($pub['servicio_id'] ?? 0);
                        ?>

                        <div class="col">
                            <div class="card card-publicacion h-100 border-0 shadow-sm">

                                <div class="card-servicio-topbar"></div>

                                <div class="card-body">

                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= htmlspecialchars($estadoTexto) ?>
                                        </span>
                                        <span class="badge bg-dark">
                                            $ <?= htmlspecialchars($precio) ?>
                                        </span>
                                    </div>

                                    <h5 class="card-title fw-bold mb-2">
                                        <?= htmlspecialchars($tituloShort) ?>
                                    </h5>

                                    <div class="text-muted small mb-2">
                                        <i class="bi bi-box-seam"></i>
                                        <strong>Servicio base:</strong> <?= htmlspecialchars($servName) ?>
                                    </div>

                                    <div class="text-muted small mb-3">
                                        <i class="bi bi-tag"></i>
                                        <strong>Categoría:</strong> <?= htmlspecialchars($catName) ?>
                                    </div>

                                    <div class="meta-row text-muted small mb-2">
                                        <i class="bi bi-calendar3"></i>
                                        <span>Publicada: <?= htmlspecialchars($fecha) ?></span>
                                    </div>

                                    <?php if ($estado === 'rechazado' && $motivoRechazo !== '') : ?>
                                        <div class="alert alert-danger py-2 px-3 small mb-0">
                                            <strong>Motivo del rechazo:</strong><br>
                                            <?= nl2br(htmlspecialchars($motivoRechazo)) ?>
                                        </div>
                                    <?php endif; ?>

                                </div>

                                <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                    <div class="d-flex gap-2 flex-wrap">

                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary flex-fill btn-ver-detalle-publicacion"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalDetallePublicacion"
                                            data-publicacion-id="<?= $pub['id'] ?? '' ?>">
                                            <i class="bi bi-eye"></i> Ver
                                        </button>

                                        <?php if ($servicioId > 0) : ?>
                                            <a href="<?= BASE_URL ?>/proveedor/editar-servicio?id=<?= $servicioId ?>"
                                                class="btn btn-sm btn-outline-success flex-fill"
                                                title="Editar servicio">
                                                <i class="bi bi-pencil-square"></i> Editar
                                            </a>
                                        <?php endif; ?>

                                    </div>
                                </div>

                            </div>
                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

    </main>

    <!-- Modal de Detalle de Publicación -->
    <div class="modal fade" id="modalDetallePublicacion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-file-text me-2"></i>Detalle de la Publicación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">

                    <!-- Loader (efecto visual) -->
                    <div id="loader-detalle-publicacion" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>

                    <!-- Contenido (se muestra después del loader) -->
                    <div id="contenido-detalle-publicacion" class="d-none">

                        <!-- Cabecera con título y estado -->
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
                            <div>
                                <h3 id="modal-publicacion-titulo" class="fw-bold mb-2 text-dark"></h3>
                                <span id="modal-publicacion-estado" class="badge"></span>
                            </div>
                            <div class="text-end">
                                <span id="modal-publicacion-precio" class="badge bg-dark fs-6 p-3"></span>
                            </div>
                        </div>

                        <!-- Información del servicio base -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded h-100">
                                    <h6 class="text-primary fw-bold mb-3">
                                        <i class="bi bi-box-seam me-2"></i>Servicio Base
                                    </h6>
                                    <p class="mb-1 fw-medium" id="modal-publicacion-servicio"></p>
                                    <p class="mb-0 text-muted small" id="modal-publicacion-categoria"></p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded h-100">
                                    <h6 class="text-primary fw-bold mb-3">
                                        <i class="bi bi-calendar3 me-2"></i>Fecha de Publicación
                                    </h6>
                                    <p class="mb-0" id="modal-publicacion-fecha"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="p-3 bg-light rounded mb-4">
                            <h6 class="text-primary fw-bold mb-3">
                                <i class="bi bi-card-text me-2"></i>Descripción de la Publicación
                            </h6>
                            <p class="mb-0 text-muted" id="modal-publicacion-descripcion">Sin descripción</p>
                        </div>

                        <!-- Motivo de rechazo (solo visible si aplica) -->
                        <div id="modal-publicacion-rechazo-container" class="p-3 bg-danger bg-opacity-10 rounded mb-4 d-none">
                            <h6 class="text-danger fw-bold mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>Motivo del Rechazo
                            </h6>
                            <p class="mb-0 text-danger" id="modal-publicacion-rechazo"></p>
                        </div>

                        <!-- Acciones -->
                        <div class="d-flex gap-2 flex-wrap mt-4">
                            <a id="modal-link-editar-publicacion" href="#" class="btn btn-outline-success">
                                <i class="bi bi-pencil-square"></i> Editar Servicio
                            </a>
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



    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- JS dashboard (sidebar, etc.) -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/app.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/detallePublicacion.js"></script>


</body>

</html>