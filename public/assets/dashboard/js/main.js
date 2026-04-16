/* ==========================================================================
   main.js — ProviServers | Lógica general
   ========================================================================== */

const PROJECT_URL =
  typeof BASE_URL !== "undefined" ? BASE_URL : "/proviservers";

document.addEventListener("DOMContentLoaded", () => {
  // =====================================================================
  // REFERENCIAS GLOBALES
  // =====================================================================
  const body = document.body;
  const sidebar = document.querySelector(".sidebar");
  const btnHamburguesa = document.getElementById("btn-toggle-menu");
  const btnToggleLogo = document.getElementById("btnToggleSidebar");
  const modeToggle = document.getElementById("modeToggle");
  const modeText = modeToggle?.querySelector(".mode-text");
  const closeMovil = document.getElementById("closeMenuMobile");

  // =====================================================================
  // 1. SIDEBAR — Colapsar / Expandir (hamburguesa + botón logo)
  // =====================================================================
  function esMovil() {
    return window.innerWidth <= 1024;
  }

  function toggleSidebar() {
    body.classList.toggle("toggle-sidebar");
    const collapsed = body.classList.contains("toggle-sidebar");
    localStorage.setItem("sidebar-collapsed", String(collapsed));

    // Al colapsar, cerrar todos los submenús abiertos
    if (collapsed) {
      cerrarTodosSubmenu();
    }
  }

  // Restaurar preferencia guardada (solo desktop)
  if (!esMovil() && localStorage.getItem("sidebar-collapsed") === "true") {
    body.classList.add("toggle-sidebar");
  }

  btnHamburguesa?.addEventListener("click", toggleSidebar);
  btnToggleLogo?.addEventListener("click", toggleSidebar);

  // =====================================================================
  // 2. SIDEBAR MÓVIL — Cerrar con X, overlay y ESC
  // =====================================================================
  closeMovil?.addEventListener("click", () => {
    body.classList.remove("toggle-sidebar");
  });

  document.addEventListener("click", (e) => {
    if (!esMovil()) return;
    if (!body.classList.contains("toggle-sidebar")) return;
    if (!sidebar?.contains(e.target) && !btnHamburguesa?.contains(e.target)) {
      body.classList.remove("toggle-sidebar");
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") body.classList.remove("toggle-sidebar");
  });

  window.addEventListener("resize", () => {
    if (!esMovil()) body.classList.remove("toggle-sidebar");
  });

  // Cerrar sidebar al navegar en móvil
  document.querySelectorAll(".sidebar a").forEach((link) => {
    link.addEventListener("click", () => {
      if (esMovil()) {
        setTimeout(() => body.classList.remove("toggle-sidebar"), 100);
      }
    });
  });

  // =====================================================================
  // 3. SUBMENÚS — Acordeón (abre uno, cierra los demás)
  // =====================================================================
  function cerrarTodosSubmenu() {
    document
      .querySelectorAll(".has-submenu.open")
      .forEach((el) => el.classList.remove("open"));
  }

  function toggleSubmenu(li) {
    if (body.classList.contains("toggle-sidebar") && !esMovil()) return; // en colapsado desktop, el hover CSS lo maneja
    const estaAbierto = li.classList.contains("open");
    cerrarTodosSubmenu(); // acordeón: cierra todos
    if (!estaAbierto) li.classList.add("open");
  }

  // Click en botón flecha (chevron)
  document.querySelectorAll(".has-submenu .toggle-submenu").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      toggleSubmenu(btn.closest(".has-submenu"));
    });
  });

  // Click en el texto/link del ítem padre
  document.querySelectorAll(".has-submenu > .menu-link").forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      toggleSubmenu(link.closest(".has-submenu"));
    });
  });

  // =====================================================================
  // 4. SUBMENÚ FLOTANTE (colapsado desktop) — Al hacer clic en un item:
  //    expande el sidebar y deja el submenú padre abierto
  // =====================================================================
  document.querySelectorAll(".submenu-link").forEach((link) => {
    link.addEventListener("click", () => {
      // Marcar activo
      document
        .querySelectorAll(".submenu-link")
        .forEach((a) => a.classList.remove("active"));
      link.classList.add("active");

      const parentHasSub = link.closest(".has-submenu");

      if (body.classList.contains("toggle-sidebar") && !esMovil()) {
        // Expandir sidebar
        body.classList.remove("toggle-sidebar");
        localStorage.setItem("sidebar-collapsed", "false");

        // Abrir submenú padre con pequeño delay para que la transición se vea bien
        setTimeout(() => {
          cerrarTodosSubmenu();
          if (parentHasSub) parentHasSub.classList.add("open");
        }, 50);
      }
    });
  });

  // =====================================================================
  // 5. DARK MODE — con memoria localStorage
  // =====================================================================
  if (localStorage.getItem("dark-mode") === "true") {
    body.classList.add("dark-mode");
    if (modeText) modeText.textContent = "Modo claro";
  }

  modeToggle?.addEventListener("click", () => {
    body.classList.toggle("dark-mode");
    const isDark = body.classList.contains("dark-mode");
    localStorage.setItem("dark-mode", String(isDark));
    if (modeText) modeText.textContent = isDark ? "Modo claro" : "Modo oscuro";
  });

  // =====================================================================
  // 6. MARCAR ENLACE ACTIVO SEGÚN LA URL ACTUAL
  // =====================================================================
  function setActiveLink() {
    const currentUrl = window.location.href;

    document.querySelectorAll(".sidebar a").forEach((link) => {
      link.classList.remove("active");
      const href = link.getAttribute("href");
      if (href && href !== "#" && currentUrl.includes(href)) {
        link.classList.add("active");

        // Si está dentro de un submenú, abrir el padre
        const submenu = link.closest(".submenu");
        if (submenu) {
          const parentHasSub = submenu.closest(".has-submenu");
          if (parentHasSub) {
            parentHasSub.classList.add("open");
            parentHasSub.classList.add("active");
          }
        }
      }
    });
  }

  setActiveLink();

  // =====================================================================
  // 9. TOOLTIPS EN MODO COLAPSADO — Pegados al body (sin recorte)
  // =====================================================================
  function initTooltipsSidebar() {
    const tooltip = document.createElement("div");
    tooltip.className = "sidebar-tooltip";
    document.body.appendChild(tooltip);

    let tooltipTimer = null;

    function mostrarTooltip(el, texto) {
      clearTimeout(tooltipTimer);
      tooltip.textContent = texto;
      tooltip.classList.add("visible");
      posicionarTooltip(el);
    }

    function ocultarTooltip() {
      tooltip.classList.remove("visible");
    }

    function posicionarTooltip(el) {
      const rect = el.getBoundingClientRect();
      tooltip.style.top =
        rect.top + rect.height / 2 - tooltip.offsetHeight / 2 + "px";
      tooltip.style.left = rect.right + 12 + "px";
    }

    function bindTooltip(el) {
      const titulo = el.getAttribute("data-title");
      if (!titulo) return;

      el.addEventListener("mouseenter", () => {
        if (!body.classList.contains("toggle-sidebar") || esMovil()) return;
        mostrarTooltip(el, titulo);
      });

      el.addEventListener("mouseleave", () => ocultarTooltip());
      el.addEventListener("click", () => ocultarTooltip());
    }

    // Aplicar a todos los elementos con data-title
    document.querySelectorAll(".sidebar [data-title]").forEach(bindTooltip);

    // En modo colapsado, click en menu-link de has-submenu → navega al primer hijo
    document.querySelectorAll(".has-submenu > .menu-link").forEach((link) => {
      link.addEventListener("click", (e) => {
        if (!body.classList.contains("toggle-sidebar") || esMovil()) return;
        e.preventDefault();
        e.stopPropagation();
        const primerHijo = link
          .closest(".has-submenu")
          ?.querySelector(".submenu-link");
        if (primerHijo) {
          window.location.href = primerHijo.getAttribute("href");
        }
      });
    });
  }

  initTooltipsSidebar();

  // =====================================================================
  // 7. BREADCRUMBS — Generación automática desde URL
  // =====================================================================
  const breadcrumb = document.getElementById("breadcrumb");
  if (breadcrumb) {
    breadcrumb.innerHTML = "";

    const path = window.location.pathname
      .split("/")
      .filter((s) => s !== "" && s !== "proviservers");

    const homeItem = document.createElement("li");
    homeItem.className = "breadcrumb-item";
    const homeLink = document.createElement("a");
    homeLink.href = PROJECT_URL + "/admin/dashboard";
    homeLink.innerHTML = '<i class="bi bi-house-door-fill"></i>';
    homeItem.appendChild(homeLink);
    breadcrumb.appendChild(homeItem);

    let rutaAcumulada = PROJECT_URL;

    path.forEach((segmento, index) => {
      rutaAcumulada += `/${segmento}`;
      if (["admin", "dashboard"].includes(segmento) || !isNaN(segmento)) return;

      const item = document.createElement("li");
      item.classList.add("breadcrumb-item");

      const texto = decodeURIComponent(segmento)
        .replace(/-/g, " ")
        .replace(/\b\w/g, (l) => l.toUpperCase());

      const esUltimo = index === path.length - 1;

      if (!esUltimo) {
        const link = document.createElement("a");
        link.href = rutaAcumulada;
        link.textContent = texto;
        item.appendChild(link);
      } else {
        item.textContent = texto;
        item.classList.add("active");
        item.setAttribute("aria-current", "page");
      }

      breadcrumb.appendChild(item);
    });
  }

  // =====================================================================
  // 8. PREVISUALIZACIÓN DE IMAGEN (formularios)
  // =====================================================================
  const fotoInput = document.getElementById("foto-input");
  const fotoPreview = document.getElementById("foto-preview");

  if (fotoInput && fotoPreview) {
    const defaultImage = fotoPreview.src;
    fotoInput.addEventListener("change", (e) => {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (ev) => {
          fotoPreview.src = ev.target.result;
        };
        reader.readAsDataURL(file);
      } else {
        fotoPreview.src = defaultImage;
      }
    });
  }
});

