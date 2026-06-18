<?php
$solicitudes = $solicitudesNuevas ?? [];

$totalNuevas = count($solicitudes);

$totalUrgentes = count(array_filter($solicitudes, function ($s) {
    $valor = $s['urgencia'] ?? $s['prioridad'] ?? 'baja';
    return mb_strtolower((string)$valor) === 'alta';
}));

$totalHoy = count(array_filter($solicitudes, function ($s) {
    if (empty($s['fecha_preferida'])) return false;
    return date('Y-m-d', strtotime($s['fecha_preferida'])) === date('Y-m-d');
}));
?>

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

<section id="cards-solicitudes" class="mt-4 pb-3">
    <?php if (!empty($solicitudes)) : ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
            <?php foreach ($solicitudes as $solicitud) : ?>

                <?php
                $estado = mb_strtolower((string)($solicitud['estado'] ?? 'pendiente'));

                $badgeEstado = match ($estado) {
                    'pendiente'   => 'bg-secondary',
                    'aceptada'    => 'bg-success',
                    'rechazada'   => 'bg-danger',
                    'en_proceso'  => 'bg-primary',
                    'finalizado', 'finalizada' => 'bg-success',
                    default       => 'bg-secondary',
                };

                $solicitudId = (int)($solicitud['id'] ?? 0);

                $clienteNombre = $solicitud['nombre_cliente']
                    ?? $solicitud['cliente_nombre']
                    ?? 'Cliente Desconocido';

                $clienteTelefono = $solicitud['telefono_cliente']
                    ?? $solicitud['cliente_telefono']
                    ?? 'N/A';

                $tituloServicio = $solicitud['servicio_nombre']
                    ?? $solicitud['publicacion_titulo']
                    ?? $solicitud['solicitud_titulo']
                    ?? 'Servicio';

                $recibida = !empty($solicitud['created_at'])
                    ? date('d/m/y', strtotime($solicitud['created_at']))
                    : 'N/A';

                $fechaPref = !empty($solicitud['fecha_preferida'])
                    ? date('d/m/Y', strtotime($solicitud['fecha_preferida']))
                    : 'Sin fecha';

                $franja = $solicitud['franja_horaria'] ?? 'N/A';

                $ciudad = $solicitud['ciudad'] ?? '';
                $zona = $solicitud['zona'] ?? '';

                $payload = json_encode(
                    $solicitud,
                    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                );
                ?>

                <div class="col">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <div class="fw-bold">
                                        <?= htmlspecialchars($clienteNombre) ?>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="bi bi-telephone"></i>
                                        <?= htmlspecialchars($clienteTelefono) ?>
                                    </div>
                                </div>

                                <span class="badge <?= $badgeEstado ?> text-capitalize">
                                    <?= htmlspecialchars($estado) ?>
                                </span>
                            </div>

                            <div class="mb-2">
                                <div class="fw-medium">
                                    <i class="bi bi-briefcase"></i>
                                    <?= htmlspecialchars($tituloServicio) ?>
                                </div>
                                <div class="text-muted small">
                                    Recibida: <?= htmlspecialchars($recibida) ?>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-3 text-muted small mb-2">
                                <div>
                                    <i class="bi bi-calendar3"></i>
                                    <span class="fw-medium text-dark"><?= htmlspecialchars($fechaPref) ?></span>
                                </div>
                                <div>
                                    <i class="bi bi-clock"></i>
                                    <?= htmlspecialchars($franja) ?>
                                </div>
                            </div>

                            <?php if ($ciudad !== '') : ?>
                                <div class="text-muted small">
                                    <i class="bi bi-geo-alt"></i>
                                    <?= htmlspecialchars($ciudad . ($zona ? ' · ' . $zona : '')) ?>
                                </div>
                            <?php endif; ?>

                        </div>

                        <div class="card-footer bg-white border-0 pt-0">
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary flex-fill"
                                    type="button"
                                    title="Ver Detalle"
                                    onclick='verDetalle(<?= $payload ?>)'>
                                    <i class="bi bi-eye"></i> Ver
                                </button>

                                <?php if ($solicitudId > 0) : ?>
                                    <a href="<?= BASE_URL ?>/proveedor/solicitudes?accion=aceptar_solicitud&id=<?= $solicitudId ?>&tab=nuevas"
                                        class="btn btn-sm btn-outline-success flex-fill"
                                        title="Aceptar"
                                        onclick="return confirm('¿Confirmas aceptar esta solicitud?')">
                                        <i class="bi bi-check-lg"></i> Aceptar
                                    </a>

                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger flex-fill btn-abrir-rechazo"
                                        title="Rechazar"
                                        data-id="<?= $solicitudId ?>">
                                        <i class="bi bi-x-lg"></i> Rechazar
                                    </button>
                                <?php else : ?>
                                    <button class="btn btn-sm btn-outline-secondary flex-fill" type="button" disabled>
                                        Sin ID
                                    </button>
                                <?php endif; ?>
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

<!-- Modal rechazo con motivo -->
<div class="modal fade" id="modalRechazar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="<?= BASE_URL ?>/proveedor/solicitudes" class="modal-content">
            <input type="hidden" name="accion" value="rechazar_solicitud">
            <input type="hidden" name="id" id="rechazo_id">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-x-circle text-danger me-2"></i>Rechazar solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Puedes indicar el motivo del rechazo para que el cliente lo sepa (opcional).</p>
                <label for="rechazo_motivo" class="form-label fw-semibold">Motivo del rechazo</label>
                <textarea name="motivo" id="rechazo_motivo" class="form-control" rows="3"
                          maxlength="500" placeholder="Ej: No tengo disponibilidad en esa fecha..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-x-lg me-1"></i>Confirmar rechazo
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.btn-abrir-rechazo').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('rechazo_id').value = this.dataset.id;
        document.getElementById('rechazo_motivo').value = '';
        new bootstrap.Modal(document.getElementById('modalRechazar')).show();
    });
});
</script>