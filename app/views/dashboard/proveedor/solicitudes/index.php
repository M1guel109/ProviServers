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

// Aquí metes también los recién contratados
$serviciosEnProceso = array_values(array_filter(
  $serviciosAll,
  fn($s) => in_array(($s['estado'] ?? ''), ['pendiente', 'confirmado', 'en_proceso'], true)
));

$serviciosCompletados = array_values(array_filter(
  $serviciosAll,
  fn($s) => ($s['estado'] ?? '') === 'finalizado'
));

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
        $contains($s['publicacion_titulo_cotizacion'] ?? '', $q) ||
        $contains($s['publicacion_titulo_solicitud'] ?? '', $q) ||
        $contains($s['cotizacion_titulo'] ?? '', $q) ||
        $contains($s['solicitud_titulo'] ?? '', $q) ||
        $contains($s['necesidad_titulo'] ?? '', $q);
    }

    $okFecha = true;
    if ($fh !== '') {
      $fecha =
        $s['fecha_ejecucion']
        ?? $s['necesidad_fecha_preferida']
        ?? $s['solicitud_fecha_preferida']
        ?? $s['fecha_solicitud']
        ?? null;

      $okFecha = $matchFecha($fecha, $fh);
    }

    return $okQ && $okFecha;
  }));

  $serviciosCompletados = array_values(array_filter($serviciosCompletados, function ($s) use ($q, $fh, $contains, $matchFecha) {
    $okQ = true;
    if ($q !== '') {
      $okQ =
        $contains($s['cliente_nombre'] ?? '', $q) ||
        $contains($s['cliente_telefono'] ?? '', $q) ||
        $contains($s['servicio_nombre'] ?? '', $q) ||
        $contains($s['publicacion_titulo_cotizacion'] ?? '', $q) ||
        $contains($s['publicacion_titulo_solicitud'] ?? '', $q) ||
        $contains($s['cotizacion_titulo'] ?? '', $q) ||
        $contains($s['solicitud_titulo'] ?? '', $q) ||
        $contains($s['necesidad_titulo'] ?? '', $q);
    }

    $okFecha = true;
    if ($fh !== '') {
      $fecha =
        $s['fecha_ejecucion']
        ?? $s['necesidad_fecha_preferida']
        ?? $s['solicitud_fecha_preferida']
        ?? $s['fecha_solicitud']
        ?? null;

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

  <!-- CSS específicos -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/Solicitudes.css">


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
            <option value="alta" <?= $urg === 'alta' ? 'selected' : '' ?>>Alta</option>
            <option value="media" <?= $urg === 'media' ? 'selected' : '' ?>>Media</option>
            <option value="baja" <?= $urg === 'baja' ? 'selected' : '' ?>>Baja</option>
          </select>
        </div>

        <div class="col-md-2">
          <select class="form-select" name="fh">
            <option value="">Fecha</option>
            <option value="hoy" <?= $fh === 'hoy' ? 'selected' : '' ?>>Hoy</option>
            <option value="semana" <?= $fh === 'semana' ? 'selected' : '' ?>>Esta semana</option>
            <option value="mes" <?= $fh === 'mes' ? 'selected' : '' ?>>Este mes</option>
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
          <button class="nav-link <?= $tab === 'nuevas' ? 'active' : '' ?>"
            data-bs-toggle="tab"
            data-bs-target="#tab-nuevas"
            type="button"
            role="tab"
            data-tab="nuevas">
            Nuevas <span class="badge bg-secondary ms-1"><?= $totalNuevas ?></span>
          </button>
        </li>

        <li class="nav-item" role="presentation">
          <button class="nav-link <?= $tab === 'proceso' ? 'active' : '' ?>"
            data-bs-toggle="tab"
            data-bs-target="#tab-proceso"
            type="button"
            role="tab"
            data-tab="proceso">
            En proceso <span class="badge bg-primary ms-1"><?= $totalEnProceso ?></span>
          </button>
        </li>

        <li class="nav-item" role="presentation">
          <button class="nav-link <?= $tab === 'completadas' ? 'active' : '' ?>"
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
        <div class="tab-pane fade <?= $tab === 'nuevas' ? 'show active' : '' ?>" id="tab-nuevas" role="tabpanel">
          <?php include __DIR__ . '/partials/nuevas.php'; ?>
        </div>

        <div class="tab-pane fade <?= $tab === 'proceso' ? 'show active' : '' ?>" id="tab-proceso" role="tabpanel">
          <?php include __DIR__ . '/partials/enProceso.php'; ?>
        </div>

        <div class="tab-pane fade <?= $tab === 'completadas' ? 'show active' : '' ?>" id="tab-completadas" role="tabpanel">
          <?php include __DIR__ . '/partials/completadas.php'; ?>
        </div>
      </div>
    </section>
  </main>

  <!-- Modal único para detalle (usado por verDetalle() en tarjetas) -->
  <!-- Modal único para detalle (usado por verDetalle() en tarjetas) -->
  <div class="modal fade" id="modalDetalleSolicitud" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- 👈 Agregué modal-dialog-centered -->
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

  <!-- Modal de Seguimiento (centrado como los demás) -->
  <div class="modal fade" id="modalSeguimiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow">

        <div class="modal-header bg-primary-detalle text-white">
          <h5 class="modal-title">
            <i class="bi bi-clipboard-pulse me-2"></i> Seguimiento del Servicio
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">

          <!-- Información del servicio -->
          <div class="bg-light p-3 rounded-3 mb-4">
            <h6 id="seg-servicio-nombre" class="fw-bold mb-2">Cargando...</h6>
            <p class="mb-0 text-muted"><i class="bi bi-person me-2"></i> Cliente: <span id="seg-cliente-nombre"></span></p>
          </div>

          <!-- Tabs -->
          <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#tab-historial-modal" type="button" role="tab">
                <i class="bi bi-clock-history me-1"></i> Historial
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link fw-bold text-success" data-bs-toggle="tab" data-bs-target="#tab-nuevo-avance-modal" type="button" role="tab">
                <i class="bi bi-plus-circle me-1"></i> Añadir Avance
              </button>
            </li>
          </ul>

          <div class="tab-content mt-3">

            <!-- Tab Historial -->
            <div class="tab-pane fade show active" id="tab-historial-modal" role="tabpanel">
              <div class="timeline" id="contenedor-timeline-modal">
                <div class="timeline-item">
                  <div class="timeline-marker bg-secondary"><i class="bi bi-play-fill text-white"></i></div>
                  <div class="timeline-content bg-light p-3 rounded-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <h6 class="fw-bold text-dark mb-0">Servicio Iniciado</h6>
                      <small class="text-muted">Generado automáticamente</small>
                    </div>
                    <p class="text-secondary small mb-0">El proveedor aceptó la solicitud y el servicio comenzó.</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tab Añadir Avance -->
            <div class="tab-pane fade" id="tab-nuevo-avance-modal" role="tabpanel">
              <form id="formSeguimientoModal">
                <input type="hidden" id="seg-contrato-id-modal" name="contrato_id">

                <div class="mb-3">
                  <label class="form-label small fw-bold">Estado del Servicio</label>
                  <select class="form-select" name="estado_actual" id="seg-estado-modal">
                    <option value="en_proceso" selected>🔵 Mantener en Proceso</option>
                    <option value="finalizado">✅ Marcar como Finalizado</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label small fw-bold">Título del Avance</label>
                  <input type="text" class="form-control" name="titulo" placeholder="Ej: Compra de materiales..." required>
                </div>

                <div class="mb-3">
                  <label class="form-label small fw-bold">Detalles / Comentarios</label>
                  <textarea class="form-control" name="descripcion" rows="4" placeholder="Explica qué se hizo hoy..." required></textarea>
                </div>

                <div class="mb-4">
                  <label class="form-label small fw-bold">Archivo Adjunto <span class="text-muted fw-normal">(Opcional)</span></label>
                  <input type="file" class="form-control" name="archivo">
                  <div class="form-text">Fotos del avance o recibos (Max 5MB).</div>
                </div>
              </form>
            </div>
          </div>

        </div>

        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" onclick="document.getElementById('formSeguimientoModal').submit()">
            <i class="bi bi-send me-2"></i> Publicar Avance
          </button>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    document.querySelectorAll('button[data-bs-toggle="tab"][data-tab]').forEach(btn => {
      btn.addEventListener('shown.bs.tab', () => {
        const tabValue = btn.getAttribute('data-tab');
        if (!tabValue) return;

        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabValue);
        window.history.replaceState({}, '', url);

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

      const tituloServicio =
        data.servicio_nombre ??
        data.publicacion_titulo_cotizacion ??
        data.publicacion_titulo_solicitud ??
        data.publicacion_titulo ??
        data.cotizacion_titulo ??
        data.solicitud_titulo ??
        data.necesidad_titulo ??
        'N/A';

      const fechaTexto =
        data.fecha_ejecucion ??
        data.necesidad_fecha_preferida ??
        data.solicitud_fecha_preferida ??
        data.fecha_preferida ??
        data.fecha_solicitud ??
        'Sin fecha';

      const franjaTexto =
        data.necesidad_franja_horaria ??
        data.solicitud_franja_horaria ??
        data.franja_horaria ??
        'N/A';

      const direccionTexto =
        data.necesidad_direccion ??
        data.solicitud_direccion ??
        data.direccion ??
        data.direccion_servicio ??
        'N/A';

      const ciudadTexto =
        data.necesidad_ciudad ??
        data.solicitud_ciudad ??
        data.ciudad ??
        'N/A';

      const zonaTexto =
        data.necesidad_zona ??
        data.solicitud_zona ??
        data.zona ??
        '';

      body.innerHTML = `
      <div class="row g-3">
        <div class="col-md-6">
          <div class="p-3 bg-light rounded">
            <div class="fw-semibold text-dark mb-1">Cliente</div>
            <div>${escapeHtml(data.nombre_cliente ?? data.cliente_nombre ?? 'N/A')}</div>
            <div class="text-muted">
              <i class="bi bi-telephone"></i>
              ${escapeHtml(data.telefono_cliente ?? data.cliente_telefono ?? 'N/A')}
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="p-3 bg-light rounded">
            <div class="fw-semibold text-dark mb-1">Servicio</div>
            <div>${escapeHtml(tituloServicio)}</div>
            <div class="text-muted">Estado: ${escapeHtml(data.estado ?? 'pendiente')}</div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="p-3 bg-light rounded">
            <div class="fw-semibold text-dark mb-1">Fecha / horario</div>
            <div><i class="bi bi-calendar3"></i> ${escapeHtml(fechaTexto)}</div>
            <div class="text-muted"><i class="bi bi-clock"></i> ${escapeHtml(franjaTexto)}</div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="p-3 bg-light rounded">
            <div class="fw-semibold text-dark mb-1">Ubicación</div>
            <div><i class="bi bi-geo-alt"></i> ${escapeHtml(direccionTexto)}</div>
            <div class="text-muted">
              ${escapeHtml(ciudadTexto)}${String(zonaTexto).trim() ? ' · ' + escapeHtml(zonaTexto) : ''}
            </div>
          </div>
        </div>
      </div>
    `;

      const modal = new bootstrap.Modal(document.getElementById('modalDetalleSolicitud'));
      modal.show();
    }

    const BASE_URL = "<?= BASE_URL ?>";
  </script>

  <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/enProceso.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/completadas.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>

</body>

</html>