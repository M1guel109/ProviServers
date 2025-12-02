<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Registrar Membresía</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- CSS del dashboard (usaremos el mismo que registrarUsuario, si los estilos son genéricos) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/registrarUsuario.css">
</head>

<body>
    <!-- SIDEBAR (lateral izquierdo) -->
    <?php
    // Asumiendo que el sidebar_administrador.php ya fue actualizado con el enlace de Membresías
    include_once __DIR__ . '/../../layouts/sidebar_administrador.php';
    ?>

    <main class="contenido">
        <?php
        include_once __DIR__ . '/../../layouts/header_administrador.php';
        ?>

        <!-- Secciones -->
        <!-- titulo -->
        <section id="titulo-principal">
            <div class="row">
                <div class="col-md-8">
                    <h1 class="mb-1">Registrar Nuevo Plan de Membresía</h1>
                    <p class="text-muted mb-0">
                        Define los detalles, costos y límites de funcionalidad que los proveedores podrán adquirir.
                    </p>
                </div>
                <div class="col-md-4">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0"></ol>
                    </nav>
                </div>
            </div>
        </section>

        <!-- Formulario Membresía -->
        <section id="formulario-membresia">
            <div class="contenedor-formulario">
                <!-- Formulario -->
                <form action="<?= BASE_URL ?>/admin/guardar-membresia" method="post" class="formulario-membresia">
                    
                    <!-- Tarjeta 1: Detalles Básicos del Plan -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">1. Información General</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Nombre del Plan (tipo) -->
                                <div class="col-md-12">
                                    <label for="tipo" class="form-label">Nombre del Plan</label>
                                    <input type="text" class="form-control" id="tipo" name="tipo"
                                        placeholder="Ej: Plan Premium Mensual, Prueba Gratis, Plan Anual Pro" required maxlength="50">
                                </div>

                                <!-- Costo (costo) -->
                                <div class="col-md-6">
                                    <label for="costo" class="form-label">Costo (COP)</label>
                                    <input type="number" class="form-control" id="costo" name="costo"
                                        placeholder="Ej: 49900.00" 
                                        step="0.01" min="0.00" required>
                                </div>

                                <!-- Duración (duracion_dias) -->
                                <div class="col-md-6">
                                    <label for="duracion_dias" class="form-label">Duración del Plan (en días)</label>
                                    <input type="number" class="form-control" id="duracion_dias" name="duracion_dias"
                                        placeholder="Ej: 30, 90, 365" 
                                        min="1" required>
                                </div>

                                <!-- Descripción y Beneficios (descripcion) -->
                                <div class="col-md-12">
                                    <label for="descripcion" class="form-label">Descripción Detallada</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="4"
                                        placeholder="Detalla los beneficios clave, limitaciones y el público objetivo de este plan." required maxlength="150"></textarea>
                                    <div class="form-text">Máximo 150 caracteres.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta 2: Configuración de Funcionalidad y Límites -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">2. Configuración de Acceso y Límites</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                
                                <!-- Limite de Servicios Activos -->
                                <div class="col-md-6">
                                    <label for="max_servicios_activos" class="form-label">Máx. Servicios Activos</label>
                                    <input type="number" class="form-control" id="max_servicios_activos" name="max_servicios_activos"
                                        placeholder="Ej: 5 (Número de servicios que pueden publicar)" min="1" required>
                                    <div class="form-text">Establece el límite de publicaciones activas permitidas.</div>
                                </div>
                                
                                <!-- Orden Visual -->
                                <div class="col-md-6">
                                    <label for="orden_visual" class="form-label">Orden Visual (Prioridad)</label>
                                    <input type="number" class="form-control" id="orden_visual" name="orden_visual"
                                        placeholder="Ej: 1, 2, 3 (siendo 1 el primero en mostrarse)" min="1" >
                                    <div class="form-text">Opcional. Dejar vacío si no se requiere orden especial.</div>
                                </div>

                                <!-- Acceso a Estadísticas PRO -->
                                <div class="col-md-4">
                                    <label class="form-label d-block">Estadísticas Pro</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="acceso_estadisticas_pro" id="stats_si" value="1" checked>
                                        <label class="form-check-label" for="stats_si">Permitir Acceso</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="acceso_estadisticas_pro" id="stats_no" value="0">
                                        <label class="form-check-label" for="stats_no">Sin Acceso</label>
                                    </div>
                                    <div class="form-text">Si este plan incluye reportes avanzados de rendimiento.</div>
                                </div>
                                
                                <!-- Permite Videos -->
                                <div class="col-md-4">
                                    <label class="form-label d-block">Subir Videos</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="permite_videos" id="videos_si" value="1" checked>
                                        <label class="form-check-label" for="videos_si">Permitir Subida</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="permite_videos" id="videos_no" value="0">
                                        <label class="form-check-label" for="videos_no">No Permitir</label>
                                    </div>
                                    <div class="form-text">Permite al proveedor incluir videos en sus publicaciones.</div>
                                </div>
                                
                                <!-- Es Destacado (Recomendado) -->
                                <div class="col-md-4">
                                    <label class="form-label d-block">Plan Destacado</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="es_destacado" id="destacado_si" value="1">
                                        <label class="form-check-label" for="destacado_si">Sí, Destacar</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="es_destacado" id="destacado_no" value="0" checked>
                                        <label class="form-check-label" for="destacado_no">No Destacar</label>
                                    </div>
                                    <div class="form-text">Señala este plan como 'Más Popular' en el frontend.</div>
                                </div>
                                
                                <!-- Estado del Plan -->
                                <div class="col-md-12 mt-3">
                                    <label for="estado" class="form-label">Estado del Plan</label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="ACTIVO" selected>ACTIVO (Visible para la compra)</option>
                                        <option value="INACTIVO">INACTIVO (Oculto y no disponible)</option>
                                    </select>
                                    <div class="form-text">Controla si los proveedores pueden ver y comprar este plan.</div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Botón de Envío -->
                    <div class="text-center mt-5">
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow">Guardar Nuevo Plan</button>
                    </div>

                </form>
            </div>
        </section>
        
        <!-- Script para simular el breadcrumb y el BASE_URL (opcional, si es necesario en el entorno) -->
        <!-- <script>
            // Asegúrate de que BASE_URL esté disponible en el JS si lo necesitas para rutas
            // Aquí solo un ejemplo simple para el breadcrumb que no requiere PHP.
            const breadcrumbOl = document.getElementById('breadcrumb');
            if (breadcrumbOl) {
                breadcrumbOl.innerHTML = `
                    <li class="breadcrumb-item"><a href="#">Admin</a></li>
                    <li class="breadcrumb-item"><a href="#">Membresías</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Registrar</li>
                `;
            }
        </script> -->

    </main>


    <footer>
        <!-- Enlaces / Información -->
    </footer>

    <!-- apexcharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- tu javaScript -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
</body>

</html>