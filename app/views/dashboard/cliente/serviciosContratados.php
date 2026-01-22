<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proviservers | Servicios Contratados</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

  <!-- Estilos globales -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
  <!-- Estilos específicos -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardCliente.css">
</head>
<body>

  <!-- SIDEBAR -->
  <?php 
    $currentPage = 'servicios-contratados';
    include_once __DIR__ . '/../../layouts/sidebar_cliente.php'; 
  ?>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="contenido">

    <!-- HEADER -->
    <?php include_once __DIR__ . '/../../layouts/header_cliente.php'; ?>

    <section id="servicios-contratados">
      <div class="section-hero mb-4">
        <p class="breadcrumb">Inicio > Servicios Contratados</p>
        <h1><i class="bi text-primary"></i>Servicios Contratados</h1>
        <p>Gestiona todos tus servicios contratados y programados desde aquí.</p>
      </div>

      <!-- Pestañas por estado -->
      <ul class="nav nav-tabs mb-4" id="estadoTabs" role="tablist">
        <li class="nav-item">
          <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#curso" type="button">
            En curso
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#programado" type="button">
            Programados
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#completado" type="button">
            Completados
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#cancelado" type="button">
            Cancelados
          </button>
        </li>
      </ul>

      <div class="tab-content" id="estadoTabsContent">
        <!-- En curso -->
        <div class="tab-pane fade show active" id="curso">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($serviciosEnCurso)): ?>
              <?php foreach ($serviciosEnCurso as $srv): ?>
                <?php
                  $imagen = !empty($srv['servicio_imagen'])
                    ? BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($srv['servicio_imagen'])
                    : BASE_URL . '/public/assets/dashBoard/img/imagen-servicio.png';

                  $tituloServicio   = $srv['servicio_nombre']    ?? $srv['publicacion_titulo'] ?? $srv['solicitud_titulo'];
                  $proveedorNombre  = $srv['proveedor_nombre']   ?? 'Proveedor sin nombre';
                  $fechaTexto       = $srv['fecha_ejecucion']    ?: $srv['fecha_preferida'] ?: $srv['fecha_solicitud'];
                  $ciudad           = $srv['ciudad']             ?? '';
                  $zona             = $srv['zona']               ?? '';
                ?>
                <div class="col">
                  <div class="card service-card estado-curso">
                    <img src="<?= $imagen ?>" class="card-img-top" alt="Servicio">
                    <div class="card-body">
                      <h5 class="card-title"><?= htmlspecialchars($tituloServicio) ?></h5>
                      <p class="card-subtitle text-muted">
                        <i class="bi bi-person-fill"></i>
                        <?= htmlspecialchars($proveedorNombre) ?>
                      </p>
                      <?php if ($fechaTexto): ?>
                        <p class="card-text">
                          <i class="bi bi-calendar-event"></i>
                          Programado para <?= htmlspecialchars($fechaTexto) ?>
                        </p>
                      <?php endif; ?>
                      <?php if ($ciudad): ?>
                        <p class="card-text">
                          <i class="bi bi-geo-alt"></i>
                          <?= htmlspecialchars($ciudad . ($zona ? ' - ' . $zona : '')) ?>
                        </p>
                      <?php endif; ?>

                      <!-- Barra de progreso placeholder -->
                      <div class="progress mb-3" style="height: 20px;">
                        <div class="progress-bar bg-success"
                             role="progressbar"
                             style="width: 0%;"
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                          En curso
                        </div>
                      </div>

                      <a href="#" class="btn btn-primary w-100">Ver detalles</a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col">
                <div class="alert alert-info">
                  No tienes servicios en curso en este momento.
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Programados -->
        <div class="tab-pane fade" id="programado">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($serviciosProgramados)): ?>
              <?php foreach ($serviciosProgramados as $srv): ?>
                <?php
                  $imagen = !empty($srv['servicio_imagen'])
                    ? BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($srv['servicio_imagen'])
                    : BASE_URL . '/public/assets/dashBoard/img/imagen-servicio.png';

                  $tituloServicio   = $srv['servicio_nombre']    ?? $srv['publicacion_titulo'] ?? $srv['solicitud_titulo'];
                  $proveedorNombre  = $srv['proveedor_nombre']   ?? 'Proveedor sin nombre';
                  $fechaTexto       = $srv['fecha_ejecucion']    ?: $srv['fecha_preferida'] ?: $srv['fecha_solicitud'];
                  $ciudad           = $srv['ciudad']             ?? '';
                  $zona             = $srv['zona']               ?? '';
                ?>
                <div class="col">
                  <div class="card service-card estado-programado">
                    <img src="<?= $imagen ?>" class="card-img-top" alt="Servicio programado">
                    <div class="card-body">
                      <h5 class="card-title"><?= htmlspecialchars($tituloServicio) ?></h5>
                      <p class="card-subtitle text-muted">
                        <i class="bi bi-person-fill"></i>
                        <?= htmlspecialchars($proveedorNombre) ?>
                      </p>
                      <?php if ($fechaTexto): ?>
                        <p class="card-text">
                          <i class="bi bi-calendar-event"></i>
                          <?= htmlspecialchars($fechaTexto) ?>
                        </p>
                      <?php endif; ?>
                      <?php if ($ciudad): ?>
                        <p class="card-text">
                          <i class="bi bi-geo-alt"></i>
                          <?= htmlspecialchars($ciudad . ($zona ? ' - ' . $zona : '')) ?>
                        </p>
                      <?php endif; ?>
                      <a href="#" class="btn btn-primary w-100">Ver detalles</a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col">
                <div class="alert alert-info">
                  No tienes servicios programados por ahora.
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Completados -->
        <div class="tab-pane fade" id="completado">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($serviciosCompletados)): ?>
              <?php foreach ($serviciosCompletados as $srv): ?>
                <?php
                  $imagen = !empty($srv['servicio_imagen'])
                    ? BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($srv['servicio_imagen'])
                    : BASE_URL . '/public/assets/dashBoard/img/imagen-servicio.png';

                  $tituloServicio   = $srv['servicio_nombre']    ?? $srv['publicacion_titulo'] ?? $srv['solicitud_titulo'];
                  $proveedorNombre  = $srv['proveedor_nombre']   ?? 'Proveedor sin nombre';
                  $fechaTexto       = $srv['fecha_ejecucion']    ?: $srv['fecha_preferida'] ?: $srv['fecha_solicitud'];
                ?>
                <div class="col">
                  <div class="card service-card estado-completado">
                    <img src="<?= $imagen ?>" class="card-img-top" alt="Servicio completado">
                    <div class="card-body">
                      <h5 class="card-title"><?= htmlspecialchars($tituloServicio) ?></h5>
                      <p class="card-subtitle text-muted">
                        <i class="bi bi-person-fill"></i>
                        <?= htmlspecialchars($proveedorNombre) ?>
                      </p>
                      <?php if ($fechaTexto): ?>
                        <p class="card-text">
                          <i class="bi bi-calendar-check"></i>
                          Completado el <?= htmlspecialchars($fechaTexto) ?>
                        </p>
                      <?php endif; ?>
                      <a href="#" class="btn btn-primary w-100">Ver detalles</a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col">
                <div class="alert alert-info">
                  Aún no tienes servicios completados.
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Cancelados -->
        <div class="tab-pane fade" id="cancelado">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($serviciosCancelados)): ?>
              <?php foreach ($serviciosCancelados as $srv): ?>
                <?php
                  $imagen = !empty($srv['servicio_imagen'])
                    ? BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($srv['servicio_imagen'])
                    : BASE_URL . '/public/assets/dashBoard/img/imagen-servicio.png';

                  $tituloServicio   = $srv['servicio_nombre']    ?? $srv['publicacion_titulo'] ?? $srv['solicitud_titulo'];
                  $proveedorNombre  = $srv['proveedor_nombre']   ?? 'Proveedor sin nombre';
                  $fechaTexto       = $srv['fecha_ejecucion']    ?: $srv['fecha_preferida'] ?: $srv['fecha_solicitud'];
                ?>
                <div class="col">
                  <div class="card service-card estado-cancelado">
                    <img src="<?= $imagen ?>" class="card-img-top" alt="Servicio cancelado">
                    <div class="card-body">
                      <h5 class="card-title"><?= htmlspecialchars($tituloServicio) ?></h5>
                      <p class="card-subtitle text-muted">
                        <i class="bi bi-person-fill"></i>
                        <?= htmlspecialchars($proveedorNombre) ?>
                      </p>
                      <?php if ($fechaTexto): ?>
                        <p class="card-text">
                          <i class="bi bi-calendar-x"></i>
                          Cancelado el <?= htmlspecialchars($fechaTexto) ?>
                        </p>
                      <?php endif; ?>
                      <a href="#" class="btn btn-primary w-100">Ver detalles</a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col">
                <div class="alert alert-info">
                  No tienes servicios cancelados.
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
