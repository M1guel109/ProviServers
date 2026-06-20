<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Servicios por Fecha - Proviservers</title>
    <style>
        body {
            font-family: "Helvetica", sans-serif;
            margin: 40px;
            padding: 0;
            font-size: 12px;
            color: #333;
        }
        .logo-container { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 150px; height: auto; }
        .header-title {
            color: #0066ff;
            text-align: center;
            font-size: 24px;
            margin-top: 20px;
            margin-bottom: 6px;
        }
        .description-paragraph {
            color: #000;
            margin-bottom: 24px;
            line-height: 1.5;
            font-size: 12px;
            text-align: center;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #0066ff;
            border-bottom: 2px solid #0066ff;
            padding-bottom: 4px;
            margin-top: 28px;
            margin-bottom: 12px;
        }
        .stats-row { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .stats-row td { width: 16.6%; padding: 5px; text-align: center; }
        .stat-card {
            background: #f2f7ff;
            border: 1px solid #c8dcff;
            border-radius: 6px;
            padding: 10px 6px;
        }
        .stat-number { font-size: 20px; font-weight: bold; color: #0066ff; display: block; }
        .stat-number.green  { color: #28a745; }
        .stat-number.teal   { color: #17a2b8; }
        .stat-number.yellow { color: #ffc107; }
        .stat-number.gray   { color: #6c757d; }
        .stat-number.red    { color: #dc3545; }
        .stat-label  { font-size: 9px; color: #555; margin-top: 4px; }
        .filtros-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 10px;
            margin-bottom: 16px;
        }
        .periodo-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .periodo-table td { padding: 4px 6px; font-size: 10px; }
        .per-name  { width: 140px; font-weight: bold; }
        .per-bar   { width: 50%; padding: 2px 4px; }
        .per-count { width: 50px; text-align: right; color: #555; }
        .per-detail { width: 80px; font-size: 9px; color: #888; }
        .table-reporte { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 10px; }
        .table-reporte th, .table-reporte td { border: 1px solid #ddd; padding: 6px 7px; text-align: left; }
        .table-reporte th { background-color: #f2f2f2; color: #333; text-transform: uppercase; font-size: 9px; }
        .table-reporte tr:nth-child(even) td { background-color: #fafafa; }
        .text-center { text-align: center; }
        .badge-finalizado   { background: #d4edda; color: #155724; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-en_proceso   { background: #d1ecf1; color: #0c5460; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-confirmado   { background: #e2e3e5; color: #383d41; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-pendiente    { background: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-cancelado    { background: #f8d7da; color: #721c24; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .footer {
            position: fixed; bottom: 0; left: 0; right: 0;
            height: 30px; line-height: 30px; text-align: center;
            font-size: 10px; color: #777; border-top: 1px solid #eee;
        }
    </style>
</head>
<body>

    <div class="logo-container">
        <img class="logo" src="<?= BASE_URL ?>/public/assets/img/logos/logo-principal.png" alt="Proviservers">
    </div>

    <h1 class="header-title">Reporte de Servicios por Fecha</h1>
    <p class="description-paragraph">
        Servicios contratados en la plataforma Proviservers.<br>
        Generado el <?= date('d/m/Y H:i') ?>.
    </p>

    <?php
        $global     = $reporte['global'];
        $porPeriodo = $reporte['porPeriodo'];
        $detalle    = $reporte['detalle'];

        $total       = (int)($global['total']       ?? 0);
        $finalizados = (int)($global['finalizados'] ?? 0);
        $enProceso   = (int)($global['en_proceso']  ?? 0);
        $pendientes  = (int)($global['pendientes']  ?? 0);
        $confirmados = (int)($global['confirmados'] ?? 0);
        $cancelados  = (int)($global['cancelados']  ?? 0);

        $agrupacionLabels = ['dia' => 'Día', 'semana' => 'Semana', 'mes' => 'Mes'];
        $agrupLabel = $agrupacionLabels[$agrupacion] ?? 'Período';

        $filtrosActivos = array_filter([
            'Desde'      => $filtros['desde']     ?? null,
            'Hasta'      => $filtros['hasta']     ?? null,
            'Estado'     => $filtros['estado']    ?? null,
            'Agrupación' => $agrupLabel,
        ]);
    ?>

    <?php if (!empty($filtrosActivos)): ?>
        <div class="filtros-box">
            <strong>Filtros aplicados:</strong>
            <?php foreach ($filtrosActivos as $label => $valor): ?>
                <strong><?= $label ?>:</strong> <?= htmlspecialchars($valor) ?> &nbsp;
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- RESUMEN GLOBAL -->
    <div class="section-title">Resumen Global</div>
    <table class="stats-row">
        <tr>
            <td><div class="stat-card"><span class="stat-number"><?= $total ?></span><span class="stat-label">Total contratos</span></div></td>
            <td><div class="stat-card"><span class="stat-number green"><?= $finalizados ?></span><span class="stat-label">Finalizados</span></div></td>
            <td><div class="stat-card"><span class="stat-number teal"><?= $enProceso ?></span><span class="stat-label">En proceso</span></div></td>
            <td><div class="stat-card"><span class="stat-number yellow"><?= $pendientes ?></span><span class="stat-label">Pendientes</span></div></td>
            <td><div class="stat-card"><span class="stat-number gray"><?= $confirmados ?></span><span class="stat-label">Confirmados</span></div></td>
            <td><div class="stat-card"><span class="stat-number red"><?= $cancelados ?></span><span class="stat-label">Cancelados</span></div></td>
        </tr>
    </table>

    <!-- POR PERÍODO -->
    <?php if (!empty($porPeriodo)): ?>
        <div class="section-title">Contratos por <?= $agrupLabel ?></div>
        <?php $maxPer = max(array_column($porPeriodo, 'total')) ?: 1; ?>
        <table class="periodo-table">
            <?php foreach ($porPeriodo as $per): ?>
                <?php $ancho = max(round(($per['total'] / $maxPer) * 100), 1); ?>
                <tr>
                    <td class="per-name"><?= htmlspecialchars($per['periodo']) ?></td>
                    <td class="per-bar">
                        <table style="width:100%; height:12px; border-collapse:collapse; font-size:1px; line-height:12px;">
                            <tr>
                                <td style="width:<?= $ancho ?>%; background-color:#0066ff; height:12px;"></td>
                                <td style="background-color:#e9ecef; height:12px;"></td>
                            </tr>
                        </table>
                    </td>
                    <td class="per-count"><?= (int)$per['total'] ?></td>
                    <td class="per-detail"><?= (int)$per['finalizados'] ?> fin. / <?= (int)$per['cancelados'] ?> cancel.</td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- DETALLE -->
    <div class="section-title">Detalle de Contratos (máx. 200)</div>
    <?php if (!empty($detalle)): ?>
        <table class="table-reporte">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Servicio</th>
                    <th>Cliente</th>
                    <th>Proveedor</th>
                    <th>Estado</th>
                    <th>F. Solicitud</th>
                    <th>F. Ejecución</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalle as $sc): ?>
                    <?php
                        $badgeClass = match($sc['estado']) {
                            'finalizado'          => 'badge-finalizado',
                            'en_proceso'          => 'badge-en_proceso',
                            'confirmado'          => 'badge-confirmado',
                            'pendiente'           => 'badge-pendiente',
                            'cancelado',
                            'cancelado_cliente',
                            'cancelado_proveedor' => 'badge-cancelado',
                            default               => '',
                        };
                        $estadoLabel = match($sc['estado']) {
                            'cancelado_cliente'   => 'Canc. cliente',
                            'cancelado_proveedor' => 'Canc. proveedor',
                            default               => ucfirst(str_replace('_', ' ', $sc['estado'])),
                        };
                    ?>
                    <tr>
                        <td class="text-center"><?= (int)$sc['contrato_id'] ?></td>
                        <td><?= htmlspecialchars($sc['servicio_nombre']) ?></td>
                        <td><?= htmlspecialchars($sc['cliente_nombre']) ?></td>
                        <td><?= htmlspecialchars($sc['proveedor_nombre']) ?></td>
                        <td class="text-center"><span class="<?= $badgeClass ?>"><?= $estadoLabel ?></span></td>
                        <td><?= $sc['fecha_solicitud'] ? date('d/m/Y', strtotime($sc['fecha_solicitud'])) : '—' ?></td>
                        <td><?= $sc['fecha_ejecucion'] ? date('d/m/Y', strtotime($sc['fecha_ejecucion'])) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color:#777;">No hay contratos con los filtros aplicados.</p>
    <?php endif; ?>

    <div class="footer">Proviservers — Reporte de Servicios por Fecha — <?= date('d/m/Y H:i') ?></div>

</body>
</html>
