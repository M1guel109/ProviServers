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
        <section id="cards-publicaciones" class="mt-3">

            <?php if (empty($publicaciones)) : ?>
                <div class="empty-state">
                    <h5 class="text-muted mb-1">Todavía no tienes publicaciones</h5>
                    <p class="text-muted mb-0" style="font-size: 0.95rem;">
                        Crea un servicio desde
                        <a href="<?= BASE_URL ?>/proveedor/registrar-servicio">“Registrar servicio”</a>
                        y se generará una publicación pendiente de aprobación.
                    </p>
                </div>
            <?php else : ?>

                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">

                    <?php foreach ($publicaciones as $pub) : ?>
                        <?php
                        $estado = $pub['estado_publicacion'] ?? 'pendiente';

                        // Badge / texto por estado
                        switch ($estado) {
                            case 'pendiente':
                                $badgeClass  = 'bg-warning text-dark';
                                $estadoTexto = 'Pendiente de aprobación';
                                break;
                            case 'aprobado':
                                $badgeClass  = 'bg-success';
                                $estadoTexto = 'Publicada';
                                break;
                            case 'pausada':
                                $badgeClass  = 'bg-info text-dark';
                                $estadoTexto = 'Pausada';
                                break;
                            case 'rechazada':
                                $badgeClass  = 'bg-danger';
                                $estadoTexto = 'Rechazada';
                                break;
                            default:
                                $badgeClass  = 'bg-secondary';
                                $estadoTexto = ucfirst((string)$estado);
                                break;
                        }

                        $titulo   = (string)($pub['titulo'] ?? '');
                        $servName = (string)($pub['servicio_nombre'] ?? 'Sin nombre');
                        $catName  = (string)($pub['categoria_nombre'] ?? 'Sin categoría');

                        $precio = isset($pub['precio']) ? number_format((float)$pub['precio'], 2) : '0.00';
                        $fecha  = (string)($pub['publicacion_created_at'] ?? '');

                        $servicioId = (int)($pub['servicio_id'] ?? 0);

                        // Opcional: descripción corta del título (si quieres recortar)
                        $tituloShort = $titulo;
                        if (mb_strlen($tituloShort) > 60) $tituloShort = mb_substr($tituloShort, 0, 60) . '...';
                        ?>

                        <div class="col">
                            <div class="card card-publicacion h-100 border-0 shadow-sm">

                                <!-- Barra superior como tus servicios -->
                                <div class="card-servicio-topbar"></div>

                                <div class="card-body">

                                    <!-- Estado + Precio -->
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($estadoTexto) ?></span>
                                        <span class="badge bg-dark">$ <?= $precio ?></span>
                                    </div>

                                    <!-- Título publicación -->
                                    <h5 class="card-title fw-bold mb-2">
                                        <?= htmlspecialchars($tituloShort) ?>
                                    </h5>

                                    <!-- Servicio base -->
                                    <div class="text-muted small mb-2">
                                        <i class="bi bi-box-seam"></i>
                                        <strong>Servicio base:</strong> <?= htmlspecialchars($servName) ?>
                                    </div>

                                    <!-- Categoría -->
                                    <div class="text-muted small mb-3">
                                        <i class="bi bi-tag"></i>
                                        <strong>Categoría:</strong> <?= htmlspecialchars($catName) ?>
                                    </div>

                                    <!-- Fecha -->
                                    <div class="meta-row text-muted small">
                                        <i class="bi bi-calendar3"></i>
                                        <span>Creada: <?= htmlspecialchars($fecha) ?></span>
                                    </div>

                                </div>

                                <!-- Acciones -->
                                <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                    <div class="d-flex gap-2 flex-wrap">

                                        <!-- Ver detalle (placeholder) -->
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary flex-fill"
                                            title="Ver detalle (pendiente de conectar)">
                                            <i class="bi bi-eye"></i> Ver
                                        </button>

                                        <!-- Editar servicio base -->
                                        <a href="<?= BASE_URL ?>/proveedor/editar-servicio?id=<?= $servicioId ?>"
                                            class="btn btn-sm btn-outline-success flex-fill"
                                            title="Editar servicio">
                                            <i class="bi bi-pencil-square"></i> Editar
                                        </a>

                                        <!-- Placeholder pause/reactivar según estado (si lo vas a usar luego) -->
                                        <?php if ($estado === 'aprobado') : ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" title="Pausar (placeholder)">
                                                <i class="bi bi-pause-circle"></i> Pausar
                                            </button>
                                        <?php elseif ($estado === 'pausada') : ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" title="Reactivar (placeholder)">
                                                <i class="bi bi-play-circle"></i> Reactivar
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