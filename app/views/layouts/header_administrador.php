<?php
// 1. Carga de dependencias y datos
require_once BASE_PATH . '/app/controllers/perfilController.php';
require_once BASE_PATH . '/app/helpers/admin_notificaciones.php';

// Validar sesión para evitar errores
$id_usuario = $_SESSION['user']['id'] ?? 0;

// Obtener datos del perfil
$usuarioP = mostrarPerfilAdmin($id_usuario);
// Fallback por si la imagen viene vacía
$fotoPerfil = !empty($usuarioP['foto']) ? $usuarioP['foto'] : 'default_user.png';
$nombrePerfil = $usuarioP['nombres'] ?? 'Usuario';
$rolPerfil = ($usuarioP['rol'] === 'admin' || $usuarioP['rol'] === 'superadmin') ? 'Administrador' : ucfirst($usuarioP['rol'] ?? 'Staff');

// Obtener notificaciones
$misNotificaciones = obtenerNotificacionesAdmin(); // Asumimos que esto devuelve un array
$cantidadNotif = count($misNotificaciones);

/**
 * Helper rápido para definir estilos según el tipo de notificación
 * Tipos esperados: 'pago', 'alerta', 'nuevo_usuario', 'info'
 */
function obtenerEstiloNotificacion($tipo) {
    switch ($tipo) {
        case 'pago':
            return ['icon' => 'bi-cash-coin', 'color' => 'text-success', 'bg' => 'bg-success-light'];
        case 'alerta':
            return ['icon' => 'bi-exclamation-triangle', 'color' => 'text-warning', 'bg' => 'bg-warning-light'];
        case 'error':
            return ['icon' => 'bi-x-circle', 'color' => 'text-danger', 'bg' => 'bg-danger-light'];
        case 'nuevo_usuario':
            return ['icon' => 'bi-person-plus', 'color' => 'text-primary', 'bg' => 'bg-primary-light'];
        default: // 'info'
            return ['icon' => 'bi-info-circle', 'color' => 'text-info', 'bg' => 'bg-info-light'];
    }
}
?>

<header class="barra-superior d-flex align-items-center justify-content-between">
    
    <div class="d-flex align-items-center gap-3">
        <button id="btn-toggle-menu" class="btn-toggle border-0 bg-transparent fs-4 text-primary">
            <i class="bi bi-list"></i>
        </button>

        <div class="buscador d-none d-md-flex align-items-center bg-light rounded-pill px-3 py-1 border">
            <i class="bi bi-search text-muted me-2"></i>
            <input type="text" class="form-control border-0 bg-transparent shadow-none p-1" placeholder="Buscar...">
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

            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications p-0 shadow border-0" style="width: 320px;">
                <li class="dropdown-header p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">Notificaciones</span>
                    <a href="<?= BASE_URL ?>/admin/notificaciones" class="badge bg-primary text-decoration-none">Ver todas</a>
                </li>

                <div class="notificaciones-scroll" style="max-height: 300px; overflow-y: auto;">
                    <?php if ($cantidadNotif > 0): ?>
                        <?php foreach ($misNotificaciones as $notif): ?>
                            <?php 
                                // Definimos el estilo dinámicamente
                                $tipo = $notif['tipo'] ?? 'info'; // Asegúrate que tu DB traiga este campo
                                $estilo = obtenerEstiloNotificacion($tipo); 
                            ?>
                            <li class="notification-item d-flex align-items-start p-3 border-bottom hover-bg">
                                <div class="me-3 fs-4 <?= $estilo['color'] ?>">
                                    <i class="bi <?= $estilo['icon'] ?>"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold fs-6 text-dark"><?= htmlspecialchars($notif['titulo']) ?></h6>
                                    <p class="mb-1 small text-secondary lh-sm"><?= htmlspecialchars($notif['mensaje']) ?></p>
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        <i class="bi bi-clock me-1"></i><?= $notif['hora'] ?>
                                    </small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="p-4 text-center text-muted">
                            <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
                            No tienes notificaciones nuevas
                        </li>
                    <?php endif; ?>
                </div>

                <li class="dropdown-footer text-center p-2 bg-light border-top">
                    <a href="<?= BASE_URL ?>/admin/notificaciones" class="small text-primary text-decoration-none fw-bold">Ver historial completo</a>
                </li>
            </ul>
        </div>

        <div class="nav-item dropdown d-none d-sm-block">
            <a class="nav-link d-flex align-items-center gap-2 text-dark text-decoration-none" href="#" data-bs-toggle="dropdown">
                <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/bandera-idioma.png" alt="ES" height="20">
            </a>
            </div>

        <div class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center gap-2 ps-2" href="#" data-bs-toggle="dropdown">
                <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $fotoPerfil ?>" alt="Perfil" class="rounded-circle border" width="38" height="38" style="object-fit: cover;">
                <div class="d-none d-md-block text-start lh-1">
                    <span class="d-block fw-bold text-dark" style="font-size: 0.9rem;"><?= htmlspecialchars($nombrePerfil) ?></span>
                    <span class="text-muted" style="font-size: 0.75rem;"><?= $rolPerfil ?></span>
                </div>
                <i class="bi bi-chevron-down small text-muted"></i>
            </a>

            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile shadow border-0 pt-0">
                <li class="dropdown-header text-center bg-primary bg-opacity-10 py-3">
                    <h6 class="mb-0 text-primary fw-bold"><?= htmlspecialchars($nombrePerfil) ?></h6>
                    <small class="text-muted"><?= $rolPerfil ?></small>
                </li>

                <li><a class="dropdown-item d-flex align-items-center py-2" href="<?= BASE_URL ?>/admin/perfil">
                    <i class="bi bi-person me-2 text-secondary"></i> Mi Perfil
                </a></li>
                
                <li><a class="dropdown-item d-flex align-items-center py-2" href="<?= BASE_URL ?>/admin/configuracion">
                    <i class="bi bi-gear me-2 text-secondary"></i> Configuración
                </a></li>
                
                <li><hr class="dropdown-divider m-0"></li>
                
                <li><a class="dropdown-item d-flex align-items-center py-2 text-danger fw-bold" href="<?= BASE_URL ?>/cerrar-sesion">
                    <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                </a></li>
            </ul>
        </div>

    </div>
</header>