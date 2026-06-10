<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ingresos por Servicios - Proviservers</title>
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
            font-size: 22px;
            margin-top: 20px;
            margin-bottom: 6px;
        }
        .description-paragraph {
            color: #555;
            margin-bottom: 24px;
            font-size: 11px;
            text-align: center;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #0066ff;
            border-bottom: 2px solid #0066ff;
            padding-bottom: 4px;
            margin-top: 28px;
            margin-bottom: 12px;
        }
        .kpi-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .kpi-table td { width: 25%; padding: 6px; text-align: center; }
        .kpi-card {
            background: #f2f7ff;
            border: 1px solid #c8dcff;
            border-radius: 6px;
            padding: 12px 8px;
        }
        .kpi-num   { font-size: 18px; font-weight: bold; color: #0066ff; display: block; }
        .kpi-label { font-size: 9px; color: #666; margin-top: 4px; display: block; }
        .kpi-card.verde  { background: #f0fff4; border-color: #b2dfdb; }
        .kpi-card.verde .kpi-num { color: #2e7d32; }
        .kpi-card.naranja { background: #fff8e1; border-color: #ffe082; }
        .kpi-card.naranja .kpi-num { color: #e65100; }
        .table-r { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 10px; }
        .table-r th, .table-r td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        .table-r th { background: #f2f2f2; color: #333; text-transform: uppercase; font-size: 9px; }
        .table-r tr:nth-child(even) td { background: #fafafa; }
        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .text-green  { color: #2e7d32; font-weight: bold; }
        .text-muted  { color: #999; font-style: italic; }
        .badge-ok  { background: #d4edda; color: #155724; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-pend { background: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .footer {
            position: fixed; bottom: 0; left: 0; right: 0;
            height: 28px; line-height: 28px; text-align: center;
            font-size: 9px; color: #aaa; border-top: 1px solid #eee;
        }
    </style>
</head>
<body>

    <div class="logo-container">
        <img class="logo" src="<?= BASE_URL ?>/public/assets/img/logos/logo-principal.png" alt="Proviservers">
    </div>

    <h1 class="header-title">Reporte de Ingresos por Servicios</h1>
    <p class="description-paragraph">
        Ingresos generados por pagos de servicios en la plataforma Proviservers.<br>
        Comisión plataforma: <?= $reporte['tasa_comision'] ?>% &nbsp;|&nbsp; Generado: <?= date('d/m/Y H:i') ?>
    </p>

    <?php
        $g = $reporte['global'];
        $fmt = fn($n) => '$' . number_format((float)$n, 0, ',', '.');
    ?>

    <!-- KPIs GLOBALES -->
    <div class="section-title">Resumen Global</div>
    <table class="kpi-table">
        <tr>
            <td><div class="kpi-card">
                <span class="kpi-num"><?= (int)$g['total_transacciones'] ?></span>
                <span class="kpi-label">Transacciones aprobadas</span>
            </div></td>
            <td><div class="kpi-card">
                <span class="kpi-num"><?= $fmt($g['bruto']) ?></span>
                <span class="kpi-label">Ingreso bruto total</span>
            </div></td>
            <td><div class="kpi-card naranja">
                <span class="kpi-num"><?= $fmt($g['comision']) ?></span>
                <span class="kpi-label">Comisión plataforma (<?= $reporte['tasa_comision'] ?>%)</span>
            </div></td>
            <td><div class="kpi-card verde">
                <span class="kpi-num"><?= $fmt($g['neto']) ?></span>
                <span class="kpi-label">Ingreso neto plataforma</span>
            </div></td>
        </tr>
    </table>
    <table class="kpi-table">
        <tr>
            <td><div class="kpi-card">
                <span class="kpi-num"><?= (int)$g['liberados'] ?></span>
                <span class="kpi-label">Pagos liberados a proveedores</span>
            </div></td>
            <td><div class="kpi-card">
                <span class="kpi-num"><?= $fmt($g['bruto_liberado']) ?></span>
                <span class="kpi-label">Monto liberado (bruto)</span>
            </div></td>
            <td><div class="kpi-card naranja">
                <span class="kpi-num"><?= $fmt($g['comision_liberado']) ?></span>
                <span class="kpi-label">Comisión sobre liberados</span>
            </div></td>
            <td><div class="kpi-card verde">
                <span class="kpi-num"><?= $fmt($g['neto_liberado']) ?></span>
                <span class="kpi-label">Neto liberado</span>
            </div></td>
        </tr>
    </table>

    <!-- INGRESOS POR MES -->
    <div class="section-title">Ingresos por Mes (últimos 12 meses)</div>
    <?php if (!empty($reporte['porMes'])): ?>
        <table class="table-r">
            <thead>
                <tr>
                    <th>Período</th>
                    <th class="text-center">Transacciones</th>
                    <th class="text-right">Bruto</th>
                    <th class="text-right">Comisión (<?= $reporte['tasa_comision'] ?>%)</th>
                    <th class="text-right">Neto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reporte['porMes'] as $mes): ?>
                    <tr>
                        <td><?= htmlspecialchars($mes['periodo']) ?></td>
                        <td class="text-center"><?= (int)$mes['transacciones'] ?></td>
                        <td class="text-right"><?= $fmt($mes['bruto']) ?></td>
                        <td class="text-right"><?= $fmt($mes['comision']) ?></td>
                        <td class="text-right text-green"><?= $fmt($mes['neto']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted">Sin transacciones registradas aún.</p>
    <?php endif; ?>

    <!-- TOP PROVEEDORES -->
    <div class="section-title">Top 10 Proveedores por Ingresos</div>
    <?php if (!empty($reporte['porProveedor'])): ?>
        <table class="table-r">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Proveedor</th>
                    <th class="text-center">Transacciones</th>
                    <th class="text-right">Bruto</th>
                    <th class="text-right">Comisión</th>
                    <th class="text-right">Neto proveedor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reporte['porProveedor'] as $i => $prov): ?>
                    <tr>
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($prov['proveedor']) ?></td>
                        <td class="text-center"><?= (int)$prov['transacciones'] ?></td>
                        <td class="text-right"><?= $fmt($prov['bruto']) ?></td>
                        <td class="text-right"><?= $fmt($prov['comision']) ?></td>
                        <td class="text-right text-green"><?= $fmt($prov['neto']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted">Sin datos de proveedores aún.</p>
    <?php endif; ?>

    <!-- ÚLTIMAS TRANSACCIONES -->
    <div class="section-title">Últimas 50 Transacciones</div>
    <?php if (!empty($reporte['recientes'])): ?>
        <table class="table-r">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Proveedor</th>
                    <th>Servicio</th>
                    <th class="text-right">Bruto</th>
                    <th class="text-right">Neto</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reporte['recientes'] as $t): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($t['created_at'])) ?></td>
                        <td><?= htmlspecialchars($t['cliente']) ?></td>
                        <td><?= htmlspecialchars($t['proveedor']) ?></td>
                        <td><?= htmlspecialchars($t['servicio']) ?></td>
                        <td class="text-right"><?= $fmt($t['bruto']) ?></td>
                        <td class="text-right text-green"><?= $fmt($t['neto']) ?></td>
                        <td class="text-center">
                            <?php if ($t['liberado']): ?>
                                <span class="badge-ok">Liberado</span>
                            <?php else: ?>
                                <span class="badge-pend">Retenido</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted">Sin transacciones registradas aún.</p>
    <?php endif; ?>

    <div class="footer">Proviservers — Reporte de Ingresos por Servicios — <?= date('d/m/Y H:i') ?></div>

</body>
</html>
