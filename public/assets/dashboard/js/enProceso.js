// enProceso.js - Funcionalidad para la vista de servicios en proceso

document.addEventListener('DOMContentLoaded', function() {
    
    // Elementos del DOM
    const filtroCategoria = document.getElementById('filtro-categoria');
    const filtroPrioridad = document.getElementById('filtro-prioridad');
    const buscarProceso = document.getElementById('buscar-proceso');
    const tarjetasProceso = document.querySelectorAll('.tarjeta-proceso');
    
    // Botones de acción
    const botonesActualizar = document.querySelectorAll('.btn-actualizar');
    const botonesContactar = document.querySelectorAll('.btn-contactar');
    const botonesCompletar = document.querySelectorAll('.btn-completar');

    // === FUNCIONALIDAD DE FILTROS ===
    
    // Filtrar por categoría
    if (filtroCategoria) {
        filtroCategoria.addEventListener('change', aplicarFiltros);
    }

    // Filtrar por prioridad
    if (filtroPrioridad) {
        filtroPrioridad.addEventListener('change', aplicarFiltros);
    }

    // Búsqueda en tiempo real
    if (buscarProceso) {
        buscarProceso.addEventListener('input', aplicarFiltros);
    }

    function aplicarFiltros() {
        const categoriaSeleccionada = filtroCategoria ? filtroCategoria.value.toLowerCase() : '';
        const prioridadSeleccionada = filtroPrioridad ? filtroPrioridad.value.toLowerCase() : '';
        const textoBusqueda = buscarProceso ? buscarProceso.value.toLowerCase() : '';

        tarjetasProceso.forEach(tarjeta => {
            // Obtener datos de la tarjeta
            const titulo = tarjeta.querySelector('.proceso-titulo').textContent.toLowerCase();
            const clienteNombre = tarjeta.querySelector('.cliente-nombre').textContent.toLowerCase();
            const badgeCategoria = tarjeta.querySelector('.badge-categoria');
            const badgePrioridad = tarjeta.querySelector('.badge-prioridad');
            
            const categoria = badgeCategoria ? badgeCategoria.textContent.toLowerCase().trim() : '';
            const prioridad = badgePrioridad ? badgePrioridad.textContent.toLowerCase().trim() : '';

            // Aplicar filtros
            const coincideCategoria = !categoriaSeleccionada || categoria.includes(categoriaSeleccionada);
            const coincidePrioridad = !prioridadSeleccionada || prioridad.includes(prioridadSeleccionada);
            const coincideBusqueda = !textoBusqueda || 
                                    titulo.includes(textoBusqueda) || 
                                    clienteNombre.includes(textoBusqueda);

            // Mostrar u ocultar tarjeta
            if (coincideCategoria && coincidePrioridad && coincideBusqueda) {
                tarjeta.style.display = 'block';
                tarjeta.style.animation = 'fadeIn 0.3s ease';
            } else {
                tarjeta.style.display = 'none';
            }
        });

        // Mensaje si no hay resultados
        mostrarMensajeSinResultados();
    }

    function mostrarMensajeSinResultados() {
        const listaProcesos = document.getElementById('lista-procesos');
        const tarjetasVisibles = Array.from(tarjetasProceso).filter(
            tarjeta => tarjeta.style.display !== 'none'
        );

        // Eliminar mensaje anterior si existe
        const mensajeExistente = document.getElementById('mensaje-sin-resultados');
        if (mensajeExistente) {
            mensajeExistente.remove();
        }

        // Mostrar mensaje si no hay resultados
        if (tarjetasVisibles.length === 0) {
            const mensaje = document.createElement('div');
            mensaje.id = 'mensaje-sin-resultados';
            mensaje.style.cssText = `
                background-color: #f8fafc;
                border: 2px dashed #cbd5e1;
                border-radius: 12px;
                padding: 40px;
                text-align: center;
                color: #64748b;
                font-size: 1.1rem;
            `;
            mensaje.innerHTML = `
                <i class="bi bi-inbox" style="font-size: 3rem; display: block; margin-bottom: 15px;"></i>
                <strong>No se encontraron servicios</strong>
                <p style="margin-top: 10px; font-size: 0.95rem;">
                    Intenta ajustar los filtros o la búsqueda
                </p>
            `;
            listaProcesos.appendChild(mensaje);
        }
    }

    // === FUNCIONALIDAD DE BOTONES ===

    // Botones actualizar estado
    botonesActualizar.forEach(boton => {
        boton.addEventListener('click', function() {
            const tarjeta = this.closest('.tarjeta-proceso');
            const titulo = tarjeta.querySelector('.proceso-titulo').textContent;
            
            mostrarModal('Actualizar Estado', `
                <p>Selecciona el nuevo estado del servicio:</p>
                <p><strong>${titulo}</strong></p>
                <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px;">
                    <button class="btn-modal-opcion" onclick="actualizarProgreso(this, 25)">
                        <i class="bi bi-arrow-right-circle"></i> 25% - Iniciado
                    </button>
                    <button class="btn-modal-opcion" onclick="actualizarProgreso(this, 50)">
                        <i class="bi bi-arrow-right-circle"></i> 50% - En progreso
                    </button>
                    <button class="btn-modal-opcion" onclick="actualizarProgreso(this, 75)">
                        <i class="bi bi-arrow-right-circle"></i> 75% - Casi completo
                    </button>
                    <button class="btn-modal-opcion" onclick="actualizarProgreso(this, 100)">
                        <i class="bi bi-check-circle"></i> 100% - Completado
                    </button>
                </div>
            `);
        });
    });

    // Botones contactar cliente
    botonesContactar.forEach(boton => {
        boton.addEventListener('click', function() {
            const tarjeta = this.closest('.tarjeta-proceso');
            const clienteNombre = tarjeta.querySelector('.cliente-nombre').textContent;
            const clienteTelefono = tarjeta.querySelector('.cliente-contacto').textContent.trim();
            
            mostrarModal('Contactar Cliente', `
                <p>Selecciona el método de contacto para <strong>${clienteNombre}</strong>:</p>
                <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px;">
                    <button class="btn-modal-opcion" onclick="contactarCliente('telefono', '${clienteTelefono}')">
                        <i class="bi bi-telephone"></i> Llamar por teléfono
                    </button>
                    <button class="btn-modal-opcion" onclick="contactarCliente('whatsapp', '${clienteTelefono}')">
                        <i class="bi bi-whatsapp"></i> Enviar WhatsApp
                    </button>
                    <button class="btn-modal-opcion" onclick="contactarCliente('chat', '${clienteNombre}')">
                        <i class="bi bi-chat-dots"></i> Abrir chat interno
                    </button>
                </div>
            `);
        });
    });

    // Botones marcar como completado
    botonesCompletar.forEach(boton => {
        boton.addEventListener('click', function() {
            const tarjeta = this.closest('.tarjeta-proceso');
            const titulo = tarjeta.querySelector('.proceso-titulo').textContent;
            const progresoPorcentaje = tarjeta.querySelector('.progreso-porcentaje').textContent;
            
            if (progresoPorcentaje === '100%') {
                mostrarModal('Confirmar Completado', `
                    <p>¿Estás seguro de marcar como completado el servicio?</p>
                    <p><strong>${titulo}</strong></p>
                    <div style="display: flex; gap: 10px; margin-top: 20px; justify-content: center;">
                        <button class="btn-modal-confirmar" onclick="marcarCompletado(this)">
                            <i class="bi bi-check-circle"></i> Sí, completar
                        </button>
                        <button class="btn-modal-cancelar" onclick="cerrarModal()">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    </div>
                `);
            } else {
                mostrarAlerta('warning', 'El servicio aún no está al 100% de progreso. ¿Deseas actualizarlo primero?');
            }
        });
    });

    // === FUNCIONES AUXILIARES ===

    function mostrarModal(titulo, contenido) {
        // Crear overlay
        const overlay = document.createElement('div');
        overlay.id = 'modal-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        `;

        // Crear modal
        const modal = document.createElement('div');
        modal.style.cssText = `
            background-color: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.3s ease;
        `;

        modal.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; font-size: 1.5rem; color: #0E1116;">${titulo}</h3>
                <button onclick="cerrarModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div>${contenido}</div>
        `;

        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        // Cerrar al hacer clic fuera del modal
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                cerrarModal();
            }
        });
    }

    window.cerrarModal = function() {
        const overlay = document.getElementById('modal-overlay');
        if (overlay) {
            overlay.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => overlay.remove(), 300);
        }
    };

    function mostrarAlerta(tipo, mensaje) {
        const colores = {
            success: '#10b981',
            warning: '#f59e0b',
            error: '#ef4444',
            info: '#0066FF'
        };

        const alerta = document.createElement('div');
        alerta.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: ${colores[tipo] || colores.info};
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 10000;
            animation: slideInRight 0.3s ease;
            max-width: 350px;
        `;
        alerta.textContent = mensaje;

        document.body.appendChild(alerta);

        setTimeout(() => {
            alerta.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => alerta.remove(), 300);
        }, 3000);
    }

    // Funciones globales para los botones del modal
    window.actualizarProgreso = function(boton, porcentaje) {
        cerrarModal();
        mostrarAlerta('success', `Progreso actualizado a ${porcentaje}%`);
        
        // Aquí iría la lógica para actualizar el progreso en el backend
        console.log('Actualizando progreso a:', porcentaje);
    };

    window.contactarCliente = function(metodo, dato) {
        cerrarModal();
        
        switch(metodo) {
            case 'telefono':
                mostrarAlerta('info', 'Abriendo marcador telefónico...');
                // window.location.href = `tel:${dato}`;
                break;
            case 'whatsapp':
                mostrarAlerta('success', 'Abriendo WhatsApp...');
                // window.open(`https://wa.me/${dato.replace(/\D/g, '')}`, '_blank');
                break;
            case 'chat':
                mostrarAlerta('info', 'Abriendo chat con el cliente...');
                // Redirigir a la vista de chat
                break;
        }
    };

    window.marcarCompletado = function(boton) {
        cerrarModal();
        mostrarAlerta('success', '¡Servicio marcado como completado!');
        
        // Aquí iría la lógica para marcar como completado en el backend
        console.log('Servicio completado');
    };

    // === ANIMACIONES CSS ===
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes slideInRight {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }

        .btn-modal-opcion {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e2e8f0;
            background-color: white;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #0E1116;
        }

        .btn-modal-opcion:hover {
            background-color: #0066FF;
            border-color: #0066FF;
            color: white;
            transform: translateX(5px);
        }

        .btn-modal-confirmar {
            padding: 12px 24px;
            background-color: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .btn-modal-confirmar:hover {
            background-color: #059669;
            transform: translateY(-1px);
        }

        .btn-modal-cancelar {
            padding: 12px 24px;
            background-color: #f1f5f9;
            color: #64748b;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .btn-modal-cancelar:hover {
            background-color: #e2e8f0;
            transform: translateY(-1px);
        }
    `;
    document.head.appendChild(style);

    console.log('✅ Sistema de procesos iniciado correctamente');
});