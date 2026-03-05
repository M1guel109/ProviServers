document.addEventListener("DOMContentLoaded", function () {
    const pasos = document.querySelectorAll(".wizard-step");

    function mostrarPaso(idPaso) {
        pasos.forEach(paso => paso.classList.add("d-none"));
        document.getElementById(idPaso).classList.remove("d-none");
    }

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

            if (current.id === "paso-1") {
                const passInput = document.getElementById("contrasena");
                const confirmInput = document.getElementById("confirmar");
                const errorFeedback = document.getElementById("feedback-confirmar");
                if (passInput.value !== confirmInput.value) {
                    confirmInput.classList.add("is-invalid");
                    if (errorFeedback) errorFeedback.textContent = "⚠️ Las contraseñas no coinciden.";
                    return;
                }
            }

            const rol = document.getElementById("rol").value;

            if (current.id === "paso-2" && rol === "cliente") {
                generarResumen();
                mostrarPaso("paso-5");
                return;
            }

            if (next === "paso-5") {
                generarResumen();
            }

            mostrarPaso(next);
        });
    });

    document.querySelectorAll(".btn-prev").forEach(btn => {
        btn.addEventListener("click", function () {
            const prev = this.dataset.prev;
            const current = this.closest(".wizard-step");
            const rol = document.getElementById("rol").value;
            if (current.id === "paso-5" && rol === "cliente") {
                mostrarPaso("paso-2");
                return;
            }
            mostrarPaso(prev);
        });
    });

    // LÓGICA DE HABILIDADES
    const selectCategoria = document.getElementById('select-categoria');
    const inputNuevaCatContainer = document.getElementById('input-nueva-cat-container');
    const inputNuevaCategoria = document.getElementById('input-nueva-categoria');
    const btnAddCategoria = document.getElementById('btn-add-categoria');
    const contenedorTags = document.getElementById('contenedor-tags');
    const inputListaCategorias = document.getElementById('lista_categorias');
    let habilidadesSeleccionadas = [];

    if (selectCategoria) {
        selectCategoria.addEventListener('change', function () {
            inputNuevaCatContainer.classList.toggle('d-none', this.value !== 'nueva');
            if (this.value === 'nueva') inputNuevaCategoria.focus();
        });
    }

    // Asegúrate de que esta parte en tu registro.js esté así:
    if (btnAddCategoria) {
        btnAddCategoria.addEventListener('click', function () {
            let nuevaHabilidad = "";

            if (selectCategoria.value === 'nueva') {
                nuevaHabilidad = inputNuevaCategoria.value.trim();
            } else {
                nuevaHabilidad = selectCategoria.value;
            }

            if (nuevaHabilidad && !habilidadesSeleccionadas.includes(nuevaHabilidad) && nuevaHabilidad !== "nueva") {
                habilidadesSeleccionadas.push(nuevaHabilidad);
                actualizarDOMHabilidades(); // Esta función ya la tienes y llena el input hidden

                // Resetear campos
                selectCategoria.value = "";
                inputNuevaCategoria.value = "";
                inputNuevaCatContainer.classList.add('d-none');
            }
        });
    }

    function actualizarDOMHabilidades() {
        contenedorTags.innerHTML = '';
        habilidadesSeleccionadas.forEach((habilidad, index) => {
            const tag = document.createElement('span');
            tag.className = 'badge bg-primary p-2 d-flex align-items-center gap-2';
            tag.innerHTML = `${habilidad} <i class="bi bi-x-circle" style="cursor: pointer;" data-index="${index}"></i>`;
            contenedorTags.appendChild(tag);
        });
        inputListaCategorias.value = JSON.stringify(habilidadesSeleccionadas);
        document.querySelectorAll('#contenedor-tags .bi-x-circle').forEach(btn => {
            btn.addEventListener('click', function () {
                habilidadesSeleccionadas.splice(this.getAttribute('data-index'), 1);
                actualizarDOMHabilidades();
            });
        });
    }

    function generarResumen() {
        const resumen = document.getElementById("resumen-registro");
        const rol = document.getElementById("rol").value;
        let html = `
            <p><strong>Documento:</strong> ${document.getElementById("documento").value}</p>
            <p><strong>Email:</strong> ${document.getElementById("email").value}</p>
            <p><strong>Rol:</strong> <span class="text-capitalize">${rol}</span></p>
            <hr>
            <p><strong>Nombres:</strong> ${document.getElementById("nombres").value}</p>
            <p><strong>Teléfono:</strong> ${document.getElementById("telefono").value}</p>
        `;

        if (rol === "proveedor") {
            const cedula = document.getElementById("doc-cedula")?.files.length ? "✅" : "❌";
            const selfie = document.getElementById("doc-selfie")?.files.length ? "✅" : "❌";
            const ant = document.getElementById("doc-antecedentes")?.files.length ? "✅" : "❌";
            html += `
                <hr><p><strong>Docs:</strong> Cédula ${cedula}, Selfie ${selfie}, Antecedentes ${ant}</p>
                <hr><p><strong>Habilidades:</strong> ${habilidadesSeleccionadas.length > 0 ? habilidadesSeleccionadas.join(', ') : '<span class="text-danger">Ninguna</span>'}</p>
            `;
        }
        resumen.innerHTML = html;
    }

    document.getElementById("registro-wizard").addEventListener("submit", function (e) {
        if (document.getElementById("rol").value === "proveedor" && habilidadesSeleccionadas.length === 0) {
            e.preventDefault();
            alert("Debes seleccionar al menos una habilidad.");
            mostrarPaso("paso-4");
        }
    });
});