<?php
require_once BASE_PATH . '/app/helpers/session_proveedor.php';

// Datos de ejemplo para promociones
$promociones_activas = [
    [
        'id' => 1,
        'titulo' => 'Descuento del 20% en primera contratación',
        'descripcion' => 'Ofrece un 20% de descuento a nuevos clientes que contraten tu servicio por primera vez.',
        'tipo' => 'descuento',
        'valor' => 20,
        'usos' => 12,
        'limite_usos' => 50,
        'fecha_inicio' => '2025-03-01',
        'fecha_fin' => '2025-04-30',
        'estado' => 'activa',
        'servicios' => ['Plomería', 'Electricidad'],
        'imagen' => 'promo-descuento.png'
    ],
    [
        'id' => 2,
        'titulo' => 'Paquete de 3 servicios al precio de 2',
        'descripcion' => 'Cliente que contrate 3 servicios, paga solo 2. Válido para servicios de mantenimiento.',
        'tipo' => 'paquete',
        'valor' => 33,
        'usos' => 8,
        'limite_usos' => 30,
        'fecha_inicio' => '2025-03-15',
        'fecha_fin' => '2025-05-15',
        'estado' => 'activa',
        'servicios' => ['Limpieza', 'Jardinería'],
        'imagen' => 'promo-paquete.png'
    ]
];

$promociones_disponibles = [
    [
        'id' => 3,
        'titulo' => 'Primera visita gratis',
        'descripcion' => 'Ofrece una visita de diagnóstico sin costo para nuevos clientes.',
        'tipo' => 'gratis',
        'valor' => 100,
        'usos' => 0,
        'limite_usos' => 20,
        'fecha_inicio' => '2025-04-01',
        'fecha_fin' => '2025-06-30',
        'estado' => 'disponible',
        'servicios' => ['Electricidad', 'Plomería', 'Carpintería'],
        'imagen' => 'promo-gratis.png'
    ],
    [
        'id' => 4,
        'titulo' => '10% de descuento en mantenimiento anual',
        'descripcion' => 'Clientes que contraten plan de mantenimiento anual reciben 10% de descuento.',
        'tipo' => 'descuento',
        'valor' => 10,
        'usos' => 0,
        'limite_usos' => 15,
        'fecha_inicio' => '2025-04-15',
        'fecha_fin' => '2025-07-15',
        'estado' => 'disponible',
        'servicios' => ['Jardinería', 'Limpieza'],
        'imagen' => 'promo-mantenimiento.png'
    ]
];

$promociones_finalizadas = [
    [
        'id' => 5,
        'titulo' => '15% de descuento en reparaciones',
        'descripcion' => 'Descuento especial en servicios de reparación durante enero.',
        'tipo' => 'descuento',
        'valor' => 15,
        'usos' => 45,
        'limite_usos' => 50,
        'fecha_inicio' => '2025-01-01',
        'fecha_fin' => '2025-01-31',
        'estado' => 'finalizada',
        'servicios' => ['Plomería', 'Electricidad'],
        'imagen' => 'promo-reparacion.png'
    ]
];

$estadisticas = [
    'total_promociones' => 5,
    'activas' => 2,
    'usos_totales' => 65,
    'clientes_alcanzados' => 48,
    'ingresos_extra' => 1250000
];

$tipos_promocion = [
    'descuento' => ['nombre' => 'Descuento', 'icono' => 'bi-percent', 'color' => 'primary'],
    'paquete' => ['nombre' => 'Paquete', 'icono' => 'bi-box-seam', 'color' => 'success'],
    'gratis' => ['nombre' => 'Gratis', 'icono' => 'bi-gift', 'color' => 'warning']
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboard-Proveedor.css">

    <!-- CSS Específico -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/promociones.css">
</head>

<body>
    <!-- Sidebar Proveedor -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_proveedor.php'; ?>

    <main class="contenido">
        <!-- Header Proveedor -->
        <?php include_once __DIR__ . '/../../layouts/header_proveedor.php'; ?>

        <!-- TÍTULO CON BREADCRUMB -->
        <section id="titulo-principal">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Promociones</h1>
                    <p class="text-muted mb-0">
                        Crea y gestiona promociones para atraer más clientes y aumentar tus ventas.
                    </p>
                </div>
                <div class="col-md-4">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/proveedor/dashboard">Inicio</a></li>
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
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditarPromocion">
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
    <div class="modal fade" id="modalCrearPromocion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Crear nueva promoción
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="form-crear-promocion">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Título de la promoción</label>
                                <input type="text" class="form-control" placeholder="Ej: 20% de descuento en plomería" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tipo de promoción</label>
                                <select class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="descuento">Descuento (%)</option>
                                    <option value="paquete">Paquete de servicios</option>
                                    <option value="gratis">Primera visita gratis</option>
                                </select>
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Descripción</label>
                                <textarea class="form-control" rows="3" placeholder="Describe los detalles de la promoción..." required></textarea>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Valor</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" placeholder="20" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Fecha inicio</label>
                                <input type="date" class="form-control" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Fecha fin</label>
                                <input type="date" class="form-control" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Límite de usos</label>
                                <input type="number" class="form-control" placeholder="50" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Servicios aplicables</label>
                                <select class="form-select" multiple size="3">
                                    <option value="plomeria">Plomería</option>
                                    <option value="electricidad">Electricidad</option>
                                    <option value="limpieza">Limpieza</option>
                                    <option value="pintura">Pintura</option>
                                    <option value="jardineria">Jardinería</option>
                                    <option value="carpinteria">Carpintería</option>
                                </select>
                                <small class="text-muted">Selecciona múltiples (Ctrl+clic)</small>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="activarAhora">
                                    <label class="form-check-label" for="activarAhora">
                                        Activar inmediatamente después de crear
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn-guardar-promocion">
                        <i class="bi bi-save me-2"></i>Crear promoción
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL ESTADÍSTICAS DE PROMOCIONES -->
    <div class="modal fade" id="modalEstadisticas" tabindex="-1" aria-hidden="true">
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
    <div class="modal fade" id="modalEditarPromocion" tabindex="-1" aria-hidden="true">
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

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/promociones.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>
</body>

</html>