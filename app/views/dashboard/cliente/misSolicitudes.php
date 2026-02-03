<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Mis Solicitudes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
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

    <section class="p-3">
      <div class="section-hero mb-4">
        <p class="breadcrumb">Inicio > Mis solicitudes</p>
        <h1>Mis solicitudes</h1>
        <p>Consulta tus solicitudes por estado y revisa el detalle.</p>
      </div>

      <!-- Tabs -->
      <ul class="nav nav-tabs mb-3">
        <?php
        $tabs = [
          'pendiente' => 'Pendientes',
          'aceptada'  => 'Aceptadas',
          'rechazada' => 'Rechazadas',
          'cancelada' => 'Canceladas'
        ];
        ?>
        <?php foreach ($tabs as $key => $label): ?>
          <li class="nav-item">
            <a class="nav-link <?= ($estado === $key ? 'active' : '') ?>"
              href="<?= BASE_URL ?>/cliente/mis-solicitudes?estado=<?= $key ?>">
              <?= $label ?>
              <span class="badge bg-secondary ms-1"><?= (int)($contadores[$key] ?? 0) ?></span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>

      <div class="row g-4">
        <!-- Listado -->
        <div class="col-lg-6">
          <div class="card p-3">
            <h5 class="mb-3">Listado (<?= htmlspecialchars($tabs[$estado] ?? $estado) ?>)</h5>

            <?php if (!empty($solicitudes)): ?>
              <div class="list-group">
                <?php foreach ($solicitudes as $s): ?>
                  <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                      <div>
                        <strong><?= htmlspecialchars($s['titulo']) ?></strong><br>
                        <small class="text-muted">
                          Proveedor: <?= htmlspecialchars($s['proveedor_nombre'] ?? '-') ?> ·
                          <?= htmlspecialchars($s['ciudad'] ?? '-') ?><?= !empty($s['zona']) ? (' / ' . htmlspecialchars($s['zona'])) : '' ?>
                        </small><br>
                        <small class="text-muted">
                          Fecha: <?= htmlspecialchars($s['fecha_preferida'] ?? '-') ?>
                          <?= !empty($s['franja_horaria']) ? (' · ' . htmlspecialchars($s['franja_horaria'])) : '' ?>
                        </small>
                      </div>

                      <div class="text-end">
                        <span class="badge bg-<?=
                                              $s['estado'] === 'pendiente' ? 'primary' : ($s['estado'] === 'aceptada' ? 'success' : ($s['estado'] === 'rechazada' ? 'secondary' : 'warning'))
                                              ?>">
                          <?= htmlspecialchars($s['estado']) ?>
                        </span>

                        <div class="mt-2">
                          <small class="text-muted">
                            Presupuesto: $ <?= htmlspecialchars($s['presupuesto_estimado'] ?? '0') ?>
                          </small>
                        </div>

                        <a class="btn btn-sm btn-outline-primary mt-2"
                          href="<?= BASE_URL ?>/cliente/mis-solicitudes?estado=<?= urlencode($estado) ?>&id=<?= (int)$s['id'] ?>">
                          Ver detalle
                        </a>

                        <?php if (in_array($s['estado'], ['pendiente', 'aceptada'], true)): ?>
                          <a class="btn btn-sm btn-outline-success mt-2"
                            href="<?= BASE_URL ?>/mensajes/abrir?tipo=solicitud&id=<?= (int)$s['id'] ?>">
                            <i class="bi bi-chat-dots"></i> Mensajes
                          </a>
                        <?php endif; ?>





                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="alert alert-info mb-0">No tienes solicitudes en este estado.</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Detalle -->
        <div class="col-lg-6">
          <div class="card p-3">
            <h5 class="mb-3">Detalle</h5>

            <?php if (!empty($detalle)): ?>
              <p class="mb-1"><strong><?= htmlspecialchars($detalle['titulo']) ?></strong></p>
              <p class="text-muted"><?= nl2br(htmlspecialchars($detalle['descripcion'] ?? '')) ?></p>

              <hr>

              <p class="mb-1"><strong>Proveedor:</strong> <?= htmlspecialchars($detalle['proveedor_nombre'] ?? '-') ?></p>
              <p class="mb-1"><strong>Servicio:</strong> <?= htmlspecialchars($detalle['servicio_nombre'] ?? '-') ?></p>
              <p class="mb-1"><strong>Ciudad/Zona:</strong>
                <?= htmlspecialchars($detalle['ciudad'] ?? '-') ?>
                <?= !empty($detalle['zona']) ? (' / ' . htmlspecialchars($detalle['zona'])) : '' ?>
              </p>
              <p class="mb-1"><strong>Dirección:</strong> <?= htmlspecialchars($detalle['direccion'] ?? '-') ?></p>
              <p class="mb-1"><strong>Fecha:</strong> <?= htmlspecialchars($detalle['fecha_preferida'] ?? '-') ?></p>
              <p class="mb-1"><strong>Horario:</strong> <?= htmlspecialchars($detalle['franja_horaria'] ?? 'Cualquiera') ?></p>
              <p class="mb-3"><strong>Presupuesto:</strong> $ <?= htmlspecialchars($detalle['presupuesto_estimado'] ?? '0') ?></p>

              <?php
              $adj = [];
              if (!empty($detalle['adjuntos'])) {
                $adj = array_filter(array_map('trim', explode(',', $detalle['adjuntos'])));
              }
              ?>

              <h6>Adjuntos</h6>
              <?php if (!empty($adj)): ?>
                <ul class="mb-0">
                  <?php foreach ($adj as $file): ?>
                    <li>
                      <a href="<?= BASE_URL ?>/public/uploads/solicitudes/<?= urlencode($file) ?>" target="_blank">
                        <?= htmlspecialchars($file) ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <div class="text-muted">No hay adjuntos.</div>
              <?php endif; ?>

            <?php else: ?>
              <div class="alert alert-info mb-0">Selecciona una solicitud del listado para ver el detalle.</div>
            <?php endif; ?>

          </div>
        </div>


      </div>

    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>