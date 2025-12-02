<?php
// Validar sesión de proveedor
require_once BASE_PATH . '/app/helpers/session_proveedor.php';

// Enlazar controlador para obtener solicitudes
require_once BASE_PATH . '/app/controllers/proveedorController.php';

// Modelo de servicios para mostrar nombre del servicio
require_once BASE_PATH . '/app/models/servicio.php';

// Obtener las solicitudes del proveedor
// Aquí deberías tener una función que obtenga las solicitudes
// $solicitudes = obtenerSolicitudesProveedor();

// Datos de ejemplo (reemplaza con tu función real)
$solicitudes = [
    [
        'id' => 1,
        'nombre_cliente' => 'María González',
        'email_cliente' => 'maria.gonzalez@email.com',
        'telefono_cliente' => '+57 300 123 4567',
        'servicio_solicitado' => 'Reparación de tuberías',
        'fecha_solicitud' => '2024-11-25 10:30:00',
        'fecha_preferida' => '2024-11-28',
        'hora_preferida' => '10:00 AM',
        'direccion' => 'Calle 123 #45-67, Bogotá',
        'descripcion' => 'Necesito reparación urgente de tubería que presenta fuga en el baño principal.',
        'estado' => 'pendiente',
        'urgencia' => 'alta'
    ],
    [
        'id' => 2,
        'nombre_cliente' => 'Carlos Rodríguez',
        'email_cliente' => 'carlos.r@email.com',
        'telefono_cliente' => '+57 310 987 6543',
        'servicio_solicitado' => 'Instalación eléctrica',
        'fecha_solicitud' => '2024-11-24 14:20:00',
        'fecha_preferida' => '2024-11-30',
        'hora_preferida' => '2:00 PM',
        'direccion' => 'Carrera 7 #89-12, Bogotá',
        'descripcion' => 'Instalación de nuevos puntos eléctricos en sala y comedor.',
        'estado' => 'pendiente',
        'urgencia' => 'media'
    ],
    [
        'id' => 3,
        'nombre_cliente' => 'Ana Martínez',
        'email_cliente' => 'ana.martinez@email.com',
        'telefono_cliente' => '+57 320 456 7890',
        'servicio_solicitado' => 'Limpieza profunda',
        'fecha_solicitud' => '2024-11-23 09:15:00',
        'fecha_preferida' => '2024-11-27',
        'hora_preferida' => '9:00 AM',
        'direccion' => 'Avenida 15 #23-45, Bogotá',
        'descripcion' => 'Limpieza completa de apartamento de 3 habitaciones.',
        'estado' => 'pendiente',
        'urgencia' => 'baja'
    ]
];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Nuevas Solicitudes</title>

    <!-- Css DataTables Export -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.dataTables.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- CSS específico para solicitudes -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardTable.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/nuevasSolicitudes.css">
</head>

