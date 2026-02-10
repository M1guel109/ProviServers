/*Primera grafica */



var options = {
    chart: {
        type: 'area',
        height: 350,
        toolbar: { show: false }
    },
    colors: ['#0066ff', '#0e1116'], // Azul y gris oscuro
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

var chart = new ApexCharts(document.querySelector("#chart"), options);
chart.render();

/*segunda grafica */

var optionsUsuarios = {
    chart: {
        type: 'donut',
        height: 250
    },
    series: [34249, 1420], // Clientes activos, Proveedores activos
    labels: ['34249', '1420'],
    labels: ['Clientes activos', 'Proveedores activos'],
    colors: ['#007bff', '#000000'], // Opcional: azul y negro como en el diseño
    dataLabels: {
        enabled: false
    },
    legend: {
        position: 'bottom'
    },
    plotOptions: {
        pie: {
            donut: {
                size: '70%' // Ajusta el grosor del anillo
            }
        }
    }
};

var chartUsuarios = new ApexCharts(document.querySelector("#chart-usuarios"), optionsUsuarios);
chartUsuarios.render();

/*tercera grafica */

var optionsMetricas = {
    chart: {
        type: 'line',
        height: 280,
        toolbar: { show: false }
    },
    series: [
        {
            name: 'Servicios publicados',
            data: [25, 65, 55, 45, 50, 75, 100] // azul
        },
        {
            name: 'Servicios contratados',
            data: [0, 50, 60, 25, 30, 60, 95] // negro
        }
    ],
    stroke: {
        width: 3,
        curve: 'smooth'
    },
    markers: {
        size: 5
    },
    xaxis: {
        categories: ['2015', '2016', '2017', '2018', '2019'],
        labels: {
            style: { fontSize: '12px' }
        }
    },
    yaxis: {
        labels: {
            style: { fontSize: '12px' }
        }
    },
    grid: {
        strokeDashArray: 4
    },
    dataLabels: {
        enabled: false
    },
    legend: {
        show: true,
        position: 'bottom'
    }
};

var chartMetricas = new ApexCharts(
    document.querySelector("#chart-nuevos-servicios"),
    optionsMetricas
);
chartMetricas.render();

document.addEventListener("DOMContentLoaded", () => {

    // Seleccionamos TODOS los elementos que pueden abrir el menú:
    // 1. El enlace de texto (a) directo hijo de .has-submenu
    // 2. El botón de la flecha (.toggle-submenu)
    const triggers = document.querySelectorAll(".has-submenu > a, .has-submenu .toggle-submenu");

    triggers.forEach(trigger => {
        trigger.addEventListener("click", (e) => {
            // Detenemos cualquier comportamiento por defecto (como recargar página)
            e.preventDefault();
            // Detenemos la propagación para que no haga burbuja a otros elementos
            e.stopPropagation();

            // 1. Identificar el contenedor padre y el submenú
            // Usamos closest para encontrar el LI padre sin importar cuál de los dos se clickeó
            const parentLi = trigger.closest(".has-submenu");
            const submenu = parentLi.querySelector(".submenu");

            // Si no hay submenú, no hacemos nada
            if (!submenu) return;

            // 2. Determinar si vamos a ABRIR o CERRAR
            // Verificamos si ya tiene la clase active
            const isOpen = parentLi.classList.contains("active");

            if (isOpen) {
                // --- CERRAR ---

                // Paso clave para que la animación no sea brusca:
                // Antes de colapsar, reasignamos la altura actual en píxeles explícitamente.
                // Esto permite al navegador saber desde dónde animar hacia 0.
                submenu.style.maxHeight = submenu.scrollHeight + "px";

                // Forzamos un "reflow" (lectura de propiedad) para que el navegador aplique el estilo de arriba
                // antes de aplicar el estilo de cierre.
                submenu.offsetHeight;

                // Ahora sí, colapsamos
                parentLi.classList.remove("active");
                submenu.style.maxHeight = "0";

            } else {
                // --- ABRIR ---

                // (Opcional) Cerrar otros menús abiertos si quieres efecto acordeón:
                /* document.querySelectorAll('.has-submenu.active').forEach(activeLi => {
                    activeLi.classList.remove('active');
                    activeLi.querySelector('.submenu').style.maxHeight = "0";
                });
                */

                parentLi.classList.add("active");
                // Asignamos la altura real del contenido para que se expanda suavemente
                submenu.style.maxHeight = submenu.scrollHeight + "px";
            }
        });
    });
});
