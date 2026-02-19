/* editarUsuario.js - Versión Final con AJAX Real */

document.addEventListener('DOMContentLoaded', function () {
    console.log("Script editarUsuario.js cargado.");

    // =======================================================
    // 1. PREVISUALIZACIÓN DE FOTO
    // =======================================================
    const fotoInput = document.getElementById('foto-input');
    const fotoPreview = document.getElementById('foto-preview');

    if (fotoInput && fotoPreview) {
        fotoInput.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) { fotoPreview.src = e.target.result; };
                reader.readAsDataURL(file);
            }
        });
    }

    // =======================================================
    // 2. SISTEMA DE CATEGORÍAS (Tags)
    // =======================================================
    const inputHidden = document.getElementById('lista_categorias');
    const selectCat = document.getElementById('select-categoria');
    const divNuevaCat = document.getElementById('div-nueva-cat');
    const inputNuevaCat = document.getElementById('input-nueva-cat');
    const btnAdd = document.getElementById('btn-add-categoria');
    const contenedorTags = document.getElementById('contenedor-tags');

    // Variable global para usarla en eliminarCategoria
    window.categorias = [];

    // A. Inicializar datos existentes
    if (inputHidden && inputHidden.value) {
        const valores = inputHidden.value.split(',').filter(c => c.trim() !== '');
        window.categorias = valores;
        renderizarTags();
    }

    // B. Detectar si elige "Nueva"
    if (selectCat) {
        selectCat.addEventListener('change', function() {
            if (this.value === 'nueva') {
                divNuevaCat.classList.remove('d-none');
                inputNuevaCat.focus();
            } else {
                divNuevaCat.classList.add('d-none');
                inputNuevaCat.value = '';
            }
        });
    }

    // C. Agregar Categoría
    if (btnAdd) {
        btnAdd.addEventListener('click', function() {
            let valor = '';
            if (selectCat.value === 'nueva') {
                valor = inputNuevaCat.value.trim();
            } else {
                valor = selectCat.value;
            }

            if (!valor) return;

            // Validación duplicados
            if (window.categorias.some(c => c.toLowerCase() === valor.toLowerCase())) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Repetida',
                    text: 'Esta categoría ya está agregada.',
                    toast: true, position: 'top-end', timer: 2000, showConfirmButton: false
                });
                return;
            }

            // Agregar y Renderizar
            window.categorias.push(valor);
            renderizarTags();

            // Resetear UI
            if (selectCat.value === 'nueva') {
                inputNuevaCat.value = '';
                divNuevaCat.classList.add('d-none');
            }
            selectCat.value = '';
        });
    }

    // D. Función Renderizar
    function renderizarTags() {
        if(!contenedorTags) return;
        contenedorTags.innerHTML = '';
        
        if(window.categorias.length === 0) {
            contenedorTags.innerHTML = '<span class="text-muted small">Sin categorías asignadas.</span>';
        }

        window.categorias.forEach(cat => {
            const tag = document.createElement('div');
            tag.className = 'badge bg-light text-primary border border-primary p-2 d-flex align-items-center gap-2';
            tag.innerHTML = `
                ${cat}
                <i class="bi bi-x-circle-fill text-danger" style="cursor:pointer" onclick="eliminarCategoria('${cat}')"></i>
            `;
            contenedorTags.appendChild(tag);
        });

        if(inputHidden) inputHidden.value = window.categorias.join(',');
    }

    // E. Función Eliminar (Global)
    window.eliminarCategoria = function(nombre) {
        window.categorias = window.categorias.filter(c => c !== nombre);
        renderizarTags();
    };


    // =======================================================
    // 3. LÓGICA DE CAMBIO DE ROL
    // =======================================================
    const rolSelect = document.getElementById('rol');
    const camposProveedor = document.getElementById('campos-proveedor');

    if (rolSelect && camposProveedor) {
        function toggleCamposProveedor() {
            if (rolSelect.value === 'proveedor') {
                camposProveedor.classList.remove('d-none');
                setTimeout(() => camposProveedor.classList.add('fade-in'), 10);
            } else {
                camposProveedor.classList.add('d-none');
                camposProveedor.classList.remove('fade-in');
            }
        }
        rolSelect.addEventListener('change', toggleCamposProveedor);
        toggleCamposProveedor(); // Ejecutar al inicio
    }
});


// =======================================================
// 4. CAMBIAR ESTADO DOCUMENTO (AJAX REAL)
// =======================================================
window.cambiarEstadoDoc = async function(idDoc, nuevoEstado, btnElement) {
    
    // Obtener contenedor de botones para poner spinner
    // Si se pasa 'this' desde el HTML, usamos ese elemento, si no, buscamos el evento
    const btn = btnElement || event.target.closest('button');
    const btnGroup = btn.closest('.btn-group');
    const originalHtml = btnGroup.innerHTML;

    // Feedback visual (Cargando...)
    btnGroup.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"></div>';

    try {
        const formData = new FormData();
        formData.append('accion', 'cambiar_estado_documento');
        formData.append('id_doc', idDoc);
        formData.append('nuevo_estado', nuevoEstado);

        // Usamos la variable global BASE_URL definida en la vista PHP
        const url = `${BASE_URL}/app/controllers/adminController.php`;

        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const res = await response.json();

        if (res.success) {
            
            // 1. Actualizar visualmente la fila (Badge y Botones)
            const tr = btnGroup.closest('tr');
            const celdaEstado = tr.querySelector('td:nth-child(2)'); // Columna Estado

            if (nuevoEstado === 'aprobado') {
                celdaEstado.innerHTML = '<span class="badge bg-success">aprobado</span>';
                // Dejar solo botón rechazar
                btnGroup.innerHTML = `<button type="button" class="btn btn-outline-danger" onclick="cambiarEstadoDoc(${idDoc}, 'rechazado', this)" title="Rechazar"><i class="bi bi-x-lg"></i></button>`;
            } else {
                celdaEstado.innerHTML = '<span class="badge bg-danger">rechazado</span>';
                // Dejar botón aprobar
                btnGroup.innerHTML = `<button type="button" class="btn btn-outline-success" onclick="cambiarEstadoDoc(${idDoc}, 'aprobado', this)" title="Aprobar"><i class="bi bi-check-lg"></i></button>`;
            }

            // 2. Alerta Bonita
            Swal.fire({
                icon: 'success',
                title: 'Actualizado',
                text: `Documento marcado como ${nuevoEstado}`,
                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000
            });

        } else {
            Swal.fire('Error', res.message || 'Error al actualizar', 'error');
            btnGroup.innerHTML = originalHtml; // Restaurar si falla
        }

    } catch (error) {
        console.error(error);
        Swal.fire('Error', 'Fallo de conexión', 'error');
        btnGroup.innerHTML = originalHtml;
    }
};