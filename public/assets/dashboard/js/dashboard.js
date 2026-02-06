/* ==========================================================================
   DASHBOARD.JS - Lógica específica del Panel Principal (Gráficas y Métricas)
   ========================================================================== */

document.addEventListener("DOMContentLoaded", () => {
    // Iniciamos la carga de datos apenas carga la página
    cargarDatosBackend();
});

/**
 * Función asíncrona para pedir datos al servidor
 */
async function cargarDatosBackend() {
    try {
        // Petición al endpoint que creamos en el index.php
        const response = await fetch(BASE_URL + '/admin/api/dashboard-data');
        
        // Convertir respuesta a JSON
        const data = await response.json();

        if (data.success) {
            console.log("Datos recibidos:", data); // Para depuración

            // 1. Renderizar Gráfica Principal (Área)
            renderizarGraficaServicios(data.grafica_principal);

            // 2. Renderizar Gráfica de Usuarios (Dona)
            renderizarGraficaUsuarios(data.tarjetas);

            // 3. Actualizar Tarjetas de Texto (Métricas)
            actualizarTarjetas(data.tarjetas);

            // 4. Actualizar Servicio Destacado
            actualizarServicioDestacado(data.servicio_destacado);
            
            // 5. Renderizar tercera gráfica (Usaremos datos estáticos o del backend si agregas más)
            renderizarGraficaMetricas(); 

        } else {
            console.error("Error del servidor:", data.error);
        }

    } catch (error) {
        console.error("Error de conexión:", error);
    }
}

/**
 * Actualiza los números en las tarjetas HTML
 */
function actualizarTarjetas(datosUsuarios) {
    // Buscamos el contenedor de métricas dentro de la tarjeta de usuarios
    const contenedorMetricas = document.querySelector('.tarjeta .metricas');
    
    if (contenedorMetricas) {
        // Asumiendo el orden: Primero Clientes, Segundo Proveedores
        const valores = contenedorMetricas.querySelectorAll('.valor');
        if (valores.length >= 2) {
            valores[0].innerText = datosUsuarios.clientes.toLocaleString(); // Formato 1,000
            valores[1].innerText = datosUsuarios.proveedores.toLocaleString();
        }
    }
}

/**
 * Actualiza la tarjeta del Servicio Destacado
 */
function actualizarServicioDestacado(servicio) {
    if (!servicio) return;

    const imgElement = document.querySelector('.servicio-imagen img');
    const nameElement = document.querySelector('.servicio-nombre');

    if (nameElement) nameElement.textContent = servicio.nombre || 'Sin datos';
    
    // Si tienes imagen en BD, úsala. Si no, deja la default.
    if (imgElement && servicio.imagen) {
        imgElement.src = BASE_URL + '/public/uploads/servicios/' + servicio.imagen;
    }
}

/* ==========================================================================
   CONFIGURACIÓN DE GRÁFICAS (APEXCHARTS)
   ========================================================================== */

// 1. GRÁFICA PRINCIPAL (Servicios por Mes)
function renderizarGraficaServicios(dataMensual) {
    var options = {
        chart: {
            type: 'area',
            height: 350,
            toolbar: { show: false },
            fontFamily: 'Poppins, sans-serif'
        },
        colors: ['#0066ff', '#0e1116'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        series: [
            {
                name: 'Servicios Publicados',
                data: dataMensual // [10, 5, 20...] Datos reales del PHP
            }
        ],
        xaxis: {
            // Meses estáticos
            categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            labels: { style: { colors: '#64748b' } }
        },
        yaxis: {
            labels: { style: { colors: '#64748b' } }
        },
        grid: {
            borderColor: '#e2e8f0',
            strokeDashArray: 4
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.3,
                stops: [0, 90, 100]
            }
        },
        tooltip: { theme: 'light' }
    };

    var chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();
}

// 2. GRÁFICA DE USUARIOS (Dona)
function renderizarGraficaUsuarios(datosUsuarios) {
    // Convertimos a números para evitar errores
    const clientes = parseInt(datosUsuarios.clientes) || 0;
    const proveedores = parseInt(datosUsuarios.proveedores) || 0;

    var optionsUsuarios = {
        chart: {
            type: 'donut',
            height: 280,
            fontFamily: 'Poppins, sans-serif'
        },
        series: [clientes, proveedores], // Datos reales
        labels: ['Clientes', 'Proveedores'],
        colors: ['#0066ff', '#1e293b'], // Azul ProviServers y Oscuro
        dataLabels: { enabled: false },
        legend: {
            position: 'bottom',
            itemMargin: { horizontal: 10, vertical: 5 }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '75%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total',
                            fontSize: '16px',
                            fontWeight: 600,
                            color: '#64748b',
                            formatter: function (w) {
                                // Sumar total
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                            }
                        },
                        value: {
                            fontSize: '24px',
                            fontWeight: 700,
                            color: '#0e1116',
                        }
                    }
                }
            }
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + " usuarios";
                }
            }
        }
    };

    var chartUsuarios = new ApexCharts(document.querySelector("#chart-usuarios"), optionsUsuarios);
    chartUsuarios.render();
}

// 3. TERCERA GRÁFICA (Métricas)
// (Esta la dejé con datos estáticos por ahora, ya que el controlador no enviaba datos específicos para esta)
function renderizarGraficaMetricas() {
    var optionsMetricas = {
        chart: {
            type: 'bar', // Cambié a barra para variar visualmente
            height: 280,
            toolbar: { show: false },
            fontFamily: 'Poppins, sans-serif'
        },
        series: [
            {
                name: 'Ingresos',
                data: [25, 65, 55, 45, 50, 75, 100] 
            }
        ],
        colors: ['#0066ff'],
        plotOptions: {
            bar: { borderRadius: 4, columnWidth: '50%' }
        },
        xaxis: {
            categories: ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'],
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        grid: {
            borderColor: '#f1f5f9',
            strokeDashArray: 4
        }
    };

    if(document.querySelector("#chart-nuevos-servicios")) {
        var chartMetricas = new ApexCharts(document.querySelector("#chart-nuevos-servicios"), optionsMetricas);
        chartMetricas.render();
    }
}