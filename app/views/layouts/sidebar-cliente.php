<?php
require_once BASE_PATH . '/app/helpers/lang-helper.php';
?>

<aside class="sidebar" id="mainSidebar">
    <div class="logo">
        <a href="<?= BASE_URL ?>/cliente/dashboard">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/logo-principal.png"
                 alt="<?= __('cliente_alt_logo') ?>" class="logo-completo">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/favicon.png"
                 alt="<?= __('cliente_alt_logo') ?>" class="logo-favicon">
        </a>
        <button class="close-menu-mobile" id="closeMenuMobile" aria-label="Cerrar menú">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="menu-bar">
        <nav class="menu-principal">
            <ul>
                <!-- Panel Principal -->
                <li>
                    <a href="<?= BASE_URL ?>/cliente/dashboard" data-title="<?= __('cliente_menu_panel') ?>">
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span class="nav-text"><?= __('cliente_menu_panel') ?></span>
                    </a>
                </li>

                <li class="menu-header"><?= __('cliente_header_servicios') ?></li>

                <!-- Explorar servicios -->
                <li>
                    <a href="<?= BASE_URL ?>/cliente/explorar-servicios" data-title="<?= __('cliente_explorar_servicios') ?>">
                        <i class="bi bi-compass"></i>
                        <span class="nav-text"><?= __('cliente_explorar_servicios') ?></span>
                    </a>
                </li>

                <!-- Mis solicitudes -->
                <li>
                    <a href="<?= BASE_URL ?>/cliente/mis-solicitudes" data-title="<?= __('cliente_mis_solicitudes') ?>">
                        <i class="bi bi-send"></i>
                        <span class="nav-text"><?= __('cliente_mis_solicitudes') ?></span>
                    </a>
                </li>

                <!-- Servicios contratados -->
                <li>
                    <a href="<?= BASE_URL ?>/cliente/servicios-contratados" data-title="<?= __('cliente_servicios_contratados') ?>">
                        <i class="bi bi-briefcase"></i>
                        <span class="nav-text"><?= __('cliente_servicios_contratados') ?></span>
                    </a>
                </li>

                <!-- Historial -->
                <li>
                    <a href="<?= BASE_URL ?>/cliente/historial" data-title="<?= __('cliente_historial') ?>">
                        <i class="bi bi-clock-history"></i>
                        <span class="nav-text"><?= __('cliente_historial') ?></span>
                    </a>
                </li>

                <!-- Favoritos -->
                <li>
                    <a href="<?= BASE_URL ?>/cliente/favoritos" data-title="<?= __('cliente_favoritos') ?>">
                        <i class="bi bi-heart"></i>
                        <span class="nav-text"><?= __('cliente_favoritos') ?></span>
                    </a>
                </li>

                <li class="menu-header"><?= __('cliente_header_comunicacion') ?></li>

                <!-- Mensajes -->
                <li>
                    <a href="<?= BASE_URL ?>/cliente/mensajes" data-title="<?= __('cliente_mensajes') ?>">
                        <i class="bi bi-chat-dots"></i>
                        <span class="nav-text"><?= __('cliente_mensajes') ?></span>
                    </a>
                </li>

                <li class="menu-header"><?= __('cliente_header_configuracion') ?></li>

                <!-- Mi Perfil -->
                <li>
                    <a href="<?= BASE_URL ?>/cliente/perfil" data-title="<?= __('cliente_mi_perfil') ?>">
                        <i class="bi bi-person-circle"></i>
                        <span class="nav-text"><?= __('cliente_mi_perfil') ?></span>
                    </a>
                </li>

                <!-- Ayuda -->
                <li>
                    <a href="<?= BASE_URL ?>/cliente/ayuda" data-title="<?= __('cliente_ayuda') ?>">
                        <i class="bi bi-question-circle"></i>
                        <span class="nav-text"><?= __('cliente_ayuda') ?></span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="menu-footer">
            <!-- Cerrar sesión -->
            <a href="<?= BASE_URL ?>/cerrar-sesion" data-title="<?= __('cliente_salir') ?>">
                <i class="bi bi-box-arrow-right"></i>
                <span><?= __('cliente_salir') ?></span>
            </a>

            <!-- Dark mode toggle -->
            <div class="mode-row" id="modeToggle" data-title="<?= __('cliente_dark_mode') ?>">
                <div class="sun-moon">
                    <i class="bi bi-moon-fill moon"></i>
                    <i class="bi bi-sun-fill sun"></i>
                </div>
                <span class="mode-text"><?= __('cliente_dark_mode') ?></span>
                <div class="toggle-switch"><span class="switch"></span></div>
            </div>
        </div>
    </div>
</aside>
