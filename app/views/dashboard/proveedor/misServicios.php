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

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- css del dashboard -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardTable.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/registrar-servicio.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/listar-servicio.css">

 
</head>

<body>
    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_proveedor.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header_proveedor.php'; ?>

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

            <a href="<?= BASE_URL ?>/proveedor/reporte?tipo=serviciosProveedor"
                target="_blank"
                rel="noopener noreferrer"
                class="btn btn-primary mt-3">
                <i class="bi bi-file-earmark-pdf-fill"></i> Generar Reporte PDF
            </a>
        </section>

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

                        // Texto y estilos por estado
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
                            : (BASE_URL . '/public/assets/img/default_service.png');

                        $servicioId = (int)($fila['servicio_id'] ?? 0);

                        // Descripción full y corta
                        $descFull = trim((string)($fila['servicio_descripcion'] ?? 'Sin descripción'));
                        $descShort = $descFull;
                        if (mb_strlen($descShort) > 120) $descShort = mb_substr($descShort, 0, 120) . '...';

                        $fechaPub = $fila['publicacion_created_at'] ?? '';
                        $fechaLabel = ($estado === 'aprobado') ? 'Publicado' : 'Creado';

                        // Payload para el modal
                        $payload = [
                            'id' => $servicioId,
                            'nombre' => $fila['servicio_nombre'] ?? '',
                            'categoria' => $fila['categoria_nombre'] ?? 'Sin categoría',
                            'estado' => $estado,
                            'estadoTexto' => $textoEstado,
                            'disponible' => $disponible ? 1 : 0,
                            'imgUrl' => $imgUrl,
                            'descFull' => $descFull,
                            'descShort' => $descShort,
                            'fecha' => $fechaPub,
                            'fechaLabel' => $fechaLabel,
                        ];

                        $dataServicio = htmlspecialchars(json_encode($payload, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                        ?>

                        <div class="col">
                            <div class="card card-servicio h-100 border-0 shadow-sm">

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

                                    <!-- Descripción resumida -->
                                    <p class="card-text text-secondary small mb-3">
                                        <?= htmlspecialchars($descShort) ?>
                                    </p>

                                    <!-- Fecha -->
                                    <div class="meta-row text-muted small">
                                        <i class="bi bi-calendar3"></i>
                                        <span><?= htmlspecialchars($fechaLabel) ?>: <?= htmlspecialchars($fechaPub) ?></span>
                                    </div>
                                </div>

                                <!-- Acciones -->
                                <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                    <div class="d-flex gap-2 flex-wrap">

                                        <!-- VER (abre modal) -->
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary flex-fill btn-ver-servicio"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalServicio"
                                            data-servicio="<?= $dataServicio ?>"
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

                                        <!-- Eliminar (GET como lo tienes; recomendado migrar a POST+CSRF) -->
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

                </div>

            <?php endif; ?>

        </section>

    </main>

    <!-- MODAL ÚNICO REUTILIZABLE -->
    <div class="modal fade" id="modalServicio" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1" id="modalServicioTitulo">Detalle del servicio</h5>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge" id="modalServicioEstado"></span>
                            <span class="badge" id="modalServicioDisponibilidad"></span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Imagen SOLO en modal -->
                        <div class="col-12 col-md-5">
                            <div class="modal-img-wrap">
                                <img id="modalServicioImg" src="" alt="Imagen del servicio">
                            </div>
                        </div>

                        <div class="col-12 col-md-7">
                            <div class="text-muted small mb-2">
                                <i class="bi bi-tag"></i>
                                <span id="modalServicioCategoria"></span>
                            </div>

                            <div class="text-muted small mb-3">
                                <i class="bi bi-calendar3"></i>
                                <span id="modalServicioFecha"></span>
                            </div>

                            <h6 class="fw-bold mb-2">Descripción</h6>
                            <p class="text-secondary mb-0" id="modalServicioDesc"></p>
                        </div>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between flex-wrap gap-2">
                    <div class="text-muted small" id="modalServicioMeta"></div>

                    <div class="d-flex gap-2 flex-wrap">
                        <a id="modalBtnEditar" href="#" class="btn btn-outline-success btn-sm d-none">
                            <i class="bi bi-pencil-square"></i> Editar
                        </a>

                        <a id="modalBtnEliminar" href="#" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash3"></i> Eliminar
                        </a>

                        <button id="modalBtnPausar" type="button" class="btn btn-outline-secondary btn-sm d-none">
                            <i class="bi bi-pause-circle"></i> Pausar
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- JS del dashboard -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/app.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>

    <script>
        // Modal: llenar contenido dinámicamente desde data-servicio
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('modalServicio');

            modalEl.addEventListener('show.bs.modal', function(event) {
                const btn = event.relatedTarget;
                if (!btn) return;

                const raw = btn.getAttribute('data-servicio');
                if (!raw) return;

                let s;
                try {
                    s = JSON.parse(raw);
                } catch (e) {
                    return;
                }

                // Título
                document.getElementById('modalServicioTitulo').textContent = s.nombre || 'Detalle del servicio';

                // Estado badge
                const estadoBadge = document.getElementById('modalServicioEstado');
                estadoBadge.textContent = s.estadoTexto || s.estado || '';
                estadoBadge.className = 'badge ' + (
                    s.estado === 'aprobado' ? 'bg-success' :
                    s.estado === 'rechazada' ? 'bg-danger' :
                    s.estado === 'pausada' ? 'bg-secondary' :
                    'bg-warning text-dark'
                );

                // Disponibilidad badge
                const dispBadge = document.getElementById('modalServicioDisponibilidad');
                if (Number(s.disponible) === 1) {
                    dispBadge.textContent = 'Disponible';
                    dispBadge.className = 'badge bg-primary';
                } else {
                    dispBadge.textContent = 'No disponible';
                    dispBadge.className = 'badge bg-dark';
                }

                // Imagen
                const img = document.getElementById('modalServicioImg');
                img.src = s.imgUrl || '<?= BASE_URL ?>/public/assets/img/default_service.png';
                img.onerror = function() {
                    this.onerror = null;
                    this.src = '<?= BASE_URL ?>/public/assets/img/default_service.png';
                };

                // Datos
                document.getElementById('modalServicioCategoria').textContent = s.categoria || 'Sin categoría';
                document.getElementById('modalServicioFecha').textContent = (s.fechaLabel ? (s.fechaLabel + ': ') : 'Fecha: ') + (s.fecha || '—');
                document.getElementById('modalServicioDesc').textContent = s.descFull || 'Sin descripción';

                // Acciones
                const btnEditar = document.getElementById('modalBtnEditar');
                const btnEliminar = document.getElementById('modalBtnEliminar');
                const btnPausar = document.getElementById('modalBtnPausar');

                // Editar solo si pendiente/rechazada
                if (s.estado === 'pendiente' || s.estado === 'rechazada') {
                    btnEditar.classList.remove('d-none');
                    btnEditar.href = '<?= BASE_URL ?>/proveedor/editar-servicio?id=' + s.id;
                } else {
                    btnEditar.classList.add('d-none');
                    btnEditar.href = '#';
                }

                // Eliminar (GET como lo tienes)
                btnEliminar.href = '<?= BASE_URL ?>/proveedor/guardar-servicio?accion=eliminar&id=' + s.id;

                // Pausar (placeholder) solo si aprobado
                if (s.estado === 'aprobado') {
                    btnPausar.classList.remove('d-none');
                } else {
                    btnPausar.classList.add('d-none');
                }

                // Meta
                document.getElementById('modalServicioMeta').textContent = 'ID Servicio: ' + (s.id ?? '—');
            });

            // Submenús del sidebar (si NO está centralizado en tus JS)
            const toggleSubmenuButtons = document.querySelectorAll('.toggle-submenu');
            toggleSubmenuButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const contenedor = this.closest('.has-submenu');
                    if (!contenedor) return;

                    const submenu = contenedor.querySelector('.submenu');
                    contenedor.classList.toggle('active');

                    if (submenu) {
                        submenu.style.maxHeight = contenedor.classList.contains('active') ? (submenu.scrollHeight + 'px') : '0';
                    }
                });
            });
        });
    </script>
</body>

</html>
