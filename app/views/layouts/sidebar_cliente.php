<?php
// En cada vista define la página actual antes de incluir el sidebar
// Ejemplo: $currentPage = 'mensajes';
?>

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
                <a href="<?= BASE_URL ?>/cliente/dashboard" 
                   class="<?= ($currentPage == 'dashboard') ? 'active' : '' ?>" 
                   data-title="Inicio">
                    <i class="bi bi-house-door"></i>
                    <span>Inicio</span>
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/cliente/explorar" 
                   class="<?= ($currentPage == 'explorar') ? 'active' : '' ?>" 
                   data-title="Explorar Servicios">
                    <i class="bi bi-compass"></i>
                    <span>Explorar Servicios</span>
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/cliente/servicios-contratados" 
                   class="<?= ($currentPage == 'servicios-contratados') ? 'active' : '' ?>" 
                   data-title="Mis Servicios">
                    <i class="bi bi-briefcase"></i>
                    <span>Servicios Contratados</span>
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/cliente/favoritos" 
                   class="<?= ($currentPage == 'favoritos') ? 'active' : '' ?>" 
                   data-title="Favoritos">
                    <i class="bi bi-heart"></i>
                    <span>Favoritos</span>
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/cliente/mensajes" 
                   class="<?= ($currentPage == 'mensajes') ? 'active' : '' ?>" 
                   data-title="Mensajes">
                    <i class="bi bi-chat-dots"></i>
                    <span>Mensajes</span>
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/cliente/historial" 
                   class="<?= ($currentPage == 'historial') ? 'active' : '' ?>" 
                   data-title="Historial">
                    <i class="bi bi-clock-history"></i>
                    <span>Historial</span>
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>/cliente/perfil" 
                   class="<?= ($currentPage == 'perfil') ? 'active' : '' ?>" 
                   data-title="Mi Perfil">
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
                <a href="<?= BASE_URL ?>/cliente/ayuda" 
                   class="<?= ($currentPage == 'ayuda') ? 'active' : '' ?>" 
                   data-title="Ayuda">
                    <i class="bi bi-question-circle"></i>
                    <span>Ayuda</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
