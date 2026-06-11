<?php
require_once BASE_PATH . '/app/helpers/session-proveedor.php';
// ✅ CORREGIDO: datos vienen del controlador
require_once BASE_PATH . '/app/controllers/proveedor-controller.php';

$usuarioId = (int)($_SESSION['user']['id'] ?? 0);
$datos     = obtenerServiciosDelProveedor($usuarioId);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Mis Servicios</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/listar-servicio.css">
</head>

<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar-proveedor.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-proveedor.php'; ?>

        <!-- Título -->
        <section id="titulo-principal" class="section-hero mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-1">Mis Servicios</h1>
                    <p class="text-muted mb-0">Aquí puedes ver tus servicios publicados, su estado y gestionar sus acciones.</p>
                    <a href="<?= BASE_URL ?>/proveedor/reporte?tipo=serviciosProveedor"
                       target="_blank" class="btn btn-primary btn-sm mt-3">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Generar Reporte PDF
                    </a>
                </div>
                <div class="col-md-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/proveedor/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Mis Servicios</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <!-- Listado en tarjetas -->
        <section id="cards-servicios" class="mt-3">

            <?php if (empty($datos)) : ?>
                <div class="empty-state">
                    <i class="bi bi-briefcase fs-1 text-muted d-block mb-3"></i>
                    <h5 class="text-muted mb-1">No tienes servicios publicados aún</h5>
                    <p class="text-muted mb-3">
                        Crea tu primer servicio para empezar a recibir solicitudes.
                    </p>
                    <a href="<?= BASE_URL ?>/proveedor/registrar-servicio"
                        class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Registrar mi primer servicio
                    </a>
                </div>

            <?php else : ?>

                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">

                    <?php foreach ($datos as $fila) : ?>
                        <?php
                        // ✅ CORREGIDO: alias correcto del modelo
                        $estado = $fila['estado_publicacion'] ?? 'pendiente';

                        switch ($estado) {
                            case 'aprobado':
                                $textoEstado    = 'Publicado';
                                $claseEstado    = 'estado-publicacion estado-activa';
                                $badgeModalClass = 'bg-success';
                                break;
                            case 'rechazado':
                                $textoEstado    = 'Rechazado';
                                $claseEstado    = 'estado-publicacion estado-rechazada';
                                $badgeModalClass = 'bg-danger';
                                break;
                            case 'pausada':
                                $textoEstado    = 'Pausado';
                                $claseEstado    = 'estado-publicacion estado-pausada';
                                $badgeModalClass = 'bg-secondary';
                                break;
                            default: // pendiente
                                $textoEstado    = 'Pendiente de aprobación';
                                $claseEstado    = 'estado-publicacion estado-pendiente';
                                $badgeModalClass = 'bg-warning text-dark';
                                break;
                        }

                        $disponible  = (int)($fila['servicio_disponible'] ?? 0) === 1;
                        $servicioId  = (int)($fila['servicio_id'] ?? 0);
                        $img         = $fila['servicio_imagen'] ?? '';
                        $imgUrl      = !empty($img)
                            ? BASE_URL . '/public/uploads/servicios/' . $img
                            : BASE_URL . '/public/uploads/servicios/default_service.png';

                        $descFull = trim((string)($fila['servicio_descripcion'] ?? 'Sin descripción'));
                        $descCard = mb_strlen($descFull) > 120
                            ? mb_substr($descFull, 0, 120) . '...'
                            : $descFull;

                        $fechaPub = $fila['publicacion_created_at'] ?? '';
                        ?>

                        <div class="col">
                            <div class="card card-servicio h-100 border-0 shadow-sm">

                                <div class="card-servicio-topbar"></div>

                                <div class="card-body">

                                    <!-- Estado + disponibilidad -->
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <span class="<?= $claseEstado ?>">
                                            <?= $textoEstado ?>
                                        </span>
                                        <?php if ($disponible): ?>
                                            <span class="badge-disponibilidad badge-disponible">Disponible</span>
                                        <?php else: ?>
                                            <span class="badge-disponibilidad badge-no-disponible">No disponible</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Nombre -->
                                    <h5 class="card-title fw-bold mb-1">
                                        <?= htmlspecialchars($fila['servicio_nombre'] ?? '') ?>
                                    </h5>

                                    <!-- Categoría -->
                                    <div class="text-muted small mb-2">
                                        <i class="bi bi-tag"></i>
                                        <?= htmlspecialchars($fila['categoria_nombre'] ?? 'Sin categoría') ?>
                                    </div>

                                    <!-- Descripción corta -->
                                    <p class="card-text text-secondary small mb-3">
                                        <?= htmlspecialchars($descCard) ?>
                                    </p>

                                    <!-- Motivo rechazo si aplica -->
                                    <?php if ($estado === 'rechazado' && !empty($fila['motivo_rechazo'])): ?>
                                        <div class="alert alert-danger py-2 px-3 small mb-3">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            <strong>Motivo:</strong>
                                            <?= htmlspecialchars($fila['motivo_rechazo']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Fecha -->
                                    <div class="meta-row text-muted small">
                                        <i class="bi bi-calendar3"></i>
                                        <span>
                                            <?= !empty($fechaPub)
                                                ? date('d/m/Y', strtotime($fechaPub))
                                                : '—' ?>
                                        </span>
                                    </div>

                                </div>

                                <!-- Acciones -->
                                <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                    <div class="d-flex gap-2 flex-wrap">

                                        <!-- VER DETALLE -->
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary flex-fill btn-ver-detalle-servicio"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalDetalleServicio"
                                            data-base-url="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>"
                                            data-servicio-id="<?= $servicioId ?>"
                                            data-servicio-nombre="<?= htmlspecialchars($fila['servicio_nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            data-servicio-categoria="<?= htmlspecialchars($fila['categoria_nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            data-servicio-descripcion="<?= htmlspecialchars($descFull, ENT_QUOTES, 'UTF-8') ?>"
                                            data-servicio-img="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?>"
                                            data-servicio-fecha="<?= htmlspecialchars($fechaPub, ENT_QUOTES, 'UTF-8') ?>"
                                            data-servicio-estado-texto="<?= htmlspecialchars($textoEstado, ENT_QUOTES, 'UTF-8') ?>"
                                            data-servicio-estado-badgeclass="<?= htmlspecialchars($badgeModalClass, ENT_QUOTES, 'UTF-8') ?>"
                                            data-servicio-disponible="<?= $disponible ? 1 : 0 ?>"
                                            data-servicio-disponible-texto="<?= $disponible ? 'Disponible' : 'No disponible' ?>"
                                            title="Ver detalle">
                                            <i class="bi bi-eye"></i> Ver
                                        </button>

                                        <!-- EDITAR (solo pendiente o rechazado) -->
                                        <?php if (in_array($estado, ['pendiente', 'rechazado'], true)) : ?>
                                            <a href="<?= BASE_URL ?>/proveedor/editar-servicio?id=<?= $servicioId ?>"
                                                class="btn btn-sm btn-outline-success flex-fill"
                                                title="Editar servicio">
                                                <i class="bi bi-pencil-square"></i> Editar
                                            </a>
                                        <?php endif; ?>

                                        <!-- ✅ CORREGIDO: Eliminar con confirmación SweetAlert -->
                                        <button type="button"
                                            class="btn btn-sm btn-outline-danger flex-fill btn-eliminar-card"
                                            data-id="<?= $servicioId ?>"
                                            title="Eliminar servicio">
                                            <i class="bi bi-trash3"></i> Eliminar
                                        </button>

                                        <?php if ($estado === 'aprobado') : ?>

                                            <?php if ($disponible) : ?>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-secondary flex-fill btn-pausar-servicio"
                                                    data-id="<?= $servicioId ?>"
                                                    title="Pausar publicación">
                                                    <i class="bi bi-pause-circle"></i> Pausar
                                                </button>
                                            <?php else : ?>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-success flex-fill btn-reanudar-servicio"
                                                    data-id="<?= $servicioId ?>"
                                                    title="Activar publicación">
                                                    <i class="bi bi-play-circle"></i> Activar
                                                </button>
                                            <?php endif; ?>

                                        <?php endif; ?>

                                    </div>
                                </div>

                            </div>
                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

        <!-- MODAL DETALLE SERVICIO -->
        <div class="modal fade modal-cliente" id="modalDetalleServicio" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-box-seam-fill me-2"></i>Detalle del Servicio
                        </h5>
                        <button type="button" class="btn-close btn-close-white"
                            data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body p-4">

                        <!-- Loader -->
                        <div id="loader-detalle-servicio" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>

                        <!-- Contenido -->
                        <div id="contenido-detalle-servicio" class="d-none">

                            <!-- Imagen -->
                            <div class="servicio-img-wrap mb-4">
                                <img id="modal-servicio-img" src="" alt="Imagen del servicio"
                                    onerror="this.src='<?= BASE_URL ?>/public/uploads/servicios/default_service.png';">
                                <div class="servicio-img-overlay d-flex justify-content-between align-items-start">
                                    <span id="modal-servicio-estado" class="badge"></span>
                                    <span id="modal-servicio-disponible" class="badge"></span>
                                </div>
                            </div>

                            <!-- Título + ID + Fecha -->
                            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3 pb-3 border-bottom">
                                <div>
                                    <h3 id="modal-servicio-nombre" class="mb-1 fw-bold"></h3>
                                    <small id="modal-servicio-id" class="text-muted fst-italic"></small>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted small">
                                        <i class="bi bi-calendar3"></i>
                                        <span id="modal-servicio-fecha"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded h-100">
                                        <h6 class="text-primary fw-bold mb-3">
                                            <i class="bi bi-tag me-2"></i>Categoría
                                        </h6>
                                        <p class="mb-0 text-muted" id="modal-servicio-categoria"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded h-100">
                                        <h6 class="text-primary fw-bold mb-3">
                                            <i class="bi bi-info-circle me-2"></i>Estado
                                        </h6>
                                        <p class="mb-0">
                                            <strong>Publicación:</strong>
                                            <span id="modal-servicio-estado-texto"></span><br>
                                            <strong>Disponibilidad:</strong>
                                            <span id="modal-servicio-disponible-texto"></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 bg-light rounded">
                                        <h6 class="text-primary fw-bold mb-3">
                                            <i class="bi bi-card-text me-2"></i>Descripción
                                        </h6>
                                        <p class="mb-0 text-muted" id="modal-servicio-descripcion"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Acciones rápidas -->
                            <div class="d-flex gap-2 flex-wrap mt-4">
                                <a id="modal-link-editar" href="#" class="btn btn-outline-success">
                                    <i class="bi bi-pencil-square"></i> Editar
                                </a>
                                <button type="button"
                                    id="modal-btn-eliminar"
                                    class="btn btn-outline-danger">
                                    <i class="bi bi-trash3"></i> Eliminar
                                </button>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cerrar</button>
                    </div>

                </div>
            </div>
        </div>

    </main>

    <footer></footer>

    <!-- ✅ SweetAlert primero -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>
    <!-- ✅ CORREGIDO: nombres correctos, sin app.js -->
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/dashboard-proveedor.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/detalle-servicio.js"></script>



</body>

</html>