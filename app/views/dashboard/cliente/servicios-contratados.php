<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proviservers | Servicios Contratados</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-cliente.css">
</head>

<body>

  <?php
  $currentPage = 'servicios-contratados';
  include_once __DIR__ . '/../../layouts/sidebar-cliente.php';
  ?>

  <main class="contenido">

    <?php include_once __DIR__ . '/../../layouts/header-cliente.php'; ?>

    <section id="servicios-contratados">

      <div class="section-hero mb-4">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a href="<?= BASE_URL ?>/cliente/dashboard">Inicio</a>
            </li>
            <li class="breadcrumb-item active">Servicios Contratados</li>
          </ol>
        </nav>
        <h1>Servicios Contratados</h1>
        <p class="text-muted">Gestiona todos tus servicios contratados y programados desde aquí.</p>
      </div>

      <!-- Tabs -->
      <ul class="nav nav-tabs mb-4" id="estadoTabs" role="tablist">
        <li class="nav-item">
          <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#curso" type="button">
            En curso
            <?php if (!empty($serviciosEnCurso)): ?>
              <span class="badge bg-primary ms-1"><?= count($serviciosEnCurso) ?></span>
            <?php endif; ?>
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#programado" type="button">
            Programados
            <?php if (!empty($serviciosProgramados)): ?>
              <span class="badge bg-secondary ms-1"><?= count($serviciosProgramados) ?></span>
            <?php endif; ?>
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#completado" type="button">
            Completados
            <?php if (!empty($serviciosCompletados)): ?>
              <span class="badge bg-success ms-1"><?= count($serviciosCompletados) ?></span>
            <?php endif; ?>
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#cancelado" type="button">
            Cancelados
            <?php if (!empty($serviciosCancelados)): ?>
              <span class="badge bg-danger ms-1"><?= count($serviciosCancelados) ?></span>
            <?php endif; ?>
          </button>
        </li>
      </ul>

      <div class="tab-content" id="estadoTabsContent">

        <!-- ===================================================
                     TAB 1: EN CURSO
                     =================================================== -->
        <div class="tab-pane fade show active" id="curso">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($serviciosEnCurso)): ?>
              <?php foreach ($serviciosEnCurso as $srv): ?>
                <?php
                $imagen = !empty($srv['servicio_imagen'])
                  ? BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($srv['servicio_imagen'])
                  : BASE_URL . '/public/assets/dashboard/img/imagen-servicio.png';

                $tituloServicio  = $srv['servicio_nombre']   ?? $srv['solicitud_titulo'] ?? 'Servicio';
                $proveedorNombre = $srv['proveedor_nombre']  ?? 'Proveedor';

                // ✅ Alias correcto del modelo
                $estadoContrato  = $srv['estado_contrato']   ?? $srv['estado'] ?? 'en_proceso';

                $fechaRaw = $srv['fecha_ejecucion']
                  ?? $srv['solicitud_fecha_preferida']
                  ?? $srv['fecha_solicitud']
                  ?? null;

                // ✅ Fecha formateada
                $fechaTexto = $fechaRaw
                  ? date('d/m/Y', strtotime($fechaRaw))
                  : null;

                $ciudad = $srv['solicitud_ciudad'] ?? '';
                $zona   = $srv['solicitud_zona']   ?? '';

                // ✅ Progreso real según estado
                $progreso = match ($estadoContrato) {
                  'pendiente'  => 15,
                  'confirmado' => 40,
                  'en_proceso' => 65,
                  'finalizado' => 100,
                  default      => 10,
                };

                $contratoId = (int)($srv['contrato_id'] ?? 0);
                ?>
                <div class="col">
                  <div class="card service-card h-100 border-0 shadow-sm">
                    <img src="<?= $imagen ?>"
                      class="card-img-top"
                      alt="<?= htmlspecialchars($tituloServicio) ?>"
                      style="height:180px;object-fit:cover;"
                      onerror="this.src='<?= BASE_URL ?>/public/assets/dashboard/img/imagen-servicio.png'">

                    <div class="card-body">
                      <h5 class="card-title fw-bold">
                        <?= htmlspecialchars($tituloServicio) ?>
                      </h5>

                      <p class="text-muted small mb-1">
                        <i class="bi bi-person-fill me-1"></i>
                        <?= htmlspecialchars($proveedorNombre) ?>
                      </p>

                      <?php if ($fechaTexto): ?>
                        <p class="text-muted small mb-1">
                          <i class="bi bi-calendar-event me-1"></i>
                          Programado para <?= $fechaTexto ?>
                        </p>
                      <?php endif; ?>

                      <?php if ($ciudad): ?>
                        <p class="text-muted small mb-2">
                          <i class="bi bi-geo-alt me-1"></i>
                          <?= htmlspecialchars($ciudad . ($zona ? ' - ' . $zona : '')) ?>
                        </p>
                      <?php endif; ?>

                      <!-- ✅ Barra de progreso dinámica -->
                      <div class="mb-1 d-flex justify-content-between small text-muted">
                        <span>Progreso</span>
                        <span><?= $progreso ?>%</span>
                      </div>
                      <div class="progress mb-3" style="height:8px;">
                        <div class="progress-bar bg-primary"
                          role="progressbar"
                          style="width: <?= $progreso ?>%"
                          aria-valuenow="<?= $progreso ?>"
                          aria-valuemin="0"
                          aria-valuemax="100">
                        </div>
                      </div>

                      <button type="button"
                        class="btn btn-primary w-100 mb-2 btn-ver-detalles"
                        data-contrato-id="<?= $contratoId ?>"
                        data-titulo="<?= htmlspecialchars($tituloServicio) ?>"
                        data-proveedor="<?= htmlspecialchars($proveedorNombre) ?>"
                        data-estado="<?= htmlspecialchars($estadoContrato) ?>"
                        data-fecha="<?= $fechaTexto ?? '—' ?>"
                        data-ciudad="<?= htmlspecialchars($ciudad ?: '—') ?>"
                        data-zona="<?= htmlspecialchars($zona) ?>"
                        data-descripcion="<?= htmlspecialchars($srv['solicitud_descripcion'] ?? $srv['cotizacion_mensaje'] ?? '') ?>"
                        data-monto="<?= (float)($srv['monto'] ?? 0) ?>">
                        <i class="bi bi-eye me-1"></i> Ver detalles
                      </button>

                      <?php
                      $montoSrv   = (float)($srv['monto']     ?? 0);
                      $yaPagado   = (int)($srv['ya_pagado']   ?? 0);
                      ?>
                      <?php if ($montoSrv > 0 && !$yaPagado && $estadoContrato === 'en_proceso'): ?>
                        <button type="button"
                          class="btn btn-success w-100 btn-pagar-servicio"
                          data-contrato-id="<?= $contratoId ?>"
                          data-monto="<?= number_format($montoSrv, 0, ',', '.') ?>"
                          data-titulo="<?= htmlspecialchars($tituloServicio) ?>">
                          <i class="bi bi-credit-card me-1"></i> Pagar servicio
                        </button>
                      <?php elseif ($yaPagado): ?>
                        <span class="badge bg-success w-100 py-2">
                          <i class="bi bi-check-circle me-1"></i> Pagado
                        </span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12">
                <div class="alert alert-info">
                  <i class="bi bi-info-circle me-2"></i>
                  No tienes servicios en curso en este momento.
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- ===================================================
                     TAB 2: PROGRAMADOS
                     =================================================== -->
        <div class="tab-pane fade" id="programado">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($serviciosProgramados)): ?>
              <?php foreach ($serviciosProgramados as $srv): ?>
                <?php
                $imagen = !empty($srv['servicio_imagen'])
                  ? BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($srv['servicio_imagen'])
                  : BASE_URL . '/public/assets/dashboard/img/imagen-servicio.png';

                $tituloServicio  = $srv['servicio_nombre']  ?? $srv['solicitud_titulo'] ?? 'Servicio';
                $proveedorNombre = $srv['proveedor_nombre'] ?? 'Proveedor';

                // ✅ Alias correcto
                $estadoContrato  = $srv['estado_contrato']  ?? $srv['estado'] ?? '';

                $fechaRaw = $srv['fecha_ejecucion']
                  ?? $srv['solicitud_fecha_preferida']
                  ?? $srv['fecha_solicitud']
                  ?? null;

                $fechaTexto = $fechaRaw
                  ? date('d/m/Y', strtotime($fechaRaw))
                  : null;

                $ciudad     = $srv['solicitud_ciudad'] ?? '';
                $zona       = $srv['solicitud_zona']   ?? '';
                $contratoId = (int)($srv['contrato_id'] ?? 0);
                ?>
                <div class="col">
                  <div class="card service-card h-100 border-0 shadow-sm">
                    <img src="<?= $imagen ?>"
                      class="card-img-top"
                      alt="<?= htmlspecialchars($tituloServicio) ?>"
                      style="height:180px;object-fit:cover;"
                      onerror="this.src='<?= BASE_URL ?>/public/assets/dashboard/img/imagen-servicio.png'">

                    <div class="card-body">
                      <h5 class="card-title fw-bold">
                        <?= htmlspecialchars($tituloServicio) ?>
                      </h5>

                      <p class="text-muted small mb-1">
                        <i class="bi bi-person-fill me-1"></i>
                        <?= htmlspecialchars($proveedorNombre) ?>
                      </p>

                      <?php if ($fechaTexto): ?>
                        <p class="text-muted small mb-1">
                          <i class="bi bi-calendar-event me-1"></i>
                          <?= $fechaTexto ?>
                        </p>
                      <?php endif; ?>

                      <?php if ($ciudad): ?>
                        <p class="text-muted small mb-2">
                          <i class="bi bi-geo-alt me-1"></i>
                          <?= htmlspecialchars($ciudad . ($zona ? ' - ' . $zona : '')) ?>
                        </p>
                      <?php endif; ?>

                      <button type="button"
                        class="btn btn-primary w-100 mb-2 btn-ver-detalles"
                        data-contrato-id="<?= $contratoId ?>"
                        data-titulo="<?= htmlspecialchars($tituloServicio) ?>"
                        data-proveedor="<?= htmlspecialchars($proveedorNombre) ?>"
                        data-estado="<?= htmlspecialchars($estadoContrato) ?>"
                        data-fecha="<?= $fechaTexto ?? '—' ?>"
                        data-ciudad="<?= htmlspecialchars($ciudad ?: '—') ?>"
                        data-zona="<?= htmlspecialchars($zona) ?>"
                        data-descripcion="<?= htmlspecialchars($srv['solicitud_descripcion'] ?? $srv['cotizacion_mensaje'] ?? '') ?>"
                        data-monto="<?= (float)($srv['monto'] ?? 0) ?>">
                        <i class="bi bi-eye me-1"></i> Ver detalles
                      </button>

                      <?php
                      $montoSrv2 = (float)($srv['monto']     ?? 0);
                      $yaPagado2 = (int)($srv['ya_pagado']   ?? 0);
                      ?>
                      <?php if ($montoSrv2 > 0 && !$yaPagado2 && $estadoContrato === 'confirmado'): ?>
                        <button type="button"
                          class="btn btn-success w-100 mb-2 btn-pagar-servicio"
                          data-contrato-id="<?= $contratoId ?>"
                          data-monto="<?= number_format($montoSrv2, 0, ',', '.') ?>"
                          data-titulo="<?= htmlspecialchars($tituloServicio) ?>">
                          <i class="bi bi-credit-card me-1"></i> Pagar servicio
                        </button>
                      <?php elseif ($yaPagado2): ?>
                        <span class="badge bg-success w-100 py-2 mb-2">
                          <i class="bi bi-check-circle me-1"></i> Pagado
                        </span>
                      <?php endif; ?>

                      <!-- ✅ Cancelar con data-attribute — SweetAlert abajo -->
                      <?php if ($contratoId > 0 && in_array($estadoContrato, ['pendiente', 'confirmado'], true)): ?>
                        <button type="button"
                          class="btn btn-outline-danger w-100 btn-cancelar-contrato"
                          data-contrato-id="<?= $contratoId ?>"
                          data-servicio="<?= htmlspecialchars($tituloServicio) ?>">
                          <i class="bi bi-x-circle me-1"></i> Cancelar servicio
                        </button>

                        <!-- Form oculto — se envía desde JS -->
                        <form method="POST"
                          id="form-cancelar-<?= $contratoId ?>"
                          action="<?= BASE_URL ?>/cliente/servicios-contratados/cancelar"
                          style="display:none;">
                          <input type="hidden" name="accion" value="cancelar_servicio">
                          <input type="hidden" name="contrato_id" value="<?= $contratoId ?>">
                        </form>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12">
                <div class="alert alert-info">
                  <i class="bi bi-info-circle me-2"></i>
                  No tienes servicios programados por ahora.
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- ===================================================
                     TAB 3: COMPLETADOS
                     =================================================== -->
        <div class="tab-pane fade" id="completado">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($serviciosCompletados)): ?>
              <?php foreach ($serviciosCompletados as $srv): ?>
                <?php
                $imagen = !empty($srv['servicio_imagen'])
                  ? BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($srv['servicio_imagen'])
                  : BASE_URL . '/public/assets/dashboard/img/imagen-servicio.png';

                $tituloServicio  = $srv['servicio_nombre']  ?? $srv['solicitud_titulo'] ?? 'Servicio';
                $proveedorNombre = $srv['proveedor_nombre'] ?? 'Proveedor';
                $estadoContrato  = $srv['estado_contrato']  ?? $srv['estado'] ?? '';

                $fechaRaw = $srv['fecha_ejecucion']
                  ?? $srv['solicitud_fecha_preferida']
                  ?? $srv['fecha_solicitud']
                  ?? null;

                $fechaTexto = $fechaRaw
                  ? date('d/m/Y', strtotime($fechaRaw))
                  : null;

                $contratoId      = (int)($srv['contrato_id']     ?? 0);
                $tieneValoracion = (int)($srv['tiene_valoracion'] ?? 0);
                $miCalif         = (int)($srv['mi_calificacion']  ?? 0);
                $miCom           = trim((string)($srv['mi_comentario'] ?? ''));
                ?>
                <div class="col">
                  <div class="card service-card h-100 border-0 shadow-sm">
                    <img src="<?= $imagen ?>"
                      class="card-img-top"
                      alt="<?= htmlspecialchars($tituloServicio) ?>"
                      style="height:180px;object-fit:cover;"
                      onerror="this.src='<?= BASE_URL ?>/public/assets/dashboard/img/imagen-servicio.png'">

                    <div class="card-body">
                      <h5 class="card-title fw-bold">
                        <?= htmlspecialchars($tituloServicio) ?>
                      </h5>

                      <p class="text-muted small mb-1">
                        <i class="bi bi-person-fill me-1"></i>
                        <?= htmlspecialchars($proveedorNombre) ?>
                      </p>

                      <?php if ($fechaTexto): ?>
                        <p class="text-muted small mb-2">
                          <i class="bi bi-calendar-check me-1"></i>
                          Completado el <?= $fechaTexto ?>
                        </p>
                      <?php endif; ?>

                      <button type="button"
                        class="btn btn-outline-primary w-100 mb-2 btn-ver-detalles"
                        data-contrato-id="<?= $contratoId ?>"
                        data-titulo="<?= htmlspecialchars($tituloServicio) ?>"
                        data-proveedor="<?= htmlspecialchars($proveedorNombre) ?>"
                        data-estado="<?= htmlspecialchars($estadoContrato) ?>"
                        data-fecha="<?= $fechaTexto ?? '—' ?>"
                        data-ciudad="<?= htmlspecialchars(($srv['solicitud_ciudad'] ?? '') ?: '—') ?>"
                        data-zona="<?= htmlspecialchars($srv['solicitud_zona'] ?? '') ?>"
                        data-descripcion="<?= htmlspecialchars($srv['solicitud_descripcion'] ?? $srv['cotizacion_mensaje'] ?? '') ?>"
                        data-monto="<?= (float)($srv['monto'] ?? 0) ?>">
                        <i class="bi bi-eye me-1"></i> Ver detalles
                      </button>

                      <?php if ($contratoId > 0): ?>
                        <a href="<?= BASE_URL ?>/cliente/contrato-pdf?id=<?= $contratoId ?>"
                          class="btn btn-outline-secondary w-100 mb-2"
                          target="_blank">
                          <i class="bi bi-file-pdf me-1"></i> Descargar comprobante
                        </a>
                      <?php endif; ?>

                      <?php if ($estadoContrato === 'finalizado'): ?>
                        <?php if ($tieneValoracion === 0): ?>
                          <button type="button"
                            class="btn btn-success w-100"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCalificar"
                            data-contrato-id="<?= $contratoId ?>"
                            data-servicio-nombre="<?= htmlspecialchars($tituloServicio) ?>">
                            <i class="bi bi-star-fill me-1"></i> Calificar servicio
                          </button>
                        <?php else: ?>
                          <div class="p-2 border rounded bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                              <span class="small fw-semibold">Tu calificación:</span>
                              <span class="text-warning">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                  <i class="bi <?= $i <= $miCalif ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                <?php endfor; ?>
                              </span>
                            </div>
                            <?php if ($miCom !== ''): ?>
                              <p class="small text-muted mt-1 mb-0 fst-italic">
                                "<?= htmlspecialchars($miCom) ?>"
                              </p>
                            <?php endif; ?>
                          </div>
                        <?php endif; ?>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12">
                <div class="alert alert-info">
                  <i class="bi bi-info-circle me-2"></i>
                  Aún no tienes servicios completados.
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- ===================================================
                     TAB 4: CANCELADOS
                     =================================================== -->
        <div class="tab-pane fade" id="cancelado">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($serviciosCancelados)): ?>
              <?php foreach ($serviciosCancelados as $srv): ?>
                <?php
                $imagen = !empty($srv['servicio_imagen'])
                  ? BASE_URL . '/public/uploads/servicios/' . htmlspecialchars($srv['servicio_imagen'])
                  : BASE_URL . '/public/assets/dashboard/img/imagen-servicio.png';

                $tituloServicio    = $srv['servicio_nombre']    ?? $srv['solicitud_titulo'] ?? 'Servicio';
                $proveedorNombre   = $srv['proveedor_nombre']   ?? 'Proveedor';
                $motivoCancelacion = trim((string)($srv['motivo_cancelacion'] ?? ''));

                $fechaRaw = $srv['fecha_ejecucion']
                  ?? $srv['solicitud_fecha_preferida']
                  ?? $srv['fecha_solicitud']
                  ?? null;

                $fechaTexto = $fechaRaw
                  ? date('d/m/Y', strtotime($fechaRaw))
                  : null;
                ?>
                <div class="col">
                  <div class="card service-card h-100 border-0 shadow-sm opacity-75">
                    <img src="<?= $imagen ?>"
                      class="card-img-top"
                      alt="<?= htmlspecialchars($tituloServicio) ?>"
                      style="height:180px;object-fit:cover;filter:grayscale(50%);"
                      onerror="this.src='<?= BASE_URL ?>/public/assets/dashboard/img/imagen-servicio.png'">

                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title fw-bold mb-0">
                          <?= htmlspecialchars($tituloServicio) ?>
                        </h5>
                        <span class="badge bg-danger ms-2">Cancelado</span>
                      </div>

                      <p class="text-muted small mb-1">
                        <i class="bi bi-person-fill me-1"></i>
                        <?= htmlspecialchars($proveedorNombre) ?>
                      </p>

                      <?php if ($fechaTexto): ?>
                        <p class="text-muted small mb-1">
                          <i class="bi bi-calendar-x me-1"></i>
                          Cancelado el <?= $fechaTexto ?>
                        </p>
                      <?php endif; ?>

                      <?php if ($motivoCancelacion !== ''): ?>
                        <p class="small text-danger mb-2">
                          <i class="bi bi-exclamation-circle me-1"></i>
                          <?= htmlspecialchars($motivoCancelacion) ?>
                        </p>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12">
                <div class="alert alert-info">
                  <i class="bi bi-info-circle me-2"></i>
                  No tienes servicios cancelados.
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </section>
  </main>

  <!-- Modal Detalles del Servicio -->
  <div class="modal fade" id="modalDetalleServicio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="det-titulo">Detalles del servicio</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="det-body">
          <!-- llenado por JS -->
        </div>
        <div class="modal-footer justify-content-between">
          <a href="#" id="det-pdf-link" class="btn btn-outline-secondary btn-sm" target="_blank">
            <i class="bi bi-file-pdf me-1"></i> Comprobante PDF
          </a>
          <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Calificar -->
  <div class="modal fade" id="modalCalificar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST"
        action="<?= BASE_URL ?>/cliente/servicios-contratados/calificar"
        class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-star-fill text-warning me-2"></i>
            Calificar servicio
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="accion" value="calificar_servicio">
          <input type="hidden" name="contrato_id" id="calificar_contrato_id">

          <p class="text-muted small mb-3" id="calificar_servicio_nombre"></p>

          <div class="mb-3">
            <label class="form-label fw-semibold">
              Calificación <span class="text-danger">*</span>
            </label>
            <select name="calificacion" class="form-select" required>
              <option value="">Selecciona una calificación…</option>
              <option value="5">⭐⭐⭐⭐⭐ — Excelente</option>
              <option value="4">⭐⭐⭐⭐ — Muy bueno</option>
              <option value="3">⭐⭐⭐ — Bueno</option>
              <option value="2">⭐⭐ — Regular</option>
              <option value="1">⭐ — Malo</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Comentario (opcional)</label>
            <textarea name="comentario"
              class="form-control"
              rows="3"
              maxlength="800"
              placeholder="Cuéntanos cómo te fue con el servicio..."></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Cancelar
          </button>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-send me-1"></i> Guardar calificación
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- ✅ Bootstrap JS primero -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <!-- ✅ SweetAlert antes de scripts que lo usan -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    // ── Modal calificar — llenar datos al abrir ──────────────────
    const modalCalificar = document.getElementById('modalCalificar');
    modalCalificar?.addEventListener('show.bs.modal', function(event) {
      const btn = event.relatedTarget;
      document.getElementById('calificar_contrato_id').value =
        btn.getAttribute('data-contrato-id') || '';
      const nombreEl = document.getElementById('calificar_servicio_nombre');
      if (nombreEl) {
        nombreEl.textContent =
          btn.getAttribute('data-servicio-nombre') || '';
      }
    });

    // ── Cancelar contrato con SweetAlert ────────────────────────
    // ✅ Delegación de eventos — sin onsubmit inline
    document.addEventListener('click', function(e) {
      const btn = e.target.closest('.btn-cancelar-contrato');
      if (!btn) return;

      const contratoId = btn.dataset.contratoId;
      const servicio = btn.dataset.servicio || 'este servicio';

      Swal.fire({
        title: '¿Cancelar servicio?',
        text: `¿Estás seguro de que quieres cancelar "${servicio}"? Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No, volver',
        confirmButtonColor: '#dc3545'
      }).then(result => {
        if (result.isConfirmed) {
          document.getElementById('form-cancelar-' + contratoId)?.submit();
        }
      });
    });
  </script>

  <!-- ── Modal detalles ──────────────────────────────── -->
  <script>
    const ESTADO_LABELS = {
      pendiente: 'Pendiente', confirmado: 'Confirmado', en_proceso: 'En proceso',
      finalizado: 'Finalizado', cancelado: 'Cancelado',
      cancelado_cliente: 'Cancelado por ti', cancelado_proveedor: 'Cancelado por proveedor'
    };
    const ESTADO_COLORS = {
      pendiente: 'secondary', confirmado: 'primary', en_proceso: 'info',
      finalizado: 'success', cancelado: 'danger',
      cancelado_cliente: 'danger', cancelado_proveedor: 'danger'
    };

    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.btn-ver-detalles');
      if (!btn) return;

      const titulo      = btn.dataset.titulo      || 'Servicio';
      const proveedor   = btn.dataset.proveedor   || '—';
      const estado      = btn.dataset.estado      || '';
      const fecha       = btn.dataset.fecha       || '—';
      const ciudad      = btn.dataset.ciudad      || '—';
      const zona        = btn.dataset.zona        || '';
      const descripcion = btn.dataset.descripcion || '';
      const monto       = parseFloat(btn.dataset.monto || 0);
      const contratoId  = btn.dataset.contratoId  || '';

      document.getElementById('det-titulo').textContent = titulo;

      const badge  = `<span class="badge bg-${ESTADO_COLORS[estado] || 'secondary'}">${ESTADO_LABELS[estado] || estado}</span>`;
      const montoHtml = monto > 0
        ? `<strong class="text-success">$${monto.toLocaleString('es-CO')} COP</strong>`
        : '<span class="text-muted fst-italic">Sin monto definido</span>';
      const descHtml = descripcion
        ? `<dt class="col-sm-5">Descripción</dt><dd class="col-sm-7"><small class="text-muted">${descripcion}</small></dd>`
        : '';

      document.getElementById('det-body').innerHTML = `
        <dl class="row mb-0">
          <dt class="col-sm-5">Proveedor</dt><dd class="col-sm-7">${proveedor}</dd>
          <dt class="col-sm-5">Estado</dt><dd class="col-sm-7">${badge}</dd>
          <dt class="col-sm-5">Fecha</dt><dd class="col-sm-7">${fecha}</dd>
          <dt class="col-sm-5">Ubicación</dt><dd class="col-sm-7">${ciudad}${zona ? ' — ' + zona : ''}</dd>
          <dt class="col-sm-5">Monto</dt><dd class="col-sm-7">${montoHtml}</dd>
          ${descHtml}
        </dl>`;

      const pdfLink = document.getElementById('det-pdf-link');
      if (pdfLink) pdfLink.href = '<?= BASE_URL ?>/cliente/contrato-pdf?id=' + contratoId;

      bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleServicio')).show();
    });
  </script>

  <!-- ── Pagar servicio ─────────────────────────────── -->
  <script>
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.btn-pagar-servicio');
      if (!btn) return;

      const contratoId = btn.dataset.contratoId;
      const monto      = btn.dataset.monto;
      const titulo     = btn.dataset.titulo || 'este servicio';

      Swal.fire({
        title: 'Confirmar pago',
        html: `<p class="mb-1">Servicio: <strong>${titulo}</strong></p>
               <p>Monto: <strong class="text-success">$${monto} COP</strong></p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-credit-card me-1"></i> Ir a pagar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754'
      }).then(result => {
        if (!result.isConfirmed) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Procesando...';

        const body = new FormData();
        body.append('contrato_id', contratoId);

        fetch('<?= BASE_URL ?>/cliente/pagar-servicio', {
          method: 'POST',
          body,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
          .then(r => r.json())
          .then(data => {
            if (data.ok) {
              window.location.href = data.url;
            } else {
              Swal.fire('Error', data.error || 'No se pudo iniciar el pago.', 'error');
              btn.disabled = false;
              btn.innerHTML = '<i class="bi bi-credit-card me-1"></i> Pagar servicio';
            }
          })
          .catch(() => {
            Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-credit-card me-1"></i> Pagar servicio';
          });
      });
    });
  </script>

  <script src="<?= BASE_URL ?>/public/assets/dashboard/js/dashboard-cliente.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
</body>

</html>