<?php
// Aquí asumimos que $publicacion viene desde el controlador
// y que ya se validó que no es null.

require_once BASE_PATH . '/app/models/valoracion.php';

$titulo          = $publicacion['titulo'] ?? $publicacion['servicio_nombre'] ?? 'Servicio';
$servicioNombre  = $publicacion['servicio_nombre'] ?? '';
$servicioDesc    = $publicacion['servicio_descripcion'] ?? ($publicacion['publicacion_descripcion'] ?? '');
$servicioImg     = $publicacion['servicio_imagen'] ?? 'default_service.png';
$categoriaNombre = $publicacion['categoria_nombre'] ?? 'Sin categoría';

$precioRaw       = isset($publicacion['precio']) ? (float)$publicacion['precio'] : 0;
$descuento       = (int)($publicacion['promo_descuento'] ?? 0);
$precioFinal     = $descuento > 0 ? round($precioRaw * (1 - $descuento / 100)) : $precioRaw;
$precioFormato   = $precioRaw > 0 ? number_format($precioFinal, 0, ',', '.') : null;
$promoHasta      = $publicacion['promo_hasta'] ?? null;

$proveedorNombre    = $publicacion['proveedor_nombre'] ?? 'Proveedor';
$proveedorUbicacion = $publicacion['proveedor_ubicacion'] ?? 'Ubicación no especificada';
$proveedorFoto      = $publicacion['proveedor_foto'] ?? 'default_user.png';

$disponible = (int)($publicacion['servicio_disponible'] ?? 0) === 1;

// Reseñas del proveedor
$proveedorUsuarioId = (int)($publicacion['proveedor_usuario_id'] ?? 0);
$perfilPublico      = $perfilPublico ?? ['perfil' => [], 'politicas' => [], 'disponibilidad' => []];
$ppPerfil           = $perfilPublico['perfil']         ?? [];
$ppPoliticas        = $perfilPublico['politicas']      ?? [];
$ppDisp             = $perfilPublico['disponibilidad'] ?? [];

