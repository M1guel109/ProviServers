/* ======================================================
   membresia.js - Funcionalidad para gestión de membresías
   ====================================================== */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Membresía cargada');
    
    // Manejar selección de plan en modal
    const botonesSeleccionar = document.querySelectorAll('.btn-seleccionar-plan, .btn-cambiar-plan');
    botonesSeleccionar.forEach(btn => {
        btn.addEventListener('click', function() {
            const plan = this.getAttribute('data-plan');
            const precio = this.getAttribute('data-precio');
            
            if (plan && precio) {
                actualizarModalPago(plan, parseInt(precio));
            }
        });
    });
    
    // Cambiar método de pago
    const metodoPago = document.getElementById('metodo-pago');
    if (metodoPago) {
        metodoPago.addEventListener('change', function() {
            cambiarMetodoPago(this.value);
        });
    }
    
    // Confirmar pago
    const btnConfirmar = document.getElementById('btn-confirmar-pago');
    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', confirmarPago);
    }
});

function actualizarModalPago(plan, precio) {
    const modalPlan = document.getElementById('modal-plan-seleccionado');
    const modalPrecio = document.getElementById('modal-precio-plan');
    const modalIva = document.getElementById('modal-iva');
    const modalTotal = document.getElementById('modal-total');
    
    if (modalPlan) {
        modalPlan.textContent = `Plan ${plan}`;
    }
    
    if (modalPrecio) {
        modalPrecio.textContent = `$${precio.toLocaleString('es-CO')}/mes`;
    }
    
    // Calcular IVA (19%)
    const iva = Math.round(precio * 0.19);
    const total = precio + iva;
    
    if (modalIva) {
        modalIva.textContent = `$${iva.toLocaleString('es-CO')}`;
    }
    
    if (modalTotal) {
        modalTotal.textContent = `$${total.toLocaleString('es-CO')}`;
    }
}

function cambiarMetodoPago(metodo) {
    // Ocultar todos los paneles de información
    document.querySelectorAll('.pago-info').forEach(el => {
        el.classList.add('d-none');
    });
    
    // Mostrar el panel correspondiente
    const panel = document.getElementById(`${metodo}-info`);
    if (panel) {
        panel.classList.remove('d-none');
    }
}

function confirmarPago() {
    const metodo = document.getElementById('metodo-pago')?.value;
    const plan = document.getElementById('modal-plan-seleccionado')?.textContent;
    
    if (!metodo || !plan) {
        alert('Error al procesar el pago. Intenta de nuevo.');
        return;
    }
    
    // Simular procesamiento de pago
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
            text: `Tu cambio al ${plan} ha sido procesado correctamente.`,
            timer: 3000,
            showConfirmButton: false
        }).then(() => {
            // Cerrar modal y redirigir o recargar
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarPago'));
            if (modal) modal.hide();
            
            // Opcional: recargar la página para mostrar nuevo plan
            // setTimeout(() => location.reload(), 500);
        });
    }, 2000);
}

// Función para formatear moneda (útil si se necesita)
function formatearMoneda(valor) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(valor);
}

// Inicializar tooltips de Bootstrap
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.map(function(tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});