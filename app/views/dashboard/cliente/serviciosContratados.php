<?php
// app/views/dashboard/cliente/serviciosContratados.php

// Proteger vista: solo cliente logueado
// require_once BASE_PATH . '/app/helpers/session_cliente.php';

// Modelo de servicios contratados
require_once BASE_PATH . '/app/models/ServicioContratado.php';

// ID de usuario en sesión
$usuarioId = $_SESSION['user']['id'] ?? null;
$serviciosContratados = [];

if ($usuarioId) {
    $modelSC = new ServicioContratado();
    $serviciosContratados = $modelSC->listarPorClienteUsuario((int)$usuarioId);
}

// Agrupar por estado_ui para las pestañas
$grupos = [
    'en_curso'   => [],
    'programado' => [],
    'completado' => [],
    'cancelado'  => [],
];

foreach ($serviciosContratados as $row) {
    $estadoUi = $row['estado_ui'] ?? 'en_curso';
    if (!isset($grupos[$estadoUi])) {
        $grupos[$estadoUi] = [];
    }
    $grupos[$estadoUi][] = $row;
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
        <h1><i class="bi bi-clipboard-check text-primary"></i> Servicios Contratados</h1>
        <p>Gestiona todos tus servicios aceptados, en curso y finalizados desde aquí.</p>
      </div>

      <!-- Pestañas por estado -->
      <ul class="nav nav-tabs mb-4" id="estadoTabs" role="tablist">
        <li class="nav-item">
          <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-curso" type="button">
            En curso
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-programado" type="button">
            Programados
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-completado" type="button">
            Completados
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-cancelado" type="button">
            Cancelados
          </button>
        </li>
      </ul>

      <div class="tab-content" id="estadoTabsContent">
        <!-- ===================== EN CURSO ===================== -->
        <div class="tab-pane fade show active" id="tab-curso">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($grupos['en_curso'])): ?>
              <?php foreach ($grupos['en_curso'] as $item): ?>
                <?php
                  $tituloServicio   = $item['publicacion_titulo'] ?: $item['servicio_nombre'];
                  $proveedorNombre  = $item['proveedor_nombre'] ?? 'Proveedor';
                  $fechaPreferida   = $item['fecha_preferida'] ?? null;
                  $franjaHoraria    = $item['franja_horaria'] ?? null;
                  $imagenServicio   = $item['servicio_imagen'] ?? null;
                  $ciudad           = $item['ciudad'] ?? '';
                  $zona             = $item['zona'] ?? '';
                  $progreso         = 50; // puedes ajustar la lógica real de progreso
                ?>
                <div class="col">
                  <div class="card service-card estado-curso">
                    <?php if ($imagenServicio): ?>
                      <img src="<?= BASE_URL ?>/public/uploads/servicios/<?= htmlspecialchars($imagenServicio) ?>" 
                           class="card-img-top" 
                           alt="<?= htmlspecialchars($tituloServicio) ?>">
                    <?php else: ?>
                      <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/imagen-servicio.png" 
                           class="card-img-top" 
                           alt="Servicio">
                    <?php endif; ?>
                    <div class="card-body">
                      <h5 class="card-title"><?= htmlspecialchars($tituloServicio) ?></h5>
                      <p class="card-subtitle text-muted">
                        <i class="bi bi-person-fill"></i> <?= htmlspecialchars($proveedorNombre) ?>
                      </p>
                      <?php if ($fechaPreferida): ?>
                        <p class="card-text mb-1">
                          <i class="bi bi-calendar-event"></i>
                          Servicio para el <?= htmlspecialchars($fechaPreferida) ?>
                          <?php if ($franjaHoraria): ?>
                            (<?= htmlspecialchars($franjaHoraria) ?>)
                          <?php endif; ?>
                        </p>
                      <?php endif; ?>
                      <?php if ($ciudad): ?>
                        <p class="card-text mb-2">
                          <i class="bi bi-geo-alt"></i>
                          <?= htmlspecialchars($ciudad . ($zona ? ' - ' . $zona : '')) ?>
                        </p>
                      <?php endif; ?>

                      <div class="progress mb-3" style="height: 20px;">
                        <div class="progress-bar bg-success"
                             role="progressbar"
                             style="width: <?= (int)$progreso ?>%;"
                             aria-valuenow="<?= (int)$progreso ?>"
                             aria-valuemin="0"
                             aria-valuemax="100">
                          <?= (int)$progreso ?>%
                        </div>
                      </div>

                      <a href="#"
                         class="btn btn-primary w-100">
                        Ver detalles
                      </a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12">
                <div class="alert alert-light border text-center">
                  <p class="mb-0">No tienes servicios en curso en este momento.</p>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- ===================== PROGRAMADOS ===================== -->
        <div class="tab-pane fade" id="tab-programado">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($grupos['programado'])): ?>
              <?php foreach ($grupos['programado'] as $item): ?>
                <?php
                  $tituloServicio   = $item['publicacion_titulo'] ?: $item['servicio_nombre'];
                  $proveedorNombre  = $item['proveedor_nombre'] ?? 'Proveedor';
                  $fechaPreferida   = $item['fecha_preferida'] ?? null;
                  $franjaHoraria    = $item['franja_horaria'] ?? null;
                  $imagenServicio   = $item['servicio_imagen'] ?? null;
                  $ciudad           = $item['ciudad'] ?? '';
                  $zona             = $item['zona'] ?? '';
                ?>
                <div class="col">
                  <div class="card service-card estado-programado">
                    <?php if ($imagenServicio): ?>
                      <img src="<?= BASE_URL ?>/public/uploads/servicios/<?= htmlspecialchars($imagenServicio) ?>" 
                           class="card-img-top" 
                           alt="<?= htmlspecialchars($tituloServicio) ?>">
                    <?php else: ?>
                      <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/imagen-servicio.png" 
                           class="card-img-top" 
                           alt="Servicio">
                    <?php endif; ?>
                    <div class="card-body">
                      <h5 class="card-title"><?= htmlspecialchars($tituloServicio) ?></h5>
                      <p class="card-subtitle text-muted">
                        <i class="bi bi-person-fill"></i> <?= htmlspecialchars($proveedorNombre) ?>
                      </p>
                      <?php if ($fechaPreferida): ?>
                        <p class="card-text mb-1">
                          <i class="bi bi-calendar-event"></i>
                          Programado para el <?= htmlspecialchars($fechaPreferida) ?>
                          <?php if ($franjaHoraria): ?>
                            (<?= htmlspecialchars($franjaHoraria) ?>)
                          <?php endif; ?>
                        </p>
                      <?php endif; ?>
                      <?php if ($ciudad): ?>
                        <p class="card-text mb-2">
                          <i class="bi bi-geo-alt"></i>
                          <?= htmlspecialchars($ciudad . ($zona ? ' - ' . $zona : '')) ?>
                        </p>
                      <?php endif; ?>

                      <a href="#"
                         class="btn btn-primary w-100">
                        Ver detalles
                      </a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12">
                <div class="alert alert-light border text-center">
                  <p class="mb-0">No tienes servicios programados próximos.</p>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- ===================== COMPLETADOS ===================== -->
        <div class="tab-pane fade" id="tab-completado">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($grupos['completado'])): ?>
              <?php foreach ($grupos['completado'] as $item): ?>
                <?php
                  $tituloServicio   = $item['publicacion_titulo'] ?: $item['servicio_nombre'];
                  $proveedorNombre  = $item['proveedor_nombre'] ?? 'Proveedor';
                  $fechaPreferida   = $item['fecha_preferida'] ?? null;
                  $imagenServicio   = $item['servicio_imagen'] ?? null;
                  $ciudad           = $item['ciudad'] ?? '';
                  $zona             = $item['zona'] ?? '';
                ?>
                <div class="col">
                  <div class="card service-card estado-completado">
                    <?php if ($imagenServicio): ?>
                      <img src="<?= BASE_URL ?>/public/uploads/servicios/<?= htmlspecialchars($imagenServicio) ?>" 
                           class="card-img-top" 
                           alt="<?= htmlspecialchars($tituloServicio) ?>">
                    <?php else: ?>
                      <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/imagen-servicio.png" 
                           class="card-img-top" 
                           alt="Servicio">
                    <?php endif; ?>
                    <div class="card-body">
                      <h5 class="card-title"><?= htmlspecialchars($tituloServicio) ?></h5>
                      <p class="card-subtitle text-muted">
                        <i class="bi bi-person-fill"></i> <?= htmlspecialchars($proveedorNombre) ?>
                      </p>
                      <?php if ($fechaPreferida): ?>
                        <p class="card-text mb-1">
                          <i class="bi bi-calendar-check"></i>
                          Servicio realizado el <?= htmlspecialchars($fechaPreferida) ?>
                        </p>
                      <?php endif; ?>
                      <?php if ($ciudad): ?>
                        <p class="card-text mb-2">
                          <i class="bi bi-geo-alt"></i>
                          <?= htmlspecialchars($ciudad . ($zona ? ' - ' . $zona : '')) ?>
                        </p>
                      <?php endif; ?>

                      <!-- En el futuro aquí irá el botón de calificación -->
                      <a href="#" class="btn btn-outline-primary w-100">
                        Ver detalles
                      </a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12">
                <div class="alert alert-light border text-center">
                  <p class="mb-0">Aún no tienes servicios completados.</p>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- ===================== CANCELADOS ===================== -->
        <div class="tab-pane fade" id="tab-cancelado">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($grupos['cancelado'])): ?>
              <?php foreach ($grupos['cancelado'] as $item): ?>
                <?php
                  $tituloServicio   = $item['publicacion_titulo'] ?: $item['servicio_nombre'];
                  $proveedorNombre  = $item['proveedor_nombre'] ?? 'Proveedor';
                  $fechaPreferida   = $item['fecha_preferida'] ?? null;
                  $imagenServicio   = $item['servicio_imagen'] ?? null;
                  $ciudad           = $item['ciudad'] ?? '';
                  $zona             = $item['zona'] ?? '';
                ?>
                <div class="col">
                  <div class="card service-card estado-cancelado">
                    <?php if ($imagenServicio): ?>
                      <img src="<?= BASE_URL ?>/public/uploads/servicios/<?= htmlspecialchars($imagenServicio) ?>" 
                           class="card-img-top" 
                           alt="<?= htmlspecialchars($tituloServicio) ?>">
                    <?php else: ?>
                      <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/imagen-servicio.png" 
                           class="card-img-top" 
                           alt="Servicio">
                    <?php endif; ?>
                    <div class="card-body">
                      <h5 class="card-title"><?= htmlspecialchars($tituloServicio) ?></h5>
                      <p class="card-subtitle text-muted">
                        <i class="bi bi-person-fill"></i> <?= htmlspecialchars($proveedorNombre) ?>
                      </p>
                      <?php if ($fechaPreferida): ?>
                        <p class="card-text mb-1">
                          <i class="bi bi-calendar-x"></i>
                          Servicio cancelado (fecha planificada: <?= htmlspecialchars($fechaPreferida) ?>)
                        </p>
                      <?php endif; ?>
                      <?php if ($ciudad): ?>
                        <p class="card-text mb-2">
                          <i class="bi bi-geo-alt"></i>
                          <?= htmlspecialchars($ciudad . ($zona ? ' - ' . $zona : '')) ?>
                        </p>
                      <?php endif; ?>

                      <a href="#" class="btn btn-outline-secondary w-100">
                        Ver detalles
                      </a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12">
                <div class="alert alert-light border text-center">
                  <p class="mb-0">No hay servicios cancelados.</p>
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
