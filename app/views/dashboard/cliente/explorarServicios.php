<?php
// Esta vista asume que llega $publicaciones desde el controlador
// y que NO requiere sesión de cliente para ver el catálogo
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Explorar Servicios</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <!-- Estilos específicos de cliente -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboardCliente.css">
</head>

<body>
    <!-- SIDEBAR -->
    <?php
    $currentPage = 'explorar';
    include_once __DIR__ . '/../../layouts/sidebar_cliente.php';
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido">

        <!-- HEADER -->
        <?php include_once __DIR__ . '/../../layouts/header_cliente.php'; ?>

        <section id="explorar">
            <div class="section-hero mb-4">
                <p class="breadcrumb">Inicio &gt; Explorar Servicios</p>
                <h1>Explorar Servicios</h1>
                <p>Descubre profesionales verificados listos para ayudarte.</p>
            </div>

            <!-- Buscador -->
            <div class="mb-4">
                <form
                    class="d-flex gap-2"
                    method="GET"
                    action="<?= BASE_URL ?>/cliente/explorar-servicios">
                    <input
                        type="text"
                        class="form-control"
                        name="q"
                        value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                        placeholder="Buscar servicios, proveedores...">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>

            <!-- Filtros de categorías (por ahora estáticos; luego se pueden hacer dinámicos) -->
            <div class="mb-4 category-filters">
                <div class="d-flex flex-wrap gap-2">
                    <?php
                    $catActual = $_GET['cat'] ?? '';
                    ?>
                    <a
                        href="<?= BASE_URL ?>/cliente/explorar-servicios"
                        class="btn btn-outline-primary <?= $catActual === '' ? 'active' : '' ?>">
                        <i class="bi bi-columns-gap"></i> Todas
                    </a>

                    <!-- Ejemplos de categorías fijas: luego puedes mapear IDs reales -->
                    <a
                        href="<?= BASE_URL ?>/cliente/explorar-servicios?cat=1"
                        class="btn btn-outline-primary <?= $catActual == '1' ? 'active' : '' ?>">
                        <i class="bi bi-house"></i> Hogar
                    </a>
                    <a
                        href="<?= BASE_URL ?>/cliente/explorar-servicios?cat=2"
                        class="btn btn-outline-primary <?= $catActual == '2' ? 'active' : '' ?>">
                        <i class="bi bi-laptop"></i> Tecnología
                    </a>
                    <a
                        href="<?= BASE_URL ?>/cliente/explorar-servicios?cat=3"
                        class="btn btn-outline-primary <?= $catActual == '3' ? 'active' : '' ?>">
                        <i class="bi bi-heart"></i> Mascotas
                    </a>
                    <a
                        href="<?= BASE_URL ?>/cliente/explorar-servicios?cat=4"
                        class="btn btn-outline-primary <?= $catActual == '4' ? 'active' : '' ?>">
                        <i class="bi bi-truck"></i> Transporte
                    </a>
                    <a
                        href="<?= BASE_URL ?>/cliente/explorar-servicios?cat=5"
                        class="btn btn-outline-primary <?= $catActual == '5' ? 'active' : '' ?>">
                        <i class="bi bi-heart-pulse"></i> Salud
                    </a>
                </div>
            </div>

            <!-- Tarjetas de servicios (publicaciones aprobadas) -->
            <div class="row g-4" id="contenedor-servicios">
                <?php if (!empty($publicaciones)) : ?>
                    <?php foreach ($publicaciones as $pub) : ?>
                        <?php
                        $titulo          = $pub['titulo'] ?? $pub['servicio_nombre'] ?? 'Servicio';
                        $descripcion     = $pub['descripcion'] ?? $pub['servicio_descripcion'] ?? '';
                        $categoriaNombre = $pub['categoria_nombre'] ?? 'Sin categoría';
                        $precio          = isset($pub['precio']) ? (float)$pub['precio'] : 0.0;
                        $imagenServicio  = $pub['servicio_imagen'] ?? 'default_service.png';

                        // Ruta de imagen del servicio
                        $rutaImagen = BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($imagenServicio);
                        ?>
                        <div class="col-md-4">
                            <div class="card service-card h-100">
                                <div class="service-image">
                                    <img
                                        src="<?= $rutaImagen ?>"
                                        alt="<?= htmlspecialchars($titulo) ?>"
                                        style="width: 100%; height: 200px; object-fit: cover;">
                                </div>
                                <div class="card-body service-content d-flex flex-column">
                                    <h5 class="card-title">
                                        <?= htmlspecialchars($titulo) ?>
                                    </h5>

                                    <p class="card-category mb-1">
                                        <strong>Categoría:</strong>
                                        <?= htmlspecialchars($categoriaNombre) ?>
                                    </p>

                                    <?php if ($precio > 0) : ?>
                                        <p class="mb-1">
                                            <strong>Desde:</strong>
                                            $ <?= number_format($precio, 0, ',', '.') ?>
                                        </p>
                                    <?php endif; ?>

                                    <?php if (!empty($descripcion)) : ?>
                                        <p class="card-text flex-grow-1">
                                            <?= nl2br(htmlspecialchars(mb_strimwidth($descripcion, 0, 160, '...'))) ?>
                                        </p>
                                    <?php else : ?>
                                        <p class="card-text flex-grow-1 text-muted">
                                            El proveedor aún no ha detallado la descripción de este servicio.
                                        </p>
                                    <?php endif; ?>

                                    <!-- Placeholder de rating por ahora -->
                                    <p class="card-rating mb-2">
                                        ⭐ 4.8/5 <!-- Luego esto vendrá de tabla de reseñas -->
                                    </p>

                                    <!-- CTA: contratar / ver detalle -->
                                    <a
                                        href="<?= BASE_URL ?>/cliente/detalle-servicio?id=<?= $pub['id'] ?>"
                                        class="btn btn-primary w-100 mt-auto">
                                        Ver detalles y contratar
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            No encontramos servicios publicados que coincidan con tu búsqueda.
                            <br>
                            Prueba cambiando los filtros o la palabra clave.
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </section>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JS propio -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboardCliente.js"></script>
</body>

</html>
