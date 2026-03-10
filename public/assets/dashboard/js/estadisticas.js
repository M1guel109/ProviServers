/* ======================================================
   estadisticas.js - Gráficas y funcionalidad de estadísticas
   ====================================================== */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Estadísticas cargadas');
    
    // Inicializar gráficas
    initCharts();
    
    // Event listeners para filtros
    const aplicarFiltro = document.getElementById('aplicar-filtro');
    const periodoSelect = document.getElementById('periodo-estadisticas');
    
    if (aplicarFiltro) {
        aplicarFiltro.addEventListener('click', function() {
            const periodo = periodoSelect ? periodoSelect.value : 'mes';
            console.log('Filtrar por período:', periodo);
            // Aquí iría la lógica para actualizar datos según el período
            // Por ahora solo mostramos un mensaje
            alert(`Filtrando datos por: ${periodo}`);
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
});

// Variables globales para las gráficas
let chartIngresos, chartCategorias;

function initCharts() {
    // Verificar que los datos existen
    if (typeof datosIngresos === 'undefined' || typeof datosCategorias === 'undefined') {
        console.error('Datos no disponibles');
        return;
    }
    
    // Gráfica de ingresos
    const ctxIngresos = document.getElementById('chartIngresos')?.getContext('2d');
    if (ctxIngresos) {
        chartIngresos = new Chart(ctxIngresos, {
            type: 'line',
            data: {
                labels: datosIngresos.meses,
                datasets: [{
                    label: 'Ingresos ($)',
                    data: datosIngresos.valores,
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
    
    // Gráfica de categorías (pastel)
    const ctxCategorias = document.getElementById('chartCategorias')?.getContext('2d');
    if (ctxCategorias) {
        chartCategorias = new Chart(ctxCategorias, {
            type: 'doughnut',
            data: {
                labels: datosCategorias.categorias,
                datasets: [{
                    data: datosCategorias.valores,
                    backgroundColor: [
                        '#0066FF',
                        '#27ae60',
                        '#f39c12',
                        '#9b59b6',
                        '#e74c3c',
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
                        position: 'bottom',
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
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} servicios (${percentage}%)`;
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
    if (!chartIngresos) return;
    
    chartIngresos.config.type = tipo;
    chartIngresos.update();
    
    // Cambiar opciones según el tipo
    if (tipo === 'bar') {
        chartIngresos.data.datasets[0].fill = false;
        chartIngresos.data.datasets[0].tension = 0;
    } else {
        chartIngresos.data.datasets[0].fill = true;
        chartIngresos.data.datasets[0].tension = 0.3;
    }
}

// Función para actualizar datos (cuando se implemente el backend)
function actualizarEstadisticas(periodo) {
    // Aquí iría un fetch al backend para obtener nuevos datos
    console.log('Actualizando estadísticas para período:', periodo);
    
    // Ejemplo de actualización:
    /*
    fetch(`${BASE_URL}/proveedor/estadisticas/datos?periodo=${periodo}`)
        .then(response => response.json())
        .then(data => {
            if (chartIngresos) {
                chartIngresos.data.labels = data.meses;
                chartIngresos.data.datasets[0].data = data.valores;
                chartIngresos.update();
            }
            
            if (chartCategorias) {
                chartCategorias.data.labels = data.categorias;
                chartCategorias.data.datasets[0].data = data.valoresCategorias;
                chartCategorias.update();
            }
            
            // Actualizar tarjetas de estadísticas
            actualizarTarjetas(data.resumen);
        })
        .catch(error => console.error('Error:', error));
    */
}

function actualizarTarjetas(resumen) {
    // Actualizar los valores de las tarjetas principales
    // Ejemplo:
    /*
    document.querySelectorAll('.estadistica-valor').forEach((el, index) => {
        // Lógica para actualizar según el índice o usar IDs específicos
    });
    */
}