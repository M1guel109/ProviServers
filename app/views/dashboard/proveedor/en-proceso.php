<?php
// $servicios y $stats son inyectados por mostrarEnProceso() en proveedor-controller.php
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Servicios en Proceso</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/enProcesos.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-proveedor.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar-proveedor.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-proveedor.php'; ?>

        <section id="titulo-principal" class="section-hero mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-1">Servicios en Proceso</h1>
                    <p class="text-muted mb-0">Gestiona los servicios que actualmente estás realizando.</p>
                </div>
                <div class="col-md-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/proveedor/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">En Proceso</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <section id="estadisticas-proceso">
            <div class="tarjeta-stat">
                <i class="bi bi-hourglass-split icono-stat"></i>
                <div class="stat-info">
                    <div class="stat-numero"><?= $stats['en_proceso'] ?></div>
                    <div class="stat-label">En Proceso</div>
                </div>
            </div>
            </section>

        <section id="filtros-proceso">
            <div class="contenedor-filtros">
                <div class="grupo-filtro">
                    <label for="filtro-categoria">Categoría</label>
                    <select id="filtro-categoria">
                        <option value="">Todas</option>
                        <option value="plomeria">Plomería</option>
                        </select>
                </div>
                </div>
        </section>

        <section id="lista-procesos">
            <?php if (!empty($servicios)): ?>
                <?php foreach ($servicios as $servicio): ?>

                    <div class="tarjeta-proceso"
                         data-contrato-id="<?= $servicio['contrato_id'] ?>"
                         data-estado="<?= htmlspecialchars($servicio['estado'] ?? 'pendiente') ?>">

                        <div class="proceso-header">
                            <div class="proceso-info-principal">
                                <h3 class="proceso-titulo">
                                    <?= htmlspecialchars($servicio['servicio_nombre']) ?>
                                </h3>
                                <div class="proceso-meta">
                                    <span class="badge-categoria">
                                        <i class="bi bi-briefcase"></i> <?= htmlspecialchars($servicio['solicitud_titulo']) ?>
                                    </span>
                                    <span class="proceso-fecha">
                                        <i class="bi bi-calendar3"></i> Inicio: <?= date('d M Y', strtotime($servicio['fecha_solicitud'])) ?>
                                    </span>
                                </div>
                            </div>

                            <?php
                                $estadoMap = [
                                    'pendiente'  => ['label' => 'Pendiente', 'class' => 'media', 'progress' => 25],
                                    'en_proceso' => ['label' => 'En proceso', 'class' => 'alta',  'progress' => 60],
                                    'finalizado' => ['label' => 'Finalizado', 'class' => 'completado', 'progress' => 100],
                                ];
                                $estado = $estadoMap[$servicio['estado']] ?? $estadoMap['pendiente'];
                            ?>

                            <div class="proceso-prioridad">
                                <span class="badge-prioridad badge-estado <?= $estado['class'] ?>">
                                    <?= $estado['label'] ?>
                                </span>
                            </div>
                        </div>

                        <div class="proceso-cliente">
                            <img src="<?= BASE_URL . '/public/uploads/usuarios/' . ($servicio['cliente_foto'] ?: 'default_user.png') ?>" alt="Cliente" class="cliente-avatar">
                            <div class="cliente-info">
                                <div class="cliente-nombre"><?= htmlspecialchars($servicio['cliente_nombre']) ?></div>
                                <div class="cliente-contacto"><i class="bi bi-telephone"></i> <?= htmlspecialchars($servicio['cliente_telefono']) ?></div>
                            </div>
                        </div>

                        <div class="proceso-progreso">
                            <div class="progreso-header">
                                <span class="progreso-label">Estado del servicio</span>
                                <span class="progreso-porcentaje progreso-estado"><?= $estado['label'] ?></span>
                            </div>
                            <div class="barra-progreso">
                                <div class="barra-progreso-fill" style="width: <?= $estado['progress'] ?>%"></div>
                            </div>
                        </div>

                        <div class="proceso-acciones">
                            <button class="btn-accion btn-actualizar">
                                <i class="bi bi-arrow-clockwise"></i> Actualizar Estado
                            </button>
                            
                            <button class="btn-accion btn-contactar">
                                <i class="bi bi-chat-dots"></i> Contactar
                            </button>
                        </div>

                    </div> <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted text-center p-5">No tienes servicios en proceso actualmente.</p>
            <?php endif; ?>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
            crossorigin="anonymous"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Modal Seguimiento Proveedor -->
    <div class="modal fade" id="modalSeguimientoProveedor" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header">
            <h5 class="modal-title fw-bold" id="prov-titulo">Seguimiento del servicio</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">

            <h6 class="fw-semibold mb-2">
              <i class="bi bi-arrow-clockwise me-1 text-primary"></i> Actualizar estado
            </h6>
            <form id="form-estado-proveedor" class="d-flex gap-2 align-items-center mb-3">
              <input type="hidden" name="contrato_id" id="prov-contrato-id">
              <select name="estado_actual" id="prov-estado-select" class="form-select form-select-sm">
                <option value="confirmado">Confirmado</option>
                <option value="en_proceso">En proceso</option>
                <option value="finalizado">Finalizado</option>
                <option value="cancelado_proveedor">Cancelar servicio</option>
              </select>
              <button type="submit" class="btn btn-primary btn-sm text-nowrap">
                <i class="bi bi-check-circle me-1"></i> Guardar
              </button>
            </form>

            <hr>

            <h6 class="fw-semibold mb-2">
              <i class="bi bi-chat-dots me-1 text-secondary"></i> Agregar comentario
            </h6>
            <form id="form-seg-proveedor" enctype="multipart/form-data" class="mb-3">
              <input type="hidden" name="contrato_id" id="prov-seg-contrato-id">
              <div class="mb-2">
                <textarea name="comentario" class="form-control form-control-sm" rows="2"
                  placeholder="Comentario o nota para el cliente…" required maxlength="1000"></textarea>
              </div>
              <div class="mb-2">
                <input type="file" name="archivo" class="form-control form-control-sm"
                  accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.txt">
                <small class="text-muted">Opcional — PDF, imagen o doc (máx 5 MB)</small>
              </div>
              <button type="submit" class="btn btn-secondary btn-sm">
                <i class="bi bi-send me-1"></i> Enviar comentario
              </button>
            </form>

            <hr>

            <h6 class="fw-semibold mb-2">
              <i class="bi bi-clock-history me-1 text-primary"></i> Historial
            </h6>
            <div id="prov-seguimiento" style="max-height:240px;overflow-y:auto;"></div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>

    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/enProceso.js"></script>

</body>
</html>