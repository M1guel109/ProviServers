<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Error 404 - ¡Houston, tenemos un problema!</title>
  
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/error404.css">

</head>

<body>
  <div class="error-container">
    <div class="space-scene">
      <div class="big-404">44</div>

      <img src="<?= BASE_URL ?>/public/assets/auth/img/astronautaa.png" alt="Astronauta flotando en el espacio - Error 404"
        class="main-illustration">
    </div>

    <div class="text-content">
      <h1 class="message-title">PÁGINA NO ENCONTRADA</h1>
      <p class="message-subtitle">¡HOUSTON, TENEMOS UN PROBLEMA!</p>

      <div class="actions">
        <a href="<?= BASE_URL ?>/" class="btn primary-btn">VOLVER AL INICIO</a>
        <a href="#" class="btn secondary-btn">EXPLORAR SERVICIOS</a>
      </div>
    </div>
  </div>

  <script src="<?= BASE_URL ?>/public/assets/auth/js/error404.js"></script>
</body>

</html>
