document.addEventListener("DOMContentLoaded", () => {

    // ================================================================
    // VARIABLES GLOBALES DE GRÁFICAS
    // ================================================================
    let chartPrincipal = null;   // Referencia a la gráfica principal (para destruir/recrear)
    let chartUsuarios  = null;
    let chartMetricas  = null;

    // ================================================================
    // 1. INICIALIZACIÓN — Cargar todo al arrancar
    // ================================================================
    cargarDashboard('mensual');

    // ================================================================
    // 2. SELECTOR DE PERÍODO — Escucha cambios y recarga la gráfica
    // ================================================================
    const selectPeriodo = document.getElementById('periodo');
    if (selectPeriodo) {
        selectPeriodo.addEventListener('change', () => {
            cargarDashboard(selectPeriodo.value);
        });
    }

    // ================================================================
    // 3. FUNCIÓN PRINCIPAL — Fetch a nuestro endpoint PHP
    // ================================================================
    function cargarDashboard(periodo) {
        fetch(`${BASE_URL}/admin/dashboard-stats?periodo=${periodo}`)
            .then(res => {
                if (!res.ok) throw new Error('Error en la respuesta del servidor');
                return res.json();
            })
            .then(data => {
                renderGraficaPrincipal(data.grafica);
                renderGraficaUsuarios(data.metricas);
                renderGraficaMetricas(data.grafica);
                actualizarMetricasNumericas(data.metricas);
            })
            .catch(err => {
                console.error('Error cargando el dashboard:', err);
            });
    }

    // ================================================================
    // 4. GRÁFICA PRINCIPAL — Publicaciones vs Contratados
    // ================================================================
    function renderGraficaPrincipal(grafica) {
        const el = document.querySelector("#chart");
        if (!el) return;

        // Destruir instancia anterior si existe (evita gráficas apiladas)
        if (chartPrincipal) {
            chartPrincipal.destroy();
        }

        chartPrincipal = new ApexCharts(el, {
            chart: {
                type: 'area',
                height: 320,
                toolbar: { show: false },
                animations: { enabled: true, speed: 600 }
            },
            colors: ['#0066ff', '#0e1116'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth' },
            series: [
                {
                    name: 'Servicios publicados',
                    data: grafica.publicaciones
                },
                {
                    name: 'Servicios contratados',
                    data: grafica.contratados
                }
            ],
            xaxis: {
                categories: grafica.labels,
                labels: { style: { fontSize: '12px' } }
            },
            legend: {
                position: 'bottom',
                markers: { width: 12, height: 12 }
            },
            fill: {
                type: 'solid',
                opacity: 0.6
            },
            // Mensaje cuando no hay datos
            noData: {
                text: 'Sin datos para este período',
                align: 'center',
                verticalAlign: 'middle',
                style: { color: '#94a3b8', fontSize: '14px' }
            }
        });

        chartPrincipal.render();
    }

    // ================================================================
    // 5. GRÁFICA DONUT — Clientes vs Proveedores activos
    // ================================================================
    function renderGraficaUsuarios(metricas) {
        const el = document.querySelector("#chart-usuarios");
        if (!el) return;

        if (chartUsuarios) chartUsuarios.destroy();

        const c = parseInt(metricas.clientes_activos)    || 0;
        const p = parseInt(metricas.proveedores_activos) || 0;

        if (c === 0 && p === 0) {
            el.innerHTML = '<p class="text-center text-muted py-4 small">Sin usuarios activos</p>';
            return;
        }

        chartUsuarios = new ApexCharts(el, {
            chart: {
                type: 'donut',
                height: 220
            },
            series: [c, p],
            labels: ['Clientes activos', 'Proveedores activos'],
            colors: ['#007bff', '#000000'],
            dataLabels: { enabled: false },
            legend: { position: 'bottom' },
            plotOptions: {
                pie: { donut: { size: '70%' } }
            },
            noData: {
                text: 'Sin datos',
                style: { color: '#94a3b8' }
            }
        });

        chartUsuarios.render();
    }

    // ================================================================
    // 6. GRÁFICA DE LÍNEA — Métricas (mismos datos, diferente visual)
    // ================================================================
    function renderGraficaMetricas(grafica) {
        const el = document.querySelector("#chart-nuevos-servicios");
        if (!el) return;

        if (chartMetricas) chartMetricas.destroy();

        chartMetricas = new ApexCharts(el, {
            chart: {
                type: 'line',
                height: 220,
                toolbar: { show: false }
            },
            series: [
                {
                    name: 'Publicados',
                    data: grafica.publicaciones
                },
                {
                    name: 'Contratados',
                    data: grafica.contratados
                }
            ],
            stroke: { width: 3, curve: 'smooth' },
            markers: { size: 4 },
            xaxis: {
                categories: grafica.labels,
                labels: { style: { fontSize: '11px' } }
            },
            grid: { strokeDashArray: 4 },
            dataLabels: { enabled: false },
            legend: { show: true, position: 'bottom' },
            colors: ['#0066ff', '#0e1116'],
            noData: {
                text: 'Sin datos',
                style: { color: '#94a3b8' }
            }
        });

        chartMetricas.render();
    }

    // ================================================================
    // 7. MÉTRICAS NUMÉRICAS — Actualizar los spans con datos reales
    // ================================================================
    function actualizarMetricasNumericas(metricas) {
        // Seleccionamos los dos spans .valor dentro de .metricas
        const spans = document.querySelectorAll('.metricas .valor');

        if (spans[0]) {
            spans[0].textContent = Number(metricas.clientes_total || 0).toLocaleString('es-CO');
        }
        if (spans[1]) {
            spans[1].textContent = Number(metricas.proveedores_total || 0).toLocaleString('es-CO');
        }
    }

    // ================================================================
    // 8. SIDEBAR — Lógica de submenús desplegables (sin cambios)
    // ================================================================
    const triggers = document.querySelectorAll(".has-submenu > a, .has-submenu .toggle-submenu");

    triggers.forEach(trigger => {
        trigger.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();

            const parentLi = trigger.closest(".has-submenu");
            const submenu  = parentLi.querySelector(".submenu");

            if (!submenu) return;

            const isOpen = parentLi.classList.contains("active");

            if (isOpen) {
                submenu.style.maxHeight = submenu.scrollHeight + "px";
                submenu.offsetHeight; // Force reflow
                parentLi.classList.remove("active");
                submenu.style.maxHeight = "0";
            } else {
                parentLi.classList.add("active");
                submenu.style.maxHeight = submenu.scrollHeight + "px";
            }
        });
    });

});