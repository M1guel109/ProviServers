<aside class="sidebar">
    <div class="logo">
        <a href="#">
            <!-- Se asume que /public/assets/img/logos/LOGO PRINCIPAL.png es la ruta estática -->
            <img src="<?= BASE_URL ?>/public/assets/img/logos/LOGO PRINCIPAL.png" alt="Logo Proviservers" class="logo-completo">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/FAVICON.png" alt="Logo Proviservers" class="logo-favicon">
        </a>
    </div>

    <!-- Menú principal -->
    <nav class="menu-principal">
        <ul>
            <!-- 1. Panel Principal (Clase 'active' para demostrar el estado actual) -->
            <li>
                <a href="<?= BASE_URL ?>/admin/dashboard" class="active" data-title="Dashboard">
                    <i class="bi bi-house-door"></i><span>Panel Principal</span>
                </a>
            </li>

            <!-- 2. Usuarios -->
            <li class="has-submenu">
                <a href="#" data-title="Usuarios" class="menu-link">
                    <i class="bi bi-people"></i><span>Usuarios</span>
                </a>
                <button class="toggle-submenu" aria-label="Mostrar opciones">
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>/admin/registrar-usuario" class="submenu-link"><i
                                class="bi bi-plus-circle"></i>Registrar Usuario</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/consultar-usuarios" class="submenu-link"><i
                                class="bi bi-eye"></i>Consultar Usuarios</a></li>
                </ul>
            </li>

            <!-- 3. Categorías de Servicios -->
            <li class="has-submenu">
                <a href="#" data-title="Categorías" class="menu-link">
                    <i class="bi bi-grid"></i><span>Categorías de Servicios</span>
                </a>
                <button class="toggle-submenu" aria-label="Mostrar opciones">
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>/admin/registrar-categoria" class="submenu-link"><i
                                class="bi bi-plus-circle"></i>Registrar Categoría</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/consultar-categorias" class="submenu-link"><i
                                class="bi bi-eye"></i>Consultar Categorías</a></li>
                </ul>
            </li>

            <!-- 4. Reportes (Estructura completa de submenú estático) -->
            <li class="has-submenu">
                <a href="#" data-title="Reportes" class="menu-link">
                    <i class="bi bi-file-earmark-bar-graph"></i><span>Reportes</span>
                </a>
                <button class="toggle-submenu" aria-label="Mostrar opciones">
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>/admin/reportes-usuarios" class="submenu-link">
                            <i class="bi bi-person-lines-fill"></i>Usuarios</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/reportes-servicios" class="submenu-link">
                            <i class="bi bi-briefcase"></i>Servicios y solicitudes</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/reportes-resenas" class="submenu-link">
                            <i class="bi bi-star"></i>Calidad y reseñas</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/reportes-financieros" class="submenu-link">
                            <i class="bi bi-cash-stack"></i>Financieros</a></li>
                    <li><a href<?= BASE_URL ?>="/admin/reportes-marketing" class="submenu-link">
                            <i class="bi bi-megaphone"></i>Marketing</a></li>
                </ul>
            </li>
            <!-- FIN: Reportes -->

            <!-- 5. Estadísticas -->
            <li><a href="/admin/estadisticas" data-title="Estadísticas"><i class="bi bi-graph-up"></i><span>Estadísticas</span></a>
            </li>



            <!-- 7. Calendario -->
            <li><a href="/admin/calendario" data-title="Calendario"><i class="bi bi-calendar-event"></i><span>Calendario</span></a>

            <li><a href="<?= BASE_URL ?>/admin/finanzas" data-title="Finanzas"><i class="bi bi-cash-stack"></i><span>Finanzas</span></a></li>


            </li>

            <!-- 8. Facturación -->
            <li><a href="/admin/facturacion" data-title="Facturación"><i class="bi bi-receipt"></i><span>Facturación</span></a></li>

            <!-- 9. Marketing -->
            <li><a href="/admin/marketing" data-title="Marketing"><i class="bi bi-megaphone"></i><span>Marketing</span></a></li>

            <!-- 10. Integraciones -->
            <li><a href="/admin/integraciones" data-title="Integraciones"><i
                        class="bi bi-plug"></i><span>Integraciones</span></a></li>
        </ul>
    </nav>


    <!-- Footer del menú -->
    <div class="menu-footer">
        <!-- Ajustes -->
        <a href="#" data-title="Ajustes"><i class="bi bi-gear"></i><span>Ajustes</span></a>

        <!-- Cerrar Sesión -->
        <a href="<?= BASE_URL ?>/cerrar-sesion" data-title="Cerrar Sesión"><i
                class="bi bi-box-arrow-right"></i><span>Cerrar Sesión</span></a>
    </div>
</aside>