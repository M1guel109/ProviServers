/* ======================================================
   finanzas.js - Gráficas y funcionalidad de finanzas
   ====================================================== */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Finanzas cargadas');
    
    // Inicializar gráficas
    initCharts();
    
    // Event listeners para filtros
    const aplicarFiltro = document.getElementById('aplicar-filtro');
    const periodoSelect = document.getElementById('periodo-finanzas');
    
    if (aplicarFiltro) {
        aplicarFiltro.addEventListener('click', function() {
            const periodo = periodoSelect ? periodoSelect.value : 'mes';
            console.log('Filtrar por período:', periodo);
            alert(`Filtrando datos financieros por: ${periodo}`);
        });
    }
    
    // Toggle entre tipos de gráfica
    const chartButtons = document.querySelectorAll('[data-chart]');
    chartButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Quitar clase active de todos
            chartButtons.forEach(b => b.classList.remove('active'));
            // Agregar active al clickeado
            this.classList.add('active');
            
            const tipo = this.getAttribute('data-chart');
            actualizarTipoGrafico(tipo);
        });
    });
    
    // Botones de exportar (simulado)
    const btnExportar = document.querySelector('#modalExportar .btn-success');
    if (btnExportar) {
        btnExportar.addEventListener('click', function() {
            alert('Reporte exportado exitosamente (simulado)');
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalExportar'));
            if (modal) modal.hide();
        });
    }
});

// Variables globales para las gráficas
let chartIngresosGastos, chartIngresosCategorias;

function initCharts() {
    // Verificar que los datos existen
    if (typeof datosIngresos === 'undefined' || typeof datosGastos === 'undefined') {
        console.error('Datos no disponibles');
        return;
    }
    
    // Gráfica de ingresos vs gastos
    const ctxIngresosGastos = document.getElementById('chartIngresosGastos')?.getContext('2d');
    if (ctxIngresosGastos) {
        chartIngresosGastos = new Chart(ctxIngresosGastos, {
            type: 'bar',
            data: {
                labels: datosIngresos.meses,
                datasets: [
                    {
                        label: 'Ingresos',
                        data: datosIngresos.valores,
                        backgroundColor: 'rgba(0, 102, 255, 0.7)',
                        borderColor: '#0066FF',
                        borderWidth: 1,
                        borderRadius: 6
                    },
                    {
                        label: 'Gastos',
                        data: datosGastos.valores,
                        backgroundColor: 'rgba(231, 76, 60, 0.7)',
                        borderColor: '#e74c3c',
                        borderWidth: 1,
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#64748b',
                            font: {
                                size: 12
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'white',
                        titleColor: '#0E1116',
                        bodyColor: '#64748b',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        padding: 10,
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
    
    // Gráfica de ingresos por categoría (pastel)
    const ctxCategorias = document.getElementById('chartIngresosCategorias')?.getContext('2d');
    if (ctxCategorias) {
        chartIngresosCategorias = new Chart(ctxCategorias, {
            type: 'doughnut',
            data: {
                labels: ['Plomería', 'Electricidad', 'Pintura', 'Jardinería', 'Limpieza'],
                datasets: [{
                    data: [1250000, 980000, 720000, 450000, 380000],
                    backgroundColor: [
                        '#0066FF',
                        '#f39c12',
                        '#27ae60',
                        '#9b59b6',
                        '#3498db'
                    ],
                    borderColor: 'white',
                    borderWidth: 2,
                    hoverOffset: 10
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
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: $${value.toLocaleString('es-CO')} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }
}

function actualizarTipoGrafico(tipo) {
    if (!chartIngresosGastos) return;
    
    chartIngresosGastos.config.type = tipo;
    chartIngresosGastos.update();
}

// Función para formatear moneda
function formatearMoneda(valor) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(valor);
}

// Función para actualizar datos (cuando se implemente el backend)
function actualizarFinanzas(periodo) {
    // Aquí iría un fetch al backend para obtener nuevos datos
    console.log('Actualizando finanzas para período:', periodo);
    
    // Ejemplo de actualización:
    /*
    fetch(`${BASE_URL}/proveedor/finanzas/datos?periodo=${periodo}`)
        .then(response => response.json())
        .then(data => {
            if (chartIngresosGastos) {
                chartIngresosGastos.data.labels = data.meses;
                chartIngresosGastos.data.datasets[0].data = data.ingresos;
                chartIngresosGastos.data.datasets[1].data = data.gastos;
                chartIngresosGastos.update();
            }
            
            // Actualizar tarjetas financieras
            actualizarTarjetas(data.resumen);
        })
        .catch(error => console.error('Error:', error));
    */
}

function actualizarTarjetas(resumen) {
    // Actualizar los valores de las tarjetas principales
    // Ejemplo:
    /*
    document.querySelectorAll('.financiera-valor').forEach((el, index) => {
        // Lógica para actualizar según el índice o usar IDs específicos
    });
    */
}