// =====================================================================
// SWEET ALERT — Helper global del proyecto
// =====================================================================
function sweetProyecto(icon, title, text, callback = null) {
  Swal.fire({
    icon,
    title,
    text,
    confirmButtonText: "Aceptar",
    confirmButtonColor: "#0066ff",
    background: "#fff",
    color: "#0e1116",
  }).then(() => {
    if (typeof callback === "function") callback();
  });
}

// ==========================================
// BUSCADOR GLOBAL (Funciona en todo el dashboard)
// ==========================================

// Inicializar buscador cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", function () {
  initGlobalSearch();
});

function initGlobalSearch() {
  const searchInput = document.querySelector(".buscador input");
  if (!searchInput) return;

  let searchTimeout;

  // Escuchar eventos de entrada
  searchInput.addEventListener("input", function (e) {
    const term = e.target.value.trim();

    // Limpiar timeout anterior
    clearTimeout(searchTimeout);

    if (term.length < 2) {
      hideSearchResults();
      return;
    }

    // Debounce para evitar muchas peticiones
    searchTimeout = setTimeout(() => {
      performSearch(term);
    }, 500);
  });

  // Atajo Ctrl+K para enfocar el buscador
  document.addEventListener("keydown", (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === "k") {
      e.preventDefault();
      searchInput.focus();
      searchInput.select();
    }
  });

  // Cerrar resultados al hacer clic fuera
  document.addEventListener("click", function (e) {
    if (
      !e.target.closest(".buscador") &&
      !e.target.closest(".search-results")
    ) {
      hideSearchResults();
    }
  });
}

