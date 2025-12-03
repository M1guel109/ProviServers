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
      <section id="perfil">
        <div class="section-hero">
          <h1>Mi Perfil</h1>
          <p>Administra tu información personal y configuración de cuenta.</p>
        </div>
        <div class="section-content">
          <!-- Información personal, foto de perfil y formulario -->
        </div>
      </section>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JS propio -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboardCliente.js"></script>
</body>
</html>

