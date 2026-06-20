<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Servicios Ofrecidos - Proviservers</title>
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
        .stats-row { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .stats-row td { width: 16.6%; padding: 6px; text-align: center; }
        .stat-card {
            background: #f2f7ff;
            border: 1px solid #c8dcff;
            border-radius: 6px;
            padding: 10px 6px;
        }
        .stat-number { font-size: 22px; font-weight: bold; color: #0066ff; display: block; }
        .stat-label  { font-size: 9px; color: #555; margin-top: 4px; }
        .stat-number.green  { color: #28a745; }
        .stat-number.yellow { color: #ffc107; }
        .stat-number.red    { color: #dc3545; }
        .stat-number.teal   { color: #17a2b8; }
        .stat-number.gray   { color: #6c757d; }
        .table-reporte { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 10px; }
        .table-reporte th, .table-reporte td { border: 1px solid #ddd; padding: 6px 7px; text-align: left; }
        .table-reporte th { background-color: #f2f2f2; color: #333; text-transform: uppercase; font-size: 9px; }
        .table-reporte tr:nth-child(even) td { background-color: #fafafa; }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .badge-aprobado  { background: #d4edda; color: #155724; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-pendiente { background: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-rechazado { background: #f8d7da; color: #721c24; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .cat-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .cat-table td { padding: 4px 6px; font-size: 10px; }
        .cat-name { width: 120px; font-weight: bold; }
        .cat-bar  { width: 55%; }
        .cat-count { width: 40px; text-align: right; color: #555; }
        .filtros-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 10px;
            margin-bottom: 16px;
        }
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

    <h1 class="header-title">Reporte de Servicios Ofrecidos</h1>
    <p class="description-paragraph">
        Publicaciones de servicios registradas en la plataforma Proviservers.<br>
        Generado el <?= date('d/m/Y H:i') ?>.
    </p>

    <?php
        $global        = $reporte['global'];
        $publicaciones = $reporte['publicaciones'];
        $porCategoria  = $reporte['porCategoria'];

        $total            = (int)($global['total']             ?? 0);
        $aprobados        = (int)($global['aprobados']         ?? 0);
        $pendientes       = (int)($global['pendientes']        ?? 0);
        $rechazados       = (int)($global['rechazados']        ?? 0);
        $totalSolicitudes = (int)($global['total_solicitudes'] ?? 0);
        $totalContratos   = (int)($global['total_contratos']   ?? 0);

        $filtrosActivos = array_filter([
            'Categoría'  => $filtros['categoria'] ?? null,
            'Estado'     => $filtros['estado']    ?? null,
            'Proveedor'  => $filtros['proveedor'] ?? null,
            'Desde'      => $filtros['desde']     ?? null,
            'Hasta'      => $filtros['hasta']     ?? null,
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
            <td><div class="stat-card"><span class="stat-number"><?= $total ?></span><span class="stat-label">Total publicaciones</span></div></td>
            <td><div class="stat-card"><span class="stat-number green"><?= $aprobados ?></span><span class="stat-label">Aprobadas</span></div></td>
            <td><div class="stat-card"><span class="stat-number yellow"><?= $pendientes ?></span><span class="stat-label">Pendientes</span></div></td>
            <td><div class="stat-card"><span class="stat-number red"><?= $rechazados ?></span><span class="stat-label">Rechazadas</span></div></td>
            <td><div class="stat-card"><span class="stat-number teal"><?= $totalSolicitudes ?></span><span class="stat-label">Solicitudes recibidas</span></div></td>
            <td><div class="stat-card"><span class="stat-number gray"><?= $totalContratos ?></span><span class="stat-label">Contratos generados</span></div></td>
        </tr>
    </table>

    <!-- POR CATEGORÍA -->
    <?php if (!empty($porCategoria)): ?>
        <div class="section-title">Publicaciones por Categoría</div>
        <?php $maxCat = max(array_column($porCategoria, 'total')) ?: 1; ?>
        <table class="cat-table">
            <?php foreach ($porCategoria as $cat): ?>
                <?php $ancho = max(round(($cat['total'] / $maxCat) * 100), 1); ?>
                <tr>
                    <td class="cat-name"><?= htmlspecialchars($cat['categoria']) ?></td>
                    <td class="cat-bar">
                        <table style="width:100%; height:12px; border-collapse:collapse; font-size:1px; line-height:12px;">
                            <tr>
                                <td style="width:<?= $ancho ?>%; background-color:#0066ff; height:12px;"></td>
                                <td style="background-color:#e9ecef; height:12px;"></td>
                            </tr>
                        </table>
                    </td>
                    <td class="cat-count"><?= (int)$cat['total'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- DETALLE DE PUBLICACIONES -->
    <div class="section-title">Detalle de Publicaciones (máx. 200)</div>
    <?php if (!empty($publicaciones)): ?>
        <table class="table-reporte">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Título</th>
                    <th>Proveedor</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th class="text-center">Solicitudes</th>
                    <th class="text-center">Contratos</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($publicaciones as $i => $pub): ?>
                    <?php
                        $badgeClass = match($pub['estado']) {
                            'aprobado'  => 'badge-aprobado',
                            'pendiente' => 'badge-pendiente',
                            'rechazado' => 'badge-rechazado',
                            default     => '',
                        };
                    ?>
                    <tr>
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($pub['titulo'] ?? $pub['servicio_nombre']) ?></td>
                        <td><?= htmlspecialchars($pub['proveedor_nombre']) ?></td>
                        <td><?= htmlspecialchars($pub['categoria_nombre'] ?? '—') ?></td>
                        <td class="text-right">$<?= number_format((float)$pub['precio'], 0, ',', '.') ?></td>
                        <td class="text-center"><span class="<?= $badgeClass ?>"><?= ucfirst($pub['estado']) ?></span></td>
                        <td class="text-center"><?= (int)$pub['solicitudes'] ?></td>
                        <td class="text-center"><?= (int)$pub['contratos'] ?></td>
                        <td><?= date('d/m/Y', strtotime($pub['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color:#777;">No hay publicaciones con los filtros aplicados.</p>
    <?php endif; ?>

    <div class="footer">Proviservers — Reporte de Servicios Ofrecidos — <?= date('d/m/Y H:i') ?></div>

</body>
</html>
