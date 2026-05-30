<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Comprobante de Contratación #<?= (int)$contrato['contrato_id'] ?></title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        color: #1e293b;
        background: #ffffff;
        padding: 0;
    }

    /* ── ENCABEZADO ─────────────────────────────────── */
    .header {
        background: linear-gradient(135deg, #0066ff 0%, #1e3a8a 100%);
        color: #ffffff;
        padding: 28px 40px 22px 40px;
    }
    .header-inner {
        display: table;
        width: 100%;
    }
    .header-logo-cell {
        display: table-cell;
        vertical-align: middle;
        width: 160px;
    }
    .header-logo-cell img {
        max-width: 140px;
        height: auto;
    }
    .header-text-cell {
        display: table-cell;
        vertical-align: middle;
        text-align: right;
    }
    .header-text-cell h1 {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    .header-text-cell p {
        font-size: 10px;
        opacity: 0.85;
        margin-top: 2px;
    }

    /* ── BANDA DE ESTADO ────────────────────────────── */
    .status-bar {
        background: #f1f5f9;
        border-left: 5px solid #0066ff;
        padding: 10px 40px;
        display: table;
        width: 100%;
    }
    .status-bar-cell {
        display: table-cell;
        vertical-align: middle;
    }
    .status-bar-cell:last-child {
        text-align: right;
    }
    .status-bar .label {
        font-size: 10px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 2px;
    }
    .status-bar .value {
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
    }
    .badge-estado {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }
    .badge-finalizado   { background: #d1fae5; color: #065f46; }
    .badge-en_proceso   { background: #dbeafe; color: #1e40af; }
    .badge-confirmado   { background: #fef3c7; color: #92400e; }
    .badge-pendiente    { background: #fce7f3; color: #9d174d; }
    .badge-cancelado    { background: #fee2e2; color: #991b1b; }

    /* ── CONTENIDO PRINCIPAL ────────────────────────── */
    .content {
        padding: 28px 40px;
    }

    /* ── PARTES (Cliente / Proveedor) ───────────────── */
    .partes-table {
        display: table;
        width: 100%;
        border-collapse: separate;
        border-spacing: 12px 0;
        margin-bottom: 24px;
    }
    .parte-cell {
        display: table-cell;
        width: 50%;
        vertical-align: top;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px 18px;
    }
    .parte-cell h3 {
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #0066ff;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 6px;
        margin-bottom: 10px;
    }
    .parte-row {
        margin-bottom: 5px;
    }
    .parte-row .parte-label {
        font-size: 9px;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 1px;
    }
    .parte-row .parte-value {
        font-size: 11px;
        font-weight: 600;
        color: #0f172a;
    }
    .parte-row .parte-value.secondary {
        font-weight: 400;
        color: #475569;
    }

    /* ── SECCIÓN SERVICIO ───────────────────────────── */
    .section-title {
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #0066ff;
        margin-bottom: 10px;
        padding-bottom: 5px;
        border-bottom: 2px solid #e2e8f0;
    }

    .servicio-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px 18px;
        margin-bottom: 20px;
    }
    .servicio-nombre {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
    }
    .servicio-categoria {
        font-size: 10px;
        color: #0066ff;
        font-weight: 600;
        margin-bottom: 8px;
    }
    .servicio-descripcion {
        font-size: 10px;
        color: #475569;
        line-height: 1.5;
        margin-bottom: 12px;
    }

    .servicio-meta-table {
        display: table;
        width: 100%;
    }
    .servicio-meta-cell {
        display: table-cell;
        vertical-align: top;
        width: 33%;
    }
    .meta-label {
        font-size: 9px;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 2px;
    }
    .meta-value {
        font-size: 11px;
        font-weight: 600;
        color: #0f172a;
    }
    .meta-value.precio {
        font-size: 15px;
        color: #0066ff;
    }

    /* ── DETALLES DE EJECUCIÓN ──────────────────────── */
    .detalles-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px 18px;
        margin-bottom: 24px;
    }
    .detalles-grid {
        display: table;
        width: 100%;
    }
    .detalles-col {
        display: table-cell;
        vertical-align: top;
        width: 50%;
        padding-right: 12px;
    }
    .detalle-item {
        margin-bottom: 8px;
    }
    .detalle-item .di-label {
        font-size: 9px;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 1px;
    }
    .detalle-item .di-value {
        font-size: 11px;
        color: #0f172a;
    }

    /* ── SELLO OFICIAL ──────────────────────────────── */
    .sello-row {
        display: table;
        width: 100%;
        margin-top: 20px;
        margin-bottom: 8px;
    }
    .sello-left {
        display: table-cell;
        vertical-align: middle;
        width: 60%;
    }
    .sello-right {
        display: table-cell;
        vertical-align: middle;
        text-align: right;
    }
    .aviso-legal {
        font-size: 9px;
        color: #94a3b8;
        line-height: 1.5;
        max-width: 340px;
    }
    .qr-placeholder {
        display: inline-block;
        width: 70px;
        height: 70px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        text-align: center;
        line-height: 70px;
        font-size: 9px;
        color: #94a3b8;
    }

    /* ── FOOTER ─────────────────────────────────────── */
    .footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #0066ff;
        color: rgba(255,255,255,0.9);
        font-size: 9px;
        padding: 7px 40px;
        display: table;
        width: 100%;
    }
    .footer-left  { display: table-cell; vertical-align: middle; }
    .footer-right { display: table-cell; vertical-align: middle; text-align: right; }

    .divider {
        border: none;
        border-top: 1px solid #e2e8f0;
        margin: 18px 0;
    }
</style>
</head>
<body>

<!-- ══ ENCABEZADO ══════════════════════════════════ -->
<div class="header">
    <div class="header-inner">
        <div class="header-logo-cell">
            <img src="<?= BASE_URL ?>/public/assets/img/logos/logo-principal.png" alt="ProviServers">
        </div>
        <div class="header-text-cell">
            <h1>Comprobante de Contratación</h1>
            <p>Documento oficial generado por la plataforma ProviServers</p>
            <p>Emitido el <?= date('d \d\e F \d\e Y', strtotime($contrato['fecha_contrato'])) ?> · <?= date('H:i', strtotime($contrato['fecha_contrato'])) ?> hrs</p>
        </div>
    </div>
</div>

<!-- ══ BANDA CONTRATO / ESTADO ═════════════════════ -->
<div class="status-bar">
    <div class="status-bar-cell">
        <div class="label">N.° Contrato</div>
        <div class="value">#<?= str_pad((int)$contrato['contrato_id'], 6, '0', STR_PAD_LEFT) ?></div>
    </div>
    <div class="status-bar-cell">
        <div class="label">Servicio</div>
        <div class="value"><?= htmlspecialchars($contrato['servicio_nombre'] ?? 'N/A') ?></div>
    </div>
    <div class="status-bar-cell">
        <div class="label">Estado actual</div>
        <div class="value">
            <?php
            $est = $contrato['estado'] ?? 'pendiente';
            $etiquetas = [
                'finalizado'         => 'Finalizado',
                'en_proceso'         => 'En Proceso',
                'confirmado'         => 'Confirmado',
                'pendiente'          => 'Pendiente',
                'cancelado'          => 'Cancelado',
                'cancelado_cliente'  => 'Cancelado por cliente',
                'cancelado_proveedor'=> 'Cancelado por proveedor',
            ];
            ?>
            <span class="badge-estado badge-<?= htmlspecialchars($est) ?>">
                <?= htmlspecialchars($etiquetas[$est] ?? ucfirst($est)) ?>
            </span>
        </div>
    </div>
</div>

<!-- ══ CONTENIDO ═══════════════════════════════════ -->
<div class="content">

    <!-- PARTES -->
    <p class="section-title">Partes del Contrato</p>
    <div class="partes-table">
        <div class="parte-cell">
            <h3>Cliente</h3>
            <div class="parte-row">
                <div class="parte-label">Nombre completo</div>
                <div class="parte-value"><?= htmlspecialchars($contrato['cliente_nombre'] ?? '-') ?></div>
            </div>
            <div class="parte-row">
                <div class="parte-label">Correo electrónico</div>
                <div class="parte-value secondary"><?= htmlspecialchars($contrato['cliente_email'] ?? '-') ?></div>
            </div>
            <div class="parte-row">
                <div class="parte-label">Teléfono</div>
                <div class="parte-value secondary"><?= htmlspecialchars($contrato['cliente_telefono'] ?? '-') ?></div>
            </div>
            <div class="parte-row">
                <div class="parte-label">Ubicación</div>
                <div class="parte-value secondary"><?= htmlspecialchars($contrato['cliente_ubicacion'] ?? '-') ?></div>
            </div>
        </div>

        <div class="parte-cell">
            <h3>Proveedor de Servicios</h3>
            <div class="parte-row">
                <div class="parte-label">Nombre completo</div>
                <div class="parte-value"><?= htmlspecialchars($contrato['proveedor_nombre'] ?? '-') ?></div>
            </div>
            <div class="parte-row">
                <div class="parte-label">Correo electrónico</div>
                <div class="parte-value secondary"><?= htmlspecialchars($contrato['proveedor_email'] ?? '-') ?></div>
            </div>
            <div class="parte-row">
                <div class="parte-label">Teléfono</div>
                <div class="parte-value secondary"><?= htmlspecialchars($contrato['proveedor_telefono'] ?? '-') ?></div>
            </div>
            <div class="parte-row">
                <div class="parte-label">Ubicación</div>
                <div class="parte-value secondary"><?= htmlspecialchars($contrato['proveedor_ubicacion'] ?? '-') ?></div>
            </div>
        </div>
    </div>

    <!-- SERVICIO CONTRATADO -->
    <p class="section-title">Servicio Contratado</p>
    <div class="servicio-box">
        <div class="servicio-nombre"><?= htmlspecialchars($contrato['servicio_nombre'] ?? '-') ?></div>
        <?php if (!empty($contrato['categoria_nombre'])): ?>
        <div class="servicio-categoria"><?= htmlspecialchars($contrato['categoria_nombre']) ?></div>
        <?php endif; ?>
        <?php if (!empty($contrato['servicio_descripcion'])): ?>
        <div class="servicio-descripcion"><?= htmlspecialchars($contrato['servicio_descripcion']) ?></div>
        <?php endif; ?>

        <div class="servicio-meta-table">
            <div class="servicio-meta-cell">
                <div class="meta-label">Precio pactado</div>
                <div class="meta-value precio">
                    $<?= number_format((float)($contrato['precio'] ?? 0), 0, ',', '.') ?> COP
                </div>
            </div>
            <div class="servicio-meta-cell">
                <div class="meta-label">Fecha de solicitud</div>
                <div class="meta-value">
                    <?= !empty($contrato['fecha_solicitud'])
                        ? date('d/m/Y', strtotime($contrato['fecha_solicitud']))
                        : date('d/m/Y', strtotime($contrato['fecha_contrato'])) ?>
                </div>
            </div>
            <div class="servicio-meta-cell">
                <div class="meta-label">Fecha de ejecución</div>
                <div class="meta-value">
                    <?= !empty($contrato['fecha_ejecucion'])
                        ? date('d/m/Y', strtotime($contrato['fecha_ejecucion']))
                        : (!empty($contrato['solicitud_fecha_preferida'])
                            ? date('d/m/Y', strtotime($contrato['solicitud_fecha_preferida']))
                            : 'Por acordar') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- DETALLES DE EJECUCIÓN -->
    <?php if (!empty($contrato['solicitud_direccion']) || !empty($contrato['solicitud_ciudad'])): ?>
    <p class="section-title">Detalles de Ejecución</p>
    <div class="detalles-box">
        <div class="detalles-grid">
            <div class="detalles-col">
                <?php if (!empty($contrato['solicitud_direccion'])): ?>
                <div class="detalle-item">
                    <div class="di-label">Dirección</div>
                    <div class="di-value"><?= htmlspecialchars($contrato['solicitud_direccion']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($contrato['solicitud_ciudad'])): ?>
                <div class="detalle-item">
                    <div class="di-label">Ciudad</div>
                    <div class="di-value"><?= htmlspecialchars($contrato['solicitud_ciudad']) ?></div>
                </div>
                <?php endif; ?>
            </div>
            <div class="detalles-col">
                <?php if (!empty($contrato['solicitud_franja_horaria'])): ?>
                <div class="detalle-item">
                    <div class="di-label">Franja horaria</div>
                    <div class="di-value"><?= htmlspecialchars(ucfirst($contrato['solicitud_franja_horaria'])) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($contrato['solicitud_descripcion'])): ?>
                <div class="detalle-item">
                    <div class="di-label">Descripción del trabajo</div>
                    <div class="di-value"><?= htmlspecialchars(mb_strimwidth($contrato['solicitud_descripcion'], 0, 120, '…')) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <hr class="divider">

    <!-- SELLO / AVISO LEGAL -->
    <div class="sello-row">
        <div class="sello-left">
            <p class="aviso-legal">
                Este documento es un comprobante oficial generado automáticamente por la
                plataforma ProviServers. Certifica el acuerdo de contratación entre las
                partes mencionadas bajo los términos y condiciones aceptados al momento
                del registro. Para disputas o consultas, comuníquese con soporte en
                soporte@proviservers.co.
            </p>
        </div>
        <div class="sello-right">
            <div class="qr-placeholder">SELLO<br>DIGITAL</div>
        </div>
    </div>

</div>

<!-- ══ FOOTER ══════════════════════════════════════ -->
<div class="footer">
    <div class="footer-left">© <?= date('Y') ?> ProviServers · Todos los derechos reservados</div>
    <div class="footer-right">Contrato #<?= str_pad((int)$contrato['contrato_id'], 6, '0', STR_PAD_LEFT) ?> · Generado: <?= date('d/m/Y H:i') ?></div>
</div>

</body>
</html>
