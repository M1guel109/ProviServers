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