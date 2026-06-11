<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Calificaciones - Proviservers</title>
    <style>
        body {
            font-family: "Poppins", sans-serif;
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
        .stats-row { width: 100%; margin-bottom: 20px; }
        .stats-row td { width: 25%; padding: 6px; text-align: center; }
        .stat-card {
            background: #f2f7ff;
            border: 1px solid #c8dcff;
            border-radius: 6px;
            padding: 12px 8px;
        }
        .stat-number { font-size: 26px; font-weight: bold; color: #0066ff; display: block; }
        .stat-label  { font-size: 10px; color: #555; margin-top: 4px; }
        .dist-table  { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .dist-table td { padding: 5px 8px; font-size: 11px; }
        .star-label { width: 60px; font-weight: bold; color: #f5a623; }
        .pct-label  { width: 40px; text-align: right; color: #555; }
        .table-reporte { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 10px; }
        .table-reporte th, .table-reporte td { border: 1px solid #ddd; padding: 7px 8px; text-align: left; }
        .table-reporte th { background-color: #f2f2f2; color: #333; text-transform: uppercase; font-size: 9px; }
        .table-reporte tr:nth-child(even) td { background-color: #fafafa; }
        .stars { color: #f5a623; }
        .badge-alto  { background: #d4edda; color: #155724; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-medio { background: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-bajo  { background: #f8d7da; color: #721c24; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .footer {
            position: fixed; bottom: 0; left: 0; right: 0;
            height: 30px; line-height: 30px; text-align: center;
            font-size: 10px; color: #777; border-top: 1px solid #eee;
        }
        .text-center { text-align: center; }
        .text-muted  { color: #777; }
    </style>
</head>
<body>

    <div class="logo-container">
        <img class="logo" src="<?= BASE_URL ?>/public/assets/img/logos/logo-principal.png" alt="Proviservers">
    </div>

    <h1 class="header-title">Reporte de Calificaciones y Valoraciones</h1>
    <p class="description-paragraph">
        Resumen global de las valoraciones registradas en la plataforma Proviservers.<br>
        Generado el <?= date('d/m/Y H:i') ?>.
    </p>

    <?php
        $global  = $reporte['global'];
        $total   = (int)($global['total']   ?? 0);
        $prom    = (float)($global['promedio'] ?? 0);
        $dist    = [
            5 => (int)($global['cinco']  ?? 0),
            4 => (int)($global['cuatro'] ?? 0),
            3 => (int)($global['tres']   ?? 0),
            2 => (int)($global['dos']    ?? 0),
            1 => (int)($global['uno']    ?? 0),
        ];
        $positivas = $dist[5] + $dist[4];
        $bajas     = $dist[1] + $dist[2];
    ?>

    <!-- RESUMEN GLOBAL -->
    <div class="section-title">Resumen Global</div>
    <table class="stats-row">
        <tr>
            <td><div class="stat-card"><span class="stat-number"><?= $total ?></span><span class="stat-label">Total valoraciones</span></div></td>
            <td><div class="stat-card"><span class="stat-number"><?= number_format($prom, 1) ?> ★</span><span class="stat-label">Promedio global</span></div></td>
            <td><div class="stat-card"><span class="stat-number"><?= $positivas ?></span><span class="stat-label">Positivas (4-5 ★)</span></div></td>
            <td><div class="stat-card"><span class="stat-number"><?= $bajas ?></span><span class="stat-label">Bajas (1-2 ★)</span></div></td>
        </tr>
    </table>

    <!-- DISTRIBUCIÓN -->
    <div class="section-title">Distribución por Estrellas</div>
    <table class="dist-table">
        <?php foreach ([5, 4, 3, 2, 1] as $estrella): ?>
            <?php
                $cant  = $dist[$estrella];
                $pct   = $total > 0 ? round(($cant / $total) * 100) : 0;
                $ancho = max($pct, 1);
            ?>
            <tr>
                <td class="star-label"><?= str_repeat('*', $estrella) ?> (<?= $estrella ?>)</td>
                <td style="width:60%; padding:2px 4px;">
                    <table style="width:100%; height:14px; border-collapse:collapse; font-size:1px; line-height:14px;">
                        <tr>
                            <td style="width:<?= $ancho ?>%; background-color:#0066ff; height:14px;"></td>
                            <td style="background-color:#e9ecef; height:14px;"></td>
                        </tr>
                    </table>
                </td>
                <td class="pct-label"><?= $pct ?>%</td>
                <td style="width:50px; color:#555;"><?= $cant ?> votos</td>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- TOP PROVEEDORES -->
    <div class="section-title">Top Proveedores por Calificación</div>
    <?php if (!empty($reporte['topProveedores'])): ?>
        <table class="table-reporte">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Proveedor</th>
                    <th>Promedio</th>
                    <th>Valoraciones</th>
                    <th>Nivel</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reporte['topProveedores'] as $i => $prov): ?>
                    <?php
                        $p = (float)$prov['promedio'];
                        if ($p >= 4.0)      $badge = '<span class="badge-alto">Excelente</span>';
                        elseif ($p >= 3.0)  $badge = '<span class="badge-medio">Regular</span>';
                        else                $badge = '<span class="badge-bajo">Bajo</span>';
                    ?>
                    <tr>
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($prov['proveedor']) ?></td>
                        <td class="text-center stars"><?= number_format($p, 2) ?> *</td>
                        <td class="text-center"><?= (int)$prov['total'] ?></td>
                        <td class="text-center"><?= $badge ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted">No hay valoraciones registradas aún.</p>
    <?php endif; ?>

    <!-- ÚLTIMAS VALORACIONES -->
    <div class="section-title">Últimas 50 Valoraciones</div>
    <?php if (!empty($reporte['recientes'])): ?>
        <table class="table-reporte">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Proveedor</th>
                    <th>Servicio</th>
                    <th>Cal.</th>
                    <th>Comentario</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reporte['recientes'] as $val): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($val['created_at'])) ?></td>
                        <td><?= htmlspecialchars($val['cliente']) ?></td>
                        <td><?= htmlspecialchars($val['proveedor']) ?></td>
                        <td><?= htmlspecialchars($val['servicio']) ?></td>
                        <td class="text-center stars"><?= (int)$val['calificacion'] ?> *</td>
                        <td><?= htmlspecialchars($val['comentario'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted">No hay valoraciones registradas aún.</p>
    <?php endif; ?>

    <div class="footer">Proviservers — Reporte de Calificaciones — <?= date('d/m/Y H:i') ?></div>

</body>
</html>
