/* categoria.js — Previsualización de ícono y confirmación de eliminación */

document.addEventListener('DOMContentLoaded', () => {

    // =======================================================
    // 1. PREVISUALIZACIÓN DE ÍCONO
    // =======================================================
    const inputIcono = document.getElementById('icono-input');
    const imgPreview = document.getElementById('foto-preview');

    if (inputIcono && imgPreview) {
        inputIcono.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            // Validar que sea imagen
            if (!file.type.startsWith('image/')) {
                Swal.fire('Formato inválido', 'El archivo debe ser una imagen.', 'error');
                this.value = '';
                return;
            }

            // Previsualizar
            const reader = new FileReader();
            reader.onload = (ev) => imgPreview.src = ev.target.result;
            reader.readAsDataURL(file);
        });
    }

    // =======================================================
    // 2. CONFIRMACIÓN DE ELIMINACIÓN
    // Usada desde gestionar-categorias.php via onclick
    // =======================================================
    window.confirmarEliminacion = function (url) {
        Swal.fire({
            title: '¿Eliminar categoría?',
            text: 'Si tiene servicios asociados no se podrá eliminar.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    };

});