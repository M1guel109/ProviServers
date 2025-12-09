document.addEventListener('DOMContentLoaded', function () {
    const rolSelect = document.getElementById('rol');
    const documentosDiv = document.getElementById('documentos-proveedor-admin');
    // Campos de archivo obligatorios para el proveedor
    const proveedorDocs = documentosDiv.querySelectorAll('.file-doc-proveedor');

    function toggleDocumentos() {
        if (rolSelect.value === 'proveedor') {
            documentosDiv.classList.remove('d-none');
            // Establecer como requeridos los campos de Cédula, Selfie y Antecedentes cuando es proveedor
            proveedorDocs.forEach(input => {
                // El certificado es opcional, así que no se toca.
                if (input.id !== 'doc-certificado') {
                    input.required = true;
                }
            });
        } else {
            documentosDiv.classList.add('d-none');
            // Remover el atributo required cuando no es proveedor
            proveedorDocs.forEach(input => {
                input.required = false;
            });
        }
    }

    // Escuchar el cambio en el select de Rol
    rolSelect.addEventListener('change', toggleDocumentos);

    // Ejecutar al cargar para asegurar el estado inicial correcto
    toggleDocumentos();

    // Lógica para previsualizar la foto de perfil
    const fotoInput = document.getElementById('foto-input');
    const fotoPreview = document.getElementById('foto-preview');

    if (fotoInput && fotoPreview) {
        fotoInput.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    fotoPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});