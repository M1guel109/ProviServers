<?php
// Vista: app/views/dashboard/cliente/explorarServicios.php
// Variables disponibles desde el controlador:
// $publicaciones, $categorias, $busqueda, $categoriaId

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

  <!-- CONTENIDO PRINCIAL -->
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
        <form class="d-flex gap-2"
              method="GET"
              action="<?= BASE_URL ?>/cliente/explorar">
          <input 
              type="text" 
              class="form-control" 
              name="q"
              placeholder="Buscar servicios, proveedores..."
              value="<?= htmlspecialchars($busqueda ?? '') ?>">
          <?php if (!empty($categoriaId)): ?>
              <input type="hidden" name="categoria" value="<?= (int)$categoriaId ?>">
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
          <a href="<?= BASE_URL ?>/cliente/explorar<?= $busqueda ? ('?q=' . urlencode($busqueda)) : '' ?>"
             class="btn btn-outline-primary <?= empty($categoriaId) ? 'active' : '' ?>">
            <i class="bi bi-columns-gap"></i> Todas
          </a>

          <?php if (!empty($categorias)): ?>
            <?php foreach ($categorias as $cat): ?>
              <?php
                $isActive = (!empty($categoriaId) && (int)$categoriaId === (int)$cat['id']);
                // Construimos la URL preservando la búsqueda
                $query = [
                  'categoria' => $cat['id'],
                ];
                if (!empty($busqueda)) {
                    $query['q'] = $busqueda;
                }
                $url = BASE_URL . '/cliente/explorar?' . http_build_query($query);
              ?>
              <a href="<?= $url ?>"
                 class="btn btn-outline-primary <?= $isActive ? 'active' : '' ?>">
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
              // Imagen
              $imagen = !empty($pub['servicio_imagen'])
                ? BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($pub['servicio_imagen'])
                : BASE_URL . '/public/assets/dashBoard/img/imagen-servicio.png';

              // Título (publicación o servicio)
              $titulo = !empty($pub['titulo'])
                ? $pub['titulo']
                : ($pub['servicio_nombre'] ?? 'Servicio');

              // Proveedor
              $proveedorNombre = $pub['proveedor_nombre'] ?? 'Proveedor';

              // Descripción corta
              $descripcion = $pub['descripcion'] ?? '';
              if (mb_strlen($descripcion) > 120) {
                  $descripcion = mb_substr($descripcion, 0, 117) . '...';
              }

              // Ubicación
              $ubicacion = $pub['ciudad'] ?? '';
              if (!empty($pub['zona'])) {
                  $ubicacion = trim($ubicacion . ' - ' . $pub['zona']);
              }
              if ($ubicacion === '') {
                  $ubicacion = 'Ubicación no especificada';
              }

              // Categoría
              $categoriaNombre = $pub['categoria_nombre'] ?? 'Sin categoría';

              // Precio (si lo estás usando)
              $precio = isset($pub['precio']) ? (float)$pub['precio'] : 0.0;
            ?>
            <div class="col-md-4">
              <div class="card service-card h-100">
                <div class="service-image">
                  <img src="<?= $imagen ?>" alt="<?= htmlspecialchars($titulo) ?>">
                </div>
                <div class="card-body service-content d-flex flex-column">
                  <h5 class="card-title"><?= htmlspecialchars($titulo) ?></h5>
                  <p class="card-subtitle mb-1">
                    Proveedor: <?= htmlspecialchars($proveedorNombre) ?>
                  </p>

                  <?php if ($precio > 0): ?>
                    <p class="card-text mb-1">
                      <strong>Desde:</strong> $ <?= number_format($precio, 0, ',', '.') ?>
                    </p>
                  <?php endif; ?>

                  <p class="card-text flex-grow-1">
                    <?= htmlspecialchars($descripcion) ?>
                  </p>

                  <p class="card-location mb-1">
                    <i class="bi bi-geo-alt"></i>
                    <?= htmlspecialchars($ubicacion) ?>
                  </p>

                  <p class="card-category mb-1">
                    <i class="bi bi-tags"></i>
                    Categoría: <?= htmlspecialchars($categoriaNombre) ?>
                  </p>

                  <!-- Por ahora sin calificaciones reales -->
                  <p class="card-rating mb-3">
                    <i class="bi bi-star-fill text-warning"></i>
                    Sin calificaciones aún
                  </p>

                  <!-- Botón para avanzar al detalle / contratación -->
                  <!-- Más adelante lo cambiamos a la ruta de detalle o creación de solicitud -->
                  <a href="#"
                     class="btn btn-primary w-100 mt-auto">
                    Contratar Servicio
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="alert alert-info">
              No encontramos servicios activos con los filtros actuales.
              Intenta limpiar la búsqueda o cambiar de categoría.
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
