<aside class="sidebar">
    <div class="logo">
        <a href="<?= BASE_URL ?>/admin/dashboard">
            <!-- Se asume que /public/assets/img/logos/LOGO PRINCIPAL.png es la ruta estática -->
            <img src="<?= BASE_URL ?>/public/assets/img/logos/LOGO PRINCIPAL.png" alt="Logo Proviservers" class="logo-completo">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/FAVICON.png" alt="Logo Proviservers" class="logo-favicon">
        </a>
    </div>

    <!-- Menú principal -->
    <nav class="menu-principal">
        <ul>
            <!-- 1. Panel Principal -->
            <li>
                <a href="<?= BASE_URL ?>/admin/dashboard" class="active" data-title="Dashboard">
                    <i class="bi bi-house-door"></i><span>Panel Principal</span>
                </a>
            </li>

            <!-- 2. Gestión de Usuarios (Moderación de roles y perfiles) -->
            <li class="has-submenu">
                <a href="#" data-title="Usuarios" class="menu-link">
                    <i class="bi bi-people"></i><span>Gestión de Usuarios</span>
                </a>
                <button class="toggle-submenu" aria-label="Mostrar opciones">
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>/admin/registrar-usuario" class="submenu-link"><i
                                class="bi bi-plus-circle"></i>Registrar Usuario</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/consultar-usuarios" class="submenu-link"><i
                                class="bi bi-eye"></i>Consultar Usuarios</a></li>
                </ul>
            </li>

            <!-- 3. Moderación de Servicios (Control de calidad y aprobación de Proveedores) -->
            <li>
                <!-- Este enlace es CRUCIAL para que el Admin revise los servicios creados por los Proveedores -->
                <a href="<?= BASE_URL ?>/admin/consultar-servicios" data-title="Moderación de Servicios">
                    <i class="bi bi-list-check"></i><span>Moderación de Servicios</span>
                </a>
            </li>
            
            <!-- 4. Gestión de Categorías (Definición de la estructura del Marketplace) -->
            <li class="has-submenu">
                <a href="#" data-title="Categorías" class="menu-link">
                    <i class="bi bi-grid"></i><span>Gestión de Categorías</span>
                </a>
                <button class="toggle-submenu" aria-label="Mostrar opciones">
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>/admin/registrar-categoria" class="submenu-link"><i
                                class="bi bi-plus-circle"></i>Crear Nueva</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/consultar-categorias" class="submenu-link"><i
                                class="bi bi-eye"></i>Ver Categorías</a></li>
                </ul>
            </li>


            <!-- 5. Gestión de Membresías -->
            <li class="has-submenu">
                <a href="#" data-title="Membresías" class="menu-link">
                    <i class="bi bi-person-badge"></i><span>Gestión de Membresías</span>
                </a>
                <button class="toggle-submenu" aria-label="Mostrar opciones">
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>/admin/registrar-membresia" class="submenu-link"><i
                                class="bi bi-file-earmark-plus"></i>Crear Plan</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/consultar-membresias" class="submenu-link"><i
                                class="bi bi-card-list"></i>Ver Planes</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/consultar-suscripciones" class="submenu-link"><i
                                class="bi bi-bell"></i>Suscripciones Activas</a></li>
                </ul>
            </li>

            <!-- 6. Finanzas y Contabilidad (Consolida Finanzas y Facturación) -->
            <li class="has-submenu">
                <a href="#" data-title="Finanzas" class="menu-link">
                    <i class="bi bi-cash-stack"></i><span>Finanzas y Contabilidad</span>
                </a>
                <button class="toggle-submenu" aria-label="Mostrar opciones">
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>/admin/finanzas" class="submenu-link"><i
                                class="bi bi-wallet2"></i>Panel Financiero</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/facturacion" class="submenu-link"><i
                                class="bi bi-receipt"></i>Facturación</a></li>
                </ul>
            </li>

            <!-- 7. Reportes (Datos estáticos y exportables) -->
            <li class="has-submenu">
                <a href="#" data-title="Reportes" class="menu-link">
                    <i class="bi bi-file-earmark-bar-graph"></i><span>Reportes</span>
                </a>
                <button class="toggle-submenu" aria-label="Mostrar opciones">
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>/admin/reportes-usuarios" class="submenu-link">
                            <i class="bi bi-person-lines-fill"></i>Usuarios</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/reportes-servicios" class="submenu-link">
                            <i class="bi bi-briefcase"></i>Servicios y Solicitudes</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/reportes-financieros" class="submenu-link">
                            <i class="bi bi-cash-stack"></i>Transacciones</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/reportes-resenas" class="submenu-link">
                            <i class="bi bi-star"></i>Calidad y Reseñas</a></li>
                </ul>
            </li>
            
            <!-- 8. Módulos Operacionales -->
            <li><a href="<?= BASE_URL ?>/admin/marketing" data-title="Marketing"><i class="bi bi-megaphone"></i><span>Marketing (Campañas)</span></a></li>
            <li><a href="<?= BASE_URL ?>/admin/calendario" data-title="Calendario"><i class="bi bi-calendar-event"></i><span>Calendario Global</span></a></li>
            <li><a href="<?= BASE_URL ?>/admin/estadisticas" data-title="Estadísticas"><i class="bi bi-graph-up"></i><span>Estadísticas</span></a></li>

            
            <!-- 9. Soporte y Comunicaciones (NUEVA SECCIÓN CRÍTICA) -->
            <li>
                <a href="<?= BASE_URL ?>/admin/soporte" data-title="Soporte y Mensajería">
                    <i class="bi bi-chat-dots"></i><span>Soporte y Comunicaciones</span>
                </a>
            </li>

            <!-- 10. Gestión de Contenido y Legal (CMS) (NUEVA SECCIÓN CRÍTICA) -->
            <li class="has-submenu">
                <a href="#" data-title="Contenido" class="menu-link">
                    <i class="bi bi-file-earmark-richtext"></i><span>Gestión de Contenido y Legal</span>
                </a>
                <button class="toggle-submenu" aria-label="Mostrar opciones">
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>/admin/cms-paginas" class="submenu-link"><i
                                class="bi bi-file-text"></i>Páginas Públicas (Home, FAQ)</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/cms-legal" class="submenu-link"><i
                                class="bi bi-shield-lock"></i>Documentos Legales</a></li>
                </ul>
            </li>

            <!-- 11. Logs y Auditoría -->
            <li class="has-submenu">
                <a href="#" data-title="Logs" class="menu-link">
                    <i class="bi bi-journal-check"></i><span>Logs y Auditoría</span>
                </a>
                <button class="toggle-submenu" aria-label="Mostrar opciones">
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </button>
                <ul class="submenu">
                    <li><a href="<?= BASE_URL ?>/admin/logs-sistema" class="submenu-link"><i
                                class="bi bi-bug"></i>Log de Errores</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/auditoria-acciones" class="submenu-link"><i
                                class="bi bi-file-spreadsheet"></i>Auditoría de Acciones</a></li>
                </ul>
            </li>

            <!-- 12. Integraciones -->
            <li><a href="<?= BASE_URL ?>/admin/integraciones" data-title="Integraciones"><i
                                class="bi bi-plug"></i><span>Integraciones</span></a></li>
        </ul>
    </nav>


    <!-- Footer del menú -->
    <div class="menu-footer">
        <!-- Ajustes -->
        <a href="<?= BASE_URL ?>/admin/ajustes" data-title="Ajustes"><i class="bi bi-gear"></i><span>Ajustes del Sistema</span></a>

        <!-- Cerrar Sesión -->
        <a href="<?= BASE_URL ?>/cerrar-sesion" data-title="Cerrar Sesión"><i
                                class="bi bi-box-arrow-right"></i><span>Cerrar Sesión</span></a>
    </div>
</aside>