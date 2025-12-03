<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proviservers | Servicios Contratados</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

  <!-- Estilos globales -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
  <!-- Estilos específicos -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardCliente.css">
</head>
<body>

  <!-- SIDEBAR -->
  <?php 
    $currentPage = 'servicios-contratados';
    include_once __DIR__ . '/../../layouts/sidebar_cliente.php'; 
  ?>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="contenido">

    <!-- HEADER -->
    <?php include_once __DIR__ . '/../../layouts/header_cliente.php'; ?>

    <section id="servicios-contratados">
      <div class="section-hero mb-4">
        <p class="breadcrumb">Inicio > Servicios Contratados</p>
        <h1><i class="bi bi-briefcase-fill me-2 text-primary"></i>Servicios Contratados</h1>
        <p>Gestiona todos tus servicios contratados y programados desde aquí.</p>
      </div>

      <!-- Pestañas por estado -->
      <ul class="nav nav-tabs mb-4" id="estadoTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#curso">En curso</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#programado">Programados</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#completado">Completados</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#cancelado">Cancelados</button></li>
      </ul>

      <div class="tab-content" id="estadoTabsContent">
        <!-- En curso -->
        <div class="tab-pane fade show active" id="curso">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

            <!-- Servicio 1 -->
            <div class="col">
              <div class="card service-card estado-curso">
                <img src="<?= BASE_URL ?>/public/uploads/proveedores/jardinero.jpg" class="card-img-top" alt="Jardinería">
                <div class="card-body">
                  <h5 class="card-title">Jardinería y Paisajismo</h5>
                  <p class="card-subtitle text-muted"><i class="bi bi-person-fill"></i> Miguel Torres</p>
                  <p class="card-text"><i class="bi bi-calendar-event"></i> Finaliza el 10 de diciembre</p>
                  <div class="progress mb-3" style="height: 20px;">
                    <div class="progress-bar bg-success" role="progressbar"
                        style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                        data-progreso="60">
                      0%
                    </div>
                  </div>
                  <a href="#" class="btn btn-primary w-100">Ver detalles</a>
                </div>
              </div>
            </div>

            <!-- Servicio 2 -->
            <div class="col">
              <div class="card service-card estado-curso">
                <img src="<?= BASE_URL ?>/public/uploads/proveedores/fontanero.jpg" class="card-img-top" alt="Plomería">
                <div class="card-body">
                  <h5 class="card-title">Plomería</h5>
                  <p class="card-subtitle text-muted"><i class="bi bi-person-fill"></i> Carlos Ruiz</p>
                  <p class="card-text"><i class="bi bi-calendar-event"></i> Finaliza el 15 de diciembre</p>
                  <div class="progress mb-3" style="height: 20px;">
                    <div class="progress-bar bg-info" role="progressbar"
                        style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                        data-progreso="30">
                      0%
                    </div>
                  </div>
                  <a href="#" class="btn btn-primary w-100">Ver detalles</a>
                </div>
              </div>
            </div>

            <!-- Servicio 3 -->
            <div class="col">
              <div class="card service-card estado-curso">
                <img src="<?= BASE_URL ?>/public/uploads/proveedores/electricista.jpg" class="card-img-top" alt="Electricidad">
                <div class="card-body">
                  <h5 class="card-title">Electricidad</h5>
                  <p class="card-subtitle text-muted"><i class="bi bi-person-fill"></i> Luis Martínez</p>
                  <p class="card-text"><i class="bi bi-calendar-event"></i> Finaliza el 20 de diciembre</p>
                  <div class="progress mb-3" style="height: 20px;">
                    <div class="progress-bar bg-warning" role="progressbar"
                        style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                        data-progreso="85">
                      0%
                    </div>
                  </div>
                  <a href="#" class="btn btn-primary w-100">Ver detalles</a>
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- Programados -->
        <div class="tab-pane fade" id="programado">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <div class="col">
              <div class="card service-card estado-programado">
                <img src="<?= BASE_URL ?>/public/uploads/proveedores/fontanero.jpg" class="card-img-top" alt="Plomería">
                <div class="card-body">
                  <h5 class="card-title">Plomería</h5>
                  <p class="card-subtitle text-muted"><i class="bi bi-person-fill"></i> Carlos Ruiz</p>
                  <p class="card-text"><i class="bi bi-calendar-event"></i> 15 de diciembre</p>
                  <a href="#" class="btn btn-primary w-100">Ver detalles</a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Completados -->
        <div class="tab-pane fade" id="completado">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <div class="col">
              <div class="card service-card estado-completado">
                <img src="<?= BASE_URL ?>/public/uploads/proveedores/electricista.jpg" class="card-img-top" alt="Electricidad">
                <div class="card-body">
                  <h5 class="card-title">Electricidad</h5>
                  <p class="card-subtitle text-muted"><i class="bi bi-person-fill"></i> Luis Martínez</p>
                  <p class="card-text"><i class="bi bi-calendar-check"></i> Completado el 1 de diciembre</p>
                  <a href="#" class="btn btn-primary w-100">Ver detalles</a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Cancelados -->
        <div class="tab-pane fade" id="cancelado">
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <div class="col">
              <div class="card service-card estado-cancelado">
                <img src="<?= BASE_URL ?>/public/uploads/proveedores/limpiezaResidencial.jpg" class="card-img-top" alt="Limpieza">
                <div class="card-body">
                  <h5 class="card-title">Limpieza Residencial</h5>
                  <p class="card-subtitle text-muted"><i class="bi bi-person-fill"></i> Ana Gómez</p>
                  <p class="card-text"><i class="bi bi-calendar-x"></i> Cancelado el 2 de diciembre</p>
                  <a href="#" class="btn btn-primary w-100">Ver detalles</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
