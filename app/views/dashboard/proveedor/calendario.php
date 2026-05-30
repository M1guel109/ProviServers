<?php
require_once BASE_PATH . '/app/helpers/session-proveedor.php';
require_once BASE_PATH . '/config/database.php';

$uid = (int)($_SESSION['user']['id'] ?? 0);
$calStats   = ['hoy' => 0, 'pendientes' => 0, 'ingresos_hoy' => 0];
$eventosJSON = '[]';
$proximoServicio = null;
$resumenMes = ['confirmado' => 0, 'pendiente_pago' => 0];

try {
    $db  = new Conexion();
    $pdo = $db->getConexion();

    $stProv = $pdo->prepare("SELECT id FROM proveedores WHERE usuario_id = :uid LIMIT 1");
    $stProv->execute([':uid' => $uid]);
    $proveedorId = (int)($stProv->fetchColumn() ?: 0);

    if ($proveedorId > 0) {
        // Servicios para hoy
        $stHoy = $pdo->prepare("
            SELECT COUNT(*) FROM servicios_contratados
            WHERE proveedor_id = :pid AND DATE(fecha_ejecucion) = CURDATE()
              AND estado IN ('confirmado','en_proceso')
        ");
        $stHoy->execute([':pid' => $proveedorId]);
        $calStats['hoy'] = (int)$stHoy->fetchColumn();

        // Solicitudes pendientes
        $stPend = $pdo->prepare("
            SELECT COUNT(*) FROM solicitudes WHERE proveedor_id = :pid AND estado = 'pendiente'
        ");
        $stPend->execute([':pid' => $proveedorId]);
        $calStats['pendientes'] = (int)$stPend->fetchColumn();

        // Ingresos hoy (servicios finalizados hoy)
        $stIngHoy = $pdo->prepare("
            SELECT COALESCE(SUM(COALESCE(c.precio, pub_sol.precio, 0)), 0)
            FROM servicios_contratados sc
            LEFT JOIN cotizaciones c        ON sc.cotizacion_id    = c.id
            LEFT JOIN solicitudes sol       ON sc.solicitud_id     = sol.id
            LEFT JOIN publicaciones pub_sol ON sol.publicacion_id  = pub_sol.id
            WHERE sc.proveedor_id = :pid AND sc.estado = 'finalizado'
              AND DATE(sc.modified_at) = CURDATE()
        ");
        $stIngHoy->execute([':pid' => $proveedorId]);
        $calStats['ingresos_hoy'] = (float)($stIngHoy->fetchColumn() ?: 0);

        // Eventos para el calendario (próximos 90 días)
        $stEvt = $pdo->prepare("
            SELECT sc.fecha_ejecucion, sc.estado,
                   COALESCE(c.titulo, sol.titulo, 'Servicio') AS titulo,
                   TRIM(CONCAT(u.nombre, ' ', COALESCE(u.apellido,''))) AS cliente
            FROM servicios_contratados sc
            LEFT JOIN cotizaciones c  ON sc.cotizacion_id = c.id
            LEFT JOIN solicitudes sol ON sc.solicitud_id  = sol.id
            LEFT JOIN clientes cl     ON sc.cliente_id    = cl.id
            LEFT JOIN usuarios u      ON cl.usuario_id    = u.id
            WHERE sc.proveedor_id = :pid
              AND sc.fecha_ejecucion >= CURDATE()
              AND sc.fecha_ejecucion <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
              AND sc.estado IN ('confirmado','en_proceso','pendiente')
            ORDER BY sc.fecha_ejecucion ASC
        ");
        $stEvt->execute([':pid' => $proveedorId]);
        $eventos = $stEvt->fetchAll(PDO::FETCH_ASSOC);
        $eventosJSON = json_encode(array_map(fn($e) => [
            'date'    => $e['fecha_ejecucion'],
            'title'   => $e['titulo'],
            'cliente' => $e['cliente'],
            'estado'  => $e['estado'],
        ], $eventos));

        // Próximo servicio
        if (!empty($eventos)) {
            $proximoServicio = $eventos[0];
        }

        // Resumen mes
        $stMes = $pdo->prepare("
            SELECT
                COALESCE(SUM(CASE WHEN sc.estado IN ('confirmado','en_proceso','finalizado')
                    THEN COALESCE(c.precio, pub_sol.precio, 0) ELSE 0 END), 0) AS confirmado,
                COALESCE(SUM(CASE WHEN sc.estado = 'pendiente'
                    THEN COALESCE(c.precio, pub_sol.precio, 0) ELSE 0 END), 0) AS pendiente_pago
            FROM servicios_contratados sc
            LEFT JOIN cotizaciones c        ON sc.cotizacion_id    = c.id
            LEFT JOIN solicitudes sol       ON sc.solicitud_id     = sol.id
            LEFT JOIN publicaciones pub_sol ON sol.publicacion_id  = pub_sol.id
            WHERE sc.proveedor_id = :pid
              AND MONTH(sc.fecha_ejecucion) = MONTH(CURDATE())
              AND YEAR(sc.fecha_ejecucion)  = YEAR(CURDATE())
        ");
        $stMes->execute([':pid' => $proveedorId]);
        $mesRow = $stMes->fetch(PDO::FETCH_ASSOC);
        $resumenMes = [
            'confirmado'    => (float)($mesRow['confirmado']    ?? 0),
            'pendiente_pago'=> (float)($mesRow['pendiente_pago'] ?? 0),
        ];
    }
} catch (PDOException $e) {
    error_log('calendario.php: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Mi Agenda de Trabajo</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos Globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-Proveedor.css">

    <!-- CSS Específico -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/calendarioProveedor.css">
</head>

<body>
    <!-- Sidebar Proveedor -->
    <?php include_once __DIR__ . '/../../layouts/sidebar-proveedor.php'; ?>

    <main class="contenido">
        <!-- Header Proveedor -->
        <?php include_once __DIR__ . '/../../layouts/header-proveedor.php'; ?>

        <section id="titulo-principal" class="section-hero mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-1">Mi Agenda de Trabajo</h1>
                    <p class="text-muted mb-0">Administra tus reservas, disponibilidad y controla tus ingresos diarios.</p>
                </div>
                <div class="col-md-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/proveedor/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Calendario</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <!-- CARDS DE ESTADÍSTICAS (estilo tarjetas) -->
        <section class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="tarjeta-estadistica">
                    <i class="bi bi-calendar-check icono-estadistica text-primary"></i>
                    <div>
                        <div class="valor-estadistica"><?= $calStats['hoy'] ?></div>
                        <div class="etiqueta-estadistica">Servicios Hoy</div>
                        <small class="text-success">Confirmados o en proceso</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="tarjeta-estadistica">
                    <i class="bi bi-clock-history icono-estadistica text-warning"></i>
                    <div>
                        <div class="valor-estadistica"><?= $calStats['pendientes'] ?></div>
                        <div class="etiqueta-estadistica">Solicitudes Pendientes</div>
                        <small class="text-warning"><?= $calStats['pendientes'] > 0 ? 'Requieren confirmación' : 'Al día' ?></small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="tarjeta-estadistica">
                    <i class="bi bi-cash-coin icono-estadistica text-success"></i>
                    <div>
                        <div class="valor-estadistica">$<?= $calStats['ingresos_hoy'] > 0 ? number_format($calStats['ingresos_hoy'], 0, ',', '.') : '0' ?></div>
                        <div class="etiqueta-estadistica">Ingresos Hoy</div>
                        <small class="text-primary">Finalizados hoy</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="tarjeta-estadistica">
                    <i class="bi bi-calendar-event icono-estadistica text-info"></i>
                    <div>
                        <div class="valor-estadistica"><?= count(json_decode($eventosJSON, true)) ?></div>
                        <div class="etiqueta-estadistica">Próximas Citas</div>
                        <small class="text-info">Siguientes 90 días</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- NUEVA ESTRUCTURA DIFERENTE -->
        <section class="row">
            <!-- CALENDARIO PRINCIPAL -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <button class="btn btn-light btn-sm" id="prevMonth">
                                <i class="bi bi-chevron-left"></i>
                            </button>

                            <span class="fw-bold mx-3" id="currentMonth">
                                Diciembre 2025
                            </span>

                            <button class="btn btn-light btn-sm" id="nextMonth">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>

                        <div>
                            <button class="btn btn-outline-primary btn-sm" id="todayBtn">
                                Hoy
                            </button>

                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#bloquearDiaModal">
                                <i class="bi bi-calendar-x"></i> Bloquear Día
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Encabezados -->
                        <div class="row text-center fw-bold border-bottom pb-2">
                            <div class="col">Dom</div>
                            <div class="col">Lun</div>
                            <div class="col">Mar</div>
                            <div class="col">Mié</div>
                            <div class="col">Jue</div>
                            <div class="col">Vie</div>
                            <div class="col">Sáb</div>
                        </div>

                        <!-- DÍAS DINÁMICOS -->
                        <div id="calendarDays" class="mt-3">
                            <!-- Se llenará con JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL DERECHO -->
            <div class="col-lg-4">
                <!-- SERVICIOS DEL DÍA -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-briefcase"></i> Servicios del Día
                        </h6>
                    </div>
                    <div class="card-body" id="servicesOfDay">
                        <p class="text-muted">Selecciona un día para ver detalles.</p>
                    </div>
                </div>

                <!-- RESUMEN MES -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-cash-stack"></i> Resumen del Mes
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-1">Ingresos confirmados:</p>
                        <strong class="text-success">$<?= number_format($resumenMes['confirmado'], 0, ',', '.') ?></strong>
                        <hr>
                        <p class="mb-1">Pendiente de confirmar:</p>
                        <strong class="text-warning">$<?= number_format($resumenMes['pendiente_pago'], 0, ',', '.') ?></strong>
                    </div>
                </div>

                <!-- PRÓXIMO SERVICIO -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-calendar-event"></i> Próximo Servicio
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if ($proximoServicio): ?>
                            <p class="mb-1 fw-bold"><?= htmlspecialchars($proximoServicio['titulo']) ?></p>
                            <small class="text-muted d-block mb-1">
                                <i class="bi bi-person"></i> <?= htmlspecialchars($proximoServicio['cliente'] ?? '') ?>
                            </small>
                            <small class="text-primary">
                                <i class="bi bi-calendar3"></i>
                                <?= date('d M Y', strtotime($proximoServicio['fecha_ejecucion'])) ?>
                            </small>
                        <?php else: ?>
                            <p class="text-muted small mb-0">No hay servicios programados próximamente.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- MODAL BLOQUEAR DÍA (centrado) -->
    <div class="modal fade modal-cliente" id="bloquearDiaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-x me-2"></i>Bloquear Día
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label fw-bold">Selecciona la fecha</label>
                    <input type="date" class="form-control mb-3">

                    <label class="form-label fw-bold">Motivo (opcional)</label>
                    <textarea class="form-control" rows="3" placeholder="Ej: Descanso, mantenimiento, etc."></textarea>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger">
                        <i class="bi bi-calendar-x me-2"></i>Confirmar Bloqueo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script>const EVENTOS_CALENDARIO = <?= $eventosJSON ?>;</script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/calendario.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
</body>
</html>