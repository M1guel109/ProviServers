// Función para verificar si un campo requerido en un paso está vacío
function validateStep(stepId) {
    const step = document.getElementById(stepId);
    let isValid = true;

    // Limpiar validaciones previas
    step.querySelectorAll('.is-invalid').forEach(field => field.classList.remove('is-invalid'));

    // Buscar todos los elementos de formulario requeridos dentro del paso activo
    const requiredFields = step.querySelectorAll('[required]');

    requiredFields.forEach(field => {
        // Si es un campo de texto/número/textarea/select y está vacío
        if ((field.type !== 'radio' && field.type !== 'checkbox') && !field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        }
        // Para radios, verificar si alguno dentro del mismo grupo está seleccionado
        else if (field.type === 'radio') {
            const name = field.name;
            // Seleccionar todos los radios con el mismo nombre en el paso actual
            const radios = step.querySelectorAll(`input[name="${name}"][required]`);
            const isChecked = Array.from(radios).some(radio => radio.checked);

            if (!isChecked) {
                // En lugar de marcar el radio, puedes marcar un contenedor si lo tuvieras, 
                // pero para simplicidad, solo fallamos la validación.
                isValid = false;
                // Puedes añadir una clase de error visual al grupo si fuera necesario
                // console.log(`Radio group ${name} is not checked.`); 
            }
        }
        // Si el campo tiene valor, quitar la clase de invalidación (si existe, para revalidación)
        else if (field.classList.contains('is-invalid')) {
            field.classList.remove('is-invalid');
        }
    });

    return isValid;
}

// Inicialización del Wizard
document.addEventListener('DOMContentLoaded', () => {
    let currentStep = 1;
    const totalSteps = 2;
    const steps = document.querySelectorAll('.wizard-step');
    const indicators = document.querySelectorAll('.step-indicator');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');

    // Asegura que solo el primer paso esté visible al inicio
    steps.forEach((step, index) => {
        if (index + 1 !== currentStep) {
            step.classList.add('d-none');
        } else {
            step.classList.remove('d-none');
        }
    });


    function updateWizardDisplay() {
        // Ocultar todos los pasos y actualizar clases
        steps.forEach((step, index) => {
            const stepNum = index + 1;
            if (stepNum === currentStep) {
                step.classList.remove('d-none');
                step.classList.add('active'); // Para CSS de transición si se usa
            } else {
                step.classList.add('d-none');
                step.classList.remove('active');
            }
        });

        // Actualizar indicadores
        indicators.forEach(indicator => {
            const stepNum = parseInt(indicator.dataset.step);
            if (stepNum === currentStep) {
                indicator.classList.add('active');
            } else {
                indicator.classList.remove('active');
            }
        });

        // Control de botones de navegación
        prevBtn.style.display = (currentStep === 1) ? 'none' : 'block';
        nextBtn.style.display = (currentStep === totalSteps) ? 'none' : 'block';
        submitBtn.style.display = (currentStep === totalSteps) ? 'block' : 'none';
    }

    nextBtn.addEventListener('click', () => {
        // Validar el paso actual antes de avanzar
        if (validateStep(`step-${currentStep}`)) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateWizardDisplay();
            }
        } else {
            // En un entorno de producción, aquí se podría mostrar un mensaje toast o modal.
            console.error('Por favor, completa todos los campos requeridos en este paso.');
        }
    });

    prevBtn.addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            updateWizardDisplay();
        }
    });

    // Permitir clic en el indicador para ir a pasos anteriores ya visitados
    indicators.forEach(indicator => {
        indicator.addEventListener('click', (e) => {
            const stepToGo = parseInt(e.currentTarget.dataset.step);
            // Permitir ir atrás sin validación
            if (stepToGo < currentStep) {
                currentStep = stepToGo;
                updateWizardDisplay();
            }
            // Permitir ir adelante si ya está validado (o validar si es el siguiente)
            else if (stepToGo === currentStep + 1 && validateStep(`step-${currentStep}`)) {
                currentStep = stepToGo;
                updateWizardDisplay();
            }
        });
    });

    // Asegurar que el paso 1 se muestra correctamente al cargar
    updateWizardDisplay();
});