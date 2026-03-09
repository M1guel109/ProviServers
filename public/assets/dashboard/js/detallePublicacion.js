// detallePublicacion.js - Versión sin backend (datos en el botón)
document.addEventListener('DOMContentLoaded', function() {
    const modalDetalle = document.getElementById('modalDetallePublicacion');
    const botonesVer = document.querySelectorAll('.btn-ver-detalle-publicacion');
    
    if (!modalDetalle || !botonesVer.length) return;

    // Elementos del modal
    const loader = document.getElementById('loader-detalle-publicacion');
    const contenido = document.getElementById('contenido-detalle-publicacion');
    
    const titulo = document.getElementById('modal-publicacion-titulo');
    const estado = document.getElementById('modal-publicacion-estado');
    const precio = document.getElementById('modal-publicacion-precio');
    const servicio = document.getElementById('modal-publicacion-servicio');
    const categoria = document.getElementById('modal-publicacion-categoria');
    const fecha = document.getElementById('modal-publicacion-fecha');
    const descripcion = document.getElementById('modal-publicacion-descripcion');
    const rechazoContainer = document.getElementById('modal-publicacion-rechazo-container');
    const rechazo = document.getElementById('modal-publicacion-rechazo');
    const linkEditar = document.getElementById('modal-link-editar-publicacion');

    const BASE_URL = window.BASE_URL || '';

    modalDetalle.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        
        // Mostrar loader por efecto visual
        loader.classList.remove('d-none');
        contenido.classList.add('d-none');
        
        // Simular carga (solo por efecto visual)
        setTimeout(() => {
            // Obtener datos del botón (están quemados en data-*)
            const tituloVal = button.getAttribute('data-publicacion-titulo');
            const estadoVal = button.getAttribute('data-publicacion-estado');
            const estadoTextoVal = button.getAttribute('data-publicacion-estado-texto');
            const precioVal = button.getAttribute('data-publicacion-precio');
            const servicioVal = button.getAttribute('data-publicacion-servicio');
            const categoriaVal = button.getAttribute('data-publicacion-categoria');
            const fechaVal = button.getAttribute('data-publicacion-fecha');
            const descripcionVal = button.getAttribute('data-publicacion-descripcion');
            const motivoRechazoVal = button.getAttribute('data-publicacion-motivo-rechazo');
            const servicioId = button.getAttribute('data-servicio-id');

            // Determinar clase del badge según estado
            let badgeClass = 'bg-secondary';
            switch(estadoVal) {
                case 'pendiente': badgeClass = 'bg-warning text-dark'; break;
                case 'aprobado': badgeClass = 'bg-success'; break;
                case 'rechazado': badgeClass = 'bg-danger'; break;
                default: badgeClass = 'bg-secondary';
            }

            // Llenar campos
            if (titulo) titulo.textContent = tituloVal || 'Sin título';
            if (estado) {
                estado.textContent = estadoTextoVal || estadoVal;
                estado.className = 'badge ' + badgeClass;
            }
            if (precio) precio.textContent = '$ ' + (precioVal || '0');
            if (servicio) servicio.textContent = servicioVal || 'No especificado';
            if (categoria) categoria.textContent = categoriaVal || 'Sin categoría';
            if (fecha) fecha.textContent = fechaVal || 'Fecha no disponible';
            if (descripcion) descripcion.textContent = descripcionVal || 'Sin descripción adicional';
            
            // Manejar motivo de rechazo
            if (rechazoContainer && rechazo) {
                if (estadoVal === 'rechazado' && motivoRechazoVal) {
                    rechazo.textContent = motivoRechazoVal;
                    rechazoContainer.classList.remove('d-none');
                } else {
                    rechazoContainer.classList.add('d-none');
                }
            }
            
            // Link de edición
            if (linkEditar && servicioId) {
                linkEditar.href = `${BASE_URL}/proveedor/editar-servicio?id=${servicioId}`;
            }
            
            // Ocultar loader, mostrar contenido
            loader.classList.add('d-none');
            contenido.classList.remove('d-none');
        }, 300); // Pequeño delay para efecto visual
    });

    modalDetalle.addEventListener('hidden.bs.modal', function() {
        loader.classList.remove('d-none');
        contenido.classList.add('d-none');
    });
});