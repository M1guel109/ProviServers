<?php
require_once BASE_PATH . '/app/helpers/session-proveedor.php';
require_once BASE_PATH . '/app/models/Notificacion.php';

$uid     = (int)$_SESSION['user']['id'];
$request ??= ''; // definido por index.php; fallback para el linter

// ── AJAX: marcar una como leída ──────────────────────────────────────
if ($request === '/proveedor/notificaciones/marcar-leida' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_clean();
    header('Content-Type: application/json');
    $id = (int)($_POST['id'] ?? 0);
    echo json_encode(['ok' => $id > 0 && Notificacion::marcarLeida($id, $uid)]);
    exit;
}

// ── AJAX: marcar todas como leídas ──────────────────────────────────
if ($request === '/proveedor/notificaciones/marcar-todas' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['ok' => Notificacion::marcarTodasLeidas($uid)]);
    exit;
}

// ── Datos para la vista ──────────────────────────────────────────────
$filtro         = $_GET['filtro'] ?? 'todas';
$soloNoLeidas   = $filtro === 'no-leidas' ? true : null;
$notificaciones = Notificacion::listar($uid, $soloNoLeidas, 100);
$totalNoLeidas  = Notificacion::contarNoLeidas($uid);

function tiempoAtrasNotifPr(string $fecha): string {
    $diff = time() - strtotime($fecha);
    if ($diff < 60)     return 'Hace un momento';
    if ($diff < 3600)   return 'Hace ' . floor($diff / 60) . ' min';
    if ($diff < 86400)  return 'Hace ' . floor($diff / 3600) . ' h';
    if ($diff < 604800) return 'Hace ' . floor($diff / 86400) . ' días';
    return date('d/m/Y', strtotime($fecha));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Notificaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-proveedor.css">
</head>
<body>
<?php
$currentPage = 'notificaciones';
include_once __DIR__ . '/../../layouts/sidebar-proveedor.php';
?>
<main class="contenido">
    <?php include_once __DIR__ . '/../../layouts/header-proveedor.php'; ?>

    <section id="titulo-principal" class="section-hero mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-1">Notificaciones</h1>
                <p class="text-muted mb-0">
                    <?= $totalNoLeidas > 0
                        ? $totalNoLeidas . ' notificación(es) sin leer'
                        : 'Estás al día con todas tus notificaciones' ?>
                </p>
            </div>
            <div class="col-md-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 justify-content-md-end">
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>/proveedor/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                        </li>
                        <li class="breadcrumb-item active">Notificaciones</li>
                    </ol>
                </nav>
            </div>
        </div>
    </section>

    <div class="container-fluid px-4 pb-5">

        <!-- Filtros + acción masiva -->
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link <?= $filtro === 'todas' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>/proveedor/notificaciones?filtro=todas">Todas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $filtro === 'no-leidas' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>/proveedor/notificaciones?filtro=no-leidas">
                        No leídas
                        <?php if ($totalNoLeidas > 0): ?>
                            <span class="badge bg-danger ms-1"><?= $totalNoLeidas ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
            <?php if ($totalNoLeidas > 0): ?>
                <button class="btn btn-outline-secondary btn-sm" id="btn-marcar-todas">
                    <i class="bi bi-check2-all"></i> Marcar todas como leídas
                </button>
            <?php endif; ?>
        </div>

        <!-- Lista -->
        <?php if (empty($notificaciones)): ?>
            <div class="text-center py-5">
                <i class="bi bi-bell-slash fs-1 text-muted"></i>
                <p class="mt-3 text-muted">
                    <?= $filtro === 'no-leidas' ? 'No tienes notificaciones sin leer.' : 'Aún no tienes notificaciones.' ?>
                </p>
            </div>
        <?php else: ?>
            <div class="list-group shadow-sm" id="lista-notificaciones">
                <?php foreach ($notificaciones as $n): ?>
                    <div class="list-group-item list-group-item-action d-flex gap-3 py-3 notif-item <?= !$n['leida'] ? 'fw-semibold bg-light' : '' ?>"
                         data-id="<?= $n['id'] ?>" data-leida="<?= $n['leida'] ?>">

                        <div class="fs-4 pt-1 flex-shrink-0">
                            <i class="bi <?= htmlspecialchars($n['icono']) ?> <?= htmlspecialchars($n['color']) ?>"></i>
                        </div>

                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <span><?= htmlspecialchars($n['titulo']) ?></span>
                                <small class="text-muted text-nowrap"><?= tiempoAtrasNotifPr($n['created_at']) ?></small>
                            </div>
                            <p class="mb-1 small text-muted fw-normal"><?= htmlspecialchars($n['mensaje']) ?></p>
                            <div class="d-flex gap-2 mt-1">
                                <?php if ($n['url']): ?>
                                    <a href="<?= htmlspecialchars($n['url']) ?>" class="btn btn-sm btn-outline-primary">
                                        Ver detalle <i class="bi bi-arrow-right"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (!$n['leida']): ?>
                                    <button class="btn btn-sm btn-outline-secondary btn-marcar-leida" data-id="<?= $n['id'] ?>">
                                        <i class="bi bi-check2"></i> Marcar como leída
                                    </button>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary align-self-center">
                                        <i class="bi bi-check2-all"></i> Leída
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script>const BASE_URL = "<?= BASE_URL ?>";</script>
<script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {

    async function jsonPost(url, body = null) {
        try {
            const opts = { method: 'POST' };
            if (body) {
                opts.headers = { 'Content-Type': 'application/x-www-form-urlencoded' };
                opts.body = body;
            }
            const res  = await fetch(url, opts);
            const text = await res.text();
            return JSON.parse(text);
        } catch (e) {
            console.error('notif AJAX error:', e);
            return { ok: false };
        }
    }

    document.querySelectorAll('.btn-marcar-leida').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id   = btn.dataset.id;
            const item = btn.closest('.notif-item');
            const data = await jsonPost(BASE_URL + '/proveedor/notificaciones/marcar-leida', 'id=' + id);
            if (data.ok) {
                item.classList.remove('fw-semibold', 'bg-light');
                btn.replaceWith(Object.assign(document.createElement('span'), {
                    className: 'badge bg-secondary-subtle text-secondary align-self-center',
                    innerHTML: '<i class="bi bi-check2-all"></i> Leída',
                }));
                cambiarContador(-1);
            }
        });
    });

    const btnTodas = document.getElementById('btn-marcar-todas');
    if (btnTodas) {
        btnTodas.addEventListener('click', async () => {
            const data = await jsonPost(BASE_URL + '/proveedor/notificaciones/marcar-todas');
            if (data.ok) {
                document.querySelectorAll('.notif-item').forEach(item => {
                    item.classList.remove('fw-semibold', 'bg-light');
                    const b = item.querySelector('.btn-marcar-leida');
                    if (b) b.replaceWith(Object.assign(document.createElement('span'), {
                        className: 'badge bg-secondary-subtle text-secondary align-self-center',
                        innerHTML: '<i class="bi bi-check2-all"></i> Leída',
                    }));
                });
                btnTodas.remove();
                cambiarContador(0, true);
            }
        });
    }

    function cambiarContador(delta, reset = false) {
        const targets = [
            document.querySelector('.nav-pills .badge'),
            document.querySelector('.sidebar a[href*="notificaciones"] .badge'),
            document.querySelector('.hdr-badge'),
        ];
        targets.forEach(b => {
            if (!b) return;
            if (reset) { b.remove(); return; }
            const n = parseInt(b.textContent) + delta;
            n <= 0 ? b.remove() : (b.textContent = n);
        });
    }
});
</script>
</body>
</html>
