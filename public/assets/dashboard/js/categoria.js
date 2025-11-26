document.addEventListener('DOMContentLoaded', function() {
    const inputFile = document.getElementById('icono-input');
    const previewImage = document.getElementById('foto-preview');
    const defaultIconPath = '<?= BASE_URL ?>/public/uploads/categorias/default_icon.png';

    if (inputFile && previewImage) {
        inputFile.addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                }
                reader.readAsDataURL(file);
            } else {
                // Si el usuario cancela la selección, se restaura el ícono por defecto
                previewImage.src = defaultIconPath; 
            }
        });
    }
});