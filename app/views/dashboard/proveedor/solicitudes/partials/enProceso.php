<?php
$servicios = $serviciosEnProceso ?? [];
$stats_en_proceso = count($servicios);
?>

<section id="estadisticas-proceso">
    <div class="tarjeta-estadistica">
        <i class="bi bi-hourglass-split icono-estadistica"></i>
        <div class="stat-info">
            <div class="stat-numero"><?= $stats_en_proceso ?></div>
            <div class="stat-label">En Proceso</div>
        </div>
    </div>
</section>

<section id="filtros-proceso">
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
                'pendiente'   => ['label' => 'Pendiente',   'class' => 'media',      'progress' => 25],
                'confirmado'  => ['label' => 'Confirmado',  'class' => 'media',      'progress' => 40],
                'en_proceso'  => ['label' => 'En proceso',  'class' => 'alta',       'progress' => 60],
                'finalizado'  => ['label' => 'Finalizado',  'class' => 'completado', 'progress' => 100],
            ];

            $estadoKey = $servicio['estado'] ?? 'pendiente';
            $estado = $estadoMap[$estadoKey] ?? $estadoMap['pendiente'];

            $clienteFoto = trim((string)($servicio['cliente_foto'] ?? ''));
            $avatar = $clienteFoto !== '' ? $clienteFoto : 'default_user.png';

            $tituloServicio =
                $servicio['servicio_nombre']
                ?? $servicio['publicacion_titulo_cotizacion']
                ?? $servicio['publicacion_titulo_solicitud']
                ?? $servicio['cotizacion_titulo']
                ?? $servicio['solicitud_titulo']
                ?? $servicio['necesidad_titulo']
                ?? 'Servicio';

            $subtitulo =
                $servicio['publicacion_titulo_cotizacion']
                ?? $servicio['publicacion_titulo_solicitud']
                ?? $servicio['cotizacion_titulo']
                ?? $servicio['solicitud_titulo']
                ?? $servicio['necesidad_titulo']
                ?? 'Solicitud';

            $fechaInicio =
                $servicio['fecha_solicitud']
                ?? $servicio['necesidad_fecha_preferida']
                ?? $servicio['solicitud_fecha_preferida']
                ?? null;

            $contratoId = (int)($servicio['contrato_id'] ?? 0);
            $clienteNombre = $servicio['cliente_nombre'] ?? 'Cliente';
            ?>

            <div class="tarjeta-proceso" data-contrato-id="<?= $contratoId ?>">

                <div class="proceso-header">
                    <div class="proceso-info-principal">
                        <h3 class="proceso-titulo">
                            <?= htmlspecialchars($tituloServicio) ?>
                        </h3>

                        <div class="proceso-meta">
                            <span class="badge-categoria">
                                <i class="bi bi-briefcase"></i>
                                <?= htmlspecialchars($subtitulo) ?>
                            </span>

                            <span class="proceso-fecha">
                                <i class="bi bi-calendar3"></i>
                                Inicio:
                                <?= $fechaInicio ? date('d M Y', strtotime($fechaInicio)) : 'N/A' ?>
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
                    <img src="<?= BASE_URL . '/public/uploads/usuarios/' . htmlspecialchars($avatar) ?>"
                        alt="Cliente"
                        class="cliente-avatar"
                        onerror="this.onerror=null; this.src='<?= BASE_URL ?>/public/uploads/usuarios/default_user.png';">

                    <div class="cliente-info">
                        <div class="cliente-nombre"><?= htmlspecialchars($clienteNombre) ?></div>
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
                    <button type="button"
                        class="btn-accion btn-actualizar"
                        onclick='abrirSeguimiento(
                            <?= $contratoId ?>,
                            "<?= htmlspecialchars($tituloServicio, ENT_QUOTES) ?>",
                            "<?= htmlspecialchars($estadoKey, ENT_QUOTES) ?>",
                            "<?= htmlspecialchars($clienteNombre, ENT_QUOTES) ?>"
                        )'>
                        <i class="bi bi-clipboard-pulse"></i> Hacer Seguimiento
                    </button>

                    <button type="button"
                        class="btn-accion btn-contactar">
                        <i class="bi bi-chat-dots"></i> Contactar
                    </button>
                </div>

            </div>

        <?php endforeach; ?>
    <?php else : ?>
        <div class="empty-state">
            No tienes servicios en proceso actualmente.
        </div>
    <?php endif; ?>
</section>