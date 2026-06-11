<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proviservers | Ayuda</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

  <!-- Estilos globales -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
  <!-- Estilos específicos de cliente -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-cliente.css">
</head>
<body>
  
  <!-- SIDEBAR -->
  <?php 
    $currentPage = 'ayuda';
    include_once __DIR__ . '/../../layouts/sidebar-cliente.php'; 
  ?>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="contenido">
    <!-- HEADER -->
    <?php include_once __DIR__ . '/../../layouts/header-cliente.php'; ?>

    <!-- Sección Ayuda -->
    <section id="servicios-ayuda" class="section-servicios">
      <div class="container">
        <!-- Encabezado con breadcrumb -->
        <div id="titulo-principal" class="section-hero mb-4">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h1 class="mb-1">Centro de Ayuda</h1>
              <p class="text-muted mb-0">Encuentra respuestas rápidas, soporte especializado y recursos útiles.</p>
            </div>
            <div class="col-md-4">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 justify-content-md-end">
                  <li class="breadcrumb-item">
                    <a href="<?= BASE_URL ?>/cliente/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                  </li>
                  <li class="breadcrumb-item active" aria-current="page">Ayuda</li>
                </ol>
              </nav>
            </div>
          </div>
        </div>

        <!-- Preguntas Frecuentes -->
        <div class="section-block">
          <h3 class="block-title">Preguntas Frecuentes</h3>
          <div class="accordion" id="faqAccordion">
            
            <!-- FAQ 1 -->
            <div class="accordion-item">
              <h2 class="accordion-header" id="faqHeadingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseOne" aria-expanded="true" aria-controls="faqCollapseOne">
                  ¿Cómo puedo contactar soporte?
                </button>
              </h2>
              <div id="faqCollapseOne" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  Puedes escribirnos directamente desde el formulario de contacto o llamarnos al número oficial.
                </div>
              </div>
            </div>

            <!-- FAQ 2 -->
            <div class="accordion-item">
              <h2 class="accordion-header" id="faqHeadingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseTwo" aria-expanded="false" aria-controls="faqCollapseTwo">
                  ¿Dónde encuentro mis facturas?
                </button>
              </h2>
              <div id="faqCollapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  Accede a tu perfil en la sección "Facturación" para descargar tus comprobantes.
                </div>
              </div>
            </div>

            <!-- FAQ 3 -->
            <div class="accordion-item">
              <h2 class="accordion-header" id="faqHeadingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseThree" aria-expanded="false" aria-controls="faqCollapseThree">
                  ¿Qué hacer si olvidé mi contraseña?
                </button>
              </h2>
              <div id="faqCollapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  Haz clic en "Olvidé mi contraseña" en la página de inicio de sesión y sigue las instrucciones.
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- Contacto rápido -->
        <div class="section-block text-center">
          <h3 class="block-title pt-5">¿Necesitas más ayuda?</h3>
          <p>Estamos disponibles para ti en múltiples canales:</p>
          <div class="contact-row">
            <div class="contact-item"><i class="bi bi-envelope"></i> soporte@empresa.com</div>
            <div class="contact-item"><i class="bi bi-telephone"></i> +57 123 456 7890</div>
            <div class="contact-item"><i class="bi bi-chat-dots"></i> Chat en vivo 24/7</div>
          </div>
          <a href="#contact-form" class="btn btn-primary">Ir al formulario de contacto</a>
        </div>
      </div>
    </section>


  </main>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/dashboard/js/dashboard-cliente.js"></script>
</body>
</html>
