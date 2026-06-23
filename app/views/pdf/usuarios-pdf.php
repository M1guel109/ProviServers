<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Usuarios - Proviservers</title>
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
        .stat-number.teal   { color: #17a2b8; }
        .stat-number.yellow { color: #ffc107; }
        .stat-number.red    { color: #dc3545; }
        .stat-label { font-size: 9px; color: #555; margin-top: 4px; }
        .filtros-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 8px 12px; font-size: 10px; margin-bottom: 16px; }
        .dist-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .dist-table td { padding: 4px 6px; font-size: 10px; }
        .dist-name  { width: 100px; font-weight: bold; }
        .dist-bar   { width: 60%; padding: 2px 4px; }
        .dist-count { width: 50px; text-align: right; color: #555; }
        .table-reporte { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 10px; }
        .table-reporte th, .table-reporte td { border: 1px solid #ddd; padding: 5px 6px; text-align: left; }
        .table-reporte th { background-color: #f2f2f2; color: #333; text-transform: uppercase; font-size: 9px; }
        .table-reporte tr:nth-child(even) td { background-color: #fafafa; }
        .text-center { text-align: center; }
        .badge-cliente   { background: #d1ecf1; color: #0c5460; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-proveedor { background: #d4edda; color: #155724; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-admin     { background: #343a40; color: #fff;    padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-activo    { background: #d4edda; color: #155724; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-pendiente { background: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-suspendido { background: #f8d7da; color: #721c24; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-inactivo  { background: #e2e3e5; color: #383d41; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .badge-bloqueado { background: #343a40; color: #fff;    padding: 2px 6px; border-radius: 10px; font-size: 9px; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; height: 30px; line-height: 30px; text-align: center; font-size: 10px; color: #777; border-top: 1px solid #eee; }
    </style>
</head>
<body>

    <div class="logo-container">
        <img class="logo" src="<?= BASE_URL ?>/public/assets/img/logos/logo-principal.png" alt="Proviservers">
    </div>

    <h1 class="header-title">Reporte de Usuarios</h1>
    <p class="description-paragraph">
        Métricas de usuarios registrados en Proviservers.<br>
        Generado el <?= date('d/m/Y H:i') ?>.
    </p>

    <?php
        $global      = $reporte['global'];
        $crecimiento = $reporte['crecimiento'];
        $porEstado   = $reporte['porEstado'];
        $detalle     = $reporte['detalle'];

        $total       = (int)($global['total']       ?? 0);
        $clientes    = (int)($global['clientes']    ?? 0);
        $proveedores = (int)($global['proveedores'] ?? 0);
        $activos     = (int)($global['activos']     ?? 0);
        $pendientes  = (int)($global['pendientes']  ?? 0);
        $suspendidos = (int)($global['suspendidos'] ?? 0);

        $estadoLabels = [
            'activo'     => 'Activo',
            'pendiente'  => 'Pendiente',
            'suspendido' => 'Suspendido',
            'inactivo'   => 'Inactivo',
            'bloqueado'  => 'Bloqueado',
        ];

        $filtrosActivos = array_filter([
            'Desde' => $filtros['desde'] ?? null,
            'Hasta' => $filtros['hasta'] ?? null,
            'Rol'   => $filtros['rol']   ?? null,
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
            <td><div class="stat-card"><span class="stat-number"><?= $total ?></span><span class="stat-label">Total usuarios</span></div></td>
            <td><div class="stat-card"><span class="stat-number teal"><?= $clientes ?></span><span class="stat-label">Clientes</span></div></td>
            <td><div class="stat-card"><span class="stat-number green"><?= $proveedores ?></span><span class="stat-label">Proveedores</span></div></td>
            <td><div class="stat-card"><span class="stat-number green"><?= $activos ?></span><span class="stat-label">Activos</span></div></td>
            <td><div class="stat-card"><span class="stat-number yellow"><?= $pendientes ?></span><span class="stat-label">Pendientes</span></div></td>
            <td><div class="stat-card"><span class="stat-number red"><?= $suspendidos ?></span><span class="stat-label">Suspendidos</span></div></td>
        </tr>
    </table>

    <!-- POR ESTADO -->
    <?php if (!empty($porEstado)): ?>
        <div class="section-title">Distribución por Estado</div>
        <?php $maxEst = max(array_column($porEstado, 'total')) ?: 1; ?>
        <table class="dist-table">
            <?php foreach ($porEstado as $e): ?>
                <?php $ancho = max(round(($e['total'] / $maxEst) * 100), 1); ?>
                <tr>
                    <td class="dist-name"><?= $estadoLabels[$e['estado']] ?? ucfirst($e['estado']) ?></td>
                    <td class="dist-bar">
                        <table style="width:100%; height:12px; border-collapse:collapse; font-size:1px; line-height:12px;">
                            <tr>
                                <td style="width:<?= $ancho ?>%; background-color:#0066ff; height:12px;"></td>
                                <td style="background-color:#e9ecef; height:12px;"></td>
                            </tr>
                        </table>
                    </td>
                    <td class="dist-count"><?= (int)$e['total'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <!-- CRECIMIENTO -->
    <?php if (!empty($crecimiento)): ?>
        <div class="section-title">Crecimiento de Registros por Período</div>
        <table class="table-reporte">
            <thead>
                <tr>
                    <th>Período</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Clientes</th>
                    <th class="text-center">Proveedores</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($crecimiento as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['label']) ?></td>
                        <td class="text-center"><?= (int)$c['total'] ?></td>
                        <td class="text-center"><?= (int)$c['clientes'] ?></td>
                        <td class="text-center"><?= (int)$c['proveedores'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- DETALLE -->
    <div class="section-title">Detalle de Usuarios (máx. 200)</div>
    <?php if (!empty($detalle)): ?>
        <table class="table-reporte">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Registro</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalle as $u): ?>
                    <tr>
                        <td class="text-center"><?= (int)$u['id'] ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td class="text-center"><span class="badge-<?= $u['rol'] ?>"><?= ucfirst($u['rol']) ?></span></td>
                        <td class="text-center"><span class="badge-<?= $u['estado'] ?>"><?= $estadoLabels[$u['estado']] ?? ucfirst($u['estado']) ?></span></td>
                        <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="color:#777;">No hay usuarios con los filtros aplicados.</p>
    <?php endif; ?>

    <div class="footer">Proviservers — Reporte de Usuarios — <?= date('d/m/Y H:i') ?></div>

</body>
</html>
