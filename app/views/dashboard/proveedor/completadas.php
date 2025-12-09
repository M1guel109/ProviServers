<?php
require_once BASE_PATH . '/app/helpers/session_proveedor.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Servicios Completados</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos Globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- CSS Específico -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/completadas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboard-Proveedor.css">
</head>

<body>

    <!-- SIDEBAR (FIJO) -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_proveedor.php'; ?>

    <!-- CONTENIDO GENERAL -->
    <main class="contenido">

        <!-- HEADER (FIJO) -->
        <?php include_once __DIR__ . '/../../layouts/header_proveedor.php'; ?>

        <!-- CONTENIDO INTERNO QUE SÍ DEBE MOVERSE -->
        <div class="contenido-completados">

            <!-- Título -->
            <section id="titulo-principal">
                <h1>Servicios Completados</h1>
                <p class="subtitulo">Historial de servicios finalizados y evaluaciones recibidas</p>
            </section>

            <!-- Estadísticas -->
            <section id="estadisticas-completadas">
                <div class="tarjeta-stat">
                    <i class="bi bi-check-circle icono-stat"></i>
                    <div class="stat-info">
                        <div class="stat-numero">47</div>
                        <div class="stat-label">Total Completados</div>
                    </div>
                </div>
                <div class="tarjeta-stat">
                    <i class="bi bi-calendar-month icono-stat"></i>
                    <div class="stat-info">
                        <div class="stat-numero">12</div>
                        <div class="stat-label">Este Mes</div>
                    </div>
                </div>
                <div class="tarjeta-stat">
                    <i class="bi bi-star-fill icono-stat"></i>
                    <div class="stat-info">
                        <div class="stat-numero">4.8</div>
                        <div class="stat-label">Calificación Promedio</div>
                    </div>
                </div>
                <div class="tarjeta-stat">
                    <i class="bi bi-cash-coin icono-stat"></i>
                    <div class="stat-info">
                        <div class="stat-numero">$8,450</div>
                        <div class="stat-label">Ingresos Totales</div>
                    </div>
                </div>
            </section>

            <!-- Filtros -->
            <section id="filtros-completadas">
                <div class="contenedor-filtros">
                    <div class="grupo-filtro">
                        <label for="filtro-categoria">Categoría</label>
                        <select id="filtro-categoria">
                            <option value="">Todas las categorías</option>
                            <option value="plomeria">Plomería</option>
                            <option value="electricidad">Electricidad</option>
                            <option value="limpieza">Limpieza</option>
                            <option value="pintura">Pintura</option>
                            <option value="jardineria">Jardinería</option>
                        </select>
                    </div>

                    <div class="grupo-filtro">
                        <label for="filtro-periodo">Período</label>
                        <select id="filtro-periodo">
                            <option value="">Todos</option>
                            <option value="semana">Esta semana</option>
                            <option value="mes">Este mes</option>
                            <option value="trimestre">Últimos 3 meses</option>
                            <option value="anio">Este año</option>
                        </select>
                    </div>

                    <div class="grupo-filtro busqueda-filtro">
                        <label for="buscar-completadas">Buscar</label>
                        <input type="text" id="buscar-completadas" placeholder="Buscar por cliente o servicio...">
                    </div>
                </div>
            </section>

            <!-- LISTA DE COMPLETADOS -->
            <section id="lista-completadas" class="grid-completadas">

                <!-- ⭐ SERVICIO COMPLETADO 1 -->
                <div class="tarjeta-completada">

                    <div class="completada-header">
                        <div class="completada-info-principal">
                            <h3 class="completada-titulo">Reparación de sistema eléctrico residencial</h3>

                            <div class="completada-meta">
                                <span class="badge-categoria electricidad">
                                    <i class="bi bi-lightning-charge"></i> Electricidad
                                </span>

                                <span class="completada-feja">
                                    <i class="bi bi-calendar-check"></i> Completado: 25 Nov 2024
                                </span>
                            </div>
                        </div>

                        <div class="completada-estado">
                            <span class="badge-completado">
                                <i class="bi bi-check-circle-fill"></i> Completado
                            </span>
                        </div>
                    </div>

                    <div class="completada-cliente">
                        <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/avatar-cliente.png" class="cliente-avatar">
                        <div class="cliente-info">
                            <div class="cliente-nombre">Carlos Rodríguez</div>
                            <div class="cliente-contacto">
                                <i class="bi bi-telephone"></i> +57 300 123 4567
                            </div>
                        </div>
                    </div>

                    <div class="completada-detalles">
                        <div class="detalle-item">
                            <i class="bi bi-calendar3"></i>
                            <div class="detalle-info">
                                <span class="detalle-label">Fecha de inicio</span>
                                <span class="detalle-valor">20 Nov 2024</span>
                            </div>
                        </div>

                        <div class="detalle-item">
                            <i class="bi bi-clock"></i>
                            <div class="detalle-info">
                                <span class="detalle-label">Duración</span>
                                <span class="detalle-valor">5 días</span>
                            </div>
                        </div>

                        <div class="detalle-item">
                            <i class="bi bi-currency-dollar"></i>
                            <div class="detalle-info">
                                <span class="detalle-label">Monto</span>
                                <span class="detalle-valor">$450.000</span>
                            </div>
                        </div>
                    </div>

                    <div class="completada-calificacion">
                        <div class="calificacion-header">
                            <span class="calificacion-titulo">Calificación del cliente</span>
                            <div class="estrellas">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <span class="calificacion-numero">5.0</span>
                            </div>
                        </div>

                        <p class="calificacion-comentario">
                            "Excelente trabajo, muy profesional y cumplió con todos los tiempos establecidos. Recomendado al 100%"
                        </p>
                    </div>

                    <div class="completada-acciones">
                        <button class="btn-accion btn-ver-detalles">
                            <i class="bi bi-eye"></i> Ver Detalles Completos
                        </button>

                        <button class="btn-accion btn-descargar">
                            <i class="bi bi-download"></i> Descargar Factura
                        </button>

                        <button class="btn-accion btn-contactar">
                            <i class="bi bi-chat-dots"></i> Contactar Cliente
                        </button>
                    </div>

                </div>

            </section>

        </div> <!-- FIN contenido-completados -->

    </main>

    <!-- Footer -->
    <footer></footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JS -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/completadas.js"></script>

</body>
</html>
