<?php
require_once BASE_PATH . '/app/helpers/session_proveedor.php';

// Datos del plan actual del proveedor (esto vendrá de la BD)
$plan_actual = [
    'nombre' => 'Básico',
    'precio' => 0,
    'periodo' => 'mensual',
    'fecha_inicio' => '2025-01-15',
    'fecha_vencimiento' => '2025-02-15',
    'estado' => 'activo',
    'beneficios' => [
        'Perfil profesional básico',
        'Publicar hasta 3 servicios',
        'Acceso a solicitudes de clientes cercanos',
        'Calificaciones visibles',
        'Chat limitado con clientes',
        'Soporte por correo electrónico'
    ]
];

// Planes disponibles
$planes_disponibles = [
    'basico' => [
        'nombre' => 'Básico',
        'precio' => 0,
        'periodo' => 'gratuito',
        'icono' => 'bi-gem',
        'color' => 'secondary',
        'destacado' => false,
        'beneficios' => [
            'Perfil profesional básico',
            'Publicar hasta 3 servicios',
            'Acceso a solicitudes de clientes cercanos',
            'Calificaciones visibles',
            'Chat limitado con clientes',
            'Soporte por correo electrónico'
        ]
    ],
    'crecimiento' => [
        'nombre' => 'Crecimiento',
        'precio' => 25000,
        'periodo' => 'mes',
        'icono' => 'bi-rocket',
        'color' => 'primary',
        'destacado' => true,
        'beneficios' => [
            'Todo lo del Paquete Básico',
            'Publicar hasta 10 servicios',
            'Acceso a clientes sin límite de zona',
            'Chat directo y sin restricciones',
            'Perfil profesional optimizado',
            'Verificación y métricas básicas'
        ]
    ],
    'premium' => [
        'nombre' => 'Premium',
        'precio' => 49000,
        'periodo' => 'mes',
        'icono' => 'bi-stars',
        'color' => 'warning',
        'destacado' => false,
        'beneficios' => [
            'Todo lo del Paquete Crecimiento',
            'Publicaciones ilimitadas',
            'Prioridad en resultados de búsqueda',
            'Sello de proveedor destacado',
            'Exposición preferencial en campañas',
            'Soporte prioritario'
        ]
    ]
];

