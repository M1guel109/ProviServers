<?php
require_once BASE_PATH . '/app/helpers/session-proveedor.php';
require_once BASE_PATH . '/config/database.php';

$uid = (int)($_SESSION['user']['id'] ?? 0);

$facturacion  = ['saldo_actual' => 0, 'facturas_pendientes' => 0, 'facturas_pagadas' => 0, 'proximo_pago' => null, 'monto_proximo' => 0];
$facturas     = [];
$metodos_pago = [];
$resumen_anual = ['total_facturado' => 0, 'total_comisiones' => 0, 'total_neto' => 0, 'promedio_mensual' => 0];
$escrow = ['retenido' => 0.0, 'liberado' => 0.0, 'pagos' => []];

try {
    $db  = new Conexion();
    $pdo = $db->getConexion();

    $stProv = $pdo->prepare("SELECT id FROM proveedores WHERE usuario_id = :uid LIMIT 1");
    $stProv->execute([':uid' => $uid]);
    $proveedorId = (int)($stProv->fetchColumn() ?: 0);

    if ($proveedorId > 0) {
        // Saldo: ingresos de servicios finalizados este año
        $stSaldo = $pdo->prepare("
            SELECT COALESCE(SUM(COALESCE(c.precio, pub_sol.precio, 0)), 0)
            FROM servicios_contratados sc
            LEFT JOIN cotizaciones c        ON sc.cotizacion_id    = c.id
            LEFT JOIN solicitudes sol       ON sc.solicitud_id     = sol.id
            LEFT JOIN publicaciones pub_sol ON sol.publicacion_id  = pub_sol.id
            WHERE sc.proveedor_id = :pid AND sc.estado = 'finalizado'
              AND YEAR(sc.created_at) = YEAR(CURDATE())
        ");
        $stSaldo->execute([':pid' => $proveedorId]);
        $facturacion['saldo_actual'] = (float)($stSaldo->fetchColumn() ?: 0);

        // Pagos de membresía
        $stPagos = $pdo->prepare("
            SELECT p.id, p.monto, p.estado_pago, p.metodo_pago, p.fecha_pago, p.created_at,
                   m.tipo AS concepto
            FROM pagos p
            LEFT JOIN proveedor_membresia pm ON p.proveedor_membresia_id = pm.id
            LEFT JOIN membresias m ON pm.membresia_id = m.id
            WHERE p.proveedor_id = :pid
            ORDER BY p.created_at DESC
            LIMIT 20
        ");
        $stPagos->execute([':pid' => $proveedorId]);
        $pagosRaw = $stPagos->fetchAll(PDO::FETCH_ASSOC);

        $pagados  = 0;
        $pendientes = 0;
        foreach ($pagosRaw as $i => $p) {
            $esPagado = in_array($p['estado_pago'] ?? '', ['aprobado', 'approved', 'pagado', 'completado']);
            if ($esPagado) $pagados++;
            else $pendientes++;
            $facturas[] = [
                'id'               => 'MEM-' . str_pad($p['id'], 4, '0', STR_PAD_LEFT),
                'fecha_emision'    => $p['created_at']  ?? date('Y-m-d'),
                'fecha_vencimiento'=> $p['fecha_pago']  ?? date('Y-m-d', strtotime('+15 days')),
                'concepto'         => 'Membresía ' . ($p['concepto'] ?? 'Plan'),
                'monto'            => (float)($p['monto'] ?? 0),
                'estado'           => $esPagado ? 'pagada' : 'pendiente',
                'metodo_pago'      => $p['metodo_pago'] ?? null,
                'pdf_url'          => '#',
            ];
        }

        // Completar con servicios finalizados recientes si no hay pagos
        if (empty($facturas)) {
            $stSrv = $pdo->prepare("
                SELECT sc.id, sc.created_at,
                       COALESCE(c.titulo, sol.titulo, 'Servicio') AS concepto,
                       COALESCE(c.precio, pub_sol.precio, 0) AS monto
                FROM servicios_contratados sc
                LEFT JOIN cotizaciones c        ON sc.cotizacion_id    = c.id
                LEFT JOIN solicitudes sol       ON sc.solicitud_id     = sol.id
                LEFT JOIN publicaciones pub_sol ON sol.publicacion_id  = pub_sol.id
                WHERE sc.proveedor_id = :pid AND sc.estado = 'finalizado'
                ORDER BY sc.created_at DESC LIMIT 10
            ");
            $stSrv->execute([':pid' => $proveedorId]);
            foreach ($stSrv->fetchAll(PDO::FETCH_ASSOC) as $srv) {
                $pagados++;
                $facturas[] = [
                    'id'               => 'CTR-' . str_pad($srv['id'], 4, '0', STR_PAD_LEFT),
                    'fecha_emision'    => $srv['created_at'],
                    'fecha_vencimiento'=> $srv['created_at'],
                    'concepto'         => htmlspecialchars($srv['concepto']),
                    'monto'            => (float)$srv['monto'],
                    'estado'           => 'pagada',
                    'metodo_pago'      => null,
                    'pdf_url'          => BASE_URL . '/proveedor/contrato-pdf?id=' . $srv['id'],
                ];
            }
        }

        $facturacion['facturas_pagadas']   = $pagados;
        $facturacion['facturas_pendientes'] = $pendientes;

        // Próximo pago de membresía
        $stProx = $pdo->prepare("
            SELECT pm.fecha_fin, m.costo
            FROM proveedor_membresia pm
            JOIN membresias m ON pm.membresia_id = m.id
            WHERE pm.proveedor_id = :pid AND pm.estado = 'activa' AND m.costo > 0
            ORDER BY pm.fecha_fin ASC LIMIT 1
        ");
        $stProx->execute([':pid' => $proveedorId]);
        $proximo = $stProx->fetch(PDO::FETCH_ASSOC);
        if ($proximo) {
            $facturacion['proximo_pago']  = $proximo['fecha_fin'];
            $facturacion['monto_proximo'] = (float)$proximo['costo'];
        }

        // Datos de facturación guardados
        $stFact = $pdo->prepare("SELECT * FROM proveedores_pagos_facturacion WHERE proveedor_id = :pid LIMIT 1");
        $stFact->execute([':pid' => $proveedorId]);
        $factRow = $stFact->fetch(PDO::FETCH_ASSOC);
        if ($factRow) {
            $metodoIcono = match(strtolower($factRow['metodo_pago_preferido'] ?? '')) {
                'nequi', 'daviplata' => 'bi-phone',
                'pse', 'bancolombia' => 'bi-bank',
                default => 'bi-credit-card',
            };
            $metodos_pago[] = [
                'tipo'           => $factRow['metodo_pago_preferido'] ?? 'Sin método',
                'icono'          => $metodoIcono,
                'numero'         => $factRow['numero_cuenta'] ?? '—',
                'predeterminado' => true,
            ];
        }

        // Resumen anual
        $stAnual = $pdo->prepare("
            SELECT
                COALESCE(SUM(COALESCE(c.precio, pub_sol.precio, 0)), 0) AS total
            FROM servicios_contratados sc
            LEFT JOIN cotizaciones c        ON sc.cotizacion_id    = c.id
            LEFT JOIN solicitudes sol       ON sc.solicitud_id     = sol.id
            LEFT JOIN publicaciones pub_sol ON sol.publicacion_id  = pub_sol.id
            WHERE sc.proveedor_id = :pid AND sc.estado = 'finalizado'
              AND YEAR(sc.created_at) = YEAR(CURDATE())
        ");
        $stAnual->execute([':pid' => $proveedorId]);
        $totalAnual = (float)($stAnual->fetchColumn() ?: 0);
        $resumen_anual = [
            'total_facturado'  => $totalAnual,
            'total_comisiones' => 0,
            'total_neto'       => $totalAnual,
            'promedio_mensual' => round($totalAnual / 12),
        ];
    }
} catch (PDOException $e) {
    error_log('facturacion.php: ' . $e->getMessage());
}

// Datos de escrow desde pagos_servicios
try {
    $db2 = new Conexion();
    $pdo2 = $db2->getConexion();
    $stE = $pdo2->prepare("
        SELECT ps.id, ps.monto, ps.liberado, ps.fecha_liberacion, ps.created_at,
               COALESCE(sv.nombre, sol.titulo, cot.titulo, 'Servicio') AS servicio,
               TRIM(CONCAT(u.nombre, ' ', COALESCE(u.apellido,''))) AS cliente
        FROM pagos_servicios ps
        JOIN servicios_contratados sc ON ps.servicio_contratado_id = sc.id
        JOIN servicios sv             ON sc.servicio_id = sv.id
        JOIN clientes cl              ON ps.cliente_id  = cl.id
        JOIN usuarios u               ON cl.usuario_id  = u.id
        LEFT JOIN solicitudes sol     ON sc.solicitud_id = sol.id
        LEFT JOIN cotizaciones cot    ON sc.cotizacion_id = cot.id
        WHERE ps.proveedor_id = :pid
        ORDER BY ps.created_at DESC
    ");
    $stE->execute([':pid' => $proveedorId ?? 0]);
    $escrow['pagos'] = $stE->fetchAll(PDO::FETCH_ASSOC);
    foreach ($escrow['pagos'] as $ep) {
        if ($ep['liberado']) $escrow['liberado'] += (float)$ep['monto'];
        else                 $escrow['retenido'] += (float)$ep['monto'];
    }
} catch (PDOException $e) { /* tabla puede no existir aún */ }
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-proveedor.css">

    <!-- CSS Específico -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/facturacion.css">
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
                    <h1 class="mb-1">Facturación</h1>
                    <p class="text-muted mb-0">Gestiona tus facturas, métodos de pago y visualiza el resumen de tus transacciones.</p>
                </div>
                <div class="col-md-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/proveedor/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                            </li>
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

        <!-- PANEL ESCROW -->
        <section class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-15"
                              style="width:56px;height:56px;flex-shrink:0;">
                            <i class="bi bi-hourglass-split text-warning fs-4"></i>
                        </span>
                        <div>
                            <div class="text-muted small mb-1">Dinero retenido (por liberar)</div>
                            <div class="fw-bold fs-4 text-warning">
                                $<?= number_format($escrow['retenido'], 0, ',', '.') ?>
                            </div>
                            <small class="text-muted">Se libera al finalizar cada servicio</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-15"
                              style="width:56px;height:56px;flex-shrink:0;">
                            <i class="bi bi-check-circle text-success fs-4"></i>
                        </span>
                        <div>
                            <div class="text-muted small mb-1">Dinero liberado (disponible)</div>
                            <div class="fw-bold fs-4 text-success">
                                $<?= number_format($escrow['liberado'], 0, ',', '.') ?>
                            </div>
                            <small class="text-muted">Listo para transferencia a tu cuenta</small>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($escrow['pagos'])): ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-safe2 me-2 text-primary"></i>Pagos de servicios recibidos
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Servicio</th>
                                    <th>Cliente</th>
                                    <th>Fecha cobro</th>
                                    <th class="text-end">Monto</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Liberado</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($escrow['pagos'] as $ep): ?>
                            <tr>
                                <td class="fw-semibold small"><?= htmlspecialchars($ep['servicio']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($ep['cliente']) ?></td>
                                <td class="text-muted small"><?= date('d M Y', strtotime($ep['created_at'])) ?></td>
                                <td class="text-end fw-bold text-success">
                                    $<?= number_format((float)$ep['monto'], 0, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">Pagado</span>
                                </td>
                                <td class="text-center">
                                    <?php if ($ep['liberado']): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-lg me-1"></i>Liberado
                                        </span>
                                        <div class="text-muted" style="font-size:.7rem;">
                                            <?= date('d/m/Y', strtotime($ep['fecha_liberacion'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-hourglass me-1"></i>Retenido
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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
    <div class="modal fade modal-cliente" id="modalPagarFactura" tabindex="-1" aria-hidden="true">
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
    <div class="modal fade modal-cliente" id="modalAgregarMetodo" tabindex="-1" aria-hidden="true">
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
    <div class="modal fade modal-cliente" id="modalExportar" tabindex="-1" aria-hidden="true">
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