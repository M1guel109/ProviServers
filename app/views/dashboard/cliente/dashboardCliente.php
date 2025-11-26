<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers - Portal de Cliente</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Tu CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardCliente.css">
</head>

<body>
    <?php
    // 1. Incluir el Head, CSS y la etiqueta
    include 'app/views/layouts/header_cliente.php';

    ?>


    <!-- Hero Section -->
    <section id="inicio" class="hero-cliente">
        <div class="hero-content">
            <div class="hero-text">
                <h1>¡Bienvenido de nuevo, Carlos! 👋</h1>
                <p>Gestiona tus servicios, explora nuevos proveedores y mantente conectado con los mejores profesionales
                </p>
                <div class="hero-buttons">
                    <button class="btn-primary">
                        <i class="bi bi-plus-circle"></i>
                        Buscar Servicio
                    </button>
                    <button class="btn-secondary">
                        <i class="bi bi-clock-history"></i>
                        Ver Servicios Activos
                    </button>
                </div>
            </div>
            <div class="hero-stats">
                <div class="stat-box">
                    <div class="stat-icon" style="background: #dbeafe;">
                        <i class="bi bi-briefcase" style="color: #0066FF;"></i>
                    </div>
                    <div class="stat-info">
                        <h3>5</h3>
                        <p>Servicios Activos</p>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon" style="background: #dcfce7;">
                        <i class="bi bi-check-circle" style="color: #16a34a;"></i>
                    </div>
                    <div class="stat-info">
                        <h3>23</h3>
                        <p>Completados</p>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon" style="background: #fef3c7;">
                        <i class="bi bi-star-fill" style="color: #f59e0b;"></i>
                    </div>
                    <div class="stat-info">
                        <h3>4.9</h3>
                        <p>Tu Calificación</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="activity-board">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="alert-card pending-payment-alert">
                        <i class="bi bi-credit-card-2-front me-2"></i>
                        <div>
                            <h4>Tienes 1 Pago Pendiente</h4>
                            <p class="mb-1">El pago de "Diseño Gráfico" vence en 3 días.</p>
                            <a href="<?= BASE_URL ?>/customers/quotations" class="alert-link">Pagar Ahora <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="alert-card new-messages-alert">
                        <i class="bi bi-chat-dots me-2"></i>
                        <div>
                            <h4>Mensajes sin Leer</h4>
                            <p class="mb-1">Tienes 2 mensajes nuevos de "Juan García".</p>
                            <a href="<?= BASE_URL ?>/customers/messages" class="alert-link">Ver Conversación <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="alert-card appointment-alert">
                        <i class="bi bi-calendar-check me-2"></i>
                        <div>
                            <h4>Cita Próxima</h4>
                            <p class="mb-1">Cita con "Carlos Ruiz" (Plomería) el 08 Nov a las 10:00 AM.</p>
                            <a href="<?= BASE_URL ?>/customers/services#cita" class="alert-link">Gestionar Cita <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Servicios Activos -->
    <section id="servicios" class="servicios-section">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2>Servicios en Progreso</h2>
                    <p>Mantén el control de todos tus servicios activos</p>
                </div>
                <a href="#" class="ver-todo">Ver todos <i class="bi bi-arrow-right"></i></a>
            </div>

            <div class="servicios-grid">
                <!-- Servicio 1 -->
                <div class="servicio-card">
                    <div class="card-header">
                        <span class="status-badge active">En Progreso</span>
                        <div class="card-menu">
                            <i class="bi bi-three-dots-vertical"></i>
                        </div>
                    </div>

                    <div class="proveedor-info">
                        <img src="https://via.placeholder.com/60" alt="Proveedor">
                        <div>
                            <h4>Juan García</h4>
                            <p>Desarrollo Web Profesional</p>
                            <div class="rating">
                                <span class="stars">★★★★★</span>
                                <span>5.0</span>
                            </div>
                        </div>
                    </div>

                    <div class="servicio-details">
                        <div class="detail-row">
                            <i class="bi bi-calendar3"></i>
                            <span>Inicio: 01 Nov 2024</span>
                        </div>
                        <div class="detail-row">
                            <i class="bi bi-clock"></i>
                            <span>Entrega: 15 Nov 2024</span>
                        </div>
                        <div class="detail-row">
                            <i class="bi bi-cash"></i>
                            <span>$500.00</span>
                        </div>
                    </div>

                    <div class="progress-section">
                        <div class="progress-header">
                            <span>Progreso del proyecto</span>
                            <span class="progress-percent">65%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 65%;"></div>
                        </div>
                    </div>

                    <div class="card-actions">
                        <button class="btn-outline">
                            <i class="bi bi-chat"></i>
                            Mensaje
                        </button>
                        <button class="btn-primary-small">
                            Ver Detalles
                        </button>
                    </div>
                </div>

                <!-- Servicio 2 -->
                <div class="servicio-card">
                    <div class="card-header">
                        <span class="status-badge pending">Pendiente Pago</span>
                        <div class="card-menu">
                            <i class="bi bi-three-dots-vertical"></i>
                        </div>
                    </div>

                    <div class="proveedor-info">
                        <img src="https://via.placeholder.com/60" alt="Proveedor">
                        <div>
                            <h4>María López</h4>
                            <p>Diseño Gráfico & Branding</p>
                            <div class="rating">
                                <span class="stars">★★★★★</span>
                                <span>4.9</span>
                            </div>
                        </div>
                    </div>

                    <div class="servicio-details">
                        <div class="detail-row">
                            <i class="bi bi-calendar3"></i>
                            <span>Completado: 30 Oct 2024</span>
                        </div>
                        <div class="detail-row">
                            <i class="bi bi-exclamation-circle"></i>
                            <span>Vence: 05 Nov 2024</span>
                        </div>
                        <div class="detail-row">
                            <i class="bi bi-cash"></i>
                            <span class="highlight">$150.00</span>
                        </div>
                    </div>

                    <div class="payment-alert">
                        <i class="bi bi-info-circle"></i>
                        <span>Pago pendiente - Vence en 3 días</span>
                    </div>

                    <div class="card-actions">
                        <button class="btn-outline">
                            <i class="bi bi-download"></i>
                            Descargar
                        </button>
                        <button class="btn-primary-small">
                            <i class="bi bi-credit-card"></i>
                            Pagar Ahora
                        </button>
                    </div>
                </div>

                <!-- Servicio 3 -->
                <div class="servicio-card">
                    <div class="card-header">
                        <span class="status-badge scheduled">Programado</span>
                        <div class="card-menu">
                            <i class="bi bi-three-dots-vertical"></i>
                        </div>
                    </div>

                    <div class="proveedor-info">
                        <img src="https://via.placeholder.com/60" alt="Proveedor">
                        <div>
                            <h4>Carlos Ruiz</h4>
                            <p>Plomería y Reparaciones</p>
                            <div class="rating">
                                <span class="stars">★★★★★</span>
                                <span>4.8</span>
                            </div>
                        </div>
                    </div>

                    <div class="servicio-details">
                        <div class="detail-row">
                            <i class="bi bi-calendar-check"></i>
                            <span>Fecha: 08 Nov 2024</span>
                        </div>
                        <div class="detail-row">
                            <i class="bi bi-clock"></i>
                            <span>Hora: 10:00 AM</span>
                        </div>
                        <div class="detail-row">
                            <i class="bi bi-geo-alt"></i>
                            <span>A domicilio</span>
                        </div>
                    </div>

                    <div class="appointment-info">
                        <i class="bi bi-calendar3"></i>
                        <span>Tu cita es en 4 días</span>
                    </div>

                    <div class="card-actions">
                        <button class="btn-outline">
                            <i class="bi bi-pencil"></i>
                            Reprogramar
                        </button>
                        <button class="btn-primary-small">
                            Ver Detalles
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="discovery-section bg-light py-5">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2>Proveedores Recomendados</h2>
                    <p>Basado en tus servicios anteriores y preferencias</p>
                </div>
                <a href="<?= BASE_URL ?>/customers/providers" class="ver-todo">Ver todos <i
                        class="bi bi-arrow-right"></i></a>
            </div>
            <div class="proveedores-grid mb-5">
            </div>

            <div class="section-header mt-5">
                <div>
                    <h2>Explora por Categoría</h2>
                    <p>Encuentra el servicio que necesitas</p>
                </div>
                <a href="<?= BASE_URL ?>/customers/search#categories" class="ver-todo">Ver todas <i
                        class="bi bi-arrow-right"></i></a>
            </div>
            <div class="categorias-grid">
            </div>
        </div>
    </section>
    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>¿Eres un Profesional?</h2>
                <p>Únete a ProviServers y conecta con miles de clientes que necesitan tus servicios</p>
                <button class="btn-cta">
                    <i class="bi bi-briefcase"></i>
                    Conviértete en Proveedor
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div class="footer-logo">
                        <a href="#">
                            <img src="<?= BASE_URL ?>/public/assets/img/logos/LOGO PRINCIPAL.png"
                                alt="Logo Proviservers" class="logo-completo" width="100">
                        </a>

                    </div>
                    <p>Conectamos personas con los mejores proveedores de servicios profesionales.</p>
                </div>
                <div class="footer-col">
                    <h4>Empresa</h4>
                    <ul>
                        <li><a href="#">Sobre Nosotros</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Carreras</a></li>
                        <li><a href="#">Prensa</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Soporte</h4>
                    <ul>
                        <li><a href="#">Centro de Ayuda</a></li>
                        <li><a href="#">Contacto</a></li>
                        <li><a href="#">Términos</a></li>
                        <li><a href="#">Privacidad</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Síguenos</h4>
                    <div class="social-links">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-twitter"></i></a>
                        <a href="#"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 ProviServers. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- Tu JS -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboardCliente.js"></script>
</body>

</html>