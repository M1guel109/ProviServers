<?php
require_once BASE_PATH . '/app/helpers/session-proveedor.php';
require_once BASE_PATH . '/config/database.php';

$uid = (int)($_SESSION['user']['id'] ?? 0);
$promociones_activas    = [];
$promociones_disponibles = [];
$promociones_finalizadas = [];
$estadisticas = ['total_promociones' => 0, 'activas' => 0, 'usos_totales' => 0, 'clientes_alcanzados' => 0, 'ingresos_extra' => 0];

try {
    $db  = new Conexion();
    $pdo = $db->getConexion();

    $stProv = $pdo->prepare("SELECT id FROM proveedores WHERE usuario_id = :uid LIMIT 1");
    $stProv->execute([':uid' => $uid]);
    $proveedorId = (int)($stProv->fetchColumn() ?: 0);

    if ($proveedorId > 0) {
        $stPromos = $pdo->prepare("
            SELECT pr.id, pr.porcentaje_descuento, pr.fecha_inicio, pr.fecha_fin, pr.created_at,
                   pub.titulo AS servicio_titulo
            FROM promociones pr
            LEFT JOIN publicaciones pub ON pr.publicacion_id = pub.id
            WHERE pr.proveedor_id = :pid
            ORDER BY pr.fecha_fin DESC
        ");
        $stPromos->execute([':pid' => $proveedorId]);
        $promosRaw = $stPromos->fetchAll(PDO::FETCH_ASSOC);

        $hoy = date('Y-m-d');
        foreach ($promosRaw as $p) {
            $inicio = $p['fecha_inicio'] ?? $hoy;
            $fin    = $p['fecha_fin']    ?? $hoy;
            $promo  = [
                'id'          => $p['id'],
                'titulo'      => $p['servicio_titulo'] ? htmlspecialchars($p['servicio_titulo']) . ' — ' . $p['porcentaje_descuento'] . '% desc.' : 'Descuento ' . $p['porcentaje_descuento'] . '%',
                'descripcion' => 'Descuento del ' . $p['porcentaje_descuento'] . '% en este servicio.',
                'tipo'        => 'descuento',
                'valor'       => (int)$p['porcentaje_descuento'],
                'usos'        => 0,
                'limite_usos' => 0,
                'fecha_inicio'=> $inicio,
                'fecha_fin'   => $fin,
                'estado'      => $fin < $hoy ? 'finalizada' : ($inicio > $hoy ? 'disponible' : 'activa'),
                'servicios'   => [$p['servicio_titulo'] ?? 'Servicio'],
            ];
            if ($promo['estado'] === 'activa')      $promociones_activas[]    = $promo;
            elseif ($promo['estado'] === 'disponible') $promociones_disponibles[] = $promo;
            else                                    $promociones_finalizadas[] = $promo;
        }

        $estadisticas['total_promociones']  = count($promosRaw);
        $estadisticas['activas']            = count($promociones_activas);

        // Publicaciones activas del proveedor para el selector del modal
        $stPubs = $pdo->prepare("
            SELECT pub.id, pub.titulo, pub.precio
            FROM publicaciones pub
            WHERE pub.proveedor_id = :pid AND pub.estado = 'aprobado'
            ORDER BY pub.titulo ASC
        ");
        $stPubs->execute([':pid' => $proveedorId]);
        $misPublicaciones = $stPubs->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log('promociones.php: ' . $e->getMessage());
}
$misPublicaciones = $misPublicaciones ?? [];

$tipos_promocion = [
    'descuento' => ['nombre' => 'Descuento', 'icono' => 'bi-percent', 'color' => 'primary'],
    'paquete'   => ['nombre' => 'Paquete',   'icono' => 'bi-box-seam','color' => 'success'],
    'gratis'    => ['nombre' => 'Gratis',    'icono' => 'bi-gift',    'color' => 'warning'],
];

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Promociones</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos Globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-Proveedor.css">

    <!-- CSS Específico -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/promociones.css">
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
                    <h1 class="mb-1">Promociones</h1>
                    <p class="text-muted mb-0">Crea y gestiona promociones para atraer más clientes y aumentar tus ventas.</p>
                </div>
                <div class="col-md-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/proveedor/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Promociones</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <!-- ACCIONES RÁPIDAS -->
        <section class="filtros-container mb-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="mb-0 fw-bold">Gestiona tus campañas promocionales</h6>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrearPromocion">
                            <i class="bi bi-plus-circle"></i> Nueva promoción
                        </button>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalEstadisticas">
                            <i class="bi bi-graph-up"></i> Estadísticas
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- TARJETAS DE ESTADÍSTICAS RÁPIDAS -->
        <section class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="tarjeta-promocion">
                    <div class="icono-wrapper bg-primary-light">
                        <i class="bi bi-megaphone icono-promocion text-primary"></i>
                    </div>
                    <div class="promocion-contenido">
                        <span class="promocion-valor"><?= $estadisticas['total_promociones'] ?></span>
                        <span class="promocion-etiqueta">Total promociones</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="tarjeta-promocion">
                    <div class="icono-wrapper bg-success-light">
                        <i class="bi bi-play-circle icono-promocion text-success"></i>
                    </div>
                    <div class="promocion-contenido">
                        <span class="promocion-valor"><?= $estadisticas['activas'] ?></span>
                        <span class="promocion-etiqueta">Activas</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="tarjeta-promocion">
                    <div class="icono-wrapper bg-warning-light">
                        <i class="bi bi-people icono-promocion text-warning"></i>
                    </div>
                    <div class="promocion-contenido">
                        <span class="promocion-valor"><?= $estadisticas['clientes_alcanzados'] ?></span>
                        <span class="promocion-etiqueta">Clientes alcanzados</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="tarjeta-promocion">
                    <div class="icono-wrapper bg-info-light">
                        <i class="bi bi-cash-coin icono-promocion text-info"></i>
                    </div>
                    <div class="promocion-contenido">
                        <span class="promocion-valor">$<?= number_format($estadisticas['ingresos_extra'], 0, ',', '.') ?></span>
                        <span class="promocion-etiqueta">Ingresos extra</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- FILTROS DE BÚSQUEDA -->
        <section class="filtros-container mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 bg-light" placeholder="Buscar promoción...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select">
                        <option value="">Todas las categorías</option>
                        <option value="plomeria">Plomería</option>
                        <option value="electricidad">Electricidad</option>
                        <option value="limpieza">Limpieza</option>
                        <option value="pintura">Pintura</option>
                        <option value="jardineria">Jardinería</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select">
                        <option value="">Todas las promociones</option>
                        <option value="activas">Activas</option>
                        <option value="disponibles">Disponibles</option>
                        <option value="finalizadas">Finalizadas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                </div>
            </div>
        </section>

        <!-- SECCIÓN DE PROMOCIONES ACTIVAS -->
        <section class="mb-5">
            <h5 class="fw-bold mb-3">
                <i class="bi bi-play-circle-fill text-success me-2"></i>Promociones activas
            </h5>
            
            <div class="row g-4">
                <?php foreach ($promociones_activas as $promo): ?>
                    <div class="col-md-6">
                        <div class="card-promocion activa">
                            <div class="promocion-badge activa">ACTIVA</div>
                            
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <div class="promocion-imagen">
                                        <i class="bi bi-megaphone"></i>
                                    </div>
                                </div>
                                
                                <div class="col-md-8">
                                    <div class="promocion-detalle">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="fw-bold"><?= $promo['titulo'] ?></h5>
                                            <span class="badge <?= $tipos_promocion[$promo['tipo']]['color'] ?>">
                                                <i class="bi <?= $tipos_promocion[$promo['tipo']]['icono'] ?> me-1"></i>
                                                <?= $tipos_promocion[$promo['tipo']]['nombre'] ?>
                                            </span>
                                        </div>
                                        
                                        <p class="text-muted small mb-3"><?= $promo['descripcion'] ?></p>
                                        
                                        <div class="promocion-meta mb-3">
                                            <div class="d-flex align-items-center gap-3 small">
                                                <span><i class="bi bi-calendar3 me-1 text-primary"></i> 
                                                    <?= date('d/m/Y', strtotime($promo['fecha_inicio'])) ?> - <?= date('d/m/Y', strtotime($promo['fecha_fin'])) ?>
                                                </span>
                                                <span><i class="bi bi-tag me-1 text-success"></i> 
                                                    <?= $promo['valor'] ?>% 
                                                </span>
                                            </div>
                                            <div class="mt-2">
                                                <span class="badge bg-light text-dark me-1"><?= implode('</span><span class="badge bg-light text-dark me-1">', $promo['servicios']) ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="progress mb-2" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: <?= ($promo['usos'] / $promo['limite_usos']) * 100 ?>%"></div>
                                        </div>
                                        <div class="d-flex justify-content-between small text-muted mb-3">
                                            <span><?= $promo['usos'] ?> usos</span>
                                            <span>Límite: <?= $promo['limite_usos'] ?></span>
                                        </div>
                                        
                                        <div class="d-flex gap-2">
                                            <form method="POST" action="<?= BASE_URL ?>/proveedor/promociones/eliminar"
                                                  onsubmit="return confirm('¿Eliminar esta promoción?')">
                                                <input type="hidden" name="promo_id" value="<?= $promo['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i> Eliminar
                                                </button>
                                            </form>
                                            <button class="btn btn-sm btn-outline-primary d-none" data-bs-toggle="modal" data-bs-target="#modalEditarPromocion">
                                                <i class="bi bi-pencil"></i> Editar
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-stop-circle"></i> Pausar
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-bar-chart"></i> Ver detalles
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- SECCIÓN DE PROMOCIONES DISPONIBLES -->
        <section class="mb-5">
            <h5 class="fw-bold mb-3">
                <i class="bi bi-plus-circle-fill text-primary me-2"></i>Promociones disponibles para activar
            </h5>
            
            <div class="row g-4">
                <?php foreach ($promociones_disponibles as $promo): ?>
                    <div class="col-md-4">
                        <div class="card-promocion disponible">
                            <div class="promocion-badge disponible">DISPONIBLE</div>
                            
                            <div class="promocion-header text-center">
                                <i class="bi bi-gift promocion-icono-grande"></i>
                                <h6 class="fw-bold mt-3"><?= $promo['titulo'] ?></h6>
                            </div>
                            
                            <div class="promocion-body">
                                <p class="small text-muted mb-3"><?= $promo['descripcion'] ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge <?= $tipos_promocion[$promo['tipo']]['color'] ?>">
                                        <i class="bi <?= $tipos_promocion[$promo['tipo']]['icono'] ?> me-1"></i>
                                        <?= $tipos_promocion[$promo['tipo']]['nombre'] ?> <?= $promo['valor'] ?>%
                                    </span>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3"></i> <?= date('d/m', strtotime($promo['fecha_inicio'])) ?>
                                    </small>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-primary w-100">Activar promoción</button>
                                    <button class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- SECCIÓN DE PROMOCIONES FINALIZADAS -->
        <section class="mb-4">
            <h5 class="fw-bold mb-3">
                <i class="bi bi-clock-history text-secondary me-2"></i>Promociones finalizadas
            </h5>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Promoción</th>
                            <th>Tipo</th>
                            <th>Período</th>
                            <th>Usos</th>
                            <th>Resultado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($promociones_finalizadas as $promo): ?>
                        <tr>
                            <td>
                                <span class="fw-semibold"><?= $promo['titulo'] ?></span>
                                <br>
                                <small class="text-muted"><?= $promo['descripcion'] ?></small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <i class="bi <?= $tipos_promocion[$promo['tipo']]['icono'] ?> me-1"></i>
                                    <?= $tipos_promocion[$promo['tipo']]['nombre'] ?>
                                </span>
                            </td>
                            <td>
                                <small><?= date('d/m/Y', strtotime($promo['fecha_inicio'])) ?><br>
                                <?= date('d/m/Y', strtotime($promo['fecha_fin'])) ?></small>
                            </td>
                            <td><?= $promo['usos'] ?>/<?= $promo['limite_usos'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: 90%"></div>
                                    </div>
                                    <small>90%</small>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-arrow-repeat"></i> Reactivar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <!-- MODAL CREAR PROMOCIÓN -->
    <div class="modal fade modal-cliente" id="modalCrearPromocion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Nueva promoción
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?= BASE_URL ?>/proveedor/promociones/crear">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Publicación <span class="text-danger">*</span></label>
                            <select name="publicacion_id" class="form-select" required>
                                <option value="">Selecciona un servicio publicado...</option>
                                <?php foreach ($misPublicaciones as $pub): ?>
                                    <option value="<?= $pub['id'] ?>">
                                        <?= htmlspecialchars($pub['titulo']) ?>
                                        <?= $pub['precio'] > 0 ? ' — $'.number_format((float)$pub['precio'],0,',','.') : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($misPublicaciones)): ?>
                                <small class="text-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    No tienes publicaciones aprobadas. Debes publicar un servicio primero.
                                </small>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Descuento (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="porcentaje_descuento" class="form-control"
                                    min="1" max="100" placeholder="Ej: 20" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold">Fecha inicio <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_inicio" class="form-control"
                                min="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-6">
                            <label class="form-label fw-semibold">Fecha fin <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_fin" class="form-control"
                                min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" <?= empty($misPublicaciones) ? 'disabled' : '' ?>>
                        <i class="bi bi-save me-2"></i>Crear promoción
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL ESTADÍSTICAS DE PROMOCIONES -->
    <div class="modal fade modal-cliente" id="modalEstadisticas" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-graph-up me-2"></i>Estadísticas de promociones
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <span class="text-muted small d-block">Tasa de conversión</span>
                                <span class="fw-bold fs-2 text-primary">68%</span>
                                <span class="text-success d-block small"><i class="bi bi-arrow-up"></i> +12% vs mes anterior</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <span class="text-muted small d-block">Retorno de inversión</span>
                                <span class="fw-bold fs-2 text-success">320%</span>
                                <span class="text-success d-block small">Por cada $1 invertido</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h6 class="fw-bold mb-3">Rendimiento por tipo de promoción</h6>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Descuentos</span>
                                <span class="fw-bold">45%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" style="width: 45%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Paquetes</span>
                                <span class="fw-bold">30%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: 30%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Primera visita gratis</span>
                                <span class="fw-bold">25%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: 25%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h6 class="fw-bold mb-3">Ingresos generados por promociones</h6>
                        <canvas id="chartPromociones" style="width:100%; max-height:200px;"></canvas>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR PROMOCIÓN -->
    <div class="modal fade modal-cliente" id="modalEditarPromocion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>Editar promoción
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="form-editar-promocion">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Título de la promoción</label>
                                <input type="text" class="form-control" value="Descuento del 20% en primera contratación" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tipo de promoción</label>
                                <select class="form-select" required>
                                    <option value="descuento" selected>Descuento (%)</option>
                                    <option value="paquete">Paquete de servicios</option>
                                    <option value="gratis">Primera visita gratis</option>
                                </select>
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Descripción</label>
                                <textarea class="form-control" rows="3">Ofrece un 20% de descuento a nuevos clientes que contraten tu servicio por primera vez.</textarea>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Valor</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" value="20" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Fecha inicio</label>
                                <input type="date" class="form-control" value="2025-03-01" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Fecha fin</label>
                                <input type="date" class="form-control" value="2025-04-30" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Límite de usos</label>
                                <input type="number" class="form-control" value="50" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Estado</label>
                                <select class="form-select">
                                    <option value="activa" selected>Activa</option>
                                    <option value="pausada">Pausada</option>
                                    <option value="finalizada">Finalizada</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning">
                        <i class="bi bi-save me-2"></i>Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/promociones.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>
</body>

</html>