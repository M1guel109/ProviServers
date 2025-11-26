<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
require_once BASE_PATH . '/app/controllers/perfilController.php';

// llamamos la funcion especifica que exite en dicho controlador
$id = $_SESSION['user']['id'];

// Llamamos la funcion especifica del controlador y le pasamoas los datos a una variable que podamos manipular en un archivo 
$usuario = mostrarPerfilAdmin($id);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Plataforma de servicios locales</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- tu css -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardPerfil.css">
</head>

<body>
    <!-- SIDEBAR (lateral izquierdo) -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_administrador.php'
    ?>




    <main class="contenido">
        <!-- AQUI IBA EL HEADER -->
        <?php
        include_once __DIR__ . '/../../layouts/header_administrador.php'
        ?>


        <!--     Secciones -->
        <!-- titulo -->
        <section id="titulo-principal" class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h1 class="mb-1">Ver Perfil</h1>
                <p class="text-muted mb-0">
                    Aquí puedes actualizar tus datos de usuario y tu información personal.
                </p>
            </div>

            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol id="breadcrumb" class="breadcrumb mb-0"></ol>
            </nav>
        </section>


        <!-- Formualior admin -->
        <section class="section profile">
            <div class="row">
                <div class="col-xl-4">

                    <div class="card">
                        <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">

                            <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $usuario['foto'] ?>" alt="Foto de Perfil" class="rounded-circle">

                            <h2><?= $usuario['nombres'] ?></h2>
                            <h3><?= $usuario['rol'] ?></h3>

                            <div class="social-links mt-2">
                                <a href="#" class="twitter"><i class="bi bi-twitter"></i></a>
                                <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
                                <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
                                <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
                            </div>

                        </div>
                    </div>

                </div>


                <div class="col-xl-8">

                    <div class="card">
                        <div class="card-body pt-3">
                            <!-- Bordered Tabs -->
                            <ul class="nav nav-tabs nav-tabs-bordered" role="tablist">

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview" aria-selected="true" role="tab">
                                        Información
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit" aria-selected="false" tabindex="-1" role="tab">
                                        Editar Perfil
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-settings" aria-selected="false" tabindex="-1" role="tab">
                                        Configuración
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password" aria-selected="false" tabindex="-1" role="tab">
                                        Cambiar Contraseña
                                    </button>
                                </li>

                            </ul>


                            <div class="tab-content pt-2">

                                <div class="tab-pane fade show active profile-overview" id="profile-overview" role="tabpanel">
                                    <!-- <h5 class="card-title">Acerca de</h5> -->
                                    <!-- <p class="small fst-italic">
                                        Administradora con más de 5 años de experiencia gestionando plataformas y supervisando equipos de trabajo.
                                        Apasionada por la organización, la tecnología y la mejora continua.
                                    </p> -->

                                    <h5 class="card-title">Detalles del Perfil</h5>

                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Nombre Completo</div>
                                        <div class="col-lg-9 col-md-8"><?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?></div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Empresa</div>
                                        <div class="col-lg-9 col-md-8">Proviservers</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Cargo</div>
                                        <div class="col-lg-9 col-md-8"><?= $usuario['rol'] ?></div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">País</div>
                                        <div class="col-lg-9 col-md-8">Colombia</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Dirección</div>
                                        <div class="col-lg-9 col-md-8"><?= $usuario['ubicacion'] ?></div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Teléfono</div>
                                        <div class="col-lg-9 col-md-8"><?= $usuario['telefono'] ?></div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 label">Correo</div>
                                        <div class="col-lg-9 col-md-8"><?= $usuario['email'] ?></div>
                                    </div>

                                </div>


                                <div class="tab-pane fade profile-edit pt-3" id="profile-edit" role="tabpanel">

                                    <!-- Formulario de Edición de Perfil -->
                                    <form>
                                        <div class="row mb-3">
                                            <label for="profileImage" class="col-md-4 col-lg-3 col-form-label">Foto de Perfil</label>
                                            <div class="col-md-8 col-lg-9">
                                                <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $usuario['foto'] ?>" width="50" height="50" alt="Foto de Perfil">
                                                <div class="pt-2">
                                                    <a href="#" class="btn btn-primary btn-sm" title="Subir nueva foto">
                                                        <i class="bi bi-upload"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-danger btn-sm" title="Eliminar foto">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Nombre Completo</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="fullName" type="text" class="form-control" id="fullName" value="<?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?>">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="about" class="col-md-4 col-lg-3 col-form-label">Descripción</label>
                                            <div class="col-md-8 col-lg-9">
                                                <textarea name="about" class="form-control" id="about" style="height: 100px">Administradora del sistema con 5 años de experiencia gestionando contenido y usuarios en plataformas digitales.</textarea>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="company" class="col-md-4 col-lg-3 col-form-label">Empresa</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="company" type="text" class="form-control" id="company" value="Proviservers">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="Job" class="col-md-4 col-lg-3 col-form-label">Cargo</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="job" type="text" class="form-control" id="Job" value="<?= $usuario['rol'] ?>">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="Country" class="col-md-4 col-lg-3 col-form-label">País</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="country" type="text" class="form-control" id="Country" value="Colombia">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="Address" class="col-md-4 col-lg-3 col-form-label">Dirección</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="address" type="text" class="form-control" id="Address" value="<?= $usuario['ubicacion'] ?>">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="Phone" class="col-md-4 col-lg-3 col-form-label">Teléfono</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="phone" type="text" class="form-control" id="Phone" value="<?= $usuario['telefono'] ?>">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="Email" class="col-md-4 col-lg-3 col-form-label">Correo Electrónico</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="email" type="email" class="form-control" id="Email" value="<?= $usuario['email'] ?>">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="Twitter" class="col-md-4 col-lg-3 col-form-label">Perfil de Twitter</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="twitter" type="text" class="form-control" id="Twitter" value="https://twitter.com/mariagomez">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="Facebook" class="col-md-4 col-lg-3 col-form-label">Perfil de Facebook</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="facebook" type="text" class="form-control" id="Facebook" value="https://facebook.com/maria.gomez">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="Instagram" class="col-md-4 col-lg-3 col-form-label">Perfil de Instagram</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="instagram" type="text" class="form-control" id="Instagram" value="https://instagram.com/maria.gomez">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="Linkedin" class="col-md-4 col-lg-3 col-form-label">Perfil de LinkedIn</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="linkedin" type="text" class="form-control" id="Linkedin" value="https://linkedin.com/in/maria-gomez">
                                            </div>
                                        </div>

                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                        </div>
                                    </form><!-- Fin Formulario Editar Perfil -->

                                </div>


                                <div class="tab-pane fade pt-3" id="profile-settings" role="tabpanel">

                                    <!-- Formulario de Configuración -->
                                    <form>

                                        <div class="row mb-3">
                                            <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Notificaciones por Correo</label>
                                            <div class="col-md-8 col-lg-9">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="changesMade" checked="">
                                                    <label class="form-check-label" for="changesMade">
                                                        Cambios realizados en tu cuenta
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="newProducts" checked="">
                                                    <label class="form-check-label" for="newProducts">
                                                        Información sobre nuevos productos y servicios
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="proOffers">
                                                    <label class="form-check-label" for="proOffers">
                                                        Ofertas y promociones de marketing
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="securityNotify" checked="" disabled="">
                                                    <label class="form-check-label" for="securityNotify">
                                                        Alertas de seguridad
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                        </div>
                                    </form><!-- Fin Formulario de Configuración -->

                                </div>


                                <div class="tab-pane fade pt-3" id="profile-change-password" role="tabpanel">
                                    <!-- Formulario de Cambio de Contraseña -->
                                    <form id="formCambioClave" action="<?= BASE_URL ?>/admin/perfil/cambiar-clave" method="POST" enctype="multipart/form-data">

                                        <div class="row mb-3">
                                            <label for="currentPassword" class="col-md-4 col-lg-3 col-form-label">Contraseña Actual</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="clave_actual" type="password" class="form-control" id="currentPassword" required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">Nueva Contraseña</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="clave_nueva" type="password" class="form-control" id="newPassword" required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="renewPassword" class="col-md-4 col-lg-3 col-form-label">Reingresar Nueva Contraseña</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="clave_confirmar" type="password" class="form-control" id="renewPassword" required>
                                            </div>
                                        </div>

                                        <div class="text-center">
                                            <button type="submit" name="cambiar_clave" class="btn btn-primary">Cambiar Contraseña</button>
                                        </div>
                                    </form>
                                    <!-- Fin Formulario de Cambio de Contraseña -->

                                </div>


                            </div><!-- End Bordered Tabs -->

                        </div>
                    </div>

                </div>
            </div>
        </section>


    </main>


    <footer>
        <!-- Enlaces / Información -->
    </footer>

    <!-- apexcharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- tu javaScript -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
</body>

</html>