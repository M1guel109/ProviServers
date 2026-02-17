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

function cargarDetalleMembresia(tipo, descripcion, costo, duracion, estado, maxServicios, destacado, videos, stats) {

    // 1. Llenar textos básicos
    document.getElementById('modal-titulo').innerText = tipo;
    document.getElementById('modal-descripcion').innerText = descripcion;
    document.getElementById('modal-duracion').innerText = duracion + " días";
    document.getElementById('modal-servicios').innerText = maxServicios + " publicaciones";

    // 2. Formatear Moneda (Pesos Colombianos)
    const formatoMoneda = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 });
    document.getElementById('modal-costo').innerText = formatoMoneda.format(costo);

    // 3. Badge de Estado
    const divEstado = document.getElementById('modal-estado-badge');
    if (estado === 'ACTIVO') {
        divEstado.innerHTML = '<span class="badge bg-success px-3 py-2 rounded-pill">Activo</span>';
    } else {
        divEstado.innerHTML = '<span class="badge bg-danger px-3 py-2 rounded-pill">Inactivo</span>';
    }

    // 4. Lógica de Iconos (Check Verde / Cruz Roja) para los beneficios
    const iconCheck = '<i class="bi bi-check-circle-fill text-success fs-5"></i>';
    const iconX = '<i class="bi bi-x-circle-fill text-secondary opacity-50 fs-5"></i>';

    document.getElementById('check-destacado').innerHTML = (destacado == 1) ? iconCheck : iconX;
    document.getElementById('check-videos').innerHTML = (videos == 1) ? iconCheck : iconX;
    document.getElementById('check-stats').innerHTML = (stats == 1) ? iconCheck : iconX;
}

document.addEventListener("DOMContentLoaded", () => {

    // --- CONFIGURACIÓN A: TABLA PRINCIPAL (#tabla) ---
    // Solo búsqueda y paginación. Diseño limpio.
    let tableMain = new DataTable('#tabla', {
        responsive: true,
        pageLength: 10,
        scrollX: true,
        layout: {
            topStart: 'search',
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        language: {
            url: "https://cdn.datatables.net/plug-ins/2.0.2/i18n/es-ES.json"
        },
        // Personalización del input de búsqueda (Lupa)
        initComplete: function () {
            styleSearchInput('#tabla_wrapper');
        }
    });

    // --- CONFIGURACIÓN B: TABLA EXPORTACIÓN (#tabla-1) ---
    // Con botones, sin paginación (para exportar todo).
    let tableExport = new DataTable('#tabla-1', {
        responsive: true,
        paging: false, // ¡Importante! Para exportar todos los datos, no solo la página actual
        layout: {
            topStart: {
                buttons: [
                    {
                        extend: 'copy',
                        text: '<i class="bi bi-clipboard"></i> Copiar',
                        className: 'btn btn-outline-secondary btn-sm',
                    },
                    {
                        extend: 'excel',
                        text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Reporte_Usuarios'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        title: 'Reporte_Usuarios',
                        orientation: 'landscape'
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer"></i> Imprimir',
                        className: 'btn btn-info btn-sm text-white',
                    }
                ]
            },
            topEnd: 'search'
        },
        language: {
            url: "https://cdn.datatables.net/plug-ins/2.0.2/i18n/es-ES.json"
        },
        initComplete: function () {
            styleSearchInput('#tabla-1_wrapper');
        }
    });

    // --- FUNCIÓN AUXILIAR: Estilar el buscador con la lupa ---
    function styleSearchInput(wrapperSelector) {
        const wrapper = document.querySelector(wrapperSelector);
        if (!wrapper) return;

        const dtSearch = wrapper.querySelector('.dt-search');
        if (!dtSearch) return;

        const input = dtSearch.querySelector('input[type="search"]');
        if (!input) return;

        // Crear contenedor personalizado
        const buscadorDiv = document.createElement('div');
        buscadorDiv.className = 'buscador'; // Clase definida en tu CSS
        buscadorDiv.innerHTML = `<i class="bi bi-search"></i>`;
        buscadorDiv.appendChild(input);

        // Limpiar y agregar nuevo input
        dtSearch.innerHTML = '';
        dtSearch.appendChild(buscadorDiv);

        // Estilos inline para asegurar compatibilidad
        input.setAttribute('placeholder', 'Buscar...');
        input.style.width = "100%";
        input.style.border = "none";
        input.style.background = "transparent";
        input.style.outline = "none";
        input.style.paddingLeft = "10px";
    }

    // --- CORRECCIÓN DE PESTAÑAS (Tabs) ---
    // Ajusta las columnas cuando se cambia de pestaña para evitar deformaciones
    const tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabEls.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (event) {
            // Ajustar columnas de ambas tablas
            tableMain.columns.adjust().responsive.recalc();
            tableExport.columns.adjust().responsive.recalc();
        });
    });

});

