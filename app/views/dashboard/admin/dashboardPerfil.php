<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
require_once BASE_PATH . '/app/controllers/perfilController.php';

// 1. Obtener ID del admin logueado
$id = $_SESSION['user']['id'];

// 2. Obtener datos del perfil (Asegúrate que tu controlador haga JOIN entre usuarios y admins)
$usuario = mostrarPerfilAdmin($id);

// 3. Lógica de foto de perfil (Para no repetir código en el HTML)
$fotoNombre = !empty($usuario['foto']) ? $usuario['foto'] : 'default_user.png';
$rutaFoto = BASE_URL . '/public/uploads/usuarios/' . $fotoNombre;

// Si la imagen física no existe, usa la default
// (Esto es opcional, pero previene imágenes rotas)
// if (!file_exists(BASE_PATH . '/public/uploads/usuarios/' . $fotoNombre)) {
//     $rutaFoto = BASE_URL . '/public/assets/img/default_user.png';
// }

// // BORRAR ESTO DESPUÉS DE PROBAR
// echo "<pre>";
// print_r($usuario);
// echo "</pre>";
// die(); // Detiene la carga para ver los datos
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Perfil Administrador</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardPerfil.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar_administrador.php' ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header_administrador.php' ?>

        <section id="titulo-principal" class="d-flex justify-content-between align-items-start flex-wrap mb-4">
            <div>
                <h1 class="mb-1">Mi Perfil</h1>
                <p class="text-muted mb-0">Gestiona tu información personal y seguridad.</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Inicio</a></li>
                    <li class="breadcrumb-item active">Perfil</li>
                </ol>
            </nav>
        </section>

        <section class="section profile">
            <div class="row">
                
                <div class="col-xl-4">
                    <div class="card mb-4">
                        <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                            
                            <img src="<?= $rutaFoto ?>" alt="Perfil" class="rounded-circle object-fit-cover" width="120" height="120"
                                 onerror="this.src='<?= BASE_URL ?>/public/assets/img/default_user.png';">

                            <h2 class="mt-3"><?= htmlspecialchars($usuario['nombres']) ?></h2>
                            <h3 class="text-muted"><?= ucfirst($usuario['tipo_admin'] ?? 'Administrador') ?></h3>
                            
                            <div class="badge bg-success mt-2">Activo</div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-body pt-3">
                            
                            <ul class="nav nav-tabs nav-tabs-bordered" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview">Información</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit">Editar Perfil</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password">Cambiar Contraseña</button>
                                </li>
                            </ul>

                            <div class="tab-content pt-4">

                                <div class="tab-pane fade show active profile-overview" id="profile-overview">
                                    
                                    <h5 class="card-title fw-bold mb-3">Detalles del Perfil</h5>

                                    <div class="row mb-3">
                                        <div class="col-lg-3 col-md-4 label text-muted fw-bold">Nombre Completo</div>
                                        <div class="col-lg-9 col-md-8"><?= htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']) ?></div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-lg-3 col-md-4 label text-muted fw-bold">Rol/Tipo</div>
                                        <div class="col-lg-9 col-md-8"><?= htmlspecialchars(ucfirst($usuario['tipo_admin'] ?? 'Admin')) ?></div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-lg-3 col-md-4 label text-muted fw-bold">Ubicación</div>
                                        <div class="col-lg-9 col-md-8"><?= htmlspecialchars($usuario['direccion'] ?? 'No registrada') ?></div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-lg-3 col-md-4 label text-muted fw-bold">Teléfono</div>
                                        <div class="col-lg-9 col-md-8"><?= htmlspecialchars($usuario['telefono'] ?? 'No registrado') ?></div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-lg-3 col-md-4 label text-muted fw-bold">Correo Electrónico</div>
                                        <div class="col-lg-9 col-md-8"><?= htmlspecialchars($usuario['correo']) ?></div>
                                    </div>
                                </div>

                                <div class="tab-pane fade profile-edit pt-3" id="profile-edit">

                                    <form action="<?= BASE_URL ?>/admin/perfil/actualizar" method="POST" enctype="multipart/form-data">
                                        
                                        <div class="row mb-3">
                                            <label for="profileImage" class="col-md-4 col-lg-3 col-form-label">Foto de Perfil</label>
                                            <div class="col-md-8 col-lg-9">
                                                <img src="<?= $rutaFoto ?>" alt="Perfil" width="80" class="mb-2">
                                                <div class="pt-2">
                                                    <input class="form-control form-control-sm" type="file" name="foto" id="foto" accept="image/*">
                                                    <div class="form-text small">Formatos: JPG, PNG. Máx 2MB.</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="nombres" class="col-md-4 col-lg-3 col-form-label">Nombres</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="nombres" type="text" class="form-control" id="nombres" value="<?= htmlspecialchars($usuario['nombres']) ?>" required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="apellidos" class="col-md-4 col-lg-3 col-form-label">Apellidos</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="apellidos" type="text" class="form-control" id="apellidos" value="<?= htmlspecialchars($usuario['apellidos']) ?>" required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="ubicacion" class="col-md-4 col-lg-3 col-form-label">Ubicación</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="ubicacion" type="text" class="form-control" id="ubicacion" value="<?= htmlspecialchars($usuario['direccion'] ?? '') ?>">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="telefono" class="col-md-4 col-lg-3 col-form-label">Teléfono</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="telefono" type="text" class="form-control" id="telefono" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label for="email" class="col-md-4 col-lg-3 col-form-label">Correo (Usuario)</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="email" type="email" class="form-control" id="email" value="<?= htmlspecialchars($usuario['correo']) ?>" readonly style="background-color: #e9ecef;">
                                                <small class="text-muted">El correo no se puede cambiar desde aquí.</small>
                                            </div>
                                        </div>

                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane fade pt-3" id="profile-change-password">
                                    
                                    <form action="<?= BASE_URL ?>/admin/perfil/cambiar-clave" method="POST">

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
                                            <label for="renewPassword" class="col-md-4 col-lg-3 col-form-label">Confirmar Contraseña</label>
                                            <div class="col-md-8 col-lg-9">
                                                <input name="clave_confirmar" type="password" class="form-control" id="renewPassword" required>
                                            </div>
                                        </div>

                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                                        </div>
                                    </form>

                                </div>

                            </div></div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- tu javaScript -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/app.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>