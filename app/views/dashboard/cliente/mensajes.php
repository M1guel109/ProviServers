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

    <!-- HEADER -->
    <?php include_once __DIR__ . '/../../layouts/header_cliente.php'; ?>

        <section id="mensajes">
        <div class="container">
            <div class="section-hero mb-4">
            <p class="breadcrumb">Inicio > Mensajes</p>
            <h1><i class="bi text-primary"></i>Mensajes</h1>
            <p>Comunícate directamente con tus proveedores. Revisa tus conversaciones y responde cuando lo necesites.</p>
            </div>

            <div class="messages-list">
            <!-- Conversación 1 -->
            <div class="card message-item mb-4 shadow-sm border-0 rounded-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="fw-semibold mb-0">Prov. Miguel Torres</h5>
                <small class="text-muted">Hace 2 horas</small>
                </div>
                <p class="text-muted mb-2">Hola Karen, ya estoy en camino para el servicio de jardinería.</p>
                <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm">Responder</button>
                <button class="btn btn-outline-secondary btn-sm">Ver perfil</button>
                </div>
            </div>
            </div>

            <!-- Conversación 2 -->
            <div class="card message-item mb-4 shadow-sm border-0 rounded-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="fw-semibold mb-0">Prov. Carlos Ruiz</h5>
                <small class="text-muted">Ayer</small>
                </div>
                <p class="text-muted mb-2">Confirmo la cita de plomería para el jueves a las 10 AM.</p>
                <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm">Responder</button>
                <button class="btn btn-outline-secondary btn-sm">Ver perfil</button>
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