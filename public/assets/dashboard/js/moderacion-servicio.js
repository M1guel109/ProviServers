/* moderacion-servicio.js
   Gestiona la tabla de moderación y el modal de detalle de servicios. */

// =======================================================
// 1. DELEGACIÓN DE EVENTOS (TABLA PRINCIPAL)
// =======================================================
document.addEventListener('click', function (e) {

    // Ver detalle
    const btnView = e.target.closest('.btn-view');
    if (btnView) {
        e.preventDefault();
        const id = btnView.dataset.id;
        if (id) cargarDetalleServicio(id);
        return;
    }

    // Aprobar
    const btnApprove = e.target.closest('.btn-approve');
    if (btnApprove) {
        e.preventDefault();
        confirmarAprobacion(btnApprove.dataset.id);
        return;
    }

    // Rechazar
    const btnReject = e.target.closest('.btn-reject');
    if (btnReject) {
        e.preventDefault();
        confirmarRechazo(btnReject.dataset.id);
        return;
    }
});

// =======================================================
// 2. CARGAR DETALLE EN MODAL (AJAX GET)
// =======================================================
function cargarDetalleServicio(id) {
    const modalElement = document.getElementById('modalDetalleServicio');
    if (!modalElement) return;

    const modal         = new bootstrap.Modal(modalElement);
    const loader        = document.getElementById('loader-detalle');
    const contenido     = document.getElementById('contenido-detalle');
    const footerActions = document.getElementById('modal-acciones-footer');

    // Resetear UI
    loader.classList.remove('d-none');
    contenido.classList.add('d-none');
    footerActions.classList.add('d-none');
    modal.show();

    // ✅ CORREGIDO: ruta sin "api"
    fetch(`${BASE_URL}/admin/servicio-detalle?id=${id}`)
        .then(res => {
            if (!res.ok) throw new Error('Error en la respuesta del servidor');
            return res.json();
        })
        .then(data => {
            if (data.error) throw new Error(data.error);

            // Textos básicos
            setText('modal-titulo',      data.nombre);
            setText('modal-categoria',   data.categoria);
            setText('modal-descripcion', data.descripcion);

            // Precio
            const precioF = new Intl.NumberFormat('es-CO', {
                style: 'currency', currency: 'COP'
            }).format(data.precio || 0);
            setText('modal-precio', Number(data.precio) > 0 ? precioF : 'A Convenir');

            // Foto
            const img = document.getElementById('modal-foto-servicio');
            if (img) {
                img.src = `${BASE_URL}/public/uploads/servicios/${data.foto || 'default_service.png'}`;
                img.onerror = () => {
                    img.src = `${BASE_URL}/public/uploads/servicios/default_service.png`;
                };
            }

            // Datos del proveedor
            setText('modal-proveedor',          data.proveedor_nombre);
            setText('modal-proveedor-email',    data.proveedor_email    || 'No disponible');
            setText('modal-proveedor-tel',      data.proveedor_tel      || 'No disponible');
            setText('modal-proveedor-ubicacion',data.proveedor_ubicacion|| 'Sin ubicación');

            // Fecha
            if (data.created_at) {
                const fecha = new Date(data.created_at);
                setText('modal-fecha', fecha.toLocaleDateString('es-CO', {
                    year: 'numeric', month: 'long', day: 'numeric'
                }));
            }

            // Badge de estado
            const badge  = document.getElementById('modal-estado-badge');
            const estado = (data.estado || 'pendiente').toLowerCase();

            if (badge) {
                badge.textContent = estado.toUpperCase();
                badge.className   = 'badge rounded-pill px-4 py-2 fs-6 shadow-sm';
                if (estado === 'aprobado')       badge.classList.add('bg-success');
                else if (estado === 'rechazado') badge.classList.add('bg-danger');
                else                             badge.classList.add('bg-warning', 'text-dark');
            }

            // Botones del modal — clonar para limpiar eventos anteriores
            const btnApprove = document.querySelector('.btn-modal-approve');
            const btnReject  = document.querySelector('.btn-modal-reject');
            const newApprove = btnApprove.cloneNode(true);
            const newReject  = btnReject.cloneNode(true);
            btnApprove.parentNode.replaceChild(newApprove, btnApprove);
            btnReject.parentNode.replaceChild(newReject, btnReject);

            // Mostrar botones solo si el servicio está pendiente
            if (estado === 'pendiente') {
                footerActions.classList.remove('d-none');
                newApprove.addEventListener('click', () => {
                    modal.hide();
                    confirmarAprobacion(data.id);
                });
                newReject.addEventListener('click', () => {
                    modal.hide();
                    confirmarRechazo(data.id);
                });
            }
            // Si ya está aprobado o rechazado, los botones permanecen ocultos

            // Mostrar contenido
            loader.classList.add('d-none');
            contenido.classList.remove('d-none');
        })
        .catch(err => {
            console.error('Error cargando detalle:', err);
            modal.hide();
            Swal.fire('Error', 'No se pudo cargar la información del servicio.', 'error');
        });
}

