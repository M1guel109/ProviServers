document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("formNecesidadModal");
  if (!form) return;

  const selCategoria = document.getElementById("categoria_nec");
  const wrapOtro = document.getElementById("categoriaOtroWrapper_nec");
  const inputOtro = document.getElementById("categoriaOtro_nec");

  function syncCategoriaOtros() {
    const esOtros = selCategoria && selCategoria.value === "Otros";
    if (wrapOtro) wrapOtro.classList.toggle("d-none", !esOtros);

    // OJO: si está oculto, NO debe ser required (si no, checkValidity falla)
    if (inputOtro) {
      inputOtro.required = !!esOtros;
      if (!esOtros) inputOtro.value = "";
    }
  }

  if (selCategoria) {
    selCategoria.addEventListener("change", syncCategoriaOtros);
    syncCategoriaOtros();
  }

  form.addEventListener("submit", (event) => {
    syncCategoriaOtros();

    if (!form.checkValidity()) {
      event.preventDefault();
      event.stopPropagation();
      form.classList.add("was-validated");
      alert("Por favor completa todos los campos obligatorios antes de enviar.");
      return;
    }

    // ✅ Si es válido, dejamos enviar al backend
    form.classList.add("was-validated");
  });

  // Opcional: al cerrar modal, limpia validación
  const modalEl = document.getElementById("modalNecesidad");
  if (modalEl) {
    modalEl.addEventListener("hidden.bs.modal", () => {
      form.reset();
      form.classList.remove("was-validated");
      syncCategoriaOtros();
    });
  }
});

// --- Toggle Sidebar ---
const btnToggle = document.getElementById("btn-toggle-menu");
const sidebar = document.querySelector(".sidebar");

if (btnToggle && sidebar) {
  // ✅ Al cargar la página, revisa si hay estado guardado
  const estadoGuardado = localStorage.getItem("sidebarEstado");
  if (estadoGuardado === "plegado") {
    sidebar.classList.add("plegado");
  }

  btnToggle.addEventListener("click", () => {
    sidebar.classList.toggle("plegado");

    // ✅ Guarda el estado actual en localStorage
    if (sidebar.classList.contains("plegado")) {
      localStorage.setItem("sidebarEstado", "plegado");
    } else {
      localStorage.setItem("sidebarEstado", "expandido");
    }
  });
}




