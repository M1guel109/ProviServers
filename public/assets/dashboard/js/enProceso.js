// enProceso.js — Seguimiento de contratos para el proveedor

function renderTimelineProv(entries) {
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
      ? `<a href="${BASE_URL}/${e.archivo_adjunto}" target="_blank" class="d-block small mt-1"><i class="bi bi-paperclip me-1"></i>Archivo adjunto</a>`
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

function cargarSeguimientoProv(contratoId) {
  const cont = document.getElementById('prov-seguimiento');
  cont.innerHTML = '<div class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
  fetch(BASE_URL + '/proveedor/contrato/seguimiento?id=' + contratoId, { credentials: 'same-origin' })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      cont.innerHTML = res.ok
        ? renderTimelineProv(res.data)
        : '<p class="text-danger small">No se pudo cargar el historial.</p>';
    })
    .catch(function() {
      cont.innerHTML = '<p class="text-muted small">Error de conexión.</p>';
    });
}

// Abrir modal al hacer clic en "Actualizar Estado"
document.addEventListener('click', function(e) {
  const btn = e.target.closest('.btn-actualizar');
  if (!btn) return;

  const card       = btn.closest('.tarjeta-proceso');
  const contratoId = card?.dataset.contratoId || '';
  const estadoActual = card?.dataset.estado || 'en_proceso';

  document.getElementById('prov-contrato-id').value     = contratoId;
  document.getElementById('prov-seg-contrato-id').value = contratoId;

  const tituloEl = card?.querySelector('.proceso-titulo');
  document.getElementById('prov-titulo').textContent =
    tituloEl ? tituloEl.textContent.trim() : 'Seguimiento del servicio';

  const sel = document.getElementById('prov-estado-select');
  if (sel) sel.value = estadoActual;

  if (contratoId) cargarSeguimientoProv(parseInt(contratoId));

  bootstrap.Modal.getOrCreateInstance(document.getElementById('modalSeguimientoProveedor')).show();
});

// Actualizar estado del contrato
document.getElementById('form-estado-proveedor')?.addEventListener('submit', function(e) {
  e.preventDefault();
  const btn = this.querySelector('[type="submit"]');
  btn.disabled = true;
  const fd = new FormData(this);
  fetch(BASE_URL + '/proveedor/actualizar-estado', { method: 'POST', body: fd, credentials: 'same-origin' })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.success) {
        Swal.fire({ icon: 'success', title: 'Estado actualizado', text: res.message, timer: 2000, showConfirmButton: false })
          .then(function() { location.reload(); });
      } else {
        Swal.fire('Error', res.message || 'No se pudo actualizar.', 'error');
        btn.disabled = false;
      }
    })
    .catch(function() {
      Swal.fire('Error', 'Error de conexión.', 'error');
      btn.disabled = false;
    });
});

// Enviar comentario del proveedor
document.getElementById('form-seg-proveedor')?.addEventListener('submit', function(e) {
  e.preventDefault();
  const form = this;
  const btn  = form.querySelector('[type="submit"]');
  btn.disabled = true;
  const fd = new FormData(form);
  fetch(BASE_URL + '/proveedor/contrato/comentario', { method: 'POST', body: fd, credentials: 'same-origin' })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.ok) {
        form.querySelector('textarea').value       = '';
        form.querySelector('input[type="file"]').value = '';
        cargarSeguimientoProv(parseInt(document.getElementById('prov-seg-contrato-id').value));
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
