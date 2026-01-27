document.addEventListener('DOMContentLoaded', function() {
    
    // Referencia al modal de respuesta
    const modalResponder = document.getElementById('modalResponder');
    
    if (modalResponder) {
        // Escuchamos el evento "cuando el modal se va a mostrar" de Bootstrap
        modalResponder.addEventListener('show.bs.modal', function(event) {
            
            // 1. Identificamos qué botón disparó el modal
            const boton = event.relatedTarget;
            
            // 2. Sacamos el ID de la reseña del atributo 'data-id'
            const idValoracion = boton.getAttribute('data-id');
            
            // 3. Lo metemos dentro del input oculto del formulario
            const inputId = modalResponder.querySelector('#modal_id_valoracion');
            inputId.value = idValoracion;
            
            // (Opcional) Limpiar el textarea por si había texto viejo
            const textArea = modalResponder.querySelector('#texto_respuesta');
            textArea.value = '';
            
            // console.log("Respondiendo a la valoración ID:", idValoracion); // Para depurar
        });
    }
});