// Variable global para la URL (si no la tienes definida ya)
// const BASE_URL = "http://localhost/ProviServers"; 

function cargarDetalleSuscripcion(id) {
    const modal = new bootstrap.Modal(document.getElementById('modalDetalleSuscripcion'));
    modal.show();

    // 1. Resetear UI
    document.getElementById('loader-sub').classList.remove('d-none');
    document.getElementById('contenido-sub').classList.add('d-none');

    // 2. Petición AJAX (Ajusta la URL a tu estructura de rutas)
    // Opción A: Si usas rutas amigables
    // const url = `${BASE_URL}/admin/api/suscripcion-detalle?id=${id}`;
    // Opción B: Si usas parámetros GET en controlador principal
    const url = `${BASE_URL}/app/controllers/SuscripcionController.php?action=detalle&id=${id}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                Swal.fire('Error', data.error, 'error');
                return;
            }
            
            llenarModalSuscripcion(data);

            document.getElementById('loader-sub').classList.add('d-none');
            document.getElementById('contenido-sub').classList.remove('d-none');
        })
        .catch(error => {
            console.error(error);
            Swal.fire('Error', 'No se pudo cargar la información', 'error');
        });
}

function llenarModalSuscripcion(data) {
    // Foto y Datos Proveedor
    const rutaFoto = `${BASE_URL}/public/uploads/usuarios/${data.foto_proveedor || 'default_user.png'}`;
    document.getElementById('modal-sub-foto').src = rutaFoto;
    document.getElementById('modal-sub-proveedor').textContent = data.nombre_proveedor;
    document.getElementById('modal-sub-email').textContent = data.email;

    // Estado Badge
    const badge = document.getElementById('modal-sub-estado-badge');
    badge.textContent = data.estado.toUpperCase();
    badge.className = 'badge rounded-pill px-3 py-2 fs-6'; // Reset clases
    
    if (data.estado.toLowerCase() === 'activa') {
        badge.classList.add('bg-success');
    } else if (data.estado.toLowerCase() === 'vencida') {
        badge.classList.add('bg-danger');
    } else {
        badge.classList.add('bg-secondary');
    }

    // Datos del Plan
    document.getElementById('modal-sub-plan').textContent = data.nombre_plan;
    document.getElementById('modal-sub-id').textContent = `#${data.id}`;
    
    // Formato Moneda COP
    const formatoMoneda = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 });
    document.getElementById('modal-sub-costo').textContent = formatoMoneda.format(data.costo);

    // Fechas
    document.getElementById('modal-sub-inicio').textContent = data.fecha_inicio;
    const divFin = document.getElementById('modal-sub-fin');
    divFin.textContent = data.fecha_fin;

    // Cálculo Días Restantes
    const fechaFin = new Date(data.fecha_fin_raw); // Usamos la fecha cruda (YYYY-MM-DD)
    const hoy = new Date();
    const diffTime = fechaFin - hoy;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 

    const spanRestante = document.getElementById('modal-sub-restante');
    if (diffDays < 0) {
        spanRestante.textContent = `Venció hace ${Math.abs(diffDays)} días`;
        spanRestante.className = 'badge bg-danger text-white px-3 py-2 rounded-pill';
        divFin.classList.add('text-danger');
    } else {
        spanRestante.textContent = `Quedan ${diffDays} días`;
        spanRestante.className = 'badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill';
        divFin.classList.remove('text-danger');
    }
}