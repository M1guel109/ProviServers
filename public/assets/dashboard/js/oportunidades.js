document.addEventListener('DOMContentLoaded', function() {
    
    // Referencia al modal de cotización
    const modalCotizar = document.getElementById('modalCotizar');

    if (modalCotizar) {
        modalCotizar.addEventListener('show.bs.modal', function(event) {
            // 1. Botón que activó el modal
            const button = event.relatedTarget;
            
            // 2. Extraer info de los atributos data-*
            const idNecesidad = button.getAttribute('data-id');
            const titulo = button.getAttribute('data-titulo');

            // 3. Llenar los campos dentro del modal
            const inputId = modalCotizar.querySelector('#modal_necesidad_id');
            const labelTitulo = modalCotizar.querySelector('#modal_titulo_necesidad');

            inputId.value = idNecesidad;
            labelTitulo.textContent = titulo;

            // (Opcional) Limpiar campos de precio y mensaje al abrir
            modalCotizar.querySelector('input[name="precio_oferta"]').value = '';
            modalCotizar.querySelector('textarea[name="mensaje"]').value = '';
        });
    }
});