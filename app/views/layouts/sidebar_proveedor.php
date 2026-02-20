<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

$isActive = function (string $path) use ($currentPath): string {
    // Marca activo si la ruta actual es exactamente esa o empieza por ella (útil si hay ?id=...)
    return (strpos($currentPath, $path) === 0) ? 'active' : '';
};
?>

<aside class="sidebar">
    <div class="logo">
        <a href="#">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/LOGO PRINCIPAL.png" alt="Logo Proviservers" class="logo-completo">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/FAVICON.png" alt="Logo Proviservers" class="logo-favicon">
        </a>
    </div>

    <nav class="menu-principal">
        <ul>
            <!-- DASHBOARD -->
            <li>
                <a href="<?= BASE_URL ?>/proveedor/dashboard"
                   class="<?= $isActive('/proveedor/dashboard') ?>"
                   data-title="Dashboard">
                    <i class="bi bi-house-door"></i><span>Panel Principal</span>
                </a>
            </li>

             <li>
                <a href="<?= BASE_URL ?>/proveedor/registrar-servicio"
                   class="<?= $isActive('/proveedor/registrar-servicio') ?>"
                   data-title="Registrar Servicio">
                    <i class="bi bi-plus-circle"></i><span>Registrar servicio</span>
                </a>
            </li>

            <!-- SERVICIOS (SIN SUBMENÚ) -->
            <li>
                <a href="<?= BASE_URL ?>/proveedor/listar-servicio"
                   class="<?= $isActive('/proveedor/listar-servicio') ?>"
                   data-title="Servicios">
                    <i class="bi bi-briefcase"></i><span>Servicios</span>
                </a>
            </li>

        
            <!-- PUBLICACIONES -->
            <li>
                <a href="<?= BASE_URL ?>/proveedor/publicaciones"
                   class="<?= $isActive('/proveedor/publicaciones') ?>"
                   data-title="Mis publicaciones">
                    <i class="bi bi-broadcast-pin"></i>
                    <span>Mis publicaciones</span>
                </a>
            </li>

            <!-- SOLICITUDES -->
            <li>
                <a href="<?= BASE_URL ?>/proveedor/nuevas_solicitudes"
                   class="<?= $isActive('/proveedor/nuevas_solicitudes') ?>"
                   data-title="Solicitudes">
                    <i class="bi bi-envelope"></i><span>Solicitudes</span>
                </a>
            </li>

            <!-- OPORTUNIDADES -->
            <li>
                <a href="<?= BASE_URL ?>/proveedor/oportunidades"
                   class="<?= $isActive('/proveedor/oportunidades') ?>"
                   data-title="Oportunidades">
                    <i class="bi bi-binoculars"></i>
                    <span>Oportunidades</span>
                </a>
            </li>

            <!-- RESTO (NO TOCADO) -->
            <li>
                <a href="<?= BASE_URL ?>/proveedor/resenas"
                   class="<?= $isActive('/proveedor/resenas') ?>"
                   data-title="Reseñas">
                    <i class="bi bi-star"></i><span>Reseñas</span>
                </a>
            </li>

            <li><a href="#" data-title="Calendario"><i class="bi bi-calendar-event"></i><span>Calendario</span></a></li>
            <li><a href="#" data-title="Estadísticas"><i class="bi bi-graph-up"></i><span>Estadísticas</span></a></li>
            <li><a href="#" data-title="Finanzas"><i class="bi bi-cash-stack"></i><span>Finanzas</span></a></li>
            <li><a href="#" data-title="Facturación"><i class="bi bi-receipt"></i><span>Facturación</span></a></li>
            <li><a href="#" data-title="Promociones"><i class="bi bi-megaphone"></i><span>Promociones</span></a></li>

            <li>
                <a href="<?= BASE_URL ?>/proveedor/configuracion"
                   class="<?= $isActive('/proveedor/configuracion') ?>"
                   data-title="Configuración">
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