// Historial de pagos (simulado)
$historial_pagos = [
    ['fecha' => '2025-01-15', 'plan' => 'Básico', 'monto' => 0, 'estado' => 'activo'],
    ['fecha' => '2024-12-15', 'plan' => 'Básico', 'monto' => 0, 'estado' => 'completado'],
    ['fecha' => '2024-11-15', 'plan' => 'Básico', 'monto' => 0, 'estado' => 'completado'],
];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Mi Membresía</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos Globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboard-Proveedor.css">

    <!-- CSS Específico -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/membresia.css">
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
                    <h1>Mi Membresía</h1>
                    <p class="text-muted mb-0">
                        Gestiona tu plan, beneficios y mejora tu experiencia en la plataforma.
                    </p>
                </div>
                <div class="col-md-4">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/proveedor/dashboard">Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Mi Membresía</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <!-- PLAN ACTUAL -->
        <section class="row mb-4">
            <div class="col-12">
                <div class="card-plan-actual">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-3">
                                <div class="plan-icono <?= $plan_actual['nombre'] === 'Básico' ? 'bg-secondary' : 'bg-primary' ?>">
                                    <i class="bi bi-gem fs-1 text-white"></i>
                                </div>
                                <div>
                                    <span class="badge bg-success mb-2">Plan activo</span>
                                    <h2 class="fw-bold mb-1">Plan <?= $plan_actual['nombre'] ?></h2>
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        Vigencia: <?= date('d/m/Y', strtotime($plan_actual['fecha_inicio'])) ?> - <?= date('d/m/Y', strtotime($plan_actual['fecha_vencimiento'])) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="plan-precio">
                                <?php if ($plan_actual['precio'] == 0): ?>
                                    <h3 class="fw-bold text-success">Gratuito</h3>
                                <?php else: ?>
                                    <h3 class="fw-bold text-primary">$<?= number_format($plan_actual['precio'], 0, ',', '.') ?>/mes</h3>
                                <?php endif; ?>
                                <button class="btn btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalCambiarPlan">
                                    <i class="bi bi-arrow-up-circle me-2"></i>Cambiar plan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- BENEFICIOS DEL PLAN ACTUAL -->
        <section class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-check-circle me-2 text-primary"></i>Beneficios incluidos en tu plan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php foreach ($plan_actual['beneficios'] as $beneficio): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <span><?= $beneficio ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PLANES DISPONIBLES -->
        <section class="row mb-4">
            <div class="col-12">
                <h5 class="fw-bold mb-3">Planes disponibles para mejorar</h5>
            </div>

            <?php foreach ($planes_disponibles as $key => $plan): ?>
                <?php if ($plan['nombre'] !== $plan_actual['nombre']): ?>
                    <div class="col-md-6">
                        <div class="card-plan <?= $plan['destacado'] ? 'destacado' : '' ?>">
                            <?php if ($plan['destacado']): ?>
                                <div class="plan-badge">MÁS ELEGIDO</div>
                            <?php endif; ?>

                            <div class="plan-header">
                                <i class="bi <?= $plan['icono'] ?> plan-icono"></i>
                                <h3>Plan <?= $plan['nombre'] ?></h3>
                                <div class="plan-precio-card">
                                    <span class="precio">$<?= number_format($plan['precio'], 0, ',', '.') ?></span>
                                    <span class="periodo">/mes</span>
                                </div>
                            </div>

                            <div class="plan-body">
                                <ul class="plan-beneficios">
                                    <?php foreach ($plan['beneficios'] as $beneficio): ?>
                                        <li>
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                            <?= $beneficio ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>

                                <button class="btn btn-<?= $plan['color'] ?> w-100 btn-cambiar-plan"
                                    data-plan="<?= $plan['nombre'] ?>"
                                    data-precio="<?= $plan['precio'] ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalConfirmarPago">
                                    <i class="bi bi-arrow-up-circle me-2"></i>Cambiar a este plan
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </section>

        <!-- HISTORIAL DE PAGOS -->
        <section class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-clock-history me-2 text-primary"></i>Historial de pagos
                        </h6>
                        <a href="#" class="btn btn-link btn-sm text-primary">Ver todos</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Plan</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Comprobante</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial_pagos as $pago): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($pago['fecha'])) ?></td>
                                            <td>Plan <?= $pago['plan'] ?></td>
                                            <td>
                                                <?php if ($pago['monto'] == 0): ?>
                                                    <span class="text-success">Gratuito</span>
                                                <?php else: ?>
                                                    $<?= number_format($pago['monto'], 0, ',', '.') ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= $pago['estado'] === 'activo' ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= ucfirst($pago['estado']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($pago['monto'] > 0): ?>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-file-pdf"></i>
                                                    </button>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- MODAL CAMBIAR PLAN (informativo) -->
    <div class="modal fade" id="modalCambiarPlan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-up-circle me-2"></i>Cambiar de plan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted mb-3">Selecciona uno de los planes disponibles para ver sus beneficios y proceder al pago.</p>

                    <div class="list-group">
                        <?php foreach ($planes_disponibles as $key => $plan): ?>
                            <?php if ($plan['nombre'] !== $plan_actual['nombre']): ?>
                                <button type="button"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center btn-seleccionar-plan"
                                    data-plan="<?= $plan['nombre'] ?>"
                                    data-precio="<?= $plan['precio'] ?>"
                                    data-bs-dismiss="modal"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalConfirmarPago">
                                    <div>
                                        <i class="bi <?= $plan['icono'] ?> me-2 text-<?= $plan['color'] ?>"></i>
                                        <strong>Plan <?= $plan['nombre'] ?></strong>
                                        <small class="text-muted d-block"><?= $plan['beneficios'][0] ?></small>
                                    </div>
                                    <span class="fw-bold text-primary">$<?= number_format($plan['precio'], 0, ',', '.') ?>/mes</span>
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CONFIRMAR PAGO (PASARELA) -->
    <div class="modal fade" id="modalConfirmarPago" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-credit-card me-2"></i>Confirmar pago
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-circle d-inline-flex p-3 mb-3">
                            <i class="bi bi-arrow-up-circle fs-1 text-primary"></i>
                        </div>
                        <h4 id="modal-plan-seleccionado">Plan Crecimiento</h4>
                        <p class="text-muted">Estás a punto de mejorar tu membresía</p>
                    </div>

                    <div class="bg-light p-3 rounded-3 mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Precio del plan:</span>
                            <strong id="modal-precio-plan">$25,000/mes</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>IVA (19%):</span>
                            <strong id="modal-iva">$4,750</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total a pagar:</span>
                            <strong class="fs-5 text-primary" id="modal-total">$29,750</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Método de pago</label>
                        <select class="form-select" id="metodo-pago">
                            <option value="nequi">Nequi</option>
                            <option value="daviplata">DaviPlata</option>
                            <option value="bancolombia">Bancolombia</option>
                            <option value="tarjeta">Tarjeta de crédito/débito</option>
                            <option value="pse">PSE</option>
                        </select>
                    </div>

                    <div id="nequi-info" class="pago-info bg-light p-3 rounded-3">
                        <p class="mb-1"><i class="bi bi-phone me-2"></i>Número Nequi: <strong>300 123 4567</strong></p>
                        <p class="mb-0 small text-muted">Realiza el pago y confirma la transacción</p>
                    </div>

                    <div id="daviplata-info" class="pago-info bg-light p-3 rounded-3 d-none">
                        <p class="mb-1"><i class="bi bi-phone me-2"></i>Número DaviPlata: <strong>300 123 4567</strong></p>
                        <p class="mb-0 small text-muted">Realiza el pago y confirma la transacción</p>
                    </div>

                    <div id="bancolombia-info" class="pago-info bg-light p-3 rounded-3 d-none">
                        <p class="mb-1"><i class="bi bi-bank me-2"></i>Cuenta de ahorros: <strong>123-456789-01</strong></p>
                        <p class="mb-0 small text-muted">Banco: Bancolombia - Titular: Proviservers SAS</p>
                    </div>

                    <div id="tarjeta-info" class="pago-info bg-light p-3 rounded-3 d-none">
                        <form id="form-tarjeta">
                            <div class="mb-2">
                                <input type="text" class="form-control form-control-sm" placeholder="Número de tarjeta">
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-sm" placeholder="MM/AA">
                                </div>
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-sm" placeholder="CVV">
                                </div>
                            </div>
                        </form>
                    </div>

                    <div id="pse-info" class="pago-info bg-light p-3 rounded-3 d-none">
                        <select class="form-select form-select-sm">
                            <option value="">Selecciona tu banco</option>
                            <option value="bancolombia">Bancolombia</option>
                            <option value="davivienda">Davivienda</option>
                            <option value="bbva">BBVA</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btn-confirmar-pago">
                        <i class="bi bi-check-circle me-2"></i>Confirmar pago
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Agregar en el <head> o antes del script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/membresia.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>
</body>

</html>