// Helper — asignar texto de forma segura
function setText(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text ?? '';
}

// =======================================================
// 3. CONFIRMACIONES SWEETALERT
// =======================================================
function confirmarAprobacion(id) {
    Swal.fire({
        title: '¿Aprobar servicio?',
        text: 'El servicio será visible para todos los clientes.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0066ff',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, Aprobar',
        cancelButtonText: 'Cancelar'
    }).then(result => {
        if (result.isConfirmed) enviarEstado(id, 'aprobado');
    });
}

function confirmarRechazo(id) {
    Swal.fire({
        title: '¿Rechazar servicio?',
        text: 'Indica el motivo del rechazo:',
        input: 'textarea',
        inputPlaceholder: 'Ej: La foto es borrosa, falta descripción...',
        inputAttributes: { 'aria-label': 'Motivo del rechazo' },
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Rechazar',
        cancelButtonText: 'Cancelar',
        preConfirm: (motivo) => {
            if (!motivo || !motivo.trim()) {
                Swal.showValidationMessage('El motivo es obligatorio');
            }
            return motivo;
        }
    }).then(result => {
        if (result.isConfirmed) enviarEstado(id, 'rechazado', result.value);
    });
}

// =======================================================
// 4. ENVÍO AL SERVIDOR (AJAX POST)
// =======================================================
function enviarEstado(id, estado, motivo = '') {
    const formData = new FormData();
    formData.append('id',     id);
    formData.append('estado', estado);
    if (motivo) formData.append('motivo', motivo);

    // ✅ CORREGIDO: ruta sin "api"
    fetch(`${BASE_URL}/admin/moderacion-actualizar`, {
        method: 'POST',
        body: formData
    })
    .then(res => {
        if (!res.ok) throw new Error('Error en la respuesta del servidor');
        return res.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: estado === 'aprobado' ? '¡Aprobado!' : '¡Rechazado!',
                text: 'El estado se actualizó correctamente.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            throw new Error(data.error || 'Error desconocido');
        }
    })
    .catch(err => {
        console.error('Error enviando estado:', err);
        Swal.fire('Error', err.message, 'error');
    });
}

// =======================================================
// 5. DATATABLES
// =======================================================
document.addEventListener('DOMContentLoaded', () => {

    let tableMain = new DataTable('#tabla', {
        responsive: true,
        pageLength: 10,
        layout: {
            topStart: 'search',
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        language: {
            url: 'https://cdn.datatables.net/plug-ins/2.0.2/i18n/es-ES.json'
        },
        initComplete: function () {
            styleSearchInput('#tabla_wrapper');
        }
    });

    let tableExport = new DataTable('#tabla-1', {
        responsive: true,
        paging: false,
        layout: {
            topStart: {
                buttons: [
                    {
                        extend: 'copy',
                        text: '<i class="bi bi-clipboard"></i> Copiar',
                        className: 'btn btn-outline-secondary btn-sm'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Moderacion_Servicios'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        title: 'Moderacion_Servicios',
                        orientation: 'landscape'
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer"></i> Imprimir',
                        className: 'btn btn-info btn-sm text-white'
                    }
                ]
            },
            topEnd: 'search'
        },
        language: {
            url: 'https://cdn.datatables.net/plug-ins/2.0.2/i18n/es-ES.json'
        },
        initComplete: function () {
            styleSearchInput('#tabla-1_wrapper');
        }
    });

    function styleSearchInput(wrapperSelector) {
        const wrapper = document.querySelector(wrapperSelector);
        if (!wrapper) return;

        const dtSearch = wrapper.querySelector('.dt-search');
        if (!dtSearch) return;

        const input = dtSearch.querySelector('input[type="search"]');
        if (!input) return;

        const buscadorDiv = document.createElement('div');
        buscadorDiv.className = 'buscador';
        buscadorDiv.innerHTML = `<i class="bi bi-search"></i>`;
        buscadorDiv.appendChild(input);

        dtSearch.innerHTML = '';
        dtSearch.appendChild(buscadorDiv);

        input.setAttribute('placeholder', 'Buscar...');
        input.style.cssText = 'width:100%; border:none; background:transparent; outline:none; padding-left:10px;';
    }

    // Ajustar columnas al cambiar pestaña
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', () => {
            tableMain.columns.adjust().responsive.recalc();
            tableExport.columns.adjust().responsive.recalc();
        });
    });

});