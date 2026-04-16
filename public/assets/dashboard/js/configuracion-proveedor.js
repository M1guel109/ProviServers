document.addEventListener('DOMContentLoaded', function() {
    const modalInput = document.getElementById('confirmarEliminarModal');
    const confirmBtn = document.getElementById('btnConfirmarEliminarModal');
    const formModal = document.getElementById('formEliminarCuentaModal');

    // Habilitar botón solo cuando se escribe "ELIMINAR"
    if (modalInput && confirmBtn) {
        modalInput.addEventListener('input', function() {
            confirmBtn.disabled = this.value !== 'ELIMINAR';
        });
    }

    // Enviar el formulario al confirmar
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (confirmBtn.disabled === false) {
                formModal.submit();
            }
        });
    }
});