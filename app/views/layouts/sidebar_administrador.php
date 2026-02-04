<aside class="sidebar">
    <div class="logo">
        <a href="<?= BASE_URL ?>/admin/dashboard">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/LOGO PRINCIPAL.png" alt="Logo Proviservers" class="logo-completo">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/FAVICON.png" alt="Logo Proviservers" class="logo-favicon">
        </a>
    </div>

    <nav class="menu-principal">
        <ul>
            <li>
                <a href="<?= BASE_URL ?>/admin/dashboard" class="active" data-title="Dashboard">
                    <i class="bi bi-grid-1x2-fill"></i><span>Panel Principal</span>
                </a>
            </li>

            <li class="menu-header">GESTIÓN PRINCIPAL</li>

            <li class="has-submenu">
                <a href="#" class="menu-link" data-title="Usuarios">
                    <i class="bi bi-people-fill"></i><span>Usuarios</span>
                </a>
                <button class="toggle-submenu"><i class="bi bi-chevron-down toggle-icon"></i></button>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>/admin/registrar-usuario" class="submenu-link">Registrar Usuario</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/consultar-usuarios" class="submenu-link">Listar Usuarios</a></li>
                </ul>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/admin/consultar-servicios" data-title="Servicios">
                    <i class="bi bi-briefcase-fill"></i><span>Moderación de Servicios</span>
                </a>
            </li>

            <li class="has-submenu">
                <a href="#" class="menu-link" data-title="Categorías">
                    <i class="bi bi-tags-fill"></i><span>Categorías</span>
                </a>
                <button class="toggle-submenu"><i class="bi bi-chevron-down toggle-icon"></i></button>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>/admin/registrar-categoria" class="submenu-link">Nueva Categoría</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/consultar-categorias" class="submenu-link">Listar Categorías</a></li>
                </ul>
            </li>

            <li class="has-submenu">
                <a href="#" class="menu-link" data-title="Membresías">
                    <i class="bi bi-gem"></i><span>Membresías</span>
                </a>
                <button class="toggle-submenu"><i class="bi bi-chevron-down toggle-icon"></i></button>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>/admin/registrar-membresia" class="submenu-link">Crear Plan</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/consultar-membresias" class="submenu-link">Planes Disponibles</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/consultar-suscripciones" class="submenu-link">Suscripciones Activas</a></li>
                </ul>
            </li>

            <li class="menu-header">ADMINISTRACIÓN</li>

            <li class="has-submenu">
                <a href="#" class="menu-link">
                    <i class="bi bi-wallet-fill"></i><span>Finanzas</span>
                </a>
                <button class="toggle-submenu"><i class="bi bi-chevron-down toggle-icon"></i></button>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>/admin/finanzas" class="submenu-link">Balance</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/facturacion" class="submenu-link">Facturación</a></li>
                </ul>
            </li>

            <li class="has-submenu">
                <a href="#" class="menu-link">
                    <i class="bi bi-bar-chart-fill"></i><span>Reportes</span>
                </a>
                <button class="toggle-submenu"><i class="bi bi-chevron-down toggle-icon"></i></button>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>/admin/reportes-usuarios" class="submenu-link">General</a></li>
                </ul>
            </li>

        </ul>
    </nav>

    <div class="menu-footer">
        <a href="<?= BASE_URL ?>/admin/ajustes" title="Configuración">
            <i class="bi bi-gear-fill"></i><span>Ajustes</span>
        </a>
        <a href="<?= BASE_URL ?>/cerrar-sesion" title="Salir" class="text-danger">
            <i class="bi bi-box-arrow-right"></i><span>Salir</span>
        </a>
    </div>
</aside>