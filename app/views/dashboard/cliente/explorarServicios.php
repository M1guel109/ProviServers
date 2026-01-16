<?php
// Proteger: solo cliente logueado
require_once BASE_PATH . '/app/helpers/session_cliente.php';

// Modelos necesarios
require_once BASE_PATH . '/app/models/Publicacion.php';
require_once BASE_PATH . '/app/models/categoria.php';

// Filtros desde la URL
$busquedaActual   = trim($_GET['q'] ?? '');
$categoriaActual  = $_GET['categoria'] ?? '';
$categoriaId      = $categoriaActual !== '' ? (int)$categoriaActual : null;

// Cargar publicaciones activas para el catálogo
$publicacionModel = new Publicacion();
$publicaciones    = $publicacionModel->listarPublicasActivas($busquedaActual, $categoriaId);

// Cargar categorías para los filtros
$categoriaModel = new Categoria();
$categorias     = $categoriaModel->mostrar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proviservers | Explorar servicios</title>

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
        <p class="breadcrumb">Inicio > Explorar Servicios</p>
        <h1>Explorar Servicios</h1>
        <p>Descubre profesionales verificados listos para ayudarte.</p>
      </div>

      <!-- Buscador -->
      <div class="mb-4">
        <form class="d-flex gap-2" method="GET" action="<?= BASE_URL ?>/cliente/explorar-servicios">
          <input 
            type="text" 
            class="form-control" 
            name="q"
            placeholder="Buscar servicios, proveedores..." 
            value="<?= htmlspecialchars($busquedaActual) ?>"
          >
          <?php if ($categoriaActual !== ''): ?>
            <input type="hidden" name="categoria" value="<?= htmlspecialchars($categoriaActual) ?>">
          <?php endif; ?>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-search"></i>
          </button>
        </form>
      </div>

      <!-- Filtros de categorías -->
      <div class="mb-4 category-filters">
        <div class="d-flex flex-wrap gap-2">
          <!-- Todas -->
          <a 
            href="<?= BASE_URL ?>/cliente/explorar-servicios<?= $busquedaActual !== '' ? '?q=' . urlencode($busquedaActual) : '' ?>" 
            class="btn btn-outline-primary <?= $categoriaActual === '' ? 'active' : '' ?>"
          >
            <i class="bi bi-columns-gap"></i> Todas
          </a>

          <?php if (!empty($categorias)): ?>
            <?php foreach ($categorias as $cat): ?>
              <?php
                $url = BASE_URL . '/cliente/explorar-servicios?categoria=' . $cat['id'];
                if ($busquedaActual !== '') {
                    $url .= '&q=' . urlencode($busquedaActual);
                }
              ?>
              <a 
                href="<?= $url ?>" 
                class="btn btn-outline-primary <?= ($categoriaActual !== '' && (int)$categoriaActual === (int)$cat['id']) ? 'active' : '' ?>"
              >
                <?= htmlspecialchars($cat['nombre']) ?>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Tarjetas de servicios -->
      <div class="row g-4" id="contenedor-servicios">

        <?php if (!empty($publicaciones)): ?>
          <?php foreach ($publicaciones as $pub): ?>
            <?php
              // Imagen del servicio
              $imagenServicio = $pub['servicio_imagen'] ?? 'default_service.png';

              // Descripción corta
              $descripcion = $pub['descripcion'] ?? '';
              if (strlen($descripcion) > 140) {
                  $descripcion = substr($descripcion, 0, 137) . '...';
              }

              // Precio (opcional)
              $precio = null;
              if (isset($pub['precio']) && (float)$pub['precio'] > 0) {
                  $precio = number_format((float)$pub['precio'], 0, ',', '.');
              }
            ?>
            <div class="col-md-4">
              <div class="card service-card h-100">
                <div class="service-image">
                  <img 
                    src="<?= BASE_URL ?>/public/uploads/servicios/<?= htmlspecialchars($imagenServicio) ?>" 
                    alt="<?= htmlspecialchars($pub['servicio_nombre'] ?? $pub['titulo']) ?>"
                  >
                </div>
                <div class="card-body service-content d-flex flex-column">
                  <h5 class="card-title">
                    <?= htmlspecialchars($pub['titulo'] ?? $pub['servicio_nombre'] ?? 'Servicio') ?>
                  </h5>
                  <p class="card-subtitle">
                    Proveedor: <?= htmlspecialchars($pub['proveedor_nombre'] ?? 'No especificado') ?>
                  </p>

                  <p class="card-text flex-grow-1">
                    <?= htmlspecialchars($descripcion !== '' ? $descripcion : 'Este proveedor aún no ha agregado una descripción detallada.') ?>
                  </p>

                  <p class="card-location mb-1">
                    <strong>Ubicación:</strong>
                    <?= htmlspecialchars($pub['ubicacion'] ?? 'No especificada') ?>
                  </p>

                  <p class="card-category mb-1">
                    <strong>Categoría:</strong>
                    <?= htmlspecialchars($pub['categoria_nombre'] ?? 'Sin categoría') ?>
                  </p>

                  <?php if ($precio !== null): ?>
                    <p class="card-price mb-1">
                      <strong>Desde:</strong> $ <?= $precio ?>
                    </p>
                  <?php endif; ?>

                  <!-- Por ahora, calificación estática / placeholder -->
                  <p class="card-rating mb-3">
                    ⭐ Sin calificaciones aún
                  </p>

                  <!-- Botón para ir al detalle del servicio/publicación -->
                  <a 
                    href="<?= BASE_URL ?>/cliente/detalle-servicio?id=<?= (int)$pub['id'] ?>" 
                    class="btn btn-primary w-100 mt-auto"
                  >
                    Ver detalles y contratar
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="alert alert-light border text-center" role="alert">
              <h5 class="mb-1">No encontramos servicios para tu búsqueda.</h5>
              <p class="mb-0" style="font-size: 0.9rem;">
                Intenta con otros términos o quita algunos filtros de categoría.
              </p>
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
