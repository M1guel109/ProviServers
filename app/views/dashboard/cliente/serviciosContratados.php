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

                $tituloServicio   = $srv['servicio_nombre'] ?? $srv['publicacion_titulo'] ?? $srv['solicitud_titulo'];
                $proveedorNombre  = $srv['proveedor_nombre'] ?? 'Proveedor sin nombre';
                $fechaTexto       = $srv['fecha_ejecucion'] ?: $srv['fecha_preferida'] ?: $srv['fecha_solicitud'];
                $ciudad           = $srv['ciudad'] ?? '';
                $zona             = $srv['zona'] ?? '';
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

                $tituloServicio   = $srv['servicio_nombre'] ?? $srv['publicacion_titulo'] ?? $srv['solicitud_titulo'];
                $proveedorNombre  = $srv['proveedor_nombre'] ?? 'Proveedor sin nombre';
                $fechaTexto       = $srv['fecha_ejecucion'] ?: $srv['fecha_preferida'] ?: $srv['fecha_solicitud'];
                $ciudad           = $srv['ciudad'] ?? '';
                $zona             = $srv['zona'] ?? '';
                $estado           = $srv['estado'] ?? '';
                $contratoId       = (int)($srv['contrato_id'] ?? 0);
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

                      <!-- ✅ 3.2 Acción cliente: Cancelar (solo si pendiente/confirmado) -->
                      <?php if ($contratoId > 0 && in_array($estado, ['pendiente', 'confirmado'], true)): ?>
                        <form method="POST"
                          action="<?= BASE_URL ?>/cliente/servicios-contratados/cancelar"
                          class="mt-2"
                          onsubmit="return confirm('¿Seguro que deseas cancelar este servicio?');">
                          <input type="hidden" name="contrato_id" value="<?= $contratoId ?>">
                          <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-x-circle"></i> Cancelar servicio
                          </button>
                        </form>
                      <?php endif; ?>

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

                $tituloServicio   = $srv['servicio_nombre'] ?? $srv['publicacion_titulo'] ?? $srv['solicitud_titulo'];
                $proveedorNombre  = $srv['proveedor_nombre'] ?? 'Proveedor sin nombre';
                $fechaTexto       = $srv['fecha_ejecucion'] ?: $srv['fecha_preferida'] ?: $srv['fecha_solicitud'];
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


                      <?php if (($srv['estado'] ?? '') === 'finalizado'): ?>

                        <?php if ((int)($srv['tiene_valoracion'] ?? 0) === 0): ?>
                          <button type="button"
                            class="btn btn-success w-100 mt-2"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCalificar"
                            data-contrato-id="<?= (int)$srv['contrato_id'] ?>"
                            data-servicio-nombre="<?= htmlspecialchars($srv['servicio_nombre'] ?? $srv['solicitud_titulo'] ?? 'Servicio') ?>">
                            <i class="bi bi-star-fill"></i> Calificar servicio
                          </button>
                        <?php else: ?>
                          <?php
                          $miCalif = (int)($srv['mi_calificacion'] ?? 0);
                          $miCom   = trim((string)($srv['mi_comentario'] ?? ''));
                          ?>

                          <div class="mt-2 p-2 border rounded bg-light">
                            <div class="d-flex align-items-center justify-content-between">
                              <span class="fw-semibold">Tu calificación:</span>
                              <span class="text-warning">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                  <i class="bi <?= $i <= $miCalif ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                <?php endfor; ?>
                              </span>
                            </div>

                            <?php if ($miCom !== ''): ?>
                              <div class="mt-2 small text-muted">
                                “<?= htmlspecialchars($miCom) ?>”
                              </div>
                            <?php endif; ?>
                          </div>
                        <?php endif; ?>

                      <?php endif; ?>



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

                $tituloServicio   = $srv['servicio_nombre'] ?? $srv['publicacion_titulo'] ?? $srv['solicitud_titulo'];
                $proveedorNombre  = $srv['proveedor_nombre'] ?? 'Proveedor sin nombre';
                $fechaTexto       = $srv['fecha_ejecucion'] ?: $srv['fecha_preferida'] ?: $srv['fecha_solicitud'];
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



  <!-- Modal Calificar -->
  <div class="modal fade" id="modalCalificar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST" action="<?= BASE_URL ?>/cliente/servicios-contratados/calificar" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Calificar servicio</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="contrato_id" id="calificar_contrato_id" value="">

          <div class="mb-3">
            <label class="form-label">Calificación</label>
            <select name="calificacion" class="form-select" required>
              <option value="">Selecciona…</option>
              <option value="5">5 - Excelente</option>
              <option value="4">4 - Muy bueno</option>
              <option value="3">3 - Bueno</option>
              <option value="2">2 - Regular</option>
              <option value="1">1 - Malo</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Comentario (opcional)</label>
            <textarea name="comentario" class="form-control" rows="3" maxlength="800"
              placeholder="Cuéntanos cómo te fue con el servicio..."></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">
            Guardar calificación
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const modalCalificar = document.getElementById('modalCalificar');
    modalCalificar?.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      const contratoId = button.getAttribute('data-contrato-id');
      document.getElementById('calificar_contrato_id').value = contratoId || '';
    });
  </script>


  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <!-- JS propio -->
  <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboardCliente.js"></script>
</body>

</html>