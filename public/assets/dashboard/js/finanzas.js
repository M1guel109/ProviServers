// ==================== ESPERAR A QUE EL DOM ESTÉ LISTO ====================
document.addEventListener('DOMContentLoaded', function() {
    
    // Datos para diferentes períodos
    const dataMensual = {
        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        ingresos: [1200000, 1400000, 1100000, 1600000, 1800000, 1500000, 2000000, 2200000, 1900000, 2100000, 2400000, 2845320],
        gastos: [800000, 900000, 750000, 1000000, 1100000, 950000, 1200000, 1300000, 1100000, 1250000, 1400000, 895420]
    };

    const dataSemanal = {
        labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
        ingresos: [650000, 720000, 680000, 795320],
        gastos: [220000, 240000, 210000, 225420]
    };

    const dataAnual = {
        labels: ['2020', '2021', '2022', '2023', '2024', '2025'],
        ingresos: [12000000, 15000000, 18000000, 21000000, 24000000, 28000000],
        gastos: [8000000, 9500000, 11000000, 13000000, 15000000, 16500000]
    };

    // ==================== VERIFICAR QUE LOS CANVAS EXISTAN ====================
    const lineChartElement = document.getElementById('lineChart');
    const pieChartElement = document.getElementById('pieChart');
    const periodoSelectElement = document.getElementById('periodoSelect');

    // Si no existen los elementos, salir
    if (!lineChartElement || !pieChartElement) {
        console.error('Error: No se encontraron los elementos canvas');
        return;
    }

    // Verificar que Chart.js esté cargado
    if (typeof Chart === 'undefined') {
        console.error('Error: Chart.js no está cargado');
        return;
    }

    // ==================== GRÁFICO DE LÍNEA ====================
    const ctxLine = lineChartElement.getContext('2d');
    let lineChart = new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: dataMensual.labels,
            datasets: [{
                label: 'Ingresos',
                data: dataMensual.ingresos,
                borderColor: '#0066FF',
                backgroundColor: 'rgba(0, 102, 255, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#0066FF',
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 2
            }, {
                label: 'Gastos',
                data: dataMensual.gastos,
                borderColor: '#EF4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#EF4444',
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 2
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
                            return context.dataset.label + ': $' + context.parsed.y.toLocaleString('es-CO');
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

    console.log('✅ Gráfico de línea creado correctamente');

    // ==================== GRÁFICO CIRCULAR ====================
    const ctxPie = pieChartElement.getContext('2d');
    const pieChart = new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ['Nómina', 'Materiales', 'Servicios', 'Marketing', 'Otros'],
            datasets: [{
                data: [450000, 180000, 125000, 85000, 55420],
                backgroundColor: [
                    '#0066FF',
                    '#10B981',
                    '#F59E0B',
                    '#9333EA',
                    '#6B7280'
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
                            return label + ': $' + value.toLocaleString('es-CO') + ' (' + percentage + '%)';
                        }
                    }
                },
                cutout: '65%'
            }
        }
    });

    console.log('✅ Gráfico circular creado correctamente');

    // ==================== CAMBIO DE PERÍODO ====================
    if (periodoSelectElement) {
        periodoSelectElement.addEventListener('change', function(e) {
            let newData;
            
            switch(e.target.value) {
                case 'semanal':
                    newData = dataSemanal;
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
            lineChart.data.datasets[1].data = newData.gastos;
            lineChart.update('active');

            console.log('✅ Período cambiado a:', e.target.value);
        });
    }

    console.log('✅ JavaScript de Finanzas cargado correctamente');
});