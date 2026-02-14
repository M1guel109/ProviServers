document.addEventListener('DOMContentLoaded', () => {
    
    // Previsualización de Imagen
    const inputIcono = document.getElementById('icono-input');
    const imgPreview = document.getElementById('foto-preview');

    if (inputIcono && imgPreview) {
        inputIcono.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Verificamos que sea imagen
                if (!file.type.startsWith('image/')) {
                    Swal.fire('Error', 'El archivo debe ser una imagen', 'error');
                    this.value = ''; // Limpiar input
                    return;
                }

                // Crear URL temporal para mostrarla
                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }
});