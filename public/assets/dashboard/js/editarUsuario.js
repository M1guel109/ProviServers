/* editarUsuario.js - Lógica corregida */

document.addEventListener('DOMContentLoaded', function () {
    console.log("Script editarUsuario.js cargado correctamente.");

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

    let categorias = [];

    // A. Inicializar
    if (inputHidden && inputHidden.value) {
        const valores = inputHidden.value.split(',').filter(c => c.trim() !== '');
        categorias = valores;
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
            if (categorias.some(c => c.toLowerCase() === valor.toLowerCase())) {
                if(typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Repetida',
                        text: 'Esta categoría ya está agregada.',
                        toast: true, position: 'top-end', timer: 2000, showConfirmButton: false
                    });
                } else {
                    alert('Categoría repetida');
                }
                return;
            }

            // Agregar y Renderizar
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

    // D. Función Renderizar (Local)
    function renderizarTags() {
        if(!contenedorTags) return;
        contenedorTags.innerHTML = '';
        
        if(categorias.length === 0) {
            contenedorTags.innerHTML = '<span class="text-muted small">Sin categorías asignadas.</span>';
        }

        categorias.forEach(cat => {
            const tag = document.createElement('div');
            tag.className = 'badge-categoria'; 
            tag.style.cssText = 'background: #e3f2fd; color: #0d47a1; padding: 5px 10px; border-radius: 20px; font-size: 0.9em; display: inline-flex; align-items: center; gap: 5px; border: 1px solid #bbdefb;';
            tag.innerHTML = `
                ${cat}
                <i class="bi bi-x-circle-fill text-danger" style="cursor:pointer" onclick="eliminarCategoria('${cat}')"></i>
            `;
            contenedorTags.appendChild(tag);
        });

        if(inputHidden) inputHidden.value = categorias.join(',');
    }

    // E. Función Eliminar (Global)
    window.eliminarCategoria = function(nombre) {
        categorias = categorias.filter(c => c !== nombre);
        renderizarTags();
    };

    // =======================================================
    // 3. LÓGICA DE CAMBIO DE ROL (MOSTRAR/OCULTAR)
    // =======================================================
    const rolSelect = document.getElementById('rol');
    const camposProveedor = document.getElementById('campos-proveedor');

    if (rolSelect && camposProveedor) {
        
        function toggleCamposProveedor() {
            console.log("Cambio de rol detectado: " + rolSelect.value);
            if (rolSelect.value === 'proveedor') {
                camposProveedor.classList.remove('d-none');
                camposProveedor.classList.add('fade-in'); 
            } else {
                camposProveedor.classList.add('d-none');
                camposProveedor.classList.remove('fade-in');
            }
        }

        rolSelect.addEventListener('change', toggleCamposProveedor);
        
        // Ejecutar al inicio para establecer estado correcto
        toggleCamposProveedor();
    } else {
        console.warn("No se encontraron los elementos 'rol' o 'campos-proveedor'");
    }

});

// =======================================================
// 4. CAMBIAR ESTADO DE DOCUMENTO (FUERA DEL DOMCONTENTLOADED)
// =======================================================
// Al estar aquí afuera, el HTML onclick="cambiarEstadoDoc(...)" sí puede verla.
window.cambiarEstadoDoc = function(docId, nuevoEstado) {
    
    const accion = nuevoEstado === 'aprobado' ? 'Aprobar' : 'Rechazar';
    const color = nuevoEstado === 'aprobado' ? '#198754' : '#dc3545';

    if(typeof Swal === 'undefined') {
        if(confirm(`¿${accion} documento?`)) {
            alert(`Simulación: Documento ${docId} ${nuevoEstado}`);
        }
        return;
    }

    Swal.fire({
        title: `¿${accion} documento?`,
        text: "El estado cambiará inmediatamente.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: color,
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // AQUÍ IRÍA TU FETCH REAL
            Swal.fire({
                icon: 'success',
                title: 'Simulación Exitosa',
                text: `Se enviaría ID: ${docId} con Estado: ${nuevoEstado} al servidor.`
            });
        }
    });
};