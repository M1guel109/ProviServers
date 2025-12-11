<?php
require_once BASE_PATH . '/app/helpers/session_proveedor.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Reseñas y Calificaciones</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
     <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboard-Proveedor.css">
    <!-- CSS específico para reseñas -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/resenas.css">
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
            <h1>Reseñas y Calificaciones</h1>
            <p class="descripcion-seccion">Gestiona las opiniones de tus clientes y responde a sus comentarios. Las reseñas ayudan a mejorar tu reputación y atraer más clientes.</p>
        </section>

        <!-- Tarjetas de estadísticas de reseñas -->
        <section id="tarjetas-superiores">
            <div class="tarjeta tarjeta-estadistica">
                <i class="bi bi-star-fill icono-estadistica"></i>
                <div class="valor-estadistica">4.8</div>
                <div class="etiqueta-estadistica">Calificación Promedio</div>
                <div class="tendencia positiva">
                    <i class="bi bi-arrow-up"></i> +0.3 este mes
                </div>
            </div>

            <div class="tarjeta tarjeta-estadistica">
                <i class="bi bi-chat-square-text icono-estadistica"></i>
                <div class="valor-estadistica">127</div>
                <div class="etiqueta-estadistica">Total de Reseñas</div>
                <div class="tendencia positiva">
                    <i class="bi bi-arrow-up"></i> 8 nuevas
                </div>
            </div>

            <div class="tarjeta tarjeta-estadistica">
                <i class="bi bi-hand-thumbs-up icono-estadistica"></i>
                <div class="valor-estadistica">95%</div>
                <div class="etiqueta-estadistica">Recomendación</div>
                <div class="tendencia positiva">
                    <i class="bi bi-arrow-up"></i> Excelente
                </div>
            </div>

            <div class="tarjeta tarjeta-estadistica">
                <i class="bi bi-clock-history icono-estadistica"></i>
                <div class="valor-estadistica">3</div>
                <div class="etiqueta-estadistica">Pendientes de Responder</div>
                <div class="tendencia neutral">
                    <i class="bi bi-dash-circle"></i> Responde pronto
                </div>
            </div>
        </section>

        <!-- Distribución de Calificaciones -->
        <section id="distribucion-calificaciones">
            <div class="tarjeta">
                <h3>Distribución de Calificaciones</h3>
                <div class="calificaciones-detalle">
                    <div class="calificacion-fila">
                        <span class="estrellas-label">5 <i class="bi bi-star-fill"></i></span>
                        <div class="barra-progreso-calificacion">
                            <div class="progreso-fill" style="width: 75%"></div>
                        </div>
                        <span class="porcentaje-label">75%</span>
                    </div>
                    <div class="calificacion-fila">
                        <span class="estrellas-label">4 <i class="bi bi-star-fill"></i></span>
                        <div class="barra-progreso-calificacion">
                            <div class="progreso-fill" style="width: 18%"></div>
                        </div>
                        <span class="porcentaje-label">18%</span>
                    </div>
                    <div class="calificacion-fila">
                        <span class="estrellas-label">3 <i class="bi bi-star-fill"></i></span>
                        <div class="barra-progreso-calificacion">
                            <div class="progreso-fill" style="width: 5%"></div>
                        </div>
                        <span class="porcentaje-label">5%</span>
                    </div>
                    <div class="calificacion-fila">
                        <span class="estrellas-label">2 <i class="bi bi-star-fill"></i></span>
                        <div class="barra-progreso-calificacion">
                            <div class="progreso-fill" style="width: 2%"></div>
                        </div>
                        <span class="porcentaje-label">2%</span>
                    </div>
                    <div class="calificacion-fila">
                        <span class="estrellas-label">1 <i class="bi bi-star-fill"></i></span>
                        <div class="barra-progreso-calificacion">
                            <div class="progreso-fill negativo" style="width: 0%"></div>
                        </div>
                        <span class="porcentaje-label">0%</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filtros -->
        <section id="filtros-resenas">
            <div class="filtros-contenedor">
                <select id="filtro-calificacion" class="filtro-select">
                    <option value="">Todas las calificaciones</option>
                    <option value="5">5 estrellas</option>
                    <option value="4">4 estrellas</option>
                    <option value="3">3 estrellas</option>
                    <option value="2">2 estrellas</option>
                    <option value="1">1 estrella</option>
                </select>

                <select id="filtro-respuesta" class="filtro-select">
                    <option value="">Todas</option>
                    <option value="respondidas">Respondidas</option>
                    <option value="sin-responder">Sin responder</option>
                </select>

                <div class="buscador-resenas">
                    <i class="bi bi-search"></i>
                    <input type="text" id="buscar-resena" placeholder="Buscar en reseñas...">
                </div>
            </div>
        </section>

        <!-- ----------------------------
             CONTENEDOR SCROLL: AQUI VA
             ---------------------------- -->
        <div id="contenedor-scrollable-resenas">
            <!-- Lista de Reseñas -->
            <section id="lista-resenas">
                <!-- Reseña 1 - Sin responder -->
                <div class="tarjeta tarjeta-resena sin-responder">
                    <div class="resena-header">
                        <div class="cliente-info">
                            <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/avatar-cliente.png" alt="Cliente" class="avatar-cliente">
                            <div>
                                <h4 class="nombre-cliente">María González</h4>
                                <div class="calificacion-estrellas">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <span class="calificacion-numero">5.0</span>
                                </div>
                            </div>
                        </div>
                        <div class="resena-meta">
                            <span class="fecha-resena"><i class="bi bi-calendar3"></i> Hace 2 días</span>
                            <span class="badge-estado pendiente">Sin responder</span>
                        </div>
                    </div>

                    <div class="servicio-asociado">
                        <i class="bi bi-laptop"></i> <strong>Servicio:</strong> Reparación de computador portátil
                    </div>

                    <div class="resena-comentario">
                        <p>"Excelente servicio técnico. Muy profesional, rápido y eficiente. Mi laptop quedó funcionando perfectamente. Explicó claramente el problema y la solución. Totalmente recomendado."</p>
                    </div>

                    <div class="resena-acciones">
                        <button class="btn-responder">
                            <i class="bi bi-reply-fill"></i> Responder
                        </button>
                        <button class="btn-reportar">
                            <i class="bi bi-flag"></i> Reportar
                        </button>
                    </div>
                </div>

                <!-- Reseña 2 - Ya respondida -->
                <div class="tarjeta tarjeta-resena respondida">
                    <div class="resena-header">
                        <div class="cliente-info">
                            <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/avatar-cliente.png" alt="Cliente" class="avatar-cliente">
                            <div>
                                <h4 class="nombre-cliente">Carlos Rodríguez</h4>
                                <div class="calificacion-estrellas">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-half"></i>
                                    <span class="calificacion-numero">4.5</span>
                                </div>
                            </div>
                        </div>
                        <div class="resena-meta">
                            <span class="fecha-resena"><i class="bi bi-calendar3"></i> Hace 5 días</span>
                            <span class="badge-estado respondida">Respondida</span>
                        </div>
                    </div>

                    <div class="servicio-asociado">
                        <i class="bi bi-wifi"></i> <strong>Servicio:</strong> Instalación de red WiFi empresarial
                    </div>

                    <div class="resena-comentario">
                        <p>"Buen trabajo en general. La instalación fue correcta y la red funciona bien. Hubo un pequeño retraso en el inicio pero el resultado final fue satisfactorio."</p>
                    </div>

                    <!-- Respuesta del proveedor -->
                    <div class="respuesta-proveedor">
                        <div class="respuesta-header">
                            <i class="bi bi-person-circle"></i>
                            <strong>Tu respuesta</strong>
                            <span class="fecha-respuesta">Hace 4 días</span>
                        </div>
                        <p>"Muchas gracias por tu comentario Carlos. Lamento el retraso inicial, tuvimos un inconveniente con el equipo. Me alegra que el resultado final haya sido de tu agrado. ¡Espero poder ayudarte nuevamente!"</p>
                    </div>

                    <div class="resena-acciones">
                        <button class="btn-editar-respuesta">
                            <i class="bi bi-pencil"></i> Editar respuesta
                        </button>
                    </div>
                </div>

                <!-- Reseña 3 - Sin responder -->
                <div class="tarjeta tarjeta-resena sin-responder">
                    <div class="resena-header">
                        <div class="cliente-info">
                            <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/avatar-cliente.png" alt="Cliente" class="avatar-cliente">
                            <div>
                                <h4 class="nombre-cliente">Ana López</h4>
                                <div class="calificacion-estrellas">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <span class="calificacion-numero">5.0</span>
                                </div>
                            </div>
                        </div>
                        <div class="resena-meta">
                            <span class="fecha-resena"><i class="bi bi-calendar3"></i> Hace 1 semana</span>
                            <span class="badge-estado pendiente">Sin responder</span>
                        </div>
                    </div>

                    <div class="servicio-asociado">
                        <i class="bi bi-laptop"></i> <strong>Servicio:</strong> Mantenimiento preventivo de equipos
                    </div>

                    <div class="resena-comentario">
                        <p>"Servicio impecable. Muy puntual, ordenado y profesional. Hizo un trabajo completo y dejó todo funcionando perfecto. Los precios son justos y la atención es excelente."</p>
                    </div>

                    <div class="resena-acciones">
                        <button class="btn-responder">
                            <i class="bi bi-reply-fill"></i> Responder
                        </button>
                        <button class="btn-reportar">
                            <i class="bi bi-flag"></i> Reportar
                        </button>
                    </div>
                </div>

                <!-- Reseña 4 - Calificación baja -->
                <div class="tarjeta tarjeta-resena respondida">
                    <div class="resena-header">
                        <div class="cliente-info">
                            <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/avatar-cliente.png" alt="Cliente" class="avatar-cliente">
                            <div>
                                <h4 class="nombre-cliente">Pedro Martínez</h4>
                                <div class="calificacion-estrellas baja">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star"></i>
                                    <i class="bi bi-star"></i>
                                    <span class="calificacion-numero">3.0</span>
                                </div>
                            </div>
                        </div>
                        <div class="resena-meta">
                            <span class="fecha-resena"><i class="bi bi-calendar3"></i> Hace 2 semanas</span>
                            <span class="badge-estado respondida">Respondida</span>
                        </div>
                    </div>

                    <div class="servicio-asociado">
                        <i class="bi bi-laptop"></i> <strong>Servicio:</strong> Formateo y reinstalación de sistema
                    </div>

                    <div class="resena-comentario">
                        <p>"El servicio estuvo bien pero esperaba más rapidez. Tardó más de lo acordado inicialmente. El trabajo quedó correcto pero la comunicación podría mejorar."</p>
                    </div>

                    <!-- Respuesta del proveedor -->
                    <div class="respuesta-proveedor">
                        <div class="respuesta-header">
                            <i class="bi bi-person-circle"></i>
                            <strong>Tu respuesta</strong>
                            <span class="fecha-respuesta">Hace 2 semanas</span>
                        </div>
                        <p>"Gracias por tu comentario Pedro. Entiendo tu punto sobre los tiempos. Hubo complicaciones técnicas imprevistas que requirieron más trabajo del estimado. Tomaré en cuenta tu feedback sobre la comunicación. Saludos."</p>
                    </div>

                    <div class="resena-acciones">
                        <button class="btn-editar-respuesta">
                            <i class="bi bi-pencil"></i> Editar respuesta
                        </button>
                    </div>
                </div>

                <!-- Puedes añadir más reseñas aquí replicando la estructura anterior -->

            </section>
        </div>
        <!-- FIN CONTENEDOR SCROLL -->

    </main>

    <footer>
        <!-- Enlaces / Información -->
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- tu javaScript -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/resenas.js"></script>
</body>

</html>
