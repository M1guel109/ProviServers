<?php
require_once BASE_PATH . '/app/helpers/session-proveedor.php';
require_once BASE_PATH . '/app/models/servicio-contratado.php';
require_once BASE_PATH . '/app/models/solicitud.php';
require_once BASE_PATH . '/app/helpers/plan-helper.php';

$uid = (int)($_SESSION['user']['id'] ?? 0);
$scModel  = new ServicioContratado();
$solModel = new Solicitud();

$resumen            = $scModel->obtenerResumenDashboardProveedor($uid);
$todosServicios     = $scModel->listarPorProveedorUsuario($uid);
$serviciosRecientes = array_slice($todosServicios, 0, 4);
$resenasRecientes   = $scModel->obtenerResenasRecientesProveedor($uid, 3);
$proximasCitas      = $scModel->obtenerProximasCitasProveedor($uid, 3);
$totalPendientes    = count($solModel->listarPorProveedor($uid));
?>


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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-proveedor.css">
</head>

<body>
    <!-- SIDEBAR (lateral izquierdo) -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar-proveedor.php';
    ?>

    <main class="contenido">
        <?php
        include_once __DIR__ . '/../../layouts/header-proveedor.php';
        ?>

        <!-- Secciones -->
        <!-- BANNER RECORDATORIO MEMBRESÍA -->
        <?php
        $planDash = obtenerPlanActivoProveedor($uid);
        if (planProximoAVencer($uid)):
            $diasDash = (int)$planDash['dias_restantes'];
            $colorDash = $diasDash <= 2 ? 'danger' : 'warning';
        ?>
            <div class="alert alert-<?= $colorDash ?> d-flex align-items-center gap-2 mb-4 rounded-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>
                    <?= $diasDash === 0
                        ? '<strong>Tu membresía venció.</strong> Renuévala para seguir publicando sin límites.'
                        : "<strong>Tu plan vence en {$diasDash} " . ($diasDash === 1 ? 'día' : 'días') . ".</strong> Renuévalo para no perder tus beneficios." ?>
                    <a href="<?= BASE_URL ?>/proveedor/membresia" class="alert-link ms-1">Renovar ahora →</a>
                </span>
            </div>
        <?php endif; ?>

        <!-- titulo con breadcrumb y explicación -->
        <section id="titulo-principal">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Panel de Proveedor</h1>
                    <p class="text-muted mb-0">
                        Bienvenido a tu panel de control. Aquí puedes gestionar tus servicios, revisar solicitudes,
                        dar seguimiento a tus ingresos y mantener actualizada tu información profesional.
                    </p>
                </div>
                <div class="col-md-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/proveedor/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Panel</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <!-- Tarjetas de estadísticas principales -->
        <section id="tarjetas-superiores">
            <div class="tarjeta tarjeta-estadistica">
                <i class="bi bi-cash-coin icono-estadistica"></i>
                <div class="valor-estadistica">
                    <?= $resumen['ingresos_mes'] > 0
                        ? '$' . number_format($resumen['ingresos_mes'], 0, ',', '.')
                        : '$0' ?>
                </div>
                <div class="etiqueta-estadistica">Ingresos del Mes</div>
                <div class="tendencia positiva">
                    <i class="bi bi-graph-up"></i> Servicios finalizados
                </div>
            </div>

            <div class="tarjeta tarjeta-estadistica">
                <i class="bi bi-briefcase icono-estadistica"></i>
                <div class="valor-estadistica"><?= $resumen['servicios_activos'] ?></div>
                <div class="etiqueta-estadistica">Servicios Activos</div>
                <div class="tendencia positiva">
                    <i class="bi bi-hourglass-split"></i> En proceso o pendientes
                </div>
            </div>

            <div class="tarjeta tarjeta-estadistica">
                <i class="bi bi-star icono-estadistica"></i>
                <div class="valor-estadistica">
                    <?= $resumen['calificacion_promedio'] !== null
                        ? number_format($resumen['calificacion_promedio'], 1)
                        : 'N/A' ?>
                </div>
                <div class="etiqueta-estadistica">Calificación</div>
                <div class="tendencia positiva">
                    <i class="bi bi-star-fill"></i> Promedio de tus servicios
                </div>
            </div>

            <div class="tarjeta tarjeta-estadistica">
                <i class="bi bi-clock icono-estadistica"></i>
                <div class="valor-estadistica"><?= $totalPendientes ?></div>
                <div class="etiqueta-estadistica">Solicitudes Pendientes</div>
                <div class="tendencia <?= $totalPendientes > 0 ? 'positiva' : 'negativa' ?>">
                    <i class="bi bi-bell"></i>
                    <?= $totalPendientes > 0 ? 'Requieren atención' : 'Al día' ?>
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
                    <?php if (!empty($serviciosRecientes)): ?>
                        <?php foreach ($serviciosRecientes as $srv):
                            $tituloSrv =
                                $srv['servicio_nombre']
                                ?? $srv['publicacion_titulo_cotizacion']
                                ?? $srv['publicacion_titulo_solicitud']
                                ?? $srv['cotizacion_titulo']
                                ?? $srv['solicitud_titulo']
                                ?? 'Servicio';

                            $estadoSrv = $srv['estado'] ?? 'pendiente';
                            $estadoClass = match ($estadoSrv) {
                                'finalizado'            => 'estado-inactivo',
                                'pendiente', 'confirmado' => 'estado-pendiente',
                                default                 => 'estado-activo'
                            };
                            $estadoLabel = match ($estadoSrv) {
                                'finalizado'  => 'Finalizado',
                                'pendiente'   => 'Pendiente',
                                'confirmado'  => 'Confirmado',
                                'en_proceso'  => 'En proceso',
                                default       => ucfirst($estadoSrv)
                            };
                        ?>
                            <div class="servicio-item">
                                <img src="<?= BASE_URL ?>/public/assets/dashboard/img/imagen-servicio.png" alt="Servicio">
                                <div class="servicio-info">
                                    <div class="servicio-nombre-item"><?= htmlspecialchars($tituloSrv) ?></div>
                                    <div class="servicio-categoria"><?= htmlspecialchars($srv['cliente_nombre'] ?? '') ?></div>
                                </div>
                                <span class="servicio-estado <?= $estadoClass ?>"><?= $estadoLabel ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted small py-3">No tienes servicios contratados aún.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- tarjeta reseñas recientes -->
            <div class="tarjeta">
                <h3>Reseñas Recientes</h3>
                <div class="reseñas-recientes">
                    <?php if (!empty($resenasRecientes)): ?>
                        <?php foreach ($resenasRecientes as $r):
                            $n = (int)round((float)($r['calificacion'] ?? 0));
                        ?>
                            <div class="reseña-item">
                                <div class="reseña-header">
                                    <div class="reseña-cliente"><?= htmlspecialchars($r['cliente_nombre'] ?? 'Cliente') ?></div>
                                    <div class="reseña-calificacion">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <i class="bi bi-star<?= $i < $n ? '-fill' : '' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <?php if (!empty($r['comentario'])): ?>
                                    <div class="reseña-comentario">"<?= htmlspecialchars($r['comentario']) ?>"</div>
                                <?php endif; ?>
                                <div class="reseña-fecha">
                                    <?= $r['created_at'] ? date('d/m/Y', strtotime($r['created_at'])) : '' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted small py-3">Aún no tienes reseñas de clientes.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- tarjeta proximas citas -->
            <div class="tarjeta">
                <h3>Próximas Citas</h3>
                <div class="citas-proximas">
                    <?php if (!empty($proximasCitas)): ?>
                        <?php foreach ($proximasCitas as $cita): ?>
                            <div class="cita-item">
                                <div class="cita-fecha">
                                    <span class="cita-dia">
                                        <?= $cita['fecha_ejecucion'] ? date('d', strtotime($cita['fecha_ejecucion'])) : '--' ?>
                                    </span>
                                    <span class="cita-mes">
                                        <?= $cita['fecha_ejecucion'] ? date('M', strtotime($cita['fecha_ejecucion'])) : '' ?>
                                    </span>
                                </div>
                                <div class="cita-info">
                                    <div class="cita-servicio"><?= htmlspecialchars($cita['servicio_nombre'] ?? 'Servicio') ?></div>
                                    <div class="cita-cliente"><?= htmlspecialchars($cita['cliente_nombre'] ?? '') ?></div>
                                    <div class="cita-hora"><?= htmlspecialchars($cita['franja_horaria'] ?? '') ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted small py-3">No hay citas próximas programadas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <!-- Enlaces / Información -->
    </footer>

    <!-- Bootstrap JS primero (incluye Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- main.js (sidebar, dark mode, tooltips) -->
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>

    <!-- ApexCharts después de Bootstrap para no interferir con Popper -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- JS específico del dashboard -->
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/dashboard-proveedor.js"></script>
</body>

</html>