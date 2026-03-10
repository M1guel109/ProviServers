/* ======================================================
   facturacion.js - Funcionalidad para gestión de facturación
   ====================================================== */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Facturación cargada');
    
    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Event listeners para filtros
    const aplicarFiltro = document.getElementById('aplicar-filtro');
    const periodoSelect = document.getElementById('periodo-facturas');
    
    if (aplicarFiltro) {
        aplicarFiltro.addEventListener('click', function() {
            const periodo = periodoSelect ? periodoSelect.value : 'año';
            console.log('Filtrar por período:', periodo);
            alert(`Filtrando facturas por: ${periodo}`);
        });
    }
    
    // Botones de filtro de tabla (Todas, Pendientes, Pagadas)
    const filtroBotones = document.querySelectorAll('.btn-group .btn-outline-secondary');
    filtroBotones.forEach((btn, index) => {
        btn.addEventListener('click', function() {
            // Quitar active de todos
            filtroBotones.forEach(b => b.classList.remove('active'));
            // Agregar active al clickeado
            this.classList.add('active');
            
            // Filtrar tabla según índice
            filtrarTabla(index);
        });
    });
    
    // Botones de pagar factura
    const botonesPagar = document.querySelectorAll('.btn-pagar-factura');
    botonesPagar.forEach(btn => {
        btn.addEventListener('click', function() {
            const facturaId = this.getAttribute('data-factura-id');
            const monto = this.getAttribute('data-monto');
            
            if (facturaId && monto) {
                actualizarModalPago(facturaId, parseInt(monto));
            }
        });
    });
    
    // Cambiar método de pago en modal
    const metodoPago = document.getElementById('metodo-pago-factura');
    if (metodoPago) {
        metodoPago.addEventListener('change', function() {
            cambiarMetodoPagoFactura(this.value);
        });
    }
    
    // Confirmar pago de factura
    const btnConfirmar = document.getElementById('btn-confirmar-pago-factura');
    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', confirmarPagoFactura);
    }
    
    // Guardar nuevo método de pago
    const btnGuardarMetodo = document.getElementById('btn-guardar-metodo');
    if (btnGuardarMetodo) {
        btnGuardarMetodo.addEventListener('click', guardarMetodoPago);
    }
    
    // Botones de exportar
    const btnExportar = document.querySelector('#modalExportar .btn-success');
    if (btnExportar) {
        btnExportar.addEventListener('click', function() {
            alert('Reporte exportado exitosamente (simulado)');
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalExportar'));
            if (modal) modal.hide();
        });
    }
    
    // Botones de eliminar método de pago
    const botonesEliminar = document.querySelectorAll('.btn-outline-danger');
    botonesEliminar.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const metodoItem = this.closest('.metodo-pago-item');
            const metodoNombre = metodoItem.querySelector('.fw-semibold')?.textContent;
            
            if (confirm(`¿Estás seguro de eliminar el método de pago "${metodoNombre}"?`)) {
                metodoItem.style.opacity = '0.5';
                setTimeout(() => {
                    metodoItem.remove();
                    alert('Método de pago eliminado (simulado)');
                }, 300);
            }
        });
    });
});

function filtrarTabla(filtro) {
    const filas = document.querySelectorAll('#tabla-facturas tbody tr');
    
    filas.forEach(fila => {
        const estado = fila.querySelector('.badge')?.textContent.toLowerCase().trim();
        
        if (filtro === 0) { // Todas
            fila.style.display = '';
        } else if (filtro === 1) { // Pendientes
            fila.style.display = estado === 'pendiente' ? '' : 'none';
        } else if (filtro === 2) { // Pagadas
            fila.style.display = estado === 'pagada' ? '' : 'none';
        }
    });
}

function actualizarModalPago(facturaId, monto) {
    const modalFacturaId = document.getElementById('modal-factura-id');
    const modalMonto = document.getElementById('modal-factura-monto');
    
    if (modalFacturaId) {
        modalFacturaId.textContent = facturaId;
    }
    
    if (modalMonto) {
        modalMonto.textContent = `$${monto.toLocaleString('es-CO')}`;
    }
    
    // Resetear método de pago a Nequi (predeterminado)
    const metodoPago = document.getElementById('metodo-pago-factura');
    if (metodoPago) {
        metodoPago.value = 'nequi';
        cambiarMetodoPagoFactura('nequi');
    }
}

