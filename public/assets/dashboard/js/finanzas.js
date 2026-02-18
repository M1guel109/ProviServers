document.addEventListener("DOMContentLoaded", function () {
    
    // 1. VERIFICACIONES DE SEGURIDAD
    // Verificamos que ApexCharts esté cargado y que tengamos datos del backend
    if (typeof ApexCharts === 'undefined') {
        console.error('❌ Error: ApexCharts no está cargado.');
        return;
    }

    const lineChartEl = document.querySelector("#lineChart");
    const pieChartEl = document.querySelector("#pieChart");
    const periodoSelect = document.querySelector("#periodoSelect");

    // Datos por defecto (si PHP no envía nada, evitamos que rompa)
    let datosIngresos = (typeof dashboardData !== 'undefined') ? dashboardData.ingresos : [];
    let datosPlanes = (typeof dashboardData !== 'undefined') ? dashboardData.planes : [];

    // =============================================================
    // 2. PREPARACIÓN DE DATOS (Mapeo de PHP a Arrays JS)
    // =============================================================
    
    // A. Datos para Gráfico de Línea (Ingresos)
    let labelsMeses = datosIngresos.map(item => item.mes); 
    let dataMontos = datosIngresos.map(item => parseFloat(item.total));

    // Si no hay datos, ponemos placeholders para que se vea algo bonito
    if (labelsMeses.length === 0) {
        labelsMeses = ['Sin datos'];
        dataMontos = [0];
    }

    // B. Datos para Gráfico Circular (Planes)
    let labelsPlanes = datosPlanes.map(item => item.tipo);
    let dataCantidad = datosPlanes.map(item => parseInt(item.cantidad));

    if (labelsPlanes.length === 0) {
        labelsPlanes = ['Sin datos'];
        dataCantidad = [1]; // Valor dummy
    }

    // =============================================================
    // 3. GRÁFICO DE LÍNEA / ÁREA (Evolución de Ingresos)
    // =============================================================
    let chartLine; // Variable global para poder actualizarla luego

    if (lineChartEl) {
        const optionsLine = {
            series: [{
                name: "Ingresos",
                data: dataMontos
            }],
            chart: {
                height: 350,
                type: 'area', // 'area' se ve más moderno que 'line'
                fontFamily: 'Segoe UI, sans-serif',
                toolbar: { show: false }, // Ocultar menú hamburguesa del gráfico
                zoom: { enabled: false }
            },
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth', // Curvas suaves
                width: 3,
                colors: ['#0066FF']
            },
            fill: {
                type: "gradient",
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.6,
                    opacityTo: 0.1,
                    stops: [0, 90, 100],
                    colorStops: [
                        { offset: 0, color: "#0066FF", opacity: 0.5 },
                        { offset: 100, color: "#0066FF", opacity: 0.1 }
                    ]
                }
            },
            xaxis: {
                categories: labelsMeses,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: '#9ca3af' } }
            },
            yaxis: {
                labels: {
                    style: { colors: '#9ca3af' },
                    formatter: (value) => { 
                        // Formato abreviado (Ej: $1.2M o $500K) para que no ocupe mucho espacio
                        if(value >= 1000000) return "$" + (value/1000000).toFixed(1) + "M";
                        if(value >= 1000) return "$" + (value/1000).toFixed(0) + "K";
                        return "$" + value;
                    }
                }
            },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function (val) {
                        // Formato completo en el tooltip: $ 1.200.000
                        return "$ " + val.toLocaleString('es-CO');
                    }
                }
            },
            grid: {
                borderColor: '#f3f4f6',
                strokeDashArray: 4,
            },
            colors: ['#0066FF']
        };

        chartLine = new ApexCharts(lineChartEl, optionsLine);
        chartLine.render();
    }

    // =============================================================
    // 4. GRÁFICO DE DONA (Distribución de Planes)
    // =============================================================
    if (pieChartEl) {
        const optionsPie = {
            series: dataCantidad,
            labels: labelsPlanes,
            chart: {
                type: 'donut',
                height: 350,
                fontFamily: 'Segoe UI, sans-serif'
            },
            // Colores personalizados (Azul, Verde, Gris, Violeta, Cyan)
            colors: ['#0066FF', '#10B981', '#6B7280', '#8B5CF6', '#06B6D4'], 
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%', // Grosor del anillo
                        labels: {
                            show: true,
                            name: { show: true },
                            value: {
                                show: true,
                                formatter: function (val) {
                                    return parseInt(val);
                                }
                            },
                            total: {
                                show: true,
                                label: 'Total',
                                color: '#373d3f',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false }, // Ocultamos números sobre el gráfico para limpieza
            stroke: { show: false }, // Sin bordes blancos entre secciones
            legend: {
                position: 'bottom',
                horizontalAlign: 'center', 
            },
            tooltip: {
                enabled: true,
                y: {
                    formatter: function(val) {
                        return val + " proveedores";
                    }
                }
            }
        };

        const chartPie = new ApexCharts(pieChartEl, optionsPie);
        chartPie.render();
    }

    // =============================================================
    // 5. LÓGICA DEL SELECTOR DE PERIODO (Simulación)
    // =============================================================
    // Nota: Como tu backend actual solo devuelve los últimos 6 meses reales,
    // aquí simulamos los datos trimestrales/anuales estáticos para mantener tu funcionalidad visual.
    // En el futuro, esto debería hacer un fetch() a la API con ?periodo=anual.

    if (periodoSelect && chartLine) {
        
        // Datos Estáticos para demostración (Replica tu lógica anterior)
        const dataSimulada = {
            mensual: {
                cats: labelsMeses, // Usamos los reales de la BD
                data: dataMontos   // Usamos los reales de la BD
            },
            trimestral: {
                cats: ['Q1 2025', 'Q2 2025', 'Q3 2025', 'Q4 2025'],
                data: [5900000, 6950000, 7850000, 8295320]
            },
            anual: {
                cats: ['2021', '2022', '2023', '2024', '2025'],
                data: [12000000, 16500000, 21000000, 26000000, 28995320]
            }
        };

        periodoSelect.addEventListener('change', function(e) {
            const periodo = e.target.value;
            const seleccion = dataSimulada[periodo] || dataSimulada.mensual;

            console.log(`🔄 Actualizando gráfico a periodo: ${periodo}`);

            // ACTUALIZAR APEXCHARTS
            // 1. Actualizamos los datos (Series)
            chartLine.updateSeries([{
                data: seleccion.data
            }]);

            // 2. Actualizamos las categorías (Eje X)
            chartLine.updateOptions({
                xaxis: {
                    categories: seleccion.cats
                }
            });
        });
    }
});