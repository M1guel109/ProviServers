document.addEventListener("DOMContentLoaded", () => {
  /* =========================
     Sidebar: active por URL
  ========================= */
  try {
    const current = window.location.href;
    const links = document.querySelectorAll(".sidebar a");

    links.forEach(link => link.classList.remove("active"));

    links.forEach(link => {
      const href = link.href;
      if (href && current.includes(href)) {
        link.classList.add("active");
      }
    });
  } catch (e) {
    console.error("Sidebar active error:", e);
  }

  /* =========================
     Sidebar toggle (móvil/escritorio)
  ========================= */
  try {
    const btnToggleMenu = document.getElementById("btn-toggle-menu");
    const sidebar = document.querySelector(".sidebar");

    if (btnToggleMenu && sidebar) {
      const mqMobile = window.matchMedia("(max-width: 767px)");

      function toggleSidebar() {
        if (mqMobile.matches) {
          sidebar.classList.toggle("sidebar-open");
        } else {
          sidebar.classList.toggle("plegado");
        }
      }

      btnToggleMenu.addEventListener("click", toggleSidebar);

      mqMobile.addEventListener("change", () => {
        sidebar.classList.remove("sidebar-open");
      });
    }
  } catch (e) {
    console.error("Sidebar toggle error:", e);
  }

  /* =========================
     FormNecesidad: validar sin bloquear POST real
     - Solo evita submit si es inválido
     - Si es válido, NO preventDefault => envía al backend
  ========================= */
  try {
    const form = document.getElementById("formNecesidad");
    if (form) {
      form.addEventListener("submit", (event) => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
          form.classList.add("was-validated");
          alert("Por favor completa todos los campos obligatorios antes de enviar.");
        }
        // ✅ Si es válido, el navegador envía el POST normal
      });
    }
  } catch (e) {
    console.error("FormNecesidad error:", e);
  }

  /* =========================
     Explorar servicios: buscador + filtros
  ========================= */
  try {
    const searchInput = document.querySelector("form.d-flex input.form-control");
    const serviceCols = document.querySelectorAll("#contenedor-servicios .col-md-4");
    const categoryButtons = document.querySelectorAll(".category-filters button");
    const exitBtn = document.getElementById("exitCategory");

    function normalizeText(text) {
      return (text || "")
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "");
    }

    function resetCards() {
      serviceCols.forEach(col => {
        col.style.display = "block";
      });
    }

    if (searchInput && serviceCols.length) {
      let timeout;
      searchInput.addEventListener("input", () => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
          const query = normalizeText(searchInput.value);

          if (query === "") {
            resetCards();
            return;
          }

          serviceCols.forEach(col => {
            const titleEl = col.querySelector(".card-title");
            const providerEl = col.querySelector(".card-subtitle");
            const title = normalizeText(titleEl ? titleEl.textContent : "");
            const provider = normalizeText(providerEl ? providerEl.textContent : "");

            col.style.display = (title.includes(query) || provider.includes(query)) ? "block" : "none";
          });
        }, 300);
      });
    }

    if (categoryButtons.length && serviceCols.length) {
      categoryButtons.forEach(button => {
        button.addEventListener("click", () => {
          const category = normalizeText(button.textContent.trim());
          if (category === "" || category === "todos") {
            resetCards();
            return;
          }

          serviceCols.forEach(col => {
            const descEl = col.querySelector(".card-text");
            const titleEl = col.querySelector(".card-title");
            const description = normalizeText(descEl ? descEl.textContent : "");
            const title = normalizeText(titleEl ? titleEl.textContent : "");

            col.style.display = (description.includes(category) || title.includes(category)) ? "block" : "none";
          });
        });
      });
    }

    if (exitBtn && serviceCols.length) {
      exitBtn.addEventListener("click", () => {
        if (searchInput) searchInput.value = "";
        resetCards();
      });
    }
  } catch (e) {
    console.error("Explorar servicios error:", e);
  }

  /* =========================
     Buscador simple (si existe)
  ========================= */
  try {
    const buscadorServicios = document.getElementById("buscadorServicios");
    if (buscadorServicios) {
      buscadorServicios.addEventListener("input", function () {
        const query = (this.value || "").toLowerCase();
        document.querySelectorAll(".service-card").forEach(card => {
          const text = (card.innerText || "").toLowerCase();
          const wrapper = card.parentElement;
          if (wrapper) wrapper.style.display = text.includes(query) ? "" : "none";
        });
      });
    }
  } catch (e) {
    console.error("BuscadorServicios error:", e);
  }

  /* =========================
     Animación barras de progreso (si existen)
  ========================= */
  try {
    const barras = document.querySelectorAll(".progress-bar[data-progreso]");
    if (barras.length) {
      barras.forEach(barra => {
        const porcentajeFinal = parseInt(barra.getAttribute("data-progreso"), 10);
        if (Number.isNaN(porcentajeFinal)) return;

        let progreso = 0;
        const intervalo = setInterval(() => {
          if (progreso >= porcentajeFinal) {
            clearInterval(intervalo);
          } else {
            progreso++;
            barra.style.width = progreso + "%";
            barra.setAttribute("aria-valuenow", String(progreso));
            barra.textContent = progreso + "%";
          }
        }, 30);
      });
    }
  } catch (e) {
    console.error("Progress bar error:", e);
  }

  /* =========================
     Favoritos (demo): quitar card
  ========================= */
  try {
    document.querySelectorAll(".btn-fav").forEach(btn => {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        const card = btn.closest(".col-lg-4, .col-md-6");
        if (!card) return;
        card.classList.add("fade-out");
        setTimeout(() => card.remove(), 400);
      });
    });
  } catch (e) {
    console.error("Favoritos demo error:", e);
  }

  /* =========================
     Historial (demo) botones
  ========================= */
  try {
    const historial = document.getElementById("historial-servicios");
    if (historial) {
      historial.querySelectorAll(".btn-primary").forEach(btn => {
        btn.addEventListener("click", () => {
          alert("Función de contratar de nuevo en construcción...");
        });
      });

      historial.querySelectorAll(".btn-outline-primary").forEach(btn => {
        btn.addEventListener("click", () => {
          alert("Función de ver detalles en construcción...");
        });
      });
    }
  } catch (e) {
    console.error("Historial demo error:", e);
  }

  /* =========================
     Perfil (demo)
  ========================= */
  try {
    const changePhotoBtn = document.querySelector(".profile-card .btn-outline-primary");
    if (changePhotoBtn) {
      changePhotoBtn.addEventListener("click", () => {
        alert("Función de cambiar foto en construcción...");
      });
    }

    const editForm = document.querySelector("#profile-edit form");
    if (editForm) {
      editForm.addEventListener("submit", (e) => {
        e.preventDefault();
        alert("Cambios de perfil guardados (demo). Aquí conectarías con tu backend.");
      });
    }

    const settingsForm = document.querySelector("#profile-settings form");
    if (settingsForm) {
      settingsForm.addEventListener("submit", (e) => {
        e.preventDefault();
        alert("Configuración guardada (demo).");
      });
    }

    const passwordForm = document.querySelector("#profile-change-password form");
    if (passwordForm) {
      passwordForm.addEventListener("submit", (e) => {
        e.preventDefault();
        alert("Contraseña actualizada (demo).");
      });
    }
  } catch (e) {
    console.error("Perfil demo error:", e);
  }
});
