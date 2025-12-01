<aside class="sidebar">
    <div class="logo">
        <a href="#">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/LOGO PRINCIPAL.png" alt="Logo Proviservers" class="logo-completo">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/FAVICON.png" alt="Logo Proviservers" class="logo-favicon">
        </a>
    </div>

    <!-- Menú principal -->
    <nav class="menu-principal">
        <ul>
            <li><a href="<?= BASE_URL ?>/admin/dashboard" class="active" data-title="Dashboard"><i
                        class="bi bi-house-door"></i><span>Panel Principal</span></a></li>

            <li class="has-submenu">
                <a href="#" data-title="Servicios" class="menu-link">
                    <i class="bi bi-people"></i><span>Usuarios</span>
                </a>
                <button class="toggle-submenu" aria-label="Mostrar opciones">
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>/admin/registrar-usuario" class="submenu-link"><i
                                class="bi bi-plus-circle"></i>Registrar Usuario</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/consultar-usuarios" class="submenu-link"><i class="bi bi-eye"></i>Consultar
                            Usuarios</a></li>
                </ul>
            </li>

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
            <li><a href="#" data-title="Reportes"><i
                        class="bi bi-file-earmark-bar-graph"></i><span>Reportes</span></a></li>
            <li><a href="#" data-title="Estadísticas"><i class="bi bi-graph-up"></i><span>Estadísticas</span></a>
            </li>
            <li><a href="<?= BASE_URL ?>/admin/finanzas" data-title="Finanzas"><i class="bi bi-cash-stack"></i><span>Finanzas</span></a></li>
            <li><a href="#" data-title="Calendario"><i class="bi bi-calendar-event"></i><span>Calendario</span></a>
            </li>
            <li><a href="#" data-title="Facturación"><i class="bi bi-receipt"></i><span>Facturación</span></a></li>
            <li><a href="#" data-title="Marketing"><i class="bi bi-megaphone"></i><span>Marketing</span></a></li>
            <li><a href="dashboardFormulario.html" data-title="Integraciones"><i
                        class="bi bi-plug"></i><span>Integraciones</span></a></li>
        </ul>
    </nav>


    <!-- Footer del menú -->
    <div class="menu-footer">
        <a href="#" data-title="Ajustes"><i class="bi bi-gear"></i><span>Ajustes</span></a>
        <a href="<?= BASE_URL ?>/login" data-title="Cerrar Sesión"><i
                class="bi bi-box-arrow-right"></i><span>Cerrar Sesión</span></a>
    </div>
</aside>