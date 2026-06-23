<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
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
              <a href="<?= BASE_URL ?>/cliente/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Servicios Contratados</li>
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
                        <a href="<?= BASE_URL ?>/cliente/checkout?sc_id=<?= $contratoId ?>"
                           class="btn btn-success w-100">
                          <i class="bi bi-credit-card me-1"></i> Pagar servicio
                        </a>
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
                        <a href="<?= BASE_URL ?>/cliente/checkout?sc_id=<?= $contratoId ?>"
                           class="btn btn-success w-100 mb-2">
                          <i class="bi bi-credit-card me-1"></i> Pagar servicio
                        </a>
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

                $contratoId        = (int)($srv['contrato_id']         ?? 0);
                $tieneValoracion   = (int)($srv['tiene_valoracion']   ?? 0);
                $valoracionId      = (int)($srv['valoracion_id']      ?? 0);
                $miCalif           = (int)($srv['mi_calificacion']    ?? 0);
                $miCom             = trim((string)($srv['mi_comentario']        ?? ''));
                $respuestaProveedor = trim((string)($srv['respuesta_proveedor'] ?? ''));
                $respuestaCliente   = trim((string)($srv['respuesta_cliente']   ?? ''));
                $fechaRespCli       = $srv['fecha_respuesta_cliente'] ?? '';
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
                        data-monto="<?= (float)($srv['monto'] ?? 0) ?>"
                        data-valoracion-id="<?= $valoracionId ?>"
                        data-respuesta-proveedor="<?= htmlspecialchars($respuestaProveedor) ?>"
                        data-respuesta-cliente="<?= htmlspecialchars($respuestaCliente) ?>"
                        data-fecha-resp-cli="<?= htmlspecialchars($fechaRespCli) ?>">
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

                            <?php if ($respuestaProveedor !== ''): ?>
                              <div class="mt-2 pt-2 border-top">
                                <small class="fw-bold text-info"><i class="bi bi-person-check me-1"></i> Respuesta del proveedor:</small>
                                <p class="small text-muted fst-italic mb-1 resp-clamp">"<?= htmlspecialchars($respuestaProveedor) ?>"</p>
                                <?php if ($respuestaCliente !== ''): ?>
                                  <div class="mt-1 p-2 bg-white border rounded">
                                    <small class="fw-bold text-success"><i class="bi bi-reply me-1"></i> Tu respuesta:</small>
                                    <p class="small text-muted fst-italic mb-0 resp-clamp">"<?= htmlspecialchars($respuestaCliente) ?>"</p>
                                  </div>
                                <?php else: ?>
                                  <button type="button" class="btn btn-sm btn-outline-primary mt-1 btn-responder-resena"
                                    data-id="<?= $valoracionId ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalResponderCliente">
                                    <i class="bi bi-reply me-1"></i> Responder
                                  </button>
                                <?php endif; ?>
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
  <div class="modal fade modal-cliente" id="modalDetalleServicio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="det-titulo">Detalles del servicio</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="det-info"></div>
          <hr class="my-3">
          <h6 class="fw-semibold mb-2">
            <i class="bi bi-clock-history me-1 text-primary"></i> Historial de seguimiento
          </h6>
          <div id="det-seguimiento" style="max-height:240px;overflow-y:auto;" class="mb-3"></div>
          <div id="det-form-comentario" style="display:none;">
            <h6 class="small fw-semibold mb-2">
              <i class="bi bi-chat-dots me-1"></i> Agregar comentario
            </h6>
            <form id="form-seg-cliente" enctype="multipart/form-data">
              <input type="hidden" name="contrato_id" id="seg-contrato-id">
              <div class="mb-2">
                <textarea name="comentario" class="form-control form-control-sm" rows="2"
                  placeholder="Escribe un comentario…" required maxlength="1000"></textarea>
              </div>
              <div class="mb-2">
                <input type="file" name="archivo" class="form-control form-control-sm"
                  accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.txt">
                <small class="text-muted">Opcional — PDF, imagen o doc (máx 5 MB)</small>
              </div>
              <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-send me-1"></i> Enviar
              </button>
            </form>
          </div>
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

  <!-- Modal Responder reseña del proveedor -->
  <div class="modal fade modal-cliente" id="modalResponderCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="bi bi-reply me-2"></i>Responder al proveedor</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <input type="hidden" id="resp-valoracion-id">
          <div class="mb-3">
            <label class="form-label fw-bold">Tu respuesta:</label>
            <textarea class="form-control" id="resp-texto" rows="4" maxlength="500"
              placeholder="Escribe tu respuesta al comentario del proveedor…"></textarea>
            <div class="form-text text-end"><span id="resp-contador">0</span>/500</div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="btn-enviar-respuesta">
            <i class="bi bi-send me-1"></i> Enviar respuesta
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Calificar -->
  <div class="modal fade modal-cliente" id="modalCalificar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
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

  <!-- ── Modal detalles + seguimiento ──────────────────────────────── -->
  <script>
    const BASE_URL_CLI = '<?= BASE_URL ?>';
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
    const ESTADOS_ACTIVOS = ['pendiente', 'confirmado', 'en_proceso'];

    function renderTimelineCli(entries) {
      if (!entries.length) {
        return '<p class="text-muted small fst-italic text-center">Sin actualizaciones aún.</p>';
      }
      return entries.map(function(e) {
        const esEstado = e.estado_nuevo !== null;
        const icon = esEstado ? 'bi-arrow-right-circle-fill text-primary' : 'bi-chat-dots-fill text-secondary';
        const fecha = new Date(e.created_at).toLocaleString('es-CO', { dateStyle: 'short', timeStyle: 'short' });
        const estadoHtml = esEstado
          ? `<div class="mb-1"><span class="badge bg-secondary">${e.estado_anterior || '—'}</span> <i class="bi bi-arrow-right small"></i> <span class="badge bg-primary">${e.estado_nuevo}</span></div>`
          : '';
        const texto = e.descripcion || e.comentario || '';
        const archivoHtml = e.archivo_adjunto
          ? `<a href="${BASE_URL_CLI}/${e.archivo_adjunto}" target="_blank" class="d-block small mt-1"><i class="bi bi-paperclip me-1"></i>Archivo adjunto</a>`
          : '';
        return `<div class="d-flex gap-2 mb-3">
          <div class="pt-1"><i class="bi ${icon} fs-6"></i></div>
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between mb-1">
              <span class="fw-semibold small">${e.responsable_nombre}</span>
              <span class="text-muted" style="font-size:.72rem;">${fecha}</span>
            </div>
            ${estadoHtml}
            ${texto ? `<p class="mb-0 small">${texto}</p>` : ''}
            ${archivoHtml}
          </div>
        </div>`;
      }).join('');
    }

    function cargarSeguimientoCliente(contratoId) {
      const cont = document.getElementById('det-seguimiento');
      cont.innerHTML = '<div class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
      fetch(BASE_URL_CLI + '/cliente/contrato/seguimiento?id=' + contratoId, { credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(res) {
          cont.innerHTML = res.ok
            ? renderTimelineCli(res.data)
            : '<p class="text-danger small">No se pudo cargar el historial.</p>';
        })
        .catch(function() {
          cont.innerHTML = '<p class="text-muted small">Error al conectar.</p>';
        });
    }

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

      document.getElementById('det-info').innerHTML = `
        <dl class="row mb-0">
          <dt class="col-sm-5">Proveedor</dt><dd class="col-sm-7">${proveedor}</dd>
          <dt class="col-sm-5">Estado</dt><dd class="col-sm-7">${badge}</dd>
          <dt class="col-sm-5">Fecha</dt><dd class="col-sm-7">${fecha}</dd>
          <dt class="col-sm-5">Ubicación</dt><dd class="col-sm-7">${ciudad}${zona ? ' — ' + zona : ''}</dd>
          <dt class="col-sm-5">Monto</dt><dd class="col-sm-7">${montoHtml}</dd>
          ${descHtml}
        </dl>`;

      const formCont = document.getElementById('det-form-comentario');
      document.getElementById('seg-contrato-id').value = contratoId;
      formCont.style.display = ESTADOS_ACTIVOS.includes(estado) ? 'block' : 'none';

      const pdfLink = document.getElementById('det-pdf-link');
      if (pdfLink) pdfLink.href = BASE_URL_CLI + '/cliente/contrato-pdf?id=' + contratoId;

      if (contratoId) cargarSeguimientoCliente(parseInt(contratoId));

      bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleServicio')).show();
    });

    // Comment form submit
    document.getElementById('form-seg-cliente')?.addEventListener('submit', function(e) {
      e.preventDefault();
      const btn = this.querySelector('[type="submit"]');
      btn.disabled = true;
      const fd = new FormData(this);
      fetch(BASE_URL_CLI + '/cliente/contrato/comentario', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(res) {
          if (res.ok) {
            document.querySelector('#form-seg-cliente textarea').value = '';
            document.querySelector('#form-seg-cliente input[type="file"]').value = '';
            cargarSeguimientoCliente(parseInt(document.getElementById('seg-contrato-id').value));
          } else {
            Swal.fire('Error', res.message || 'No se pudo enviar.', 'error');
          }
          btn.disabled = false;
        })
        .catch(function() {
          Swal.fire('Error', 'Error de conexión.', 'error');
          btn.disabled = false;
        });
    });
  </script>


  <style>
    .resp-clamp {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .resp-clamp.expandida {
      display: block;
      overflow: visible;
    }
  </style>

  <script src="<?= BASE_URL ?>/public/assets/dashboard/js/dashboard-cliente.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>

  <script>
    // Expandir/colapsar textos de respuesta truncados
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.resp-clamp').forEach(function(p) {
        if (p.scrollHeight <= p.clientHeight + 4) return; // no truncado, no agregar link
        const link = document.createElement('a');
        link.href = '#';
        link.className = 'small text-primary d-block mt-1';
        link.textContent = 'Ver más';
        link.addEventListener('click', function(e) {
          e.preventDefault();
          p.classList.toggle('expandida');
          link.textContent = p.classList.contains('expandida') ? 'Ver menos' : 'Ver más';
        });
        p.insertAdjacentElement('afterend', link);
      });
    });

    // Modal responder reseña — llenar id al abrir
    document.addEventListener('click', function(e) {
      const btn = e.target.closest('.btn-responder-resena');
      if (!btn) return;
      document.getElementById('resp-valoracion-id').value = btn.dataset.id || '';
      document.getElementById('resp-texto').value = '';
      document.getElementById('resp-contador').textContent = '0';
    });

    document.getElementById('resp-texto')?.addEventListener('input', function() {
      document.getElementById('resp-contador').textContent = this.value.length;
    });

    document.getElementById('btn-enviar-respuesta')?.addEventListener('click', async function() {
      const id     = document.getElementById('resp-valoracion-id').value;
      const texto  = document.getElementById('resp-texto').value.trim();
      if (!texto) {
        Swal.fire('Atención', 'Escribe una respuesta antes de enviar.', 'warning');
        return;
      }
      this.disabled = true;
      const body = new URLSearchParams({ id_valoracion: id, texto_respuesta: texto });
      try {
        const res  = await fetch(BASE_URL_CLI + '/cliente/resenas/responder', { method: 'POST', body, credentials: 'same-origin' });
        const data = await res.json();
        if (data.ok) {
          bootstrap.Modal.getInstance(document.getElementById('modalResponderCliente')).hide();
          await Swal.fire('¡Listo!', data.message, 'success');
          location.reload();
        } else {
          Swal.fire('Error', data.message, 'error');
        }
      } catch {
        Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
      }
      this.disabled = false;
    });
  </script>
</body>

</html>