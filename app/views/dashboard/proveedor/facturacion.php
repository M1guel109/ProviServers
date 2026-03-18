<?php
require_once BASE_PATH . '/app/helpers/session_proveedor.php';

// Datos de ejemplo (luego vendrán del controlador)
$facturacion = [
    'saldo_actual' => 8450000,
    'facturas_pendientes' => 3,
    'facturas_pagadas' => 15,
    'proximo_pago' => '2025-04-15',
    'monto_proximo' => 1250000,
    'metodo_pago_predeterminado' => 'Nequi',
    'numero_cuenta' => '300 123 4567'
];

$facturas = [
    [
        'id' => 'FAC-2025-001',
        'fecha_emision' => '2025-03-01',
        'fecha_vencimiento' => '2025-03-15',
        'concepto' => 'Membresía Premium - Marzo',
        'monto' => 49000,
        'estado' => 'pagada',
        'metodo_pago' => 'Nequi',
        'pdf_url' => '#'
    ],
    [
        'id' => 'FAC-2025-002',
        'fecha_emision' => '2025-03-05',
        'fecha_vencimiento' => '2025-03-20',
        'concepto' => 'Comisión por servicios',
        'monto' => 324000,
        'estado' => 'pagada',
        'metodo_pago' => 'Tarjeta crédito',
        'pdf_url' => '#'
    ],
    [
        'id' => 'FAC-2025-003',
        'fecha_emision' => '2025-03-10',
        'fecha_vencimiento' => '2025-03-25',
        'concepto' => 'Membresía Premium - Abril',
        'monto' => 49000,
        'estado' => 'pendiente',
        'metodo_pago' => null,
        'pdf_url' => '#'
    ],
    [
        'id' => 'FAC-2025-004',
        'fecha_emision' => '2025-03-12',
        'fecha_vencimiento' => '2025-03-27',
        'concepto' => 'Servicio destacado',
        'monto' => 25000,
        'estado' => 'pendiente',
        'metodo_pago' => null,
        'pdf_url' => '#'
    ],
    [
        'id' => 'FAC-2025-005',
        'fecha_emision' => '2025-03-15',
        'fecha_vencimiento' => '2025-03-30',
        'concepto' => 'Publicación destacada',
        'monto' => 15000,
        'estado' => 'pendiente',
        'metodo_pago' => null,
        'pdf_url' => '#'
    ]
];

$metodos_pago = [
    ['tipo' => 'Nequi', 'icono' => 'bi-phone', 'numero' => '300 123 4567', 'predeterminado' => true],
    ['tipo' => 'DaviPlata', 'icono' => 'bi-phone', 'numero' => '300 765 4321', 'predeterminado' => false],
    ['tipo' => 'Bancolombia', 'icono' => 'bi-bank', 'numero' => '123-456789-01', 'predeterminado' => false],
    ['tipo' => 'Tarjeta crédito', 'icono' => 'bi-credit-card', 'numero' => '**** **** **** 1234', 'predeterminado' => false]
];

