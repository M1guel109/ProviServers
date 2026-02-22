<!-- app/views/dashboard/proveedor/solicitudes/partials/en_proceso.php -->
<?php
$servicios = $serviciosEnProceso ?? [];
$stats_en_proceso = count($servicios);
?>

<section id="estadisticas-proceso" class="mb-3">
    <div class="tarjeta-stat">
        <i class="bi bi-hourglass-split icono-stat"></i>
        <div class="stat-info">
            <div class="stat-numero"><?= $stats_en_proceso ?></div>
            <div class="stat-label">En Proceso</div>
        </div>
    </div>
</section>

<section id="filtros-proceso" class="mb-3">
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
    </div>
</section>

<section id="lista-procesos">
    <?php if (!empty($servicios)) : ?>
        <?php foreach ($servicios as $servicio) : ?>

            <?php
            $estadoMap = [
                'pendiente'  => ['label' => 'Pendiente',   'class' => 'media',      'progress' => 25],
                'en_proceso' => ['label' => 'En proceso',  'class' => 'alta',       'progress' => 60],
                'finalizado' => ['label' => 'Finalizado',  'class' => 'completado', 'progress' => 100],
            ];
            $estadoKey = $servicio['estado'] ?? 'pendiente';
            $estado = $estadoMap[$estadoKey] ?? $estadoMap['pendiente'];

            $clienteFoto = $servicio['cliente_foto'] ?? '';
            $avatar = $clienteFoto ? $clienteFoto : 'default_user.png';
            ?>

            <div class="tarjeta-proceso" data-contrato-id="<?= (int)($servicio['contrato_id'] ?? 0) ?>">

                <div class="proceso-header">
                    <div class="proceso-info-principal">
                        <h3 class="proceso-titulo">
                            <?= htmlspecialchars($servicio['servicio_nombre'] ?? 'Servicio') ?>
                        </h3>
                        <div class="proceso-meta">
                            <span class="badge-categoria">
                                <i class="bi bi-briefcase"></i>
                                <?= htmlspecialchars($servicio['solicitud_titulo'] ?? 'Solicitud') ?>
                            </span>
                            <span class="proceso-fecha">
                                <i class="bi bi-calendar3"></i>
                                Inicio:
                                <?= !empty($servicio['fecha_solicitud']) ? date('d M Y', strtotime($servicio['fecha_solicitud'])) : 'N/A' ?>
                            </span>
                        </div>
                    </div>

                    <div class="proceso-prioridad">
                        <span class="badge-prioridad badge-estado <?= htmlspecialchars($estado['class']) ?>">
                            <?= htmlspecialchars($estado['label']) ?>
                        </span>
                    </div>
                </div>

                <div class="proceso-cliente">
                    <img src="<?= BASE_URL . '/public/uploads/usuarios/' . $avatar ?>"
                        alt="Cliente"
                        class="cliente-avatar">
                    <div class="cliente-info">
                        <div class="cliente-nombre"><?= htmlspecialchars($servicio['cliente_nombre'] ?? 'Cliente') ?></div>
                        <div class="cliente-contacto">
                            <i class="bi bi-telephone"></i>
                            <?= htmlspecialchars($servicio['cliente_telefono'] ?? 'N/A') ?>
                        </div>
                    </div>
                </div>

                <div class="proceso-progreso">
                    <div class="progreso-header">
                        <span class="progreso-label">Estado del servicio</span>
                        <span class="progreso-porcentaje progreso-estado"><?= htmlspecialchars($estado['label']) ?></span>
                    </div>
                    <div class="barra-progreso">
                        <div class="barra-progreso-fill" style="width: <?= (int)$estado['progress'] ?>%"></div>
                    </div>
                </div>

                <div class="proceso-acciones">
                    <button type="button" class="btn-accion btn-actualizar"
                        onclick='abrirSeguimiento(
                                <?= (int)($servicio['contrato_id'] ?? 0) ?>, 
                                "<?= htmlspecialchars($servicio['servicio_nombre'] ?? $servicio['solicitud_titulo'] ?? 'Servicio en proceso', ENT_QUOTES) ?>", 
                                "<?= htmlspecialchars($estadoKey) ?>",
                                "<?= htmlspecialchars($servicio['cliente_nombre'] ?? 'Cliente', ENT_QUOTES) ?>"
                            )'>
                        <i class="bi bi-clipboard-pulse"></i> Hacer Seguimiento
                    </button>

                    <button class="btn-accion btn-contactar">
                        <i class="bi bi-chat-dots"></i> Contactar
                    </button>
                </div>

            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p class="text-muted text-center p-5">No tienes servicios en proceso actualmente.</p>
    <?php endif; ?>
</section>