function performSearch(term) {
  // Obtener la sección actual (admin, proveedor, cliente)
  const currentPath = window.location.pathname;
  let module = "general";

  if (currentPath.includes("/admin/")) module = "admin";
  else if (currentPath.includes("/proveedor/")) module = "proveedor";
  else if (currentPath.includes("/cliente/")) module = "cliente";

  // Mostrar loading
  showSearchLoading();

  // Realizar petición AJAX
  fetch(`${BASE_URL}/api/buscar?q=${encodeURIComponent(term)}&modulo=${module}`)
    .then((response) => response.json())
    .then((data) => {
      displaySearchResults(data, term);
    })
    .catch((error) => {
      console.error("Error en búsqueda:", error);
      showSearchError();
    });
}

function showSearchLoading() {
  removeExistingResults();

  const resultsDiv = document.createElement("div");
  resultsDiv.className = "search-results loading";
  resultsDiv.innerHTML = `
        <div class="search-loading">
            <div class="spinner-border spinner-border-sm text-primary me-2"></div>
            Buscando...
        </div>
    `;

  const buscador = document.querySelector(".buscador");
  buscador.parentElement.style.position = "relative";
  buscador.insertAdjacentElement("afterend", resultsDiv);
}

function displaySearchResults(data, term) {
  removeExistingResults();

  if (!data.results || data.results.length === 0) {
    showNoResults(term);
    return;
  }

  const resultsDiv = document.createElement("div");
  resultsDiv.className = "search-results";

  // Agrupar resultados por categoría
  const grouped = {};
  data.results.forEach((item) => {
    if (!grouped[item.type]) grouped[item.type] = [];
    grouped[item.type].push(item);
  });

  let html = `<div class="search-header">Resultados para "${term}"</div>`;

  // Servicios
  if (grouped.servicios) {
    html += `<div class="search-category">
                    <div class="search-category-title">
                        <i class="bi bi-briefcase"></i> Servicios (${grouped.servicios.length})
                    </div>`;
    grouped.servicios.forEach((item) => {
      html += `<a href="${item.url}" class="search-item">
                        <i class="bi bi-briefcase"></i>
                        <div>
                            <strong>${highlightText(item.title, term)}</strong>
                            <small>${item.subtitle || ""}</small>
                        </div>
                    </a>`;
    });
    html += `</div>`;
  }

  // Usuarios
  if (grouped.usuarios) {
    html += `<div class="search-category">
                    <div class="search-category-title">
                        <i class="bi bi-people"></i> Usuarios (${grouped.usuarios.length})
                    </div>`;
    grouped.usuarios.forEach((item) => {
      html += `<a href="${item.url}" class="search-item">
                        <i class="bi bi-person-circle"></i>
                        <div>
                            <strong>${highlightText(item.title, term)}</strong>
                            <small>${item.subtitle || ""}</small>
                        </div>
                    </a>`;
    });
    html += `</div>`;
  }

  // Publicaciones
  if (grouped.publicaciones) {
    html += `<div class="search-category">
                    <div class="search-category-title">
                        <i class="bi bi-file-text"></i> Publicaciones (${grouped.publicaciones.length})
                    </div>`;
    grouped.publicaciones.forEach((item) => {
      html += `<a href="${item.url}" class="search-item">
                        <i class="bi bi-file-text"></i>
                        <div>
                            <strong>${highlightText(item.title, term)}</strong>
                            <small>${item.subtitle || ""}</small>
                        </div>
                    </a>`;
    });
    html += `</div>`;
  }

  html += `<div class="search-footer">
                <small>Presiona Enter para ver todos los resultados</small>
            </div>`;

  resultsDiv.innerHTML = html;

  const buscador = document.querySelector(".buscador");
  buscador.insertAdjacentElement("afterend", resultsDiv);
}

