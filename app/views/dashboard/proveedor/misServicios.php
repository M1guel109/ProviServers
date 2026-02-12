<?php
// Validar sesión de proveedor
require_once BASE_PATH . '/app/helpers/session_proveedor.php';

// Modelo de publicaciones
require_once BASE_PATH . '/app/models/Publicacion.php';

$usuarioId = $_SESSION['user']['id'] ?? null;
$datos = [];

if ($usuarioId) {
    $pubModel = new Publicacion();
    $datos = $pubModel->listarPorProveedorUsuario((int)$usuarioId);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Mis Servicios</title>

    <!-- Css DataTables Export -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.dataTables.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- css de tablas / dashboard -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardTable.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/registrar-servicio.css">

    <style>
        /* Estados de publicación */
        .estado-publicacion {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .estado-pendiente {
            background-color: #fff7e6;
            color: #b45309;
        }

        .estado-activa {
            background-color: #e6fffa;
            color: #047857;
        }

        .estado-rechazada {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .estado-pausada {
            background-color: #e5e7eb;
            color: #4b5563;
        }

        /* Disponibilidad del servicio */
        .badge-disponibilidad {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 500;
        }

        .badge-disponible {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-no-disponible {
            background-color: #fee2e2;
            color: #b91c1c;
        }
    </style>
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

        <!-- Sección título -->
        <section id="titulo-principal" class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h1 class="mb-1">Mis Servicios</h1>
                <p class="text-muted mb-0">
                    Aquí puedes ver tus servicios publicados, su estado y gestionar sus acciones.
                </p>
            </div>

            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol id="breadcrumb" class="breadcrumb mb-0"></ol>
            </nav>

            <a href="<?= BASE_URL ?>/proveedor/reporte?tipo=serviciosProveedor" target="_blank" class="btn btn-primary mt-3">
                <i class="bi bi-file-earmark-pdf-fill"></i> Generar Reporte PDF
            </a>
        </section>

        <!-- Tabla de servicios / publicaciones -->
<!-- LISTADO EN TARJETAS -->
<section id="cards-servicios" class="mt-3">
    

    <?php if (empty($datos)) : ?>
        <div class="empty-state">
            <h5 class="text-muted mb-1">No tienes servicios publicados aún</h5>
            <p class="text-muted mb-0">Crea tu primer servicio para empezar a recibir solicitudes.</p>
        </div>
    <?php else : ?>

        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">

            <?php foreach ($datos as $fila) : ?>
                <?php
                $estado = $fila['estado_publicacion'] ?? 'pendiente';

                // Texto y estilos por estado (igual que tu lógica)
                switch ($estado) {
                    case 'aprobado':
                        $textoEstado = 'Publicado';
                        $claseEstado = 'estado-publicacion estado-activa';
                        break;
                    case 'rechazada':
                        $textoEstado = 'Rechazado';
                        $claseEstado = 'estado-publicacion estado-rechazada';
                        break;
                    case 'pausada':
                        $textoEstado = 'Pausado';
                        $claseEstado = 'estado-publicacion estado-pausada';
                        break;
                    case 'pendiente':
                    default:
                        $textoEstado = 'Pendiente de aprobación';
                        $claseEstado = 'estado-publicacion estado-pendiente';
                        break;
                }

                $disponible = (int)($fila['servicio_disponible'] ?? 0) === 1;

                $img = $fila['servicio_imagen'] ?? '';
                $imgUrl = !empty($img)
                    ? (BASE_URL . '/public/uploads/servicios/' . $img)
                    : (BASE_URL . '/public/assets/img/default_service.png'); // crea este placeholder si quieres

                $servicioId = (int)($fila['servicio_id'] ?? 0);

                // Descripción corta
                $desc = trim((string)($fila['servicio_descripcion'] ?? 'Sin descripción'));
                if (mb_strlen($desc) > 120) $desc = mb_substr($desc, 0, 120) . '...';

                $fechaPub = $fila['publicacion_created_at'] ?? '';
                ?>

                <div class="col">
                    <div class="card card-servicio h-100 border-0 shadow-sm">

                        <!-- Imagen -->
                        <div class="card-servicio-img">
                            <img src="<?= htmlspecialchars($imgUrl) ?>"
                                 alt="Imagen del servicio"
                                 onerror="this.onerror=null; this.src='<?= BASE_URL ?>/public/assets/img/default_service.png';">
                        </div>

                        <div class="card-body">
                            <!-- Estado + disponibilidad -->
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <span class="<?= $claseEstado ?>"><?= $textoEstado ?></span>

                                <?php if ($disponible): ?>
                                    <span class="badge-disponibilidad badge-disponible">Disponible</span>
                                <?php else: ?>
                                    <span class="badge-disponibilidad badge-no-disponible">No disponible</span>
                                <?php endif; ?>
                            </div>

                            <!-- Nombre -->
                            <h5 class="card-title fw-bold mb-1">
                                <?= htmlspecialchars($fila['servicio_nombre'] ?? '') ?>
                            </h5>

                            <!-- Categoría -->
                            <div class="text-muted small mb-2">
                                <i class="bi bi-tag"></i>
                                <?= htmlspecialchars($fila['categoria_nombre'] ?? 'Sin categoría') ?>
                            </div>

                            <!-- Descripción -->
                            <p class="card-text text-secondary small mb-3">
                                <?= htmlspecialchars($desc) ?>
                            </p>

                            <!-- Fecha publicación -->
                            <div class="meta-row text-muted small">
                                <i class="bi bi-calendar3"></i>
                                <span>Publicado: <?= htmlspecialchars($fechaPub) ?></span>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                            <div class="d-flex gap-2 flex-wrap">

                                <!-- Ver detalle (placeholder) -->
                                <a href="#"
                                   class="btn btn-sm btn-outline-primary flex-fill"
                                   title="Ver detalle">
                                    <i class="bi bi-eye"></i> Ver
                                </a>

                                <!-- Editar (solo pendiente o rechazada) -->
                                <?php if (in_array($estado, ['pendiente', 'rechazada'], true)) : ?>
                                    <a href="<?= BASE_URL ?>/proveedor/editar-servicio?id=<?= $servicioId ?>"
                                       class="btn btn-sm btn-outline-success flex-fill"
                                       title="<?= $estado === 'rechazada' ? 'Editar y reenviar a revisión' : 'Editar servicio' ?>">
                                        <i class="bi bi-pencil-square"></i> Editar
                                    </a>
                                <?php endif; ?>

                                <!-- Eliminar -->
                                <a href="<?= BASE_URL ?>/proveedor/guardar-servicio?accion=eliminar&id=<?= $servicioId ?>"
                                   class="btn btn-sm btn-outline-danger flex-fill"
                                   title="Eliminar servicio"
                                   onclick="return confirm('¿Eliminar este servicio?');">
                                    <i class="bi bi-trash3"></i> Eliminar
                                </a>

                                <!-- Opcional: Pausar (dejas tu placeholder; OJO: tu condición original decía 'activa' pero tu estado publicado es 'aprobado') -->
                                <?php if ($estado === 'aprobado') : ?>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary flex-fill"
                                            title="Pausar publicación (placeholder)">
                                        <i class="bi bi-pause-circle"></i> Pausar
                                    </button>
                                <?php endif; ?>

                            </div>
                        </div>

                    </div>
                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>



    </main>

    <footer>
        <!-- Enlaces / Información -->
    </footer>

    <!-- Datatables export -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.print.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- JS del dashboard -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/app.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>

    <script>
        // Inicializar DataTable
        $(document).ready(function() {
            $('#tabla-1').DataTable({
                responsive: true,
                // language: {
                //     url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                // }
            });
        });

        // Submenús del sidebar (si no lo tienes ya centralizado)
        document.addEventListener('DOMContentLoaded', function() {
            const toggleSubmenuButtons = document.querySelectorAll('.toggle-submenu');

            toggleSubmenuButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const contenedor = this.closest('.has-submenu');
                    if (!contenedor) return;

                    const submenu = contenedor.querySelector('.submenu');
                    contenedor.classList.toggle('active');

                    if (submenu) {
                        if (contenedor.classList.contains('active')) {
                            submenu.style.maxHeight = submenu.scrollHeight + 'px';
                        } else {
                            submenu.style.maxHeight = '0';
                        }
                    }
                });
            });
        });
    </script>

</body>

</html>