function cambiarMetodoPagoFactura(metodo) {
    // Ocultar todos los paneles de información
    document.querySelectorAll('#modalPagarFactura .pago-info').forEach(el => {
        el.classList.add('d-none');
    });
    
    // Mostrar el panel correspondiente
    const infoPanel = document.querySelector('#modalPagarFactura .pago-info');
    if (infoPanel) {
        infoPanel.classList.remove('d-none');
        
        // Actualizar contenido según método
        const icono = infoPanel.querySelector('i');
        const numero = infoPanel.querySelector('strong');
        
        if (metodo === 'nequi') {
            icono.className = 'bi bi-phone me-2';
            numero.textContent = '300 123 4567';
            infoPanel.querySelector('p:first-child').innerHTML = '<i class="bi bi-phone me-2"></i>Número Nequi: <strong>300 123 4567</strong>';
        } else if (metodo === 'daviplata') {
            icono.className = 'bi bi-phone me-2';
            numero.textContent = '300 765 4321';
            infoPanel.querySelector('p:first-child').innerHTML = '<i class="bi bi-phone me-2"></i>Número DaviPlata: <strong>300 765 4321</strong>';
        } else if (metodo === 'bancolombia') {
            icono.className = 'bi bi-bank me-2';
            numero.textContent = '123-456789-01';
            infoPanel.querySelector('p:first-child').innerHTML = '<i class="bi bi-bank me-2"></i>Cuenta Bancolombia: <strong>123-456789-01</strong>';
        } else if (metodo === 'tarjeta') {
            icono.className = 'bi bi-credit-card me-2';
            numero.textContent = '**** **** **** 1234';
            infoPanel.querySelector('p:first-child').innerHTML = '<i class="bi bi-credit-card me-2"></i>Tarjeta: <strong>**** **** **** 1234</strong>';
        }
    }
}

function confirmarPagoFactura() {
    const facturaId = document.getElementById('modal-factura-id')?.textContent;
    const metodo = document.getElementById('metodo-pago-factura')?.value;
    
    if (!facturaId || !metodo) {
        alert('Error al procesar el pago. Intenta de nuevo.');
        return;
    }
    
    // Simular procesamiento de pago
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Procesando pago...',
            text: 'Por favor espera mientras confirmamos tu transacción.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Simular tiempo de procesamiento
        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: '¡Pago exitoso!',
                text: `La factura ${facturaId} ha sido pagada correctamente.`,
                timer: 3000,
                showConfirmButton: false
            }).then(() => {
                // Cerrar modal y actualizar tabla
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalPagarFactura'));
                if (modal) modal.hide();
                
                // Marcar factura como pagada en la tabla (simulado)
                actualizarEstadoFactura(facturaId);
            });
        }, 2000);
    } else {
        alert(`Pago de factura ${facturaId} procesado (simulado)`);
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalPagarFactura'));
        if (modal) modal.hide();
    }
}

function actualizarEstadoFactura(facturaId) {
    // Buscar la fila de la factura por el ID
    const filas = document.querySelectorAll('#tabla-facturas tbody tr');
    
    filas.forEach(fila => {
        if (fila.querySelector('td:first-child')?.textContent.trim() === facturaId) {
            // Actualizar badge de estado
            const badge = fila.querySelector('.badge');
            if (badge) {
                badge.className = 'badge bg-success';
                badge.textContent = 'pagada';
            }
            
            // Actualizar método de pago
            const metodoCell = fila.querySelector('td:nth-child(7)');
            if (metodoCell) {
                metodoCell.innerHTML = '<span class="text-muted small">Nequi</span>';
            }
            
            // Ocultar botón de pago
            const btnPagar = fila.querySelector('.btn-success');
            if (btnPagar) {
                btnPagar.remove();
            }
            
            // Quitar clase de pendiente
            fila.classList.remove('factura-pendiente');
            
            // Actualizar contadores de las tarjetas
            actualizarContadores();
        }
    });
}

function actualizarContadores() {
    // Simular actualización de contadores de tarjetas
    const pendientesActuales = document.querySelectorAll('#tabla-facturas tbody tr .badge.bg-warning').length;
    const pagadasActuales = document.querySelectorAll('#tabla-facturas tbody tr .badge.bg-success').length;
    
    const tarjetaPendientes = document.querySelector('.tarjeta-facturacion:nth-child(2) .facturacion-valor');
    const tarjetaPagadas = document.querySelector('.tarjeta-facturacion:nth-child(3) .facturacion-valor');
    
    if (tarjetaPendientes) {
        tarjetaPendientes.textContent = pendientesActuales;
    }
    
    if (tarjetaPagadas) {
        tarjetaPagadas.textContent = pagadasActuales;
    }
}

function guardarMetodoPago() {
    const form = document.getElementById('form-agregar-metodo');
    const tipo = document.getElementById('tipo-metodo')?.value;
    const numero = document.querySelector('#form-agregar-metodo input[type="text"]')?.value;
    const titular = document.querySelectorAll('#form-agregar-metodo input[type="text"]')[1]?.value;
    
    if (!tipo || !numero || !titular) {
        alert('Por favor completa todos los campos');
        return;
    }
    
    // Simular guardado
    alert('Método de pago guardado correctamente (simulado)');
    
    // Cerrar modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalAgregarMetodo'));
    if (modal) modal.hide();
    
    // Limpiar formulario
    form.reset();
}

// Función para formatear moneda
function formatearMoneda(valor) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(valor);
}