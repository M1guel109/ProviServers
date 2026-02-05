/* main.js - Lógica general del sitio */

// 1. Definir BASE_URL si no existe (Parche de seguridad)
// Lo ideal es que definas "const BASE_URL = '...'" en tu HTML antes de cargar este script
const PROJECT_URL = (typeof BASE_URL !== 'undefined') ? BASE_URL : '/proviservers'; // Ajusta '/proviservers' si es necesario

document.addEventListener("DOMContentLoaded", () => {
    
// ==========================================
    // 1. LÓGICA DEL SIDEBAR (Con Memoria)
    // ==========================================
    const btnToggle = document.getElementById("btn-toggle-menu"); // Asegúrate que el ID coincida

    // 1. Revisar si el usuario ya lo había plegado antes
    if (localStorage.getItem('sidebar-collapsed') === 'true') {
        document.body.classList.add('toggle-sidebar');
    }

    if (btnToggle) {
        btnToggle.addEventListener("click", () => {
            document.body.classList.toggle("toggle-sidebar");
            
            // 2. Guardar la preferencia del usuario
            if (document.body.classList.contains('toggle-sidebar')) {
                localStorage.setItem('sidebar-collapsed', 'true');
            } else {
                localStorage.setItem('sidebar-collapsed', 'false');
            }
        });
    }

    // ==========================================
    // 2. LÓGICA DE BREADCRUMBS (Ruta de navegación)
    // ==========================================
    const breadcrumb = document.getElementById("breadcrumb");
    
    if (breadcrumb) { // Solo se ejecuta si existe el breadcrumb en esta vista
        breadcrumb.innerHTML = ""; 

        // Obtener ruta y limpiar
        const path = window.location.pathname
            .split("/")
            .filter(segment => segment !== "" && segment !== "proviservers"); // Filtramos la carpeta raíz si es necesario

        // Agregar enlace Inicio
        const homeItem = document.createElement("li");
        homeItem.className = "breadcrumb-item";
        
        const homeLink = document.createElement("a");
        homeLink.href = PROJECT_URL + "/admin/dashboard"; // Usamos la URL del proyecto
        homeLink.textContent = "Inicio";
        
        homeItem.appendChild(homeLink);
        breadcrumb.appendChild(homeItem);

        // Construir el resto
        let rutaAcumulada = PROJECT_URL;
        
        path.forEach((segmento, index) => {
            // Ignorar segmentos numéricos (IDs) o 'admin' si ya está en Inicio
            if(segmento === 'admin') return; 

            rutaAcumulada += `/${segmento}`;
            const item = document.createElement("li");
            item.classList.add("breadcrumb-item");

            // Formatear texto (primera mayúscula, quitar guiones)
            const texto = decodeURIComponent(segmento)
                .replace(/-/g, " ")
                .replace(/\b\w/g, l => l.toUpperCase());

            if (index < path.length - 1) {
                const link = document.createElement("a");
                link.href = rutaAcumulada;
                link.textContent = texto;
                item.appendChild(link);
            } else {
                item.textContent = texto;
                item.classList.add("active");
                item.setAttribute("aria-current", "page");
            }
            breadcrumb.appendChild(item);
        });
    }

    // ==========================================
    // 3. PREVISUALIZACIÓN DE IMAGEN (Solo formularios)
    // ==========================================
    const fotoInput = document.getElementById('foto-input');
    const fotoPreview = document.getElementById('foto-preview');

    // IMPORTANTE: El if verifica que AMBOS existan antes de intentar hacer nada
    if (fotoInput && fotoPreview) {
        
        // Guardamos la imagen original por si cancela
        const defaultImage = fotoPreview.src; 

        fotoInput.addEventListener('change', function (e) {
            const file = e.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    fotoPreview.src = event.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                // Si cancela, volvemos a la que estaba al cargar la página
                fotoPreview.src = defaultImage; 
            }
        });
    }
});