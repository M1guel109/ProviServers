/**
 * Lógica para la página de restablecer contraseña.
 * Incluye:
 * 1. Manejo de la visibilidad del mensaje de éxito (usando parámetro de URL).
 * 2. Inicialización del carrusel de Bootstrap.
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // --- Lógica del Mensaje de Éxito ---
    const successMessage = document.getElementById('successMessage');

    // Obtener los parámetros de la URL para verificar si la acción fue exitosa
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

    // Si el controlador PHP redirigió con "?status=success", mostramos el mensaje.
    if (status === 'success') {
        if (successMessage) {
            successMessage.style.display = 'block';
        }
        
        // Opcional: limpiar la URL después de mostrar el mensaje para evitar recargas accidentales
        // history.replaceState(null, '', window.location.pathname);
    }

    // --- Inicialización del Carrusel (Carousel) ---
    // Asegúrate de que el carrusel se inicie y tenga auto-play
    var myCarouselElement = document.querySelector('#infoCarousel');
    if (myCarouselElement) {
        var carousel = new bootstrap.Carousel(myCarouselElement, {
            // Cambia la imagen automáticamente cada 5 segundos
            interval: 5000, 
            wrap: true 
        });
    }
});