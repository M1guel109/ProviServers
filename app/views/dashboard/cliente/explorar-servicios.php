<?php
require_once BASE_PATH . '/app/helpers/lang-helper.php';
require_once BASE_PATH . '/app/models/categoria.php';

// Aseguramos que las variables existan para evitar errores de notice
$busqueda        = $busqueda        ?? '';
$catActual       = $catActual       ?? '';
$ciudad          = $ciudad          ?? '';
$precioMax       = $precioMax       ?? null;
$orden           = $orden           ?? 'recientes';
$soloOfertas     = $soloOfertas     ?? false;
$calificacionMin = $calificacionMin ?? null;
$lat             = $lat             ?? null;
$lng             = $lng             ?? null;
$radioKm         = $radioKm         ?? 10;
$publicaciones   = $publicaciones   ?? [];
$filtroPorCoordenadas = $lat !== null && $lng !== null;
$objCategoria = new Categoria();
$categorias = $objCategoria->mostrar() ?: [];


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Explorar Servicios</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-cliente.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/explorar-servicios.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <style>
        #mapa-servicios { height: 520px; border-radius: 12px; display: none; }
        .leaflet-popup-content h6 { margin: 0 0 4px; font-size: .9rem; }
        .leaflet-popup-content .precio { color: #198754; font-weight: 700; }
    </style>
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
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/cliente/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Explorar Servicios</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <section class="filtros-container mb-4">
            <form method="GET" action="<?= BASE_URL ?>/cliente/explorar-servicios" id="form-filtros">
                <!-- Inputs ocultos para filtro por coordenadas -->
                <input type="hidden" name="lat"   id="input-lat"   value="<?= $lat !== null ? htmlspecialchars((string)$lat) : '' ?>">
                <input type="hidden" name="lng"   id="input-lng"   value="<?= $lng !== null ? htmlspecialchars((string)$lng) : '' ?>">

                <!-- Fila 1: búsqueda + ciudad + precio + orden -->
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="q" class="form-control border-start-0 bg-light"
                                value="<?= htmlspecialchars($busqueda) ?>"
                                placeholder="Buscar servicios o categoría...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-geo-alt text-muted"></i>
                            </span>
                            <input type="text" name="ciudad" class="form-control border-start-0 bg-light"
                                value="<?= htmlspecialchars($ciudad) ?>"
                                placeholder="Ciudad o zona...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 small">$</span>
                            <input type="number" name="precio_max" class="form-control border-start-0 bg-light"
                                value="<?= $precioMax !== null ? (int)$precioMax : '' ?>"
                                placeholder="Precio máx." min="0" step="1000">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="estrellas" class="form-select bg-light" onchange="this.form.submit()"
                                title="Calificación mínima">
                            <option value=""  <?= $calificacionMin === null ? 'selected' : '' ?>>⭐ Todas las estrellas</option>
                            <option value="1" <?= $calificacionMin == 1 ? 'selected' : '' ?>>⭐ 1+ estrellas</option>
                            <option value="2" <?= $calificacionMin == 2 ? 'selected' : '' ?>>⭐⭐ 2+ estrellas</option>
                            <option value="3" <?= $calificacionMin == 3 ? 'selected' : '' ?>>⭐⭐⭐ 3+ estrellas</option>
                            <option value="4" <?= $calificacionMin == 4 ? 'selected' : '' ?>>⭐⭐⭐⭐ 4+ estrellas</option>
                            <option value="5" <?= $calificacionMin == 5 ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ Solo 5 estrellas</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <select name="orden" class="form-select bg-light">
                            <option value="recientes"   <?= $orden === 'recientes'   ? 'selected' : '' ?>>Más recientes</option>
                            <option value="precio_asc"  <?= $orden === 'precio_asc'  ? 'selected' : '' ?>>Precio: menor</option>
                            <option value="precio_desc" <?= $orden === 'precio_desc' ? 'selected' : '' ?>>Precio: mayor</option>
                            <option value="valorados"   <?= $orden === 'valorados'   ? 'selected' : '' ?>>Mejor valorados</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                </div>

                <!-- Fila 1b: Cerca de mí + radio (solo cuando hay coords activas) -->
                <div class="row g-2 mb-2 align-items-center">
                    <div class="col-auto">
                        <button type="button" id="btn-cerca-mi" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-crosshair me-1"></i>Cerca de mí
                        </button>
                        <?php if ($filtroPorCoordenadas): ?>
                        <button type="button" class="btn btn-outline-danger btn-sm ms-1" id="btn-quitar-coords"
                                title="Quitar filtro de ubicación">
                            <i class="bi bi-x-circle me-1"></i>Quitar ubicación
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="col-auto" id="contenedor-radio" <?= $filtroPorCoordenadas ? '' : 'style="display:none"' ?>>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="bi bi-arrows-angle-expand"></i></span>
                            <select name="radio" class="form-select form-select-sm bg-light" style="min-width:120px">
                                <option value="5"  <?= $radioKm == 5  ? 'selected' : '' ?>>5 km</option>
                                <option value="10" <?= $radioKm == 10 ? 'selected' : '' ?>>10 km</option>
                                <option value="25" <?= $radioKm == 25 ? 'selected' : '' ?>>25 km</option>
                                <option value="50" <?= $radioKm == 50 ? 'selected' : '' ?>>50 km</option>
                            </select>
                        </div>
                    </div>
                    <?php if ($filtroPorCoordenadas): ?>
                    <div class="col-auto">
                        <small class="text-success"><i class="bi bi-geo-fill me-1"></i>Filtrando por tu ubicación (<?= $radioKm ?> km)</small>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Fila 2: filtros de categoría + En oferta -->
                <div class="d-flex flex-wrap gap-2">
                    <?php
                        $qsBase = http_build_query(array_filter([
                            'q'          => $busqueda,
                            'ciudad'     => $ciudad,
                            'precio_max' => $precioMax ? (int)$precioMax : '',
                            'orden'      => $orden !== 'recientes' ? $orden : '',
                            'estrellas'  => $calificacionMin ? (int)$calificacionMin : '',
                            'lat'        => $lat !== null ? (string)$lat : '',
                            'lng'        => $lng !== null ? (string)$lng : '',
                            'radio'      => $filtroPorCoordenadas ? (string)$radioKm : '',
                        ]));
                        $qsOfertas = $qsBase ? $qsBase . '&ofertas=1' : 'ofertas=1';
                    ?>
                    <a href="<?= BASE_URL ?>/cliente/explorar-servicios<?= $qsBase ? '?'.$qsBase : '' ?>"
                        class="btn btn-outline-primary <?= $catActual === '' && !$soloOfertas ? 'active' : '' ?>">
                        <i class="bi bi-grid-3x3-gap-fill"></i> Todas
                    </a>
                    <a href="<?= BASE_URL ?>/cliente/explorar-servicios?<?= $qsOfertas ?>"
                        class="btn <?= $soloOfertas ? 'btn-danger' : 'btn-outline-danger' ?>">
                        <i class="bi bi-tag-fill"></i> En oferta
                    </a>
                    <?php foreach ($categorias as $cat):
                        $catId = $cat['id'] ?? 0;
                        $catNombre = $cat['nombre'] ?? 'Categoría';
                        $icono = match (strtolower(trim($catNombre))) {
                            'hogar'                          => 'bi-house',
                            'tecnología', 'tecnologia'       => 'bi-laptop',
                            'mascotas'                       => 'bi-heart',
                            'transporte'                     => 'bi-truck',
                            'salud'                          => 'bi-heart-pulse',
                            'educación', 'educacion'         => 'bi-book',
                            'plomería', 'plomeria'           => 'bi-wrench',
                            'electricidad'                   => 'bi-lightning-charge',
                            'limpieza'                       => 'bi-brush',
                            'pintura'                        => 'bi-palette',
                            'jardineria'                     => 'bi-tree',
                            default                          => 'bi-tag'
                        };
                        // Preservar búsqueda y ciudad al cambiar categoría
                        $qsCat = http_build_query(array_filter([
                            'cat'       => $catId,
                            'q'         => $busqueda,
                            'ciudad'    => $ciudad,
                            'precio_max'=> $precioMax ? (int)$precioMax : '',
                            'orden'     => $orden !== 'recientes' ? $orden : '',
                            'ofertas'   => $soloOfertas ? '1' : '',
                            'estrellas' => $calificacionMin ? (int)$calificacionMin : '',
                            'lat'       => $lat !== null ? (string)$lat : '',
                            'lng'       => $lng !== null ? (string)$lng : '',
                            'radio'     => $filtroPorCoordenadas ? (string)$radioKm : '',
                        ]));
                    ?>
                        <a href="<?= BASE_URL ?>/cliente/explorar-servicios?<?= $qsCat ?>"
                            class="btn btn-outline-primary <?= (string)$catActual === (string)$catId ? 'active' : '' ?>">
                            <i class="bi <?= $icono ?>"></i> <?= htmlspecialchars($catNombre) ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Badge de filtros activos -->
                <?php
                $filtrosActivos = array_filter([
                    $busqueda              ? "Búsqueda: \"$busqueda\""                                   : null,
                    $ciudad                ? "Ciudad: \"$ciudad\""                                        : null,
                    $precioMax             ? 'Precio máx: $'.number_format((int)$precioMax, 0, ',', '.') : null,
                    $catActual             ? 'Categoría seleccionada'                                    : null,
                    $soloOfertas           ? 'Solo ofertas activas'                                      : null,
                    $calificacionMin       ? str_repeat('⭐', (int)$calificacionMin) . '+ estrellas'     : null,
                    $filtroPorCoordenadas  ? "Cerca de mí ({$radioKm} km)"                              : null,
                ]);
                if ($filtrosActivos): ?>
                <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
                    <small class="text-muted">Filtros activos:</small>
                    <?php foreach ($filtrosActivos as $f): ?>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                            <?= htmlspecialchars($f) ?>
                        </span>
                    <?php endforeach; ?>
                    <a href="<?= BASE_URL ?>/cliente/explorar-servicios" class="small text-danger ms-1">
                        <i class="bi bi-x-circle me-1"></i>Limpiar todo
                    </a>
                    <span class="ms-auto small text-muted"><?= count($publicaciones) ?> resultado(s)</span>
                </div>
                <?php endif; ?>
            </form>
        </section>

        <!-- Toggle vista -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted small"><?= count($publicaciones) ?> servicio(s) encontrado(s)</span>
            <div class="btn-group btn-group-sm" role="group">
                <button id="btn-grid" class="btn btn-primary active" onclick="toggleVista('grid')">
                    <i class="bi bi-grid-3x3-gap-fill me-1"></i>Tarjetas
                </button>
                <button id="btn-mapa" class="btn btn-outline-primary" onclick="toggleVista('mapa')">
                    <i class="bi bi-map me-1"></i>Mapa
                </button>
            </div>
        </div>

        <!-- Panel de filtros del mapa (visible solo cuando el mapa está activo) -->
        <div id="panel-filtros-mapa" class="card border-0 shadow-sm mb-2" style="display:none">
            <div class="card-body py-2">
                <div class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="bi bi-geo-alt"></i></span>
                            <input type="text" id="mapa-ciudad" class="form-control bg-light"
                                placeholder="Ciudad o zona..." value="<?= htmlspecialchars($ciudad) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="mapa-categoria" class="form-select form-select-sm bg-light">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>"
                                    <?= (string)$catActual === (string)$cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="mapa-radio" class="form-select form-select-sm bg-light">
                            <option value="5"  <?= $radioKm == 5  ? 'selected' : '' ?>>5 km</option>
                            <option value="10" <?= $radioKm == 10 ? 'selected' : '' ?>>10 km</option>
                            <option value="25" <?= $radioKm == 25 ? 'selected' : '' ?>>25 km</option>
                            <option value="50" <?= $radioKm == 50 ? 'selected' : '' ?>>50 km</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="button" id="mapa-btn-cerca" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-crosshair me-1"></i>Cerca de mí
                        </button>
                        <button type="button" id="mapa-btn-buscar" class="btn btn-primary btn-sm ms-1">
                            <i class="bi bi-search me-1"></i>Buscar
                        </button>
                        <button type="button" id="mapa-btn-limpiar" class="btn btn-outline-danger btn-sm ms-1">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                    <div class="col-auto ms-auto">
                        <span id="mapa-contador" class="badge bg-primary rounded-pill">— proveedores</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mapa Leaflet -->
        <div id="mapa-servicios" class="mb-4 shadow-sm"></div>

        <section>
            <?php if (empty($publicaciones)): ?>
                <div id="resultados-grid" class="empty-state">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No hay servicios disponibles</h4>
                    <p class="text-muted">No encontramos servicios que coincidan con tu búsqueda.</p>
                    <a href="<?= BASE_URL ?>/cliente/explorar-servicios" class="btn btn-outline-primary mt-2">
                        <i class="bi bi-arrow-repeat me-2"></i>Limpiar filtros
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4" id="resultados-grid">
                    <?php foreach ($publicaciones as $pub):
                        $titulo = $pub['titulo'] ?? $pub['servicio_nombre'] ?? 'Servicio';
                        $descripcion = $pub['descripcion'] ?? $pub['servicio_descripcion'] ?? '';
                        $categoriaNombre = $pub['categoria_nombre'] ?? 'Sin categoría';
                        $precio = isset($pub['precio']) ? (float)$pub['precio'] : 0;
                        $imagenServicio = $pub['servicio_imagen'] ?? 'default_service.png';
                        $rutaImagen = BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($imagenServicio);
                        // ✅ CORREGIDO: usa calificación real, oculta si no hay
                        $calificacion     = (float)($pub['calificacion_promedio'] ?? 0);
                        $totalResenas     = (int)($pub['total_resenas'] ?? 0);
                        $proveedorNombre  = $pub['proveedor_nombre']   ?? 'Proveedor';
                        $proveedorCiudad  = $pub['proveedor_ciudad']   ?? '';
                        $proveedorZona    = $pub['proveedor_zona']     ?? '';
                        $ubicacion        = trim($proveedorCiudad . ($proveedorZona ? ' — ' . $proveedorZona : ''));
                        $descuento        = (int)($pub['promo_descuento'] ?? 0);
                        $precioFinal      = $descuento > 0 ? round($precio * (1 - $descuento / 100)) : $precio;
                        $promoHasta       = $pub['promo_hasta'] ?? null;
                    ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card-cliente service-card h-100 d-flex flex-column">
                                <div class="service-image position-relative">
                                    <img src="<?= $rutaImagen ?>" alt="<?= htmlspecialchars($titulo) ?>" class="w-100" style="height: 180px; object-fit: cover;">
                                    <!-- ✅ Solo mostrar si tiene reseñas reales -->
                                    <?php if ($descuento > 0): ?>
                                        <span class="position-absolute top-0 start-0 m-2 badge bg-danger fs-6 fw-bold">
                                            -<?= $descuento ?>%
                                        </span>
                                    <?php endif; ?>
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

                                    <p class="text-muted small mb-2">
                                        <?= htmlspecialchars(mb_strimwidth($descripcion, 0, 70, '...')) ?>
                                    </p>

                                    <?php if ($ubicacion): ?>
                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-geo-alt me-1 text-primary"></i>
                                        <?= htmlspecialchars($ubicacion) ?>
                                    </p>
                                    <?php endif; ?>

                                    <div class="small text-muted">
                                        <i class="bi bi-person-badge"></i> <strong><?= htmlspecialchars($proveedorNombre) ?></strong>
                                    </div>
                                </div>

                                <div class="card-footer bg-white border-0 p-3 pt-0">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <?php if ($descuento > 0): ?>
                                            <div>
                                                <small class="text-muted text-decoration-line-through d-block">
                                                    $<?= number_format($precio, 0, ',', '.') ?>
                                                </small>
                                                <span class="fw-bold text-danger fs-5">$<?= number_format($precioFinal, 0, ',', '.') ?></span>
                                                <?php if ($promoHasta): ?>
                                                    <small class="text-muted d-block" style="font-size:.7rem;">
                                                        Hasta <?= date('d/m/Y', strtotime($promoHasta)) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <small class="text-muted">Precio desde</small>
                                            <span class="fw-bold text-primary fs-5">$<?= number_format($precio, 0, ',', '.') ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <a href="<?= BASE_URL ?>/cliente/publicacion?id=<?= (int)$pub['id'] ?>"
                                           class="btn btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver Detalles
                                        </a>
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
                <form action="<?= BASE_URL ?>/cliente/guardar-solicitud" method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">

                        <!-- ✅ AGREGAR: acción y campo correcto -->
                        <input type="hidden" name="accion" value="guardar_solicitud_cliente">
                        <input type="hidden" name="publicacion_id" id="modal_servicio_id">

                        <!-- ✅ AGREGAR: titulo (auto-generado del servicio) -->
                        <input type="hidden" name="titulo" id="modal_servicio_titulo_input">

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
                            <input type="date" name="fecha_preferida" class="form-control"
                                min="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Horario <span class="text-danger">*</span></label>
                            <select name="franja_horaria" class="form-select" required>
                                <option value="">Seleccionar horario</option>
                                <option value="manana">Mañana (8:00 - 12:00)</option>
                                <option value="tarde">Tarde (12:00 - 18:00)</option>
                                <option value="noche">Noche (18:00 - 22:00)</option>
                            </select>
                        </div>

                        <!-- ✅ AGREGAR: ciudad obligatoria -->
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Dirección <span class="text-danger">*</span></label>
                                <input type="text" name="direccion" class="form-control"
                                    placeholder="Calle 123 # 45-67" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Ciudad <span class="text-danger">*</span></label>
                                <input type="text" name="ciudad" class="form-control"
                                    placeholder="Bogotá" required>
                            </div>
                        </div>

                        <!-- ✅ Cambiar 'mensaje' por 'descripcion' -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Descripción del trabajo <span class="text-danger">*</span>
                            </label>
                            <textarea name="descripcion" class="form-control" rows="3"
                                placeholder="Cuéntale al proveedor qué necesitas..." required></textarea>
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

    <div class="modal fade modal-cliente" id="modalDetalleServicio" tabindex="-1" aria-hidden="true">
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <?php
    // Construir array de marcadores para el mapa
    $marcadores = [];
    foreach ($publicaciones as $pub) {
        $lat = (float)($pub['proveedor_lat'] ?? 0);
        $lng = (float)($pub['proveedor_lng'] ?? 0);
        if ($lat == 0 && $lng == 0) continue;
        $descuento   = (int)($pub['promo_descuento'] ?? 0);
        $precioBase  = (float)($pub['precio'] ?? 0);
        $precioFinal = $descuento > 0 ? round($precioBase * (1 - $descuento / 100)) : $precioBase;
        $marcadores[] = [
            'lat'       => $lat,
            'lng'       => $lng,
            'titulo'    => $pub['titulo'] ?? $pub['servicio_nombre'] ?? 'Servicio',
            'precio'    => '$' . number_format($precioFinal, 0, ',', '.'),
            'proveedor' => $pub['proveedor_nombre'] ?? '',
            'ciudad'    => trim(($pub['proveedor_ciudad'] ?? '') . ($pub['proveedor_zona'] ? ' — ' . $pub['proveedor_zona'] : '')),
            'url'       => BASE_URL . '/cliente/publicacion?id=' . $pub['id'],
            'descuento' => $descuento,
        ];
    }
    ?>
    <script>
    const BASE_URL_JS = '<?= BASE_URL ?>';
    let mapaLeaflet     = null;
    let mapaCapaMarcadores = null;
    let mapaCapaCirculo    = null;
    let mapaUserLat     = <?= $lat !== null ? (float)$lat : 'null' ?>;
    let mapaUserLng     = <?= $lng !== null ? (float)$lng : 'null' ?>;

    // ── Toggle tarjetas / mapa ──────────────────────────────────────
    function toggleVista(vista) {
        const gridEl   = document.getElementById('resultados-grid') || document.querySelector('.empty-state');
        const mapaEl   = document.getElementById('mapa-servicios');
        const panelEl  = document.getElementById('panel-filtros-mapa');
        const btnGrid  = document.getElementById('btn-grid');
        const btnMapa  = document.getElementById('btn-mapa');

        if (vista === 'mapa') {
            if (gridEl)  gridEl.style.display  = 'none';
            mapaEl.style.display  = 'block';
            panelEl.style.display = 'block';
            btnGrid.classList.replace('btn-primary','btn-outline-primary');
            btnMapa.classList.replace('btn-outline-primary','btn-primary');
            iniciarMapa();
            cargarDatosMapa();
        } else {
            mapaEl.style.display  = 'none';
            panelEl.style.display = 'none';
            if (gridEl) gridEl.style.display = '';
            btnMapa.classList.replace('btn-primary','btn-outline-primary');
            btnGrid.classList.replace('btn-outline-primary','btn-primary');
        }
    }

    // ── Inicializar mapa Leaflet (solo la primera vez) ──────────────
    function iniciarMapa() {
        if (mapaLeaflet) { mapaLeaflet.invalidateSize(); return; }

        mapaLeaflet = L.map('mapa-servicios').setView([4.5709, -74.2973], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 18
        }).addTo(mapaLeaflet);
        L.control.scale({ imperial: false }).addTo(mapaLeaflet);

        mapaCapaMarcadores = L.layerGroup().addTo(mapaLeaflet);
    }

    // ── Cargar datos del endpoint y plotear ─────────────────────────
    async function cargarDatosMapa(lat = mapaUserLat, lng = mapaUserLng) {
        const ciudad  = document.getElementById('mapa-ciudad').value.trim();
        const cat     = document.getElementById('mapa-categoria').value;
        const radio   = document.getElementById('mapa-radio').value;
        const contador = document.getElementById('mapa-contador');

        const params = new URLSearchParams();
        if (ciudad)          params.set('ciudad', ciudad);
        if (cat)             params.set('cat', cat);
        if (lat !== null && lng !== null) {
            params.set('lat', lat);
            params.set('lng', lng);
            params.set('radio', radio);
        }

        contador.textContent = 'Cargando...';
        contador.className   = 'badge bg-secondary rounded-pill';

        try {
            const res  = await fetch(`${BASE_URL_JS}/cliente/mapa/datos?${params.toString()}`);
            const data = await res.json();
            if (!data.ok) throw new Error('Respuesta no ok');
            plotearMarcadores(data.marcadores, lat, lng, parseInt(radio));
            contador.textContent = `${data.total} proveedor${data.total !== 1 ? 'es' : ''}`;
            contador.className   = 'badge bg-primary rounded-pill';
        } catch (e) {
            contador.textContent = 'Error al cargar';
            contador.className   = 'badge bg-danger rounded-pill';
        }
    }

    // ── Plotear marcadores y círculo de radio ───────────────────────
    function plotearMarcadores(marcadores, lat, lng, radioKm) {
        mapaCapaMarcadores.clearLayers();
        if (mapaCapaCirculo) { mapaCapaCirculo.remove(); mapaCapaCirculo = null; }

        const bounds = [];

        // Punto del usuario
        if (lat !== null && lng !== null) {
            L.circleMarker([lat, lng], {
                radius: 8, color: '#0d6efd', fillColor: '#0d6efd', fillOpacity: 0.7
            }).addTo(mapaCapaMarcadores).bindPopup('<b>Tu ubicación</b>');

            mapaCapaCirculo = L.circle([lat, lng], {
                radius:      radioKm * 1000,
                color:       '#0d6efd',
                weight:      2,
                opacity:     0.5,
                fillColor:   '#0d6efd',
                fillOpacity: 0.05
            }).addTo(mapaLeaflet);

            bounds.push([lat, lng]);
        }

        marcadores.forEach(m => {
            const badge  = m.descuento > 0
                ? `<span style="background:#dc3545;color:#fff;padding:1px 6px;border-radius:4px;font-size:.75rem;">-${m.descuento}%</span> `
                : '';
            const estrellas = m.calificacion > 0
                ? `<span style="color:#f59e0b;font-size:.78rem;">${'★'.repeat(Math.round(m.calificacion))} ${m.calificacion}</span>`
                : '';
            const precio = '$' + Number(m.precio).toLocaleString('es-CO');

            const popup = `
                <div style="min-width:190px;">
                    <h6 style="margin:0 0 4px;">${m.titulo}</h6>
                    <p style="margin:2px 0;font-size:.78rem;color:#555;">
                        <i class="bi bi-grid"></i> ${m.servicio} &nbsp;·&nbsp; ${m.categoria}
                    </p>
                    <p style="margin:2px 0;font-size:.8rem;color:#555;">
                        <i class="bi bi-person-fill"></i> ${m.proveedor}
                    </p>
                    ${m.ciudad ? `<p style="margin:2px 0;font-size:.78rem;color:#888;">📍 ${m.ciudad}</p>` : ''}
                    ${estrellas ? `<p style="margin:2px 0;">${estrellas} <span style="font-size:.72rem;color:#999;">(${m.resenas})</span></p>` : ''}
                    <p style="margin:6px 0;font-weight:700;color:#198754;">${badge}${precio}</p>
                    <a href="${m.url}" style="display:inline-block;padding:4px 12px;background:#0d6efd;color:#fff;border-radius:6px;text-decoration:none;font-size:.8rem;">
                        Ver servicio
                    </a>
                </div>`;

            L.marker([m.lat, m.lng]).addTo(mapaCapaMarcadores).bindPopup(popup);
            bounds.push([m.lat, m.lng]);
        });

        if (mapaCapaCirculo) {
            mapaLeaflet.fitBounds(mapaCapaCirculo.getBounds(), { padding: [30, 30] });
        } else if (bounds.length > 0) {
            mapaLeaflet.fitBounds(bounds, { padding: [40, 40] });
        }
    }

    // ── Controles del panel del mapa ────────────────────────────────
    document.getElementById('mapa-btn-buscar').addEventListener('click', () => {
        mapaUserLat = null; mapaUserLng = null;
        cargarDatosMapa(null, null);
    });

    document.getElementById('mapa-btn-limpiar').addEventListener('click', () => {
        document.getElementById('mapa-ciudad').value    = '';
        document.getElementById('mapa-categoria').value = '';
        mapaUserLat = null; mapaUserLng = null;
        cargarDatosMapa(null, null);
    });

    document.getElementById('mapa-btn-cerca').addEventListener('click', function () {
        if (!navigator.geolocation) { alert('Tu navegador no soporta geolocalización.'); return; }
        const btn = this;
        btn.disabled    = true;
        btn.innerHTML   = '<span class="spinner-border spinner-border-sm me-1"></span>Localizando...';
        navigator.geolocation.getCurrentPosition(
            pos => {
                mapaUserLat = pos.coords.latitude;
                mapaUserLng = pos.coords.longitude;
                btn.disabled  = false;
                btn.innerHTML = '<i class="bi bi-crosshair me-1"></i>Cerca de mí';
                cargarDatosMapa(mapaUserLat, mapaUserLng);
            },
            () => {
                btn.disabled  = false;
                btn.innerHTML = '<i class="bi bi-crosshair me-1"></i>Cerca de mí';
                alert('No se pudo obtener tu ubicación. Verifica los permisos del navegador.');
            },
            { timeout: 10000 }
        );
    });

    // ── Botón "Cerca de mí" del formulario principal ────────────────
    document.getElementById('btn-cerca-mi').addEventListener('click', function () {
        if (!navigator.geolocation) { alert('Tu navegador no soporta geolocalización.'); return; }
        const btn = this;
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Obteniendo ubicación...';
        navigator.geolocation.getCurrentPosition(
            pos => {
                document.getElementById('input-lat').value = pos.coords.latitude;
                document.getElementById('input-lng').value = pos.coords.longitude;
                document.getElementById('contenedor-radio').style.display = '';
                document.getElementById('form-filtros').submit();
            },
            () => {
                btn.disabled  = false;
                btn.innerHTML = '<i class="bi bi-crosshair me-1"></i>Cerca de mí';
                alert('No se pudo obtener tu ubicación. Verifica los permisos del navegador.');
            },
            { timeout: 10000 }
        );
    });

    // ── Botón "Quitar ubicación" del formulario principal ───────────
    const btnQuitarCoords = document.getElementById('btn-quitar-coords');
    if (btnQuitarCoords) {
        btnQuitarCoords.addEventListener('click', () => {
            document.getElementById('input-lat').value = '';
            document.getElementById('input-lng').value = '';
            document.getElementById('form-filtros').submit();
        });
    }
    </script>

    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/dashboard-cliente.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/explorar-servicios.js"></script>
</body>

</html>