<?php
require_once BASE_PATH . '/app/helpers/session-admin.php';
require_once BASE_PATH . '/app/models/Contacto.php';
require_once BASE_PATH . '/app/helpers/lang-helper.php';

$modelo   = new Contacto();
$mensajes = $modelo->listarMensajes();

// Marcar como leído si se solicita
if (isset($_GET['leer']) && is_numeric($_GET['leer'])) {
    $modelo->marcarLeido((int)$_GET['leer']);
    header('Location: ' . BASE_URL . '/admin/mensajes-contacto');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Mensajes de Contacto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard.css">
</head>
<body>

<?php include_once BASE_PATH . '/app/views/layouts/sidebar-administrador.php'; ?>

<main class="contenido">
    <?php include_once BASE_PATH . '/app/views/layouts/header-administrador.php'; ?>

    <section id="titulo-principal">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1><i class="bi bi-envelope-fill me-2 text-primary"></i>Mensajes de Contacto</h1>
                <p class="text-muted mb-0">Mensajes recibidos desde el formulario de la landing page.</p>
            </div>
            <div class="col-md-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 justify-content-md-end">
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>/admin/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                        </li>
                        <li class="breadcrumb-item active">Mensajes de Contacto</li>
                    </ol>
                </nav>
            </div>
        </div>
    </section>

    <section class="mt-4">
        <?php if (empty($mensajes)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                <p class="text-muted">No hay mensajes de contacto todavía.</p>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
                    <span class="fw-bold">
                        Total: <?= count($mensajes) ?> mensaje<?= count($mensajes) !== 1 ? 's' : '' ?>
                        <?php $noLeidos = count(array_filter($mensajes, fn($m) => !$m['leido'])); ?>
                        <?php if ($noLeidos > 0): ?>
                            <span class="badge bg-danger ms-2"><?= $noLeidos ?> nuevo<?= $noLeidos !== 1 ? 's' : '' ?></span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Estado</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Mensaje</th>
                                <th>Fecha</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mensajes as $msg): ?>
                            <tr class="<?= !$msg['leido'] ? 'table-primary bg-opacity-25' : '' ?>">
                                <td class="ps-4">
                                    <?php if (!$msg['leido']): ?>
                                        <span class="badge bg-danger">Nuevo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Leído</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-semibold"><?= htmlspecialchars($msg['nombre']) ?></td>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" class="text-primary text-decoration-none">
                                        <?= htmlspecialchars($msg['email']) ?>
                                    </a>
                                </td>
                                <td style="max-width:320px;">
                                    <span class="text-muted" style="white-space:pre-wrap;font-size:.88rem;">
                                        <?= nl2br(htmlspecialchars($msg['mensaje'])) ?>
                                    </span>
                                </td>
                                <td class="text-nowrap text-muted small">
                                    <?= date('d/m/Y H:i', strtotime($msg['fecha_envio'])) ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!$msg['leido']): ?>
                                        <a href="<?= BASE_URL ?>/admin/mensajes-contacto?leer=<?= $msg['id'] ?>"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Marcar como leído">
                                            <i class="bi bi-check2"></i> Leído
                                        </a>
                                    <?php else: ?>
                                        <a href="mailto:<?= htmlspecialchars($msg['email']) ?>"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Responder por correo">
                                            <i class="bi bi-reply"></i> Responder
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/public/assets/dashboard/js/dashboard.js"></script>
</body>
</html>
