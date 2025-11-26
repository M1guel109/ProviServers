document.addEventListener("DOMContentLoaded", function () {

  const pasos = document.querySelectorAll(".wizard-step");

  function mostrarPaso(idPaso) {
    pasos.forEach(paso => paso.classList.add("d-none"));
    document.getElementById(idPaso).classList.remove("d-none");
  }

  // =============================
  //   BOTÓN SIGUIENTE
  // =============================
  document.querySelectorAll(".btn-next").forEach(btn => {
    btn.addEventListener("click", function () {
      const next = this.dataset.next;

      const current = this.closest(".wizard-step");
      const inputs = current.querySelectorAll("input[required], select[required]");

      for (let i of inputs) {
        if (!i.value.trim()) {
          i.classList.add("is-invalid");
          return;
        } else {
          i.classList.remove("is-invalid");
        }
      }

      // =============================
      //  VALIDAR CONTRASEÑAS EN PASO 1
      // =============================
      if (current.id === "paso-1") {
        const passInput = document.getElementById("contrasena");
        const confirmInput = document.getElementById("confirmar");

        // Elemento donde se muestra el error (lo crearemos en el HTML)
        const errorFeedback = document.getElementById("feedback-confirmar");

        if (passInput.value !== confirmInput.value) {
          confirmInput.classList.add("is-invalid");
          // Mostrar mensaje de error
          if (errorFeedback) {
            errorFeedback.textContent = "⚠️ Las contraseñas no coinciden.";
          }
          return; // Detiene el flujo para que el usuario corrija
        } else {
          confirmInput.classList.remove("is-invalid");
          // Limpiar mensaje de error
          if (errorFeedback) {
            errorFeedback.textContent = "";
          }
        }
      }

      // Si el usuario es cliente, saltar paso 3
      if (next === "paso-3") {
        const rol = document.getElementById("rol").value;
        if (rol === "cliente") {
          generarResumen();
          mostrarPaso("paso-4");
          return;
        }
      }

      // Si va a paso 4, generar resumen
      if (next === "paso-4") {
        generarResumen();
      }

      mostrarPaso(next);
    });
  });


  // =============================
  //     BOTÓN ATRÁS
  // =============================
  document.querySelectorAll(".btn-prev").forEach(btn => {
    btn.addEventListener("click", function () {
      const prev = this.dataset.prev;

      const rol = document.getElementById("rol").value;

      // Si es cliente, regresar directo a paso 1
      if (prev === "paso-3" && rol === "cliente") {
        mostrarPaso("paso-1");
        return;
      }

      mostrarPaso(prev);
    });
  });

  // =============================
  //     MOSTRAR DOCUMENTOS
  // =============================
  document.getElementById("rol").addEventListener("change", function () {
    const docs = document.getElementById("docs-proveedor");
    docs.style.display = this.value === "proveedor" ? "block" : "none";
  });

  // =============================
  //  GENERAR RESUMEN EN PASO 4
  // =============================
  function generarResumen() {
    const resumen = document.getElementById("resumen-registro");

    let html = `
            <p><strong>Documento:</strong> ${document.getElementById("documento").value}</p>
            <p><strong>Email:</strong> ${document.getElementById("email").value}</p>
            <p><strong>Rol:</strong> ${document.getElementById("rol").value}</p>
            <hr>
            <p><strong>Nombres:</strong> ${document.getElementById("nombres").value}</p>
            <p><strong>Apellidos:</strong> ${document.getElementById("apellidos").value}</p>
            <p><strong>Teléfono:</strong> ${document.getElementById("telefono").value}</p>
            <p><strong>Ubicación:</strong> ${document.getElementById("ubicacion").value}</p>
        `;

    if (document.getElementById("rol").value === "proveedor") {
      html += `
                <hr>
                <p>Cédula: ${document.getElementById("doc-cedula").files.length ? "Sí" : "No"}</p>
                <p>Selfie: ${document.getElementById("doc-selfie").files.length ? "Sí" : "No"}</p>
                <p>Antecedentes: ${document.getElementById("doc-antecedentes").files.length ? "Sí" : "No"}</p>
                <p>Certificado: ${document.getElementById("doc-certificado").files.length ? "Sí" : "No"}</p>
            `;
    }

    resumen.innerHTML = html;
  }

  // =============================
  //           SUBMIT FINAL
  // =============================
  document.getElementById("registro-wizard").addEventListener("submit", function (e) {
    // e.preventDefault();

    // alert("Formulario listo para enviar al backend vía POST ✔");

    // // Aquí puedes hacer fetch() si ya tienes la ruta backend
  });

});
