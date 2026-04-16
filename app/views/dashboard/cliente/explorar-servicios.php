<?php
require_once BASE_PATH . '/app/models/categoria.php';
require_once BASE_PATH . '/app/helpers/lang-helper.php';

$objCategoria = new Categoria();
$categorias = $objCategoria->mostrar() ?: [];
$catActual = $_GET['cat'] ?? '';
$busqueda = $_GET['q'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Explorar Servicios</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-cliente.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar-cliente.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-cliente.php'; ?>

        <!-- TÍTULO CON BREADCRUMB -->
        <section id="titulo-principal">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><?= __('cliente_explorar_servicios') ?></h1>
                    <p class="text-muted mb-0">
                        <?= __('cliente_explorar_descripcion') ?>
                    </p>
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

        <!-- FILTROS Y BÚSQUEDA -->
        <section class="filtros-container mb-4">
            <div class="row g-3">
                <div class="col-md-5">
                    <form method="GET" action="<?= BASE_URL ?>/cliente/explorar-servicios" class="d-flex gap-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" 
                                   name="q" 
                                   class="form-control border-start-0 bg-light" 
                                   value="<?= htmlspecialchars($busqueda) ?>"
                                   placeholder="Buscar servicios, proveedores...">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                        </button>
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
                            $icono = match(strtolower(trim($catNombre))) {
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

        <!-- RESULTADOS -->
        <section>
            <?php if (empty($publicaciones)): ?>
                <div class="empty-state">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No hay servicios disponibles</h4>
                    <p class="text-muted">No encontramos servicios que coincidan con tu búsqueda. Prueba con otros filtros.</p>
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
                        $calificacion = $pub['calificacion'] ?? 4.5;
                        $proveedorNombre = $pub['proveedor_nombre'] ?? 'Proveedor';
                    ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card-cliente service-card h-100">
                                <div class="service-image position-relative">
                                    <img src="<?= $rutaImagen ?>" 
                                         alt="<?= htmlspecialchars($titulo) ?>" 
                                         class="w-100" 
                                         style="height: 200px; object-fit: cover;">
                                    <span class="badge-categoria position-absolute top-0 end-0 m-2">
                                        <i class="bi bi-star-fill text-warning"></i> <?= number_format($calificacion, 1) ?>
                                    </span>
                                </div>
                                
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0"><?= htmlspecialchars($titulo) ?></h6>
                                        <span class="badge-estado badge-disponible">Disponible</span>
                                    </div>
                                    
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <i class="bi bi-tag text-primary"></i>
                                        <span class="text-muted small"><?= htmlspecialchars($categoriaNombre) ?></span>
                                    </div>
                                    
                                    <p class="text-muted small mb-3" style="line-height: 1.5;">
                                        <?= htmlspecialchars(mb_strimwidth($descripcion, 0, 120, '...')) ?>
                                    </p>
                                    
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="icono-wrapper bg-primary-light p-2 rounded-3">
                                            <i class="bi bi-person-circle text-primary"></i>
                                        </div>
                                        <div>
                                            <span class="d-block fw-semibold small">Proveedor</span>
                                            <span class="text-muted small"><?= htmlspecialchars($proveedorNombre) ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                        <div>
                                            <small class="text-muted d-block">Desde</small>
                                            <span class="fw-bold text-primary fs-5">$<?= number_format($precio, 0, ',', '.') ?></span>
                                        </div>
                                        <button class="btn-card btn-card-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalSolicitarServicio"
                                                data-servicio-id="<?= $pub['id'] ?>"
                                                data-servicio-titulo="<?= htmlspecialchars($titulo) ?>"
                                                data-servicio-precio="<?= $precio ?>">
                                            <i class="bi bi-calendar-plus"></i> Solicitar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Paginación (si aplica) -->
                <?php if (isset($pagination) && $pagination['total_paginas'] > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $pagination['total_paginas']; $i++): ?>
                            <li class="page-item <?= $i == $pagination['pagina_actual'] ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&cat=<?= $catActual ?>&q=<?= urlencode($busqueda) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </section>

    </main>

    <!-- MODAL SOLICITAR SERVICIO -->
    <div class="modal fade modal-cliente" id="modalSolicitarServicio" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-plus me-2"></i>Solicitar Servicio
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>/cliente/solicitar-servicio" method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="id_publicacion" id="modal_servicio_id">
                        
                        <div class="bg-light p-3 rounded-3 mb-4">
                            <small class="text-muted d-block mb-1">Estás solicitando:</small>
                            <strong id="modal_servicio_titulo" class="text-primary fs-5"></strong>
                            <p class="mb-0 mt-2">
                                <span class="text-muted">Precio desde:</span>
                                <strong id="modal_servicio_precio" class="text-success"></strong>
                            </p>
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
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Enviar solicitud
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Pasar datos al modal
        const modalSolicitar = document.getElementById('modalSolicitarServicio');
        if (modalSolicitar) {
            modalSolicitar.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const servicioId = button.getAttribute('data-servicio-id');
                const servicioTitulo = button.getAttribute('data-servicio-titulo');
                const servicioPrecio = button.getAttribute('data-servicio-precio');
                
                document.getElementById('modal_servicio_id').value = servicioId;
                document.getElementById('modal_servicio_titulo').textContent = servicioTitulo;
                document.getElementById('modal_servicio_precio').textContent = 
                    '$' + parseInt(servicioPrecio).toLocaleString('es-CO');
            });
        }
    </script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/dashboard-cliente.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
</body>

</html>