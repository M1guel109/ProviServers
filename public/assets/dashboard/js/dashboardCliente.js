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

    // Filtro en tiempo real
document.getElementById('buscadorServicios').addEventListener('input', function () {
    const query = this.value.toLowerCase();
    document.querySelectorAll('.service-card').forEach(card => {
    const text = card.innerText.toLowerCase();
    card.parentElement.style.display = text.includes(query) ? '' : 'none';
    });
});

// Animación de todas las barras de progreso
document.addEventListener("DOMContentLoaded", () => {
  const barras = document.querySelectorAll(".progress-bar[data-progreso]");

  barras.forEach(barra => {
    const porcentajeFinal = parseInt(barra.getAttribute("data-progreso"), 10);
    let progreso = 0;

    const intervalo = setInterval(() => {
      if (progreso >= porcentajeFinal) {
        clearInterval(intervalo);
      } else {
        progreso++;
        barra.style.width = progreso + "%";
        barra.setAttribute("aria-valuenow", progreso);
        barra.textContent = progreso + "%";
      }
    }, 30); // velocidad: cada 30ms sube 1%
  });
});


document.addEventListener("DOMContentLoaded", () => {
  // Animación al quitar favoritos (demo)
  document.querySelectorAll(".btn-fav").forEach(btn => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      const card = btn.closest(".col-lg-4, .col-md-6");
      card.classList.add("fade-out");
      setTimeout(() => card.remove(), 400);
    });
  });
});






