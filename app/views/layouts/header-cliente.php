<?php
// Aquí asumimos que session-cliente.php se ejecutó antes en la vista
require_once BASE_PATH . '/app/controllers/perfil-controller.php';

$id = $_SESSION['user']['id'] ?? null;

// Obtener datos del perfil
if ($id) {
    $usuarioC = mostrarPerfilCliente($id);
} else {
    $usuarioC = [
        'nombres' => 'Invitado',
        'rol'     => 'Cliente',
        'foto'    => 'default_user.png',
    ];
}

$fotoPerfil = !empty($usuarioC['foto']) ? $usuarioC['foto'] : 'default_user.png';
$nombrePerfil = $usuarioC['nombres'] ?? 'Usuario';
$rolPerfil = ucfirst($usuarioC['rol'] ?? 'Cliente');

// Notificaciones (simuladas por ahora)
$cantidadNotif = 2;
?>

<header class="barra-superior d-flex align-items-center justify-content-between">
    
    <div class="d-flex align-items-center gap-3">
        <!-- Botón toggle sidebar -->
        <button id="btn-toggle-menu" class="btn-toggle border-0 bg-transparent fs-4 text-primary">
            <i class="bi bi-list"></i>
        </button>

        <!-- Buscador -->
        <div class="buscador d-none d-md-flex align-items-center bg-light rounded-pill px-3 py-1 border">
            <i class="bi bi-search text-muted me-2"></i>
            <input type="text" class="form-control border-0 bg-transparent shadow-none p-1" 
                   placeholder="Buscar servicios...">
        </div>
    </div>

    <div class="acciones-barra d-flex align-items-center gap-3">

        <!-- Notificaciones -->
        <div class="nav-item dropdown">
            <a class="nav-link nav-icon position-relative fs-4 text-dark" href="#" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                <?php if ($cantidadNotif > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" 
                          style="font-size: 0.6rem;">
                        <?= $cantidadNotif ?>
                    </span>
                <?php endif; ?>
            </a>

            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications p-0 shadow border-0" style="width: 320px;">
                <li class="dropdown-header p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">Notificaciones</span>
                    <a href="<?= BASE_URL ?>/cliente/notificaciones" class="badge bg-primary text-decoration-none">Ver todas</a>
                </li>

                <div class="notificaciones-scroll" style="max-height: 300px; overflow-y: auto;">
                    <li class="notification-item d-flex align-items-start p-3 border-bottom hover-bg">
                        <div class="me-3 fs-4 text-primary bg-primary-light rounded-circle d-flex align-items-center justify-content-center" 
                            style="width:40px; height:40px;">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold fs-6 text-dark">Servicio completado</h6>
                            <p class="mb-1 small text-secondary lh-sm">Tu servicio de jardinería ha sido completado</p>
                            <small class="text-muted" style="font-size: 0.75rem;">
                                <i class="bi bi-clock me-1"></i>Hace 2 horas
                            </small>
                        </div>
                    </li>
                    <li class="notification-item d-flex align-items-start p-3 border-bottom hover-bg">
                        <div class="me-3 fs-4 text-warning bg-warning-light rounded-circle d-flex align-items-center justify-content-center" 
                            style="width:40px; height:40px;">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold fs-6 text-dark">Cita confirmada</h6>
                            <p class="mb-1 small text-secondary lh-sm">Tu cita de plomería ha sido confirmada</p>
                            <small class="text-muted" style="font-size: 0.75rem;">
                                <i class="bi bi-clock me-1"></i>Hace 1 día
                            </small>
                        </div>
                    </li>
                </div>

                <li class="dropdown-footer text-center p-2 bg-light border-top">
                    <a href="<?= BASE_URL ?>/cliente/notificaciones" class="small text-primary text-decoration-none fw-bold">
                        Ver historial completo <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Menú de usuario -->
        <div class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center gap-2 ps-2" href="#" data-bs-toggle="dropdown">
                <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= htmlspecialchars($fotoPerfil) ?>" 
                     alt="Perfil" class="rounded-circle border shadow-sm" width="42" height="42" 
                     style="object-fit: cover; border-color: var(--primary-color) !important;">
                <div class="d-none d-md-block text-start lh-1">
                    <span class="d-block fw-bold text-dark" style="font-size: 0.9rem;"><?= htmlspecialchars($nombrePerfil) ?></span>
                    <span class="text-muted" style="font-size: 0.7rem;"><?= $rolPerfil ?></span>
                </div>
                <i class="bi bi-chevron-down small text-muted"></i>
            </a>

            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile shadow border-0 pt-0" 
                style="min-width: 240px; border-radius: 12px;">
                
                <li class="dropdown-header text-center bg-primary bg-opacity-10 py-3 mb-2">
                    <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= htmlspecialchars($fotoPerfil) ?>" 
                         alt="Perfil" class="rounded-circle border border-2 border-primary mb-2" 
                         width="60" height="60" style="object-fit: cover;">
                    <h6 class="mb-0 text-primary fw-bold"><?= htmlspecialchars($nombrePerfil) ?></h6>
                    <small class="text-muted"><?= $rolPerfil ?></small>
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center py-2" href="<?= BASE_URL ?>/cliente/perfil">
                        <i class="bi bi-person-circle me-2 text-primary"></i> Mi Perfil
                    </a>
                </li>
                
                <li>
                    <a class="dropdown-item d-flex align-items-center py-2" href="<?= BASE_URL ?>/cliente/favoritos">
                        <i class="bi bi-heart me-2 text-danger"></i> Mis Favoritos
                    </a>
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center py-2" href="<?= BASE_URL ?>/cliente/configuracion">
                        <i class="bi bi-gear me-2 text-secondary"></i> Configuración
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider my-2">
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center py-2 text-danger fw-bold" 
                       href="<?= BASE_URL ?>/cerrar-sesion?accion=cerrar_sesion">
                        <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>

    </div>
</header>