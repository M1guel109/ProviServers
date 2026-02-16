// Variable global para la URL base (asegúrate que esté definida en tu HTML)
// const BASE_URL = "http://localhost/ProviServers"; 

function cargarDetalleUsuario(id) {
    // 1. Mostrar loader y ocultar contenido anterior
    document.getElementById('loader-detalle').classList.remove('d-none');
    document.getElementById('contenido-detalle').classList.add('d-none');
    
    // 2. Petición AJAX
    fetch(`${BASE_URL}/admin/api/usuario-detalle?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            
            // 3. Llenar datos básicos
            // Ruta de imagen (ajusta la carpeta según corresponda)
            const rutaFoto = `${BASE_URL}/public/uploads/usuarios/${data.foto || 'default_user.png'}`;
            document.getElementById('modal-foto').src = rutaFoto;
            
            document.getElementById('modal-nombre').textContent = `${data.nombres} ${data.apellidos}`;
            document.getElementById('modal-rol').textContent = data.rol;
            document.getElementById('modal-id').textContent = `ID: ${data.id}`;
            
            document.getElementById('modal-email').textContent = data.email;
            document.getElementById('modal-telefono').textContent = data.telefono || 'N/A';
            document.getElementById('modal-ubicacion').textContent = data.ubicacion || 'N/A';
            document.getElementById('modal-documento').textContent = data.documento;
            
            // Estado con color
            const spanEstado = document.getElementById('modal-estado-texto');
            spanEstado.textContent = data.estado;
            spanEstado.className = data.estado === 'activo' ? 'text-success fw-bold' : 'text-warning fw-bold';

            document.getElementById('modal-fecha').textContent = data.created_at;

            // 4. Lógica Proveedor
            const divProv = document.getElementById('seccion-proveedor');
            
            if (data.rol === 'proveedor') {
                divProv.classList.remove('d-none');
                
                // Categorías
                const contCat = document.getElementById('modal-categorias');
                contCat.innerHTML = '';
                if (data.categorias && data.categorias.length > 0) {
                    data.categorias.forEach(cat => {
                        contCat.innerHTML += `<span class="badge bg-info text-dark">${cat}</span>`;
                    });
                } else {
                    contCat.innerHTML = '<span class="text-muted small">Sin categorías asignadas</span>';
                }

                // Documentos
                const contDoc = document.getElementById('modal-documentos');
                contDoc.innerHTML = '';
                if (data.documentos && data.documentos.length > 0) {
                    data.documentos.forEach(doc => {
                        const rutaDoc = `${BASE_URL}/public/uploads/documentos/${doc.archivo}`;
                        contDoc.innerHTML += `
                            <a href="${rutaDoc}" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-file-earmark-text text-danger me-2"></i>
                                    ${doc.tipo_documento}
                                </div>
                                <span class="badge bg-secondary">${doc.estado}</span>
                            </a>
                        `;
                    });
                } else {
                    contDoc.innerHTML = '<div class="p-3 text-muted small">No hay documentos cargados.</div>';
                }

            } else {
                divProv.classList.add('d-none');
            }

            // 5. Mostrar contenido final
            document.getElementById('loader-detalle').classList.add('d-none');
            document.getElementById('contenido-detalle').classList.remove('d-none');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del usuario');
        });
}

// =======================================================
// 2. INICIALIZACIÓN DE TABLAS (Al cargar el DOM)
// =======================================================
document.addEventListener("DOMContentLoaded", () => {

    // --- CONFIGURACIÓN A: TABLA PRINCIPAL (#tabla) ---
    // Solo búsqueda y paginación. Diseño limpio.
    let tableMain = new DataTable('#tabla', {
        responsive: true,
        pageLength: 10,
        layout: {
            topStart: 'search',
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        language: {
            url: "https://cdn.datatables.net/plug-ins/2.0.2/i18n/es-ES.json"
        },
        // Personalización del input de búsqueda (Lupa)
        initComplete: function () {
            styleSearchInput('#tabla_wrapper');
        }
    });

    // --- CONFIGURACIÓN B: TABLA EXPORTACIÓN (#tabla-1) ---
    // Con botones, sin paginación (para exportar todo).
    let tableExport = new DataTable('#tabla-1', {
        responsive: true,
        paging: false, // ¡Importante! Para exportar todos los datos, no solo la página actual
        layout: {
            topStart: {
                buttons: [
                    {
                        extend: 'copy',
                        text: '<i class="bi bi-clipboard"></i> Copiar',
                        className: 'btn btn-outline-secondary btn-sm',
                    },
                    {
                        extend: 'excel',
                        text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Reporte_Usuarios'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        title: 'Reporte_Usuarios',
                        orientation: 'landscape'
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer"></i> Imprimir',
                        className: 'btn btn-info btn-sm text-white',
                    }
                ]
            },
            topEnd: 'search'
        },
        language: {
            url: "https://cdn.datatables.net/plug-ins/2.0.2/i18n/es-ES.json"
        },
        initComplete: function () {
            styleSearchInput('#tabla-1_wrapper');
        }
    });

    // --- FUNCIÓN AUXILIAR: Estilar el buscador con la lupa ---
    function styleSearchInput(wrapperSelector) {
        const wrapper = document.querySelector(wrapperSelector);
        if (!wrapper) return;

        const dtSearch = wrapper.querySelector('.dt-search');
        if (!dtSearch) return;

        const input = dtSearch.querySelector('input[type="search"]');
        if (!input) return;

        // Crear contenedor personalizado
        const buscadorDiv = document.createElement('div');
        buscadorDiv.className = 'buscador'; // Clase definida en tu CSS
        buscadorDiv.innerHTML = `<i class="bi bi-search"></i>`;
        buscadorDiv.appendChild(input);

        // Limpiar y agregar nuevo input
        dtSearch.innerHTML = '';
        dtSearch.appendChild(buscadorDiv);

        // Estilos inline para asegurar compatibilidad
        input.setAttribute('placeholder', 'Buscar...');
        input.style.width = "100%";
        input.style.border = "none";
        input.style.background = "transparent";
        input.style.outline = "none";
        input.style.paddingLeft = "10px";
    }

    // --- CORRECCIÓN DE PESTAÑAS (Tabs) ---
    // Ajusta las columnas cuando se cambia de pestaña para evitar deformaciones
    const tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabEls.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (event) {
            // Ajustar columnas de ambas tablas
            tableMain.columns.adjust().responsive.recalc();
            tableExport.columns.adjust().responsive.recalc();
        });
    });

});