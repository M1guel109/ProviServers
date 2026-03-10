<?php
$servicios = $serviciosCompletados ?? [];

// Stats básicas
$totalCompletados = count($servicios);

$esteMes = count(array_filter($servicios, function ($s) {
    $f = $s['fecha_ejecucion']
        ?? $s['necesidad_fecha_preferida']
        ?? $s['solicitud_fecha_preferida']
        ?? $s['fecha_solicitud']
        ?? null;

    if (!$f) return false;

    return date('Y-m', strtotime($f)) === date('Y-m');
}));

$ratings = array_values(array_filter(
    array_map(fn($s) => $s['mi_calificacion'] ?? null, $servicios),
    fn($v) => $v !== null && $v !== ''
));

$promedio = !empty($ratings)
    ? round(array_sum($ratings) / count($ratings), 2)
    : null;

$montos = array_values(array_filter(array_map(function ($s) {
    return $s['cotizacion_precio']
        ?? $s['solicitud_presupuesto_estimado']
        ?? null;
}, $servicios), fn($v) => is_numeric($v)));

$ingresos = !empty($montos) ? array_sum($montos) : null;
?>

<section id="estadisticas-completadas">
    <div class="tarjeta-estadistica">
        <i class="bi bi-check-circle icono-estadistica"></i>
        <div class="stat-info">
            <div class="stat-numero"><?= $totalCompletados ?></div>
            <div class="stat-label">Total Completados</div>
        </div>
    </div>

    <div class="tarjeta-estadistica">
        <i class="bi bi-calendar-month icono-estadistica"></i>
        <div class="stat-info">
            <div class="stat-numero"><?= $esteMes ?></div>
            <div class="stat-label">Este Mes</div>
        </div>
    </div>

    <div class="tarjeta-estadistica">
        <i class="bi bi-star-fill icono-estadistica"></i>
        <div class="stat-info">
            <div class="stat-numero"><?= $promedio !== null ? $promedio : 'N/A' ?></div>
            <div class="stat-label">Calificación Promedio</div>
        </div>
    </div>

    <div class="tarjeta-estadistica">
        <i class="bi bi-cash-coin icono-estadistica"></i>
        <div class="stat-info">
            <div class="stat-numero">
                <?= $ingresos !== null ? ('$' . number_format($ingresos, 0, ',', '.')) : 'N/A' ?>
            </div>
            <div class="stat-label">Ingresos Referenciales</div>
        </div>
    </div>
</section>

<section id="filtros-completadas">
    <div class="contenedor-filtros">
        <div class="grupo-filtro">
            <label for="filtro-categoria">Categoría</label>
            <select id="filtro-categoria">
                <option value="">Todas</option>
                <option value="plomeria">Plomería</option>
                <option value="electricidad">Electricidad</option>
                <option value="limpieza">Limpieza</option>
                <option value="pintura">Pintura</option>
                <option value="jardineria">Jardinería</option>
            </select>
        </div>

        <div class="grupo-filtro">
            <label for="filtro-periodo">Período</label>
            <select id="filtro-periodo">
                <option value="">Todos</option>
                <option value="semana">Esta semana</option>
                <option value="mes">Este mes</option>
                <option value="trimestre">Últimos 3 meses</option>
                <option value="anio">Este año</option>
            </select>
        </div>

        <div class="grupo-filtro busqueda-filtro">
            <label for="buscar-completadas">Buscar</label>
            <input type="text" id="buscar-completadas" placeholder="Buscar por cliente o servicio...">
        </div>
    </div>
</section>

