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

document.addEventListener("DOMContentLoaded", () => {
  // Ejemplo: acción al contratar de nuevo
  document.querySelectorAll("#historial-servicios .btn-primary").forEach(btn => {
    btn.addEventListener("click", () => {
      alert("Función de contratar de nuevo en construcción...");
    });
  });

  // Ejemplo: acción al ver detalles
  document.querySelectorAll("#historial-servicios .btn-outline-primary").forEach(btn => {
    btn.addEventListener("click", () => {
      alert("Función de ver detalles en construcción...");
    });
  });
});

document.addEventListener("DOMContentLoaded", () => {
  // Cambiar foto de perfil (demo)
  const changePhotoBtn = document.querySelector(".profile-card .btn-outline-primary");
  if (changePhotoBtn) {
    changePhotoBtn.addEventListener("click", () => {
      alert("Función de cambiar foto en construcción...");
    });
  }

  // Guardar cambios en perfil
  const editForm = document.querySelector("#profile-edit form");
  if (editForm) {
    editForm.addEventListener("submit", (e) => {
      e.preventDefault();
      alert("Cambios de perfil guardados (demo). Aquí conectarías con tu backend.");
    });
  }

  // Guardar configuración
  const settingsForm = document.querySelector("#profile-settings form");
  if (settingsForm) {
    settingsForm.addEventListener("submit", (e) => {
      e.preventDefault();
      alert("Configuración guardada (demo).");
    });
  }

  // Cambiar contraseña
  const passwordForm = document.querySelector("#profile-change-password form");
  if (passwordForm) {
    passwordForm.addEventListener("submit", (e) => {
      e.preventDefault();
      alert("Contraseña actualizada (demo).");
    });
  }
});






