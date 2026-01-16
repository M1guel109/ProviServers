<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Gestión de Solicitud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .status-tracker { display: flex; justify-content: space-between; margin-bottom: 30px; position: relative; }
        .status-step { text-align: center; flex: 1; z-index: 1; }
        .status-dot { width: 30px; height: 30px; background: #ddd; border-radius: 50%; margin: 0 auto 10px; }
        .status-step.active .status-dot { background: #0d6efd; box-shadow: 0 0 0 5px rgba(13,110,253,0.2); }
        .status-line { position: absolute; top: 15px; left: 10%; right: 10%; height: 2px; background: #ddd; z-index: 0; }
        .contract-preview { background: #f8f9fa; border-left: 5px solid #0d6efd; padding: 20px; font-family: 'Courier New', Courier, monospace; font-size: 0.9rem; }
    </style>
</head>
<body class="bg-light">

    <main class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="#" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Volver a solicitudes</a>
                <h2 class="fw-bold">Solicitud #ORD-8829</h2>
            </div>
            <span class="badge bg-warning text-dark fs-6 p-2">Estado: Pendiente</span>
        </div>

        <!-- Rastreador de Estado -->
        <div class="card shadow-sm border-0 p-4 mb-4">
            <div class="status-tracker">
                <div class="status-line"></div>
                <div class="status-step active">
                    <div class="status-dot"></div>
                    <span>Nueva</span>
                </div>
                <div class="status-step">
                    <div class="status-dot"></div>
                    <span>En Proceso</span>
                </div>
                <div class="status-step">
                    <div class="status-dot"></div>
                    <span>Completada</span>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Información del Cliente y Servicio -->
            <div class="col-md-7">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Detalles del Servicio</h5>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Servicio:</div>
                            <div class="col-sm-8 fw-bold">Limpieza de Oficinas Profesional</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Cliente:</div>
                            <div class="col-sm-8">Juan Pérez <span class="badge bg-light text-dark border">Ver Perfil</span></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Fecha Programada:</div>
                            <div class="col-sm-8 fw-bold text-primary">25 de Octubre, 2023 - 09:00 AM</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Ubicación:</div>
                            <div class="col-sm-8"><i class="bi bi-geo-alt"></i> Calle 123 #45-67, Edificio Horizonte</div>
                        </div>
                    </div>
                </div>

                <!-- AQUÍ ESTÁ EL DOCUMENTO/CONTRATO -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Contrato de Servicio</h5>
                            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> Descargar PDF</button>
                        </div>
                        <div class="contract-preview">
                            <p><strong>CONTRATO DE PRESTACIÓN DE SERVICIOS</strong></p>
                            <p>Entre el Cliente <strong>JUAN PÉREZ</strong> y el Proveedor <strong>LIMPIEZA & ORDEN S.A.</strong>...</p>
                            <p>El cliente acepta el pago de <strong>$150.00</strong> por la jornada de limpieza...</p>
                            <hr>
                            <p class="mb-0 text-success"><i class="bi bi-patch-check-fill"></i> Firmado digitalmente el 20/10/2023</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="col-md-5">
                <div class="card shadow-sm border-0 p-4 sticky-top" style="top: 20px;">
                    <h5 class="fw-bold mb-4">Acciones de Gestión</h5>
                    
                    <button class="btn btn-success w-100 mb-3 py-2">
                        <i class="bi bi-play-fill"></i> Iniciar Servicio (En Proceso)
                    </button>
                    
                    <button class="btn btn-primary w-100 mb-3 py-2">
                        <i class="bi bi-chat-left-text"></i> Enviar Mensaje al Cliente
                    </button>

                    <button class="btn btn-outline-danger w-100 py-2">
                        <i class="bi bi-x-circle"></i> Cancelar Solicitud
                    </button>

                    <div class="alert alert-info mt-4 small">
                        <i class="bi bi-info-circle"></i> Recuerda que al iniciar el servicio, el cliente será notificado y el contrato entra en vigencia total.
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>