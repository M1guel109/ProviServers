// dashboardCliente.js
document.addEventListener('DOMContentLoaded', () => {
    const btnToggleMenu = document.getElementById('btn-toggle-menu');
    const sidebar = document.querySelector('.sidebar');

    if (!btnToggleMenu || !sidebar) {
        console.error('No se encontró #btn-toggle-menu o .sidebar');
        return;
    }

    // Media query para distinguir móvil vs escritorio
    const mqMobile = window.matchMedia('(max-width: 767px)');

    function toggleSidebar() {
        if (mqMobile.matches) {
            // En móvil: abrir/cerrar tipo offcanvas
            sidebar.classList.toggle('sidebar-open');
        } else {
            // En escritorio: plegar/desplegar (iconos solamente)
            sidebar.classList.toggle('plegado');
        }
    }

    btnToggleMenu.addEventListener('click', toggleSidebar);

    // Al cambiar entre tamaños (ej. rotar pantalla), limpiamos estado móvil
    mqMobile.addEventListener('change', () => {
        sidebar.classList.remove('sidebar-open');
    });
});

