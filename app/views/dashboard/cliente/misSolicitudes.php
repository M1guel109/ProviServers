<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proviservers | Mis Solicitudes</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardCliente.css">
</head>
<body>

<?php
  $currentPage = 'mis-solicitudes';
  include_once __DIR__ . '/../../layouts/sidebar_cliente.php';
?>

<main class="contenido">

  <?php include_once __DIR__ . '/../../layouts/header_cliente.php'; ?>

  <section class="mt-2">
    <div class="section-hero mb-4">
      <p class="breadcrumb">Inicio > Mis Solicitudes</p>
      <h1><i class="bi bi-clipboard-check text-primary"></i> Mis Solicitudes</h1>
      <p>Revisa el estado de las solicitudes que has enviado a proveedores.</p>
    </div>

    <ul class="nav nav-tabs mb-4" role="tablist">
      <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabPendientes" type="button">
          Pendientes <span class="badge bg-secondary"><?= count($pendientes) ?></span>
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabAceptadas" type="button">
          Aceptadas <span class="badge bg-success"><?= count($aceptadas) ?></span>
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabRechazadas" type="button">
          Rechazadas <span class="badge bg-danger"><?= count($rechazadas) ?></span>
        </button>
      </li>
    </ul>

    <div class="tab-content">

      <!-- Pendientes -->
      <div class="tab-pane fade show active" id="tabPendientes">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
          <?php if (!empty($pendientes)): ?>
            <?php foreach ($pendientes as $s): ?>
              <?php
                $titulo = $s['titulo'] ?? 'Solicitud';
                $servicio = $s['servicio_nombre'] ?? '';
                $proveedor = $s['proveedor_nombre'] ?? 'Proveedor';
                $fecha = $s['fecha_preferida'] ?? '';
                $img = !empty($s['servicio_imagen'])
                  ? BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($s['servicio_imagen'])
                  : BASE_URL . '/public/assets/dashBoard/img/imagen-servicio.png';
              ?>
              <div class="col">
                <div class="card service-card">
                  <img src="<?= $img ?>" class="card-img-top" alt="Servicio">
                  <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($titulo) ?></h5>
                    <p class="text-muted mb-1">
                      <i class="bi bi-tools"></i> <?= htmlspecialchars($servicio) ?>
                    </p>
                    <p class="text-muted mb-1">
                      <i class="bi bi-person-fill"></i> <?= htmlspecialchars($proveedor) ?>
                    </p>
                    <?php if ($fecha): ?>
                      <p class="text-muted mb-2">
                        <i class="bi bi-calendar-event"></i> <?= htmlspecialchars($fecha) ?>
                      </p>
                    <?php endif; ?>

                    <span class="badge bg-warning text-dark">Pendiente</span>

                    <div class="d-grid gap-2 mt-3">
                      <a href="<?= BASE_URL ?>/cliente/publicacion?id=<?= (int)$s['publicacion_id'] ?>" class="btn btn-outline-primary">
                        Ver publicación
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col">
              <div class="alert alert-info">No tienes solicitudes pendientes.</div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Aceptadas -->
      <div class="tab-pane fade" id="tabAceptadas">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
          <?php if (!empty($aceptadas)): ?>
            <?php foreach ($aceptadas as $s): ?>
              <?php
                $titulo = $s['titulo'] ?? 'Solicitud';
                $servicio = $s['servicio_nombre'] ?? '';
                $proveedor = $s['proveedor_nombre'] ?? 'Proveedor';
                $fecha = $s['fecha_preferida'] ?? '';
                $img = !empty($s['servicio_imagen'])
                  ? BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($s['servicio_imagen'])
                  : BASE_URL . '/public/assets/dashBoard/img/imagen-servicio.png';
              ?>
              <div class="col">
                <div class="card service-card">
                  <img src="<?= $img ?>" class="card-img-top" alt="Servicio">
                  <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($titulo) ?></h5>
                    <p class="text-muted mb-1"><i class="bi bi-tools"></i> <?= htmlspecialchars($servicio) ?></p>
                    <p class="text-muted mb-1"><i class="bi bi-person-fill"></i> <?= htmlspecialchars($proveedor) ?></p>
                    <?php if ($fecha): ?>
                      <p class="text-muted mb-2"><i class="bi bi-calendar-event"></i> <?= htmlspecialchars($fecha) ?></p>
                    <?php endif; ?>

                    <span class="badge bg-success">Aceptada</span>

                    <div class="d-grid gap-2 mt-3">
                      <!-- Puedes apuntar a servicios contratados -->
                      <a href="<?= BASE_URL ?>/cliente/servicios-contratados" class="btn btn-primary">
                        Ir a servicios contratados
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col">
              <div class="alert alert-info">Aún no tienes solicitudes aceptadas.</div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Rechazadas -->
      <div class="tab-pane fade" id="tabRechazadas">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
          <?php if (!empty($rechazadas)): ?>
            <?php foreach ($rechazadas as $s): ?>
              <?php
                $titulo = $s['titulo'] ?? 'Solicitud';
                $servicio = $s['servicio_nombre'] ?? '';
                $proveedor = $s['proveedor_nombre'] ?? 'Proveedor';
                $fecha = $s['fecha_preferida'] ?? '';
                $img = !empty($s['servicio_imagen'])
                  ? BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($s['servicio_imagen'])
                  : BASE_URL . '/public/assets/dashBoard/img/imagen-servicio.png';
              ?>
              <div class="col">
                <div class="card service-card">
                  <img src="<?= $img ?>" class="card-img-top" alt="Servicio">
                  <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($titulo) ?></h5>
                    <p class="text-muted mb-1"><i class="bi bi-tools"></i> <?= htmlspecialchars($servicio) ?></p>
                    <p class="text-muted mb-1"><i class="bi bi-person-fill"></i> <?= htmlspecialchars($proveedor) ?></p>
                    <?php if ($fecha): ?>
                      <p class="text-muted mb-2"><i class="bi bi-calendar-event"></i> <?= htmlspecialchars($fecha) ?></p>
                    <?php endif; ?>

                    <span class="badge bg-danger">Rechazada</span>

                    <div class="d-grid gap-2 mt-3">
                      <a href="<?= BASE_URL ?>/cliente/explorar-servicios" class="btn btn-outline-secondary">
                        Buscar otro proveedor
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col">
              <div class="alert alert-info">No tienes solicitudes rechazadas.</div>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
