<?php
require_once BASE_PATH . '/app/helpers/session_proveedor.php';

// Modelos
require_once BASE_PATH . '/app/models/solicitud.php';
require_once BASE_PATH . '/app/models/ServicioContratado.php';

$usuarioId = (int)($_SESSION['user']['id'] ?? 0);

// Tab activo por querystring (para mantener al recargar)
$tab = $_GET['tab'] ?? 'nuevas';
$tabPermitidos = ['nuevas', 'proceso', 'completadas'];
if (!in_array($tab, $tabPermitidos, true)) $tab = 'nuevas';

// Filtros (estilo Oportunidades)
$q   = trim($_GET['q'] ?? '');
$urg = trim($_GET['urg'] ?? ''); // alta|media|baja (solo aplica a NUEVAS si existe el campo)
$fh  = trim($_GET['fh'] ?? '');  // hoy|semana|mes (aplica según campos disponibles)

// 1) Nuevas solicitudes
$solicitudModel = new Solicitud();
$solicitudesNuevas = $solicitudModel->listarPorProveedor($usuarioId);

// 2) Servicios contratados (en proceso / completadas)
$scModel = new ServicioContratado();
$serviciosAll = $scModel->listarPorProveedorUsuario($usuarioId);

$serviciosEnProceso = array_values(array_filter($serviciosAll, fn($s) => ($s['estado'] ?? '') === 'en_proceso'));
$serviciosCompletados = array_values(array_filter($serviciosAll, fn($s) => ($s['estado'] ?? '') === 'finalizado'));

/**
 * Helpers de filtrado (sin romper si faltan campos)
 */
$contains = function ($haystack, $needle) {
    $haystack = mb_strtolower((string)$haystack);
    $needle   = mb_strtolower((string)$needle);
    return $needle === '' ? true : (mb_strpos($haystack, $needle) !== false);
};

$matchFecha = function (?string $dateValue, string $fh): bool {
    if ($fh === '' || empty($dateValue)) return $fh === '' ? true : false;

    $ts = strtotime($dateValue);
    if ($ts === false) return false;

    $hoy = date('Y-m-d');
    $d   = date('Y-m-d', $ts);

    if ($fh === 'hoy') return $d === $hoy;

    if ($fh === 'semana') {
        // lunes a domingo de la semana actual
        $start = date('Y-m-d', strtotime('monday this week'));
        $end   = date('Y-m-d', strtotime('sunday this week'));
        return ($d >= $start && $d <= $end);
    }

    if ($fh === 'mes') {
        return date('Y-m', $ts) === date('Y-m');
    }

    return true;
};

