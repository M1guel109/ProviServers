/* registro-usuario.js — Lógica del formulario de registro */

document.addEventListener("DOMContentLoaded", function () {

    // =======================================================
    // 1. INICIALIZAR TOOLTIPS (Bootstrap 5)
    // =======================================================
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });

    // =======================================================
    // 2. PREVISUALIZACIÓN DE FOTO
    // =======================================================
    const fotoInput   = document.getElementById("foto-input");
    const fotoPreview = document.getElementById("foto-preview");

    if (fotoInput && fotoPreview) {
        fotoInput.addEventListener("change", function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (ev) => fotoPreview.src = ev.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

    // =======================================================
    // 3. LÓGICA DE ROL Y VISIBILIDAD DE CAMPOS
    // =======================================================
    const rolSelect            = document.getElementById("rol");
    const contenedorProveedor  = document.getElementById("campos-proveedor");
    const inputsDocumentos     = document.querySelectorAll('input[type="file"][name^="doc-"]');

    function toggleProveedor() {
        if (!contenedorProveedor) return;

        if (rolSelect.value === "proveedor") {
            contenedorProveedor.classList.remove("d-none");
            contenedorProveedor.classList.add("animate-fade-in");

            // Hacer obligatorios todos menos el certificado (opcional)
            inputsDocumentos.forEach(input => {
                if (input.name !== "doc-certificado") {
                    input.required = true;
                }
            });
        } else {
            contenedorProveedor.classList.add("d-none");

            // Quitar obligatoriedad para clientes y admins
            inputsDocumentos.forEach(input => input.required = false);
        }
    }

    if (rolSelect) {
        rolSelect.addEventListener("change", toggleProveedor);
        toggleProveedor(); // Ejecutar al cargar por si hay valor pre-seleccionado
    }

    // =======================================================
    // 4. GESTIÓN DE CATEGORÍAS (TAGS)
    // =======================================================
    const selectCat       = document.getElementById("select-categoria");
    const inputNuevaCat   = document.getElementById("input-nueva-categoria");
    const divNuevaCat     = document.getElementById("input-nueva-cat-container");
    const btnAddCat       = document.getElementById("btn-add-categoria");
    const contenedorTags  = document.getElementById("contenedor-tags");
    const inputHiddenTags = document.getElementById("lista_categorias");

    let categoriasSeleccionadas = [];

    if (selectCat && btnAddCat) {

        // A. Mostrar/ocultar input de nueva categoría
        selectCat.addEventListener("change", function () {
            if (this.value === "nueva") {
                divNuevaCat.classList.remove("d-none");
                inputNuevaCat.focus();
            } else {
                divNuevaCat.classList.add("d-none");
                inputNuevaCat.value = "";
            }
        });

        // B. Botón Agregar
        btnAddCat.addEventListener("click", function () {
            let valor = (selectCat.value === "nueva")
                ? inputNuevaCat.value.trim()
                : selectCat.value.trim();

            // Validaciones
            if (!valor || valor === "nueva") return;

            // Validar duplicado (sin distinción mayúsculas)
            const existe = categoriasSeleccionadas.some(
                cat => cat.toLowerCase() === valor.toLowerCase()
            );

            if (existe) {
                Swal.fire({
                    icon: "warning",
                    title: "Categoría duplicada",
                    text: `"${valor}" ya está en la lista.`,
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                });
                return;
            }

            // Validar máximo 5
            if (categoriasSeleccionadas.length >= 5) {
                Swal.fire({
                    icon: "warning",
                    title: "Límite alcanzado",
                    text: "Solo puedes asignar un máximo de 5 categorías.",
                    confirmButtonColor: "#0066FF",
                });
                return;
            }

            // Agregar y renderizar
            categoriasSeleccionadas.push(valor);
            renderizarTags();

            // Resetear campos
            if (selectCat.value === "nueva") {
                inputNuevaCat.value = "";
                inputNuevaCat.focus();
            } else {
                selectCat.value = "";
            }
        });

        // ✅ CORREGIDO: delegación de eventos en el contenedor
        // Evita XSS con comillas simples en nombres de categorías
        contenedorTags.addEventListener("click", function (e) {
            const icono = e.target.closest("[data-cat]");
            if (icono) {
                const nombre = icono.dataset.cat;
                categoriasSeleccionadas = categoriasSeleccionadas.filter(
                    cat => cat !== nombre
                );
                renderizarTags();
            }
        });
    }

    function renderizarTags() {
        contenedorTags.innerHTML = "";

        categoriasSeleccionadas.forEach(cat => {
            const tag = document.createElement("div");
            tag.className = "badge-categoria";

            // ✅ CORREGIDO: data-attribute en lugar de onclick con comilla simple
            // Así funciona aunque el nombre tenga comillas o caracteres especiales
            tag.innerHTML = `
                ${cat}
                <i class="bi bi-x-circle-fill" data-cat="${cat.replace(/"/g, '&quot;')}" style="cursor:pointer;"></i>
            `;
            contenedorTags.appendChild(tag);
        });

        // Actualizar el input oculto que recibe PHP
        if (inputHiddenTags) {
            inputHiddenTags.value = categoriasSeleccionadas.join(",");
        }
    }

    // =======================================================
    // 5. VALIDACIÓN FINAL AL ENVIAR
    // =======================================================
    const formulario = document.getElementById("formRegistro");

    if (formulario) {
        formulario.addEventListener("submit", function (e) {
            if (rolSelect.value === "proveedor") {

                // Mínimo 1 categoría
                if (categoriasSeleccionadas.length < 1) {
                    e.preventDefault();
                    Swal.fire({
                        icon: "warning",
                        title: "Categoría requerida",
                        text: "Debes asignar al menos 1 categoría al proveedor.",
                        confirmButtonColor: "#0066FF",
                    });
                    return false;
                }

                // Máximo 5 categorías (doble validación — la primera está en el botón)
                if (categoriasSeleccionadas.length > 5) {
                    e.preventDefault();
                    Swal.fire({
                        icon: "warning",
                        title: "Límite excedido",
                        text: "Solo puedes asignar un máximo de 5 categorías.",
                        confirmButtonColor: "#0066FF",
                    });
                    return false;
                }
            }
        });
    }

});