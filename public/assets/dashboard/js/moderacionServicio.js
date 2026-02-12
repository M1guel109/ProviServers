// Validar que BASE_URL exista
if (typeof BASE_URL === 'undefined') {
    console.error("🚨 ERROR CRÍTICO: BASE_URL no está definida. Revisa tu footer.");
} else {
    console.log("✅ Script de Moderación cargado. BASE_URL:", BASE_URL);
}

// USAMOS DELEGACIÓN GLOBAL (Al documento entero)
// Esto soluciona cualquier problema con DataTables o elementos dinámicos
document.addEventListener('click', function (e) {

    // ---------------------------------------------------
    // 1. DETECTAR CLIC EN "VER DETALLE" (OJITO)
    // ---------------------------------------------------
    const btnView = e.target.closest('.btn-view');
    if (btnView) {
        e.preventDefault(); // Evita que recargue o suba
        console.log("👁️ Clic en VER detectado. ID:", btnView.dataset.id);
        
        const id = btnView.dataset.id;
        if(id) {
            cargarDetalleServicio(id);
        } else {
            console.error("❌ El botón no tiene el atributo data-id");
            Swal.fire('Error', 'El botón no tiene un ID de servicio.', 'error');
        }
    }

    // ---------------------------------------------------
    // 2. DETECTAR CLIC EN "APROBAR" (CHECK)
    // ---------------------------------------------------
    const btnApprove = e.target.closest('.btn-approve');
    if (btnApprove) {
        e.preventDefault();
        console.log("✅ Clic en APROBAR detectado. ID:", btnApprove.dataset.id);
        
        const id = btnApprove.dataset.id;
        confirmarAprobacion(id);
    }

    // ---------------------------------------------------
    // 3. DETECTAR CLIC EN "RECHAZAR" (X)
    // ---------------------------------------------------
    const btnReject = e.target.closest('.btn-reject');
    if (btnReject) {
        e.preventDefault();
        console.log("❌ Clic en RECHAZAR detectado. ID:", btnReject.dataset.id);
        
        const id = btnReject.dataset.id;
        confirmarRechazo(id);
    }
});


// =======================================================
// LÓGICA DEL MODAL (OJITO) MEJORADA
// =======================================================
function cargarDetalleServicio(id) {
    console.log("🚀 Iniciando petición AJAX para ID:", id);

    const modalElement = document.getElementById('modalDetalleServicio');
    if (!modalElement) return;

    const modal = new bootstrap.Modal(modalElement);

    // Resetear visualmente
    document.getElementById('loader-detalle').classList.remove('d-none');
    document.getElementById('contenido-detalle').classList.add('d-none');
    
    // Limpiar botones del footer para evitar conflictos de ID anteriores
    const footerActions = document.getElementById('modal-acciones-footer');
    if(footerActions) footerActions.classList.add('d-none'); // Ocultar botones mientras carga

    modal.show();

    // Petición AJAX
    fetch(`${BASE_URL}/admin/api/servicio-detalle?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) throw new Error(data.error);

            // 1. INFO PRINCIPAL
            document.getElementById('modal-titulo').textContent = data.nombre;
            document.getElementById('modal-categoria').textContent = data.categoria;
            document.getElementById('modal-descripcion').textContent = data.descripcion;

            // 2. PRECIO
            const precioF = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP' }).format(data.precio || 0);
            document.getElementById('modal-precio').textContent = Number(data.precio) > 0 ? precioF : 'A Convenir / Gratis';

            // 3. FOTO
            const img = document.getElementById('modal-foto-servicio');
            img.src = `${BASE_URL}/public/uploads/servicios/${data.foto || 'default_service.png'}`;
            // Manejo de error de imagen
            img.onerror = function() { this.src = `${BASE_URL}/public/assets/img/no-image.png`; };

            // 4. DATOS DEL PROVEEDOR (Nuevos campos)
            document.getElementById('modal-proveedor').textContent = data.proveedor_nombre;
            document.getElementById('modal-proveedor-email').textContent = data.proveedor_email || 'No disponible';
            document.getElementById('modal-proveedor-tel').textContent = data.proveedor_tel || 'No disponible';
            document.getElementById('modal-proveedor-ubicacion').textContent = data.proveedor_ubicacion || 'Ubicación no registrada';

            // 5. FECHA (Formato legible)
            const fecha = new Date(data.created_at);
            document.getElementById('modal-fecha').textContent = fecha.toLocaleDateString('es-CO', { year: 'numeric', month: 'long', day: 'numeric' });

            // 6. ESTADO (Badge)
            const badge = document.getElementById('modal-estado-badge');
            badge.textContent = (data.estado || 'Pendiente').toUpperCase();
            badge.className = 'badge rounded-pill px-4 py-2 fs-6 shadow-sm';
            
            if (data.estado === 'aprobado') {
                badge.classList.add('bg-success');
                footerActions.classList.add('d-none'); // Si ya está aprobado, ocultamos botones de acción (opcional)
            } else if (data.estado === 'rechazado') {
                badge.classList.add('bg-danger');
                footerActions.classList.add('d-none');
            } else {
                badge.classList.add('bg-warning', 'text-dark');
                footerActions.classList.remove('d-none'); // Solo mostrar botones si está pendiente
            }

            // 7. CONFIGURAR BOTONES DEL MODAL (Importante)
            // Asignamos el ID actual a los botones del footer para que funcionen
            const btnApproveModal = document.querySelector('.btn-modal-approve');
            const btnRejectModal = document.querySelector('.btn-modal-reject');

            // Clonamos los botones para eliminar listeners viejos (truco limpio)
            const newApprove = btnApproveModal.cloneNode(true);
            const newReject = btnRejectModal.cloneNode(true);
            
            btnApproveModal.parentNode.replaceChild(newApprove, btnApproveModal);
            btnRejectModal.parentNode.replaceChild(newReject, btnRejectModal);

            // Agregamos eventos nuevos
            newApprove.addEventListener('click', () => {
                modal.hide(); // Cerramos modal primero
                confirmarAprobacion(data.id);
            });

            newReject.addEventListener('click', () => {
                modal.hide();
                confirmarRechazo(data.id);
            });

            // MOSTRAR CONTENIDO
            document.getElementById('loader-detalle').classList.add('d-none');
            document.getElementById('contenido-detalle').classList.remove('d-none');
        })
        .catch(err => {
            console.error(err);
            modal.hide();
            Swal.fire('Error', 'No se pudo cargar la información.', 'error');
        });
}

