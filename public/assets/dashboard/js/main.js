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
    // 2. LÓGICA DE BREADCRUMBS (Corregida)
    // ==========================================
    const breadcrumb = document.getElementById("breadcrumb");

    if (breadcrumb) {
        breadcrumb.innerHTML = "";

        // 1. Obtener segmentos limpios
        const path = window.location.pathname
            .split("/")
            .filter(segment => segment !== "" && segment !== "proviservers");

        // 2. Agregar "Inicio" (Link al Dashboard)
        const homeItem = document.createElement("li");
        homeItem.className = "breadcrumb-item";

        const homeLink = document.createElement("a");
        homeLink.href = PROJECT_URL + "/admin/dashboard";
        homeLink.innerHTML = '<i class="bi bi-house-door-fill"></i>'; // Icono de casa se ve mejor
        // homeLink.textContent = "Inicio"; // O usa texto si prefieres

        homeItem.appendChild(homeLink);
        breadcrumb.appendChild(homeItem);

        // 3. Variable acumuladora (Inicia con la base del proyecto)
        let rutaAcumulada = PROJECT_URL;

        path.forEach((segmento, index) => {

            // A. Construimos la ruta REAL siempre (Vital para que los enlaces funcionen)
            rutaAcumulada += `/${segmento}`;

            // B. FILTROS VISUALES (Aquí decidimos qué NO mostrar)

            // 1. Si es "admin", no lo mostramos en texto, pero ya lo sumamos a la ruta arriba.
            if (segmento === 'admin') {
                return;
            }

            // 2. Si es un número (ID), no lo mostramos (Ej: /editar-usuario/45 -> No mostrar "45")
            if (!isNaN(segmento)) {
                return;
            }

            // 3. Si es "dashboard", no lo mostramos porque ya pusimos el icono de "Inicio"
            if (segmento === 'dashboard') {
                return;
            }

            // C. CREAR EL ELEMENTO VISUAL
            const item = document.createElement("li");
            item.classList.add("breadcrumb-item");

            // Formatear texto (Quitar guiones y capitalizar)
            const texto = decodeURIComponent(segmento)
                .replace(/-/g, " ")
                .replace(/\b\w/g, l => l.toUpperCase());

            // D. DECIDIR SI ES ENLACE O TEXTO PLANO
            const esUltimo = index === path.length - 1;

            if (!esUltimo) {
                const link = document.createElement("a");
                link.href = rutaAcumulada;
                link.textContent = texto;
                item.appendChild(link);
            } else {
                // El último elemento no lleva enlace
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

    // ==========================================
    // 4. MENÚ MÓVIL - CIERRE
    // ==========================================
    const closeMenuMobile = document.getElementById('closeMenuMobile');
    const sidebar = document.querySelector('.sidebar');

    if (closeMenuMobile && sidebar) {
        // Cerrar menú con botón X
        closeMenuMobile.addEventListener('click', () => {
            document.body.classList.remove('toggle-sidebar');
        });

        // Cerrar al hacer clic fuera del menú
        document.addEventListener('click', (e) => {
            if (document.body.classList.contains('toggle-sidebar')) {
                if (!sidebar.contains(e.target) && !btnToggle?.contains(e.target)) {
                    document.body.classList.remove('toggle-sidebar');
                }
            }
        });

        // Cerrar con tecla ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && document.body.classList.contains('toggle-sidebar')) {
                document.body.classList.remove('toggle-sidebar');
            }
        });
    }

    // Al cambiar tamaño de pantalla
    window.addEventListener('resize', () => {
        if (window.innerWidth > 1024) {
            document.body.classList.remove('toggle-sidebar');
        }
    });

    // ==========================================
    // 5. CERRAR SIDEBAR AL HACER CLIC EN UN ENLACE (MÓVIL)
    // ==========================================
    const sidebarLinks = document.querySelectorAll('.sidebar a');

    sidebarLinks.forEach(link => {
        link.addEventListener('click', () => {
            // Solo cerrar en móvil (cuando el sidebar está en modo overlay)
            if (window.innerWidth <= 1024) {
                // Pequeño retraso para permitir la navegación
                setTimeout(() => {
                    document.body.classList.remove('toggle-sidebar');
                }, 100);
            }
        });
    });

    // ==========================================
    // 6. MARCAR EL ENLACE ACTIVO SEGÚN LA URL
    // ==========================================
    function setActiveLink() {
        const currentUrl = window.location.href;
        const links = document.querySelectorAll('.sidebar a');

        links.forEach(link => {
            link.classList.remove('active');

            // Verificar si el href del link está en la URL actual
            const linkHref = link.getAttribute('href');
            if (linkHref && currentUrl.includes(linkHref) && linkHref !== '#') {
                link.classList.add('active');

                // Si está dentro de un submenú, abrir el submenú
                const parentSubmenu = link.closest('.submenu');
                if (parentSubmenu) {
                    const parentHasSubmenu = parentSubmenu.closest('.has-submenu');
                    if (parentHasSubmenu) {
                        parentHasSubmenu.classList.add('active');
                    }
                }
            }
        });
    }

    // Llamar al cargar la página
    setActiveLink();
});

