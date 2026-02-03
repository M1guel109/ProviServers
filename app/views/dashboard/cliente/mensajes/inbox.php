<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Mensajes</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <!-- Estilos específicos de cliente -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardCliente.css">
</head>
<body>
<?php
// Marca sidebar
$currentPage = 'mensajes';
include_once __DIR__ . '/../../layouts/sidebar_cliente.php';

function tiempoRelativo(?string $fechaHora): string {
    if (!$fechaHora) return '';
    $ts = strtotime($fechaHora);
    if ($ts === false) return $fechaHora;

    $diff = time() - $ts;
    if ($diff < 60) return 'Hace un momento';
    $mins = (int) floor($diff / 60);
    if ($mins < 60) return "Hace {$mins} min";
    $hrs = (int) floor($mins / 60);
    if ($hrs < 24) return "Hace {$hrs} h";
    $days = (int) floor($hrs / 24);
    if ($days === 1) return "Ayer";
    if ($days < 7) return "Hace {$days} días";
    return date('Y-m-d H:i', $ts);
}
?>

<main class="contenido">
    <?php include_once __DIR__ . '/../../layouts/header_cliente.php'; ?>

    <section id="mensajes">
        <div class="container">
            <div class="section-hero mb-4">
                <p class="breadcrumb">Inicio &gt; Mensajes</p>
                <h1><i class="bi text-primary"></i>Mensajes</h1>
                <p>Comunícate directamente con tus proveedores. Revisa tus conversaciones y responde cuando lo necesites.</p>
            </div>

            <div class="messages-list">
                <?php if (empty($convs)): ?>
                    <div class="alert alert-info mb-4">
                        Aún no tienes conversaciones.
                    </div>
                <?php else: ?>
                    <?php foreach ($convs as $c): ?>
                        <?php
                            $nombre = trim(($c['otro_nombres'] ?? '') . ' ' . ($c['otro_apellidos'] ?? ''));
                            $rolOtro = $c['otro_rol'] ?? '';
                            $titulo = ($rolOtro === 'proveedor' ? 'Prov. ' : '') . ($nombre !== '' ? $nombre : 'Usuario');
                            $preview = $c['ultimo_contenido'] ?? '';
                            $preview = $preview !== '' ? mb_strimwidth($preview, 0, 120, '...') : 'Sin mensajes aún.';
                            $cuando = tiempoRelativo($c['ultimo_fecha'] ?? null);
                            $noLeidos = (int)($c['no_leidos'] ?? 0);
                        ?>

                        <div class="card message-item mb-4 shadow-sm border-0 rounded-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <h5 class="fw-semibold mb-0"><?= htmlspecialchars($titulo) ?></h5>
                                        <?php if ($noLeidos > 0): ?>
                                            <span class="badge bg-danger rounded-pill"><?= $noLeidos ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?= htmlspecialchars($cuando) ?></small>
                                </div>

                                <p class="text-muted mb-2"><?= htmlspecialchars($preview) ?></p>

                                <div class="d-flex gap-2">
                                    <a class="btn btn-outline-primary btn-sm"
                                       href="<?= BASE_URL ?>/mensajes/ver?id=<?= (int)$c['id'] ?>">
                                        Responder
                                    </a>

                                    <!-- Si luego quieres "Ver perfil", aquí lo conectamos a tu ruta real -->
                                    <!-- <a class="btn btn-outline-secondary btn-sm" href="...">Ver perfil</a> -->
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboardCliente.js"></script>
</body>
</html>
