<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Registrar Membresía</title>
    <!-- Bootstrap CSS (Consolidado a v5.3.2) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-T3c6CoI9I/f6F5T7K4jNl4g5Qp/0uJ8t6tD9s2XkF15M2f0D4M4uJ8u5k4e0d4q0k5Ww" crossorigin="anonymous">
    <!-- Bootstrap Icons para botones -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <!-- css de estilos globales o generales -->
    <!-- Nota: Asumo que estos archivos CSS definen los estilos para la sidebar, header y main. -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardFormulario.css">
    
 
</head>

<body>
    <!-- SIDEBAR (lateral izquierdo) -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_administrador.php';
    ?>

    <main class="contenido">
        <?php
        include_once __DIR__ . '/../../layouts/header_administrador.php';
        ?>

        <!-- Secciones -->
        <section id="titulo-principal" class="mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-1">Registrar Nuevo Plan de Membresía</h1>
                    <p class="text-muted mb-0">
                        Define los detalles, costos y límites de funcionalidad siguiendo los 2 pasos.
                    </p>
                </div>
                <div class="col-md-4 d-flex justify-content-end">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0">
                            <!-- Los items del breadcrumb se llenan con JS/otro PHP -->
                            <li class="breadcrumb-item"><a href="#">Admin</a></li>
                            <li class="breadcrumb-item"><a href="#">Membresías</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Registrar Plan</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <!-- Formulario Membresía (Añadido el Wizard Control) -->
        <section id="formulario-membresia">
            <div class="contenedor-formulario mx-auto" style="max-width: 800px;">

                <!-- Indicadores del Wizard -->
                <div class="d-flex justify-content-center mb-5">
                    <div class="step-indicator active" data-step="1">
                        1. Información General
                    </div>
                    <i class="bi bi-arrow-right text-muted mx-3 align-self-center"></i>
                    <div class="step-indicator" data-step="2">
                        2. Configuración de Límites
                    </div>
                </div>

                <!-- Formulario Principal -->
                <form id="membershipWizardForm" action="<?= BASE_URL ?>/admin/guardar-membresia" method="post" class="formulario-membresia">

                    <!-- PASO 1: Detalles Básicos del Plan (Información General) -->
                    <div class="wizard-step" id="step-1">
                        <div class="card shadow-lg mb-4 border-0">
                            <div class="card-header bg-primary text-white rounded-top">
                                <h5 class="mb-0 fw-bold">1. Información General del Plan</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <!-- Nombre del Plan (tipo) -->
                                    <div class="col-md-12">
                                        <label for="tipo" class="form-label">Nombre del Plan</label>
                                        <input type="text" class="form-control form-control-lg rounded-3" id="tipo" name="tipo"
                                            placeholder="Ej: Plan Premium Mensual, Prueba Gratis, Plan Anual Pro" required maxlength="50">
                                    </div>

                                    <!-- Costo (costo) -->
                                    <div class="col-md-6">
                                        <label for="costo" class="form-label">Costo (COP)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control rounded-3" id="costo" name="costo"
                                                placeholder="Ej: 49900.00" step="0.01" min="0.00" required>
                                        </div>
                                    </div>

                                    <!-- Duración (duracion_dias) -->
                                    <div class="col-md-6">
                                        <label for="duracion_dias" class="form-label">Duración del Plan (en días)</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control rounded-3" id="duracion_dias" name="duracion_dias"
                                                placeholder="Ej: 30, 90, 365" min="1" required>
                                            <span class="input-group-text">Días</span>
                                        </div>
                                    </div>

                                    <!-- Descripción y Beneficios (descripcion) -->
                                    <div class="col-md-12">
                                        <label for="descripcion" class="form-label">Descripción Detallada</label>
                                        <textarea class="form-control rounded-3" id="descripcion" name="descripcion" rows="4"
                                            placeholder="Detalla los beneficios clave, limitaciones y el público objetivo de este plan." required maxlength="150"></textarea>
                                        <div class="form-text">Máximo 150 caracteres.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Fin Paso 1 -->

                    <!-- PASO 2: Configuración de Funcionalidad y Límites -->
                    <div class="wizard-step d-none" id="step-2">
                        <div class="card shadow-lg mb-4 border-0">
                            <div class="card-header bg-primary text-white rounded-top">
                                <h5 class="mb-0 fw-bold">2. Configuración de Acceso y Límites</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">

                                    <!-- Limite de Servicios Activos -->
                                    <div class="col-md-6">
                                        <label for="max_servicios_activos" class="form-label">Máx. Servicios Activos</label>
                                        <input type="number" class="form-control rounded-3" id="max_servicios_activos" name="max_servicios_activos"
                                            placeholder="Ej: 5" min="1" required>
                                        <div class="form-text">Establece el límite de publicaciones activas permitidas.</div>
                                    </div>

                                    <!-- Orden Visual -->
                                    <div class="col-md-6">
                                        <label for="orden_visual" class="form-label">Orden Visual (Prioridad)</label>
                                        <input type="number" class="form-control rounded-3" id="orden_visual" name="orden_visual"
                                            placeholder="Ej: 1 (siendo 1 el primero en mostrarse)" min="1">
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
                                        <div class="form-text">Incluye reportes avanzados de rendimiento.</div>
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
                                        <div class="form-text">Permite incluir videos en las publicaciones.</div>
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
                                        <div class="form-text">Señala este plan como 'Más Popular'.</div>
                                    </div>

                                    <!-- Estado del Plan -->
                                    <div class="col-md-12 mt-3">
                                        <label for="estado" class="form-label">Estado del Plan</label>
                                        <select class="form-select rounded-3" id="estado" name="estado" required>
                                            <option value="ACTIVO" selected>ACTIVO (Visible para la compra)</option>
                                            <option value="INACTIVO">INACTIVO (Oculto y no disponible)</option>
                                        </select>
                                        <div class="form-text">Controla si los proveedores pueden ver y comprar este plan.</div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div> <!-- Fin Paso 2 -->

                    <!-- Controles de Navegación del Wizard -->
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" id="prevBtn" class="btn btn-secondary px-4 shadow-sm" style="display: none;">
                            <i class="bi bi-arrow-left"></i> Atrás
                        </button>
                        <button type="button" id="nextBtn" class="btn btn-primary px-5 shadow">
                            Siguiente <i class="bi bi-arrow-right"></i>
                        </button>
                        <button type="submit" id="submitBtn" class="btn btn-success btn-lg px-5 shadow" style="display: none;">
                            <i class="bi bi-check-circle"></i> Guardar Nuevo Plan
                        </button>
                    </div>

                </form>
            </div>
        </section>
    </main>


    <footer>
        <!-- Enlaces / Información -->
    </footer>

    <!-- apexcharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Bootstrap JS (Consolidado a v5.3.2) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1fK1E/r6fM+Q6F9c8O+E6O/7uF/6t"
        crossorigin="anonymous"></script>

    <!-- tu javaScript -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/membresias.js"></script>
</body>

</html>