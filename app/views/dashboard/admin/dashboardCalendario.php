html 

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario | ProviServers</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- CSS separado -->
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <div class="header">
        <h1>Calendario Administrativo</h1>
        <p>Gestiona eventos, vencimientos de membresías, servicios programados y fechas importantes</p>
    </div>

    <!-- Cards -->
    <div class="cards-container">
        <div class="calendar-card">
            <div class="card-icon icon-blue">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="card-value">24</div>
            <div class="card-label">Eventos Hoy</div>
        </div>

        <div class="calendar-card">
            <div class="card-icon icon-orange">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="card-value">12</div>
            <div class="card-label">Vencimientos Esta Semana</div>
        </div>

        <div class="calendar-card">
            <div class="card-icon icon-green">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="card-value">45</div>
            <div class="card-label">Servicios Programados</div>
        </div>

        <div class="calendar-card">
            <div class="card-icon icon-red">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="card-value">8</div>
            <div class="card-label">Verificaciones Pendientes</div>
        </div>
    </div>

    <!-- Layout Principal -->
    <div class="calendar-layout">

       
</body>
</html>