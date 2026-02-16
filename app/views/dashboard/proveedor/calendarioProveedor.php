<?php
require_once BASE_PATH . '/app/helpers/session_proveedor.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Servicios Completados</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

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

        <!-- TÍTULO -->
        <section id="titulo-principal">
            <h1>Mi Agenda de Trabajo</h1>
            <p class="text-muted mb-3">
                Administra tus reservas, disponibilidad y controla tus ingresos diarios.
            </p>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/proveedor/dashboard/calendarioProveedor">Inicio</a>
                    </li>
                    <li class="breadcrumb-item active">Mi Calendario</li>
                </ol>
            </nav>
        </section>

        <!-- CARDS DIFERENTES A ADMIN -->
        <section class="row g-4 mb-4">

            <div class="col-md-3">
                <div class="card shadow-sm p-3">
                    <h6 class="text-muted">Servicios Hoy</h6>
                    <h3>4</h3>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i> 2 más que ayer
                    </small>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm p-3">
                    <h6 class="text-muted">Solicitudes Pendientes</h6>
                    <h3>3</h3>
                    <small class="text-warning">
                        Requieren confirmación
                    </small>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm p-3">
                    <h6 class="text-muted">Ingresos Hoy</h6>
                    <h3>$320.000</h3>
                    <small class="text-primary">
                        Confirmados
                    </small>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm p-3">
                    <h6 class="text-muted">Días Bloqueados</h6>
                    <h3>2</h3>
                    <small class="text-danger">
                        No disponibles
                    </small>
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
                                Bloquear Día
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

            <!-- PANEL DERECHO COMPLETAMENTE DIFERENTE -->
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

    <!-- MODAL BLOQUEAR DÍA -->
    <div class="modal fade" id="bloquearDiaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Bloquear Día</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label class="form-label">Selecciona la fecha</label>
                    <input type="date" class="form-control">

                    <label class="form-label mt-3">Motivo (opcional)</label>
                    <textarea class="form-control"></textarea>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button class="btn btn-danger">
                        Confirmar Bloqueo
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JS personalizado -->
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/calendario_proveedor.js"></script>

</body>
</html>
