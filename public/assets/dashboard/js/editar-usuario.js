/* editar-usuario.js — Lógica del formulario de edición de usuario */

document.addEventListener('DOMContentLoaded', function () {

    // =======================================================
    // 1. PREVISUALIZACIÓN DE FOTO
    // =======================================================
    const fotoInput   = document.getElementById('foto-input');
    const fotoPreview = document.getElementById('foto-preview');

    if (fotoInput && fotoPreview) {
        fotoInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (ev) => fotoPreview.src = ev.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

    // =======================================================
    // 2. SISTEMA DE CATEGORÍAS (Tags)
    // =======================================================
    const inputHidden    = document.getElementById('lista_categorias');
    const selectCat      = document.getElementById('select-categoria');
    const divNuevaCat    = document.getElementById('div-nueva-cat');
    const inputNuevaCat  = document.getElementById('input-nueva-cat');
    const btnAdd         = document.getElementById('btn-add-categoria');
    const contenedorTags = document.getElementById('contenedor-tags');

    // Inicializar con categorías existentes del proveedor
    let categorias = [];
    if (inputHidden && inputHidden.value) {
        categorias = inputHidden.value
            .split(',')
            .map(c => c.trim())
            .filter(c => c !== '');
        renderizarTags();
    }

    // A. Mostrar/ocultar input de nueva categoría
    if (selectCat) {
        selectCat.addEventListener('change', function () {
            if (this.value === 'nueva') {
                divNuevaCat.classList.remove('d-none');
                inputNuevaCat.focus();
            } else {
                divNuevaCat.classList.add('d-none');
                inputNuevaCat.value = '';
            }
        });
    }

    // B. Botón Agregar
    if (btnAdd) {
        btnAdd.addEventListener('click', function () {
            const valor = (selectCat.value === 'nueva')
                ? inputNuevaCat.value.trim()
                : selectCat.value.trim();

            if (!valor || valor === 'nueva') return;

            // Validar duplicado
            if (categorias.some(c => c.toLowerCase() === valor.toLowerCase())) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Repetida',
                    text: 'Esta categoría ya está agregada.',
                    toast: true, position: 'top-end',
                    timer: 2000, showConfirmButton: false
                });
                return;
            }

            // Validar máximo 5
            if (categorias.length >= 5) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Límite alcanzado',
                    text: 'Solo puedes asignar un máximo de 5 categorías.',
                    confirmButtonColor: '#0066FF'
                });
                return;
            }

            categorias.push(valor);
            renderizarTags();

            // Resetear UI
            if (selectCat.value === 'nueva') {
                inputNuevaCat.value = '';
                divNuevaCat.classList.add('d-none');
            }
            selectCat.value = '';
        });
    }

    // ✅ CORREGIDO: delegación de eventos — evita XSS con comillas en nombres
    if (contenedorTags) {
        contenedorTags.addEventListener('click', function (e) {
            const icono = e.target.closest('[data-cat]');
            if (icono) {
                const nombre = icono.dataset.cat;
                categorias = categorias.filter(c => c !== nombre);
                renderizarTags();
            }
        });
    }

    function renderizarTags() {
        if (!contenedorTags) return;
        contenedorTags.innerHTML = '';

        if (categorias.length === 0) {
            contenedorTags.innerHTML =
                '<span class="text-muted small">Sin categorías asignadas.</span>';
        } else {
            categorias.forEach(cat => {
                const tag = document.createElement('div');
                tag.className = 'badge bg-light text-primary border border-primary p-2 d-flex align-items-center gap-2';

                // ✅ data-attribute en lugar de onclick con comilla simple
                tag.innerHTML = `
                    ${cat}
                    <i class="bi bi-x-circle-fill text-danger"
                       data-cat="${cat.replace(/"/g, '&quot;')}"
                       style="cursor:pointer"></i>
                `;
                contenedorTags.appendChild(tag);
            });
        }

        // Sincronizar input oculto con el array actual
        if (inputHidden) inputHidden.value = categorias.join(',');
    }

    // =======================================================
    // 3. LÓGICA DE CAMBIO DE ROL
    // =======================================================
    const rolSelect      = document.getElementById('rol');
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
        toggleCamposProveedor(); // Estado inicial
    }

});


// =======================================================
// 4. CAMBIAR ESTADO DOCUMENTO (AJAX)
// Fuera del DOMContentLoaded para ser accesible desde onclick del HTML
// =======================================================
window.cambiarEstadoDoc = async function (idDoc, nuevoEstado, btnElement) {

    const btnGroup    = btnElement.closest('.btn-group');
    const originalHtml = btnGroup.innerHTML;

    // Feedback visual — spinner mientras procesa
    btnGroup.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"></div>';

    try {
        const formData = new FormData();
        formData.append('accion',      'cambiar_estado_documento');
        formData.append('id_doc',      idDoc);
        formData.append('nuevo_estado', nuevoEstado);

        const response = await fetch(`${BASE_URL}/admin/actualizar-usuario`, {
            method: 'POST',
            body: formData
        });

        const res = await response.json();

        if (res.success) {

            // Actualizar el badge de estado visualmente
            const tr          = btnGroup.closest('tr');
            const celdaEstado = tr.querySelector('td:nth-child(2)');

            if (nuevoEstado === 'aprobado') {
                celdaEstado.innerHTML = '<span class="badge bg-success">Aprobado</span>';
                btnGroup.innerHTML = `
                    <button type="button" class="btn btn-outline-danger btn-sm"
                            onclick="cambiarEstadoDoc(${idDoc}, 'rechazado', this)"
                            title="Rechazar">
                        <i class="bi bi-x-lg"></i>
                    </button>`;
            } else {
                celdaEstado.innerHTML = '<span class="badge bg-danger">Rechazado</span>';
                btnGroup.innerHTML = `
                    <button type="button" class="btn btn-outline-success btn-sm"
                            onclick="cambiarEstadoDoc(${idDoc}, 'aprobado', this)"
                            title="Aprobar">
                        <i class="bi bi-check-lg"></i>
                    </button>`;
            }

            Swal.fire({
                icon: 'success',
                title: 'Actualizado',
                text: `Documento marcado como ${nuevoEstado}`,
                toast: true, position: 'top-end',
                showConfirmButton: false, timer: 3000
            });

        } else {
            Swal.fire('Error', res.message || 'No se pudo actualizar', 'error');
            btnGroup.innerHTML = originalHtml;
        }

    } catch (error) {
        console.error('Error en cambiarEstadoDoc:', error);
        Swal.fire('Error', 'Fallo de conexión', 'error');
        btnGroup.innerHTML = originalHtml;
    }
};