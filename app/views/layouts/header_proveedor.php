<?php
// Aquí ya asumimos que session_proveedor.php se ejecutó antes en la vista
require_once BASE_PATH . '/app/controllers/perfilController.php';

$id = $_SESSION['user']['id'] ?? null;

if ($id) {
    $usuarioP = mostrarPerfilProveedor($id);
} else {
    $usuarioP = [
        'nombres' => 'Invitado',
        'rol'     => 'Proveedor',
        'foto'    => 'default_user.png',
    ];
}

$fotoPerfil = !empty($usuarioP['foto']) ? $usuarioP['foto'] : 'default_user.png';
$nombrePerfil = $usuarioP['nombres'] ?? 'Usuario';
$rolPerfil = ucfirst($usuarioP['rol'] ?? 'Proveedor');

// Notificaciones (Simuladas por ahora para mantener el diseño)
$cantidadNotif = 3; 
?>

<header class="barra-superior d-flex align-items-center justify-content-between">
    
    <div class="d-flex align-items-center gap-3">
        <button id="btn-toggle-menu" class="btn-toggle border-0 bg-transparent fs-4 text-primary">
            <i class="bi bi-list"></i>
        </button>

        <div class="buscador d-none d-md-flex align-items-center bg-light rounded-pill px-3 py-1 border">
            <i class="bi bi-search text-muted me-2"></i>
            <input type="text" class="form-control border-0 bg-transparent shadow-none p-1" placeholder="Buscar servicios...">
        </div>
    </div>

    <div class="acciones-barra d-flex align-items-center gap-3">

        <div class="nav-item dropdown">
            <a class="nav-link nav-icon position-relative fs-4 text-dark" href="#" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                <?php if ($cantidadNotif > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.6rem;">
                        <?= $cantidadNotif ?>
                    </span>
                <?php endif; ?>
            </a>
            </div>

        <div class="nav-item dropdown d-none d-sm-block">
            <a class="nav-link d-flex align-items-center gap-2 text-dark text-decoration-none" href="#" data-bs-toggle="dropdown">
                <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/es.png" alt="Español" height="20" class="rounded-1 border">
                <span class="d-none d-lg-block small fw-bold">Español</span>
                <i class="bi bi-chevron-down small text-muted"></i>
            </a>
        </div>

        <div class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center gap-2 ps-2" href="#" data-bs-toggle="dropdown">
                <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= htmlspecialchars($fotoPerfil) ?>" alt="Perfil" class="rounded-circle border shadow-sm" width="38" height="38" style="object-fit: cover;">
                <div class="d-none d-md-block text-start lh-1">
                    <span class="d-block fw-bold text-dark" style="font-size: 0.9rem;"><?= htmlspecialchars($nombrePerfil) ?></span>
                    <span class="text-muted" style="font-size: 0.75rem;"><?= $rolPerfil ?></span>
                </div>
                <i class="bi bi-chevron-down small text-muted"></i>
            </a>

            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile shadow border-0 pt-0" style="min-width: 200px;">
                <li class="dropdown-header text-center bg-primary bg-opacity-10 py-3 mb-2">
                    <h6 class="mb-0 text-primary fw-bold"><?= htmlspecialchars($nombrePerfil) ?></h6>
                    <small class="text-muted"><?= $rolPerfil ?></small>
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center py-2" href="#">
                        <i class="bi bi-person me-2 text-secondary"></i> Mi Perfil
                    </a>
                </li>
                
                <li>
                    <a class="dropdown-item d-flex align-items-center py-2" href="<?= BASE_URL ?>/proveedor/configuracion">
                        <i class="bi bi-gear me-2 text-secondary"></i> Configuración
                    </a>
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center py-2" href="#">
                        <i class="bi bi-question-circle me-2 text-secondary"></i> Ayuda
                    </a>
                </li>
                
                <li><hr class="dropdown-divider my-2"></li>
                
                <li>
                    <a class="dropdown-item d-flex align-items-center py-2 text-danger fw-bold" href="<?= BASE_URL ?>/proveedor/logout">
                        <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>

    </div>
</header>