<section id="lista-completadas" class="grid-completadas">
    <?php if (!empty($servicios)) : ?>
        <?php foreach ($servicios as $s) : ?>

            <?php
            $clienteFoto = trim((string)($s['cliente_foto'] ?? ''));
            $avatar = $clienteFoto !== '' ? $clienteFoto : 'default_user.png';

            $tituloServicio =
                $s['servicio_nombre']
                ?? $s['publicacion_titulo_cotizacion']
                ?? $s['publicacion_titulo_solicitud']
                ?? $s['cotizacion_titulo']
                ?? $s['solicitud_titulo']
                ?? $s['necesidad_titulo']
                ?? 'Servicio completado';

            $subtitulo =
                $s['publicacion_titulo_cotizacion']
                ?? $s['publicacion_titulo_solicitud']
                ?? $s['cotizacion_titulo']
                ?? $s['solicitud_titulo']
                ?? $s['necesidad_titulo']
                ?? 'Solicitud';

            $fechaFin =
                $s['fecha_ejecucion']
                ?? $s['necesidad_fecha_preferida']
                ?? $s['solicitud_fecha_preferida']
                ?? $s['fecha_solicitud']
                ?? null;

            $fechaInicio =
                $s['fecha_solicitud']
                ?? $s['necesidad_fecha_preferida']
                ?? $s['solicitud_fecha_preferida']
                ?? null;

            $monto =
                $s['cotizacion_precio']
                ?? $s['solicitud_presupuesto_estimado']
                ?? null;

            $calif = $s['mi_calificacion'] ?? null;
            $comentario = trim((string)($s['mi_comentario'] ?? ''));

            $contratoId = (int)($s['contrato_id'] ?? 0);
            ?>

            <div class="tarjeta-completada">

                <div class="completada-header">
                    <div class="completada-info-principal">
                        <h3 class="completada-titulo">
                            <?= htmlspecialchars($tituloServicio) ?>
                        </h3>

                        <div class="completada-meta">
                            <span class="badge-categoria">
                                <i class="bi bi-briefcase"></i>
                                <?= htmlspecialchars($subtitulo) ?>
                            </span>

                            <span class="completada-fecha">
                                <i class="bi bi-calendar-check"></i>
                                Completado: <?= $fechaFin ? date('d M Y', strtotime($fechaFin)) : 'N/A' ?>
                            </span>
                        </div>
                    </div>

                    <div class="completada-estado">
                        <span class="badge-completado bg-success">
                            <i class="bi bi-check-circle-fill"></i> Completado
                        </span>
                    </div>
                </div>

                <div class="completada-cliente">
                    <img src="<?= BASE_URL . '/public/uploads/usuarios/' . htmlspecialchars($avatar) ?>"
                        alt="Cliente"
                        class="cliente-avatar"
                        onerror="this.onerror=null; this.src='<?= BASE_URL ?>/public/uploads/usuarios/default_user.png';">
                    <div class="cliente-info">
                        <div class="cliente-nombre"><?= htmlspecialchars($s['cliente_nombre'] ?? 'Cliente') ?></div>
                        <div class="cliente-contacto">
                            <i class="bi bi-telephone"></i> <?= htmlspecialchars($s['cliente_telefono'] ?? 'N/A') ?>
                        </div>
                    </div>
                </div>

                <div class="completada-detalles">
                    <div class="detalle-item">
                        <i class="bi bi-calendar3"></i>
                        <div class="detalle-info">
                            <span class="detalle-label">Fecha inicio</span>
                            <span class="detalle-valor"><?= $fechaInicio ? date('d M Y', strtotime($fechaInicio)) : 'N/A' ?></span>
                        </div>
                    </div>

                    <div class="detalle-item">
                        <i class="bi bi-currency-dollar"></i>
                        <div class="detalle-info">
                            <span class="detalle-label">Monto</span>
                            <span class="detalle-valor">
                                <?= is_numeric($monto) ? ('$' . number_format((float)$monto, 0, ',', '.')) : 'N/A' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="completada-calificacion">
                    <div class="calificacion-header">
                        <span class="calificacion-titulo">Calificación del cliente</span>
                        <div class="estrellas">
                            <?php
                            $n = is_numeric($calif) ? (int)round((float)$calif) : 0;
                            for ($i = 0; $i < 5; $i++) {
                                echo $i < $n ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                            }
                            ?>
                            <span class="calificacion-numero"><?= is_numeric($calif) ? number_format((float)$calif, 1) : 'N/A' ?></span>
                        </div>
                    </div>

                    <?php if ($comentario !== '') : ?>
                        <p class="calificacion-comentario">"<?= htmlspecialchars($comentario) ?>"</p>
                    <?php endif; ?>
                </div>

                <div class="completada-acciones">
                    <button class="btn-accion btn-ver-detalles"
                        type="button"
                        onclick='verDetalle(<?= json_encode($s, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)'>
                        <i class="bi bi-eye"></i> Ver Detalles
                    </button>

                    <button class="btn-accion btn-descargar" type="button">
                        <i class="bi bi-download"></i> Factura
                    </button>

                    <button class="btn-accion btn-contactar" type="button">
                        <i class="bi bi-chat-dots"></i> Contactar
                    </button>
                </div>

            </div>

        <?php endforeach; ?>
    <?php else : ?>
        <div class="empty-state">
            <p class="text-muted">No tienes servicios completados aún.</p>
        </div>
    <?php endif; ?>
</section>