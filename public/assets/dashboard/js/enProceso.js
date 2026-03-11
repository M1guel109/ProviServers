/* ======================================================
   enProceso.js - Seguimiento de servicios en proceso
   Alineado con el controlador general del proyecto
====================================================== */

/**
 * SweetAlert con estilo del proyecto
 */
function sweetProyecto(icon, title, text, callback = null, options = {}) {
    Swal.fire({
        icon,
        title,
        text,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#0066ff',
        background: '#fff',
        color: '#0e1116',
        ...options
    }).then(() => {
        if (typeof callback === 'function') {
            callback();
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    console.log('Script de Seguimiento (En Proceso) cargado.');

    const formSeguimientoModal = document.getElementById('formSeguimientoModal');
    const selectEstado = document.getElementById('seg-estado-modal');

    if (formSeguimientoModal) {
        formSeguimientoModal.addEventListener('submit', function (e) {
            e.preventDefault();
            guardarNuevoAvance(this);
        });
    }

    if (selectEstado) {
        selectEstado.addEventListener('change', toggleCamposSeguimiento);
        toggleCamposSeguimiento();
    }
});

/**
 * Obtiene el botón de guardar del modal
 */
function getBotonGuardarSeguimiento() {
    return (
        document.getElementById('btnGuardarSeguimiento') ||
        document.querySelector('button[form="formSeguimientoModal"]') ||
        document.querySelector('#modalSeguimiento .modal-footer .btn.btn-primary')
    );
}

/**
 * Escape básico de HTML
 */
function escapeHtml(str) {
    return String(str ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

/**
 * Hace título y descripción opcionales cuando el estado es finalizado.
 * Como aún no tienes tabla de seguimientos, esos campos no se persisten.
 */
function toggleCamposSeguimiento() {
    const selectEstado = document.getElementById('seg-estado-modal');
    const inputTitulo = document.querySelector('#formSeguimientoModal input[name="titulo"]');
    const inputDescripcion = document.querySelector('#formSeguimientoModal textarea[name="descripcion"]');
    const inputArchivo = document.querySelector('#formSeguimientoModal input[name="archivo"]');

    if (!selectEstado || !inputTitulo || !inputDescripcion) return;

    const esFinalizado = selectEstado.value === 'finalizado';

    inputTitulo.required = !esFinalizado;
    inputDescripcion.required = !esFinalizado;

    if (inputArchivo) {
        inputArchivo.required = false;
    }

    if (esFinalizado) {
        inputTitulo.placeholder = 'Opcional al finalizar el servicio';
        inputDescripcion.placeholder = 'Opcional al finalizar el servicio';
    } else {
        inputTitulo.placeholder = 'Ej: Compra de materiales...';
        inputDescripcion.placeholder = 'Explica qué se hizo hoy...';
    }
}

/**
 * Abre el modal y precarga los datos del servicio
 * Se llama desde el HTML:
 * abrirSeguimiento(id, nombreServicio, estadoActual, nombreCliente)
 */
window.abrirSeguimiento = function (idContrato, nombreServicio, estadoActual, nombreCliente) {
    console.log('abrirSeguimiento llamado', {
        idContrato,
        nombreServicio,
        estadoActual,
        nombreCliente
    });

    if (!idContrato || Number(idContrato) === 0) {
        sweetProyecto('error', 'Error', 'No se encontró el ID del contrato.');
        return;
    }

    const modalElement = document.getElementById('modalSeguimiento');
    const form = document.getElementById('formSeguimientoModal');
    const inputContrato = document.getElementById('seg-contrato-id-modal');
    const spanServicio = document.getElementById('seg-servicio-nombre');
    const spanCliente = document.getElementById('seg-cliente-nombre');
    const selectEstado = document.getElementById('seg-estado-modal');
    const timeline = document.getElementById('contenedor-timeline-modal');

    if (!modalElement || !form || !inputContrato || !spanServicio || !spanCliente || !selectEstado) {
        console.error('Faltan elementos del modal:', {
            modalElement: !!modalElement,
            form: !!form,
            inputContrato: !!inputContrato,
            spanServicio: !!spanServicio,
            spanCliente: !!spanCliente,
            selectEstado: !!selectEstado
        });

        sweetProyecto('error', 'Error', 'No se pudo abrir el modal de seguimiento.');
        return;
    }

    // Reiniciar formulario al abrir
    form.reset();

    // Cargar datos
    inputContrato.value = Number(idContrato);
    spanServicio.textContent = nombreServicio || 'Servicio en proceso';
    spanCliente.textContent = nombreCliente || 'Cliente';

    // Seleccionar estado actual si existe como opción
    const existeOpcion = !!selectEstado.querySelector(`option[value="${estadoActual}"]`);
    selectEstado.value = existeOpcion ? estadoActual : 'en_proceso';

    toggleCamposSeguimiento();

    // Historial visual provisional
    if (timeline) {
        const estadoTexto = String(estadoActual || 'pendiente').replaceAll('_', ' ');

        timeline.innerHTML = `
            <div class="timeline-item">
                <div class="timeline-marker bg-secondary">
                    <i class="bi bi-info-circle text-white"></i>
                </div>
                <div class="timeline-content bg-light p-3 rounded-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="fw-bold text-dark mb-0">Estado actual del servicio</h6>
                        <small class="text-muted">Contrato #${escapeHtml(idContrato)}</small>
                    </div>
                    <p class="text-secondary small mb-0">
                        Estado registrado: <strong>${escapeHtml(estadoTexto)}</strong>.
                    </p>
                </div>
            </div>
        `;
    }

    try {
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

        // Forzar apertura en la pestaña de Historial
        const tabHistorial = document.querySelector(
            '#modalSeguimiento .nav-link[data-bs-target="#tab-historial-modal"]'
        );

        if (tabHistorial) {
            bootstrap.Tab.getOrCreateInstance(tabHistorial).show();
        }

        modal.show();
    } catch (error) {
        console.error('Error al abrir modal:', error);
        sweetProyecto('error', 'Error', 'Error al abrir el modal.');
    }
};

/**
 * Guarda el cambio de estado del servicio
 * Importante:
 * - Usa el controlador general
 * - NO usa seguimientoController.php
 */
async function guardarNuevoAvance(formulario) {
    const formData = new FormData(formulario);

    const contratoId = Number(formData.get('contrato_id') || 0);
    const estadoActual = String(formData.get('estado_actual') || '').trim();
    const titulo = String(formData.get('titulo') || '').trim();
    const descripcion = String(formData.get('descripcion') || '').trim();

    if (!contratoId) {
        sweetProyecto('error', 'Error', 'No se encontró el contrato a actualizar.');
        return;
    }

    if (!estadoActual) {
        sweetProyecto('error', 'Error', 'Debes seleccionar un estado.');
        return;
    }

    // Si NO finaliza, obligamos completar los campos visuales
    if (estadoActual !== 'finalizado' && (!titulo || !descripcion)) {
        sweetProyecto(
            'warning',
            'Faltan datos',
            'Para mantener el servicio en proceso, completa el título y la descripción.'
        );
        return;
    }

    // Acción que espera tu controlador general
    formData.set('accion', 'actualizar_estado_servicio');

    const CONTROLLER_URL = `${BASE_URL}/app/controllers/ProveedorOperacionController.php`;

    const btnSubmit = getBotonGuardarSeguimiento();
    const originalHtml = btnSubmit ? btnSubmit.innerHTML : 'Guardar';

    if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Guardando...
        `;
    }

    try {
        const response = await fetch(CONTROLLER_URL, {
            method: 'POST',
            body: formData
        });

        let res;
        try {
            res = await response.json();
        } catch {
            throw new Error('La respuesta del servidor no es JSON válido.');
        }

        const exito = Boolean(res.success ?? res.ok);
        const mensaje = res.message ?? res.msg ?? 'No se pudo actualizar el servicio.';

        if (!response.ok || !exito) {
            throw new Error(mensaje);
        }

        await Swal.fire({
            icon: 'success',
            title: 'Actualización guardada',
            text: estadoActual === 'finalizado'
                ? 'El servicio fue marcado como finalizado.'
                : 'El estado del servicio fue actualizado correctamente.',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#0066ff',
            background: '#fff',
            color: '#0e1116'
        });

        const modalElement = document.getElementById('modalSeguimiento');
        if (modalElement) {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) modal.hide();
        }

        window.location.reload();

    } catch (error) {
        console.error('Error al guardar seguimiento:', error);
        sweetProyecto('error', 'Error', error.message || 'Fallo de conexión al servidor.');
    } finally {
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalHtml;
        }
    }
}