$resumen_anual = [
    'total_facturado' => 12500000,
    'total_comisiones' => 1250000,
    'total_neto' => 11250000,
    'promedio_mensual' => 1041667
];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Facturación</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos Globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-Proveedor.css">

    <!-- CSS Específico -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/facturacion.css">
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
                    <h1>Facturación</h1>
                    <p class="text-muted mb-0">
                        Gestiona tus facturas, métodos de pago y visualiza el resumen de tus transacciones.
                    </p>
                </div>
                <div class="col-md-4">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/proveedor/dashboard">Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Facturación</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <!-- FILTROS Y ACCIONES -->
        <section class="filtros-container mb-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="mb-0 fw-bold">Historial de facturación</h6>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2 justify-content-md-end">
                        <select class="form-select form-select-sm w-auto" id="periodo-facturas">
                            <option value="mes">Este mes</option>
                            <option value="trimestre">Último trimestre</option>
                            <option value="año" selected>Último año</option>
                            <option value="todo">Todo el historial</option>
                        </select>
                        <button class="btn btn-primary btn-sm" id="aplicar-filtro">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalExportar">
                            <i class="bi bi-download"></i> Exportar
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- TARJETAS DE RESUMEN -->
        <section class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="tarjeta-facturacion">
                    <div class="icono-wrapper bg-primary-light">
                        <i class="bi bi-wallet2 icono-facturacion text-primary"></i>
                    </div>
                    <div class="facturacion-contenido">
                        <span class="facturacion-etiqueta">Saldo actual</span>
                        <span class="facturacion-valor">$<?= number_format($facturacion['saldo_actual'], 0, ',', '.') ?></span>
                        <span class="facturacion-tendencia positiva">
                            <i class="bi bi-arrow-up"></i> +12% vs mes anterior
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="tarjeta-facturacion">
                    <div class="icono-wrapper bg-warning-light">
                        <i class="bi bi-clock-history icono-facturacion text-warning"></i>
                    </div>
                    <div class="facturacion-contenido">
                        <span class="facturacion-etiqueta">Facturas pendientes</span>
                        <span class="facturacion-valor"><?= $facturacion['facturas_pendientes'] ?></span>
                        <span class="facturacion-tendencia negativa">
                            <i class="bi bi-exclamation-triangle"></i> Requieren pago
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="tarjeta-facturacion">
                    <div class="icono-wrapper bg-success-light">
                        <i class="bi bi-check-circle icono-facturacion text-success"></i>
                    </div>
                    <div class="facturacion-contenido">
                        <span class="facturacion-etiqueta">Facturas pagadas</span>
                        <span class="facturacion-valor"><?= $facturacion['facturas_pagadas'] ?></span>
                        <span class="facturacion-tendencia positiva">
                            <i class="bi bi-check-circle"></i> Historial completo
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="tarjeta-facturacion">
                    <div class="icono-wrapper bg-info-light">
                        <i class="bi bi-calendar-event icono-facturacion text-info"></i>
                    </div>
                    <div class="facturacion-contenido">
                        <span class="facturacion-etiqueta">Próximo pago</span>
                        <span class="facturacion-valor">$<?= number_format($facturacion['monto_proximo'], 0, ',', '.') ?></span>
                        <span class="facturacion-tendencia neutral">
                            <i class="bi bi-calendar3"></i> <?= date('d/m/Y', strtotime($facturacion['proximo_pago'])) ?>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- TABLA DE FACTURAS -->
        <section class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-receipt me-2 text-primary"></i>Facturas recientes
                        </h6>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary active">Todas</button>
                            <button class="btn btn-outline-secondary">Pendientes</button>
                            <button class="btn btn-outline-secondary">Pagadas</button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="tabla-facturas">
                                <thead class="bg-light">
                                    <tr>
                                        <th>N° Factura</th>
                                        <th>Fecha emisión</th>
                                        <th>Fecha vencimiento</th>
                                        <th>Concepto</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Método pago</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($facturas as $factura): ?>
                                    <tr class="<?= $factura['estado'] === 'pendiente' ? 'factura-pendiente' : '' ?>">
                                        <td><span class="fw-semibold"><?= $factura['id'] ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($factura['fecha_emision'])) ?></td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($factura['fecha_vencimiento'])) ?>
                                            <?php if ($factura['estado'] === 'pendiente' && strtotime($factura['fecha_vencimiento']) < time()): ?>
                                                <span class="badge bg-danger ms-1">Vencida</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($factura['concepto']) ?></td>
                                        <td class="fw-bold">$<?= number_format($factura['monto'], 0, ',', '.') ?></td>
                                        <td>
                                            <span class="badge <?= $factura['estado'] === 'pagada' ? 'bg-success' : 'bg-warning' ?>">
                                                <?= ucfirst($factura['estado']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($factura['metodo_pago']): ?>
                                                <span class="text-muted small"><?= $factura['metodo_pago'] ?></span>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="<?= $factura['pdf_url'] ?>" class="btn btn-sm btn-outline-primary" title="Ver PDF">
                                                    <i class="bi bi-file-pdf"></i>
                                                </a>
                                                <?php if ($factura['estado'] === 'pendiente'): ?>
                                                    <button class="btn btn-sm btn-success btn-pagar-factura" 
                                                            data-factura-id="<?= $factura['id'] ?>"
                                                            data-monto="<?= $factura['monto'] ?>"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalPagarFactura">
                                                        <i class="bi bi-credit-card"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 text-end">
                        <a href="#" class="btn btn-link btn-sm text-primary">Ver todas las facturas <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </section>

        <!-- MÉTODOS DE PAGO Y RESUMEN ANUAL -->
        <section class="row g-4">
            <!-- Métodos de pago guardados -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-credit-card me-2 text-primary"></i>Métodos de pago
                        </h6>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAgregarMetodo">
                            <i class="bi bi-plus-circle"></i> Agregar
                        </button>
                    </div>
                    <div class="card-body">
                        <?php foreach ($metodos_pago as $metodo): ?>
                        <div class="metodo-pago-item d-flex align-items-center justify-content-between p-3 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-light rounded-circle p-2">
                                    <i class="bi <?= $metodo['icono'] ?> fs-5 text-primary"></i>
                                </div>
                                <div>
                                    <span class="fw-semibold d-block"><?= $metodo['tipo'] ?></span>
                                    <small class="text-muted"><?= $metodo['numero'] ?></small>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <?php if ($metodo['predeterminado']): ?>
                                    <span class="badge bg-success">Predeterminado</span>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-outline-danger" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Resumen anual -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-pie-chart me-2 text-primary"></i>Resumen anual
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="bg-light p-3 rounded-3 text-center">
                                    <span class="text-muted small d-block">Total facturado</span>
                                    <span class="fw-bold fs-5">$<?= number_format($resumen_anual['total_facturado'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light p-3 rounded-3 text-center">
                                    <span class="text-muted small d-block">Comisiones</span>
                                    <span class="fw-bold fs-5">$<?= number_format($resumen_anual['total_comisiones'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light p-3 rounded-3 text-center">
                                    <span class="text-muted small d-block">Total neto</span>
                                    <span class="fw-bold fs-5">$<?= number_format($resumen_anual['total_neto'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light p-3 rounded-3 text-center">
                                    <span class="text-muted small d-block">Promedio mensual</span>
                                    <span class="fw-bold fs-5">$<?= number_format($resumen_anual['promedio_mensual'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h6 class="fw-bold mb-3">Próximos vencimientos</h6>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: 60%"></div>
                                <div class="progress-bar bg-warning" style="width: 25%"></div>
                                <div class="progress-bar bg-danger" style="width: 15%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2 small text-muted">
                                <span><i class="bi bi-circle-fill text-success me-1"></i> Pagadas (60%)</span>
                                <span><i class="bi bi-circle-fill text-warning me-1"></i> Pendientes (25%)</span>
                                <span><i class="bi bi-circle-fill text-danger me-1"></i> Vencidas (15%)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- MODAL PAGAR FACTURA -->
    <div class="modal fade" id="modalPagarFactura" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-credit-card me-2"></i>Pagar factura
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-circle d-inline-flex p-3 mb-3">
                            <i class="bi bi-receipt fs-1 text-success"></i>
                        </div>
                        <h5 id="modal-factura-id">FAC-2025-003</h5>
                        <p class="text-muted">Estás a punto de pagar esta factura</p>
                    </div>

                    <div class="bg-light p-3 rounded-3 mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Monto a pagar:</span>
                            <strong id="modal-factura-monto" class="text-success fs-5">$49,000</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Método de pago</label>
                        <select class="form-select" id="metodo-pago-factura">
                            <option value="nequi">Nequi (predeterminado)</option>
                            <option value="daviplata">DaviPlata</option>
                            <option value="bancolombia">Bancolombia</option>
                            <option value="tarjeta">Tarjeta de crédito/débito</option>
                        </select>
                    </div>

                    <div class="pago-info bg-light p-3 rounded-3">
                        <p class="mb-1"><i class="bi bi-phone me-2"></i>Número Nequi: <strong>300 123 4567</strong></p>
                        <p class="mb-0 small text-muted">Realiza el pago y confirma la transacción</p>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btn-confirmar-pago-factura">
                        <i class="bi bi-check-circle me-2"></i>Confirmar pago
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL AGREGAR MÉTODO DE PAGO -->
    <div class="modal fade" id="modalAgregarMetodo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Agregar método de pago
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="form-agregar-metodo">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo de método</label>
                            <select class="form-select" id="tipo-metodo" required>
                                <option value="">Seleccionar...</option>
                                <option value="nequi">Nequi</option>
                                <option value="daviplata">DaviPlata</option>
                                <option value="bancolombia">Bancolombia</option>
                                <option value="tarjeta">Tarjeta de crédito/débito</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Número/Identificador</label>
                            <input type="text" class="form-control" placeholder="Ej: 300 123 4567" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Titular</label>
                            <input type="text" class="form-control" placeholder="Nombre del titular" required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="metodo-predeterminado">
                            <label class="form-check-label" for="metodo-predeterminado">Establecer como método predeterminado</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn-guardar-metodo">
                        <i class="bi bi-save me-2"></i>Guardar método
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EXPORTAR REPORTES -->
    <div class="modal fade" id="modalExportar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-download me-2"></i>Exportar reporte de facturación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted mb-3">Selecciona el formato y período para exportar:</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Formato</label>
                        <select class="form-select">
                            <option value="pdf">PDF - Documento</option>
                            <option value="excel">Excel - Hoja de cálculo</option>
                            <option value="csv">CSV - Datos simples</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Período</label>
                        <select class="form-select">
                            <option value="mes">Este mes</option>
                            <option value="trimestre">Último trimestre</option>
                            <option value="año">Último año</option>
                            <option value="todo">Todo el historial</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success">
                        <i class="bi bi-download me-2"></i>Exportar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/facturacion.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>
</body>

</html>