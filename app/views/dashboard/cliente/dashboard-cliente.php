<?php
require_once BASE_PATH . '/app/helpers/session-cliente.php';
require_once BASE_PATH . '/app/models/Categoria.php';
require_once BASE_PATH . '/app/models/ServicioContratado.php';
require_once BASE_PATH . '/app/helpers/lang-helper.php';

$objCategoria = new Categoria();
$categorias   = $objCategoria->mostrar() ?: [];
$nombreSaludo = isset($usuarioC['nombres']) ? $usuarioC['nombres'] : 'Cliente';

$uid      = (int)($_SESSION['user']['id'] ?? 0);
$scModel  = new ServicioContratado();
$contratos = $scModel->listarPorClienteUsuario($uid);

$activos     = array_values(array_filter($contratos, fn($c) => in_array($c['estado'], ['pendiente','confirmado','en_proceso'])));
$completados = array_filter($contratos, fn($c) => $c['estado'] === 'finalizado');

$totalActivos     = count($activos);
$totalCompletados = count($completados);

$ratings = array_filter(array_column(array_values($completados), 'mi_calificacion'), fn($v) => is_numeric($v));
$califPromedio = !empty($ratings) ? round(array_sum($ratings) / count($ratings), 1) : null;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Panel de Cliente</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-cliente.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar-cliente.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-cliente.php'; ?>

        <!-- TÍTULO CON BREADCRUMB -->
        <section id="titulo-principal">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><?= __('cliente_bienvenido') ?> </h1>
                    <p class="text-muted mb-0">
                        <?= __('cliente_descripcion') ?>
                    </p>
                </div>
                <div class="col-md-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/cliente/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Panel Principal</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <!-- TARJETAS DE ESTADÍSTICAS (estilo consistente) -->
        <section class="row g-4 mb-5">
            <div class="col-md-3 col-sm-6">
                <div class="tarjeta-estadistica">
                    <i class="bi bi-clock-history icono-estadistica text-primary"></i>
                    <div>
                        <div class="valor-estadistica"><?= $totalActivos ?></div>
                        <div class="etiqueta-estadistica">Servicios Activos</div>
                        <small class="text-primary"><i class="bi bi-hourglass-split"></i> En proceso o pendientes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="tarjeta-estadistica">
                    <i class="bi bi-check-circle icono-estadistica text-success"></i>
                    <div>
                        <div class="valor-estadistica"><?= $totalCompletados ?></div>
                        <div class="etiqueta-estadistica">Completados</div>
                        <small class="text-success"><i class="bi bi-check-circle"></i> Total acumulado</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="tarjeta-estadistica">
                    <i class="bi bi-heart icono-estadistica text-danger"></i>
                    <div>
                        <div class="valor-estadistica"><?= count($contratos) ?></div>
                        <div class="etiqueta-estadistica">Servicios totales</div>
                        <small class="text-primary"><i class="bi bi-briefcase"></i> Contratados</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="tarjeta-estadistica">
                    <i class="bi bi-star icono-estadistica text-warning"></i>
                    <div>
                        <div class="valor-estadistica">
                            <?= $califPromedio !== null ? number_format($califPromedio, 1) : 'N/A' ?>
                        </div>
                        <div class="etiqueta-estadistica">Calificación dada</div>
                        <small class="text-success"><i class="bi bi-star-fill"></i> Promedio a proveedores</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- ACCIONES RÁPIDAS -->
        <section class="mb-5">
            <h5 class="fw-bold mb-4"><?= __('cliente_que_necesitas') ?></h5>
            <div class="d-flex gap-3 flex-wrap">
                <a href="<?= BASE_URL ?>/cliente/explorar" class="btn-primary-proviservers">
                    <i class="bi bi-search"></i> <?= __('cliente_buscar_servicio') ?>
                </a>
                <a href="<?= BASE_URL ?>/cliente/servicios-contratados" class="btn-secondary-proviservers">
                    <i class="bi bi-briefcase"></i> <?= __('cliente_mis_servicios') ?>
                </a>
                <button type="button" class="btn-primary-proviservers" data-bs-toggle="modal" data-bs-target="#modalNecesidad">
                    <i class="bi bi-plus-circle"></i> <?= __('cliente_publicar_necesidad') ?>
                </button>
            </div>
        </section>

        <!-- SERVICIOS EN CURSO -->
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0"><?= __('cliente_servicios_curso') ?></h5>
                <a href="<?= BASE_URL ?>/cliente/servicios-contratados" class="text-primary text-decoration-none small">
                    <?= __('cliente_ver_todos') ?> <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="row g-4">
                <?php if (!empty($activos)): ?>
                    <?php foreach (array_slice($activos, 0, 4) as $sc):
                        $tituloSc =
                            $sc['servicio_nombre']
                            ?? $sc['publicacion_titulo_cotizacion']
                            ?? $sc['publicacion_titulo_solicitud']
                            ?? $sc['cotizacion_titulo']
                            ?? $sc['solicitud_titulo']
                            ?? $sc['necesidad_titulo']
                            ?? 'Servicio';

                        $estadoMap = [
                            'pendiente'  => ['label' => 'Pendiente',  'badge' => 'badge-programado', 'progress' => 25],
                            'confirmado' => ['label' => 'Confirmado', 'badge' => 'badge-programado', 'progress' => 40],
                            'en_proceso' => ['label' => 'En curso',   'badge' => 'badge-en-curso',   'progress' => 70],
                        ];
                        $est = $estadoMap[$sc['estado']] ?? ['label' => ucfirst($sc['estado']), 'badge' => 'badge-programado', 'progress' => 30];

                        $fechaInicio = $sc['solicitud_fecha_preferida'] ?? $sc['necesidad_fecha_preferida'] ?? null;
                    ?>
                    <div class="col-md-6">
                        <div class="card-cliente">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="icono-wrapper bg-primary-light">
                                        <i class="bi bi-briefcase fs-3 text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($tituloSc) ?></h6>
                                        <span class="badge-estado <?= $est['badge'] ?>"><?= $est['label'] ?></span>
                                    </div>
                                </div>
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-person"></i>
                                    <?= htmlspecialchars($sc['proveedor_nombre'] ?? 'Proveedor') ?>
                                </p>
                                <?php if ($fechaInicio): ?>
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-calendar3"></i>
                                    <?= date('d M Y', strtotime($fechaInicio)) ?>
                                </p>
                                <?php endif; ?>
                                <div class="progress-cliente mb-3">
                                    <div class="progress-cliente-fill" style="width: <?= $est['progress'] ?>%"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Progreso: <?= $est['progress'] ?>%</small>
                                    <a href="<?= BASE_URL ?>/cliente/servicios-contratados"
                                       class="btn-card btn-card-outline">
                                        <i class="bi bi-eye"></i> Ver detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <p class="text-muted">No tienes servicios activos en este momento.
                            <a href="<?= BASE_URL ?>/cliente/explorar">Explorar servicios</a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- CATEGORÍAS POPULARES -->
        <section class="mb-5">
            <h5 class="fw-bold mb-4"><?= __('cliente_categorias_populares') ?></h5>
            <div class="row g-4">
                <?php
                $categorias_populares = [
                    ['icono' => 'bi-tree', 'nombre' => 'Jardinería', 'color' => 'success', 'descripcion' => 'Mantenimiento y diseño de jardines'],
                    ['icono' => 'bi-wrench', 'nombre' => 'Plomería', 'color' => 'primary', 'descripcion' => 'Reparaciones e instalaciones'],
                    ['icono' => 'bi-scissors', 'nombre' => 'Belleza', 'color' => 'danger', 'descripcion' => 'Peluquería y estética a domicilio'],
                    ['icono' => 'bi-heart', 'nombre' => 'Mascotas', 'color' => 'warning', 'descripcion' => 'Veterinaria y cuidado'],
                    ['icono' => 'bi-lightning-charge', 'nombre' => 'Electricidad', 'color' => 'info', 'descripcion' => 'Instalaciones y reparaciones'],
                    ['icono' => 'bi-house', 'nombre' => 'Limpieza', 'color' => 'secondary', 'descripcion' => 'Limpieza profunda']
                ];
                foreach ($categorias_populares as $cat):
                ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="card-categoria text-center p-4">
                            <div class="icono-wrapper bg-<?= $cat['color'] ?>-light mx-auto mb-3">
                                <i class="bi <?= $cat['icono'] ?> fs-2 text-<?= $cat['color'] ?>"></i>
                            </div>
                            <h6 class="fw-bold mb-1"><?= $cat['nombre'] ?></h6>
                            <p class="text-muted small mb-3"><?= $cat['descripcion'] ?></p>
                            <a href="<?= BASE_URL ?>/cliente/explorar?categoria=<?= urlencode($cat['nombre']) ?>" class="btn-card btn-card-outline w-100">
                                Ver proveedores <i class="bi bi-arrow-right-short"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

    <!-- MODAL PUBLICAR NECESIDAD -->
    <div class="modal fade modal-cliente" id="modalNecesidad" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i><?= __('cliente_publicar_necesidad') ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formNecesidadModal" action="<?= BASE_URL ?>/cliente/necesidades/crear" method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <input type="hidden" name="accion" value="crear_necesidad">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Categoría <span class="text-danger">*</span></label>
                                <select class="form-select" name="categoria" id="categoria_nec" required>
                                    <option value="">Selecciona una categoría</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['nombre']) ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                    <?php endforeach; ?>
                                    <option value="Otros">Otros</option>
                                </select>
                            </div>
                            <div class="col-md-6 d-none" id="categoriaOtroWrapper_nec">
                                <label class="form-label fw-bold">Especificar categoría <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="categoria_otro" id="categoriaOtro_nec">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Título <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="titulo" id="titulo_nec" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Descripción <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="descripcion" id="descripcion_nec" rows="3" required></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Presupuesto estimado (COP)</label>
                                <input type="number" class="form-control" name="presupuesto_estimado" id="presupuesto_nec" min="0" step="1000">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Fecha deseada <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="fecha_preferida" id="fecha_nec" min="<?= date('Y-m-d') ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Franja horaria <span class="text-danger">*</span></label>
                                <select class="form-select" name="franja_horaria" id="franja_nec" required>
                                    <option value="">Seleccionar</option>
                                    <option value="manana">Mañana (8:00 - 12:00)</option>
                                    <option value="tarde">Tarde (12:00 - 18:00)</option>
                                    <option value="noche">Noche (18:00 - 22:00)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Hora exacta (opcional)</label>
                                <input type="time" class="form-control" name="hora_preferida" id="hora_nec">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Ciudad <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="ciudad" id="ciudad_nec" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Barrio / zona</label>
                                <input type="text" class="form-control" name="zona" id="zona_nec">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Dirección <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="direccion" id="direccion_nec" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Adjuntos (opcional)</label>
                                <input type="file" name="adjuntos[]" class="form-control" accept="image/*,application/pdf" multiple>
                                <small class="text-muted">Máx. 5MB por archivo.</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Publicar necesidad
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL DETALLE SERVICIO -->
    <div class="modal fade modal-cliente" id="modalDetalleServicio" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-file-text me-2"></i>Detalle del servicio
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="icono-wrapper bg-primary-light d-inline-flex p-3 rounded-circle mb-3">
                            <i class="bi bi-tree fs-1 text-primary"></i>
                        </div>
                        <h6 class="fw-bold">Jardinería y Paisajismo</h6>
                        <span class="badge-estado badge-en-curso">En curso</span>
                    </div>
                    <div class="info-row d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Proveedor</span>
                        <span class="fw-semibold">Miguel Torres</span>
                    </div>
                    <div class="info-row d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Teléfono</span>
                        <span class="fw-semibold">+57 300 123 4567</span>
                    </div>
                    <div class="info-row d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Fecha</span>
                        <span class="fw-semibold">20/11/2025</span>
                    </div>
                    <div class="info-row d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Horario</span>
                        <span class="fw-semibold">9:00 AM - 12:00 PM</span>
                    </div>
                    <div class="info-row d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Dirección</span>
                        <span class="fw-semibold">Calle 123 #45-67, Bogotá</span>
                    </div>
                    <div class="progress-cliente mt-4">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Progreso</small>
                            <small class="text-primary fw-bold">65%</small>
                        </div>
                        <div class="progress-cliente">
                            <div class="progress-cliente-fill" style="width: 65%"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary">
                        <i class="bi bi-chat me-2"></i>Contactar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            const sel = document.getElementById('categoria_nec');
            const wrap = document.getElementById('categoriaOtroWrapper_nec');
            const inputOtro = document.getElementById('categoriaOtro_nec');

            function toggleOtro() {
                const isOtro = sel && sel.value === 'Otros';
                if (!wrap) return;
                wrap.classList.toggle('d-none', !isOtro);
                if (inputOtro) {
                    inputOtro.required = isOtro;
                    if (!isOtro) inputOtro.value = '';
                }
            }

            if (sel) {
                sel.addEventListener('change', toggleOtro);
                toggleOtro();
            }
        })();
    </script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/dashboard-cliente.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
</body>

</html>