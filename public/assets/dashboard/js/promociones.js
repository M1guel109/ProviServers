/* ======================================================
   promociones.js - Funcionalidad para gestión de promociones
   ====================================================== */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Promociones cargadas');
    
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
    
    // Inicializar gráfica en modal de estadísticas
    initChartPromociones();
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