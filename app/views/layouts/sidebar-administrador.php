<?php
require_once BASE_PATH . '/app/helpers/lang-helper.php';
require_once BASE_PATH . '/app/models/Contacto.php';
$_contactoNoLeidos = (new Contacto())->contarNoLeidos();
?>

<aside class="sidebar" id="mainSidebar">
    <div class="logo">
        <a href="<?= BASE_URL ?>/admin/dashboard">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/logo-principal.png"
                 alt="<?= __('admin_alt_logo') ?>" class="logo-completo">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/favicon.png"
                 alt="<?= __('admin_alt_logo') ?>" class="logo-favicon">
        </a>
        <button class="close-menu-mobile" id="closeMenuMobile" aria-label="Cerrar menú">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="menu-bar">
        <nav class="menu-principal">
            <ul>
                <li>
                    <a href="<?= BASE_URL ?>/admin/dashboard" class="active" data-title="<?= __('admin_menu_panel') ?>">
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span class="nav-text"><?= __('admin_menu_panel') ?></span>
                    </a>
                </li>

                <li class="menu-header"><?= __('admin_header_gestion') ?></li>

                <li class="has-submenu">
                    <a href="#" class="menu-link" data-title="<?= __('admin_menu_usuarios') ?>">
                        <i class="bi bi-people-fill"></i>
                        <span class="nav-text"><?= __('admin_menu_usuarios') ?></span>
                    </a>
                    <button class="toggle-submenu">
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                    <ul class="submenu">
                        <li><a href="<?= BASE_URL ?>/admin/registrar-usuario" class="submenu-link"><?= __('admin_sub_registrar_usuario') ?></a></li>
                        <li><a href="<?= BASE_URL ?>/admin/consultar-usuarios" class="submenu-link"><?= __('admin_sub_listar_usuarios') ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>/admin/consultar-servicios" data-title="<?= __('admin_menu_servicios') ?>">
                        <i class="bi bi-briefcase-fill"></i>
                        <span class="nav-text"><?= __('admin_menu_servicios') ?></span>
                    </a>
                </li>

                <li class="has-submenu">
                    <a href="#" class="menu-link" data-title="<?= __('admin_menu_categorias') ?>">
                        <i class="bi bi-tags-fill"></i>
                        <span class="nav-text"><?= __('admin_menu_categorias') ?></span>
                    </a>
                    <button class="toggle-submenu">
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                    <ul class="submenu">
                        <li><a href="<?= BASE_URL ?>/admin/registrar-categoria" class="submenu-link"><?= __('admin_sub_nueva_categoria') ?></a></li>
                        <li><a href="<?= BASE_URL ?>/admin/consultar-categorias" class="submenu-link"><?= __('admin_sub_listar_categorias') ?></a></li>
                    </ul>
                </li>

                <li class="has-submenu">
                    <a href="#" class="menu-link" data-title="<?= __('admin_menu_membresias') ?>">
                        <i class="bi bi-gem"></i>
                        <span class="nav-text"><?= __('admin_menu_membresias') ?></span>
                    </a>
                    <button class="toggle-submenu">
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                    <ul class="submenu">
                        <li><a href="<?= BASE_URL ?>/admin/registrar-membresia" class="submenu-link"><?= __('admin_sub_crear_plan') ?></a></li>
                        <li><a href="<?= BASE_URL ?>/admin/consultar-membresias?accion=listar_membresias" class="submenu-link"><?= __('admin_sub_planes') ?></a></li>
                        <li><a href="<?= BASE_URL ?>/admin/consultar-suscripciones" class="submenu-link"><?= __('admin_sub_suscripciones') ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>/admin/mensajes-contacto" data-title="Mensajes de Contacto">
                        <i class="bi bi-envelope-fill"></i>
                        <span class="nav-text">Mensajes de Contacto</span>
                        <?php if ($_contactoNoLeidos > 0): ?>
                            <span class="badge bg-danger ms-auto"><?= $_contactoNoLeidos ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="menu-header"><?= __('admin_header_admin') ?></li>

                <li class="has-submenu">
                    <a href="#" class="menu-link" data-title="<?= __('admin_menu_finanzas') ?>">
                        <i class="bi bi-wallet-fill"></i>
                        <span class="nav-text"><?= __('admin_menu_finanzas') ?></span>
                    </a>
                    <button class="toggle-submenu">
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                    <ul class="submenu">
                        <li><a href="<?= BASE_URL ?>/admin/finanzas" class="submenu-link"><?= __('admin_sub_balance') ?></a></li>
                        <li><a href="<?= BASE_URL ?>/admin/facturacion" class="submenu-link"><?= __('admin_sub_facturacion') ?></a></li>
                    </ul>
                </li>

                <li class="has-submenu">
                    <a href="#" class="menu-link" data-title="<?= __('admin_menu_reportes') ?>">
                        <i class="bi bi-bar-chart-fill"></i>
                        <span class="nav-text"><?= __('admin_menu_reportes') ?></span>
                    </a>
                    <button class="toggle-submenu">
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                    <ul class="submenu">
                        <li><a href="<?= BASE_URL ?>/admin/reportes-usuarios" class="submenu-link"><?= __('admin_sub_general') ?></a></li>
                    </ul>
                </li>
            </ul>
        </nav>

        <div class="menu-footer">
            <a href="<?= BASE_URL ?>/admin/ajustes" data-title="<?= __('admin_footer_ajustes') ?>">
                <i class="bi bi-gear-fill"></i>
                <span><?= __('admin_footer_ajustes') ?></span>
            </a>
            <a href="<?= BASE_URL ?>/cerrar-sesion?accion=cerrar_sesion" data-title="<?= __('admin_footer_salir') ?>">
                <i class="bi bi-box-arrow-right"></i>
                <span><?= __('admin_footer_salir') ?></span>
            </a>
            
            <div class="mode-row" id="modeToggle" data-title="Cambiar tema">
                <div class="sun-moon">
                    <i class="bi bi-moon-fill moon"></i>
                    <i class="bi bi-sun-fill sun"></i>
                </div>
                <span class="mode-text">Modo oscuro</span>
                <div class="toggle-switch"><span class="switch"></span></div>
            </div>
        </div>
    </div>
</aside>