/**
 * moderacionServicio.js
 * Gestiona la interacción de la tabla de servicios y el modal de detalle.
 */

// 1. VALIDACIÓN INICIAL
if (typeof BASE_URL === 'undefined') {
    console.error("🚨 ERROR CRÍTICO: BASE_URL no está definida.");
} else {
    console.log("✅ Script de Moderación cargado. BASE_URL:", BASE_URL);
}

// =======================================================
// 2. DELEGACIÓN DE EVENTOS (TABLA PRINCIPAL)
// =======================================================
document.addEventListener('click', function (e) {

    // A. CLIC EN "VER DETALLE" (OJITO)
    const btnView = e.target.closest('.btn-view');
    if (btnView) {
        e.preventDefault();
        const id = btnView.dataset.id;
        if(id) cargarDetalleServicio(id);
    }

    // B. CLIC EN "APROBAR" (BOTÓN EN LA TABLA)
    const btnApprove = e.target.closest('.btn-approve');
    if (btnApprove) {
        e.preventDefault();
        confirmarAprobacion(btnApprove.dataset.id);
    }

    // C. CLIC EN "RECHAZAR" (BOTÓN EN LA TABLA)
    const btnReject = e.target.closest('.btn-reject');
    if (btnReject) {
        e.preventDefault();
        confirmarRechazo(btnReject.dataset.id);
    }
});

