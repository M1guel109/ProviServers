<header class="header-website">
    <nav class="navbar">
        <a href="<?= BASE_URL ?>/customers/dashboard" class="logo">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/LOGO PRINCIPAL.png" alt="Logo Proviservers"
                class="logo-completo" width="100">
        </a>

        <ul class="nav-menu">
            <li><a href="<?= BASE_URL ?>/customers/dashboard" class="active">
                    <i class="fas fa-home me-1"></i> Inicio
                </a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-wrench me-1"></i> Mis Servicios
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/customers/services/active">
                            <i class="fas fa-tasks me-2"></i> Servicios Activos
                        </a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/customers/services/history">
                            <i class="fas fa-history me-2"></i> Historial de Servicios
                        </a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-primary" href="<?= BASE_URL ?>/customers/search">
                            <i class="fas fa-plus-circle me-2"></i> Solicitar Nuevo
                        </a></li>
                </ul>
            </li>
            <li><a href="<?= BASE_URL ?>/customers/search">
                    <i class="fas fa-search me-1"></i> Explorar
                </a>
            </li>
            <li><a href="<?= BASE_URL ?>/customers/providers">
                    <i class="fas fa-user-friends me-1"></i> Proveedores
                </a>
            </li>
            <li><a href="<?= BASE_URL ?>/customers/quotations">
                    <i class="fas fa-envelope-open-text me-1"></i> Cotizaciones
                </a>
            </li>
        </ul>

        <div class="nav-actions">
            <div class="search-toggle">
                <i class="fas fa-search"></i>
            </div>

            <div class="notificaciones">
                <i class="fas fa-bell"></i>
                <span class="badge">3</span>
            </div>

            <div class="mensajes">
                <a href="<?= BASE_URL ?>/customers/messages" class="text-decoration-none text-reset">
                    <i class="fas fa-comment-dots"></i>
                    <span class="badge">2</span>
                </a>
            </div>

            <div class="user-menu dropdown">
                <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" class="text-decoration-none text-reset d-flex align-items-center">
                    <img src="https://via.placeholder.com/40" alt="Usuario" class="rounded-circle me-2">
                    <span class="d-none d-lg-inline">Carlos M.</span>
                    <i class="fas fa-chevron-down ms-2"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/customers/profile">
                            <i class="fas fa-user-circle me-2"></i> Mi Perfil
                        </a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/customers/settings">
                            <i class="fas fa-cog me-2"></i> Configuración
                        </a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout">
                            <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                        </a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="search-bar-expanded">
        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Buscar servicios, proveedores o categorías..." onkeypress="if(event.key === 'Enter') window.location.href='<?= BASE_URL ?>/customers/search?q=' + this.value;">
            <button class="search-close"><i class="fas fa-times"></i></button>
        </div>
    </div>
</header>