// Aplicar filtros
if ($q !== '' || $urg !== '' || $fh !== '') {

    // NUEVAS: filtra por búsqueda, urgencia y fecha_preferida si existe
    $solicitudesNuevas = array_values(array_filter($solicitudesNuevas, function ($s) use ($q, $urg, $fh, $contains, $matchFecha) {
        $okQ = true;
        if ($q !== '') {
            $okQ =
                $contains($s['nombre_cliente'] ?? '', $q) ||
                $contains($s['telefono_cliente'] ?? '', $q) ||
                $contains($s['servicio_nombre'] ?? '', $q) ||
                $contains($s['publicacion_titulo'] ?? '', $q);
        }

        $okUrg = true;
        if ($urg !== '') {
            $valor = $s['urgencia'] ?? $s['prioridad'] ?? '';
            $okUrg = mb_strtolower((string)$valor) === mb_strtolower($urg);
        }

        $okFecha = true;
        if ($fh !== '') {
            $okFecha = $matchFecha($s['fecha_preferida'] ?? null, $fh);
        }

        return $okQ && $okUrg && $okFecha;
    }));

    // EN PROCESO: filtra por búsqueda y fecha_solicitud/fecha_inicio si existe
    $serviciosEnProceso = array_values(array_filter($serviciosEnProceso, function ($s) use ($q, $fh, $contains, $matchFecha) {
        $okQ = true;
        if ($q !== '') {
            $okQ =
                $contains($s['cliente_nombre'] ?? '', $q) ||
                $contains($s['cliente_telefono'] ?? '', $q) ||
                $contains($s['servicio_nombre'] ?? '', $q) ||
                $contains($s['solicitud_titulo'] ?? '', $q);
        }

        $okFecha = true;
        if ($fh !== '') {
            $fecha = $s['fecha_inicio'] ?? $s['fecha_solicitud'] ?? null;
            $okFecha = $matchFecha($fecha, $fh);
        }

        return $okQ && $okFecha;
    }));

    // COMPLETADAS: filtra por búsqueda y fecha_fin/updated_at si existe
    $serviciosCompletados = array_values(array_filter($serviciosCompletados, function ($s) use ($q, $fh, $contains, $matchFecha) {
        $okQ = true;
        if ($q !== '') {
            $okQ =
                $contains($s['cliente_nombre'] ?? '', $q) ||
                $contains($s['cliente_telefono'] ?? '', $q) ||
                $contains($s['servicio_nombre'] ?? '', $q) ||
                $contains($s['solicitud_titulo'] ?? '', $q);
        }

        $okFecha = true;
        if ($fh !== '') {
            $fecha = $s['fecha_fin'] ?? $s['updated_at'] ?? null;
            $okFecha = $matchFecha($fecha, $fh);
        }

        return $okQ && $okFecha;
    }));
}

// Stats (después de filtros)
$totalNuevas = count($solicitudesNuevas);
$totalEnProceso = count($serviciosEnProceso);
$totalCompletadas = count($serviciosCompletados);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proviservers | Solicitudes</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboard-Proveedor.css">

  <!-- CSS específicos -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/nuevasSolicitudes.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/enProcesos.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/completadas.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/solicitudes.css">

</head>

<body>
<?php include_once __DIR__ . '/../../../layouts/sidebar_proveedor.php'; ?>

<main class="contenido">
  <?php include_once __DIR__ . '/../../../layouts/header_proveedor.php'; ?>

  <!-- Título estilo "Oportunidades" -->
  <section class="mb-4 px-4 pt-4">
    <h1 class="fw-bold mb-2">Solicitudes</h1>
    <p class="text-muted">Gestiona solicitudes nuevas, servicios en proceso y completadas desde un solo lugar.</p>
  </section>

  <!-- Filtros estilo "Oportunidades" -->
  <section class="filtros-container px-4 mb-3">
    <form action="<?= BASE_URL ?>/proveedor/solicitudes" method="GET" class="row g-3">
      <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">

      <div class="col-md-5">
        <div class="input-group">
          <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
          <input type="text"
                 class="form-control border-start-0 bg-light"
                 name="q"
                 value="<?= htmlspecialchars($q) ?>"
                 placeholder="Buscar por cliente, servicio o título...">
        </div>
      </div>

      <div class="col-md-3">
        <select class="form-select" name="urg">
          <option value="">Urgencia</option>
          <option value="alta"  <?= $urg==='alta'?'selected':'' ?>>Alta</option>
          <option value="media" <?= $urg==='media'?'selected':'' ?>>Media</option>
          <option value="baja"  <?= $urg==='baja'?'selected':'' ?>>Baja</option>
        </select>
      </div>

      <div class="col-md-2">
        <select class="form-select" name="fh">
          <option value="">Fecha</option>
          <option value="hoy"   <?= $fh==='hoy'?'selected':'' ?>>Hoy</option>
          <option value="semana"<?= $fh==='semana'?'selected':'' ?>>Esta semana</option>
          <option value="mes"   <?= $fh==='mes'?'selected':'' ?>>Este mes</option>
        </select>
      </div>

      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100 fw-semibold">Filtrar</button>
      </div>
    </form>
  </section>

  <!-- Tabs -->
  <section class="px-4 pb-4">
    <ul class="nav nav-tabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $tab==='nuevas'?'active':'' ?>"
                data-bs-toggle="tab"
                data-bs-target="#tab-nuevas"
                type="button"
                role="tab"
                data-tab="nuevas">
          Nuevas <span class="badge bg-secondary ms-1"><?= $totalNuevas ?></span>
        </button>
      </li>

      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $tab==='proceso'?'active':'' ?>"
                data-bs-toggle="tab"
                data-bs-target="#tab-proceso"
                type="button"
                role="tab"
                data-tab="proceso">
          En proceso <span class="badge bg-primary ms-1"><?= $totalEnProceso ?></span>
        </button>
      </li>

      <li class="nav-item" role="presentation">
        <button class="nav-link <?= $tab==='completadas'?'active':'' ?>"
                data-bs-toggle="tab"
                data-bs-target="#tab-completadas"
                type="button"
                role="tab"
                data-tab="completadas">
          Completadas <span class="badge bg-success ms-1"><?= $totalCompletadas ?></span>
        </button>
      </li>
    </ul>

    <div class="tab-content bg-white border border-top-0 p-3">
      <div class="tab-pane fade <?= $tab==='nuevas'?'show active':'' ?>" id="tab-nuevas" role="tabpanel">
        <?php include __DIR__ . '/partials/nuevas.php'; ?>
      </div>

      <div class="tab-pane fade <?= $tab==='proceso'?'show active':'' ?>" id="tab-proceso" role="tabpanel">
        <?php include __DIR__ . '/partials/enProceso.php'; ?>
      </div>

      <div class="tab-pane fade <?= $tab==='completadas'?'show active':'' ?>" id="tab-completadas" role="tabpanel">
        <?php include __DIR__ . '/partials/completadas.php'; ?>
      </div>
    </div>
  </section>
