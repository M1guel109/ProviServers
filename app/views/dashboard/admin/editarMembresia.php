<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
require_once BASE_PATH . '/app/controllers/membresiaController.php';

// llamamos la funcion especifica que exite en dicho controlador
$id = $_GET['id'];

// Llamamos la funcion especifica del controlador y le pasamoas los datos a una variable que podamos manipular en un archivo 
$membresia = mostrarMembresiaId($id);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Editar Membresía</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardFormulario.css">
</head>
<body>
<?php include_once __DIR__ . '/../../layouts/sidebar_administrador.php'; ?>

<main class="contenido">
<?php include_once __DIR__ . '/../../layouts/header_administrador.php'; ?>

<section id="titulo-principal" class="mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="mb-1">Editar Membresía</h1>
            <p class="text-muted mb-0">
                Modifica los detalles y límites de funcionalidad del plan.
            </p>
        </div>
        <div class="col-md-4 d-flex justify-content-end">
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">Admin</a></li>
                    <li class="breadcrumb-item"><a href="#">Membresías</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar Plan</li>
                </ol>
            </nav>
        </div>
    </div>
</section>

<section id="formulario-membresia">
    <div class="contenedor-formulario mx-auto" style="max-width: 800px;">
        <div class="d-flex justify-content-center mb-5">
            <div class="step-indicator active" data-step="1">1. Información General</div>
            <i class="bi bi-arrow-right text-muted mx-3 align-self-center"></i>
            <div class="step-indicator" data-step="2">2. Configuración de Límites</div>
        </div>

        <form id="membershipWizardForm" action="<?= BASE_URL ?>/admin/guardar-membresia" method="post" class="formulario-membresia">
            <input type="hidden" name="id_membresia" value="<?= $membresia['id'] ?? '' ?>">

            <!-- PASO 1 -->
            <div class="wizard-step" id="step-1">
                <div class="card shadow-lg mb-4 border-0">
                    <div class="card-header bg-primary text-white rounded-top">
                        <h5 class="mb-0 fw-bold">1. Información General del Plan</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="tipo" class="form-label">Nombre del Plan</label>
                                <input type="text" class="form-control form-control-lg rounded-3" id="tipo" name="tipo"
                                       placeholder="Ej: Plan Premium Mensual" required maxlength="50"
                                       value="<?= $membresia['tipo'] ?? '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="costo" class="form-label">Costo (COP)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control rounded-3" id="costo" name="costo"
                                           placeholder="Ej: 49900.00" step="0.01" min="0.00" required
                                           value="<?= $membresia['costo'] ?? '' ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="duracion_dias" class="form-label">Duración del Plan (en días)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control rounded-3" id="duracion_dias" name="duracion_dias"
                                           placeholder="Ej: 30" min="1" required
                                           value="<?= $membresia['duracion_dias'] ?? '' ?>">
                                    <span class="input-group-text">Días</span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label for="descripcion" class="form-label">Descripción Detallada</label>
                                <textarea class="form-control rounded-3" id="descripcion" name="descripcion" rows="4"
                                          placeholder="Beneficios y limitaciones del plan" required maxlength="150"><?= $membresia['descripcion'] ?? '' ?></textarea>
                                <div class="form-text">Máximo 150 caracteres.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 2 -->
            <div class="wizard-step d-none" id="step-2">
                <div class="card shadow-lg mb-4 border-0">
                    <div class="card-header bg-primary text-white rounded-top">
                        <h5 class="mb-0 fw-bold">2. Configuración de Acceso y Límites</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="max_servicios_activos" class="form-label">Máx. Servicios Activos</label>
                                <input type="number" class="form-control rounded-3" id="max_servicios_activos" name="max_servicios_activos"
                                       placeholder="Ej: 5" min="1" required
                                       value="<?= $membresia['max_servicios_activos'] ?? '1' ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="orden_visual" class="form-label">Orden Visual</label>
                                <input type="number" class="form-control rounded-3" id="orden_visual" name="orden_visual"
                                       placeholder="Ej: 1" min="1"
                                       value="<?= $membresia['orden_visual'] ?? '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-block">Estadísticas Pro</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="acceso_estadisticas_pro" id="stats_si" value="1"
                                           <?= isset($membresia['acceso_estadisticas_pro']) && $membresia['acceso_estadisticas_pro'] == 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="stats_si">Permitir Acceso</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="acceso_estadisticas_pro" id="stats_no" value="0"
                                           <?= isset($membresia['acceso_estadisticas_pro']) && $membresia['acceso_estadisticas_pro'] == 0 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="stats_no">Sin Acceso</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-block">Subir Videos</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="permite_videos" id="videos_si" value="1"
                                           <?= isset($membresia['permite_videos']) && $membresia['permite_videos'] == 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="videos_si">Permitir Subida</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="permite_videos" id="videos_no" value="0"
                                           <?= isset($membresia['permite_videos']) && $membresia['permite_videos'] == 0 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="videos_no">No Permitir</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-block">Plan Destacado</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="es_destacado" id="destacado_si" value="1"
                                           <?= isset($membresia['es_destacado']) && $membresia['es_destacado'] == 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="destacado_si">Sí, Destacar</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="es_destacado" id="destacado_no" value="0"
                                           <?= isset($membresia['es_destacado']) && $membresia['es_destacado'] == 0 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="destacado_no">No Destacar</label>
                                </div>
                            </div>
                            <div class="col-md-12 mt-3">
                                <label for="estado" class="form-label">Estado del Plan</label>
                                <select class="form-select rounded-3" id="estado" name="estado" required>
                                    <option value="ACTIVO" <?= isset($membresia['estado']) && $membresia['estado']=='ACTIVO' ? 'selected' : '' ?>>ACTIVO</option>
                                    <option value="INACTIVO" <?= isset($membresia['estado']) && $membresia['estado']=='INACTIVO' ? 'selected' : '' ?>>INACTIVO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="button" id="prevBtn" class="btn btn-secondary px-4 shadow-sm" style="display: none;">
                    <i class="bi bi-arrow-left"></i> Atrás
                </button>
                <button type="button" id="nextBtn" class="btn btn-primary px-5 shadow">
                    Siguiente <i class="bi bi-arrow-right"></i>
                </button>
                <button type="submit" id="submitBtn" class="btn btn-success btn-lg px-5 shadow" style="display: none;">
                    <i class="bi bi-check-circle"></i> Guardar Cambios
                </button>
            </div>

        </form>
    </div>
</section>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
<script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
<script src="<?= BASE_URL ?>/public/assets/dashBoard/js/membresias.js"></script>
</body>
</html>
