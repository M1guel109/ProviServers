<aside class="sidebar">
    <div class="logo">
        <a href="#">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/LOGO PRINCIPAL.png" alt="Logo Proviservers" class="logo-completo">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/FAVICON.png" alt="Logo Proviservers" class="logo-favicon">
        </a>
    </div>

    <nav class="menu-principal">
        <ul>
            <li><a href="<?= BASE_URL ?>/proveedor/dashboard" class="active" data-title="Dashboard"><i
                        class="bi bi-house-door"></i><span>Panel Principal</span></a></li>

            <li class="has-submenu">
                <a href="<?= BASE_URL ?>/proveedor/listar-servicio" data-title="Servicios" class="menu-link">
                    <i class="bi bi-briefcase"></i><span>Servicios</span>
                </a>
                <button class="toggle-submenu" aria-label="Mostrar opciones">
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>/proveedor/registrar-servicio" class="submenu-link"><i class="bi bi-plus-circle"></i>Registrar Servicio</a></li>
                    <li><a href="<?= BASE_URL ?>/proveedor/listar-servicio" class="submenu-link"><i class="bi bi-list-ul"></i>Consultar Servicios</a></li>
                </ul>
            </li>
            <li class="has-submenu">
                <a href="#" data-title="Solicitudes" class="menu-link">
                    <i class="bi bi-envelope"></i><span>Solicitudes</span>
                </a>
                <button class="toggle-submenu" aria-label="Mostrar opciones">
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL?>/proveedor/nuevas_solicitudes" class="submenu-link"><i class="bi bi-plus-circle"></i>Nuevas solicitudes</a></li>
                    <li><a href="#" class="submenu-link"><i class="bi bi-clock-history"></i>En proceso</a></li>
                    <li><a href="#" class="submenu-link"><i class="bi bi-check-circle"></i>Completadas</a></li>
                </ul>
            </li>

            <li><a href="#" data-title="Reseñas"><i class="bi bi-star"></i><span>Reseñas</span></a></li>
            <li><a href="#" data-title="Calendario"><i class="bi bi-calendar-event"></i><span>Calendario</span></a></li>
            <li><a href="#" data-title="Estadísticas"><i class="bi bi-graph-up"></i><span>Estadísticas</span></a></li>
            <li><a href="#" data-title="Finanzas"><i class="bi bi-cash-stack"></i><span>Finanzas</span></a></li>
            <li><a href="#" data-title="Facturación"><i class="bi bi-receipt"></i><span>Facturación</span></a></li>
            <li><a href="#" data-title="Promociones"><i class="bi bi-megaphone"></i><span>Promociones</span></a></li>
            <li>
                <a href="<?= BASE_URL ?>/proveedor/configuracion" data-title="Configuración">
                    <i class="bi bi-gear"></i><span>Configuración</span>
                </a>
            </li>

        </ul>
    </nav>


    <div class="menu-footer">
        <a href="#" data-title="Soporte">
            <i class="bi bi-headset"></i><span>Soporte</span>
        </a>
        <a href="<?= BASE_URL ?>/logout" data-title="Cerrar Sesión">
            <i class="bi bi-box-arrow-right"></i><span>Cerrar Sesión</span>
        </a>
    </div>

</aside>