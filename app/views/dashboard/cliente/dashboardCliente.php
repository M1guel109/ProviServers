<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Mi Cuenta</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos globales -->
    <link rel="stylesheet" href="public/assets/estilosGenerales/style.css">

    <!-- Estilos específicos de cliente -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboardCliente.css">
</head>

<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="logo">
            <a href="#">
                <img src="public/assets/img/logos/LOGO PRINCIPAL.png" alt="Logo Proviservers" class="logo-completo">
                <img src="public/assets/img/logos/FAVICON.png" alt="Logo Proviservers" class="logo-favicon">
            </a>
        </div>

        <!-- Menú principal -->
        <nav class="menu-principal">
            <ul>
                <li>
                    <a href="#inicio" class="active" data-title="Inicio">
                        <i class="bi bi-house-door"></i>
                        <span>Inicio</span>
                    </a>
                </li>

                <li>
                    <a href="#explorar" data-title="Explorar Servicios">
                        <i class="bi bi-compass"></i>
                        <span>Explorar Servicios</span>
                    </a>
                </li>

                <li>
                    <a href="#mis-servicios" data-title="Mis Servicios">
                        <i class="bi bi-briefcase"></i>
                        <span>Mis Servicios</span>
                    </a>
                </li>

                <li>
                    <a href="#favoritos" data-title="Favoritos">
                        <i class="bi bi-heart"></i>
                        <span>Favoritos</span>
                    </a>
                </li>

                <li>
                    <a href="#mensajes" data-title="Mensajes">
                        <i class="bi bi-chat-dots"></i>
                        <span>Mensajes</span>
                    </a>
                </li>

                <li>
                    <a href="#historial" data-title="Historial">
                        <i class="bi bi-clock-history"></i>
                        <span>Historial</span>
                    </a>
                </li>

                <li>
                    <a href="#perfil" data-title="Mi Perfil">
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
                    <a href="#ayuda" data-title="Ayuda">
                        <i class="bi bi-question-circle"></i>
                        <span>Ayuda</span>
                    </a>
                </li>
                <li>
                    <a href="login.html" data-title="Cerrar Sesión">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido">

        <!-- HEADER -->
        <header class="barra-superior">
            <button id="btn-toggle-menu" class="btn-toggle">
                <i class="bi bi-list"></i>
            </button>

            <div class="buscador">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Buscar servicios, proveedores...">
            </div>

            <div class="acciones-barra">
                <div class="notificaciones item-barra">
                    <i class="bi bi-bell-fill"></i>
                    <span class="badge">3</span>
                </div>

                <a href="#perfil" class="usuario item-barra">
                    <img src="public/assets/dashBoard/img/Foto-usuario.png" alt="Usuario">
                    <div class="info-usuario">
                        <span class="nombre">Carlos M.</span>
                        <span class="rol">Cliente</span>
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </a>
            </div>
        </header>

        <!-- SECCIÓN: INICIO -->
        <section id="inicio">
            <div class="section-hero">
                <h1>¡Hola Carlos! 👋</h1>
                <p>Bienvenido a tu espacio personal. Encuentra los mejores profesionales para cualquier servicio que necesites.</p>
            </div>

            <div class="section-content">
                <!-- Estadísticas visuales -->
                <div class="stats-visual">
                    <div class="stat-visual-item">
                        <i class="bi bi-clock-history"></i>
                        <h3>3</h3>
                        <p>Servicios Activos</p>
                    </div>
                    <div class="stat-visual-item">
                        <i class="bi bi-check-circle"></i>
                        <h3>12</h3>
                        <p>Completados</p>
                    </div>
                    <div class="stat-visual-item">
                        <i class="bi bi-heart"></i>
                        <h3>8</h3>
                        <p>Favoritos</p>
                    </div>
                    <div class="stat-visual-item">
                        <i class="bi bi-star"></i>
                        <h3>4.8</h3>
                        <p>Calificación</p>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="mt-5">
                    <h2 class="mb-4">¿Qué necesitas hoy?</h2>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#explorar" class="btn-modern">
                            <i class="bi bi-search"></i>
                            Buscar Servicio
                        </a>
                        <a href="#mis-servicios" class="btn-modern-outline">
                            <i class="bi bi-briefcase"></i>
                            Ver Mis Servicios
                        </a>
                    </div>
                </div>

                <!-- Servicios recientes -->
                <div class="mt-5">
                    <h2 class="mb-4">Servicios en Curso</h2>
                    <ul class="modern-list">
                        <li class="modern-list-item">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-tree" style="font-size: 2rem; color: var(--primary-color);"></i>
                                <div>
                                    <h5 class="mb-1" style="color: var(--dark-color);">Jardinería y Paisajismo</h5>
                                    <p class="mb-0 text-muted">Con Miguel Torres · Progreso: 65%</p>
                                </div>
                            </div>
                            <a href="#mis-servicios" class="btn-modern-outline btn-sm">Ver detalles</a>
                        </li>
                        <li class="modern-list-item">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-wrench" style="font-size: 2rem; color: var(--primary-color);"></i>
                                <div>
                                    <h5 class="mb-1" style="color: var(--dark-color);">Plomería</h5>
                                    <p class="mb-0 text-muted">Con Carlos Ruiz · Cita: 28 Nov 10:00 AM</p>
                                </div>
                            </div>
                            <a href="#mis-servicios" class="btn-modern-outline btn-sm">Ver detalles</a>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- SECCIÓN: EXPLORAR SERVICIOS -->
        <section id="explorar" style="display: none;">
            <div class="section-hero">
                <h1>Explorar Servicios 🔍</h1>
                <p>Descubre profesionales verificados listos para ayudarte. Más de 1000 proveedores disponibles.</p>
            </div>

            <div class="section-content">
                <!-- Buscador adicional -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control" placeholder="¿Qué servicio necesitas?">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select">
                            <option>Todas las categorías</option>
                            <option>Jardinería</option>
                            <option>Plomería</option>
                            <option>Belleza</option>
                            <option>Mascotas</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Ubicación">
                    </div>
                    <div class="col-md-2">
                        <button class="btn-modern w-100">Buscar</button>
                    </div>
                </div>

                <!-- Grid de servicios -->
                <h2 class="mb-4">Categorías Populares</h2>
                <div class="services-grid">
                    <div class="service-item">
                        <div class="service-image" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);"></div>
                        <div class="service-content">
                            <h3>🌳 Jardinería</h3>
                            <p>Mantenimiento, diseño de jardines y paisajismo profesional.</p>
                            <a href="#" class="btn-modern-outline">Ver proveedores</a>
                        </div>
                    </div>

                    <div class="service-item">
                        <div class="service-image" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);"></div>
                        <div class="service-content">
                            <h3>🔧 Plomería</h3>
                            <p>Reparaciones, instalaciones y mantenimiento de tuberías.</p>
                            <a href="#" class="btn-modern-outline">Ver proveedores</a>
                        </div>
                    </div>

                    <div class="service-item">
                        <div class="service-image" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);"></div>
                        <div class="service-content">
                            <h3>✂️ Belleza</h3>
                            <p>Peluquería, estética y cuidado personal a domicilio.</p>
                            <a href="#" class="btn-modern-outline">Ver proveedores</a>
                        </div>
                    </div>

                    <div class="service-item">
                        <div class="service-image" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);"></div>
                        <div class="service-content">
                            <h3>🐾 Mascotas</h3>
                            <p>Veterinaria, grooming y cuidado de mascotas.</p>
                            <a href="#" class="btn-modern-outline">Ver proveedores</a>
                        </div>
                    </div>

                    <div class="service-item">
                        <div class="service-image" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);"></div>
                        <div class="service-content">
                            <h3>⚡ Electricidad</h3>
                            <p>Instalaciones eléctricas y reparaciones seguras.</p>
                            <a href="#" class="btn-modern-outline">Ver proveedores</a>
                        </div>
                    </div>

                    <div class="service-item">
                        <div class="service-image" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);"></div>
                        <div class="service-content">
                            <h3>🏠 Limpieza</h3>
                            <p>Limpieza profunda y mantenimiento de espacios.</p>
                            <a href="#" class="btn-modern-outline">Ver proveedores</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN: MIS SERVICIOS -->
        <section id="mis-servicios" style="display: none;">
            <div class="section-hero">
                <h1>Mis Servicios 💼</h1>
                <p>Gestiona todos tus servicios contratados y programados desde aquí.</p>
            </div>

            <div class="section-content">
                <h2 class="mb-4">En Curso</h2>
                <div class="services-grid">
                    <div class="service-item">
                        <div class="service-content">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-primary">En Progreso</span>
                                <i class="bi bi-three-dots-vertical"></i>
                            </div>
                            <h3>Jardinería y Paisajismo</h3>
                            <p><strong>Proveedor:</strong> Miguel Torres</p>
                            <p><strong>Inicio:</strong> 20 Nov 2024 · <strong>Estimado:</strong> 3 días</p>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small>Progreso</small>
                                    <small class="text-primary fw-bold">65%</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar" style="width: 65%; background: var(--primary-color);"></div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="#" class="btn-modern-outline flex-fill">Mensaje</a>
                                <a href="#" class="btn-modern flex-fill">Ver Detalles</a>
                            </div>
                        </div>
                    </div>

                    <div class="service-item">
                        <div class="service-content">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge" style="background: #fef3c7; color: #92400e;">Programado</span>
                                <i class="bi bi-three-dots-vertical"></i>
                            </div>
                            <h3>Plomería y Reparaciones</h3>
                            <p><strong>Proveedor:</strong> Carlos Ruiz</p>
                            <p><strong>Fecha:</strong> 28 Nov 2024 · <strong>Hora:</strong> 10:00 AM</p>
                            <div class="alert alert-info p-2 mb-3">
                                <small><i class="bi bi-calendar3"></i> Tu cita es en 2 días</small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="#" class="btn-modern-outline flex-fill">Reprogramar</a>
                                <a href="#" class="btn-modern flex-fill">Ver Detalles</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN: FAVORITOS -->
        <section id="favoritos" style="display: none;">
            <div class="section-hero">
                <h1>Mis Favoritos ❤️</h1>
                <p>Los proveedores que más te gustan, siempre a un click de distancia.</p>
            </div>

            <div class="section-content">
                <p class="text-muted mb-4">Has guardado 8 proveedores como favoritos</p>
                <div class="services-grid">
                    <!-- Aquí irían las cards de proveedores favoritos -->
                    <div class="service-item">
                        <div class="service-content">
                            <div class="d-flex justify-content-between mb-3">
                                <h3>Miguel Torres</h3>
                                <i class="bi bi-heart-fill" style="color: #ec4899; font-size: 1.5rem;"></i>
                            </div>
                            <p>Jardinería y Paisajismo</p>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="text-warning">★★★★★</span>
                                <span class="text-muted">5.0 (48 reseñas)</span>
                            </div>
                            <a href="#" class="btn-modern w-100">Contactar</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECCIÓN: PERFIL -->
        <section id="perfil" style="display: none;">
            <div class="section-hero">
                <h1>Mi Perfil 👤</h1>
                <p>Administra tu información personal y configuración de cuenta.</p>
            </div>

            <div class="section-content">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="service-item text-center">
                            <div class="service-content">
                                <img src="public/assets/dashBoard/img/Foto-usuario.png" alt="Perfil" 
                                     style="width: 120px; height: 120px; border-radius: 50%; border: 4px solid var(--primary-color); margin-bottom: 1rem;">
                                <h3>Carlos M.</h3>
                                <p class="text-muted">cliente@correo.com</p>
                                <button class="btn-modern-outline w-100">Cambiar foto</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="service-item">
                            <div class="service-content">
                                <h3 class="mb-4">Información Personal</h3>
                                <form class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nombre completo</label>
                                        <input type="text" class="form-control" value="Carlos M.">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Correo electrónico</label>
                                        <input type="email" class="form-control" value="cliente@correo.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Teléfono</label>
                                        <input type="text" class="form-control" value="+57 300 000 0000">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Ubicación</label>
                                        <input type="text" class="form-control" value="Bogotá, Colombia">
                                    </div>
                                    <div class="col-12">
                                        <button type="button" class="btn-modern">Guardar cambios</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JS propio -->
    <script src="public/assets/dashBoard/js/dashboardCliente.js"></script>
</body>
</html>
