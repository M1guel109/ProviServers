<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Proveedores - Proviservers</title>
    <style>
        body { font-family: "Helvetica", sans-serif; margin: 40px; padding: 0; font-size: 12px; color: #333; }
        .logo-container { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 150px; height: auto; }
        .header-title { color: #0066ff; text-align: center; font-size: 24px; margin-top: 20px; margin-bottom: 6px; }
        .description-paragraph { color: #000; margin-bottom: 24px; line-height: 1.5; font-size: 12px; text-align: center; }
        .section-title { font-size: 14px; font-weight: bold; color: #0066ff; border-bottom: 2px solid #0066ff; padding-bottom: 4px; margin-top: 28px; margin-bottom: 12px; }
        .stats-row { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .stats-row td { width: 16.6%; padding: 5px; text-align: center; }
        .stat-card { background: #f2f7ff; border: 1px solid #c8dcff; border-radius: 6px; padding: 10px 6px; }
        .stat-number { font-size: 20px; font-weight: bold; color: #0066ff; display: block; }
        .stat-number.green  { color: #28a745; }
        .stat-number.yellow { color: #ffc107; }
        .stat-number.gray   { color: #6c757d; }
        .stat-label { font-size: 9px; color: #555; margin-top: 4px; }
        .filtros-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 8px 12px; font-size: 10px; margin-bottom: 16px; }
        .dist-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .dist-table td { padding: 4px 6px; font-size: 10px; }
        .dist-name  { width: 120px; font-weight: bold; }
        .dist-bar   { width: 55%; padding: 2px 4px; }
        .dist-count { width: 50px; text-align: right; color: #555; }
        .table-reporte { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 10px; }
        .table-reporte th, .table-reporte td { border: 1px solid #ddd; padding: 5px 6px; text-align: left; }
        .table-reporte th { background-color: #f2f2f2; color: #333; text-transform: uppercase; font-size: 9px; }
        .table-reporte tr:nth-child(even) td { background-color: #fafafa; }
        .text-center { text-align: center; }
        .badge-experto   { background: #d4edda; color: #155724; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-confiable { background: #cce5ff; color: #004085; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-validado  { background: #d1ecf1; color: #0c5460; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-nuevo     { background: #e2e3e5; color: #383d41; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; height: 30px; line-height: 30px; text-align: center; font-size: 10px; color: #777; border-top: 1px solid #eee; }
    </style>
</head>
<body>

    <div class="logo-container">
        <img class="logo" src="<?= BASE_URL ?>/public/assets/img/logos/logo-principal.png" alt="Proviservers">
    </div>

    <h1 class="header-title">Reporte de Proveedores</h1>
    <p class="description-paragraph">
        Métricas de proveedores registrados en Proviservers.<br>
        Generado el <?= date('d/m/Y H:i') ?>.
    </p>

    <?php
        $global       = $reporte['global'];
        $porNivel     = $reporte['porNivel'];
        $porCal       = $reporte['porCalificacion'];
        $porCategoria = $reporte['porCategoria'];
        $detalle      = $reporte['detalle'];

        $total       = (int)($global['total']              ?? 0);
        $verificados = (int)($global['verificados']         ?? 0);
        $noVerif     = (int)($global['no_verificados']      ?? 0);
        $promCal     = (float)($global['prom_calificacion'] ?? 0);
        $expertos    = (int)($global['expertos']            ?? 0);
        $confiables  = (int)($global['confiables']          ?? 0);

        $nivelLabels = ['nuevo' => 'Nuevo', 'validado' => 'Validado', 'confiable' => 'Confiable', 'experto' => 'Experto'];

        $filtrosActivos = array_filter([
            'Nivel'      => isset($filtros['nivel_confianza']) ? ($nivelLabels[$filtros['nivel_confianza']] ?? null) : null,
            'Verificado' => isset($filtros['verificado']) && $filtros['verificado'] !== '' ? ($filtros['verificado'] ? 'Sí' : 'No') : null,
            'Cal. min'   => $filtros['cal_min'] ?? null,
            'Cal. max'   => $filtros['cal_max'] ?? null,
        ]);
    ?>

    <?php if (!empty($filtrosActivos)): ?>
        <div class="filtros-box">
            <strong>Filtros aplicados:</strong>
            <?php foreach ($filtrosActivos as $label => $valor): ?>
                <strong><?= $label ?>:</strong> <?= htmlspecialchars((string)$valor) ?> &nbsp;
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- RESUMEN GLOBAL -->
    <div class="section-title">Resumen Global</div>
    <table class="stats-row">
        <tr>
            <td><div class="stat-card"><span class="stat-number"><?= $total ?></span><span class="stat-label">Total proveedores</span></div></td>
            <td><div class="stat-card"><span class="stat-number green"><?= $verificados ?></span><span class="stat-label">Verificados</span></div></td>
            <td><div class="stat-card"><span class="stat-number gray"><?= $noVerif ?></span><span class="stat-label">No verificados</span></div></td>
            <td><div class="stat-card"><span class="stat-number yellow"><?= number_format($promCal, 2) ?></span><span class="stat-label">Cal. promedio</span></div></td>
            <td><div class="stat-card"><span class="stat-number green"><?= $expertos ?></span><span class="stat-label">Expertos</span></div></td>
            <td><div class="stat-card"><span class="stat-number"><?= $confiables ?></span><span class="stat-label">Confiables</span></div></td>
        </tr>
    </table>

    <!-- POR NIVEL -->
    <?php if (!empty($porNivel)): ?>
        <div class="section-title">Por Nivel de Confianza</div>
        <?php $maxNivel = max(array_column($porNivel, 'total')) ?: 1; ?>
        <table class="dist-table">
            <?php foreach ($porNivel as $n): ?>
                <?php $ancho = max(round(($n['total'] / $maxNivel) * 100), 1); ?>
                <tr>
                    <td class="dist-name"><?= $nivelLabels[$n['nivel']] ?? ucfirst($n['nivel']) ?></td>
                    <td class="dist-bar">
                        <table style="width:100%; height:12px; border-collapse:collapse; font-size:1px; line-height:12px;">
                            <tr>
                                <td style="width:<?= $ancho ?>%; background-color:#0066ff; height:12px;"></td>
                                <td style="background-color:#e9ecef; height:12px;"></td>
                            </tr>
                        </table>
                    </td>
                    <td class="dist-count"><?= (int)$n['total'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- POR CALIFICACIÓN -->
    <?php if (!empty($porCal)): ?>
        <div class="section-title">Por Rango de Calificación</div>
        <?php $maxCal = max(array_column($porCal, 'total')) ?: 1; ?>
        <table class="dist-table">
            <?php foreach ($porCal as $r): ?>
                <?php $ancho = max(round(($r['total'] / $maxCal) * 100), 1); ?>
                <tr>
                    <td class="dist-name">&#9733; <?= $r['rango'] ?></td>
                    <td class="dist-bar">
                        <table style="width:100%; height:12px; border-collapse:collapse; font-size:1px; line-height:12px;">
                            <tr>
                                <td style="width:<?= $ancho ?>%; background-color:#ffc107; height:12px;"></td>
                                <td style="background-color:#e9ecef; height:12px;"></td>
                            </tr>
                        </table>
                    </td>
                    <td class="dist-count"><?= (int)$r['total'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- POR CATEGORÍA -->
    <?php if (!empty($porCategoria)): ?>
        <div class="section-title">Por Categoría (publicaciones aprobadas)</div>
        <?php $maxCat = max(array_column($porCategoria, 'proveedores')) ?: 1; ?>
        <table class="dist-table">
            <?php foreach ($porCategoria as $cat): ?>
                <?php $ancho = max(round(($cat['proveedores'] / $maxCat) * 100), 1); ?>
                <tr>
                    <td class="dist-name"><?= htmlspecialchars($cat['categoria']) ?></td>
                    <td class="dist-bar">
                        <table style="width:100%; height:12px; border-collapse:collapse; font-size:1px; line-height:12px;">
                            <tr>
                                <td style="width:<?= $ancho ?>%; background-color:#0066ff; height:12px;"></td>
                                <td style="background-color:#e9ecef; height:12px;"></td>
                            </tr>
                        </table>
                    </td>
                    <td class="dist-count"><?= (int)$cat['proveedores'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- DETALLE -->
    <div class="section-title">Detalle de Proveedores (máx. 200)</div>
    <?php if (!empty($detalle)): ?>
        <table class="table-reporte">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Ubicación</th>
                    <th>Nivel</th>
                    <th>Verif.</th>
                    <th>Cal.</th>
                    <th>Pub. aprob.</th>
                    <th>Contratos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalle as $p): ?>
                    <?php
                        $badgeClass = match($p['nivel_confianza']) {
                            'experto'   => 'badge-experto',
                            'confiable' => 'badge-confiable',
                            'validado'  => 'badge-validado',
                            default     => 'badge-nuevo',
                        };
                    ?>
                    <tr>
                        <td class="text-center"><?= (int)$p['id'] ?></td>
                        <td><?= htmlspecialchars($p['nombre']) ?></td>
                        <td><?= htmlspecialchars($p['ubicacion'] ?? '—') ?></td>
                        <td class="text-center"><span class="<?= $badgeClass ?>"><?= $nivelLabels[$p['nivel_confianza']] ?? ucfirst($p['nivel_confianza']) ?></span></td>
                        <td class="text-center"><?= $p['verificado'] ? 'Si' : 'No' ?></td>
                        <td class="text-center"><?= number_format((float)$p['calificacion_promedio'], 2) ?></td>
                        <td class="text-center"><?= (int)$p['publicaciones_aprobadas_count'] ?></td>
                        <td class="text-center"><?= (int)$p['total_contratos'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color:#777;">No hay proveedores con los filtros aplicados.</p>
    <?php endif; ?>

    <div class="footer">Proviservers — Reporte de Proveedores — <?= date('d/m/Y H:i') ?></div>

</body>
</html>
