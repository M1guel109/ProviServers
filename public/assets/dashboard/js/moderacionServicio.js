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
// LÓGICA DEL MODAL (OJITO)
// =======================================================
function cargarDetalleServicio(id) {
    console.log("🚀 Iniciando petición AJAX para ID:", id);

    const modalElement = document.getElementById('modalDetalleServicio');
    if (!modalElement) {
        console.error("❌ No se encontró el modal con ID 'modalDetalleServicio' en el HTML");
        return;
    }

    const modal = new bootstrap.Modal(modalElement);

    // Resetear visualmente
    const loader = document.getElementById('loader-detalle');
    const contenido = document.getElementById('contenido-detalle');
    
    if(loader) loader.classList.remove('d-none');
    if(contenido) contenido.classList.add('d-none');
    
    modal.show();

    // Petición AJAX
    const url = `${BASE_URL}/admin/api/servicio-detalle?id=${id}`;
    console.log("🌐 Consultando URL:", url);

    fetch(url)
        .then(res => {
            console.log("📩 Respuesta recibida. Status:", res.status);
            if (!res.ok) throw new Error(`Error HTTP: ${res.status}`);
            return res.text(); // Usamos text() primero para depurar si no es JSON
        })
        .then(texto => {
            console.log("📄 Cuerpo de la respuesta:", texto);
            try {
                return JSON.parse(texto);
            } catch (e) {
                throw new Error("La respuesta del servidor no es un JSON válido. Revisa el console.log 'Cuerpo de la respuesta'.");
            }
        })
        .then(data => {
            if (data.error) throw new Error(data.error);

            console.log("✅ Datos procesados correctamente:", data);

            // Llenar campos (Validamos que los elementos existan antes de asignar)
            if(document.getElementById('modal-titulo')) document.getElementById('modal-titulo').textContent = data.nombre;
            if(document.getElementById('modal-proveedor')) document.getElementById('modal-proveedor').textContent = data.proveedor_nombre;
            
            if(document.getElementById('modal-precio')) {
                const precioF = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP' }).format(data.precio);
                document.getElementById('modal-precio').textContent = precioF;
            }
            
            if(document.getElementById('modal-categoria')) document.getElementById('modal-categoria').textContent = data.categoria;
            if(document.getElementById('modal-descripcion')) document.getElementById('modal-descripcion').textContent = data.descripcion;

            // Foto
            const img = document.getElementById('modal-foto-servicio');
            if(img) img.src = `${BASE_URL}/public/uploads/servicios/${data.foto || 'default_service.png'}`;

            // Badge Estado
            const badge = document.getElementById('modal-estado-badge');
            if(badge) {
                badge.textContent = (data.estado || 'Pendiente').toUpperCase();
                badge.className = 'badge rounded-pill px-3 py-2 fs-6';
                if (data.estado === 'aprobado') badge.classList.add('bg-success');
                else if (data.estado === 'rechazado') badge.classList.add('bg-danger');
                else badge.classList.add('bg-warning', 'text-dark');
            }

            // Mostrar
            if(loader) loader.classList.add('d-none');
            if(contenido) contenido.classList.remove('d-none');
        })
        .catch(err => {
            console.error("🚨 ERROR AJAX:", err);
            modal.hide();
            Swal.fire('Error', `Fallo técnico: ${err.message}`, 'error');
        });
}

// ... (Mantén tus funciones confirmarAprobacion, confirmarRechazo y enviarEstado igual que antes)