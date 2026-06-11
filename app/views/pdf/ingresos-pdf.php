<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ingresos por Membresías - Proviservers</title>
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
        .kpi-card.verde   { background: #f0fff4; border-color: #b2dfdb; }
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
        .badge-ok   { background: #d4edda; color: #155724; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
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

    <h1 class="header-title">Reporte de Ingresos por Membresías</h1>
    <p class="description-paragraph">
        Ingresos generados por la venta de planes de membresía en la plataforma Proviservers.<br>
        Generado: <?= date('d/m/Y H:i') ?>
    </p>

    <?php
        $g   = $reporte['global'];
        $fmt = fn($n) => '$' . number_format((float)$n, 0, ',', '.');
    ?>

    <!-- KPIs GLOBALES -->
    <div class="section-title">Resumen Global</div>
    <table class="kpi-table">
        <tr>
            <td><div class="kpi-card">
                <span class="kpi-num"><?= (int)$g['total_pagos'] ?></span>
                <span class="kpi-label">Total pagos registrados</span>
            </div></td>
            <td><div class="kpi-card verde">
                <span class="kpi-num"><?= $fmt($g['confirmado']) ?></span>
                <span class="kpi-label">Ingresos confirmados</span>
            </div></td>
            <td><div class="kpi-card naranja">
                <span class="kpi-num"><?= $fmt($g['pendiente']) ?></span>
                <span class="kpi-label">Monto pendiente</span>
            </div></td>
            <td><div class="kpi-card">
                <span class="kpi-num"><?= (int)$g['pagos_confirmados'] ?></span>
                <span class="kpi-label">Pagos confirmados</span>
            </div></td>
        </tr>
    </table>

    <!-- POR PLAN -->
    <div class="section-title">Ingresos por Plan de Membresía</div>
    <?php if (!empty($reporte['porPlan'])): ?>
        <table class="table-r">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th class="text-center">Ventas</th>
                    <th class="text-right">Total ingresado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reporte['porPlan'] as $plan): ?>
                    <tr>
                        <td><?= htmlspecialchars($plan['plan']) ?></td>
                        <td class="text-center"><?= (int)$plan['ventas'] ?></td>
                        <td class="text-right text-green"><?= $fmt($plan['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted">Sin ventas de membresías registradas aún.</p>
    <?php endif; ?>

    <!-- POR MES -->
    <div class="section-title">Ingresos por Mes (últimos 12 meses)</div>
    <?php if (!empty($reporte['porMes'])): ?>
        <table class="table-r">
            <thead>
                <tr>
                    <th>Período</th>
                    <th class="text-center">Pagos</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reporte['porMes'] as $mes): ?>
                    <tr>
                        <td><?= htmlspecialchars($mes['periodo']) ?></td>
                        <td class="text-center"><?= (int)$mes['pagos'] ?></td>
                        <td class="text-right text-green"><?= $fmt($mes['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted">Sin ingresos confirmados por período aún.</p>
    <?php endif; ?>

    <!-- ÚLTIMOS PAGOS -->
    <div class="section-title">Últimos 50 Pagos de Membresías</div>
    <?php if (!empty($reporte['recientes'])): ?>
        <table class="table-r">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th>Plan</th>
                    <th>Método</th>
                    <th class="text-right">Monto</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reporte['recientes'] as $p): ?>
                    <tr>
                        <td><?= $p['fecha_pago'] ? date('d/m/Y', strtotime($p['fecha_pago'])) : '—' ?></td>
                        <td><?= htmlspecialchars($p['proveedor']) ?></td>
                        <td><?= htmlspecialchars($p['plan']) ?></td>
                        <td><?= htmlspecialchars($p['metodo_pago'] ?? '—') ?></td>
                        <td class="text-right"><?= $fmt($p['monto']) ?></td>
                        <td class="text-center">
                            <?php if ($p['estado_pago'] === 'pagado'): ?>
                                <span class="badge-ok">Pagado</span>
                            <?php else: ?>
                                <span class="badge-pend"><?= htmlspecialchars($p['estado_pago']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted">Sin pagos registrados aún.</p>
    <?php endif; ?>

    <div class="footer">Proviservers — Reporte de Ingresos por Membresías — <?= date('d/m/Y H:i') ?></div>

</body>
</html>
