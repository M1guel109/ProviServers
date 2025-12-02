<?php
// Protegemos la vista: solo proveedor logueado
$redirect_path = '/login';
require_once BASE_PATH . '/app/helpers/session_proveedor.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Configuración del Proveedor</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- Estilos específicos del dashboard proveedor -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboard-Proveedor.css">
</head>

<body>
    <!-- SIDEBAR (lateral izquierdo) -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_proveedor.php';
    ?>

    <main class="contenido">
        <?php
        include_once __DIR__ . '/../../layouts/header_proveedor.php';
        ?>

        <!-- Título principal -->
        <section id="titulo-principal" class="mb-4">
            <h1>Configuración del Proveedor</h1>
            <p class="text-muted mb-0">
                Administra tu perfil, seguridad, notificaciones y forma de trabajo en la plataforma.
            </p>
        </section>

        <!-- Contenedor principal de configuración -->
        <section id="configuracion-proveedor">
            <!-- Tabs de configuración -->
            <ul class="nav nav-tabs" id="configTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="perfil-tab" data-bs-toggle="tab"
                        data-bs-target="#perfil" type="button" role="tab" aria-controls="perfil"
                        aria-selected="true">
                        <i class="bi bi-person-circle me-1"></i> Perfil profesional
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cuenta-tab" data-bs-toggle="tab"
                        data-bs-target="#cuenta" type="button" role="tab" aria-controls="cuenta"
                        aria-selected="false">
                        <i class="bi bi-shield-lock me-1"></i> Cuenta y seguridad
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="notificaciones-tab" data-bs-toggle="tab"
                        data-bs-target="#notificaciones" type="button" role="tab"
                        aria-controls="notificaciones" aria-selected="false">
                        <i class="bi bi-bell me-1"></i> Notificaciones
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="disponibilidad-tab" data-bs-toggle="tab"
                        data-bs-target="#disponibilidad" type="button" role="tab"
                        aria-controls="disponibilidad" aria-selected="false">
                        <i class="bi bi-calendar-check me-1"></i> Disponibilidad y zona de servicio
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pagos-tab" data-bs-toggle="tab"
                        data-bs-target="#pagos" type="button" role="tab" aria-controls="pagos"
                        aria-selected="false">
                        <i class="bi bi-cash-stack me-1"></i> Pagos y facturación
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="politicas-tab" data-bs-toggle="tab"
                        data-bs-target="#politicas" type="button" role="tab" aria-controls="politicas"
                        aria-selected="false">
                        <i class="bi bi-file-earmark-text me-1"></i> Políticas de servicio
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="preferencias-tab" data-bs-toggle="tab"
                        data-bs-target="#preferencias" type="button" role="tab"
                        aria-controls="preferencias" aria-selected="false">
                        <i class="bi bi-sliders me-1"></i> Preferencias del panel
                    </button>
                </li>
            </ul>

            <!-- Contenido de cada tab -->
            <div class="tab-content mt-4" id="configTabsContent">
                <!-- Perfil profesional -->
                <div class="tab-pane fade show active" id="perfil" role="tabpanel" aria-labelledby="perfil-tab">
                    <div class="tarjeta p-4">
                        <h2 class="mb-3">Perfil profesional</h2>
                        <p class="text-muted mb-0">
                            Aquí irá el formulario para configurar tu perfil público: nombre comercial, descripción,
                            foto, categorías de servicios, etc.
                        </p>
                    </div>
                </div>

                <!-- Cuenta y seguridad -->
                <div class="tab-pane fade" id="cuenta" role="tabpanel" aria-labelledby="cuenta-tab">
                    <div class="tarjeta p-4">
                        <h2 class="mb-3">Cuenta y seguridad</h2>
                        <p class="text-muted mb-0">
                            Aquí irá el formulario para actualizar tu correo, contraseña, seguridad de la cuenta y
                            opciones avanzadas (como cierre de sesión en todos los dispositivos).
                        </p>
                    </div>
                </div>

                <!-- Notificaciones -->
                <div class="tab-pane fade" id="notificaciones" role="tabpanel" aria-labelledby="notificaciones-tab">
                    <div class="tarjeta p-4">
                        <h2 class="mb-3">Notificaciones</h2>
                        <p class="text-muted mb-0">
                            Aquí podrás elegir qué notificaciones deseas recibir (solicitudes, reseñas, pagos, etc.) y
                            por qué canales (correo, notificaciones internas, etc.).
                        </p>
                    </div>
                </div>

                <!-- Disponibilidad y zona de servicio -->
                <div class="tab-pane fade" id="disponibilidad" role="tabpanel" aria-labelledby="disponibilidad-tab">
                    <div class="tarjeta p-4">
                        <h2 class="mb-3">Disponibilidad y zona de servicio</h2>
                        <p class="text-muted mb-0">
                            Aquí configurarás tus días y horarios de trabajo, así como las zonas o ciudades donde
                            prestas servicios.
                        </p>
                    </div>
                </div>

                <!-- Pagos y facturación -->
                <div class="tab-pane fade" id="pagos" role="tabpanel" aria-labelledby="pagos-tab">
                    <div class="tarjeta p-4">
                        <h2 class="mb-3">Pagos y facturación</h2>
                        <p class="text-muted mb-0">
                            Aquí irá el formulario para definir tus datos bancarios, información fiscal y preferencias de
                            liquidación de pagos.
                        </p>
                    </div>
                </div>

                <!-- Políticas de servicio -->
                <div class="tab-pane fade" id="politicas" role="tabpanel" aria-labelledby="politicas-tab">
                    <div class="tarjeta p-4">
                        <h2 class="mb-3">Políticas de servicio</h2>
                        <p class="text-muted mb-0">
                            Aquí podrás definir tus políticas de cancelación, garantías, tiempos de respuesta y otras
                            condiciones que verán tus clientes antes de contratar.
                        </p>
                    </div>
                </div>

                <!-- Preferencias del panel -->
                <div class="tab-pane fade" id="preferencias" role="tabpanel" aria-labelledby="preferencias-tab">
                    <div class="tarjeta p-4">
                        <h2 class="mb-3">Preferencias del panel</h2>
                        <p class="text-muted mb-0">
                            Aquí configurarás preferencias visuales y de uso del panel: página de inicio por defecto,
                            idioma (en el futuro), formato de hora, etc.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <!-- Enlaces / Información adicional si lo necesitas -->
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- JS del dashboard proveedor (si quieres reaprovechar comportamiento) -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboardProveedor.js"></script>
</body>

</html>
