<?php
// Proteger: solo cliente logueado
// require_once BASE_PATH . '/app/helpers/session_cliente.php';

// Modelo de solicitudes
require_once BASE_PATH . '/app/models/Solicitud.php';

$clienteId = $_SESSION['user']['id'] ?? null;

// Arrays por estado para las pestañas
$enCurso     = [];
$programados = [];
$completados = [];
$cancelados  = [];

if ($clienteId) {
    $solicitudModel = new Solicitud();
    $solicitudes    = $solicitudModel->listarPorCliente((int)$clienteId);

    foreach ($solicitudes as $sol) {
        $estado = $sol['estado'] ?? 'pendiente';

        // Normalizamos minúsculas
        $estado = strtolower($estado);

        switch ($estado) {
            case 'en_progreso':
            case 'aceptada':
                $enCurso[] = $sol;
                break;

            case 'pendiente':
                $programados[] = $sol;
                break;

            case 'finalizada':
                $completados[] = $sol;
                break;

            case 'cancelada':
            case 'rechazada':
                $cancelados[] = $sol;
                break;

            default:
                // Si llega algo raro, lo mandamos a programados por defecto
                $programados[] = $sol;
                break;
        }
    }
}
?>
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
        <h1><i class="bi bi-briefcase text-primary"></i> Servicios Contratados</h1>
        <p>Gestiona todas tus solicitudes y servicios desde aquí.</p>
      </div>

      <!-- Pestañas por estado -->
      <ul class="nav nav-tabs mb-4" id="estadoTabs" role="tablist">
        <li class="nav-item">
          <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#curso" type="button" role="tab">
            En curso
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#programado" type="button" role="tab">
            Programados
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#completado" type="button" role="tab">
            Completados
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#cancelado" type="button" role="tab">
            Cancelados
          </button>
        </li>
      </ul>

      <div class="tab-content" id="estadoTabsContent">
        <!-- ========== TAB: EN CURSO ========== -->
        <div class="tab-pane fade show active" id="curso" role="tabpanel">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($enCurso)): ?>
              <?php foreach ($enCurso as $sol): ?>
                <?php
                  $tituloServicio = $sol['publicacion_titulo'] 
                                    ?? $sol['servicio_nombre'] 
                                    ?? $sol['titulo'];

                  $proveedorNombre = $sol['proveedor_nombre'] ?? 'Proveedor sin nombre';

                  $fecha = '';
                  if (!empty($sol['fecha_preferida'])) {
                      $timestamp = strtotime($sol['fecha_preferida']);
                      if ($timestamp) {
                          $fecha = date('d/m/Y', $timestamp);
                      } else {
                          $fecha = htmlspecialchars($sol['fecha_preferida']);
                      }
                  }

                  $franja = $sol['franja_horaria'] ?? null;

                  $img = !empty($sol['servicio_imagen'])
                      ? BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($sol['servicio_imagen'])
                      : BASE_URL . '/public/assets/dashBoard/img/imagen-servicio.png';

                  // Progreso visual "falso" según estado
                  $estadoSol = strtolower($sol['estado'] ?? '');
                  $progreso  = 40;
                  $colorBar  = 'bg-info';
                  if ($estadoSol === 'aceptada') {
                      $progreso = 50;
                      $colorBar = 'bg-info';
                  } elseif ($estadoSol === 'en_progreso') {
                      $progreso = 70;
                      $colorBar = 'bg-success';
                  }
                ?>
                <div class="col">
                  <div class="card service-card estado-curso h-100">
                    <img src="<?= $img ?>" class="card-img-top" alt="Servicio">
                    <div class="card-body d-flex flex-column">
                      <h5 class="card-title"><?= htmlspecialchars($tituloServicio) ?></h5>
                      <p class="card-subtitle text-muted mb-1">
                        <i class="bi bi-person-fill"></i>
                        <?= htmlspecialchars($proveedorNombre) ?>
                      </p>

                      <?php if ($fecha): ?>
                        <p class="card-text mb-1">
                          <i class="bi bi-calendar-event"></i>
                          Fecha: <?= $fecha ?>
                          <?php if ($franja): ?>
                            · <?= htmlspecialchars($franja) ?>
                          <?php endif; ?>
                        </p>
                      <?php endif; ?>

                      <p class="card-text text-muted mb-3">
                        <?= htmlspecialchars($sol['ciudad'] ?? '') ?>
                        <?php if (!empty($sol['zona'])): ?>
                            · <?= htmlspecialchars($sol['zona']) ?>
                        <?php endif; ?>
                      </p>

                      <div class="progress mb-3" style="height: 20px;">
                        <div class="progress-bar <?= $colorBar ?>" role="progressbar"
                            style="width: <?= $progreso ?>%;"
                            aria-valuenow="<?= $progreso ?>" aria-valuemin="0" aria-valuemax="100">
                          <?= $progreso ?>%
                        </div>
                      </div>

                      <a href="#" class="btn btn-primary w-100 mt-auto">Ver detalles</a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12">
                <div class="alert alert-info">
                  No tienes servicios en curso por el momento.
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- ========== TAB: PROGRAMADOS ========== -->
        <div class="tab-pane fade" id="programado" role="tabpanel">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($programados)): ?>
              <?php foreach ($programados as $sol): ?>
                <?php
                  $tituloServicio = $sol['publicacion_titulo'] 
                                    ?? $sol['servicio_nombre'] 
                                    ?? $sol['titulo'];

                  $proveedorNombre = $sol['proveedor_nombre'] ?? 'Proveedor sin nombre';

                  $fecha = '';
                  if (!empty($sol['fecha_preferida'])) {
                      $timestamp = strtotime($sol['fecha_preferida']);
                      if ($timestamp) {
                          $fecha = date('d/m/Y', $timestamp);
                      } else {
                          $fecha = htmlspecialchars($sol['fecha_preferida']);
                      }
                  }

                  $franja = $sol['franja_horaria'] ?? null;

                  $img = !empty($sol['servicio_imagen'])
                      ? BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($sol['servicio_imagen'])
                      : BASE_URL . '/public/assets/dashBoard/img/imagen-servicio.png';
                ?>
                <div class="col">
                  <div class="card service-card estado-programado h-100">
                    <img src="<?= $img ?>" class="card-img-top" alt="Servicio">
                    <div class="card-body d-flex flex-column">
                      <h5 class="card-title"><?= htmlspecialchars($tituloServicio) ?></h5>
                      <p class="card-subtitle text-muted mb-1">
                        <i class="bi bi-person-fill"></i>
                        <?= htmlspecialchars($proveedorNombre) ?>
                      </p>

                      <?php if ($fecha): ?>
                        <p class="card-text mb-1">
                          <i class="bi bi-calendar-event"></i>
                          Programado para: <?= $fecha ?>
                          <?php if ($franja): ?>
                            · <?= htmlspecialchars($franja) ?>
                          <?php endif; ?>
                        </p>
                      <?php endif; ?>

                      <p class="card-text text-muted mb-3">
                        <?= htmlspecialchars($sol['ciudad'] ?? '') ?>
                        <?php if (!empty($sol['zona'])): ?>
                            · <?= htmlspecialchars($sol['zona']) ?>
                        <?php endif; ?>
                      </p>

                      <a href="#" class="btn btn-primary w-100 mt-auto">Ver detalles</a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12">
                <div class="alert alert-info">
                  No tienes servicios programados aún.
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- ========== TAB: COMPLETADOS ========== -->
        <div class="tab-pane fade" id="completado" role="tabpanel">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($completados)): ?>
              <?php foreach ($completados as $sol): ?>
                <?php
                  $tituloServicio = $sol['publicacion_titulo'] 
                                    ?? $sol['servicio_nombre'] 
                                    ?? $sol['titulo'];

                  $proveedorNombre = $sol['proveedor_nombre'] ?? 'Proveedor sin nombre';

                  $fecha = '';
                  if (!empty($sol['fecha_preferida'])) {
                      $timestamp = strtotime($sol['fecha_preferida']);
                      if ($timestamp) {
                          $fecha = date('d/m/Y', $timestamp);
                      } else {
                          $fecha = htmlspecialchars($sol['fecha_preferida']);
                      }
                  }

                  $img = !empty($sol['servicio_imagen'])
                      ? BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($sol['servicio_imagen'])
                      : BASE_URL . '/public/assets/dashBoard/img/imagen-servicio.png';
                ?>
                <div class="col">
                  <div class="card service-card estado-completado h-100">
                    <img src="<?= $img ?>" class="card-img-top" alt="Servicio">
                    <div class="card-body d-flex flex-column">
                      <h5 class="card-title"><?= htmlspecialchars($tituloServicio) ?></h5>
                      <p class="card-subtitle text-muted mb-1">
                        <i class="bi bi-person-fill"></i>
                        <?= htmlspecialchars($proveedorNombre) ?>
                      </p>

                      <?php if ($fecha): ?>
                        <p class="card-text mb-1">
                          <i class="bi bi-calendar-check"></i>
                          Completado el: <?= $fecha ?>
                        </p>
                      <?php endif; ?>

                      <p class="card-text text-muted mb-3">
                        <?= htmlspecialchars($sol['ciudad'] ?? '') ?>
                        <?php if (!empty($sol['zona'])): ?>
                            · <?= htmlspecialchars($sol['zona']) ?>
                        <?php endif; ?>
                      </p>

                      <!-- Aquí más adelante irá el botón de calificar -->
                      <a href="#" class="btn btn-primary w-100 mt-auto">Ver detalles</a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12">
                <div class="alert alert-info">
                  Aún no tienes servicios completados.
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- ========== TAB: CANCELADOS ========== -->
        <div class="tab-pane fade" id="cancelado" role="tabpanel">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($cancelados)): ?>
              <?php foreach ($cancelados as $sol): ?>
                <?php
                  $tituloServicio = $sol['publicacion_titulo'] 
                                    ?? $sol['servicio_nombre'] 
                                    ?? $sol['titulo'];

                  $proveedorNombre = $sol['proveedor_nombre'] ?? 'Proveedor sin nombre';

                  $fecha = '';
                  if (!empty($sol['fecha_preferida'])) {
                      $timestamp = strtotime($sol['fecha_preferida']);
                      if ($timestamp) {
                          $fecha = date('d/m/Y', $timestamp);
                      } else {
                          $fecha = htmlspecialchars($sol['fecha_preferida']);
                      }
                  }

                  $img = !empty($sol['servicio_imagen'])
                      ? BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($sol['servicio_imagen'])
                      : BASE_URL . '/public/assets/dashBoard/img/imagen-servicio.png';
                ?>
                <div class="col">
                  <div class="card service-card estado-cancelado h-100">
                    <img src="<?= $img ?>" class="card-img-top" alt="Servicio">
                    <div class="card-body d-flex flex-column">
                      <h5 class="card-title"><?= htmlspecialchars($tituloServicio) ?></h5>
                      <p class="card-subtitle text-muted mb-1">
                        <i class="bi bi-person-fill"></i>
                        <?= htmlspecialchars($proveedorNombre) ?>
                      </p>

                      <?php if ($fecha): ?>
                        <p class="card-text mb-1">
                          <i class="bi bi-calendar-x"></i>
                          Fecha original: <?= $fecha ?>
                        </p>
                      <?php endif; ?>

                      <p class="card-text text-muted mb-3">
                        <?= htmlspecialchars($sol['ciudad'] ?? '') ?>
                        <?php if (!empty($sol['zona'])): ?>
                            · <?= htmlspecialchars($sol['zona']) ?>
                        <?php endif; ?>
                      </p>

                      <a href="#" class="btn btn-outline-secondary w-100 mt-auto">Ver detalles</a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12">
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