$resenasProveedor   = [];
$promedioCalif      = 0.0;
$totalResenas       = 0;
if ($proveedorUsuarioId > 0) {
    try {
        $resenasProveedor = (new Valoracion())->obtenerResenasPorProveedor($proveedorUsuarioId);
        $totalResenas     = count($resenasProveedor);
        if ($totalResenas > 0) {
            $promedioCalif = array_sum(array_column($resenasProveedor, 'calificacion')) / $totalResenas;
        }
    } catch (Exception $e) {
        error_log('detalle-publicacion reseñas: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Detalle del servicio</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <!-- Estilos específicos de cliente -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-cliente.css">
</head>

<body>
    <!-- SIDEBAR -->
    <?php
    $currentPage = 'explorar';
    include_once __DIR__ . '/../../layouts/sidebar-cliente.php';
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido">

        <!-- HEADER -->
        <?php include_once __DIR__ . '/../../layouts/header-cliente.php'; ?>

        <section id="detalle-servicio" class="mb-4">
            <!-- Breadcrumb + título -->
            <div id="titulo-principal" class="section-hero mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-1"><?= htmlspecialchars($titulo) ?></h1>
                        <p class="text-muted mb-0">Revisa la información del servicio y del proveedor antes de solicitarlo.</p>
                    </div>
                    <div class="col-md-4">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 justify-content-md-end">
                                <li class="breadcrumb-item">
                                    <a href="<?= BASE_URL ?>/cliente/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="<?= BASE_URL ?>/cliente/explorar-servicios">Explorar</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Detalle</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Columna izquierda: imagen + descripción -->
                <div class="col-lg-8">
                    <div class="card service-card mb-4">
                        <div class="service-image">
                            <img src="<?= BASE_URL ?>/public/uploads/servicios/<?= htmlspecialchars($servicioImg) ?>"
                                alt="Imagen del servicio"
                                style="width: 100%; height: 260px; object-fit: cover;">
                        </div>
                        <div class="card-body service-content">
                            <h5 class="card-title mb-2"><?= htmlspecialchars($servicioNombre) ?></h5>
                            <p class="card-category mb-1">
                                <strong>Categoría:</strong>
                                <?= htmlspecialchars($categoriaNombre) ?>
                            </p>

                            <?php if ($precioRaw > 0): ?>
                                <p class="mb-2">
                                    <strong>Precio desde:</strong>
                                    <?php if ($descuento > 0): ?>
                                        <span class="text-decoration-line-through text-muted small">
                                            $<?= number_format($precioRaw, 0, ',', '.') ?>
                                        </span>
                                        <span class="text-danger fw-bold ms-1">
                                            $<?= $precioFormato ?>
                                        </span>
                                        <span class="badge bg-danger ms-1">-<?= $descuento ?>%</span>
                                        <?php if ($promoHasta): ?>
                                            <small class="text-muted d-block">Promo hasta <?= date('d/m/Y', strtotime($promoHasta)) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-primary fw-bold">$<?= $precioFormato ?></span>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>

                            <hr>

                            <h6 class="mb-2">Descripción del servicio</h6>
                            <p class="card-text">
                                <?= nl2br(htmlspecialchars($servicioDesc ?: 'El proveedor aún no ha agregado una descripción detallada.')) ?>
                            </p>
                        </div>
                    </div>

                    <!-- Políticas de servicio -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-file-earmark-text me-1"></i>
                                Políticas de servicio
                            </h5>

                            <?php if (empty($ppPoliticas)): ?>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    El proveedor aún no ha configurado sus políticas de servicio.
                                </p>
                            <?php else: ?>
                                <ul class="list-unstyled mb-0" style="font-size: 0.9rem;">

                                    <?php
                                    $labelCancelacion = [
                                        'flexible' => 'Flexible',
                                        'moderada' => 'Moderada',
                                        'estricta' => 'Estricta',
                                    ];
                                    $tipoCan = $ppPoliticas['tipo_cancelacion'] ?? '';
                                    ?>
                                    <li class="mb-2">
                                        <i class="bi bi-x-circle text-danger me-1"></i>
                                        <strong>Cancelación:</strong>
                                        <?= htmlspecialchars($labelCancelacion[$tipoCan] ?? ucfirst($tipoCan)) ?>
                                        <?php if (!empty($ppPoliticas['descripcion_cancelacion'])): ?>
                                            <span class="text-muted">— <?= htmlspecialchars($ppPoliticas['descripcion_cancelacion']) ?></span>
                                        <?php endif; ?>
                                    </li>

                                    <?php if (!empty($ppPoliticas['permite_reprogramar'])): ?>
                                        <li class="mb-2">
                                            <i class="bi bi-calendar-check text-success me-1"></i>
                                            <strong>Reprogramación:</strong> Permitida
                                            <?php if (!empty($ppPoliticas['horas_min_reprogramacion'])): ?>
                                                <span class="text-muted">(mín. <?= (int)$ppPoliticas['horas_min_reprogramacion'] ?> h de anticipación)</span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php if (!empty($ppPoliticas['cobra_visita']) && !empty($ppPoliticas['valor_visita'])): ?>
                                        <li class="mb-2">
                                            <i class="bi bi-cash-coin text-warning me-1"></i>
                                            <strong>Visita:</strong> $<?= number_format((float)$ppPoliticas['valor_visita'], 0, ',', '.') ?>
                                            <span class="text-muted small">(cobro por visita de diagnóstico)</span>
                                        </li>
                                    <?php endif; ?>

                                    <?php if (!empty($ppPoliticas['ofrece_garantia'])): ?>
                                        <li class="mb-2">
                                            <i class="bi bi-shield-check text-success me-1"></i>
                                            <strong>Garantía:</strong>
                                            <?php if (!empty($ppPoliticas['dias_garantia'])): ?>
                                                <?= (int)$ppPoliticas['dias_garantia'] ?> días
                                            <?php endif; ?>
                                            <?php if (!empty($ppPoliticas['detalles_garantia'])): ?>
                                                <span class="text-muted">— <?= htmlspecialchars($ppPoliticas['detalles_garantia']) ?></span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php if (!empty($ppPoliticas['tiempo_respuesta_promedio'])): ?>
                                        <li class="mb-2">
                                            <i class="bi bi-clock me-1"></i>
                                            <strong>Tiempo de respuesta:</strong>
                                            <?= htmlspecialchars($ppPoliticas['tiempo_respuesta_promedio']) ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php if (!empty($ppPoliticas['otras_condiciones'])): ?>
                                        <li class="mb-0">
                                            <i class="bi bi-card-text me-1"></i>
                                            <strong>Otras condiciones:</strong>
                                            <span class="text-muted"><?= htmlspecialchars($ppPoliticas['otras_condiciones']) ?></span>
                                        </li>
                                    <?php endif; ?>

                                </ul>
                            <?php endif; ?>

                            <div class="alert alert-info d-flex gap-2 align-items-start mb-0 mt-3 p-2">
                                <i class="bi bi-shield-lock-fill mt-1"></i>
                                <small>Toda comunicación y contratación se realiza exclusivamente a través de ProviServers.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Reseñas del proveedor -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-1">
                                <i class="bi bi-chat-square-text me-1"></i>
                                Reseñas del proveedor
                            </h5>
                            <?php if ($totalResenas > 0): ?>
                                <p class="text-muted small mb-3">
                                    <?= number_format($promedioCalif, 1) ?> de 5 &middot; <?= $totalResenas ?> reseña<?= $totalResenas !== 1 ? 's' : '' ?>
                                </p>
                                <?php foreach ($resenasProveedor as $r): ?>
                                    <div class="border rounded p-3 mb-3">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= htmlspecialchars($r['cliente_foto'] ?: 'default_user.png') ?>"
                                                 alt="avatar" class="rounded-circle"
                                                 style="width:36px;height:36px;object-fit:cover;">
                                            <div>
                                                <div class="fw-semibold" style="font-size:.9rem;"><?= htmlspecialchars($r['cliente_nombre']) ?></div>
                                                <div style="font-size:.75rem;color:#aaa;">
                                                    <?= date('d M Y', strtotime($r['fecha'])) ?>
                                                    &middot; <?= htmlspecialchars($r['servicio_nombre']) ?>
                                                </div>
                                            </div>
                                            <div class="ms-auto" style="font-size:.85rem;">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="bi bi-star<?= $i <= (int)$r['calificacion'] ? '-fill text-warning' : ' text-secondary opacity-50' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <?php if (!empty($r['comentario'])): ?>
                                            <p class="mb-2 small text-muted fst-italic">"<?= htmlspecialchars($r['comentario']) ?>"</p>
                                        <?php endif; ?>
                                        <?php if (!empty($r['respuesta_proveedor'])): ?>
                                            <div class="p-2 rounded bg-light border-start border-3 border-primary mt-2">
                                                <small class="fw-bold text-primary d-block mb-1">
                                                    <i class="bi bi-person-check me-1"></i>Respuesta del proveedor
                                                </small>
                                                <small class="text-muted"><?= htmlspecialchars($r['respuesta_proveedor']) ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted small mt-2 mb-0">
                                    <i class="bi bi-star me-1"></i>Este proveedor aún no tiene reseñas.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: proveedor + acción -->
                <div class="col-lg-4">
                    <!-- Info proveedor -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-person-circle me-1"></i>
                                Proveedor
                            </h5>

                            <div class="d-flex align-items-center mb-3">
                                <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= htmlspecialchars($proveedorFoto) ?>"
                                    alt="Foto proveedor"
                                    style="width: 58px; height: 58px; border-radius: 50%; object-fit: cover; margin-right: 12px;">
                                <div>
                                    <p class="mb-1 fw-semibold">
                                        <?= htmlspecialchars($proveedorNombre) ?>
                                    </p>
                                    <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        <?= htmlspecialchars($proveedorUbicacion) ?>
                                    </p>
                                </div>
                            </div>

                            <?php if (!empty($ppPerfil['eslogan'])): ?>
                                <p class="fst-italic text-muted small mb-2">
                                    "<?= htmlspecialchars($ppPerfil['eslogan']) ?>"
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($ppPerfil['anios_experiencia'])): ?>
                                <p class="mb-2" style="font-size: 0.88rem;">
                                    <i class="bi bi-award me-1 text-primary"></i>
                                    <?= (int)$ppPerfil['anios_experiencia'] ?> años de experiencia
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($ppDisp['hora_inicio']) && !empty($ppDisp['hora_fin'])): ?>
                                <p class="mb-2" style="font-size: 0.88rem;">
                                    <i class="bi bi-clock me-1 text-primary"></i>
                                    <?= htmlspecialchars($ppDisp['hora_inicio']) ?> – <?= htmlspecialchars($ppDisp['hora_fin']) ?>
                                    <?php if (!empty($ppDisp['atiende_fines_semana'])): ?>
                                        <span class="badge bg-light text-dark border ms-1">Fines de semana</span>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>

                            <p class="mb-0" style="font-size: 0.9rem;">
                                <strong>Calificación:</strong>
                                <?php if ($totalResenas > 0): ?>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?= $i <= round($promedioCalif) ? '-fill text-warning' : ' text-secondary opacity-50' ?>"></i>
                                    <?php endfor; ?>
                                    <span class="ms-1 fw-semibold"><?= number_format($promedioCalif, 1) ?></span>
                                    <span class="text-muted small">(<?= $totalResenas ?> reseña<?= $totalResenas !== 1 ? 's' : '' ?>)</span>
                                <?php else: ?>
                                    <span class="text-muted small">Sin reseñas aún</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Detalles rápidos + botón de acción -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-info-circle me-1"></i>
                                Detalles del servicio
                            </h5>

                            <p class="mb-1" style="font-size: 0.9rem;">
                                <strong>Estado:</strong>
                                <?php if ($disponible): ?>
                                    <span class="badge bg-success">Proveedor disponible</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No disponible temporalmente</span>
                                <?php endif; ?>
                            </p>

                            <?php if ($precioRaw > 0): ?>
                                <p class="mb-3" style="font-size: 0.9rem;">
                                    <strong>Precio de referencia:</strong><br>
                                    <?php if ($descuento > 0): ?>
                                        <span class="text-decoration-line-through text-muted">
                                            $<?= number_format($precioRaw, 0, ',', '.') ?>
                                        </span>
                                        <span class="fs-5 fw-bold text-danger ms-2">
                                            $<?= $precioFormato ?>
                                        </span>
                                        <span class="badge bg-danger ms-1">-<?= $descuento ?>%</span>
                                    <?php else: ?>
                                        <span class="fs-5 fw-bold">$<?= $precioFormato ?></span>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>

                            <hr>

                            <p class="text-muted" style="font-size: 0.85rem;">
                                La contratación se realiza a través de Proviservers. Tus datos de contacto
                                se comparten solo cuando se genere una solicitud formal.
                            </p>

                            <!-- Botón para iniciar solicitud (flujo a implementar) -->
                            <a href="<?= BASE_URL ?>/cliente/solicitar-servicio?id=<?= (int)$publicacion['publicacion_id'] ?>"
                                class="btn btn-primary w-100 mt-2">
                                <i class="bi bi-hand-index-thumb me-1"></i>
                                Solicitar este servicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/dashboard-cliente.js"></script>
</body>

</html>