</main>

<!-- Modal único para detalle (usado por verDetalle() en tarjetas) -->
<div class="modal fade" id="modalDetalleSolicitud" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle de la solicitud</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div id="detalleSolicitudBody" class="small text-muted">Cargando...</div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<div class="offcanvas offcanvas-end shadow-lg" tabindex="-1" id="offcanvasSeguimiento" aria-labelledby="offcanvasSeguimientoLabel" style="width: 550px;">
    
    <div class="offcanvas-header bg-primary text-white pb-4">
        <div>
            <h5 class="offcanvas-title fw-bold mb-1" id="offcanvasSeguimientoLabel">
                <i class="bi bi-clipboard-pulse me-2"></i> Seguimiento del Servicio
            </h5>
            <small class="text-white-50" id="seg-servicio-nombre">Cargando...</small>
            <br>
            <small class="text-white-50"><i class="bi bi-person me-1"></i> Cliente: <span id="seg-cliente-nombre"></span></small>
        </div>
        <button type="button" class="btn-close btn-close-white align-self-start" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-0 bg-light d-flex flex-column">
        
        <ul class="nav nav-tabs px-3 pt-3 bg-white border-bottom" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#tab-historial" type="button" role="tab">
                    <i class="bi bi-clock-history me-1"></i> Historial
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-success" data-bs-toggle="tab" data-bs-target="#tab-nuevo-avance" type="button" role="tab">
                    <i class="bi bi-plus-circle me-1"></i> Añadir Avance
                </button>
            </li>
        </ul>

        <div class="tab-content flex-grow-1 overflow-auto p-4">
            
            <div class="tab-pane fade show active" id="tab-historial" role="tabpanel">
                <div class="timeline" id="contenedor-timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-secondary"><i class="bi bi-play-fill text-white"></i></div>
                        <div class="timeline-content card border-0 shadow-sm p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="fw-bold text-dark mb-0">Servicio Iniciado</h6>
                                <small class="text-muted" style="font-size: 0.75rem;">Generado automáticamente</small>
                            </div>
                            <p class="text-secondary small mb-0">El proveedor aceptó la solicitud y el servicio comenzó.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-nuevo-avance" role="tabpanel">
                <form id="formSeguimiento">
                    <input type="hidden" id="seg-contrato-id" name="contrato_id">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Estado del Servicio</label>
                        <select class="form-select bg-white" name="estado_actual" id="seg-estado">
                            <option value="en_proceso" selected>🔵 Mantener en Proceso</option>
                            <option value="finalizado">✅ Marcar como Finalizado</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Título del Avance</label>
                        <input type="text" class="form-control bg-white" name="titulo" placeholder="Ej: Compra de materiales..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Detalles / Comentarios</label>
                        <textarea class="form-control bg-white" name="descripcion" rows="4" placeholder="Explica qué se hizo hoy..." required></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Archivo Adjunto <span class="text-muted fw-normal">(Opcional)</span></label>
                        <input type="file" class="form-control bg-white" name="archivo">
                        <div class="form-text">Fotos del avance o recibos (Max 5MB).</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="bi bi-send me-2"></i> Publicar Avance</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Mantener ?tab=... sincronizado al cambiar de tab (sin recargar)
  document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(btn => {
    btn.addEventListener('shown.bs.tab', () => {
      const tabValue = btn.getAttribute('data-tab');
      const url = new URL(window.location.href);
      url.searchParams.set('tab', tabValue);
      window.history.replaceState({}, '', url);
      // también actualiza el hidden del formulario, para que al filtrar no pierdas el tab
      const hiddenTab = document.querySelector('form input[name="tab"]');
      if (hiddenTab) hiddenTab.value = tabValue;
    });
  });

  function escapeHtml(str) {
    return String(str ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function verDetalle(data) {
    const body = document.getElementById('detalleSolicitudBody');

    body.innerHTML = `
      <div class="row g-3">
        <div class="col-md-6">
          <div class="p-3 bg-light rounded">
            <div class="fw-semibold text-dark mb-1">Cliente</div>
            <div>${escapeHtml(data.nombre_cliente ?? data.cliente_nombre ?? 'N/A')}</div>
            <div class="text-muted"><i class="bi bi-telephone"></i> ${escapeHtml(data.telefono_cliente ?? data.cliente_telefono ?? 'N/A')}</div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="p-3 bg-light rounded">
            <div class="fw-semibold text-dark mb-1">Servicio</div>
            <div>${escapeHtml(data.servicio_nombre ?? data.publicacion_titulo ?? data.solicitud_titulo ?? 'N/A')}</div>
            <div class="text-muted">Estado: ${escapeHtml(data.estado ?? 'pendiente')}</div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="p-3 bg-light rounded">
            <div class="fw-semibold text-dark mb-1">Fecha / horario</div>
            <div><i class="bi bi-calendar3"></i> ${escapeHtml(data.fecha_preferida ?? data.fecha_solicitud ?? data.fecha_inicio ?? 'Sin fecha')}</div>
            <div class="text-muted"><i class="bi bi-clock"></i> ${escapeHtml(data.franja_horaria ?? 'N/A')}</div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="p-3 bg-light rounded">
            <div class="fw-semibold text-dark mb-1">Detalles</div>
            <div>${escapeHtml(data.descripcion ?? data.mensaje ?? 'Sin detalles adicionales')}</div>
          </div>
        </div>
      </div>
    `;

    const modal = new bootstrap.Modal(document.getElementById('modalDetalleSolicitud'));
    modal.show();
  }

  // Tus JS específicos (deben validar que existan elementos antes de operar)
  const BASE_URL = "<?= BASE_URL ?>";
</script>

<script src="<?= BASE_URL ?>/public/assets/dashBoard/js/enProceso.js"></script>
<script src="<?= BASE_URL ?>/public/assets/dashBoard/js/completadas.js"></script>

</body>
</html>