function showNoResults(term) {
  const resultsDiv = document.createElement("div");
  resultsDiv.className = "search-results no-results";
  resultsDiv.innerHTML = `
        <div class="search-empty">
            <i class="bi bi-search fs-1 text-muted"></i>
            <p>No se encontraron resultados para "<strong>${escapeHtml(term)}</strong>"</p>
            <small>Intenta con otras palabras o revisa la ortografía</small>
        </div>
    `;

  const buscador = document.querySelector(".buscador");
  buscador.insertAdjacentElement("afterend", resultsDiv);
}

function showSearchError() {
  const resultsDiv = document.createElement("div");
  resultsDiv.className = "search-results error";
  resultsDiv.innerHTML = `
        <div class="search-error">
            <i class="bi bi-exclamation-triangle text-warning"></i>
            <p>Error al realizar la búsqueda</p>
            <small>Intenta nuevamente</small>
        </div>
    `;

  const buscador = document.querySelector(".buscador");
  buscador.insertAdjacentElement("afterend", resultsDiv);
}

function removeExistingResults() {
  const existing = document.querySelector(".search-results");
  if (existing) existing.remove();
}

function hideSearchResults() {
  removeExistingResults();
}

function highlightText(text, term) {
  if (!term) return text;
  const regex = new RegExp(`(${escapeRegex(term)})`, "gi");
  return text.replace(regex, "<mark>$1</mark>");
}

function escapeRegex(str) {
  return str.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

function escapeHtml(str) {
  return str.replace(/[&<>]/g, function (m) {
    if (m === "&") return "&amp;";
    if (m === "<") return "&lt;";
    if (m === ">") return "&gt;";
    return m;
  });
}
