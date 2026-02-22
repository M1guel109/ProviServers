/* ======================================================
   enProceso.js - Lógica para el Panel de Seguimiento
====================================================== */

document.addEventListener('DOMContentLoaded', function () {
    console.log("Script de Seguimiento (En Proceso) cargado.");

    // Escuchar el envío del formulario de nuevo avance
    const formSeguimiento = document.getElementById('formSeguimiento');
    if (formSeguimiento) {
        formSeguimiento.addEventListener('submit', function (e) {
            e.preventDefault();
            guardarNuevoAvance(this);
        });
    }
});

/**
 * Función para abrir el panel lateral de seguimiento
 * Se llama desde el HTML: onclick="abrirSeguimiento(id, nombre, estado, cliente)"
 */
window.abrirSeguimiento = function(idContrato, nombreServicio, estadoActual, nombreCliente) {
    if (!idContrato || idContrato === 0) {
        Swal.fire('Error', 'No se encontró el ID del contrato.', 'error');
        return;
    }

    // 1. Llenar la cabecera del Offcanvas
    document.getElementById('seg-contrato-id').value = idContrato;
    document.getElementById('seg-servicio-nombre').textContent = nombreServicio;
    document.getElementById('seg-cliente-nombre').textContent = nombreCliente;
    
    // 2. Pre-seleccionar el estado en el select del formulario
    const selectEstado = document.getElementById('seg-estado');
    if(selectEstado) {
        // Asegurarse de que el option existe antes de asignarlo
        if(selectEstado.querySelector(`option[value="${estadoActual}"]`)) {
            selectEstado.value = estadoActual;
        }
    }

    // 3. (AQUÍ HAREMOS EL FETCH PARA CARGAR EL HISTORIAL REAL LUEGO)
    // cargarHistorial(idContrato);

    // 4. Mostrar el Offcanvas
    const offcanvasEl = document.getElementById('offcanvasSeguimiento');
    const offcanvas = new bootstrap.Offcanvas(offcanvasEl);
    
    // Forzar que siempre abra en la pestaña de Historial
    const tabHistorial = document.querySelector('#offcanvasSeguimiento .nav-link[data-bs-target="#tab-historial"]');
    if(tabHistorial) {
        let tab = new bootstrap.Tab(tabHistorial);
        tab.show();
    }

    offcanvas.show();
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
            // cargarHistorial(formData.get('contrato_id'));

            // Cambiar a la pestaña de historial automáticamente
            const tabHistorial = document.querySelector('#offcanvasSeguimiento .nav-link[data-bs-target="#tab-historial"]');
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