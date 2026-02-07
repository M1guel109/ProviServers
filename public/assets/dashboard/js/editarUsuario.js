/* editarUsuario.js - Lógica específica para la vista de edición */

document.addEventListener('DOMContentLoaded', function () {
    
    // =======================================================
    // 1. PREVISUALIZACIÓN DE FOTO (Si cambia)
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

    // Array en memoria
    let categorias = [];

    // A. Inicializar (Leer input hidden y pintar)
    if (inputHidden && inputHidden.value) {
        // Convierte "Plomeria,Luz" en ['Plomeria', 'Luz']
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

            if (!valor) return; // Validación vacía

            // Validación duplicados
            if (categorias.some(c => c.toLowerCase() === valor.toLowerCase())) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Repetida',
                    text: 'Esta categoría ya está agregada.',
                    toast: true, position: 'top-end', timer: 2000, showConfirmButton: false
                });
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

    // D. Función Renderizar
    function renderizarTags() {
        if(!contenedorTags) return;
        contenedorTags.innerHTML = '';
        
        if(categorias.length === 0) {
            contenedorTags.innerHTML = '<span class="text-muted small">Sin categorías asignadas.</span>';
        }

        categorias.forEach(cat => {
            const tag = document.createElement('div');
            // Usamos las mismas clases de estilo que tenías
            tag.className = 'badge-categoria'; 
            tag.style.cssText = 'background: #e3f2fd; color: #0d47a1; padding: 5px 10px; border-radius: 20px; font-size: 0.9em; display: inline-flex; align-items: center; gap: 5px; border: 1px solid #bbdefb;';
            tag.innerHTML = `
                ${cat}
                <i class="bi bi-x-circle-fill text-danger" style="cursor:pointer" onclick="eliminarCategoria('${cat}')"></i>
            `;
            contenedorTags.appendChild(tag);
        });

        // Actualizar input hidden para enviar al PHP
        if(inputHidden) inputHidden.value = categorias.join(',');
    }

    // E. Función Eliminar (Global para que funcione el onclick)
    window.eliminarCategoria = function(nombre) {
        categorias = categorias.filter(c => c !== nombre);
        renderizarTags();
    };

});

// =======================================================
// 3. CAMBIAR ESTADO DE DOCUMENTO (AJAX)
// =======================================================
function cambiarEstadoDoc(docId, nuevoEstado) {
    
    // Título dinámico para el modal
    const accion = nuevoEstado === 'aprobado' ? 'Aprobar' : 'Rechazar';
    const color = nuevoEstado === 'aprobado' ? '#198754' : '#dc3545';

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
            
            // AQUÍ LLAMARÍAS A TU API REAL
            /*
            fetch(BASE_URL + '/admin/api/cambiar-estado-doc', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: docId, estado: nuevoEstado })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    Swal.fire('¡Listo!', 'Estado actualizado.', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', 'No se pudo actualizar.', 'error');
                }
            });
            */
            
            // Simulación mientras creas el endpoint
            Swal.fire({
                icon: 'success',
                title: 'Simulación Exitosa',
                text: `Se enviaría ID: ${docId} con Estado: ${nuevoEstado} al servidor.`
            });
            // location.reload(); // Descomentar para ver efecto real al tener backend
        }
    });
}