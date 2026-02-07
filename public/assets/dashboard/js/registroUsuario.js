/* registroUsuario.js - Lógica del formulario de registro */

document.addEventListener('DOMContentLoaded', function () {
    
    // =======================================================
    // 1. INICIALIZAR TOOLTIPS (Bootstrap 5)
    // =======================================================
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // =======================================================
    // 2. PREVISUALIZACIÓN DE FOTO (Tu código original mejorado)
    // =======================================================
    const fotoInput = document.getElementById('foto-input');
    const fotoPreview = document.getElementById('foto-preview');

    if (fotoInput && fotoPreview) {
        fotoInput.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    fotoPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // =======================================================
    // 3. LÓGICA DE ROL Y VISIBILIDAD DE CAMPOS
    // =======================================================
    const rolSelect = document.getElementById('rol');
    const contenedorProveedor = document.getElementById('campos-proveedor'); // El div padre que contiene cat + docs
    
    // Seleccionamos los inputs de archivo que deben ser obligatorios
    // Nota: Asegúrate de que en el HTML los inputs tengan la clase 'file-doc-proveedor' o selecciona por ID
    const inputsDocumentos = document.querySelectorAll('input[type="file"][name^="doc-"]'); 

    function toggleProveedor() {
        if (!contenedorProveedor) return;

        if (rolSelect.value === 'proveedor') {
            // Mostrar sección
            contenedorProveedor.classList.remove('d-none');
            contenedorProveedor.classList.add('animate-fade-in');

            // Hacer obligatorios los documentos (menos el opcional)
            inputsDocumentos.forEach(input => {
                if (input.name !== 'doc-certificado') { // El certificado es opcional
                    input.required = true;
                }
            });

        } else {
            // Ocultar sección
            contenedorProveedor.classList.add('d-none');
            
            // Quitar obligatoriedad para que deje guardar si es cliente o admin
            inputsDocumentos.forEach(input => {
                input.required = false;
            });
        }
    }

    // Escuchar cambios y ejecutar al inicio
    if (rolSelect) {
        rolSelect.addEventListener('change', toggleProveedor);
        toggleProveedor(); // Ejecutar al cargar
    }

    // =======================================================
    // 4. GESTIÓN DE CATEGORÍAS (TAGS)
    // =======================================================
    const selectCat = document.getElementById('select-categoria');
    const inputNuevaCat = document.getElementById('input-nueva-categoria');
    const divNuevaCat = document.getElementById('input-nueva-cat-container');
    const btnAddCat = document.getElementById('btn-add-categoria'); // Asegúrate que tu botón tenga este ID
    const contenedorTags = document.getElementById('contenedor-tags');
    const inputHiddenTags = document.getElementById('lista_categorias');
    
    let categoriasSeleccionadas = []; // Array para guardar las categorías en memoria

    if (selectCat && btnAddCat) {
        
        // A. Detectar si elige "Crear nueva categoría"
        selectCat.addEventListener('change', function() {
            if (this.value === 'nueva') {
                divNuevaCat.classList.remove('d-none');
                inputNuevaCat.focus();
            } else {
                divNuevaCat.classList.add('d-none');
                inputNuevaCat.value = '';
            }
        });

        // B. Acción del botón "Agregar"
        btnAddCat.addEventListener('click', function() {
            let valor = '';

            // 1. Obtener el valor según el modo (select o input)
            if (selectCat.value === 'nueva') {
                valor = inputNuevaCat.value.trim();
            } else {
                valor = selectCat.value;
            }

            // 2. Validaciones básicas
            if (!valor) return; // Si está vacío, no hace nada
            if (valor === 'nueva') return; 

            // 3. Validación de duplicados (No distinguir mayúsculas/minúsculas)
            const existe = categoriasSeleccionadas.some(cat => cat.toLowerCase() === valor.toLowerCase());
            
            if (existe) {
                // Usamos SweetAlert2 si está disponible, sino alert normal
                if(typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Categoría duplicada',
                        text: `"${valor}" ya está en la lista.`,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    alert('Esa categoría ya está agregada');
                }
                return;
            }

            // 4. Agregar al array y renderizar
            categoriasSeleccionadas.push(valor);
            renderizarTags();

            // 5. Resetear campos para agregar otra
            if (selectCat.value === 'nueva') {
                inputNuevaCat.value = '';
                inputNuevaCat.focus(); // Mantener foco por si quiere agregar varias nuevas
            } else {
                selectCat.value = "";
            }
        });
    }

    // Función para dibujar las etiquetas en el HTML
    function renderizarTags() {
        contenedorTags.innerHTML = ''; // Limpiar contenedor

        categoriasSeleccionadas.forEach(cat => {
            const tag = document.createElement('div');
            tag.className = 'badge-categoria'; // Clase definida en el CSS
            tag.innerHTML = `
                ${cat}
                <i class="bi bi-x-circle-fill" onclick="eliminarCategoria('${cat}')"></i>
            `;
            contenedorTags.appendChild(tag);
        });

        // Actualizar el input oculto que se envía al PHP
        // Ejemplo de resultado: "Plomeria,Electricidad,Limpieza"
        if(inputHiddenTags) {
            inputHiddenTags.value = categoriasSeleccionadas.join(',');
        }
    }

    // Exponer la función de eliminar al ámbito global (window)
    // Esto es necesario porque el onclick está en el HTML generado dinámicamente
    window.eliminarCategoria = function(nombre) {
        categoriasSeleccionadas = categoriasSeleccionadas.filter(cat => cat !== nombre);
        renderizarTags();
    };

});