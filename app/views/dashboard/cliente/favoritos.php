<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proviservers | Favoritos</title>

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
    $currentPage = 'favoritos';
    include_once __DIR__ . '/../../layouts/sidebar_cliente.php'; 
  ?>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="contenido">

    <!-- HEADER -->
    <?php include_once __DIR__ . '/../../layouts/header_cliente.php'; ?>

    <section id="favoritos">
      <div class="container">
      <div class="section-hero mb-4">
        <p class="breadcrumb">Inicio > Favoritos</p>
        <h1><i class=" bi text-primary"></i>Mis Favoritos</h1>
        <p>Tus proveedores locales preferidos, listos para atenderte donde estés.</p>
      </div>

        <div class="row gy-4">
          <!-- Tarjeta de servicio favorito -->
          <div class="col-lg-4 col-md-6" data-id="1">
            <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden position-relative">
              <img src="<?= BASE_URL ?>/public/uploads/proveedores/reparaciones.jpg" class="card-img-top img-servicio" alt="Reparaciones a domicilio">
              <button class="btn btn-fav position-absolute top-0 end-0 m-2 p-2 rounded-circle">
                <i class="bi bi-heart-fill"></i>
              </button>
              <div class="card-body text-center d-flex flex-column justify-content-between">
                <h5 class="card-title fw-bold mb-1">Reparaciones a domicilio</h5>
                <p class="text-muted small mb-2">Proveedor: Juan Pérez</p>
                <a href="#" class="btn btn-primary btn-sm mt-auto">Solicitar servicio</a>
              </div>
            </div>
          </div>

          <!-- Repite para cada proveedor -->
          <div class="col-lg-4 col-md-6" data-id="2">
            <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden position-relative">
              <img src="<?= BASE_URL ?>/public/uploads/proveedores/cosmetologaBelleza.jpg" class="card-img-top img-servicio" alt="Belleza a domicilio">
              <button class="btn btn-fav position-absolute top-0 end-0 m-2 p-2 rounded-circle">
                <i class="bi bi-heart-fill"></i>
              </button>
              <div class="card-body text-center d-flex flex-column justify-content-between">
                <h5 class="card-title fw-bold mb-1">Belleza y cuidado personal</h5>
                <p class="text-muted small mb-2">Proveedor: María Gómez</p>
                <a href="#" class="btn btn-primary btn-sm mt-auto">Solicitar servicio</a>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-6" data-id="3">
            <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden position-relative">
              <img src="<?= BASE_URL ?>/public/uploads/proveedores/veterinaria-domicilio.jpg" class="card-img-top img-servicio" alt="Veterinaria móvil">
              <button class="btn btn-fav position-absolute top-0 end-0 m-2 p-2 rounded-circle">
                <i class="bi bi-heart-fill"></i>
              </button>
              <div class="card-body text-center d-flex flex-column justify-content-between">
                <h5 class="card-title fw-bold mb-1">Veterinaria móvil</h5>
                <p class="text-muted small mb-2">Proveedor: Clínica Mi Mascota</p>
                <a href="#" class="btn btn-primary btn-sm mt-auto">Solicitar servicio</a>
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
