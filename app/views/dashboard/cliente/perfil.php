<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proviservers | Mi Cuenta</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

  <!-- Estilos globales -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
  <!-- Estilos específicos de cliente -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardCliente.css">
</head>
<body>
  <!-- SIDEBAR -->
  <?php 
    $currentPage = 'perfil';
    include_once __DIR__ . '/../../layouts/sidebar_cliente.php'; 
  ?>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="contenido">
    <!-- HEADER -->
    <?php include_once __DIR__ . '/../../layouts/header_cliente.php'; ?>

    <!-- Perfil -->
    <section class="section profile py-5">
      <div class="container">
        <!-- Título -->
        <section id="titulo-principal" class="d-flex justify-content-between align-items-start flex-wrap border-top border-4 border-primary pt-3 mb-4">
          <div>
            <h1 class="fw-bold mb-1"><i class="bi bi-person-circle text-primary"></i> Mi Perfil</h1>
            <p class="text-secondary mb-0">Administra tu información personal y configuración de cuenta.</p>
          </div>
          <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><a href="#">Inicio</a></li>
              <li class="breadcrumb-item active" aria-current="page">Perfil</li>
            </ol>
          </nav>
        </section>

        <div class="row">
          <!-- Columna izquierda -->
          <div class="col-xl-4">
            <div class="card shadow-sm border-0 rounded-3">
              <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                <img src="<?= BASE_URL ?>/public/uploads/default_user.png/<?= $usuario['foto'] ?>" 
                     alt="Foto de Perfil" 
                     class="rounded-circle border border-3 border-light shadow-sm" 
                     style="width:130px; height:130px; object-fit:cover;">
                <h2><?= $usuario['nombres'] ?></h2>
                <h3 class="text-muted"><?= $usuario['correo'] ?></h3>
                <button class="btn btn-outline-primary btn-sm mt-2">Cambiar foto</button>
              </div>
            </div>
          </div>

          <!-- Columna derecha -->
          <div class="col-xl-8">
            <div class="card shadow-sm border-0 rounded-3">
              <div class="card-body pt-3">
                <!-- Tabs -->
                <ul class="nav nav-tabs nav-tabs-bordered" role="tablist">
                  <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview" role="tab">Información</button>
                  </li>
                  <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit" role="tab">Editar Perfil</button>
                  </li>
                  <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-settings" role="tab">Configuración</button>
                  </li>
                  <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password" role="tab">Cambiar Contraseña</button>
                  </li>
                </ul>

                <div class="tab-content pt-2">
                  <!-- Información -->
                  <div class="tab-pane fade show active profile-overview" id="profile-overview">
                    <h5 class="card-title">Datos personales</h5>
                    <div class="row mb-3">
                      <div class="col-lg-3 col-md-4 text-muted fw-semibold">Nombre</div>
                      <div class="col-lg-9 col-md-8"><?= $usuario['nombres'] ?></div>
                    </div>
                    <div class="row mb-3">
                      <div class="col-lg-3 col-md-4 text-muted fw-semibold">Correo</div>
                      <div class="col-lg-9 col-md-8"><?= $usuario['correo'] ?></div>
                    </div>
                    <div class="row mb-3">
                      <div class="col-lg-3 col-md-4 text-muted fw-semibold">Teléfono</div>
                      <div class="col-lg-9 col-md-8"><?= $usuario['telefono'] ?></div>
                    </div>
                    <div class="row mb-0">
                      <div class="col-lg-3 col-md-4 text-muted fw-semibold">Dirección</div>
                      <div class="col-lg-9 col-md-8"><?= $usuario['direccion'] ?></div>
                    </div>
                  </div>

                  <!-- Editar Perfil -->
                  <div class="tab-pane fade profile-edit pt-3" id="profile-edit">
                    <form>
                      <div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label">Nombre completo</label>
                          <input type="text" class="form-control" value="<?= $usuario['nombres'] ?>">
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Correo electrónico</label>
                          <input type="email" class="form-control" value="<?= $usuario['correo'] ?>">
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Teléfono</label>
                          <input type="text" class="form-control" value="<?= $usuario['telefono'] ?>">
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Dirección</label>
                          <input type="text" class="form-control" value="<?= $usuario['direccion'] ?>">
                        </div>
                      </div>


                      <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                      </div>
                    </form>
                  </div>

                  <!-- Configuración -->
                  <div class="tab-pane fade pt-3" id="profile-settings">
                    <h5 class="card-title">Notificaciones por correo</h5>
                    <form>
                      <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="changesMade" checked>
                        <label class="form-check-label" for="changesMade">Cambios realizados en tu cuenta</label>
                      </div>
                      <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="newProducts" checked>
                        <label class="form-check-label" for="newProducts">Información sobre nuevos servicios</label>
                      </div>
                      <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="proOffers">
                        <label class="form-check-label" for="proOffers">Ofertas y promociones</label>
                      </div>
                      <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="securityNotify" checked disabled>
                        <label class="form-check-label" for="securityNotify">Alertas de seguridad</label>
                      </div>
                      <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                      </div>
                    </form>
                  </div>

                  <!-- Cambiar Contraseña -->
                  <div class="tab-pane fade pt-3" id="profile-change-password">
                    <h5 class="card-title">Cambiar Contraseña</h5>
                    <form id="formCambioClave" action="<?= BASE_URL ?>/cliente/perfil/cambiar-clave" method="POST">
                      <div class="mb-3">
                        <label class="form-label" for="currentPassword">Contraseña actual</label>
                        <input name="clave_actual" id="currentPassword" type="password" class="form-control" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label" for="newPassword">Nueva contraseña</label>
                        <input name="clave_nueva" id="newPassword" type="password" class="form-control" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label" for="renewPassword">Confirmar nueva contraseña</label>
                        <input name="clave_confirmar" id="renewPassword" type="password" class="form-control" required>
                      </div>
                      <div class="text-center">
                        <button type="submit" name="cambiar_clave" class="btn btn-primary">Cambiar Contraseña</button>
                      </div>
                    </form>
                  </div>
                </div><!-- End tab-content -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <!-- JS propio -->
  <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboardCliente.js"></script>
</body>
</html>
