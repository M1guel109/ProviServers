<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';

// --- CAMBIO CLAVE AQUÍ ---
// En lugar de llamar al Controlador (que nos devuelve JSON por error),
// llamamos directamente al Modelo para pedir los datos.
require_once BASE_PATH . '/app/models/categoria.php';

// Validamos que venga el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . BASE_URL . '/admin/consultar-categorias');
    exit;
}

$id = $_GET['id'];

// Instanciamos el Modelo directamente
$objCategoria = new Categoria();
$categoria = $objCategoria->mostrarId($id);

// Si no existe la categoría, devolvemos al listado
if (!$categoria) {
    header('Location: ' . BASE_URL . '/admin/consultar-categorias');
    exit;
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Editar Categoría</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/registrarUsuario.css">
</head>

<body>
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_administrador.php';
    ?>


    <main class="contenido">
        <?php
        include_once __DIR__ . '/../../layouts/header_administrador.php';
        ?>


        <section id="titulo-principal" class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h1 class="mb-1">Editar Categoría</h1>
                <p class="text-muted mb-0">
                    Modifica los datos y el ícono de la categoría de servicio.
                </p>
            </div>

            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol id="breadcrumb" class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Panel Principal</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/categorias">Gestión de Categorías</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Editar Categoría</li>
                </ol>
            </nav>
        </section>

        <section id="formulario-categorias">
            <div class="contenedor-formulario">
                <form action="<?= BASE_URL ?>/admin/actualizar-categoria" method="post" class="formulario-usuario" enctype="multipart/form-data">
                    <div class="row g-3">

                        <input type="hidden" name="accion" value="actualizar">
                        <input type="hidden" name="id" value="<?= $categoria['id'] ?>">
                        <input type="hidden" name="icono_url_actual" value="<?= $categoria['icono_url'] ?>">

                        <div class="seccion-foto">
                            <div class="tarjeta-foto">
                                <div class="foto-perfil">
                                    <img src="<?= BASE_URL ?>/public/uploads/categorias/<?= $categoria['icono_url'] ?>" alt="Icono de Categoría" id="foto-preview" width="50">
                                </div>
                                <label for="icono-input" class="btn-agregar-foto">Cambiar Ícono</label>
                                <input type="file" id="icono-input" accept=".jpg, .png, .jpeg, .svg" style="display: none;" name="icono_url">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label for="nombre" class="form-label">Nombre de la Categoría</label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                placeholder="Ej: Plomería, Electricidad" required
                                value="<?= $categoria['nombre'] ?>">
                        </div>

                        <div class="col-md-12">
                            <label for="descripcion" class="form-label">Descripción Detallada</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="4"
                                placeholder="Describe brevemente qué tipo de servicios abarca esta categoría..." required><?= $categoria['descripcion'] ?></textarea>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary px-4">Actualizar Categoría</button>
                        </div>

                    </div>
                </form>
            </div>
        </section>


    </main>


    <footer>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/categoria.js"></script>

</body>

</html>