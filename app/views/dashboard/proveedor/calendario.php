<?php
require_once BASE_PATH . '/app/helpers/session_proveedor.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Mi Agenda de Trabajo</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos Globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboard-Proveedor.css">

    <!-- CSS Específico -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/calendarioProveedor.css">
</head>

<body>
    <!-- Sidebar Proveedor -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_proveedor.php'; ?>

    <main class="contenido">
        <!-- Header Proveedor -->
        <?php include_once __DIR__ . '/../../layouts/header_proveedor.php'; ?>

        <!-- TÍTULO CON BREADCRUMB (IGUAL QUE DASHBOARD) -->
        <section id="titulo-principal">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Mi Agenda de Trabajo</h1>
                    <p class="text-muted mb-0">
                        Administra tus reservas, disponibilidad y controla tus ingresos diarios.
                    </p>
                </div>
                <div class="col-md-4">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/proveedor/dashboard">Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Mi Calendario</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <!-- CARDS DE ESTADÍSTICAS (estilo tarjetas) -->
        <section class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="tarjeta-estadistica">
                    <i class="bi bi-calendar-check icono-estadistica text-primary"></i>
                    <div>
                        <div class="valor-estadistica">4</div>
                        <div class="etiqueta-estadistica">Servicios Hoy</div>
                        <small class="text-success">
                            <i class="bi bi-arrow-up"></i> 2 más que ayer
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="tarjeta-estadistica">
                    <i class="bi bi-clock-history icono-estadistica text-warning"></i>
                    <div>
                        <div class="valor-estadistica">3</div>
                        <div class="etiqueta-estadistica">Solicitudes Pendientes</div>
                        <small class="text-warning">Requieren confirmación</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="tarjeta-estadistica">
                    <i class="bi bi-cash-coin icono-estadistica text-success"></i>
                    <div>
                        <div class="valor-estadistica">$320k</div>
                        <div class="etiqueta-estadistica">Ingresos Hoy</div>
                        <small class="text-primary">Confirmados</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="tarjeta-estadistica">
                    <i class="bi bi-calendar-x icono-estadistica text-danger"></i>
                    <div>
                        <div class="valor-estadistica">2</div>
                        <div class="etiqueta-estadistica">Días Bloqueados</div>
                        <small class="text-danger">No disponibles</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- NUEVA ESTRUCTURA DIFERENTE -->
        <section class="row">
            <!-- CALENDARIO PRINCIPAL -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <button class="btn btn-light btn-sm" id="prevMonth">
                                <i class="bi bi-chevron-left"></i>
                            </button>

                            <span class="fw-bold mx-3" id="currentMonth">
                                Diciembre 2025
                            </span>

                            <button class="btn btn-light btn-sm" id="nextMonth">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>

                        <div>
                            <button class="btn btn-outline-primary btn-sm" id="todayBtn">
                                Hoy
                            </button>

                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#bloquearDiaModal">
                                <i class="bi bi-calendar-x"></i> Bloquear Día
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Encabezados -->
                        <div class="row text-center fw-bold border-bottom pb-2">
                            <div class="col">Dom</div>
                            <div class="col">Lun</div>
                            <div class="col">Mar</div>
                            <div class="col">Mié</div>
                            <div class="col">Jue</div>
                            <div class="col">Vie</div>
                            <div class="col">Sáb</div>
                        </div>

                        <!-- DÍAS DINÁMICOS -->
                        <div id="calendarDays" class="mt-3">
                            <!-- Se llenará con JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL DERECHO -->
            <div class="col-lg-4">
                <!-- SERVICIOS DEL DÍA -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-briefcase"></i> Servicios del Día
                        </h6>
                    </div>
                    <div class="card-body" id="servicesOfDay">
                        <p class="text-muted">Selecciona un día para ver detalles.</p>
                    </div>
                </div>

                <!-- INGRESOS DEL DÍA -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-cash-stack"></i> Resumen Financiero
                        </h6>
                    </div>
                    <div class="card-body">
                        <p>Total Confirmado: <strong>$450.000</strong></p>
                        <p>Pendiente de Pago: <strong>$120.000</strong></p>
                        <p>Comisión Plataforma: <strong>$45.000</strong></p>
                    </div>
                </div>

                <!-- DISPONIBILIDAD -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-calendar-x"></i> Disponibilidad
                        </h6>
                    </div>
                    <div class="card-body">
                        <p>Próximo día libre completo:</p>
                        <strong>18 de Diciembre 2025</strong>
                        <hr>
                        <p>Próximo servicio:</p>
                        <strong>Instalación Eléctrica - Mañana 8:00 AM</strong>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- MODAL BLOQUEAR DÍA (centrado) -->
    <div class="modal fade" id="bloquearDiaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-x me-2"></i>Bloquear Día
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <label class="form-label fw-bold">Selecciona la fecha</label>
                    <input type="date" class="form-control mb-3">

                    <label class="form-label fw-bold">Motivo (opcional)</label>
                    <textarea class="form-control" rows="3" placeholder="Ej: Descanso, mantenimiento, etc."></textarea>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger">
                        <i class="bi bi-calendar-x me-2"></i>Confirmar Bloqueo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/calendario.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
</body>
</html>