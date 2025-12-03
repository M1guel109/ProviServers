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
    $currentPage = 'mensajes';
    include_once __DIR__ . '/../../layouts/sidebar_cliente.php'; 
    ?>
    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido">
    <section id="mensajes">
        <div class="section-hero">
        <h1>Mensajes 📩</h1>
        <p>Comunícate directamente con tus proveedores. Revisa tus conversaciones y responde cuando lo necesites.</p>
        </div>

        <div class="section-content">
        <div class="messages-list">
            <!-- Ejemplo de conversación -->
            <div class="message-item">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Miguel Torres</h5>
                <small class="text-muted">Hace 2 horas</small>
            </div>
            <p class="text-muted">Hola Karen, ya estoy en camino para el servicio de jardinería.</p>
            <div class="d-flex gap-2 mt-2">
                <button class="btn-modern-outline btn-sm">Responder</button>
                <button class="btn-modern-outline btn-sm">Ver perfil</button>
            </div>
            </div>

            <div class="message-item">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Carlos Ruiz</h5>
                <small class="text-muted">Ayer</small>
            </div>
            <p class="text-muted">Confirmo la cita de plomería para el jueves a las 10 AM.</p>
            <div class="d-flex gap-2 mt-2">
                <button class="btn-modern-outline btn-sm">Responder</button>
                <button class="btn-modern-outline btn-sm">Ver perfil</button>
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