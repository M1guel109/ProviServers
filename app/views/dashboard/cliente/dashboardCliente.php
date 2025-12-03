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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardCliente.css">
</head>
<body>
    <!-- SIDEBAR -->
    <?php 
    $currentPage = 'dashboard';
    include_once __DIR__ . '/../../layouts/sidebar_cliente.php'; 
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido">
        <!-- HEADER -->
        <?php include_once __DIR__ . '/../../layouts/header_cliente.php'; ?>

        <?php
        // Nombre dinámico del cliente
        $nombreSaludo = isset($usuarioC['nombres']) ? $usuarioC['nombres'] : 'Cliente';
        ?>

        <!-- 1) INICIO -->
        <section id="inicio">
            <div class="section-hero">
                <h1>¡Hola <?= htmlspecialchars($nombreSaludo) ?>! 👋</h1>
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
                        <a href="<?= BASE_URL ?>/cliente/explorar" class="btn-modern">
                            <i class="bi bi-search"></i> Buscar Servicio
                        </a>
                        <a href="<?= BASE_URL ?>/cliente/servicios-contratados" class="btn-modern-outline">
                            <i class="bi bi-briefcase"></i> Ver Mis Servicios
                        </a>
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
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JS propio -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboardCliente.js"></script>
</body>
</html>