<body>
    <!-- SIDEBAR (lateral izquierdo) -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_proveedor.php';
    ?>

    <main class="contenido">
        <?php
        include_once __DIR__ . '/../../layouts/header_proveedor.php';
        ?>

        <!-- Título -->
        <section id="titulo-principal" class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h1 class="mb-1">Nuevas Solicitudes</h1>
                <p class="text-muted mb-0">
                    Gestiona las solicitudes de servicio que han realizado los clientes. Acepta, rechaza o responde a cada solicitud.
                </p>
            </div>

            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol id="breadcrumb" class="breadcrumb mb-0"></ol>
            </nav>
        </section>

        <!-- Tarjetas de estadísticas -->
        <section id="estadisticas-solicitudes">
            <div class="tarjeta-estadistica">
                <i class="bi bi-inbox icono-estadistica"></i>
                <div class="valor-estadistica"><?= count($solicitudes) ?></div>
                <div class="etiqueta-estadistica">Solicitudes Nuevas</div>
            </div>

            <div class="tarjeta-estadistica">
                <i class="bi bi-exclamation-triangle icono-estadistica urgente"></i>
                <div class="valor-estadistica">
                    <?= count(array_filter($solicitudes, fn($s) => $s['urgencia'] === 'alta')) ?>
                </div>
                <div class="etiqueta-estadistica">Urgentes</div>
            </div>

            <div class="tarjeta-estadistica">
                <i class="bi bi-calendar-check icono-estadistica"></i>
                <div class="valor-estadistica">
                    <?= count(array_filter($solicitudes, fn($s) => $s['fecha_preferida'] === date('Y-m-d'))) ?>
                </div>
                <div class="etiqueta-estadistica">Para Hoy</div>
            </div>
        </section>

        <!-- Tabla de solicitudes -->
        <section id="tabla-solicitudes" class="mt-4">
            <table id="tabla-1" class="display nowrap">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Servicio</th>
                        <th>Fecha Solicitada</th>
                        <th>Urgencia</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($solicitudes)) : ?>
                        <?php foreach ($solicitudes as $solicitud) : ?>
                            <tr>
                                <td>
                                    <div class="info-cliente">
                                        <div class="nombre-cliente">
                                            <?= htmlspecialchars($solicitud['nombre_cliente']) ?>
                                        </div>
                                        <div class="contacto-cliente">
                                            <i class="bi bi-telephone"></i>
                                            <?= htmlspecialchars($solicitud['telefono_cliente']) ?>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="info-servicio">
                                        <div class="nombre-servicio">
                                            <?= htmlspecialchars($solicitud['servicio_solicitado']) ?>
                                        </div>
                                        <div class="fecha-registro">
                                            Registrada: <?= date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])) ?>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="fecha-preferida">
                                        <i class="bi bi-calendar3"></i>
                                        <?= date('d/m/Y', strtotime($solicitud['fecha_preferida'])) ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i>
                                            <?= htmlspecialchars($solicitud['hora_preferida']) ?>
                                        </small>
                                    </div>
                                </td>

                                <td>
                                    <?php
                                    $urgenciaClass = '';
                                    $urgenciaTexto = '';
                                    switch($solicitud['urgencia']) {
                                        case 'alta':
                                            $urgenciaClass = 'urgencia-alta';
                                            $urgenciaTexto = 'Alta';
                                            break;
                                        case 'media':
                                            $urgenciaClass = 'urgencia-media';
                                            $urgenciaTexto = 'Media';
                                            break;
                                        case 'baja':
                                            $urgenciaClass = 'urgencia-baja';
                                            $urgenciaTexto = 'Baja';
                                            break;
                                    }
                                    ?>
                                    <span class="badge-urgencia <?= $urgenciaClass ?>">
                                        <?= $urgenciaTexto ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="badge-estado estado-pendiente">
                                        Pendiente
                                    </span>
                                </td>

                               <td>
    <div class="action-buttons">
        
        <!-- Ver detalle completo -->
        <button 
            class="btn-action btn-view" 
            title="Ver detalle"
            data-bs-toggle="modal" 
            data-bs-target="#modalDetalle"
            onclick='mostrarDetalle(<?= json_encode($solicitud) ?>)'>
            <i class="bi bi-eye"></i>
        </button>

        <!-- Aceptar solicitud -->
        <a href="<?= BASE_URL ?>/proveedor/aceptar-solicitud?id=<?= $solicitud['id'] ?>"
            class="btn-action btn-accept"
            title="Aceptar solicitud">
            <i class="bi bi-check-circle"></i>
        </a>

        <!-- Rechazar solicitud -->
        <a href="<?= BASE_URL ?>/proveedor/rechazar-solicitud?id=<?= $solicitud['id'] ?>"
            class="btn-action btn-reject"
            title="Rechazar solicitud"
            onclick="return confirm('¿Estás seguro de rechazar esta solicitud?')">
            <i class="bi bi-x-circle"></i>
        </a>

        <!-- Contactar cliente -->
        <a href="mailto:<?= htmlspecialchars($solicitud['email_cliente']) ?>"
            class="btn-action btn-contact"
            title="Contactar cliente">
            <i class="bi bi-envelope"></i>
        </a>

    </div>
</td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #94a3b8;"></i>
                                <h5 class="text-muted mt-3 mb-0">No hay solicitudes nuevas</h5>
                                <p class="text-muted">Las nuevas solicitudes de clientes aparecerán aquí.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </main>

    <!-- Modal para ver detalle completo -->
    <div class="modal fade" id="modalDetalle" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetalleLabel">
                        <i class="bi bi-file-text"></i> Detalle de Solicitud
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="detalle-solicitud">
                        <!-- Información del cliente -->
                        <div class="seccion-detalle">
                            <h6 class="titulo-seccion">
                                <i class="bi bi-person-circle"></i> Información del Cliente
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nombre:</strong> <span id="detalle-nombre"></span></p>
                                    <p><strong>Email:</strong> <span id="detalle-email"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Teléfono:</strong> <span id="detalle-telefono"></span></p>
                                    <p><strong>Dirección:</strong> <span id="detalle-direccion"></span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Información del servicio -->
                        <div class="seccion-detalle">
                            <h6 class="titulo-seccion">
                                <i class="bi bi-briefcase"></i> Información del Servicio
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Servicio:</strong> <span id="detalle-servicio"></span></p>
                                    <p><strong>Fecha preferida:</strong> <span id="detalle-fecha"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Hora preferida:</strong> <span id="detalle-hora"></span></p>
                                    <p><strong>Urgencia:</strong> <span id="detalle-urgencia"></span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="seccion-detalle">
                            <h6 class="titulo-seccion">
                                <i class="bi bi-chat-left-text"></i> Descripción del Problema
                            </h6>
                            <p id="detalle-descripcion" class="descripcion-texto"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" id="btn-aceptar-modal">
                        <i class=