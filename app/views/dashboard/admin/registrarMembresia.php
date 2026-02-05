<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Registrar Membresía</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/registrarUsuario.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar_administrador.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header_administrador.php'; ?>

        <section id="titulo-principal">
            <div class="row">
                <div class="col-md-8">
                    <h1 class="mb-1">Nueva Membresía</h1>
                    <p class="text-muted mb-0">Configura un nuevo plan de suscripción según tu base de datos actual.</p>
                </div>
            </div>
        </section>

        <section id="formulario-usuarios">
            <div class="contenedor-formulario">
                
                <form action="<?= BASE_URL ?>/admin/guardar-membresia" method="post" class="formulario-usuario">
                    <div class="row g-3">
                        
                        <div class="col-md-6">
                            <label for="tipo" class="form-label">Nombre del Plan </label>
                            <input type="text" class="form-control" id="tipo" name="tipo"
                                placeholder="Ej: Basico, Premium" maxlength="20" required>
                            <div class="form-text text-warning">Máximo 20 caracteres.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="costo" class="form-label">Costo (COP)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="costo" name="costo"
                                    placeholder="0.00" min="0" step="0.01" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="duracion_dias" class="form-label">Duración (Días)</label>
                            <input type="number" class="form-control" id="duracion_dias" name="duracion_dias"
                                placeholder="Ej: 30" required>
                        </div>

                        <div class="col-md-6">
                            <label for="max_servicios_activos" class="form-label">Máx. Servicios a publicar</label>
                            <input type="number" class="form-control" id="max_servicios_activos" name="max_servicios_activos"
                                value="1" min="1" required>
                        </div>

                        <div class="col-12">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="2" 
                                maxlength="150" placeholder="Breve descripción de beneficios..." required></textarea>
                            <div class="form-text">Máximo 150 caracteres.</div>
                        </div>

                        <hr class="my-4">

                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="es_destacado" name="es_destacado" value="1">
                                <label class="form-check-label" for="es_destacado">¿Es plan Destacado?</label>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="permite_videos" name="permite_videos" value="1">
                                <label class="form-check-label" for="permite_videos">¿Permite subir videos?</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="acceso_estadisticas_pro" name="acceso_estadisticas_pro" value="1">
                                <label class="form-check-label" for="acceso_estadisticas_pro">¿Acceso a Estadísticas Pro?</label>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="estado" name="estado" value="ACTIVO" checked>
                                <label class="form-check-label" for="estado">Estado ACTIVO</label>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary px-5">Guardar Membresía</button>
                        </div>

                    </div>
                </form>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- tu javaScript -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/app.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>