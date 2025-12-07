// ==================== ESPERAR A QUE EL DOM ESTÉ LISTO ====================
document.addEventListener('DOMContentLoaded', function() {
    
    // Datos para diferentes períodos - INGRESOS POR MEMBRESÍAS
    const dataMensual = {
        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        ingresos: [1850000, 2100000, 1950000, 2300000, 2450000, 2200000, 2600000, 2750000, 2500000, 2650000, 2800000, 2845320]
    };

    const dataTrimestral = {
        labels: ['Q1 2025', 'Q2 2025', 'Q3 2025', 'Q4 2025'],
        ingresos: [5900000, 6950000, 7850000, 8295320]
    };

    const dataAnual = {
        labels: ['2020', '2021', '2022', '2023', '2024', '2025'],
        ingresos: [8500000, 12000000, 16500000, 21000000, 26000000, 28995320]
    };

    // ==================== VERIFICAR QUE LOS CANVAS EXISTAN ====================
    const lineChartElement = document.getElementById('lineChart');
    const pieChartElement = document.getElementById('pieChart');
    const periodoSelectElement = document.getElementById('periodoSelect');

    if (!lineChartElement || !pieChartElement) {
        console.error('❌ Error: No se encontraron los elementos canvas');
        return;
    }

    if (typeof Chart === 'undefined') {
        console.error('❌ Error: Chart.js no está cargado');
        return;
    }

    // ==================== GRÁFICO DE LÍNEA - INGRESOS POR MEMBRESÍAS ====================
    const ctxLine = lineChartElement.getContext('2d');
    let lineChart = new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: dataMensual.labels,
            datasets: [{
                label: 'Ingresos por Membresías',
                data: dataMensual.ingresos,
                borderColor: '#0066FF',
                backgroundColor: 'rgba(0, 102, 255, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointBackgroundColor: '#0066FF',
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        font: {
                            family: 'Poppins',
                            size: 13
                        },
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        family: 'Roboto',
                        size: 14
                    },
                    bodyFont: {
                        family: 'Poppins',
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            return 'Ingresos: $' + context.parsed.y.toLocaleString('es-CO');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            family: 'Poppins',
                            size: 12
                        },
                        callback: function(value) {
                            return '$' + (value / 1000000).toFixed(1) + 'M';
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            family: 'Poppins',
                            size: 12
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    console.log('✅ Gráfico de ingresos creado correctamente');

    // ==================== GRÁFICO CIRCULAR - DISTRIBUCIÓN POR PLAN ====================
    const ctxPie = pieChartElement.getContext('2d');
    const pieChart = new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ['Premium', 'Basic', 'Free'],
            datasets: [{
                data: [45, 72, 30], // Cantidad de proveedores por plan
                backgroundColor: [
                    '#0066FF', // Premium - Azul
                    '#10B981', // Basic - Verde
                    '#6B7280'  // Free - Gris
                ],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        font: {
                            family: 'Poppins',
                            size: 13
                        },
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        family: 'Roboto',
                        size: 14
                    },
                    bodyFont: {
                        family: 'Poppins',
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' proveedores (' + percentage + '%)';
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });

    console.log('✅ Gráfico de distribución creado correctamente');

    // ==================== CAMBIO DE PERÍODO ====================
    if (periodoSelectElement) {
        periodoSelectElement.addEventListener('change', function(e) {
            let newData;
            
            switch(e.target.value) {
                case 'trimestral':
                    newData = dataTrimestral;
                    break;
                case 'anual':
                    newData = dataAnual;
                    break;
                default:
                    newData = dataMensual;
            }
            
            // Actualizar datos del gráfico
            lineChart.data.labels = newData.labels;
            lineChart.data.datasets[0].data = newData.ingresos;
            lineChart.update('active');

            console.log('✅ Período cambiado a:', e.target.value);
        });
    }

    console.log('✅ JavaScript de Finanzas cargado correctamente');
});