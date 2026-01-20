<?php
// Validar sesión de proveedor
require_once BASE_PATH . '/app/helpers/session_proveedor.php';

// Enlazar controlador para obtener solicitudes
require_once BASE_PATH . '/app/controllers/proveedorController.php';

// Modelos necesarios
require_once BASE_PATH . '/app/models/servicio.php';
require_once BASE_PATH . '/app/models/solicitud.php';

// Obtener el ID del proveedor desde la sesión
$proveedorId = $_SESSION['user']['id'];

// Instanciar modelo y obtener datos reales
$solicitudModel = new Solicitud();
$solicitudes = $solicitudModel->listarPorProveedor($proveedorId);




// Cálculos para las tarjetas de estadísticas
$totalNuevas = count($solicitudes);

// Corregimos la lógica de filtros para evitar errores de índices inexistentes
$totalUrgentes = count(array_filter($solicitudes, function($s) {
    // Si no existe 'urgencia' o 'prioridad', por defecto es 'baja'
    $valor = $s['urgencia'] ?? $s['prioridad'] ?? 'baja';
    return strtolower($valor) === 'alta';
}));

$totalHoy = count(array_filter($solicitudes, function($s) {
    if (empty($s['fecha_preferida'])) return false;
    return date('Y-m-d', strtotime($s['fecha_preferida'])) === date('Y-m-d');
}));
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Nuevas Solicitudes</title>

    <!-- Css DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos generales y específicos -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardTable.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/nuevasSolicitudes.css">
</head>

<body>
    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_proveedor.php'; ?>

    <main class="contenido">
        <!-- HEADER -->
        <?php include_once __DIR__ . '/../../layouts/header_proveedor.php'; ?>

        <!-- Título -->
        <section id="titulo-principal" class="d-flex justify-content-between align-items-start flex-wrap p-4">
            <div>
                <h1 class="mb-1">Nuevas Solicitudes</h1>
                <p class="text-muted mb-0">
                    Gestiona las solicitudes de servicio que han realizado los clientes.
                </p>
            </div>
        </section>

        <!-- Tarjetas de estadísticas -->
        <section id="estadisticas-solicitudes" class="d-flex gap-3 px-4">
            <div class="tarjeta-estadistica shadow-sm p-3 bg-white rounded flex-fill">
                <i class="bi bi-inbox icono-estadistica text-primary"></i>
                <div class="valor-estadistica fs-2 fw-bold"><?= $totalNuevas ?></div>
                <div class="etiqueta-estadistica text-muted">Solicitudes Nuevas</div>
            </div>

            <div class="tarjeta-estadistica shadow-sm p-3 bg-white rounded flex-fill border-start border-danger border-4">
                <i class="bi bi-exclamation-triangle icono-estadistica text-danger"></i>
                <div class="valor-estadistica fs-2 fw-bold"><?= $totalUrgentes ?></div>
                <div class="etiqueta-estadistica text-muted">Urgentes (Alta)</div>
            </div>

            <div class="tarjeta-estadistica shadow-sm p-3 bg-white rounded flex-fill">
                <i class="bi bi-calendar-check icono-estadistica text-success"></i>
                <div class="valor-estadistica fs-2 fw-bold"><?= $totalHoy ?></div>
                <div class="etiqueta-estadistica text-muted">Para Hoy</div>
            </div>
        </section>

        <!-- Tabla de solicitudes -->
        <section id="tabla-solicitudes" class="mt-4 px-4 pb-5">
            <div class="card shadow-sm border-0 p-3">
                <table id="tabla-1" class="display nowrap w-100">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Servicio</th>
                            <th>Fecha Preferida</th>
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
                                            <div class="fw-bold"><?= htmlspecialchars($solicitud['nombre_cliente'] ?? 'Cliente Desconocido') ?></div>
                                            <small class="text-muted">
                                                <i class="bi bi-telephone"></i> <?= htmlspecialchars($solicitud['telefono_cliente'] ?? 'N/A') ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="info-servicio">
                                            <div class="fw-medium"><?= htmlspecialchars($solicitud['servicio_nombre'] ?? $solicitud['publicacion_titulo'] ?? 'Servicio') ?></div>
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                Recibida: <?= isset($solicitud['created_at']) ? date('d/m/y', strtotime($solicitud['created_at'])) : 'N/A' ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-nowrap">
                                            <i class="bi bi-calendar3"></i> 
                                            <?= !empty($solicitud['fecha_preferida']) ? date('d/m/Y', strtotime($solicitud['fecha_preferida'])) : 'Sin fecha' ?>
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-clock"></i> <?= htmlspecialchars($solicitud['franja_horaria'] ?? 'N/A') ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                            $estado = strtolower($solicitud['estado'] ?? 'pendiente');
                                            $badgeEstado = ($estado === 'pendiente') ? 'bg-secondary' : 'bg-success';
                                        ?>
                                        <span class="badge <?= $badgeEstado ?> text-capitalize"><?= $estado ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-primary" title="Ver Detalle" onclick='verDetalle(<?= json_encode($solicitud) ?>)'>
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a href="<?= BASE_URL ?>/proveedor/aceptar-solicitud?id=<?= $solicitud['id'] ?>" class="btn btn-sm btn-outline-success" title="Aceptar">
                                                <i class="bi bi-check-lg"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/proveedor/rechazar-solicitud?id=<?= $solicitud['id'] ?>" class="btn btn-sm btn-outline-danger" title="Rechazar" onclick="return confirm('¿Rechazar esta solicitud?')">
                                                <i class="bi bi-x-lg"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tabla-1').DataTable({
                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });
        });

        function verDetalle(data) {
            console.log("Detalles de la solicitud:", data);
            // Implementar modal aquí
        }
    </script>
</body>
</html>