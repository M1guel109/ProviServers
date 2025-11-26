// JavaScript para el dashboard de proveedores

document.addEventListener('DOMContentLoaded', function() {
    // Toggle del menú lateral
    const btnToggleMenu = document.getElementById('btn-toggle-menu');
    const sidebar = document.querySelector('.sidebar');
    
    if (btnToggleMenu) {
        btnToggleMenu.addEventListener('click', function() {
            sidebar.classList.toggle('plegado');
        });
    }
    
    // Toggle de submenús
    const toggleSubmenuButtons = document.querySelectorAll('.toggle-submenu');
    
    toggleSubmenuButtons.forEach(button => {
        button.addEventListener('click', function() {
            const submenu = this.parentElement.querySelector('.submenu');
            const hasSubmenu = this.parentElement;
            
            hasSubmenu.classList.toggle('active');
            
            if (hasSubmenu.classList.contains('active')) {
                submenu.style.maxHeight = submenu.scrollHeight + 'px';
            } else {
                submenu.style.maxHeight = '0';
            }
        });
    });
    
    // Inicializar gráficos (ejemplo con ApexCharts)
    if (typeof ApexCharts !== 'undefined') {
        // Gráfica principal
        const chartOptions = {
            series: [{
                name: 'Servicios Completados',
                data: [30, 40, 35, 50, 49, 60, 70, 91, 125, 110, 95, 120]
            }, {
                name: 'Ingresos ($)',
                data: [1200, 1500, 1800, 2000, 2200, 2500, 2800, 3000, 3200, 2900, 3100, 3300]
            }],
            chart: {
                height: 300,
                type: 'line',
                zoom: {
                    enabled: false
                },
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            title: {
                text: '',
                align: 'left'
            },
            grid: {
                row: {
                    colors: ['#f3f3f3', 'transparent'],
                    opacity: 0.5
                },
            },
            xaxis: {
                categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            },
            colors: ['#3498db', '#2ecc71']
        };
        
        const chart = new ApexCharts(document.querySelector("#chart"), chartOptions);
        chart.render();
        
        // Cambiar período de la gráfica
        const periodoSelect = document.getElementById('periodo');
        if (periodoSelect) {
            periodoSelect.addEventListener('change', function() {
                // Aquí podrías actualizar la gráfica según el período seleccionado
                console.log('Período cambiado a:', this.value);
            });
        }
    }
    
    // Ejemplo de funcionalidad para notificaciones
    const notificaciones = document.querySelector('.notificaciones');
    if (notificaciones) {
        notificaciones.addEventListener('click', function() {
            alert('Tienes 3 notificaciones nuevas');
        });
    }
});