// =======================================================
// 3. LÓGICA DEL MODAL (CARGAR Y MOSTRAR)
// =======================================================
function cargarDetalleServicio(id) {
    console.log("🚀 Cargando detalle para ID:", id);

    const modalElement = document.getElementById('modalDetalleServicio');
    if (!modalElement) return;

    const modal = new bootstrap.Modal(modalElement);
    
    // Referencias al DOM
    const loader = document.getElementById('loader-detalle');
    const contenido = document.getElementById('contenido-detalle');
    const footerActions = document.getElementById('modal-acciones-footer');

    // Resetear UI antes de cargar
    loader.classList.remove('d-none');
    contenido.classList.add('d-none');
    footerActions.classList.add('d-none'); // Ocultar botones mientras carga

    modal.show();

    // Petición AJAX (GET)
    // Nota: Usamos 'accion=api_detalle' para que el controlador sepa qué JSON devolver
    fetch(`${BASE_URL}/admin/api/servicio-detalle?accion=api_detalle&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) throw new Error(data.error);

            // --- A. LLENADO DE TEXTOS BÁSICOS ---
            setText('modal-titulo', data.nombre);
            setText('modal-categoria', data.categoria);
            setText('modal-descripcion', data.descripcion);

            // --- B. PRECIO ---
            const precioF = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP' }).format(data.precio || 0);
            setText('modal-precio', Number(data.precio) > 0 ? precioF : 'A Convenir / Gratis');

            // --- C. FOTO ---
            const img = document.getElementById('modal-foto-servicio');
            if(img) {
                img.src = `${BASE_URL}/public/uploads/servicios/${data.foto || 'default_service.png'}`;
                img.onerror = () => { img.src = `${BASE_URL}/public/assets/img/no-image.png`; };
            }

            // --- D. DATOS PROVEEDOR ---
            setText('modal-proveedor', data.proveedor_nombre);
            setText('modal-proveedor-email', data.proveedor_email || 'No disponible');
            setText('modal-proveedor-tel', data.proveedor_tel || 'No disponible');
            setText('modal-proveedor-ubicacion', data.proveedor_ubicacion || 'Sin ubicación');

            // --- E. FECHA ---
            if (data.created_at) {
                const fecha = new Date(data.created_at);
                setText('modal-fecha', fecha.toLocaleDateString('es-CO', { year: 'numeric', month: 'long', day: 'numeric' }));
            }

            // --- F. ESTADO Y BOTONES ---
            const badge = document.getElementById('modal-estado-badge');
            const estado = (data.estado || 'pendiente').toLowerCase();
            
            // Configurar Badge
            if(badge) {
                badge.textContent = estado.toUpperCase();
                badge.className = 'badge rounded-pill px-4 py-2 fs-6 shadow-sm';
                
                if (estado === 'aprobado') badge.classList.add('bg-success');
                else if (estado === 'rechazado') badge.classList.add('bg-danger');
                else badge.classList.add('bg-warning', 'text-dark');
            }

            // Configurar Botones del Modal
            const btnApprove = document.querySelector('.btn-modal-approve');
            const btnReject = document.querySelector('.btn-modal-reject');
            
            // Clonamos para eliminar eventos antiguos
            const newApprove = btnApprove.cloneNode(true);
            const newReject = btnReject.cloneNode(true);
            btnApprove.parentNode.replaceChild(newApprove, btnApprove);
            btnReject.parentNode.replaceChild(newReject, btnReject);

            // Lógica de visibilidad según estado
            if (estado === 'aprobado') {
                // Si ya está aprobado, ocultamos todo el footer de acciones o solo el botón aprobar
                footerActions.classList.add('d-none'); 
            } else if (estado === 'rechazado') {
                // Si ya está rechazado, ocultamos acciones
                footerActions.classList.add('d-none');
            } else {
                // Si es PENDIENTE, mostramos las acciones
                footerActions.classList.remove('d-none');
                newApprove.classList.remove('d-none');
                newReject.classList.remove('d-none');
            }

            // Asignar eventos a los nuevos botones del modal
            newApprove.addEventListener('click', () => { modal.hide(); confirmarAprobacion(data.id); });
            newReject.addEventListener('click', () => { modal.hide(); confirmarRechazo(data.id); });

            // Mostrar contenido final
            loader.classList.add('d-none');
            contenido.classList.remove('d-none');
        })
        .catch(err => {
            console.error(err);
            modal.hide();
            Swal.fire('Error', 'No se pudo cargar la información del servicio.', 'error');
        });
}

// Helper para asignar texto de forma segura
function setText(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
}

// =======================================================
// 4. FUNCIONES DE ACCIÓN (CONFIRMACIONES SWEETALERT)
// =======================================================

function confirmarAprobacion(id) {
    Swal.fire({
        title: '¿Aprobar servicio?',
        text: "El servicio será visible para todos los clientes.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0066ff',
        cancelButtonColor: '#0e1116',
        confirmButtonText: 'Sí, Aprobar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            enviarEstado(id, 'aprobado');
        }
    });
}

function confirmarRechazo(id) {
    Swal.fire({
        title: '¿Rechazar servicio?',
        text: "Por favor indica el motivo del rechazo:",
        input: 'textarea',
        inputPlaceholder: 'Ej: La foto es borrosa, falta descripción...',
        inputAttributes: {
            'aria-label': 'Motivo del rechazo'
        },
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Rechazar',
        cancelButtonText: 'Cancelar',
        preConfirm: (motivo) => {
            if (!motivo) {
                Swal.showValidationMessage('Debes escribir un motivo obligatoriamente');
            }
            return motivo;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            enviarEstado(id, 'rechazado', result.value);
        }
    });
}

// =======================================================
// 5. ENVÍO DE DATOS AL SERVIDOR (AJAX POST)
// =======================================================
function enviarEstado(id, estado, motivo = '') {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('estado', estado);
    if (motivo) formData.append('motivo', motivo);

    // Usamos accion=api_actualizar para el POST
    fetch(`${BASE_URL}/admin/api/servicio-detalle?accion=api_actualizar`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: estado === 'aprobado' ? '¡Aprobado!' : '¡Rechazado!',
                text: 'El estado se ha actualizado correctamente.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                location.reload(); // Recargar página para actualizar tabla
            });
        } else {
            throw new Error(data.error || 'Error desconocido al actualizar');
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Error', err.message, 'error');
    });
}


document.addEventListener("DOMContentLoaded", () => {

    // --- CONFIGURACIÓN A: TABLA PRINCIPAL (#tabla) ---
    // Solo búsqueda y paginación. Diseño limpio.
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
            url: "https://cdn.datatables.net/plug-ins/2.0.2/i18n/es-ES.json"
        },
        // Personalización del input de búsqueda (Lupa)
        initComplete: function () {
            styleSearchInput('#tabla_wrapper');
        }
    });

    // --- CONFIGURACIÓN B: TABLA EXPORTACIÓN (#tabla-1) ---
    // Con botones, sin paginación (para exportar todo).
    let tableExport = new DataTable('#tabla-1', {
        responsive: true,
        paging: false, // ¡Importante! Para exportar todos los datos, no solo la página actual
        layout: {
            topStart: {
                buttons: [
                    {
                        extend: 'copy',
                        text: '<i class="bi bi-clipboard"></i> Copiar',
                        className: 'btn btn-outline-secondary btn-sm',
                    },
                    {
                        extend: 'excel',
                        text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Reporte_Usuarios'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        title: 'Reporte_Usuarios',
                        orientation: 'landscape'
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer"></i> Imprimir',
                        className: 'btn btn-info btn-sm text-white',
                    }
                ]
            },
            topEnd: 'search'
        },
        language: {
            url: "https://cdn.datatables.net/plug-ins/2.0.2/i18n/es-ES.json"
        },
        initComplete: function () {
            styleSearchInput('#tabla-1_wrapper');
        }
    });

    // --- FUNCIÓN AUXILIAR: Estilar el buscador con la lupa ---
    function styleSearchInput(wrapperSelector) {
        const wrapper = document.querySelector(wrapperSelector);
        if (!wrapper) return;

        const dtSearch = wrapper.querySelector('.dt-search');
        if (!dtSearch) return;

        const input = dtSearch.querySelector('input[type="search"]');
        if (!input) return;

        // Crear contenedor personalizado
        const buscadorDiv = document.createElement('div');
        buscadorDiv.className = 'buscador'; // Clase definida en tu CSS
        buscadorDiv.innerHTML = `<i class="bi bi-search"></i>`;
        buscadorDiv.appendChild(input);

        // Limpiar y agregar nuevo input
        dtSearch.innerHTML = '';
        dtSearch.appendChild(buscadorDiv);

        // Estilos inline para asegurar compatibilidad
        input.setAttribute('placeholder', 'Buscar...');
        input.style.width = "100%";
        input.style.border = "none";
        input.style.background = "transparent";
        input.style.outline = "none";
        input.style.paddingLeft = "10px";
    }

    // --- CORRECCIÓN DE PESTAÑAS (Tabs) ---
    // Ajusta las columnas cuando se cambia de pestaña para evitar deformaciones
    const tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabEls.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (event) {
            // Ajustar columnas de ambas tablas
            tableMain.columns.adjust().responsive.recalc();
            tableExport.columns.adjust().responsive.recalc();
        });
    });

});
