<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Necesidades</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardCliente.css">
</head>
<body>

<?php
  $currentPage = 'necesidades';
  include_once __DIR__ . '/../../layouts/sidebar_cliente.php';
?>

<main class="contenido">
  <?php include_once __DIR__ . '/../../layouts/header_cliente.php'; ?>

  <section class="p-3">
    <div class="section-hero mb-4">
      <p class="breadcrumb">Inicio > Mis necesidades</p>
      <h1>Mis necesidades</h1>
      <p>Revisa tus necesidades publicadas y las ofertas recibidas.</p>
    </div>

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="card p-3">
          <h5 class="mb-3">Listado</h5>

          <?php if (!empty($misNecesidades)): ?>
            <div class="list-group">
              <?php foreach ($misNecesidades as $n): ?>
                <a class="list-group-item list-group-item-action"
                   href="<?= BASE_URL ?>/cliente/necesidades?id=<?= (int)$n['id'] ?>">
                  <div class="d-flex justify-content-between">
                    <div>
                      <strong><?= htmlspecialchars($n['titulo']) ?></strong><br>
                      <small class="text-muted">
                        <?= htmlspecialchars($n['servicio_nombre'] ?? 'Servicio') ?> ·
                        <?= htmlspecialchars($n['ciudad']) ?>
                      </small>
                    </div>
                    <div class="text-end">
                      <span class="badge bg-<?= $n['estado']==='abierta'?'primary':($n['estado']==='cerrada'?'success':'secondary') ?>">
                        <?= htmlspecialchars($n['estado']) ?>
                      </span>
                      <div><small class="text-muted">Ofertas: <?= (int)$n['total_ofertas'] ?></small></div>
                    </div>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="alert alert-info">Aún no has publicado necesidades.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card p-3">
          <h5 class="mb-3">Detalle</h5>

          <?php if (!empty($detalle)): ?>
            <p class="mb-1"><strong><?= htmlspecialchars($detalle['titulo']) ?></strong></p>
            <p class="text-muted mb-2"><?= htmlspecialchars($detalle['descripcion']) ?></p>

            <p class="mb-1"><strong>Servicio:</strong> <?= htmlspecialchars($detalle['servicio_nombre'] ?? '-') ?></p>
            <p class="mb-1"><strong>Ubicación:</strong> <?= htmlspecialchars($detalle['ciudad'].' '.$detalle['zona']) ?></p>
            <p class="mb-1"><strong>Dirección:</strong> <?= htmlspecialchars($detalle['direccion']) ?></p>
            <p class="mb-1"><strong>Fecha:</strong> <?= htmlspecialchars($detalle['fecha_preferida']) ?></p>
            <?php if (!empty($detalle['hora_preferida'])): ?>
              <p class="mb-1"><strong>Hora:</strong> <?= htmlspecialchars($detalle['hora_preferida']) ?></p>
            <?php endif; ?>
            <p class="mb-3"><strong>Presupuesto:</strong> <?= htmlspecialchars($detalle['presupuesto_estimado'] ?? '-') ?></p>

            <hr>

            <h6>Ofertas recibidas</h6>

            <?php if (!empty($cotizaciones)): ?>
              <?php foreach ($cotizaciones as $c): ?>
                <div class="border rounded p-2 mb-2">
                  <div class="d-flex justify-content-between">
                    <div>
                      <strong><?= htmlspecialchars($c['proveedor_nombre'] ?? 'Proveedor') ?></strong>
                      <div class="text-muted small"><?= htmlspecialchars($c['titulo']) ?></div>
                    </div>
                    <span class="badge bg-<?= $c['estado']==='aceptada'?'success':($c['estado']==='rechazada'?'secondary':'primary') ?>">
                      <?= htmlspecialchars($c['estado']) ?>
                    </span>
                  </div>

                  <?php if (!empty($c['mensaje'])): ?>
                    <div class="small mt-2"><?= nl2br(htmlspecialchars($c['mensaje'])) ?></div>
                  <?php endif; ?>

                  <div class="small mt-2">
                    <strong>Precio:</strong> <?= htmlspecialchars($c['precio'] ?? '-') ?> ·
                    <strong>Tiempo:</strong> <?= htmlspecialchars($c['tiempo_estimado'] ?? '-') ?>
                  </div>

                  <?php if (($detalle['estado'] ?? '') === 'abierta' && ($c['estado'] ?? '') === 'pendiente'): ?>
                    <form class="mt-2" method="POST" action="<?= BASE_URL ?>/cliente/necesidades/aceptar-cotizacion">
                      <input type="hidden" name="cotizacion_id" value="<?= (int)$c['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-success w-100">
                        Aceptar cotización
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="alert alert-warning">Aún no tienes ofertas para esta necesidad.</div>
            <?php endif; ?>

          <?php else: ?>
            <div class="alert alert-info">Selecciona una necesidad del listado para ver detalle y ofertas.</div>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
