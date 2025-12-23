<?php
// Validar sesión de proveedor
require_once BASE_PATH . '/app/helpers/session_proveedor.php';

// Modelo de publicaciones
require_once BASE_PATH . '/app/models/Publicacion.php';

$usuarioId = $_SESSION['user']['id'] ?? null;
$publicaciones = [];

if ($usuarioId) {
    $publicacionModel = new Publicacion();
    $publicaciones = $publicacionModel->listarPorProveedorUsuario((int)$usuarioId);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Mis publicaciones</title>

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

        <!-- Título principal -->
        <section id="titulo-principal" class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h1 class="mb-1">Mis publicaciones</h1>
                <p class="text-muted mb-0">
                    Aquí ves el estado de las publicaciones que los clientes podrán encontrar en la plataforma:
                    pendientes de aprobación, activas, pausadas o rechazadas.
                </p>
            </div>

            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol id="breadcrumb" class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/proveedor/dashboard">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Mis publicaciones</li>
                </ol>
            </nav>
        </section>

        <!-- Tabla de publicaciones -->
        <section id="tabla-arriba" class="mt-3">
            <table id="tabla-publicaciones" class="display nowrap">
                <thead>
                    <tr>
                        <th>Título publicación</th>
                        <th>Servicio base</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Creada el</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($publicaciones)) : ?>
                        <?php foreach ($publicaciones as $pub) : ?>
                            <?php
                            $estado = $pub['estado_publicacion'] ?? '';
                            $badgeClass = 'bg-secondary';
                            $estadoTexto = ucfirst($estado);

                            switch ($estado) {
                                case 'pendiente':
                                    $badgeClass = 'bg-warning text-dark';
                                    $estadoTexto = 'Pendiente de aprobación';
                                    break;
                                case 'activa':
                                    $badgeClass = 'bg-success';
                                    $estadoTexto = 'Publicada';
                                    break;
                                case 'pausada':
                                    $badgeClass = 'bg-info text-dark';
                                    $estadoTexto = 'Pausada';
                                    break;
                                case 'rechazada':
                                    $badgeClass = 'bg-danger';
                                    $estadoTexto = 'Rechazada';
                                    break;
                            }

                            $precio = isset($pub['precio']) ? number_format((float)$pub['precio'], 2) : '0.00';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($pub['titulo'] ?? '') ?></td>
                                <td><?= htmlspecialchars($pub['servicio_nombre'] ?? 'Sin nombre') ?></td>
                                <td><?= htmlspecialchars($pub['categoria_nombre'] ?? 'Sin categoría') ?></td>
                                <td>$ <?= $precio ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= $estadoTexto ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($pub['publicacion_created_at'] ?? '') ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- Ver detalle (placeholder, luego lo conectas) -->
                                        <a href="#" class="btn-action btn-view" title="Ver detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <!-- Editar servicio base (por ahora voy directo al editar servicio) -->
                                        <a href="<?= BASE_URL ?>/proveedor/editar-servicio?id=<?= $pub['servicio_id'] ?>"
                                            class="btn-action btn-edit"
                                            title="Editar servicio">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <!-- A futuro: botones de pausar / reactivar publicación -->
                                        <!--
                                        <?php if ($estado === 'activa') : ?>
                                            <a href="#" class="btn-action btn-warning" title="Pausar publicación">
                                                <i class="bi bi-pause-circle"></i>
                                            </a>
                                        <?php elseif ($estado === 'pausada') : ?>
                                            <a href="#" class="btn-action btn-success" title="Reactivar publicación">
                                                <i class="bi bi-play-circle"></i>
                                            </a>
                                        <?php endif; ?>
                                        -->
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <h5 class="text-muted mb-0">Todavía no tienes publicaciones</h5>
                                <p class="text-muted mb-0" style="font-size: 0.9rem;">
                                    Crea un servicio desde
                                    <a href="<?= BASE_URL ?>/proveedor/registrar-servicio">“Registrar servicio”</a>
                                    y se generará una publicación pendiente de aprobación.
                                </p>
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

    <!-- JS dashboard (sidebar, etc.) -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/app.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>

    <script>
        // Inicializar DataTable
        document.addEventListener('DOMContentLoaded', function() {
            $('#tabla-publicaciones').DataTable({
                responsive: true,
                // language: {
                //     url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                // }
            });

            // Submenús del sidebar (si los usas)
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
