<aside class="sidebar">
    <div class="logo">
        <a href="#inicio">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/LOGO PRINCIPAL.png" 
                 alt="Logo Proviservers" class="logo-completo">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/FAVICON.png" 
                 alt="Logo Proviservers" class="logo-favicon">
        </a>
    </div>

    <!-- Menú principal -->
    <nav class="menu-principal">
        <ul>
            <li>
                <a href="<?= BASE_URL ?>/cliente/dashboard" class="active" data-title="Inicio">
                    <i class="bi bi-house-door"></i>
                    <span>Inicio</span>
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/cliente/explorar-servicios" data-title="Explorar Servicios">
                    <i class="bi bi-compass"></i>
                    <span>Explorar Servicios</span>
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/cliente/servicios-contratados" data-title="Servicios Contratados">
                    <i class="bi bi-briefcase"></i>
                    <span>Servicios Contratados</span>
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/cliente/favoritos" data-title="Favoritos">
                    <i class="bi bi-heart"></i>
                    <span>Favoritos</span>
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/cliente/mensajes" data-title="Mensajes">
                    <i class="bi bi-chat-dots"></i>
                    <span>Mensajes</span>
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/cliente/historial" data-title="Historial">
                    <i class="bi bi-clock-history"></i>
                    <span>Historial</span>
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/cliente/perfil" data-title="Mi Perfil">
                    <i class="bi bi-person-circle"></i>
                    <span>Mi Perfil</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Menú secundario -->
    <nav class="menu-secundario">
        <p>Más</p>
        <ul>
            <li>
                <a href="<?= BASE_URL ?>/cliente/ayuda" data-title="Ayuda">
                    <i class="bi bi-question-circle"></i>
                    <span>Ayuda</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/login" data-title="Cerrar Sesión">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Cerrar Sesión</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
