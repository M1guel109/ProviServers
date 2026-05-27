<?php
require_once BASE_PATH . '/app/controllers/perfil-controller.php'; // ✅ kebab-case
require_once BASE_PATH . '/app/helpers/lang-helper.php';
require_once BASE_PATH . '/app/helpers/notificaciones-proveedor.php';

$id = $_SESSION['user']['id'] ?? null;

if ($id) {
    $usuarioP = mostrarPerfilProveedor($id);
} else {
    $usuarioP = [
        'nombres' => 'Invitado',
        'rol'     => 'proveedor',
        'foto'    => 'default_user.png',
    ];
}

$fotoPerfil   = !empty($usuarioP['foto']) ? $usuarioP['foto'] : 'default_user.png';
$nombrePerfil = $usuarioP['nombres'] ?? 'Usuario';
$rolPerfil    = ucfirst($usuarioP['rol'] ?? 'Proveedor');

// Idioma actual
$idiomaActual = obtenerIdiomaActual();
$imgBandera   = ($idiomaActual === 'es') ? 'es.png' : 'us.png';
$txtIdioma    = ($idiomaActual === 'es') ? 'Español' : 'English';

$misNotificaciones = obtenerNotificacionesProveedor((int)($_SESSION['user']['id'] ?? 0));
$cantidadNotif     = count($misNotificaciones);
?>

<header class="barra-superior d-flex align-items-center justify-content-between">

    <div class="d-flex align-items-center gap-3">
        <button id="btn-toggle-menu" class="btn-toggle border-0 bg-transparent fs-4 text-primary">
            <i class="bi bi-list"></i>
        </button>

        <div class="buscador d-none d-xl-flex align-items-center bg-light rounded-pill px-3 py-1 border">
            <i class="bi bi-search text-muted me-2"></i>
            <input type="text"
                   class="form-control border-0 bg-transparent shadow-none p-1"
                   placeholder="Buscar servicios, clientes...">
        </div>
    </div>

    <div class="acciones-barra d-flex align-items-center gap-3">

        <!-- NOTIFICACIONES -->
        <div class="nav-item dropdown">
            <a class="nav-link nav-icon position-relative fs-4 text-dark"
               href="#" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                <?php if ($cantidadNotif > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light"
                          style="font-size:0.6rem;">
                        <?= $cantidadNotif ?>
                    </span>
                <?php endif; ?>
            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-0"
                style="width:320px;">

                <li class="dropdown-header p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">Notificaciones</span>
                    <a href="<?= BASE_URL ?>/proveedor/notificaciones" class="badge bg-primary text-decoration-none">Ver todas</a>
                </li>

                <div class="notificaciones-scroll" style="max-height:300px; overflow-y:auto;">
                    <?php if (!empty($misNotificaciones)): ?>
                        <?php foreach ($misNotificaciones as $notif): ?>
                            <?php $estilo = estiloNotificacion($notif['tipo'] ?? 'info'); ?>
                            <li class="notification-item d-flex align-items-start p-3 border-bottom">
                                <div class="me-3 fs-5 <?= $estilo['color'] ?> <?= $estilo['bg'] ?> rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:38px; height:38px;">
                                    <i class="bi <?= $estilo['icon'] ?>"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold fs-6 text-dark"><?= htmlspecialchars($notif['titulo']) ?></h6>
                                    <p class="mb-1 small text-secondary lh-sm"><?= htmlspecialchars($notif['mensaje']) ?></p>
                                    <small class="text-muted" style="font-size:0.75rem;">
                                        <i class="bi bi-clock me-1"></i><?= $notif['hora'] ?>
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

        <!-- SELECTOR DE IDIOMA -->
        <div class="nav-item dropdown d-none d-md-block">
            <a class="nav-link d-flex align-items-center gap-2 text-dark text-decoration-none"
               href="#" data-bs-toggle="dropdown">
                <img src="<?= BASE_URL ?>/public/assets/dashboard/img/<?= $imgBandera ?>"
                     alt="<?= $txtIdioma ?>" height="20" class="rounded-1 border">
                <span class="d-none d-lg-block small fw-bold"><?= $txtIdioma ?></span>
                <i class="bi bi-chevron-down small text-muted"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-1"
                style="min-width:140px;">
                <li>
                    <!-- ✅ CORREGIDO: por el Front Controller -->
                    <a class="dropdown-item d-flex align-items-center rounded-2 py-2 <?= $idiomaActual === 'es' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>/idioma?lang=es">
                        <img src="<?= BASE_URL ?>/public/assets/dashboard/img/es.png"
                             height="16" class="me-2"> Español
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center rounded-2 py-2 <?= $idiomaActual === 'en' ? 'active' : '' ?>"
                       href="<?= BASE_URL ?>/idioma?lang=en">
                        <img src="<?= BASE_URL ?>/public/assets/dashboard/img/us.png"
                             height="16" class="me-2"> English
                    </a>
                </li>
            </ul>
        </div>

        <!-- PLAN (hardcodeado hasta módulo membresía) -->
        <div class="d-none d-xl-flex align-items-center me-2">
            <div class="px-3 py-1 rounded-pill bg-primary bg-opacity-10 border border-primary border-opacity-25">
                <small class="text-primary fw-bold" style="font-size:0.7rem;">
                    <i class="bi bi-gem me-1"></i> PLAN GOLD
                </small>
                <span class="text-muted ms-1" style="font-size:0.65rem;">(15 días)</span>
            </div>
        </div>

        <!-- MENÚ DE USUARIO -->
        <div class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center gap-2 ps-2 text-decoration-none"
               href="#" data-bs-toggle="dropdown">
                <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= htmlspecialchars($fotoPerfil) ?>"
                     alt="Perfil"
                     class="rounded-circle border shadow-sm"
                     width="42" height="42"
                     style="object-fit:cover;">
                <div class="d-none d-md-block text-start lh-1">
                    <span class="d-block fw-bold text-dark" style="font-size:0.9rem;">
                        <?= htmlspecialchars($nombrePerfil) ?>
                    </span>
                    <span class="text-muted" style="font-size:0.7rem;"><?= $rolPerfil ?></span>
                </div>
                <i class="bi bi-chevron-down small text-muted"></i>
            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 pt-0"
                style="min-width:240px; border-radius:12px;">

                <li class="dropdown-header text-center bg-primary bg-opacity-10 py-3 mb-2">
                    <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= htmlspecialchars($fotoPerfil) ?>"
                         alt="Perfil"
                         class="rounded-circle border border-2 border-primary mb-2"
                         width="60" height="60"
                         style="object-fit:cover;">
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
                        <span>Mejorar Plan</span>
                        <span class="badge bg-warning text-dark ms-auto">GOLD</span>
                    </a>
                </li>

                <li><hr class="dropdown-divider my-2"></li>

                <li>
                    <!-- ✅ CORREGIDO: sin ?accion= innecesario -->
                    <a class="dropdown-item d-flex align-items-center py-2 text-danger fw-bold"
                       href="<?= BASE_URL ?>/cerrar-sesion">
                        <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>

    </div>
</header>