document.addEventListener("DOMContentLoaded", () => {

    // =======================================================
    // 1. GRÁFICA PRINCIPAL (#chart)
    // =======================================================
    const chartElement1 = document.querySelector("#chart");
    
    if (chartElement1) {
        var options = {
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false }
            },
            colors: ['#0066ff', '#0e1116'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth' },
            series: [
                {
                    name: 'Servicios publicados',
                    data: [20, 35, 28, 27, 55, 30, 90, 50, 65, 28, 60, 20]
                },
                {
                    name: 'Servicios contratados',
                    data: [20, 70, 40, 30, 50, 50, 30, 55, 40, 35, 90, 25]
                }
            ],
            xaxis: {
                categories: ['5k', '10k', '15k', '20k', '25k', '30k', '35k', '40k', '45k', '50k', '55k', '60k']
            },
            legend: {
                position: 'bottom',
                markers: { width: 12, height: 12 }
            },
            fill: {
                type: 'solid',
                opacity: 0.6
            }
        };

        var chart = new ApexCharts(chartElement1, options);
        chart.render();
    }


    // =======================================================
    // 2. GRÁFICA DE USUARIOS (#chart-usuarios)
    // =======================================================
    const chartElement2 = document.querySelector("#chart-usuarios");

    if (chartElement2) {
        var optionsUsuarios = {
            chart: {
                type: 'donut',
                height: 250
            },
            series: [34249, 1420], 
            labels: ['Clientes activos', 'Proveedores activos'],
            colors: ['#007bff', '#000000'],
            dataLabels: { enabled: false },
            legend: { position: 'bottom' },
            plotOptions: {
                pie: {
                    donut: { size: '70%' }
                }
            }
        };

        var chartUsuarios = new ApexCharts(chartElement2, optionsUsuarios);
        chartUsuarios.render();
    }


    // =======================================================
    // 3. GRÁFICA DE MÉTRICAS (#chart-nuevos-servicios)
    // =======================================================
    const chartElement3 = document.querySelector("#chart-nuevos-servicios");

    if (chartElement3) {
        var optionsMetricas = {
            chart: {
                type: 'line',
                height: 280,
                toolbar: { show: false }
            },
            series: [
                {
                    name: 'Servicios publicados',
                    data: [25, 65, 55, 45, 50, 75, 100]
                },
                {
                    name: 'Servicios contratados',
                    data: [0, 50, 60, 25, 30, 60, 95]
                }
            ],
            stroke: { width: 3, curve: 'smooth' },
            markers: { size: 5 },
            xaxis: {
                categories: ['2015', '2016', '2017', '2018', '2019'],
                labels: { style: { fontSize: '12px' } }
            },
            yaxis: {
                labels: { style: { fontSize: '12px' } }
            },
            grid: { strokeDashArray: 4 },
            dataLabels: { enabled: false },
            legend: { show: true, position: 'bottom' }
        };

        var chartMetricas = new ApexCharts(chartElement3, optionsMetricas);
        chartMetricas.render();
    }


    // =======================================================
    // 4. LÓGICA DEL SIDEBAR (Menú Desplegable)
    // =======================================================
    const triggers = document.querySelectorAll(".has-submenu > a, .has-submenu .toggle-submenu");

    triggers.forEach(trigger => {
        trigger.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();

            const parentLi = trigger.closest(".has-submenu");
            const submenu = parentLi.querySelector(".submenu");

            if (!submenu) return;

            const isOpen = parentLi.classList.contains("active");

            if (isOpen) {
                // CERRAR
                submenu.style.maxHeight = submenu.scrollHeight + "px";
                submenu.offsetHeight; // Force reflow
                parentLi.classList.remove("active");
                submenu.style.maxHeight = "0";
            } else {
                // ABRIR
                parentLi.classList.add("active");
                submenu.style.maxHeight = submenu.scrollHeight + "px";
            }
        });
    });

});