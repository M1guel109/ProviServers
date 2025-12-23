<?php
// Requerir la sesión de administrador
require_once BASE_PATH . '/app/helpers/session_admin.php';

// Enlazamos el controlador de Servicios (Donde asumimos está mostrarServiciosPendientes)
require_once BASE_PATH . '/app/controllers/proveedorController.php';

// Llamamos la función específica que consulta los datos de los servicios pendientes
$datos = mostrarservicios();

// NOTA: Asumiendo que BASE_URL y otras variables globales están definidas.

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Moderación de Servicios</title>

    <!-- Css DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        xintegrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- SweetAlert2 CSS (AÑADIDO) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- tu css -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardTable.css">

    <!-- Iconos de Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <!-- SIDEBAR (lateral izquierdo) -->
    <?php
    // Asumiendo que tienes un layout de sidebar para administrador
    include_once __DIR__ . '/../../layouts/sidebar_administrador.php';
    ?>


    <main class="contenido">
        <?php
        // Asumiendo que tienes un layout de header para administrador
        include_once __DIR__ . '/../../layouts/header_administrador.php';
        ?>

        <!-- Sección de Título y Breadcrumb -->
        <section id="titulo-principal" class="mb-4">
            <div class="row align-items-start">

                <div class="col-md-8 d-flex flex-column">

                    <div>
                        <h1 class="mb-1">Moderación de Servicios</h1>
                        <p class="text-muted mb-0">
                            Revisa y aprueba los servicios creados por los proveedores antes de su publicación.
                        </p>
                    </div>

                </div>

                <div class="col-md-4 d-flex justify-content-end align-items-start">

                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0 mt-2">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Moderación</li>
                        </ol>
                    </nav>

                </div>

            </div>
        </section>


        <!-- Contenido Principal: Tabla de Moderación -->
        <section id="tabla-moderacion" class="card shadow p-4">

            <div class="table-responsive">
                <table id="tabla" class="display nowrap table table-hover w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título del Servicio</th>
                            <th>Proveedor</th>
                            <th>Categoría</th>
                            <th>Fecha Creación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-servicios-moderacion">
                        <?php if (!empty($datos)) : ?>

                            <?php foreach ($datos as $servicio) : ?>

                                <tr>
                                    <td><?= htmlspecialchars($servicio['id']) ?></td>
                                    <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                                    <td><?= htmlspecialchars($servicio['proveedor_nombre']) ?></td>
                                    <td><?= htmlspecialchars($servicio['categoria_nombre']) ?></td>
                                    <td><?= htmlspecialchars($servicio['created_at']) ?></td>

                                    <td>
                                        <?php
                                        $estado = $servicio['publicacion_estado'] ?? 'pendiente'; // valor por defecto

                                        switch ($estado) {
                                            case 'aprobado':
                                                $badgeClass = 'bg-success';
                                                $icon = 'bi-check-circle';
                                                $texto = 'Aprobado';
                                                break;

                                            case 'rechazado':
                                                $badgeClass = 'bg-danger';
                                                $icon = 'bi-x-circle';
                                                $texto = 'Rechazado';
                                                break;

                                            case 'pendiente':
                                            default:
                                                $badgeClass = 'bg-warning text-dark';
                                                $icon = 'bi-clock';
                                                $texto = 'Pendiente';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <i class="bi <?= $icon ?>"></i> <?= $texto ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="action-buttons">
                                            <!-- Enlace para ver detalle del servicio -->
                                            <a href="#"
                                                class="btn-action btn-view"
                                                title="Revisar detalles">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <!-- Enlace para Aprobar (dispara SweetAlert en JS) -->
                                            <a href="#"
                                                class="btn-action btn-approve"
                                                data-id="<?= htmlspecialchars($servicio['id']) ?>">
                                                <i class="bi bi-check-circle"></i>
                                            </a>

                                            <!-- Enlace para Rechazar (dispara SweetAlert en JS) -->
                                            <a href="#"
                                                class="btn-action btn-reject"
                                                data-id="<?= htmlspecialchars($servicio['id']) ?>">
                                                <i class="bi bi-x-circle"></i>
                                            </a>

                                        </div>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <h2>No hay servicios pendientes de moderación.</h2>
                                    <p>¡El control de calidad está al día!</p>
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>
            </div>

        </section>

    </main>



    <footer>
        <!-- Enlaces / Información -->
    </footer>

        <!-- apexcharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Datatables -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- SweetAlert2 JS (AÑADIDO) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Exponer BASE_URL a JavaScript (AÑADIDO) -->
    <script>
        // Define la variable global BASE_URL para que el JS pueda usarla
        const BASE_URL = "<?= BASE_URL ?>";
    </script>

    <!-- tu javaScript (asumiendo que estos archivos existen) -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/moderacionServicio.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/app.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
</body>

</html>