<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Panel de Proveedores</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <!-- CSS específico para dashboard de proveedores -->
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

        <!-- Secciones -->
        <!-- titulo -->
        <section id="titulo-principal">
            <h1>Panel de Proveedor</h1>
        </section>

        <!-- Tarjetas de estadísticas principales -->
        <section id="tarjetas-superiores">
            <div class="tarjeta tarjeta-estadistica">
                <i class="bi bi-cash-coin icono-estadistica"></i>
                <div class="valor-estadistica">$2,450</div>
                <div class="etiqueta-estadistica">Ingresos del Mes</div>
                <div class="tendencia positiva">
                    <i class="bi bi-arrow-up"></i> 12% vs mes anterior
                </div>
            </div>

            <div class="tarjeta tarjeta-estadistica">
                <i class="bi bi-briefcase icono-estadistica"></i>
                <div class="valor-estadistica">24</div>
                <div class="etiqueta-estadistica">Servicios Activos</div>
                <div class="tendencia positiva">
                    <i class="bi bi-arrow-up"></i> 3 nuevos
                </div>
            </div>

            <div class="tarjeta tarjeta-estadistica">
                <i class="bi bi-star icono-estadistica"></i>
                <div class="valor-estadistica">4.8</div>
                <div class="etiqueta-estadistica">Calificación</div>
                <div class="tendencia positiva">
                    <i class="bi bi-arrow-up"></i> +0.2 este mes
                </div>
            </div>

            <div class="tarjeta tarjeta-estadistica">
                <i class="bi bi-clock icono-estadistica"></i>
                <div class="valor-estadistica">18</div>
                <div class="etiqueta-estadistica">Solicitudes Pendientes</div>
                <div class="tendencia negativa">
                    <i class="bi bi-arrow-down"></i> 5 desde ayer
                </div>
            </div>
        </section>

        <!-- Gráfica Principal -->
        <section id="grafica-principal">
            <div class="grafica-header">
                <h2>Rendimiento de Servicios</h2>
                <select id="periodo">
                    <option value="semanal">Semanal</option>
                    <option value="mensual" selected>Mensual</option>
                    <option value="anual">Anual</option>
                </select>
            </div>
            <div id="chart"></div>
        </section>

        <!-- tarjetas inferiores -->
        <section id="tarjetas-inferiores">
            <!-- tarjeta servicios recientes -->
            <div class="tarjeta">
                <h3>Servicios Recientes</h3>
                <div class="servicios-recientes">
                    <div class="servicio-item">
                        <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/imagen-servicio.png" alt="Servicio">
                        <div class="servicio-info">
                            <div class="servicio-nombre-item">Reparación de tuberías</div>
                            <div class="servicio-categoria">Plomería</div>
                        </div>
                        <span class="servicio-estado estado-activo">Activo</span>
                    </div>
                    <div class="servicio-item">
                        <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/imagen-servicio.png" alt="Servicio">
                        <div class="servicio-info">
                            <div class="servicio-nombre-item">Instalación eléctrica</div>
                            <div class="servicio-categoria">Electricidad</div>
                        </div>
                        <span class="servicio-estado estado-pendiente">Pendiente</span>
                    </div>
                    <div class="servicio-item">
                        <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/imagen-servicio.png" alt="Servicio">
                        <div class="servicio-info">
                            <div class="servicio-nombre-item">Limpieza residencial</div>
                            <div class="servicio-categoria">Limpieza</div>
                        </div>
                        <span class="servicio-estado estado-activo">Activo</span>
                    </div>
                    <div class="servicio-item">
                        <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/imagen-servicio.png" alt="Servicio">
                        <div class="servicio-info">
                            <div class="servicio-nombre-item">Pintura de interiores</div>
                            <div class="servicio-categoria">Pintura</div>
                        </div>
                        <span class="servicio-estado estado-inactivo">Inactivo</span>
                    </div>
                </div>
            </div>

            <!-- tarjeta reseñas recientes -->
            <div class="tarjeta">
                <h3>Reseñas Recientes</h3>
                <div class="reseñas-recientes">
                    <div class="reseña-item">
                        <div class="reseña-header">
                            <div class="reseña-cliente">María González</div>
                            <div class="reseña-calificacion">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                        </div>
                        <div class="reseña-comentario">
                            "Excelente servicio, muy profesional y puntual. Resolvió mi problema rápidamente."
                        </div>
                        <div class="reseña-fecha">Hace 2 días</div>
                    </div>
                    <div class="reseña-item">
                        <div class="reseña-header">
                            <div class="reseña-cliente">Juan Pérez</div>
                            <div class="reseña-calificacion">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star"></i>
                            </div>
                        </div>
                        <div class="reseña-comentario">
                            "Buen trabajo, aunque llegó un poco tarde. El resultado final fue satisfactorio."
                        </div>
                        <div class="reseña-fecha">Hace 5 días</div>
                    </div>
                </div>
            </div>

            <!-- tarjeta proximas citas -->
            <div class="tarjeta">
                <h3>Próximas Citas</h3>
                <div class="citas-proximas">
                    <div class="cita-item">
                        <div class="cita-fecha">
                            <span class="cita-dia">15</span>
                            <span class="cita-mes">Nov</span>
                        </div>
                        <div class="cita-info">
                            <div class="cita-servicio">Reparación de grifo</div>
                            <div class="cita-cliente">Ana Rodríguez</div>
                            <div class="cita-hora">10:00 AM</div>
                        </div>
                    </div>
                    <div class="cita-item">
                        <div class="cita-fecha">
                            <span class="cita-dia">17</span>
                            <span class="cita-mes">Nov</span>
                        </div>
                        <div class="cita-info">
                            <div class="cita-servicio">Instalación de luces</div>
                            <div class="cita-cliente">Carlos López</div>
                            <div class="cita-hora">2:30 PM</div>
                        </div>
                    </div>
                    <div class="cita-item">
                        <div class="cita-fecha">
                            <span class="cita-dia">18</span>
                            <span class="cita-mes">Nov</span>
                        </div>
                        <div class="cita-info">
                            <div class="cita-servicio">Mantenimiento general</div>
                            <div class="cita-cliente">Laura Martínez</div>
                            <div class="cita-hora">9:00 AM</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <!-- Enlaces / Información -->
    </footer>

    <!-- apexcharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- tu javaScript -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboardProveedor.js"></script>
</body>

</html>