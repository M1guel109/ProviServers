<?php
require_once BASE_PATH . '/app/controllers/perfil-controller.php';
require_once BASE_PATH . '/app/helpers/lang-helper.php';
require_once BASE_PATH . '/app/helpers/notificaciones-proveedor.php';
require_once BASE_PATH . '/app/helpers/plan-helper.php';
require_once BASE_PATH . '/app/models/Notificacion.php';

$id = $_SESSION['user']['id'] ?? null;

if ($id) {
    $usuarioP = mostrarPerfilProveedor($id);
} else {
    $usuarioP = ['nombres' => 'Invitado', 'rol' => 'proveedor', 'foto' => 'default_user.png'];
}

$fotoPerfil   = !empty($usuarioP['foto']) ? $usuarioP['foto'] : 'default_user.png';
$nombrePerfil = $usuarioP['nombres'] ?? 'Usuario';
$rolPerfil    = ucfirst($usuarioP['rol'] ?? 'Proveedor');

$langActual = $_SESSION['lang'] ?? 'es';

$misNotificaciones = $id ? Notificacion::listar((int)$id, true, 5) : [];
$cantidadNotif     = $id ? Notificacion::contarNoLeidas((int)$id) : 0;

$planActivo    = $id ? obtenerPlanActivoProveedor((int)$id) : null;
$planNombre    = $planActivo['nombre']         ?? 'Básico';
$diasRestantes = $planActivo['dias_restantes'] ?? null;
$esGratuito    = $planActivo['plan_gratuito']  ?? true;

if ($diasRestantes !== null && $diasRestantes <= 2) {
    $pillClass = 'bg-danger bg-opacity-10 border border-danger border-opacity-25';
    $textClass = 'text-danger';
} elseif ($diasRestantes !== null && $diasRestantes <= 7) {
    $pillClass = 'bg-warning bg-opacity-10 border border-warning border-opacity-25';
    $textClass = 'text-warning';
} elseif ($esGratuito) {
    $pillClass = 'bg-success bg-opacity-10 border border-success border-opacity-25';
    $textClass = 'text-success';
} else {
    $pillClass = 'bg-primary bg-opacity-10 border border-primary border-opacity-25';
    $textClass = 'text-primary';
}

$planIcon   = $esGratuito ? 'bi-gift' : 'bi-gem';
$badgeClass = ($diasRestantes !== null && $diasRestantes <= 7) ? 'bg-danger' : ($esGratuito ? 'bg-success' : 'bg-warning');
$badgeLabel = strtoupper(explode(' ', $planNombre)[0]);
?>

