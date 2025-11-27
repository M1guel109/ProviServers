<?php
// (Opcional) validar sesión de proveedor, similar a session_admin.php
require_once BASE_PATH . '/app/helpers/session_proveedor.php';


// Enlazamos el controlador que tiene la función mostrarServicios()
require_once BASE_PATH . '/app/controllers/proveedorController.php';

// Modelo de categorías para mostrar el nombre en vez del id
require_once BASE_PATH . '/app/models/categoria.php';

// Llamamos la función del controlador (igual que con mostrarUsuarios())
$datos = mostrarServicios();

// Mapeo de id_categoria -> nombre categoría
$categoriaModel = new Categoria();
$categorias = $categoriaModel->mostrar();

$mapCategorias = [];
foreach ($categorias as $categoria) {
    $mapCategorias[$categoria['id']] = $categoria['nombre'];
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

    <!-- tu css (puedes usar el mismo de tablas del admin) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardTable.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/registrar-servicio.css">
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

        <!-- Secciones -->
        <!-- titulo -->
        <section id="titulo-principal" class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h1 class="mb-1">Mis Servicios</h1>
                <p class="text-muted mb-0">
                    Aquí puedes ver todos los servicios que has registrado. Usa las acciones disponibles para gestionarlos.
                </p>
            </div>

            <!-- Si luego quieres generar reportes PDF, aquí puedes añadir un botón similar al de usuarios -->
            <!--
            <a href="<?= BASE_URL ?>/proveedor/reporte?tipo=servicios" target="_blank" class="btn btn-primary mt-3">
                <i class="bi bi-file-earmark-pdf-fill"></i> Generar Reporte PDF
            </a>
            -->
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol id="breadcrumb" class="breadcrumb mb-0"></ol>
            </nav>
        </section>

        <!-- Tabla de servicios -->
        <section id="tabla-arriba" class="mt-3">
            <table id="tabla-1" class="display nowrap">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Nombre del servicio</th>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <th>Disponibilidad</th>
                        <th>Fecha de creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-servicios">
                    <?php if (!empty($datos)) : ?>
                        <?php foreach ($datos as $servicio) : ?>
                            <tr>
                                <td>
                                    <?php if (!empty($servicio['imagen'])): ?>
                                        <img src="<?= BASE_URL ?>/public/uploads/servicios/<?= htmlspecialchars($servicio['imagen']) ?>"
                                            alt="Imagen del servicio" width="60" height="60"
                                            style="object-fit: cover; border-radius: 8px;">
                                    <?php else: ?>
                                        <span class="text-muted">Sin imagen</span>
                                    <?php endif; ?>
                                </td>

                                <td><?= htmlspecialchars($servicio['nombre']) ?></td>

                                <td>
                                    <?php
                                    $nombreCategoria = $mapCategorias[$servicio['id_categoria']] ?? 'Sin categoría';
                                    echo htmlspecialchars($nombreCategoria);
                                    ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($servicio['descripcion'] ?? 'Sin descripción') ?>
                                </td>

                                <td>
                                    <?php if ($servicio['disponibilidad']) : ?>
                                        <span>Disponible</span>
                                    <?php else: ?>
                                        <span>No disponible</span>
                                    <?php endif; ?>
                                </td>

                                <td><?= htmlspecialchars($servicio['created_at']) ?></td>

                                <td>
                                    <div class="action-buttons">
                                        <!-- Ver detalle (por ahora placeholder) -->
                                        <a href="#" class="btn-action btn-view" title="Ver detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <!-- Editar servicio (cuando tengas vista/route de edición) -->
                                        <a href="<?= BASE_URL ?>/proveedor/editar-servicio?id=<?= $servicio['id'] ?>"
                                            class="btn-action btn-edit"
                                            title="Editar servicio">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>


                                        <!-- Eliminar servicio: reutilizamos proveedorController (GET, accion=eliminar) -->
                                        <a href="<?= BASE_URL ?>/proveedor/guardar-servicio?accion=eliminar&id=<?= $servicio['id'] ?>"
                                            class="btn-action btn-delete"
                                            title="Eliminar servicio">
                                            <i class="bi bi-trash3"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <h5 class="text-muted mb-0">No hay servicios registrados</h5>
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

    <!-- tu JavaScript (si ya inicializas DataTables ahí, no necesitas más) -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/app.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>

    <script>
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