<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario | Proviservers</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- CSS de Finanzas -->
  
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/calendario.css">
</head>

<body>
    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_administrador.php'; ?>

    <main class="contenido">
        <!-- HEADER -->
        <?php include_once __DIR__ . '/../../layouts/header_administrador.php'; ?>

        <!-- Título Principal -->
        <section id="titulo-principal">
            <h1>Calendario Administrativo</h1>
            <p class="text-muted mb-3">
                Gestiona eventos, vencimientos de membresías, servicios programados y fechas importantes de la plataforma.
            </p>
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Inicio</a></li>
                    <li class="breadcrumb-item">Administrador</li>
                    <li class="breadcrumb-item active" aria-current="page">Calendario</li>
                </ol>
            </nav>
        </section>

        <!-- Cards de Resumen -->
        <section class="cards-container">
            <div class="calendar-card">
                <div class="card-icon icon-blue">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="card-value" id="eventosHoy">3</div>
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
        </section>

        <!-- Sección Principal: Calendario + Panel Lateral -->
        <section class="calendar-layout">
            <!-- Panel Izquierdo: Calendario -->
            <div class="calendar-main">
                <!-- Header del Calendario -->
                <div class="calendar-header">
                    <div class="calendar-navigation">
                        <button class="btn-nav" id="prevMonth">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <h2 class="calendar-month" id="currentMonth">Diciembre 2025</h2>
                        <button class="btn-nav" id="nextMonth">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                    <button class="btn-primary-proviservers" id="todayBtn">
                        <i class="bi bi-calendar-day"></i> Hoy
                    </button>
                </div>

                <!-- Filtros -->
                <div class="calendar-filters">
                    <div class="filter-item active" data-filter="all">
                        <span class="filter-dot bg-all"></span>
                        Todos
                    </div>
                    <div class="filter-item" data-filter="membresia">
                        <span class="filter-dot bg-membresia"></span>
                        Membresías
                    </div>
                    <div class="filter-item" data-filter="servicio">
                        <span class="filter-dot bg-servicio"></span>
                        Servicios
                    </div>
                    <div class="filter-item" data-filter="verificacion">
                        <span class="filter-dot bg-verificacion"></span>
                        Verificaciones
                    </div>
                    <div class="filter-item" data-filter="evento">
                        <span class="filter-dot bg-evento"></span>
                        Eventos
                    </div>
                </div>

                <!-- Grid del Calendario -->
                <div class="calendar-grid">
                    <div class="calendar-day-header">Dom</div>
                    <div class="calendar-day-header">Lun</div>
                    <div class="calendar-day-header">Mar</div>
                    <div class="calendar-day-header">Mié</div>
                    <div class="calendar-day-header">Jue</div>
                    <div class="calendar-day-header">Vie</div>
                    <div class="calendar-day-header">Sáb</div>
                </div>
                <div class="calendar-days-grid" id="calendarDays"></div>
            </div>

            <!-- Panel Derecho: Eventos y Detalles -->
            <div class="calendar-sidebar">
                <!-- Eventos del Día Seleccionado -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title">
                        <i class="bi bi-calendar-event"></i>
                        Eventos del Día
                    </h3>
                    <p class="sidebar-date" id="selectedDate">Martes, 10 de Diciembre 2025</p>
                    
                    <div class="events-list" id="eventsList">
                        <!-- Los eventos se cargarán dinámicamente -->
                    </div>
                </div>

                <!-- Próximos Vencimientos -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title">
                        <i class="bi bi-alarm"></i>
                        Próximos Vencimientos
                    </h3>
                    
                    <div class="upcoming-list">
                        <div class="upcoming-item">
                            <div class="upcoming-icon bg-orange">
                                <i class="bi bi-credit-card"></i>
                            </div>
                            <div class="upcoming-info">
                                <p class="upcoming-title">Membresía Premium</p>
                                <p class="upcoming-subtitle">Roberto Sánchez - 5 días</p>
                            </div>
                        </div>

                        <div class="upcoming-item">
                            <div class="upcoming-icon bg-orange">
                                <i class="bi bi-credit-card"></i>
                            </div>
                            <div class="upcoming-info">
                                <p class="upcoming-title">Membresía Basic</p>
                                <p class="upcoming-subtitle">Sandra López - 12 días</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas Rápidas -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title">
                        <i class="bi bi-graph-up"></i>
                        Este Mes
                    </h3>
                    
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-value" id="statTotal">17</span>
                            <span class="stat-label">Total Eventos</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value" id="statServicios">7</span>
                            <span class="stat-label">Servicios</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value" id="statVencimientos">4</span>
                            <span class="stat-label">Vencimientos</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value" id="statVerificaciones">3</span>
                            <span class="stat-label">Verificaciones</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- JavaScript de Calendario -->
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/calendario.js"></script>
</body>

</html>