<?php
require_once BASE_PATH . '/app/helpers/session_proveedor.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Servicios en Proceso</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <!-- CSS específico para en proceso -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/enProcesos.css">
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
            <h1>Servicios en Proceso</h1>
            <p class="subtitulo">Gestiona los servicios que actualmente estás realizando</p>
        </section>

        <!-- Estadísticas rápidas -->
        <section id="estadisticas-proceso">
            <div class="tarjeta-stat">
                <i class="bi bi-hourglass-split icono-stat"></i>
                <div class="stat-info">
                    <div class="stat-numero">8</div>
                    <div class="stat-label">En Proceso</div>
                </div>
            </div>
            <div class="tarjeta-stat">
                <i class="bi bi-calendar-check icono-stat"></i>
                <div class="stat-info">
                    <div class="stat-numero">3</div>
                    <div class="stat-label">Para Hoy</div>
                </div>
            </div>
            <div class="tarjeta-stat">
                <i class="bi bi-clock-history icono-stat"></i>
                <div class="stat-info">
                    <div class="stat-numero">2</div>
                    <div class="stat-label">Próximos a Vencer</div>
                </div>
            </div>
            <div class="tarjeta-stat">
                <i class="bi bi-percent icono-stat"></i>
                <div class="stat-info">
                    <div class="stat-numero">68%</div>
                    <div class="stat-label">Progreso Promedio</div>
                </div>
            </div>
        </section>

        <!-- Filtros y búsqueda -->
        <section id="filtros-proceso">
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
                    <label for="filtro-prioridad">Prioridad</label>
                    <select id="filtro-prioridad">
                        <option value="">Todas</option>
                        <option value="alta">Alta</option>
                        <option value="media">Media</option>
                        <option value="baja">Baja</option>
                    </select>
                </div>
                <div class="grupo-filtro busqueda-filtro">
                    <label for="buscar-proceso">Buscar</label>
                    <input type="text" id="buscar-proceso" placeholder="Buscar por cliente o servicio...">
                </div>
            </div>
        </section>

        <!-- Lista de servicios en proceso -->
        <section id="lista-procesos">
            <!-- Servicio 1 -->
            <div class="tarjeta-proceso">
                <div class="proceso-header">
                    <div class="proceso-info-principal">
                        <h3 class="proceso-titulo">Reparación de sistema eléctrico</h3>
                        <div class="proceso-meta">
                            <span class="badge-categoria electricidad">
                                <i class="bi bi-lightning-charge"></i> Electricidad
                            </span>
                            <span class="proceso-fecha">
                                <i class="bi bi-calendar3"></i> Inicio: 28 Nov 2024
                            </span>
                        </div>
                    </div>
                    <div class="proceso-prioridad">
                        <span class="badge-prioridad alta">Alta</span>
                    </div>
                </div>

                <div class="proceso-cliente">
                    <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/avatar-cliente.png" alt="Cliente" class="cliente-avatar">
                    <div class="cliente-info">
                        <div class="cliente-nombre">Carlos Rodríguez</div>
                        <div class="cliente-contacto">
                            <i class="bi bi-telephone"></i> +57 300 123 4567
                        </div>
                    </div>
                </div>

                <div class="proceso-progreso">
                    <div class="progreso-header">
                        <span class="progreso-label">Progreso del servicio</span>
                        <span class="progreso-porcentaje">75%</span>
                    </div>
                    <div class="barra-progreso">
                        <div class="barra-progreso-fill" style="width: 75%"></div>
                    </div>
                    <div class="proceso-etapas">
                        <span class="etapa completada"><i class="bi bi-check-circle-fill"></i> Inspección</span>
                        <span class="etapa completada"><i class="bi bi-check-circle-fill"></i> Materiales</span>
                        <span class="etapa activa"><i class="bi bi-arrow-right-circle-fill"></i> Instalación</span>
                        <span class="etapa pendiente"><i class="bi bi-circle"></i> Pruebas</span>
                    </div>
                </div>

                <div class="proceso-acciones">
                    <button class="btn-accion btn-actualizar">
                        <i class="bi bi-arrow-clockwise"></i> Actualizar Estado
                    </button>
                    <button class="btn-accion btn-contactar">
                        <i class="bi bi-chat-dots"></i> Contactar Cliente
                    </button>
                    <button class="btn-accion btn-completar">
                        <i class="bi bi-check-circle"></i> Marcar Completado
                    </button>
                </div>
            </div>

            <!-- Servicio 2 -->
            <div class="tarjeta-proceso">
                <div class="proceso-header">
                    <div class="proceso-info-principal">
                        <h3 class="proceso-titulo">Limpieza profunda residencial</h3>
                        <div class="proceso-meta">
                            <span class="badge-categoria limpieza">
                                <i class="bi bi-droplet"></i> Limpieza
                            </span>
                            <span class="proceso-fecha">
                                <i class="bi bi-calendar3"></i> Inicio: 29 Nov 2024
                            </span>
                        </div>
                    </div>
                    <div class="proceso-prioridad">
                        <span class="badge-prioridad media">Media</span>
                    </div>
                </div>

                <div class="proceso-cliente">
                    <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/avatar-cliente.png" alt="Cliente" class="cliente-avatar">
                    <div class="cliente-info">
                        <div class="cliente-nombre">María González</div>
                        <div class="cliente-contacto">
                            <i class="bi bi-telephone"></i> +57 301 987 6543
                        </div>
                    </div>
                </div>

                <div class="proceso-progreso">
                    <div class="progreso-header">
                        <span class="progreso-label">Progreso del servicio</span>
                        <span class="progreso-porcentaje">45%</span>
                    </div>
                    <div class="barra-progreso">
                        <div class="barra-progreso-fill" style="width: 45%"></div>
                    </div>
                    <div class="proceso-etapas">
                        <span class="etapa completada"><i class="bi bi-check-circle-fill"></i> Evaluación</span>
                        <span class="etapa activa"><i class="bi bi-arrow-right-circle-fill"></i> Limpieza</span>
                        <span class="etapa pendiente"><i class="bi bi-circle"></i> Desinfección</span>
                        <span class="etapa pendiente"><i class="bi bi-circle"></i> Revisión</span>
                    </div>
                </div>

                <div class="proceso-acciones">
                    <button class="btn-accion btn-actualizar">
                        <i class="bi bi-arrow-clockwise"></i> Actualizar Estado
                    </button>
                    <button class="btn-accion btn-contactar">
                        <i class="bi bi-chat-dots"></i> Contactar Cliente
                    </button>
                    <button class="btn-accion btn-completar">
                        <i class="bi bi-check-circle"></i> Marcar Completado
                    </button>
                </div>
            </div>

            <!-- Servicio 3 -->
            <div class="tarjeta-proceso">
                <div class="proceso-header">
                    <div class="proceso-info-principal">
                        <h3 class="proceso-titulo">Instalación de tubería principal</h3>
                        <div class="proceso-meta">
                            <span class="badge-categoria plomeria">
                                <i class="bi bi-wrench"></i> Plomería
                            </span>
                            <span class="proceso-fecha">
                                <i class="bi bi-calendar3"></i> Inicio: 30 Nov 2024
                            </span>
                        </div>
                    </div>
                    <div class="proceso-prioridad">
                        <span class="badge-prioridad alta">Alta</span>
                    </div>
                </div>

                <div class="proceso-cliente">
                    <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/avatar-cliente.png" alt="Cliente" class="cliente-avatar">
                    <div class="cliente-info">
                        <div class="cliente-nombre">Pedro Martínez</div>
                        <div class="cliente-contacto">
                            <i class="bi bi-telephone"></i> +57 302 456 7890
                        </div>
                    </div>
                </div>

                <div class="proceso-progreso">
                    <div class="progreso-header">
                        <span class="progreso-label">Progreso del servicio</span>
                        <span class="progreso-porcentaje">30%</span>
                    </div>
                    <div class="barra-progreso">
                        <div class="barra-progreso-fill" style="width: 30%"></div>
                    </div>
                    <div class="proceso-etapas">
                        <span class="etapa completada"><i class="bi bi-check-circle-fill"></i> Cotización</span>
                        <span class="etapa activa"><i class="bi bi-arrow-right-circle-fill"></i> Excavación</span>
                        <span class="etapa pendiente"><i class="bi bi-circle"></i> Instalación</span>
                        <span class="etapa pendiente"><i class="bi bi-circle"></i> Pruebas</span>
                    </div>
                </div>

                <div class="proceso-acciones">
                    <button class="btn-accion btn-actualizar">
                        <i class="bi bi-arrow-clockwise"></i> Actualizar Estado
                    </button>
                    <button class="btn-accion btn-contactar">
                        <i class="bi bi-chat-dots"></i> Contactar Cliente
                    </button>
                    <button class="btn-accion btn-completar">
                        <i class="bi bi-check-circle"></i> Marcar Completado
                    </button>
                </div>
            </div>

            <!-- Servicio 4 -->
            <div class="tarjeta-proceso">
                <div class="proceso-header">
                    <div class="proceso-info-principal">
                        <h3 class="proceso-titulo">Pintura de fachada exterior</h3>
                        <div class="proceso-meta">
                            <span class="badge-categoria pintura">
                                <i class="bi bi-paint-bucket"></i> Pintura
                            </span>
                            <span class="proceso-fecha">
                                <i class="bi bi-calendar3"></i> Inicio: 1 Dic 2024
                            </span>
                        </div>
                    </div>
                    <div class="proceso-prioridad">
                        <span class="badge-prioridad baja">Baja</span>
                    </div>
                </div>

                <div class="proceso-cliente">
                    <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/avatar-cliente.png" alt="Cliente" class="cliente-avatar">
                    <div class="cliente-info">
                        <div class="cliente-nombre">Ana López</div>
                        <div class="cliente-contacto">
                            <i class="bi bi-telephone"></i> +57 303 654 3210
                        </div>
                    </div>
                </div>

                <div class="proceso-progreso">
                    <div class="progreso-header">
                        <span class="progreso-label">Progreso del servicio</span>
                        <span class="progreso-porcentaje">60%</span>
                    </div>
                    <div class="barra-progreso">
                        <div class="barra-progreso-fill" style="width: 60%"></div>
                    </div>
                    <div class="proceso-etapas">
                        <span class="etapa completada"><i class="bi bi-check-circle-fill"></i> Preparación</span>
                        <span class="etapa completada"><i class="bi bi-check-circle-fill"></i> Primera Capa</span>
                        <span class="etapa activa"><i class="bi bi-arrow-right-circle-fill"></i> Segunda Capa</span>
                        <span class="etapa pendiente"><i class="bi bi-circle"></i> Acabados</span>
                    </div>
                </div>

                <div class="proceso-acciones">
                    <button class="btn-accion btn-actualizar">
                        <i class="bi bi-arrow-clockwise"></i> Actualizar Estado
                    </button>
                    <button class="btn-accion btn-contactar">
                        <i class="bi bi-chat-dots"></i> Contactar Cliente
                    </button>
                    <button class="btn-accion btn-completar">
                        <i class="bi bi-check-circle"></i> Marcar Completado
                    </button>
                </div>
            </div>
        </section>

    </main>

    <footer>
        <!-- Enlaces / Información -->
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- tu javaScript -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/enProceso.js"></script>
</body>

</html>