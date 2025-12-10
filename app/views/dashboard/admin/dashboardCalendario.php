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

        <!-- Calendario -->
        <div class="calendar-main">
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

            <div class="calendar-filters">
                <div class="filter-item active" data-filter="all">
                    <span class="filter-dot bg-all"></span> Todos
                </div>
                <div class="filter-item" data-filter="membresia">
                    <span class="filter-dot bg-membresia"></span> Membresías
                </div>
                <div class="filter-item" data-filter="servicio">
                    <span class="filter-dot bg-servicio"></span> Servicios
                </div>
                <div class="filter-item" data-filter="verificacion">
                    <span class="filter-dot bg-verificacion"></span> Verificaciones
                </div>
                <div class="filter-item" data-filter="evento">
                    <span class="filter-dot bg-evento"></span> Eventos
                </div>
            </div>

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

        <!-- Sidebar -->
        <div class="calendar-sidebar">

            <div class="sidebar-section">
                <h3 class="sidebar-title">
                    <i class="bi bi-calendar-event"></i> Eventos del Día
                </h3>
                <p class="sidebar-date" id="selectedDate">Martes, 10 de Diciembre 2025</p>

                <div class="events-list" id="eventsList"></div>
            </div>

            <div class="sidebar-section">
                <h3 class="sidebar-title">
                    <i class="bi bi-alarm"></i> Próximos Vencimientos
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

            <div class="sidebar-section">
                <h3 class="sidebar-title">
                    <i class="bi bi-graph-up"></i> Este Mes
                </h3>

                <div class="stats-grid">
                    <div class="stat-item"><span class="stat-value">156</span><span class="stat-label">Total Eventos</span></div>
                    <div class="stat-item"><span class="stat-value">89</span><span class="stat-label">Servicios</span></div>
                    <div class="stat-item"><span class="stat-value">32</span><span class="stat-label">Vencimientos</span></div>
                    <div class="stat-item"><span class="stat-value">18</span><span class="stat-label">Verificaciones</span></div>
                </div>
            </div>

        </div>

    </div>

    <!-- JS externo -->
    <script src="app.js"></script>

</body>
</html>