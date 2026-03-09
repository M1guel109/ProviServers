/* ======================================================
   enProceso.js - Lógica para el Panel de Seguimiento
   (Versión con MODAL centrado)
====================================================== */

document.addEventListener('DOMContentLoaded', function () {
    console.log("Script de Seguimiento (En Proceso) cargado.");

    // Escuchar el envío del formulario de nuevo avance (versión modal)
    const formSeguimientoModal = document.getElementById('formSeguimientoModal');
    if (formSeguimientoModal) {
        formSeguimientoModal.addEventListener('submit', function (e) {
            e.preventDefault();
            guardarNuevoAvance(this);
        });
    }
});

/**
 * Función para abrir el modal centrado de seguimiento
 * Se llama desde el HTML: onclick="abrirSeguimiento(id, nombre, estado, cliente)"
 */
window.abrirSeguimiento = function(idContrato, nombreServicio, estadoActual, nombreCliente) {
    console.log('abrirSeguimiento llamado', {idContrato, nombreServicio, estadoActual, nombreCliente});
    
    if (!idContrato || idContrato === 0) {
        Swal.fire('Error', 'No se encontró el ID del contrato.', 'error');
        return;
    }

    // 1. Llenar la información en el MODAL (versión modal con "-modal")
    const inputContrato = document.getElementById('seg-contrato-id-modal');
    const spanServicio = document.getElementById('seg-servicio-nombre');
    const spanCliente = document.getElementById('seg-cliente-nombre');
    const selectEstado = document.getElementById('seg-estado-modal');
    
    // Verificar que los elementos existen
    if (!inputContrato || !spanServicio || !spanCliente) {
        console.error('Elementos del modal no encontrados:', {
            inputContrato: !!inputContrato,
            spanServicio: !!spanServicio,
            spanCliente: !!spanCliente
        });
        Swal.fire('Error', 'No se pudo abrir el modal de seguimiento', 'error');
        return;
    }
    
    inputContrato.value = idContrato;
    spanServicio.textContent = nombreServicio || 'Servicio en proceso';
    spanCliente.textContent = nombreCliente || 'Cliente';
    
    // 2. Pre-seleccionar el estado en el select del modal
    if(selectEstado) {
        if(selectEstado.querySelector(`option[value="${estadoActual}"]`)) {
            selectEstado.value = estadoActual;
        }
    }

    // 3. Mostrar el MODAL centrado
    const modalElement = document.getElementById('modalSeguimiento');
    if (modalElement) {
        try {
            const modal = new bootstrap.Modal(modalElement);
            
            // Forzar que siempre abra en la pestaña de Historial
            const tabHistorial = document.querySelector('#modalSeguimiento .nav-link[data-bs-target="#tab-historial-modal"]');
            if(tabHistorial) {
                let tab = new bootstrap.Tab(tabHistorial);
                tab.show();
            }
            
            modal.show();
        } catch (e) {
            console.error('Error al abrir modal:', e);
            Swal.fire('Error', 'Error al abrir el modal', 'error');
        }
    } else {
        console.error('Modal no encontrado');
    }
};

/**
 * Función para enviar el nuevo avance vía AJAX
 */
async function guardarNuevoAvance(formulario) {
    const formData = new FormData(formulario);
    formData.append('accion', 'guardar_avance');

    // Botón de submit para ponerlo en "Cargando..."
    const btnSubmit = formulario.querySelector('button[type="submit"]');
    const originalHtml = btnSubmit.innerHTML;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';
    btnSubmit.disabled = true;

    try {
        // URL a tu controlador (Asegúrate de crear este caso en tu switch luego)
        const url = `${BASE_URL}/app/controllers/seguimientoController.php`;
        
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const res = await response.json();

        if (res.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Avance Guardado!',
                text: 'El cliente será notificado de esta actualización.',
                timer: 2000,
                showConfirmButton: false
            });

            // Limpiar formulario y recargar historial
            formulario.reset();
            // cargarHistorial(formData.get('contrato_id')); // Descomentar cuando implementes

            // Cambiar a la pestaña de historial automáticamente
            const tabHistorial = document.querySelector('#modalSeguimiento .nav-link[data-bs-target="#tab-historial-modal"]');
            if(tabHistorial) {
                let tab = new bootstrap.Tab(tabHistorial);
                tab.show();
            }

        } else {
            Swal.fire('Error', res.message || 'No se pudo guardar el avance', 'error');
        }

    } catch (error) {
        console.error(error);
        Swal.fire('Error', 'Fallo de conexión al servidor', 'error');
    } finally {
        // Restaurar botón
        btnSubmit.innerHTML = originalHtml;
        btnSubmit.disabled = false;
    }
}