<header class="barra-superior">

    <!-- Lado izquierdo: hamburger + buscador -->
    <div class="hdr-left">
        <button id="btn-toggle-menu" class="hdr-btn-icon" aria-label="Menú">
            <i class="bi bi-list"></i>
        </button>

        <div class="buscador d-none d-xl-flex align-items-center bg-light rounded-pill px-3 py-1 border">
            <i class="bi bi-search text-muted me-2"></i>
            <input type="text" class="form-control border-0 bg-transparent shadow-none p-1"
                   placeholder="Buscar servicios, clientes...">
        </div>
    </div>

    <!-- Lado derecho -->
    <div class="hdr-right">

        <!-- Plan activo — solo desktop -->
        <div class="d-none d-xl-flex align-items-center">
            <a href="<?= BASE_URL ?>/proveedor/membresia" class="text-decoration-none">
                <div class="px-3 py-1 rounded-pill <?= $pillClass ?>">
                    <small class="<?= $textClass ?> fw-bold" style="font-size:0.7rem;">
                        <i class="bi <?= $planIcon ?> me-1"></i>
                        <?= htmlspecialchars(strtoupper($planNombre)) ?>
                    </small>
                    <?php if ($diasRestantes !== null): ?>
                        <span class="text-muted ms-1" style="font-size:0.65rem;">(<?= $diasRestantes ?> días)</span>
                    <?php endif; ?>
                </div>
            </a>
        </div>

        <!-- Toggle idioma ES / EN -->
        <a href="<?= BASE_URL ?>/idioma?lang=<?= $langActual === 'es' ? 'en' : 'es' ?>"
           class="hdr-btn-lang"
           title="<?= $langActual === 'es' ? 'Switch to English' : 'Cambiar a Español' ?>">
            <?= strtoupper($langActual === 'es' ? 'EN' : 'ES') ?>
        </a>

        <!-- Notificaciones -->
        <div class="dropdown">
            <button type="button" class="hdr-btn-icon position-relative"
                    data-bs-toggle="dropdown"
                    data-bs-strategy="fixed"
                    aria-label="Notificaciones">
                <i class="bi bi-bell"></i>
                <?php if ($cantidadNotif > 0): ?>
                    <span class="hdr-badge"><?= $cantidadNotif ?></span>
                <?php endif; ?>
            </button>

            <ul class="dropdown-menu dropdown-menu-end p-0 shadow border-0" style="width:320px;">
                <li class="dropdown-header p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">Notificaciones</span>
                    <a href="<?= BASE_URL ?>/proveedor/notificaciones" class="badge bg-primary text-decoration-none">Ver todas</a>
                </li>

                <div class="notificaciones-scroll" style="max-height:300px;overflow-y:auto;">
                    <?php if (!empty($misNotificaciones)): ?>
                        <?php foreach ($misNotificaciones as $n): ?>
                            <?php $bgCls = str_replace('text-', 'bg-', $n['color']) . '-subtle'; ?>
                            <li class="notification-item d-flex align-items-start p-3 border-bottom">
                                <div class="me-3 fs-5 <?= $n['color'] ?> <?= $bgCls ?> rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:38px;height:38px;">
                                    <i class="bi <?= htmlspecialchars($n['icono']) ?>"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold fs-6 text-dark"><?= htmlspecialchars($n['titulo']) ?></h6>
                                    <p class="mb-1 small text-secondary lh-sm"><?= htmlspecialchars($n['mensaje']) ?></p>
                                    <small class="text-muted" style="font-size:0.75rem;">
                                        <i class="bi bi-clock me-1"></i><?= calcularTiempoNotif($n['created_at']) ?>
                                    </small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="p-4 text-center text-muted">
                            <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
                            Sin notificaciones nuevas
                        </li>
                    <?php endif; ?>
                </div>

                <li class="text-center p-2 bg-light border-top">
                    <a href="<?= BASE_URL ?>/proveedor/notificaciones" class="small text-primary text-decoration-none fw-bold">
                        Ver historial completo <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Usuario -->
        <div class="dropdown">
            <button type="button" class="hdr-btn-user"
                    data-bs-toggle="dropdown"
                    data-bs-strategy="fixed">
                <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= htmlspecialchars($fotoPerfil) ?>"
                     alt="Perfil" class="rounded-circle border shadow-sm"
                     width="34" height="34"
                     style="object-fit:cover;border-color:var(--primary-color)!important;">
                <div class="d-none d-md-block text-start lh-1 ms-1">
                    <span class="d-block fw-bold text-dark" style="font-size:0.88rem;"><?= htmlspecialchars($nombrePerfil) ?></span>
                    <span class="text-muted" style="font-size:0.7rem;"><?= $rolPerfil ?></span>
                </div>
                <i class="bi bi-chevron-down d-none d-md-inline small text-muted ms-1"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 pt-0"
                style="min-width:240px;border-radius:12px;">

                <li class="dropdown-header text-center bg-primary bg-opacity-10 py-3 mb-2">
                    <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= htmlspecialchars($fotoPerfil) ?>"
                         alt="Perfil" class="rounded-circle border border-2 border-primary mb-2"
                         width="60" height="60" style="object-fit:cover;">
                    <h6 class="mb-0 text-primary fw-bold"><?= htmlspecialchars($nombrePerfil) ?></h6>
                    <small class="text-muted"><?= $rolPerfil ?></small>
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center py-2"
                       href="<?= BASE_URL ?>/proveedor/configuracion">
                        <i class="bi bi-person-circle me-2 text-primary"></i> Mi Perfil
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center py-2"
                       href="<?= BASE_URL ?>/proveedor/membresia">
                        <i class="bi bi-arrow-up-circle me-2 text-success"></i>
                        Mejorar Plan
                        <span class="badge <?= $badgeClass ?> text-dark ms-auto"><?= htmlspecialchars($badgeLabel) ?></span>
                    </a>
                </li>
                <li><hr class="dropdown-divider my-2"></li>
                <li>
                    <a class="dropdown-item d-flex align-items-center py-2 text-danger fw-bold"
                       href="<?= BASE_URL ?>/cerrar-sesion">
                        <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>

    </div>
</header>
