/* ======================================================
   promociones.js - Funcionalidad para gestión de promociones
   ====================================================== */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Botones de acción
    const btnGuardarPromocion = document.getElementById('btn-guardar-promocion');
    if (btnGuardarPromocion) {
        btnGuardarPromocion.addEventListener('click', guardarPromocion);
    }
    
    // Botones de pausar promoción
    const botonesPausar = document.querySelectorAll('.btn-outline-danger');
    botonesPausar.forEach(btn => {
        if (btn.textContent.includes('Pausar')) {
            btn.addEventListener('click', pausarPromocion);
        }
    });
    
    // Botones de activar promoción
    const botonesActivar = document.querySelectorAll('.btn-primary');
    botonesActivar.forEach(btn => {
        if (btn.textContent.includes('Activar')) {
            btn.addEventListener('click', activarPromocion);
        }
    });
    
    // Botones de reactivar promoción
    const botonesReactivar = document.querySelectorAll('.btn-outline-primary');
    botonesReactivar.forEach(btn => {
        if (btn.textContent.includes('Reactivar')) {
            btn.addEventListener('click', reactivarPromocion);
        }
    });
    
    // Preview de precio con descuento en tiempo real (#190)
    const selectPub    = document.getElementById('select-publicacion-promo');
    const inputDesc    = document.getElementById('input-descuento-promo');
    const previewBox   = document.getElementById('preview-precio');
    const previewBase  = document.getElementById('preview-precio-base');
    const previewFinal = document.getElementById('preview-precio-final');
    const previewAhorro = document.getElementById('preview-ahorro');

    function actualizarPreview() {
        if (!selectPub || !inputDesc || !previewBox) return;
        const opt      = selectPub.options[selectPub.selectedIndex];
        const precio   = parseFloat(opt?.dataset?.precio || 0);
        const desc     = parseInt(inputDesc.value) || 0;
        if (precio <= 0 || desc < 1 || desc > 100) { previewBox.classList.add('d-none'); return; }
        const final  = Math.round(precio * (1 - desc / 100));
        const ahorro = Math.round(precio - final);
        const fmt    = v => '$' + Math.round(v).toLocaleString('es-CO');
        previewBase.textContent   = fmt(precio);
        previewFinal.textContent  = fmt(final);
        previewAhorro.textContent = fmt(ahorro);
        previewBox.classList.remove('d-none');
    }

    if (selectPub) selectPub.addEventListener('change', actualizarPreview);
    if (inputDesc) inputDesc.addEventListener('input',  actualizarPreview);

    // Limpiar preview al cerrar el modal
    const modalCrear = document.getElementById('modalCrearPromocion');
    if (modalCrear) {
        modalCrear.addEventListener('hidden.bs.modal', function () {
            if (previewBox) previewBox.classList.add('d-none');
            if (inputDesc)  inputDesc.value = '';
            if (selectPub)  selectPub.selectedIndex = 0;
        });
    }

    // Sincronizar min de fecha_fin cuando fecha_inicio cambia (#192)
    const inputFechaInicio = document.querySelector('#modalCrearPromocion [name="fecha_inicio"]');
    const inputFechaFin    = document.querySelector('#modalCrearPromocion [name="fecha_fin"]');
    if (inputFechaInicio && inputFechaFin) {
        inputFechaInicio.addEventListener('change', function () {
            if (!this.value) return;
            const d = new Date(this.value + 'T00:00:00');
            d.setDate(d.getDate() + 1);
            const minFin = d.toISOString().split('T')[0];
            inputFechaFin.setAttribute('min', minFin);
            if (inputFechaFin.value && inputFechaFin.value <= this.value) {
                inputFechaFin.value = '';
            }
        });
    }

    // Inicializar gráfica solo cuando el modal se abre (no sobre canvas oculto)
    let chartInicializado = false;
    const modalEstadisticas = document.getElementById('modalEstadisticas');
    if (modalEstadisticas) {
        modalEstadisticas.addEventListener('shown.bs.modal', function() {
            if (!chartInicializado) {
                initChartPromociones();
                chartInicializado = true;
            }
        });
    }
});

function guardarPromocion() {
    // Simular guardado de promoción
    alert('Promoción creada correctamente (simulado)');
    
    // Cerrar modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalCrearPromocion'));
    if (modal) modal.hide();
    
    // Limpiar formulario
    document.getElementById('form-crear-promocion').reset();
}

function pausarPromocion(e) {
    e.preventDefault();
    const btn = e.currentTarget;
    const card = btn.closest('.card-promocion');
    const titulo = card.querySelector('h5')?.textContent || 'la promoción';
    
    if (confirm(`¿Estás seguro de pausar ${titulo}?`)) {
        // Cambiar apariencia
        card.style.opacity = '0.7';
        btn.textContent = 'Pausada';
        btn.classList.remove('btn-outline-danger');
        btn.classList.add('btn-secondary');
        btn.disabled = true;
        
        alert(`Promoción "${titulo}" pausada (simulado)`);
    }
}

function activarPromocion(e) {
    e.preventDefault();
    const btn = e.currentTarget;
    const card = btn.closest('.card-promocion');
    const titulo = card.querySelector('h6')?.textContent || 'la promoción';
    
    if (confirm(`¿Activar ${titulo}?`)) {
        // Mover a la sección de activas (simulado)
        alert(`Promoción "${titulo}" activada correctamente (simulado)`);
        
        // Cambiar botón
        btn.textContent = 'Activada';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-success');
        btn.disabled = true;
    }
}

function reactivarPromocion(e) {
    e.preventDefault();
    const btn = e.currentTarget;
    const row = btn.closest('tr');
    const titulo = row.querySelector('td:first-child .fw-semibold')?.textContent || 'la promoción';
    
    if (confirm(`¿Reactivar ${titulo}?`)) {
        alert(`Promoción "${titulo}" reactivada (simulado)`);
        
        // Cambiar botón
        btn.textContent = 'Reactivada';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-success');
        btn.disabled = true;
    }
}

function initChartPromociones() {
    const ctx = document.getElementById('chartPromociones')?.getContext('2d');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
            datasets: [{
                label: 'Ingresos por promociones',
                data: [450000, 520000, 680000, 720000, 890000, 1250000],
                borderColor: '#0066FF',
                backgroundColor: 'rgba(0, 102, 255, 0.1)',
                borderWidth: 3,
                pointBackgroundColor: '#0066FF',
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'white',
                    titleColor: '#0E1116',
                    bodyColor: '#64748b',
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('es-CO', {
                                    style: 'currency',
                                    currency: 'COP',
                                    minimumFractionDigits: 0
                                }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#e2e8f0'
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString('es-CO');
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Función para formatear moneda
function formatearMoneda(valor) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(valor);
}