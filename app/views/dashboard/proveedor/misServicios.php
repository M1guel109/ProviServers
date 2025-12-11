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
        <section id="tabla-arriba" class="mt-3">
            <table id="tabla-1" class="display nowrap">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Nombre del servicio</th>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <th>Disponibilidad</th>
                        <th>Estado publicación</th>
                        <th>Fecha de publicación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-servicios">
                    <?php if (!empty($datos)) : ?>
                        <?php foreach ($datos as $fila) : ?>
                            <?php
                            $estado = $fila['estado_publicacion'] ?? 'pendiente';

                            // Texto y estilos por estado
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
                            ?>
                            <tr>
                                <!-- Imagen -->
                                <td>
                                    <?php if (!empty($fila['servicio_imagen'])): ?>
                                        <img src="<?= BASE_URL ?>/public/uploads/servicios/<?= htmlspecialchars($fila['servicio_imagen']) ?>"
                                            alt="Imagen del servicio" width="60" height="60"
                                            style="object-fit: cover; border-radius: 8px;">
                                    <?php else: ?>
                                        <span class="text-muted">Sin imagen</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Nombre -->
                                <td><?= htmlspecialchars($fila['servicio_nombre'] ?? '') ?></td>

                                <!-- Categoría -->
                                <td><?= htmlspecialchars($fila['categoria_nombre'] ?? 'Sin categoría') ?></td>

                                <!-- Descripción -->
                                <td><?= htmlspecialchars($fila['servicio_descripcion'] ?? 'Sin descripción') ?></td>

                                <!-- Disponibilidad (on/off del servicio) -->
                                <td>
                                    <?php if ($disponible): ?>
                                        <span class="badge-disponibilidad badge-disponible">
                                            Disponible
                                        </span>
                                    <?php else: ?>
                                        <span class="badge-disponibilidad badge-no-disponible">
                                            No disponible
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Estado publicación -->
                                <td>
                                    <span class="<?= $claseEstado ?>">
                                        <?= $textoEstado ?>
                                    </span>
                                </td>

                                <!-- Fecha publicación -->
                                <td><?= htmlspecialchars($fila['publicacion_created_at'] ?? '') ?></td>

                                <!-- Acciones -->
                                <td>
                                    <div class="action-buttons">
                                        <!-- Ver detalle (luego puedes conectarlo a un modal o ficha) -->
                                        <a href="#"
                                            class="btn-action btn-view"
                                            title="Ver detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <!-- Editar servicio:
                                             Permitimos editar si está PENDIENTE o RECHAZADO -->
                                        <?php if (in_array($estado, ['pendiente', 'rechazada'], true)) : ?>
                                            <a href="<?= BASE_URL ?>/proveedor/editar-servicio?id=<?= $fila['servicio_id'] ?>"
                                                class="btn-action btn-edit"
                                                title="<?= $estado === 'rechazada' ? 'Editar y reenviar a revisión' : 'Editar servicio' ?>">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Eliminar servicio (reutilizando controlador actual) -->
                                        <a href="<?= BASE_URL ?>/proveedor/guardar-servicio?accion=eliminar&id=<?= $fila['servicio_id'] ?>"
                                            class="btn-action btn-delete"
                                            title="Eliminar servicio">
                                            <i class="bi bi-trash3"></i>
                                        </a>

                                        <!-- Opcional: Pausar / reactivar si está publicado (placeholder) -->
                                        <?php if ($estado === 'activa'): ?>
                                            <button type="button"
                                                class="btn-action btn-pause"
                                                title="Pausar publicación (dejar de mostrar temporalmente)">
                                                <i class="bi bi-pause-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <h5 class="text-muted mb-0">No tienes servicios publicados aún</h5>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
