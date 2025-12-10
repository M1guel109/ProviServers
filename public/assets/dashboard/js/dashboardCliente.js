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

// Formulario con validación y campo "Otros"
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formNecesidad");
  const modalEl = document.getElementById("modalNecesidad");

  // Campo "Otros"
  const selectCategoria = document.getElementById("categoria");
  const otroWrapper = document.getElementById("categoriaOtroWrapper");
  const inputOtro = document.getElementById("categoriaOtro");

  if (!form) return;

  // Mostrar/ocultar campo "Otros"
  selectCategoria.addEventListener("change", () => {
    const esOtros = selectCategoria.value === "Otros";
    otroWrapper.classList.toggle("d-none", !esOtros);
    inputOtro.required = esOtros;

    if (!esOtros) {
      inputOtro.value = "";
      inputOtro.classList.remove("is-invalid", "is-valid");
    }
  });

  // Validación y envío simulado
  form.addEventListener("submit", (event) => {
    event.preventDefault();
    event.stopPropagation();

    if (form.checkValidity()) {
      alert("Tu necesidad fue publicada correctamente (simulado).");
      form.reset();
      form.classList.remove("was-validated");

      // Cerrar modal si existe
      if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.hide();
      }
    } else {
      form.classList.add("was-validated");
      alert("Por favor completa todos los campos obligatorios antes de enviar.");
    }
  });
});






//Explorar servicios
document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.querySelector("input.form-control");
  const searchForm = document.querySelector("form.d-flex");
  const serviceCols = document.querySelectorAll("#contenedor-servicios .col-md-4");
  const categoryButtons = document.querySelectorAll(".category-filters button");
  const exitBtn = document.getElementById("exitCategory");

  // Función para normalizar texto (quita acentos y pasa a minúsculas)
  function normalizeText(text) {
    return text
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "");
  }

  // Función para mostrar todas las tarjetas
  function resetCards() {
    serviceCols.forEach(col => {
      col.style.display = "block";
    });
  }

  // --- FILTRO POR NOMBRE/PROVEEDOR EN TIEMPO REAL ---
  let timeout;
  searchInput.addEventListener("input", () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
      const query = normalizeText(searchInput.value);

      if (query === "") {
        resetCards(); // si está vacío, mostrar todo
        return;
      }

      serviceCols.forEach(col => {
        const title = normalizeText(col.querySelector(".card-title").textContent);
        const provider = normalizeText(col.querySelector(".card-subtitle").textContent);

        col.style.display =
          (title.includes(query) || provider.includes(query)) ? "block" : "none";
      });
    }, 300); // espera 300ms antes de ejecutar
  });

  // --- FILTRO POR CATEGORÍA ---
  categoryButtons.forEach(button => {
    button.addEventListener("click", () => {
      const category = normalizeText(button.textContent.trim());

      if (category === "" || category === "todos") {
        resetCards();
        return;
      }

      serviceCols.forEach(col => {
        const description = normalizeText(col.querySelector(".card-text").textContent);
        const title = normalizeText(col.querySelector(".card-title").textContent);

        col.style.display =
          (description.includes(category) || title.includes(category)) ? "block" : "none";
      });
    });
  });

  // --- FILTRO POR UBICACIÓN ---
  function filterByLocation(location) {
    if (!location) {
      resetCards();
      return;
    }
    const locQuery = normalizeText(location);
    serviceCols.forEach(col => {
      const loc = normalizeText(col.querySelector(".card-location")?.textContent || "");
      col.style.display = loc.includes(locQuery) ? "block" : "none";
    });
  }

// --- FILTRO POR CATEGORÍA ---
function filterByCategory(category) {
  if (!category) {
    resetCards(); // si no hay categoría, mostrar todo
    return;
  }
  const catQuery = normalizeText(category);

  serviceCols.forEach(col => {
    const cardCategory = normalizeText(col.querySelector(".card-category")?.textContent || "");
    const cardTitle = normalizeText(col.querySelector(".card-title")?.textContent || "");
    const cardDescription = normalizeText(col.querySelector(".card-text")?.textContent || "");

    // Mostrar si la categoría coincide con alguno de los campos
    col.style.display =
      (cardCategory.includes(catQuery) ||
       cardTitle.includes(catQuery) ||
       cardDescription.includes(catQuery))
        ? "block"
        : "none";
  });
}

  // --- FILTRO POR CALIFICACIÓN ---
  function filterByRating(minRating) {
    if (!minRating) {
      resetCards();
      return;
    }
    serviceCols.forEach(col => {
      const ratingText = col.querySelector(".card-rating")?.textContent;
      const rating = parseFloat(ratingText?.match(/([0-9]\.\d)/)?.[0] || 0);
      col.style.display = rating >= minRating ? "block" : "none";
    });
  }

  // --- BOTÓN SALIR DE CATEGORÍA ---
  if (exitBtn) {
    exitBtn.addEventListener("click", () => {
      searchInput.value = ""; // limpiar buscador
      resetCards();           // restaurar todas las tarjetas
    });
  }
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









