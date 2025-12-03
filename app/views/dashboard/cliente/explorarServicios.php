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
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboardCliente.css">
</head>
<body>
  <!-- SIDEBAR -->
  <?php 
    $currentPage = 'explorar';
    include_once __DIR__ . '/../../layouts/sidebar_cliente.php'; 
  ?>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="contenido">

    <!-- HEADER -->
    <?php include_once __DIR__ . '/../../layouts/header_cliente.php'; ?>

    <section id="explorar">
      <div class="section-hero mb-4">
        <p class="breadcrumb">Inicio > Explorar Servicios</p>
        <h1>Explorar Servicios</h1>
        <p>Descubre profesionales verificados listos para ayudarte.</p>
      </div>


      <!-- Buscador -->
      <div class="mb-4">
        <form class="d-flex gap-2">
          <input type="text" class="form-control" placeholder="Buscar servicios, proveedores...">
          <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
        </form>
      </div>

      <!-- Filtros de categorías -->
      <div class="mb-4 category-filters">
        <div class="d-flex flex-wrap gap-2">
          <button class="btn btn-outline-primary"><i class="bi bi-house"></i> Hogar</button>
          <button class="btn btn-outline-primary"><i class="bi bi-laptop"></i> Tecnología</button>
          <button class="btn btn-outline-primary"><i class="bi bi-heart"></i> Mascotas</button>
          <button class="btn btn-outline-primary"><i class="bi bi-truck"></i> Transporte</button>
          <button class="btn btn-outline-primary"><i class="bi bi-heart-pulse"></i> Salud</button>
        </div>
      </div>


      <!-- Tarjetas de servicios -->
      <div class="section-content">
        <div class="row">
          <!-- Tarjeta 1 -->
          <div class="col-md-4 mb-4">
            <div class="card service-card">
              <div class="service-image">
                <img src="<?= BASE_URL ?>/public/uploads/proveedores/jardinero.jpg" alt="Jardinería">
              </div>
              <div class="card-body service-content">
                <h5 class="card-title">Jardinería y Paisajismo</h5>
                <p class="card-subtitle">Proveedor: Miguel Torres</p>
                <p class="card-text">Diseño y mantenimiento de jardines para tu hogar o empresa.</p>
                <p class="card-rating">⭐ 4.8/5</p>
                <a href="<?= BASE_URL ?>/cliente/servicios-contratados" class="btn btn-primary w-100">Contratar Servicio</a>
              </div>
            </div>
          </div>

          <!-- Tarjeta 2 -->
          <div class="col-md-4 mb-4">
            <div class="card service-card">
              <div class="service-image">
                <img src="<?= BASE_URL ?>/public/uploads/proveedores/fontanero.jpg" alt="Plomería">
              </div>
              <div class="card-body service-content">
                <h5 class="card-title">Plomería</h5>
                <p class="card-subtitle">Proveedor: Carlos Ruiz</p>
                <p class="card-text">Instalaciones y reparaciones rápidas para tu hogar.</p>
                <p class="card-rating">⭐ 4.5/5</p>
                <a href="<?= BASE_URL ?>/cliente/servicios-contratados" class="btn btn-primary w-100">Contratar Servicio</a>
              </div>
            </div>
          </div>

          <!-- Tarjeta 3 -->
          <div class="col-md-4 mb-4">
            <div class="card service-card">
              <div class="service-image">
                <img src="<?= BASE_URL ?>/public/uploads/proveedores/electricista.jpg" alt="Electricidad">
              </div>
              <div class="card-body service-content">
                <h5 class="card-title">Electricidad</h5>
                <p class="card-subtitle">Proveedor: Luis Martínez</p>
                <p class="card-text">Instalaciones eléctricas seguras y mantenimiento preventivo.</p>
                <p class="card-rating">⭐ 4.7/5</p>
                <a href="<?= BASE_URL ?>/cliente/servicios-contratados" class="btn btn-primary w-100">Contratar Servicio</a>
              </div>
            </div>
          </div>

          <!-- Tarjeta 4 -->
          <div class="col-md-4 mb-4">
            <div class="card service-card">
              <div class="service-image">
                <img src="<?= BASE_URL ?>/public/uploads/proveedores/limpiezaResidencial.jpg" alt="Limpieza">
              </div>
              <div class="card-body service-content">
                <h5 class="card-title">Limpieza Residencial</h5>
                <p class="card-subtitle">Proveedor: Ana Gómez</p>
                <p class="card-text">Servicios de limpieza profunda y mantenimiento del hogar.</p>
                <p class="card-rating">⭐ 4.9/5</p>
                <a href="<?= BASE_URL ?>/cliente/servicios-contratados" class="btn btn-primary w-100">Contratar Servicio</a>
              </div>
            </div>
          </div>

          <!-- Tarjeta 5 -->
          <div class="col-md-4 mb-4">
            <div class="card service-card">
              <div class="service-image">
                <img src="<?= BASE_URL ?>/public/uploads/proveedores/pintor.jpg" alt="Pintura">
              </div>
              <div class="card-body service-content">
                <h5 class="card-title">Pintura</h5>
                <p class="card-subtitle">Proveedor: José Hernández</p>
                <p class="card-text">Pintura interior y exterior con acabados profesionales.</p>
                <p class="card-rating">⭐ 4.6/5</p>
                <a href="<?= BASE_URL ?>/cliente/servicios-contratados" class="btn btn-primary w-100">Contratar Servicio</a>
              </div>
            </div>
          </div>

          <!-- Tarjeta 6 -->
          <div class="col-md-4 mb-4">
            <div class="card service-card">
              <div class="service-image">
                <img src="<?= BASE_URL ?>/public/uploads/proveedores/carpinteria.jpg" alt="Carpintería">
              </div>
              <div class="card-body service-content">
                <h5 class="card-title">Carpintería</h5>
                <p class="card-subtitle">Proveedor: María López</p>
                <p class="card-text">Muebles a medida y reparaciones en madera.</p>
                <p class="card-rating">⭐ 4.4/5</p>
                <a href="<?= BASE_URL ?>/cliente/servicios-contratados" class="btn btn-primary w-100">Contratar Servicio</a>
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
