// JavaScript para el formulario de Mis Servicios

document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const formServicio = document.getElementById('form-servicio');
    const descripcionTextarea = document.getElementById('descripcion');
    const contadorDescripcion = document.getElementById('contador-descripcion');
    const btnCancelar = document.getElementById('btn-cancelar');
    const btnVerLista = document.getElementById('btn-ver-lista');
    const serviciosGrid = document.getElementById('servicios-grid');
    const btnGuardar = document.getElementById('btn-guardar');
    const btnToggleMenu = document.getElementById('btn-toggle-menu');

    // Variables globales
    let servicios = [];
    let servicioEditando = null;

    // Inicialización
    inicializarEventos();
    cargarServiciosIniciales();
    actualizarContador();
    inicializarSidebar();

    function inicializarEventos() {
        // Contador de caracteres
        if (descripcionTextarea) {
            descripcionTextarea.addEventListener('input', actualizarContador);
        }
        
        // Botones
        if (btnCancelar) {
            btnCancelar.addEventListener('click', manejarCancelar);
        }
        
        if (btnVerLista) {
            btnVerLista.addEventListener('click', manejarVerLista);
        }
        
        // Formulario
        if (formServicio) {
            formServicio.addEventListener('submit', manejarEnvioFormulario);
        }

        // Submenús
        inicializarSubmenus();
    }

    function inicializarSidebar() {
        // Toggle del menú lateral
        if (btnToggleMenu) {
            btnToggleMenu.addEventListener('click', function() {
                const sidebar = document.querySelector('.sidebar');
                if (sidebar) {
                    sidebar.classList.toggle('plegado');
                }
            });
        }
    }

    function inicializarSubmenus() {
        const toggleSubmenuButtons = document.querySelectorAll('.toggle-submenu');
        
        toggleSubmenuButtons.forEach(button => {
            button.addEventListener('click', function() {
                const submenu = this.parentElement.querySelector('.submenu');
                const hasSubmenu = this.parentElement;
                
                hasSubmenu.classList.toggle('active');
                
                if (hasSubmenu.classList.contains('active')) {
                    submenu.style.maxHeight = submenu.scrollHeight + 'px';
                } else {
                    submenu.style.maxHeight = '0';
                }
            });
        });
    }

    function cargarServiciosIniciales() {
        // Simular carga de servicios existentes desde localStorage o API
        const serviciosGuardados = localStorage.getItem('serviciosProveedor');
        
        if (serviciosGuardados) {
            servicios = JSON.parse(serviciosGuardados);
        } else {
            // Datos de ejemplo
            servicios = [
                {
                    id: 1,
                    nombre: "Reparación de tuberías residenciales",
                    descripcion: "Servicio completo de reparación e instalación de tuberías para hogares. Incluye diagnóstico y solución de problemas de plomería.",
                    precio: "150.00",
                    categoria: "plomeria",
                    disponibilidad: 1,
                    promocion_id: null,
                    created_at: "2024-01-15 10:30:00"
                },
                {
                    id: 2,
                    nombre: "Instalación eléctrica básica",
                    descripcion: "Instalación y reparación de sistemas eléctricos residenciales. Tomas, interruptores y cableado seguro.",
                    precio: "200.00",
                    categoria: "electricidad",
                    disponibilidad: 0,
                    promocion_id: 1,
                    created_at: "2024-01-10 14:20:00"
                },
                {
                    id: 3,
                    nombre: "Pintura de interiores profesional",
                    descripcion: "Servicio completo de pintura para interiores con materiales de primera calidad y acabado profesional.",
                    precio: "300.00",
                    categoria: "pintura",
                    disponibilidad: 1,
                    promocion_id: 3,
                    created_at: "2024-01-08 09:15:00"
                }
            ];
            guardarServiciosEnLocalStorage();
        }
        
        renderizarServicios();
    }

    function guardarServiciosEnLocalStorage() {
        localStorage.setItem('serviciosProveedor', JSON.stringify(servicios));
    }

    function actualizarContador() {
        if (!descripcionTextarea || !contadorDescripcion) return;
        
        const longitud = descripcionTextarea.value.length;
        contadorDescripcion.textContent = longitud;
        
        // Cambiar color según la longitud
        if (longitud > 450) {
            contadorDescripcion.style.color = '#ef4444';
        } else if (longitud > 400) {
            contadorDescripcion.style.color = '#f59e0b';
        } else {
            contadorDescripcion.style.color = '#666';
        }
    }

    function manejarCancelar() {
        const nombre = document.getElementById('nombre').value;
        const descripcion = document.getElementById('descripcion').value;
        
        if ((nombre || descripcion) && !confirm('¿Estás seguro de que quieres cancelar? Los cambios no guardados se perderán.')) {
            return;
        }
        
        limpiarFormulario();
        mostrarMensaje('Formulario cancelado', 'info');
    }

    function manejarVerLista() {
        // Scroll suave a la lista de servicios
        const listaServicios = document.getElementById('lista-servicios');
        if (listaServicios) {
            listaServicios.scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    function limpiarFormulario() {
        if (formServicio) {
            formServicio.reset();
        }
        
        document.getElementById('servicio_id').value = '';
        servicioEditando = null;
        actualizarContador();
        
        // Restaurar texto del botón
        if (btnGuardar) {
            btnGuardar.innerHTML = '<i class="bi bi-check-circle"></i> Guardar Servicio';
            btnGuardar.disabled = false;
        }
    }

    function manejarEnvioFormulario(event) {
        event.preventDefault();
        
        if (!validarFormulario()) {
            return;
        }

        const formData = new FormData(formServicio);
        const servicioId = document.getElementById('servicio_id').value;
        
        // Mostrar estado de carga
        const textoOriginal = btnGuardar.innerHTML;
        btnGuardar.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
        btnGuardar.disabled = true;

        // Simular envío al servidor con timeout
        setTimeout(() => {
            try {
                const servicio = {
                    id: servicioId ? parseInt(servicioId) : Date.now(),
                    nombre: formData.get('nombre').trim(),
                    descripcion: formData.get('descripcion').trim(),
                    precio: formData.get('precio'),
                    categoria: formData.get('categoria'),
                    disponibilidad: parseInt(formData.get('disponibilidad')),
                    promocion_id: formData.get('promocion_id') || null,
                    created_at: servicioId && servicioEditando ? servicioEditando.created_at : new Date().toISOString()
                };

                if (servicioId) {
                    // Actualizar servicio existente
                    const index = servicios.findIndex(s => s.id == servicioId);
                    if (index !== -1) {
                        servicios[index] = servicio;
                        mostrarMensaje('Servicio actualizado correctamente', 'success');
                    }
                } else {
                    // Agregar nuevo servicio
                    servicio.id = generarNuevoId();
                    servicios.push(servicio);
                    mostrarMensaje('Servicio creado correctamente', 'success');
                }

                // Guardar en localStorage
                guardarServiciosEnLocalStorage();
                
                renderizarServicios();
                limpiarFormulario();

            } catch (error) {
                console.error('Error guardando servicio:', error);
                mostrarMensaje('Error al guardar el servicio. Por favor, intenta nuevamente.', 'error');
            } finally {
                // Restaurar estado normal
                if (btnGuardar) {
                    btnGuardar.innerHTML = textoOriginal;
                    btnGuardar.disabled = false;
                }
            }
        }, 1500);
    }

    function generarNuevoId() {
        return servicios.length > 0 ? Math.max(...servicios.map(s => s.id)) + 1 : 1;
    }

    function validarFormulario() {
        const nombre = document.getElementById('nombre').value.trim();
        const categoria = document.getElementById('categoria').value;
        const precio = document.getElementById('precio').value;
        
        // Validar nombre
        if (!nombre) {
            mostrarMensaje('El nombre del servicio es requerido', 'error');
            document.getElementById('nombre').focus();
            return false;
        }
        
        if (nombre.length > 100) {
            mostrarMensaje('El nombre no puede exceder los 100 caracteres', 'error');
            document.getElementById('nombre').focus();
            return false;
        }
        
        // Validar categoría
        if (!categoria) {
            mostrarMensaje('La categoría es requerida', 'error');
            document.getElementById('categoria').focus();
            return false;
        }
        
        // Validar precio si está presente
        if (precio) {
            const precioNum = parseFloat(precio);
            if (isNaN(precioNum) || precioNum < 0) {
                mostrarMensaje('El precio debe ser un número válido mayor o igual a 0', 'error');
                document.getElementById('precio').focus();
                return false;
            }
            
            if (precioNum > 99999999.99) {
                mostrarMensaje('El precio no puede exceder $99,999,999.99', 'error');
                document.getElementById('precio').focus();
                return false;
            }
        }
        
        return true;
    }

    function renderizarServicios() {
        if (!serviciosGrid) return;
        
        if (servicios.length === 0) {
            serviciosGrid.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-briefcase" style="font-size: 3rem; color: #cbd5e1;"></i>
                    <p class="text-muted mt-3">No tienes servicios registrados</p>
                    <p class="text-muted">Agrega tu primer servicio usando el formulario superior</p>
                </div>
            `;
            return;
        }

        serviciosGrid.innerHTML = servicios.map(servicio => `
            <div class="col">
                <div class="servicio-card">
                    <div class="servicio-header">
                        <h3 class="servicio-nombre">${escapeHtml(servicio.nombre)}</h3>
                        <div class="servicio-acciones">
                            <button class="btn-accion btn-editar" onclick="editarServicio(${servicio.id})" title="Editar servicio">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn-accion btn-eliminar" onclick="eliminarServicio(${servicio.id})" title="Eliminar servicio">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="servicio-categoria">${obtenerNombreCategoria(servicio.categoria)}</div>
                    
                    <div class="servicio-descripcion">${escapeHtml(servicio.descripcion || 'Sin descripción')}</div>
                    
                    <div class="servicio-info">
                        <div class="servicio-precio">
                            ${servicio.precio ? `$${parseFloat(servicio.precio).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : 'Precio a consultar'}
                        </div>
                        <div class="servicio-estado ${servicio.disponibilidad === 1 ? 'estado-disponible' : 'estado-no-disponible'}">
                            <i class="bi ${servicio.disponibilidad === 1 ? 'bi-check-circle' : 'bi-x-circle'}"></i>
                            ${servicio.disponibilidad === 1 ? 'Disponible' : 'No disponible'}
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    // Funciones globales para los botones de editar/eliminar
    window.editarServicio = function(id) {
        servicioEditando = servicios.find(s => s.id === id);
        if (servicioEditando) {
            // Llenar el formulario con los datos del servicio
            document.getElementById('servicio_id').value = servicioEditando.id;
            document.getElementById('nombre').value = servicioEditando.nombre;
            document.getElementById('descripcion').value = servicioEditando.descripcion || '';
            document.getElementById('precio').value = servicioEditando.precio || '';
            document.getElementById('categoria').value = servicioEditando.categoria;
            
            // Configurar disponibilidad
            const radioDisponibilidad = document.querySelector(`input[name="disponibilidad"][value="${servicioEditando.disponibilidad}"]`);
            if (radioDisponibilidad) {
                radioDisponibilidad.checked = true;
            }
            
            document.getElementById('promocion_id').value = servicioEditando.promocion_id || '';
            
            actualizarContador();
            
            // Actualizar texto del botón
            if (btnGuardar) {
                btnGuardar.innerHTML = '<i class="bi bi-check-circle"></i> Actualizar Servicio';
            }
            
            // Scroll al formulario
            const formularioSection = document.getElementById('formulario');
            if (formularioSection) {
                formularioSection.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
            
            mostrarMensaje(`Editando servicio: "${servicioEditando.nombre}"`, 'info');
        }
    };

    window.eliminarServicio = function(id) {
        const servicio = servicios.find(s => s.id === id);
        if (!servicio) return;
        
        if (confirm(`¿Estás seguro de que quieres eliminar el servicio "${servicio.nombre}"? Esta acción no se puede deshacer.`)) {
            servicios = servicios.filter(s => s.id !== id);
            
            // Guardar cambios en localStorage
            guardarServiciosEnLocalStorage();
            
            renderizarServicios();
            
            // Si estábamos editando el servicio eliminado, limpiar formulario
            if (servicioEditando && servicioEditando.id === id) {
                limpiarFormulario();
            }
            
            mostrarMensaje('Servicio eliminado correctamente', 'success');
        }
    };

    // Función para cambiar disponibilidad rápida
    window.cambiarDisponibilidad = function(id) {
        const servicio = servicios.find(s => s.id === id);
        if (servicio) {
            servicio.disponibilidad = servicio.disponibilidad === 1 ? 0 : 1;
            
            // Guardar cambios en localStorage
            guardarServiciosEnLocalStorage();
            
            renderizarServicios();
            
            const estado = servicio.disponibilidad === 1 ? 'disponible' : 'no disponible';
            mostrarMensaje(`Servicio marcado como ${estado}`, 'success');
        }
    };

    // Funciones de utilidad
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function obtenerNombreCategoria(categoria) {
        const categorias = {
            'plomeria': 'Plomería',
            'electricidad': 'Electricidad',
            'pintura': 'Pintura',
            'carpinteria': 'Carpintería',
            'limpieza': 'Limpieza',
            'jardineria': 'Jardinería',
            'mecanica': 'Mecánica',
            'tecnologia': 'Tecnología',
            'otros': 'Otros'
        };
        return categorias[categoria] || categoria;
    }

    function mostrarMensaje(mensaje, tipo = 'info') {
        // Remover mensajes existentes
        const mensajesExistentes = document.querySelectorAll('.alert');
        mensajesExistentes.forEach(msg => {
            if (msg.parentNode) {
                msg.parentNode.removeChild(msg);
            }
        });

        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[tipo] || 'alert-info';

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Insertar después del título principal
        const tituloPrincipal = document.getElementById('titulo-principal');
        if (tituloPrincipal && tituloPrincipal.parentNode) {
            tituloPrincipal.parentNode.insertBefore(alertDiv, tituloPrincipal.nextSibling);
        }

        // Auto-eliminar después de 5 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    // Función para buscar servicios
    function buscarServicios(termino) {
        if (!termino) {
            renderizarServicios();
            return;
        }
        
        const serviciosFiltrados = servicios.filter(servicio =>
            servicio.nombre.toLowerCase().includes(termino.toLowerCase()) ||
            servicio.descripcion.toLowerCase().includes(termino.toLowerCase()) ||
            obtenerNombreCategoria(servicio.categoria).toLowerCase().includes(termino.toLowerCase())
        );
        
        if (serviciosFiltrados.length === 0) {
            serviciosGrid.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search" style="font-size: 3rem; color: #cbd5e1;"></i>
                    <p class="text-muted mt-3">No se encontraron servicios</p>
                    <p class="text-muted">No hay servicios que coincidan con "${termino}"</p>
                </div>
            `;
        } else {
            serviciosGrid.innerHTML = serviciosFiltrados.map(servicio => `
                <div class="col">
                    <div class="servicio-card">
                        <div class="servicio-header">
                            <h3 class="servicio-nombre">${escapeHtml(servicio.nombre)}</h3>
                            <div class="servicio-acciones">
                                <button class="btn-accion btn-editar" onclick="editarServicio(${servicio.id})" title="Editar servicio">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn-accion btn-eliminar" onclick="eliminarServicio(${servicio.id})" title="Eliminar servicio">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="servicio-categoria">${obtenerNombreCategoria(servicio.categoria)}</div>
                        
                        <div class="servicio-descripcion">${escapeHtml(servicio.descripcion || 'Sin descripción')}</div>
                        
                        <div class="servicio-info">
                            <div class="servicio-precio">
                                ${servicio.precio ? `$${parseFloat(servicio.precio).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : 'Precio a consultar'}
                            </div>
                            <div class="servicio-estado ${servicio.disponibilidad === 1 ? 'estado-disponible' : 'estado-no-disponible'}">
                                <i class="bi ${servicio.disponibilidad === 1 ? 'bi-check-circle' : 'bi-x-circle'}"></i>
                                ${servicio.disponibilidad === 1 ? 'Disponible' : 'No disponible'}
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    }

    // Inicializar buscador si existe
    const inputBusqueda = document.querySelector('.buscador input');
    if (inputBusqueda) {
        inputBusqueda.addEventListener('input', function(e) {
            buscarServicios(e.target.value);
        });
    }

    // Función para exportar servicios
    window.exportarServicios = function() {
        const datosExportar = {
            fecha: new Date().toISOString(),
            totalServicios: servicios.length,
            servicios: servicios
        };
        
        const blob = new Blob([JSON.stringify(datosExportar, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `servicios-${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        
        mostrarMensaje('Servicios exportados correctamente', 'success');
    };
});