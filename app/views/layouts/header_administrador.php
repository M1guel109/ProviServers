<?php
// Enlazamos la dependencia,en este caso el controlador que tiene la funcion de consulatar los datos
require_once BASE_PATH . '/app/controllers/perfilController.php';

// llamamos la funcion especifica que exite en dicho controlador
$id = $_SESSION['user']['id'];

// Llamamos la funcion especifica del controlador y le pasamoas los datos a una variable que podamos manipular en un archivo 
$usuarioP = mostrarPerfilAdmin($id);

// echo "<pre>";
// var_dump($usuario);
// echo "</pre>";
// exit;


?>


<header class="barra-superior">
    <!-- Botón para plegar el menú -->
    <button id="btn-toggle-menu" class="btn-toggle">
        <i class="bi bi-list"></i>
    </button>

    <!--Barra Superior -->
    <div class="buscador">
        <i class="bi bi-search"></i>
        <input type="text" placeholder="Buscar">
    </div>
    <div class="acciones-barra">
        <!-- Notificaiones -->
        <div class="notificaciones item-barra">
            <i class="bi bi-bell-fill"></i>
            <span class="badge">6</span>
        </div>

        <!-- Idioma -->
        <div class="idioma item-barra">
            <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/bandera-idioma.png" alt="Foto bandera">
            <span>English</span>
            <i class="bi bi-chevron-down"></i>
        </div>

        <!-- Perfil -->
        <div class="nav-item dropdown pe-3">

            <a class="usuario item-barra d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $usuarioP['foto'] ?>" alt="Foto usuario" class="rounded-circle">

                <div class="info-usuario d-none d-md-block ps-2">
                    <span class="nombre"><?= $usuarioP['nombres'] ?></span>
                    <span class="rol"><?= $usuarioP['rol'] ?></span>
                </div>

                <i class="bi bi-chevron-down ms-2"></i>
            </a>

            <!-- Dropdown -->
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">

                <li class="dropdown-header">
                    <h6><?= $usuarioP['nombres'] ?></h6>
                    <span><?= $usuarioP['rol'] ?></span>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center" href="<?= BASE_URL ?>/admin/perfil">
                        <i class="bi bi-person"></i>
                        <span>Mi Perfil</span>
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center" href="dashboardPerfil.html">
                        <i class="bi bi-gear"></i>
                        <span>Configuración</span>
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center" href="ayuda.html">
                        <i class="bi bi-question-circle"></i>
                        <span>Ayuda</span>
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <a class="dropdown-item d-flex align-items-center" href="<?= BASE_URL ?>/cerrar-sesion">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Salir</span>
                    </a>
                </li>

            </ul>

        </div>


    </div>
</header>