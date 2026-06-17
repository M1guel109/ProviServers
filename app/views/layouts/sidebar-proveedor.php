<?php
require_once BASE_PATH . '/app/helpers/lang-helper.php';
?>

<aside class="sidebar" id="mainSidebar">
    <div class="logo">
        <a href="<?= BASE_URL ?>/proveedor/dashboard">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/logo-principal.png"
                 alt="ProviServers" class="logo-completo">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/favicon.png"
                 alt="ProviServers" class="logo-favicon">
        </a>
        <button class="close-menu-mobile" id="closeMenuMobile" aria-label="Cerrar menú">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="menu-bar">
        <nav class="menu-principal">
            <ul>

                <li>
                    <a href="<?= BASE_URL ?>/proveedor/dashboard"
                       data-title="<?= __('proveedor_menu_panel') ?>">
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span class="nav-text"><?= __('proveedor_menu_panel') ?></span>
                    </a>
                </li>

                <li class="menu-header"><?= __('proveedor_header_servicios') ?></li>

                <li>
                    <a href="<?= BASE_URL ?>/proveedor/registrar-servicio"
                       data-title="<?= __('proveedor_registrar_servicio') ?>">
                        <i class="bi bi-plus-circle"></i>
                        <span class="nav-text"><?= __('proveedor_registrar_servicio') ?></span>
                    </a>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>/proveedor/listar-servicio"
                       data-title="<?= __('proveedor_mis_servicios') ?>">
                        <i class="bi bi-briefcase"></i>
                        <span class="nav-text"><?= __('proveedor_mis_servicios') ?></span>
                    </a>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>/proveedor/publicaciones"
                       data-title="<?= __('proveedor_mis_publicaciones') ?>">
                        <i class="bi bi-broadcast-pin"></i>
                        <span class="nav-text"><?= __('proveedor_mis_publicaciones') ?></span>
                    </a>
                </li>

                <li class="menu-header"><?= __('proveedor_header_solicitudes') ?></li>

                <li>
                    <a href="<?= BASE_URL ?>/proveedor/nuevas-solicitudes"
                       data-title="<?= __('proveedor_solicitudes') ?>">
                        <i class="bi bi-envelope"></i>
                        <span class="nav-text"><?= __('proveedor_solicitudes') ?></span>
                    </a>
                </li>

                <li>
                    <!-- ✅ CORREGIDO: sin parámetro redundante -->
                    <a href="<?= BASE_URL ?>/proveedor/oportunidades"
                       data-title="<?= __('proveedor_oportunidades') ?>">
                        <i class="bi bi-binoculars"></i>
                        <span class="nav-text"><?= __('proveedor_oportunidades') ?></span>
                    </a>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>/proveedor/resenas"
                       data-title="<?= __('proveedor_resenas') ?>">
                        <i class="bi bi-star"></i>
                        <span class="nav-text"><?= __('proveedor_resenas') ?></span>
                    </a>
                </li>

                <li class="menu-header"><?= __('proveedor_header_negocio') ?></li>

                <li>
                    <a href="<?= BASE_URL ?>/proveedor/calendario"
                       data-title="<?= __('proveedor_calendario') ?>">
                        <i class="bi bi-calendar-event"></i>
                        <span class="nav-text"><?= __('proveedor_calendario') ?></span>
                    </a>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>/proveedor/estadisticas"
                       data-title="<?= __('proveedor_estadisticas') ?>">
                        <i class="bi bi-graph-up"></i>
                        <span class="nav-text"><?= __('proveedor_estadisticas') ?></span>
                    </a>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>/proveedor/finanzas"
                       data-title="<?= __('proveedor_finanzas') ?>">
                        <i class="bi bi-cash-stack"></i>
                        <span class="nav-text"><?= __('proveedor_finanzas') ?></span>
                    </a>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>/proveedor/historial-pagos" data-title="Historial de pagos"
                       class="<?= ($currentPage ?? '') === 'historial-pagos' ? 'active' : '' ?>">
                        <i class="bi bi-receipt"></i>
                        <span class="nav-text">Historial de pagos</span>
                    </a>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>/proveedor/facturacion"
                       data-title="<?= __('proveedor_facturacion') ?>">
                        <i class="bi bi-receipt"></i>
                        <span class="nav-text"><?= __('proveedor_facturacion') ?></span>
                    </a>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>/proveedor/promociones"
                       data-title="<?= __('proveedor_promociones') ?>">
                        <i class="bi bi-megaphone"></i>
                        <span class="nav-text"><?= __('proveedor_promociones') ?></span>
                    </a>
                </li>

                <li>
                    <a href="<?= BASE_URL ?>/proveedor/membresia"
                       data-title="<?= __('proveedor_membresia') ?>">
                        <i class="bi bi-gem"></i>
                        <span class="nav-text"><?= __('proveedor_membresia') ?></span>
                    </a>
                </li>

                <li class="menu-header"><?= __('proveedor_header_configuracion') ?></li>

                <li>
                    <a href="<?= BASE_URL ?>/proveedor/configuracion"
                       data-title="<?= __('proveedor_configuracion') ?>">
                        <i class="bi bi-gear"></i>
                        <span class="nav-text"><?= __('proveedor_configuracion') ?></span>
                    </a>
                </li>

            </ul>
        </nav>

        <div class="menu-footer">
            <!-- ✅ CORREGIDO: sin ?accion= innecesario -->
            <a href="<?= BASE_URL ?>/cerrar-sesion"
               data-title="<?= __('proveedor_salir') ?>">
                <i class="bi bi-box-arrow-right"></i>
                <span><?= __('proveedor_salir') ?></span>
            </a>

            <div class="mode-row" id="modeToggle"
                 data-title="<?= __('proveedor_dark_mode') ?>">
                <div class="sun-moon">
                    <i class="bi bi-moon-fill moon"></i>
                    <i class="bi bi-sun-fill sun"></i>
                </div>
                <span class="mode-text"><?= __('proveedor_dark_mode') ?></span>
                <div class="toggle-switch"><span class="switch"></span></div>
            </div>
        </div>
    </div>
</aside>