<?php
require_once BASE_PATH . '/app/helpers/lang-helper.php';
require_once BASE_PATH . '/app/controllers/proveedor-controller.php';

// Aseguramos que las variables existan para evitar errores de notice
$busqueda = $busqueda ?? '';
$catActual = $catActual ?? '';
// $publicaciones = obtenerDetallePublicacion($id) ?? [];
$categorias = obtenerCategorias();


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Explorar Servicios</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-cliente.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/explorar-servicios.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar-cliente.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-cliente.php'; ?>

        <section id="titulo-principal">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><?= __('cliente_explorar_servicios') ?></h1>
                    <p class="text-muted mb-0"><?= __('cliente_explorar_descripcion') ?></p>
                </div>
                <div class="col-md-4">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/cliente/dashboard">Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Explorar Servicios</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <section class="filtros-container mb-4">
            <div class="row g-3">
                <div class="col-md-5">
                    <form method="GET" action="<?= BASE_URL ?>/cliente/explorar-servicios" class="d-flex gap-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="q" class="form-control border-start-0 bg-light"
                                value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar servicios, proveedores...">
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                    </form>
                </div>

                <div class="col-md-7">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= BASE_URL ?>/cliente/explorar-servicios"
                            class="btn btn-outline-primary <?= $catActual === '' ? 'active' : '' ?>">
                            <i class="bi bi-grid-3x3-gap-fill"></i> Todas
                        </a>
                        <?php foreach ($categorias as $cat):
                            $catId = $cat['id'] ?? 0;
                            $catNombre = $cat['nombre'] ?? 'Categoría';
                            $icono = match (strtolower(trim($catNombre))) {
                                'hogar' => 'bi-house',
                                'tecnología', 'tecnologia' => 'bi-laptop',
                                'mascotas' => 'bi-heart',
                                'transporte' => 'bi-truck',
                                'salud' => 'bi-heart-pulse',
                                'educación', 'educacion' => 'bi-book',
                                'plomería', 'plomeria' => 'bi-wrench',
                                'electricidad' => 'bi-lightning-charge',
                                'limpieza' => 'bi-brush',
                                'pintura' => 'bi-palette',
                                'jardineria' => 'bi-tree',
                                default => 'bi-tag'
                            };
                        ?>
                            <a href="<?= BASE_URL ?>/cliente/explorar-servicios?cat=<?= $catId ?>"
                                class="btn btn-outline-primary <?= (string)$catActual === (string)$catId ? 'active' : '' ?>">
                                <i class="bi <?= $icono ?>"></i> <?= htmlspecialchars($catNombre) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <?php if (empty($publicaciones)): ?>
                <div class="empty-state">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No hay servicios disponibles</h4>
                    <p class="text-muted">No encontramos servicios que coincidan con tu búsqueda.</p>
                    <a href="<?= BASE_URL ?>/cliente/explorar-servicios" class="btn btn-outline-primary mt-2">
                        <i class="bi bi-arrow-repeat me-2"></i>Limpiar filtros
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($publicaciones as $pub):
                        $titulo = $pub['titulo'] ?? $pub['servicio_nombre'] ?? 'Servicio';
                        $descripcion = $pub['descripcion'] ?? $pub['servicio_descripcion'] ?? '';
                        $categoriaNombre = $pub['categoria_nombre'] ?? 'Sin categoría';
                        $precio = isset($pub['precio']) ? (float)$pub['precio'] : 0;
                        $imagenServicio = $pub['servicio_imagen'] ?? 'default_service.png';
                        $rutaImagen = BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($imagenServicio);
                        // ✅ CORREGIDO: usa calificación real, oculta si no hay
                        $calificacion   = (float)($pub['calificacion_promedio'] ?? 0);
                        $totalResenas   = (int)($pub['total_resenas'] ?? 0);
                        $proveedorNombre = $pub['proveedor_nombre'] ?? 'Proveedor';
                    ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card-cliente service-card h-100 d-flex flex-column">
                                <div class="service-image position-relative">
                                    <img src="<?= $rutaImagen ?>" alt="<?= htmlspecialchars($titulo) ?>" class="w-100" style="height: 180px; object-fit: cover;">
                                    <!-- ✅ Solo mostrar si tiene reseñas reales -->
                                    <?php if ($totalResenas > 0): ?>
                                        <span class="badge-categoria position-absolute top-0 end-0 m-2 bg-white text-dark shadow-sm">
                                            <i class="bi bi-star-fill text-warning"></i>
                                            <?= number_format($calificacion, 1) ?>
                                            <small class="text-muted">(<?= $totalResenas ?>)</small>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-categoria position-absolute top-0 end-0 m-2 bg-light text-muted shadow-sm">
                                            <i class="bi bi-star"></i> Sin reseñas
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="card-body p-3 flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0"><?= htmlspecialchars($titulo) ?></h6>
                                        <span class="badge bg-light text-primary border"><?= htmlspecialchars($categoriaNombre) ?></span>
                                    </div>

                                    <p class="text-muted small mb-3">
                                        <?= htmlspecialchars(mb_strimwidth($descripcion, 0, 70, '...')) ?>
                                    </p>

                                    <div class="small text-muted">
                                        <i class="bi bi-person-badge"></i> <strong><?= htmlspecialchars($proveedorNombre) ?></strong>
                                    </div>
                                </div>

                                <div class="card-footer bg-white border-0 p-3 pt-0">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <small class="text-muted">Precio desde</small>
                                        <span class="fw-bold text-primary fs-5">$<?= number_format($precio, 0, ',', '.') ?></span>
                                    </div>
                                    <!-- TARJETA — pasar TODOS los datos completos al botón -->
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-outline-primary btn-ver-detalle"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalDetalleServicio"
                                            data-id="<?= (int)$pub['id'] ?>"
                                            data-titulo="<?= htmlspecialchars($titulo) ?>"
                                            data-descripcion="<?= htmlspecialchars($descripcion) ?>"
                                            data-precio="<?= number_format($precio, 0, ',', '.') ?>"
                                            data-precio-raw="<?= $precio ?>"
                                            data-proveedor="<?= htmlspecialchars($proveedorNombre) ?>"
                                            data-categoria="<?= htmlspecialchars($categoriaNombre) ?>"
                                            data-imagen="<?= $rutaImagen ?>">
                                            <i class="bi bi-eye"></i> Ver Detalles
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <div class="modal fade modal-cliente" id="modalSolicitarServicio" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Solicitar Servicio</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>/cliente/solicitar-servicio" method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="accion" value="crear_solicitud">
                        <input type="hidden" name="id_publicacion" id="modal_servicio_id">
                        <div class="bg-light p-3 rounded-3 mb-4">
                            <small class="text-muted d-block mb-1">Estás solicitando:</small>
                            <strong id="modal_servicio_titulo" class="text-primary fs-5"></strong>
                            <p class="mb-0 mt-2"><span class="text-muted">Precio desde:</span> <strong id="modal_servicio_precio" class="text-success"></strong></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Fecha preferida <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_preferida" class="form-control" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Horario <span class="text-danger">*</span></label>
                            <select name="franja_horaria" class="form-select" required>
                                <option value="">Seleccionar horario</option>
                                <option value="mañana">Mañana (8:00 - 12:00)</option>
                                <option value="tarde">Tarde (12:00 - 18:00)</option>
                                <option value="noche">Noche (18:00 - 22:00)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Dirección <span class="text-danger">*</span></label>
                            <input type="text" name="direccion" class="form-control" placeholder="Ingresa tu dirección" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción adicional</label>
                            <textarea name="mensaje" class="form-control" rows="3" placeholder="Detalles adicionales sobre el servicio..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send me-2"></i>Enviar solicitud</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetalleServicio" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-box-seam"></i> Detalle del servicio
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0">
                    <div class="position-relative">
                        <img id="detalle_imagen" class="w-100" style="height: 200px; object-fit:cover;">
                    </div>

                    <div class="p-4">
                        <h3 id="detalle_titulo" class="fw-bold mb-1"></h3>
                        <p class="text-primary mb-3 fw-bold" id="detalle_categoria"></p>

                        <div class="d-flex align-items-center mb-4 p-2 bg-light rounded">
                            <i class="bi bi-person-circle fs-4 me-2 text-muted"></i>
                            <div>
                                <small class="d-block text-muted">Proveedor</small>
                                <strong id="detalle_proveedor"></strong>
                            </div>
                        </div>

                        <h6 class="fw-bold">Sobre este servicio</h6>
                        <p id="detalle_descripcion" class="text-muted"></p>

                        <div class="alert alert-success d-flex justify-content-between align-items-center mt-3">
                            <span>Precio base:</span>
                            <strong class="fs-4" id="detalle_precio"></strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                    <!-- ✅ CORREGIDO: id en el botón solicitar para pasarlo al siguiente modal -->
                    <button class="btn btn-primary"
                        id="btn-ir-a-solicitar"
                        data-bs-target="#modalSolicitarServicio"
                        data-bs-toggle="modal"
                        data-bs-dismiss="modal">
                        <i class="bi bi-calendar-plus"></i> Solicitar
                    </button>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/dashboard-cliente.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/explorar-servicios.js"></script>
</body>

</html>