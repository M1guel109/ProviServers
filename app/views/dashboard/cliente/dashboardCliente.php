<?php
// dashboardCliente.php (arriba del todo, antes del HTML)
session_start();

require_once BASE_PATH . '/app/models/Publicacion.php';

// Publicaciones aprobadas para poder asociar la solicitud desde el modal
$pubModel = new Publicacion();
$publicacionesAprobadas = $pubModel->listarPublicacionesAprobadasParaSolicitudes(); // ya la tienes en tu modelo

// (Opcional) Si quieres precargar datos del cliente:
$usuarioC = $_SESSION['user'] ?? [];
?>






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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- Estilos específicos de cliente -->
    <!-- Ajusta el path si tu carpeta se llama distinto (dashBoard vs dashboard) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardCliente.css">
</head>

<body>
    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_cliente.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido">

        <!-- HEADER -->
        <?php include_once __DIR__ . '/../../layouts/header_cliente.php'; ?>

        <?php
        // Si quieres usar el nombre del cliente en el saludo
        $nombreSaludo = isset($usuarioC['nombres']) ? $usuarioC['nombres'] : 'Cliente';
        ?>

        <!-- SECCIÓN: INICIO -->
        <section id="inicio">
            <div class="section-hero text-center">
                <h1>¡Hola, <?= htmlspecialchars($nombreSaludo) ?>! 👋</h1>
                <p class="lead">Este es tu espacio para gestionar servicios, publicar necesidades y conectar con profesionales confiables.</p>
            </div>


            <div class="section-content">
                <!-- Estadísticas visuales -->
                <div class="stats-visual row text-center">
                    <div class="col stat-visual-item">
                        <i class="bi bi-clock-history"></i>
                        <h3>3</h3>
                        <p>Servicios Activos</p>
                    </div>
                    <div class="col stat-visual-item">
                        <i class="bi bi-check-circle"></i>
                        <h3>1</h3>
                        <p>Completados</p>
                    </div>
                    <div class="col stat-visual-item">
                        <i class="bi bi-heart"></i>
                        <h3>3</h3>
                        <p>Favoritos</p>
                    </div>
                    <div class="col stat-visual-item">
                        <i class="bi bi-star"></i>
                        <h3>4.8</h3>
                        <p>Calificación</p>
                    </div>
                </div>


                <!-- Acciones rápidas -->
                <div class="mt-5">
                    <h2 class="mb-4">¿Qué necesitas hoy?</h2>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="<?= BASE_URL ?>/cliente/explorar" class="btn-modern-outline">
                            <i class="bi bi-search"></i> Buscar Servicio
                        </a>
                        <a href="<?= BASE_URL ?>/cliente/servicios-contratados" class="btn-modern-outline">
                            <i class="bi bi-briefcase"></i> Ver Mis Servicios
                        </a>
                        <button type="button" class="btn-modern-outline" data-bs-toggle="modal" data-bs-target="#modalNecesidad">
                            <i class="bi bi-plus-circle"></i> Publicar Necesidad
                        </button>
                    </div>
                </div>


                <!-- Servicios en curso -->
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
                            <a href="<?= BASE_URL ?>/cliente/servicios-contratados" class="btn-modern-outline btn-sm">Ver detalles</a>
                        </li>

                        <li class="modern-list-item">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-wrench" style="font-size: 2rem; color: var(--primary-color);"></i>
                                <div>
                                    <h5 class="mb-1" style="color: var(--dark-color);">Plomería</h5>
                                    <p class="mb-0 text-muted">Con Carlos Ruiz · Cita: 28 Nov 10:00 AM</p>
                                </div>
                            </div>
                            <a href="<?= BASE_URL ?>/cliente/servicios-contratados" class="btn-modern-outline btn-sm">Ver detalles</a>
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
                    <!-- Card ejemplo de proveedor favorito -->
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
                                <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/Foto-usuario.png"
                                    alt="Perfil"
                                    style="width: 120px; height: 120px; border-radius: 50%; border: 4px solid var(--primary-color); margin-bottom: 1rem;">
                                <h3><?= htmlspecialchars($nombreSaludo) ?></h3>
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
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($nombreSaludo) ?>">
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

    <!-- Modal -->
    <!-- Modal -->
    <!-- Modal Publicar Necesidad (CORREGIDO) -->
    <div class="modal fade" id="modalNecesidad" tabindex="-1" aria-labelledby="modalNecesidadLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalNecesidadLabel">Publicar una Necesidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form
                    id="formNecesidad"
                    action="<?= rtrim(BASE_URL, '/') ?>/cliente/guardar-solicitud"
                    method="POST"
                    enctype="multipart/form-data"
                    class="needs-validation"
                    novalidate>
                    <div class="modal-body">

                        <!-- ✅ Selección de publicación aprobada (OBLIGATORIO para tu flujo actual) -->
                        <div class="mb-3">
                            <label class="form-label">Servicio / Publicación <span class="text-danger">*</span></label>
                            <select class="form-select" name="publicacion_id" id="publicacion_id" required>
                                <option value="">Selecciona un servicio</option>
                                <?php foreach ($publicacionesAprobadas as $p): ?>
                                    <option value="<?= (int)$p['id'] ?>">
                                        <?= htmlspecialchars($p['titulo']) ?>
                                        <?php if (!empty($p['proveedor_nombre'])): ?>
                                            — <?= htmlspecialchars($p['proveedor_nombre']) ?>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Debes seleccionar un servicio/publicación.</div>
                        </div>

                        <!-- Categoría (solo UI; no la usa tu backend, pero ayuda a armar título) -->
                        <div class="mb-3">
                            <label for="categoria" class="form-label">Categoría del servicio</label>
                            <select class="form-select" id="categoria">
                                <option value="">Selecciona una categoría</option>
                                <option value="Salud">Salud</option>
                                <option value="Educación">Educación</option>
                                <option value="Tecnología">Tecnología</option>
                                <option value="Hogar">Hogar</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="categoriaOtroWrapper">
                            <label for="categoriaOtro" class="form-label">Especifica la categoría</label>
                            <input type="text" class="form-control" id="categoriaOtro" placeholder="Ej. Carpintería fina">
                        </div>

                        <!-- ✅ Título (backend espera name="titulo") -->
                        <div class="mb-3">
                            <label class="form-label">Título <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="titulo" name="titulo" maxlength="120" required>
                            <div class="invalid-feedback">El título es obligatorio.</div>
                        </div>

                        <!-- ✅ Descripción (backend espera name="descripcion") -->
                        <div class="mb-3">
                            <label class="form-label">Descripción detallada <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                            <div class="invalid-feedback">La descripción es obligatoria.</div>
                        </div>

                        <!-- ✅ Presupuesto (backend usa presupuesto_estimado) -->
                        <div class="mb-3">
                            <label class="form-label">Presupuesto estimado (COP)</label>
                            <input type="number" class="form-control" id="presupuesto" name="presupuesto_estimado" min="0">
                        </div>

                        <!-- ✅ Fecha (backend espera name="fecha_preferida") -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Fecha deseada <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fecha" name="fecha_preferida" min="<?= date('Y-m-d') ?>" required>
                                <div class="invalid-feedback">Selecciona una fecha.</div>
                            </div>

                            <!-- Hora: tu BD no tiene columna "hora", así que se anexará a la descripción -->
                            <div class="col-md-6">
                                <label class="form-label">Hora deseada</label>
                                <input type="time" class="form-control" id="hora">
                                <small class="text-muted">Se anexará a la descripción.</small>
                            </div>
                        </div>

                        <!-- ✅ Dirección (backend espera direccion) -->
                        <div class="mb-3">
                            <label class="form-label">Dirección <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="direccion" name="direccion" required>
                            <div class="invalid-feedback">La dirección es obligatoria.</div>
                        </div>

                        <!-- ✅ Ciudad / zona -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Ciudad <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ciudad" name="ciudad" required>
                                <div class="invalid-feedback">La ciudad es obligatoria.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Barrio o zona</label>
                                <input type="text" class="form-control" id="zona" name="zona">
                            </div>
                        </div>

                        <!-- ✅ Franja horaria (si quieres usarlo, tu backend la recibe) -->
                        <div class="mb-3">
                            <label class="form-label">Franja horaria (opcional)</label>
                            <select class="form-select" name="franja_horaria" id="franja_horaria">
                                <option value="">Cualquiera</option>
                                <option value="manana">Mañana</option>
                                <option value="tarde">Tarde</option>
                                <option value="noche">Noche</option>
                            </select>
                        </div>

                        <!-- Adjuntos -->
                        <div class="mb-3">
                            <label class="form-label">Adjuntar fotos o archivos (opcional)</label>
                            <input type="file" name="adjuntos[]" class="form-control" accept="image/*,application/pdf" multiple>
                            <small class="text-muted">Máx. 5MB por archivo.</small>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Publicar
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>





    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JS propio -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboardCliente.js"></script>



</body>

</html>