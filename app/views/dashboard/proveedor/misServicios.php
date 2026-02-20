<?php
// Validar sesión de proveedor
require_once BASE_PATH . '/app/helpers/session_proveedor.php';

// Modelo de publicaciones
require_once BASE_PATH . '/app/models/Publicacion.php';

$usuarioId = $_SESSION['user']['id'] ?? null;
$datos = [];

if ($usuarioId) {
    $pubModel = new Publicacion();
    $datos = $pubModel->listarPorProveedorUsuario((int)$usuarioId);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Mis Servicios</title>

    <!-- Css DataTables Export -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.dataTables.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- css de tablas / dashboard -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardTable.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/registrar-servicio.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/registrar-servicio.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/listar-servicio.css">


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

        <!-- Sección título -->
        <section id="titulo-principal" class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h1 class="mb-1">Mis Servicios</h1>
                <p class="text-muted mb-0">
                    Aquí puedes ver tus servicios publicados, su estado y gestionar sus acciones.
                </p>
            </div>

            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol id="breadcrumb" class="breadcrumb mb-0"></ol>
            </nav>

            <a href="<?= BASE_URL ?>/proveedor/reporte?tipo=serviciosProveedor" target="_blank" class="btn btn-primary mt-3">
                <i class="bi bi-file-earmark-pdf-fill"></i> Generar Reporte PDF
            </a>
        </section>

        <!-- Tabla de servicios / publicaciones -->
        <!-- LISTADO EN TARJETAS -->
        <section id="cards-servicios" class="mt-3">


            <?php if (empty($datos)) : ?>
                <div class="empty-state">
                    <h5 class="text-muted mb-1">No tienes servicios publicados aún</h5>
                    <p class="text-muted mb-0">Crea tu primer servicio para empezar a recibir solicitudes.</p>
                </div>
            <?php else : ?>

                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">

                    <?php foreach ($datos as $fila) : ?>
                        <?php
                        $estado = $fila['estado_publicacion'] ?? 'pendiente';

                        // Texto y estilos por estado (igual que tu lógica)
                        switch ($estado) {
                            case 'aprobado':
                                $textoEstado = 'Publicado';
                                $claseEstado = 'estado-publicacion estado-activa';
                                break;
                            case 'rechazada':
                                $textoEstado = 'Rechazado';
                                $claseEstado = 'estado-publicacion estado-rechazada';
                                break;
                            case 'pausada':
                                $textoEstado = 'Pausado';
                                $claseEstado = 'estado-publicacion estado-pausada';
                                break;
                            case 'pendiente':
                            default:
                                $textoEstado = 'Pendiente de aprobación';
                                $claseEstado = 'estado-publicacion estado-pendiente';
                                break;
                        }

                        $disponible = (int)($fila['servicio_disponible'] ?? 0) === 1;

                        $img = $fila['servicio_imagen'] ?? '';
                        $imgUrl = !empty($img)
                            ? (BASE_URL . '/public/uploads/servicios/' . $img)
                            : (BASE_URL . '/public/assets/img/default_service.png'); // crea este placeholder si quieres

                        $servicioId = (int)($fila['servicio_id'] ?? 0);

                        // Descripción corta
                        $desc = trim((string)($fila['servicio_descripcion'] ?? 'Sin descripción'));
                        if (mb_strlen($desc) > 120) $desc = mb_substr($desc, 0, 120) . '...';

                        $fechaPub = $fila['publicacion_created_at'] ?? '';
                        ?>

                        <?php foreach ($datos as $fila) : ?>
                            <?php
                            $estado = $fila['estado_publicacion'] ?? 'pendiente';

                            // Texto y estilos por estado (igual que tu lógica)
                            switch ($estado) {
                                case 'aprobado':
                                    $textoEstado = 'Publicado';
                                    $claseEstado = 'estado-publicacion estado-activa';
                                    $badgeModalClass = 'bg-success';
                                    break;
                                case 'rechazada':
                                    $textoEstado = 'Rechazado';
                                    $claseEstado = 'estado-publicacion estado-rechazada';
                                    $badgeModalClass = 'bg-danger';
                                    break;
                                case 'pausada':
                                    $textoEstado = 'Pausado';
                                    $claseEstado = 'estado-publicacion estado-pausada';
                                    $badgeModalClass = 'bg-secondary';
                                    break;
                                case 'pendiente':
                                default:
                                    $textoEstado = 'Pendiente de aprobación';
                                    $claseEstado = 'estado-publicacion estado-pendiente';
                                    $badgeModalClass = 'bg-warning text-dark';
                                    break;
                            }

                            $disponible = (int)($fila['servicio_disponible'] ?? 0) === 1;

                            $img = $fila['servicio_imagen'] ?? '';
                            $imgUrl = !empty($img)
                                ? (BASE_URL . '/public/uploads/servicios/' . $img)
                                : (BASE_URL . '/public/assets/img/default_service.png');

                            $servicioId = (int)($fila['servicio_id'] ?? 0);

                            // Descripción completa para modal
                            $descFull = trim((string)($fila['servicio_descripcion'] ?? 'Sin descripción'));

                            // Descripción corta para tarjeta
                            $descCard = $descFull;
                            if (mb_strlen($descCard) > 120) $descCard = mb_substr($descCard, 0, 120) . '...';

                            $fechaPub = $fila['publicacion_created_at'] ?? '';

                            // Helper para atributos HTML
                            $nombreAttr = htmlspecialchars((string)($fila['servicio_nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $catAttr    = htmlspecialchars((string)($fila['categoria_nombre'] ?? 'Sin categoría'), ENT_QUOTES, 'UTF-8');
                            $descAttr   = htmlspecialchars($descFull, ENT_QUOTES, 'UTF-8');
                            $imgAttr    = htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8');
                            $fechaAttr  = htmlspecialchars((string)$fechaPub, ENT_QUOTES, 'UTF-8');
                            $estadoTxtAttr = htmlspecialchars($textoEstado, ENT_QUOTES, 'UTF-8');
                            $badgeClassAttr = htmlspecialchars($badgeModalClass, ENT_QUOTES, 'UTF-8');
                            $dispTxtAttr = $disponible ? 'Disponible' : 'No disponible';
                            ?>

                            <div class="col">
                                <div class="card card-servicio h-100 border-0 shadow-sm">

                                    <!-- (IMAGEN REMOVIDA DE LA TARJETA) -->
                                    <!-- Puedes dejar un header visual mínimo si quieres -->
                                    <div class="card-servicio-topbar"></div>

                                    <div class="card-body">
                                        <!-- Estado + disponibilidad -->
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                            <span class="<?= $claseEstado ?>"><?= $textoEstado ?></span>

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

                                        <!-- Descripción -->
                                        <p class="card-text text-secondary small mb-3">
                                            <?= htmlspecialchars($descCard) ?>
                                        </p>

                                        <!-- Fecha publicación -->
                                        <div class="meta-row text-muted small">
                                            <i class="bi bi-calendar3"></i>
                                            <span>Publicado: <?= htmlspecialchars($fechaPub) ?></span>
                                        </div>
                                    </div>

                                    <!-- Acciones -->
                                    <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                        <div class="d-flex gap-2 flex-wrap">

                                            <!-- VER DETALLE (ABRE MODAL Y PASA DATA) -->
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary flex-fill btn-ver-detalle-servicio"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalDetalleServicio"
                                                data-base-url="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>"
                                                data-servicio-id="<?= (int)$servicioId ?>"
                                                data-servicio-nombre="<?= $nombreAttr ?>"
                                                data-servicio-categoria="<?= $catAttr ?>"
                                                data-servicio-descripcion="<?= $descAttr ?>"
                                                data-servicio-img="<?= $imgAttr ?>"
                                                data-servicio-fecha="<?= $fechaAttr ?>"
                                                data-servicio-estado-texto="<?= $estadoTxtAttr ?>"
                                                data-servicio-estado-badgeclass="<?= $badgeClassAttr ?>"
                                                data-servicio-disponible="<?= $disponible ? 1 : 0 ?>"
                                                data-servicio-disponible-texto="<?= htmlspecialchars($dispTxtAttr, ENT_QUOTES, 'UTF-8') ?>"
                                                title="Ver detalle">
                                                <i class="bi bi-eye"></i> Ver
                                            </button>

                                            <!-- Editar (solo pendiente o rechazada) -->
                                            <?php if (in_array($estado, ['pendiente', 'rechazada'], true)) : ?>
                                                <a href="<?= BASE_URL ?>/proveedor/editar-servicio?id=<?= $servicioId ?>"
                                                    class="btn btn-sm btn-outline-success flex-fill"
                                                    title="<?= $estado === 'rechazada' ? 'Editar y reenviar a revisión' : 'Editar servicio' ?>">
                                                    <i class="bi bi-pencil-square"></i> Editar
                                                </a>
                                            <?php endif; ?>

                                            <!-- Eliminar -->
                                            <a href="<?= BASE_URL ?>/proveedor/guardar-servicio?accion=eliminar&id=<?= $servicioId ?>"
                                                class="btn btn-sm btn-outline-danger flex-fill"
                                                title="Eliminar servicio"
                                                onclick="return confirm('¿Eliminar este servicio?');">
                                                <i class="bi bi-trash3"></i> Eliminar
                                            </a>

                                            <!-- Pausar (placeholder) -->
                                            <?php if ($estado === 'aprobado') : ?>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-secondary flex-fill"
                                                    title="Pausar publicación (placeholder)">
                                                    <i class="bi bi-pause-circle"></i> Pausar
                                                </button>
                                            <?php endif; ?>

                                        </div>
                                    </div>

                                </div>
                            </div>

                        <?php endforeach; ?>


                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>

        <div class="modal fade" id="modalDetalleServicio" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-box-seam-fill me-2"></i>Detalle del Servicio
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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

                            <!-- Imagen del servicio (AQUÍ SE CARGA LA IMAGEN QUE YA NO ESTÁ EN LA TARJETA) -->
                            <div class="servicio-img-wrap mb-4">
                                <img id="modal-servicio-img" src="" alt="Imagen del servicio"
                                    onerror="this.onerror=null; this.src='<?= BASE_URL ?>/public/assets/img/default_service.png';">
                                <div class="servicio-img-overlay d-flex justify-content-between align-items-start">
                                    <span id="modal-servicio-estado" class="badge"></span>
                                    <span id="modal-servicio-disponible" class="badge"></span>
                                </div>
                            </div>

                            <!-- Título + ID -->
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
                                            <strong>Publicación:</strong> <span id="modal-servicio-estado-texto"></span><br>
                                            <strong>Disponibilidad:</strong> <span id="modal-servicio-disponible-texto"></span>
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

                            <!-- Acciones rápidas (opcional, pero útil) -->
                            <div class="d-flex gap-2 flex-wrap mt-4">
                                <a id="modal-link-editar" href="#" class="btn btn-outline-success">
                                    <i class="bi bi-pencil-square"></i> Editar
                                </a>
                                <a id="modal-link-eliminar" href="#" class="btn btn-outline-danger"
                                    onclick="return confirm('¿Eliminar este servicio?');">
                                    <i class="bi bi-trash3"></i> Eliminar
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- JS del dashboard -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboardProveedor.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/app.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>

    <script>
        // Inicializar DataTable
        $(document).ready(function() {
            $('#tabla-1').DataTable({
                responsive: true,
                // language: {
                //     url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                // }
            });
        });

        // Submenús del sidebar (si no lo tienes ya centralizado)
        document.addEventListener('DOMContentLoaded', function() {
            const toggleSubmenuButtons = document.querySelectorAll('.toggle-submenu');

            toggleSubmenuButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const contenedor = this.closest('.has-submenu');
                    if (!contenedor) return;

                    const submenu = contenedor.querySelector('.submenu');
                    contenedor.classList.toggle('active');

                    if (submenu) {
                        if (contenedor.classList.contains('active')) {
                            submenu.style.maxHeight = submenu.scrollHeight + 'px';
                        } else {
                            submenu.style.maxHeight = '0';
                        }
                    }
                });
            });
        });
    </script>




</body>

</html>