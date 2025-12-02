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
?>

<header class="barra-superior">
    <!-- Botón para plegar el menú -->
    <button id="btn-toggle-menu" class="btn-toggle">
        <i class="bi bi-list"></i>
    </button>

    <!--Barra Superior -->
    <div class="buscador">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Buscar servicios...">
    </div>

    <div class="acciones-barra">
        <div class="notificaciones item-barra">
            <i class="bi bi-bell-fill"></i>
            <span class="badge">3</span>
        </div>

        <div class="idioma item-barra">
            <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/bandera-idioma.png" alt="Foto bandera">
            <span>Español</span>
            <i class="bi bi-chevron-down"></i>
        </div>

        <div class="nav-item dropdown pe-3">
            <a class="usuario item-barra d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= htmlspecialchars($usuarioP['foto']) ?>"
                    alt="Foto usuario" class="rounded-circle">

                <div class="info-usuario d-none d-md-block ps-2">
                    <span class="nombre"><?= htmlspecialchars($usuarioP['nombres']) ?></span>
                    <span class="rol"><?= htmlspecialchars($usuarioP['rol'] ?? 'Proveedor') ?></span>
                </div>

                <i class="bi bi-chevron-down ms-2"></i>
            </a>

            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                <li class="dropdown-header">
                    <h6><?= htmlspecialchars($usuarioP['nombres']) ?></h6>
                    <span><?= htmlspecialchars($usuarioP['rol'] ?? 'Proveedor') ?></span>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center" href="#">
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
                    <a class="dropdown-item d-flex align-items-center" href="#">
                        <i class="bi bi-question-circle"></i>
                        <span>Ayuda</span>
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center" href="<?= BASE_URL ?>/logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Salir</span>
                    </a>
                </li>

            </ul>
        </div>

    </div>
</header>