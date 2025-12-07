<?php
// Aquí asumimos que session_cliente.php se ejecutó antes en la vista

require_once BASE_PATH . '/app/controllers/perfilController.php';

$id = $_SESSION['user']['id'] ?? null;

// Valores por defecto
$usuarioC = [
    'nombres' => 'Invitado',
    'rol'     => 'Cliente',
    'foto'    => 'default_user.png',
];


?>

<header class="barra-superior">
    <!-- Botón para plegar el menú -->
    <button id="btn-toggle-menu" class="btn-toggle">
        <i class="bi bi-list"></i>
    </button>


    <!-- Buscador -->
    <div class="buscador">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Buscar">
    </div>

    <div class="acciones-barra">
        <div class="notificaciones item-barra">
            <i class="bi bi-bell-fill"></i>
            <span class="badge"></span>
        </div>

        <div class="nav-item dropdown pe-3">
            <a class="usuario item-barra d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= htmlspecialchars($usuarioC['foto']) ?>"
                    alt="Foto usuario" class="rounded-circle">

                <div class="info-usuario d-none d-md-block ps-2">
                    <span class="nombre"><?= htmlspecialchars($usuarioC['nombres']) ?></span>
                    <span class="rol"><?= htmlspecialchars($usuarioC['rol'] ?? 'Cliente') ?></span>
                </div>

                <i class="bi bi-chevron-down ms-2"></i>
            </a>

            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                <li class="dropdown-header">
                    <h6><?= htmlspecialchars($usuarioC['nombres']) ?></h6>
                    <span><?= htmlspecialchars($usuarioC['rol'] ?? 'Cliente') ?></span>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center" href="<?= BASE_URL ?>/cliente/perfil">
                        <i class="bi bi-person"></i>
                        <span>Mi Perfil</span>
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center" href="#">
                        <i class="bi bi-gear"></i>
                        <span>Configuración</span>
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center" href="<?= BASE_URL ?>/login">
                        <i class="bi bi-box-arrow-right"></i>
                        <span> -Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </div>

    </div>
</header>