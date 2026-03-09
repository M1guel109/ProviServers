<!-- app/views/dashboard/proveedor/solicitudes/partials/completadas.php -->
<?php
$servicios = $serviciosCompletados ?? [];

// Stats básicas
$totalCompletados = count($servicios);
$esteMes = count(array_filter($servicios, function($s){
    if (empty($s['fecha_fin']) && empty($s['updated_at'])) return false;
    $f = $s['fecha_fin'] ?? $s['updated_at'];
    return date('Y-m', strtotime($f)) === date('Y-m');
}));

$ratings = array_values(array_filter(array_map(fn($s) => $s['calificacion'] ?? null, $servicios), fn($v) => $v !== null && $v !== ''));
$promedio = !empty($ratings) ? round(array_sum($ratings) / count($ratings), 2) : null;

$montos = array_values(array_filter(array_map(fn($s) => $s['monto'] ?? $s['monto_total'] ?? null, $servicios), fn($v) => is_numeric($v)));
$ingresos = !empty($montos) ? array_sum($montos) : null;
?>

<!-- Estadísticas (igual que en nuevas.php y en_proceso.php) -->
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
            <div class="stat-label">Ingresos Totales</div>
        </div>
    </div>
</section>

<!-- Filtros (estilo oportunidades) -->
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

<!-- Listado en tarjetas -->
<section id="lista-completadas" class="grid-completadas">
    <?php if (!empty($servicios)) : ?>
        <?php foreach ($servicios as $s) : ?>

            <?php
            $clienteFoto = $s['cliente_foto'] ?? '';
            $avatar = $clienteFoto ? $clienteFoto : 'default_user.png';

            $fechaFin = $s['fecha_fin'] ?? $s['updated_at'] ?? null;
            $fechaInicio = $s['fecha_inicio'] ?? $s['fecha_solicitud'] ?? null;

            $monto = $s['monto'] ?? $s['monto_total'] ?? null;
            $calif = $s['calificacion'] ?? null;
            $comentario = $s['comentario'] ?? '';
            ?>

            <div class="tarjeta-completada">

                <!-- Header con título y estado -->
                <div class="completada-header">
                    <div class="completada-info-principal">
                        <h3 class="completada-titulo">
                            <?= htmlspecialchars($s['servicio_nombre'] ?? 'Servicio completado') ?>
                        </h3>

                        <div class="completada-meta">
                            <span class="badge-categoria">
                                <i class="bi bi-briefcase"></i>
                                <?= htmlspecialchars($s['solicitud_titulo'] ?? 'Solicitud') ?>
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

                <!-- Información del cliente (igual que en proceso) -->
                <div class="completada-cliente">
                    <img src="<?= BASE_URL . '/public/uploads/usuarios/' . $avatar ?>" 
                         alt="Cliente" 
                         class="cliente-avatar">
                    <div class="cliente-info">
                        <div class="cliente-nombre"><?= htmlspecialchars($s['cliente_nombre'] ?? 'Cliente') ?></div>
                        <div class="cliente-contacto">
                            <i class="bi bi-telephone"></i> <?= htmlspecialchars($s['cliente_telefono'] ?? 'N/A') ?>
                        </div>
                    </div>
                </div>

                <!-- Detalles del servicio (fecha inicio y monto) -->
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

                <!-- Calificación -->
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

                    <?php if (!empty($comentario)) : ?>
                        <p class="calificacion-comentario">"<?= htmlspecialchars($comentario) ?>"</p>
                    <?php endif; ?>
                </div>

                <!-- Acciones (botones) -->
                <div class="completada-acciones">
                    <button class="btn-accion btn-ver-detalles" type="button">
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