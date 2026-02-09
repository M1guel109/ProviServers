<!-- app/views/dashboard/proveedor/solicitudes/partials/nuevas.php -->
<?php
$solicitudes = $solicitudesNuevas ?? [];

$totalNuevas = count($solicitudes);

$totalUrgentes = count(array_filter($solicitudes, function ($s) {
    $valor = $s['urgencia'] ?? $s['prioridad'] ?? 'baja';
    return strtolower((string)$valor) === 'alta';
}));

$totalHoy = count(array_filter($solicitudes, function ($s) {
    if (empty($s['fecha_preferida'])) return false;
    return date('Y-m-d', strtotime($s['fecha_preferida'])) === date('Y-m-d');
}));
?>

<!-- Tarjetas de estadísticas -->
<section id="estadisticas-solicitudes" class="d-flex gap-3 flex-wrap">
    <div class="tarjeta-estadistica shadow-sm p-3 bg-white rounded flex-fill">
        <i class="bi bi-inbox icono-estadistica text-primary"></i>
        <div class="valor-estadistica fs-2 fw-bold"><?= $totalNuevas ?></div>
        <div class="etiqueta-estadistica text-muted">Solicitudes Nuevas</div>
    </div>

    <div class="tarjeta-estadistica shadow-sm p-3 bg-white rounded flex-fill border-start border-danger border-4">
        <i class="bi bi-exclamation-triangle icono-estadistica text-danger"></i>
        <div class="valor-estadistica fs-2 fw-bold"><?= $totalUrgentes ?></div>
        <div class="etiqueta-estadistica text-muted">Urgentes (Alta)</div>
    </div>

    <div class="tarjeta-estadistica shadow-sm p-3 bg-white rounded flex-fill">
        <i class="bi bi-calendar-check icono-estadistica text-success"></i>
        <div class="valor-estadistica fs-2 fw-bold"><?= $totalHoy ?></div>
        <div class="etiqueta-estadistica text-muted">Para Hoy</div>
    </div>
</section>

<!-- ✅ LISTADO EN TARJETAS (reemplaza la tabla) -->
<section id="cards-solicitudes" class="mt-4 pb-3">
    <?php if (!empty($solicitudes)) : ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
            <?php foreach ($solicitudes as $solicitud) : ?>

                <?php
                $estado = strtolower($solicitud['estado'] ?? 'pendiente');

                // Ajusta colores según tus estados reales
                $badgeEstado = match ($estado) {
                    'pendiente'   => 'bg-secondary',
                    'aceptada'    => 'bg-success',
                    'rechazada'   => 'bg-danger',
                    'en_proceso'  => 'bg-primary',
                    'finalizada'  => 'bg-success',
                    default       => 'bg-secondary',
                };

                $recibida = isset($solicitud['created_at']) ? date('d/m/y', strtotime($solicitud['created_at'])) : 'N/A';
                $fechaPref = !empty($solicitud['fecha_preferida']) ? date('d/m/Y', strtotime($solicitud['fecha_preferida'])) : 'Sin fecha';

                $payload = json_encode(
                    $solicitud,
                    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                );
                ?>

                <div class="col">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">

                            <!-- Encabezado: cliente + estado -->
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <div class="fw-bold">
                                        <?= htmlspecialchars($solicitud['nombre_cliente'] ?? 'Cliente Desconocido') ?>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="bi bi-telephone"></i>
                                        <?= htmlspecialchars($solicitud['telefono_cliente'] ?? 'N/A') ?>
                                    </div>
                                </div>
                                <span class="badge <?= $badgeEstado ?> text-capitalize">
                                    <?= htmlspecialchars($estado) ?>
                                </span>
                            </div>

                            <!-- Servicio -->
                            <div class="mb-2">
                                <div class="fw-medium">
                                    <i class="bi bi-briefcase"></i>
                                    <?= htmlspecialchars($solicitud['servicio_nombre'] ?? $solicitud['publicacion_titulo'] ?? 'Servicio') ?>
                                </div>
                                <div class="text-muted small">
                                    Recibida: <?= $recibida ?>
                                </div>
                            </div>

                            <!-- Fecha preferida + franja -->
                            <div class="d-flex flex-wrap gap-3 text-muted small">
                                <div>
                                    <i class="bi bi-calendar3"></i>
                                    <span class="fw-medium text-dark"><?= $fechaPref ?></span>
                                </div>
                                <div>
                                    <i class="bi bi-clock"></i>
                                    <?= htmlspecialchars($solicitud['franja_horaria'] ?? 'N/A') ?>
                                </div>
                            </div>

                        </div>

                        <!-- Acciones -->
                        <div class="card-footer bg-white border-0 pt-0">
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary flex-fill"
                                        title="Ver Detalle"
                                        onclick='verDetalle(<?= $payload ?>)'>
                                    <i class="bi bi-eye"></i> Ver
                                </button>

                                <a href="<?= BASE_URL ?>/proveedor/solicitudes?accion=aceptar&id=<?= (int)($solicitud['id'] ?? 0) ?>&tab=nuevas"
                                   class="btn btn-sm btn-outline-success flex-fill"
                                   title="Aceptar">
                                    <i class="bi bi-check-lg"></i> Aceptar
                                </a>

                                <a href="<?= BASE_URL ?>/proveedor/solicitudes?accion=rechazar&id=<?= (int)($solicitud['id'] ?? 0) ?>&tab=nuevas"
                                   class="btn btn-sm btn-outline-danger flex-fill"
                                   title="Rechazar"
                                   onclick="return confirm('¿Rechazar esta solicitud?')">
                                    <i class="bi bi-x-lg"></i> Rechazar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="text-muted text-center p-5 bg-white rounded shadow-sm">
            No tienes solicitudes nuevas por el momento.
        </div>
    <?php endif; ?>
</section>
