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
        <div class="nav-item dropdown">

            <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                <span class="badge bg-danger badge-number">5</span>
            </a><!-- End Notification Icon -->

            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                <li class="dropdown-header">
                    Tienes 5 nuevas notificaciones
                    <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">Ver todas</span></a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>

                <!-- 1. Nueva Solicitud de Servicio (Requiere Revisión) -->
                <li class="notification-item">
                    <i class="bi bi-clipboard-check text-warning"></i>
                    <div>
                        <h4>Servicio Pendiente</h4>
                        <p>El Proveedor 'Plomero 3000' ha enviado un nuevo servicio para aprobación.</p>
                        <p>Hace 1 min.</p>
                    </div>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <!-- 2. Alerta de Pago Fallido (Atención Urgente) -->
                <li class="notification-item">
                    <i class="bi bi-x-circle text-danger"></i>
                    <div>
                        <h4>Transacción Fallida</h4>
                        <p>Falló el pago de membresía (ID: 541) del Proveedor: Construcciones A&M.</p>
                        <p>Hace 15 min.</p>
                    </div>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <!-- 3. Nuevo Registro de Usuario (Información) -->
                <li class="notification-item">
                    <i class="bi bi-person-plus text-primary"></i>
                    <div>
                        <h4>Nuevo Registro</h4>
                        <p>Un nuevo cliente 'Juana Pérez' se ha registrado en la plataforma.</p>
                        <p>Hace 1 hora</p>
                    </div>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <!-- 4. Nuevo Ticket de Soporte (Interacción) -->
                <li class="notification-item">
                    <i class="bi bi-chat-dots text-info"></i>
                    <div>
                        <h4>Nuevo Ticket de Soporte</h4>
                        <p>El Cliente 'Roberto G.' abrió un ticket sobre facturación (ID: #405).</p>
                        <p>Hace 3 horas</p>
                    </div>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <!-- 5. Membresía a punto de Expirar (Acción preventiva) -->
                <li class="notification-item">
                    <i class="bi bi-credit-card-2-back text-secondary"></i>
                    <div>
                        <h4>Membresía Próxima a Vencer</h4>
                        <p>La membresía Pro de 365 días del Proveedor (ID: 10) vence en 3 días.</p>
                        <p>Ayer</p>
                    </div>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>
                <li class="dropdown-footer">
                    <a href="#">Mostrar todas las notificaciones</a>
                </li>

            </ul><!-- End Notification Dropdown Items -->

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