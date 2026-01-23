document.addEventListener('DOMContentLoaded', function () {

    /* ======================================================
       VARIABLES GLOBALES
    ====================================================== */
    const botonesActualizar = document.querySelectorAll('.btn-actualizar');
    let tarjetaSeleccionada = null; 

    /* ======================================================
       EVENT LISTENERS
    ====================================================== */
    botonesActualizar.forEach(btn => {
        btn.addEventListener('click', function () {
            tarjetaSeleccionada = this.closest('.tarjeta-proceso');
            
            const titulo = tarjetaSeleccionada.querySelector('.proceso-titulo').textContent;
            
            // Detectamos el estado actual leyendo el texto del badge
            // .trim() quita espacios y .toLowerCase() lo pone en minúsculas para comparar
            const estadoTexto = tarjetaSeleccionada.querySelector('.badge-prioridad').textContent.trim().toLowerCase();
            
            // Llamamos al modal de SweetAlert con lógica inteligente
            mostrarModalSweet(titulo, estadoTexto);
        });
    });

    /* ======================================================
       FUNCIÓN PRINCIPAL: ACTUALIZAR ESTADO
    ====================================================== */
    window.actualizarEstadoServicio = async function (nuevoEstado) {
        
        if (!tarjetaSeleccionada) return;

        const contratoId = tarjetaSeleccionada.dataset.contratoId;
        const url = `${BASE_URL}/proveedor/actualizar-estado`; 

        try {
            // 1. Mostrar "Cargando..."
            Swal.fire({
                title: 'Actualizando...',
                text: 'Procesando tu solicitud',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading() }
            });

            // 2. Preparar datos
            const formData = new URLSearchParams();
            formData.append('contrato_id', contratoId);
            formData.append('estado', nuevoEstado);

            // 3. Petición AJAX
            const resp = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            });

            // 4. Procesar Respuesta
            const data = await resp.json().catch(() => null);

            if (!data || !data.ok) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data?.msg || 'No se pudo actualizar el estado.'
                });
                return;
            }

            // 5. ÉXITO: Actualizar UI y notificar
            actualizarUIEstado(tarjetaSeleccionada, nuevoEstado);
            
            Swal.fire({
                icon: 'success',
                title: '¡Estado Actualizado!',
                text: `El servicio ha pasado a: ${formatearEstado(nuevoEstado)}`,
                timer: 2000,
                showConfirmButton: false
            });

        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'Verifica tu conexión a internet e inténtalo de nuevo.'
            });
        }
    };

    /* ======================================================
       CONFIRMACIÓN DE CANCELACIÓN
    ====================================================== */
    window.confirmarCancelacion = function() {
        // SweetAlert sobre SweetAlert (Pregunta de seguridad)
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Cancelar un servicio puede afectar tu reputación si no hay causa justa.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, cancelar servicio',
            cancelButtonText: 'Volver'
        }).then((result) => {
            if (result.isConfirmed) {
                actualizarEstadoServicio('cancelado_proveedor');
            } else {
                // Si cancela, volvemos a abrir el menú principal (opcional)
                // O simplemente no hacemos nada.
            }
        });
    };
});

/* ======================================================
   FUNCIONES DE UI (SWEETALERT)
====================================================== */

function mostrarModalSweet(titulo, estadoActual) {
    let botonesHTML = '';

    // LÓGICA: ¿Qué botones mostrar según el estado actual?
    
    // CASO 1: Está PENDIENTE -> Opciones: Iniciar o Cancelar
    if (estadoActual.includes('pendiente')) {
        botonesHTML = `
            <div class="d-grid gap-2">
                <button class="btn btn-primary btn-lg" onclick="actualizarEstadoServicio('en_proceso')">
                    <i class="bi bi-play-circle-fill"></i> INICIAR TRABAJO
                </button>
                <div class="text-muted small mb-3">Marca esto cuando llegues o inicies la labor.</div>

                <button class="btn btn-outline-danger" onclick="confirmarCancelacion()">
                    <i class="bi bi-x-circle"></i> Cancelar Servicio
                </button>
            </div>
        `;
    } 
    // CASO 2: Está EN PROCESO -> Opciones: Finalizar o Cancelar
    else if (estadoActual.includes('proceso')) {
        botonesHTML = `
            <div class="d-grid gap-2">
                <button class="btn btn-success btn-lg" onclick="actualizarEstadoServicio('finalizado')">
                    <i class="bi bi-check-circle-fill"></i> FINALIZAR TRABAJO
                </button>
                <div class="text-muted small mb-3">Marca esto cuando hayas terminado todo.</div>

                <button class="btn btn-outline-danger" onclick="confirmarCancelacion()">
                    <i class="bi bi-exclamation-triangle"></i> Cancelar (Problema)
                </button>
            </div>
        `;
    }
    // CASO 3: Ya terminó o se canceló
    else {
        botonesHTML = `<div class="alert alert-secondary">Este servicio ya fue finalizado o cancelado.</div>`;
    }

    // Lanzar el Modal
    Swal.fire({
        title: titulo,
        html: botonesHTML,
        showConfirmButton: false, // Ocultamos el botón "OK" por defecto
        showCloseButton: true,
        focusConfirm: false,
        customClass: {
            popup: 'animated fadeInDown' // Animación suave si la soporta
        }
    });
}

// Helper para formatear texto (ej: en_proceso -> En Proceso)
function formatearEstado(texto) {
    return texto.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function actualizarUIEstado(tarjeta, estado) {
    const badge = tarjeta.querySelector('.badge-prioridad');
    const barra = tarjeta.querySelector('.barra-progreso-fill');
    const textoProgreso = tarjeta.querySelector('.progreso-estado');

    // Mapeo de estilos (Asegúrate que coincidan con tu CSS)
    const estilos = {
        'pendiente': { clase: 'media', texto: 'Pendiente', pct: '25%' },
        'en_proceso': { clase: 'alta', texto: 'En proceso', pct: '60%' },
        'finalizado': { clase: 'completado', texto: 'Finalizado', pct: '100%' },
        'cancelado_proveedor': { clase: 'baja', texto: 'Cancelado', pct: '0%' }
    };

    const nuevoEstilo = estilos[estado];
    if (!nuevoEstilo) return;

    // Actualizar Badge (Removemos clases viejas para evitar conflictos)
    badge.classList.remove('media', 'alta', 'completado', 'baja');
    badge.classList.add(nuevoEstilo.clase);
    badge.textContent = nuevoEstilo.texto;

    // Actualizar Barra
    if (barra) barra.style.width = nuevoEstilo.pct;
    
    // Actualizar texto de progreso
    if (textoProgreso) textoProgreso.textContent = nuevoEstilo.texto;

    // Efecto visual al finalizar
    if (estado === 'finalizado') {
        // Opcional: Bajar opacidad para indicar que ya "pasó"
        tarjeta.style.opacity = '0.7';
    }
}