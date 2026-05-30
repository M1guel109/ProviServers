<?php
function tiempoRelativoInboxCli(?string $fh): string {
    if (!$fh) return '';
    $ts = strtotime($fh);
    if ($ts === false) return $fh;
    $diff = time() - $ts;
    if ($diff < 60)   return 'Ahora';
    $m = (int)($diff / 60);
    if ($m < 60)      return "Hace {$m} min";
    $h = (int)($m / 60);
    if ($h < 24)      return "Hace {$h} h";
    $d = (int)($h / 24);
    if ($d === 1)     return 'Ayer';
    if ($d < 7)       return "Hace {$d} días";
    return date('d/m/Y', $ts);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Mensajes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-cliente.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/mensajes.css">
</head>
<body>
<?php
$currentPage = 'mensajes';
include_once __DIR__ . '/../../../layouts/sidebar-cliente.php';
?>
<main class="contenido">
    <?php include_once __DIR__ . '/../../../layouts/header-cliente.php'; ?>

    <div class="container-fluid px-4 py-3">

        <div id="titulo-principal" class="section-hero mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-1"><i class="bi bi-chat-dots me-2 text-primary"></i>Mensajes</h1>
                    <p class="text-muted mb-0">Comunícate con tus proveedores. Todos los tratos deben cerrarse dentro de la plataforma.</p>
                </div>
                <div class="col-md-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/cliente/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Mensajes</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="conv-list">
            <?php if (empty($convs)): ?>
                <div class="inbox-empty">
                    <i class="bi bi-chat-square-text"></i>
                    <p class="mb-1 fw-semibold">No tienes conversaciones aún</p>
                    <small>Aparecerán aquí una vez que solicites o contratas un servicio.</small>
                </div>
            <?php else: ?>
                <?php foreach ($convs as $c): ?>
                    <?php
                        $nombre   = trim(($c['otro_nombres'] ?? '') . ' ' . ($c['otro_apellidos'] ?? ''));
                        $rolOtro  = $c['otro_rol'] ?? '';
                        $nombre   = ($rolOtro === 'proveedor' ? 'Prov. ' : '') . ($nombre !== '' ? $nombre : 'Proveedor');
                        $tema     = $c['tema'] ?? 'Conversación';
                        $preview  = $c['ultimo_contenido'] ?? '';
                        $preview  = $preview !== '' ? mb_strimwidth($preview, 0, 110, '…') : 'Sin mensajes aún.';
                        $cuando   = tiempoRelativoInboxCli($c['ultimo_fecha'] ?? null);
                        $noLeidos = (int)($c['no_leidos'] ?? 0);
                    ?>
                    <a href="<?= BASE_URL ?>/mensajes/ver?id=<?= (int)$c['id'] ?>"
                       class="conv-item <?= $noLeidos > 0 ? 'no-leidos' : '' ?>">

                        <div class="conv-avatar">
                            <i class="bi bi-person-workspace"></i>
                        </div>

                        <div class="conv-body">
                            <div class="conv-nombre">
                                <?= htmlspecialchars($nombre) ?>
                                <?php if ($noLeidos > 0): ?>
                                    <span class="badge-nl"><?= $noLeidos ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="conv-tema"><?= htmlspecialchars($tema) ?></div>
                            <div class="conv-preview"><?= htmlspecialchars($preview) ?></div>
                        </div>

                        <div class="conv-meta">
                            <span class="conv-tiempo"><?= htmlspecialchars($cuando) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
<script src="<?= BASE_URL ?>/public/assets/dashboard/js/dashboard-cliente.js"></script>
</body>
</html>
