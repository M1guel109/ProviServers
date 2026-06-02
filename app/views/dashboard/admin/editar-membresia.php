<?php
require_once BASE_PATH . '/app/helpers/session-admin.php';
// ✅ CORREGIDO: kebab-case, sin mayúscula
require_once BASE_PATH . '/app/models/membresia.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . '/admin/consultar-membresias');
    exit;
}

$id           = (int) $_GET['id'];
$objMembresia = new Membresia();
$membresia    = $objMembresia->mostrarId($id);

if (!$membresia) {
    // ✅ CORREGIDO: ruta correcta
    header('Location: ' . BASE_URL . '/admin/consultar-membresias');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Editar Membresía</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/registrar-usuario.css">
</head>
<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar-administrador.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-administrador.php'; ?>

        <section id="titulo-principal" class="mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-1">Editar Membresía</h1>
                    <p class="text-muted mb-0">Modifica los detalles del plan.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="<?= BASE_URL ?>/admin/consultar-membresias"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
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

                <form id="membershipWizardForm"
                      action="<?= BASE_URL ?>/admin/actualizar-membresia"
                      method="POST"
                      class="formulario-membresia">

                    <input type="hidden" name="id"     value="<?= (int)$membresia['id'] ?>">
                    <input type="hidden" name="accion" value="actualizar">

                    <!-- PASO 1 -->
                    <div class="wizard-step" id="step-1">
                        <div class="card shadow-sm mb-4 border-0">
                            <div class="card-header bg-primary text-white rounded-top">
                                <h5 class="mb-0 fw-bold">1. Información General del Plan</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">

                                    <div class="col-md-12">
                                        <label for="tipo" class="form-label">Nombre del Plan</label>
                                        <input type="text" class="form-control" id="tipo" name="tipo"
                                               placeholder="Ej: Plan Premium Mensual"
                                               required maxlength="50"
                                               value="<?= htmlspecialchars($membresia['tipo'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="costo" class="form-label">Costo (COP)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="costo" name="costo"
                                                   step="0.01" min="0" required
                                                   value="<?= htmlspecialchars($membresia['costo'] ?? '0') ?>">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="duracion_dias" class="form-label">Duración (días)</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="duracion_dias"
                                                   name="duracion_dias" min="1" required
                                                   value="<?= (int)($membresia['duracion_dias'] ?? 30) ?>">
                                            <span class="input-group-text">Días</span>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="descripcion" class="form-label">Descripción</label>
                                        <textarea class="form-control" id="descripcion" name="descripcion"
                                                  rows="3" maxlength="150"
                                                  required><?= htmlspecialchars($membresia['descripcion'] ?? '') ?></textarea>
                                        <div class="form-text">Máximo 150 caracteres.</div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PASO 2 -->
                    <div class="wizard-step d-none" id="step-2">
                        <div class="card shadow-sm mb-4 border-0">
                            <div class="card-header bg-primary text-white rounded-top">
                                <h5 class="mb-0 fw-bold">2. Configuración de Acceso y Límites</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label for="max_servicios_activos" class="form-label">
                                            Máx. Servicios Activos
                                        </label>
                                        <input type="number" class="form-control"
                                               id="max_servicios_activos"
                                               name="max_servicios_activos"
                                               min="1" required
                                               value="<?= (int)($membresia['max_servicios_activos'] ?? 1) ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="orden_visual" class="form-label">Orden Visual</label>
                                        <input type="number" class="form-control"
                                               id="orden_visual" name="orden_visual"
                                               placeholder="Ej: 1"
                                               value="<?= htmlspecialchars($membresia['orden_visual'] ?? '') ?>">
                                    </div>

                                    <!-- ✅ Radios con valores correctos -->
                                    <div class="col-md-4">
                                        <label class="form-label d-block">Estadísticas Pro</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                   name="acceso_estadisticas_pro" value="1"
                                                   <?= ($membresia['acceso_estadisticas_pro'] == 1) ? 'checked' : '' ?>>
                                            <label class="form-check-label">Sí</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                   name="acceso_estadisticas_pro" value="0"
                                                   <?= ($membresia['acceso_estadisticas_pro'] == 0) ? 'checked' : '' ?>>
                                            <label class="form-check-label">No</label>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label d-block">Permite Videos</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                   name="permite_videos" value="1"
                                                   <?= ($membresia['permite_videos'] == 1) ? 'checked' : '' ?>>
                                            <label class="form-check-label">Sí</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                   name="permite_videos" value="0"
                                                   <?= ($membresia['permite_videos'] == 0) ? 'checked' : '' ?>>
                                            <label class="form-check-label">No</label>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label d-block">Plan Destacado</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                   name="es_destacado" value="1"
                                                   <?= ($membresia['es_destacado'] == 1) ? 'checked' : '' ?>>
                                            <label class="form-check-label">Sí</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                   name="es_destacado" value="0"
                                                   <?= ($membresia['es_destacado'] == 0) ? 'checked' : '' ?>>
                                            <label class="form-check-label">No</label>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mt-2">
                                        <label for="estado" class="form-label">Estado del Plan</label>
                                        <select class="form-select" id="estado" name="estado" required>
                                            <option value="ACTIVO"
                                                <?= ($membresia['estado'] === 'ACTIVO') ? 'selected' : '' ?>>
                                                ACTIVO
                                            </option>
                                            <option value="INACTIVO"
                                                <?= ($membresia['estado'] === 'INACTIVO') ? 'selected' : '' ?>>
                                                INACTIVO
                                            </option>
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- NAVEGACIÓN WIZARD -->
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" id="prevBtn" class="btn btn-secondary px-4"
                                style="display:none;">
                            <i class="bi bi-arrow-left"></i> Atrás
                        </button>
                        <button type="button" id="nextBtn" class="btn btn-primary px-5">
                            Siguiente <i class="bi bi-arrow-right"></i>
                        </button>
                        <button type="submit" id="submitBtn"
                                class="btn btn-success btn-lg px-5"
                                style="display:none;">
                            <i class="bi bi-check-circle"></i> Guardar Cambios
                        </button>
                    </div>

                </form>
            </div>
        </section>
    </main>

    <footer></footer>

    <!-- ✅ SweetAlert primero -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>const BASE_URL = "<?= BASE_URL ?>";</script>
    <!-- ✅ Sin apexcharts ni dashboard.js -->
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/membresias.js"></script>

</body>
</html>