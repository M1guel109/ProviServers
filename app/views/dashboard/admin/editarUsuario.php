<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
// Enlazamos la dependencia,en este caso el controlador que tiene la funcion de consulatar los datos
require_once BASE_PATH . '/app/controllers/adminController.php';

// llamamos la funcion especifica que exite en dicho controlador
$id = $_GET['id'];

// Llamamos la funcion especifica del controlador y le pasamoas los datos a una variable que podamos manipular en un archivo 
$usuario = mostrarUsuarioId($id);

// echo "<pre>";
// var_dump($usuario);
// echo "</pre>";
// exit;

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Plataforma de servicios locales</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- tu css -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/registrarUsuario.css">
</head>

<body>
    <!-- SIDEBAR (lateral izquierdo) -->


    <?php
    include_once __DIR__ . '/../../layouts/sidebar_administrador.php'
    ?>


    <main class="contenido">
        <?php
        include_once __DIR__ . '/../../layouts/header_administrador.php'
        ?>


        <!-- Secciones -->
        <!-- titulo -->
        <section id="titulo-principal" class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <h1 class="mb-1">Actualizar Usuario</h1>
                <p class="text-muted mb-0">
                    Modifica la información de un usuario registrado, incluyendo sus datos personales, credenciales y rol dentro de la plataforma.
                </p>
            </div>

            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol id="breadcrumb" class="breadcrumb mb-0"></ol>
            </nav>
        </section>

        <!-- Formualior admin -->
        <section id="formulario-usuarios">
            <div class="contenedor-formulario">
                <!-- Foto de perfil -->
                <div class="seccion-foto">
                    <div class="tarjeta-foto">
                        <div class="foto-perfil">
                            <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $usuario['foto'] ?>" alt="Foto de perfil" id="foto-preview">
                        </div>
                        <label for="foto-input" class="btn-agregar-foto">Agregar foto</label>
                        <input type="file" id="foto-input" accept="image/*" style="display: none;">
                    </div>
                </div>

                <!-- Formulario -->
                <form action="<?= BASE_URL ?>/admin/actualizar-usuario" method="post" class="formulario-usuario">
                    <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                    <input type="hidden" name="accion" value="actualizar">

                    <div class="row g-3">

                        <!-- Nombre -->
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="nombre" name="nombres"
                                placeholder="Ej: Juan Pérez" required value="<?= $usuario['nombres']?>">
                        </div>

                        <div class="col-md-6">
                            <label for="apellidos" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos"
                                placeholder="Ej: Gómez Rojas" required value="<?= $usuario['apellidos']?>">
                        </div>

                        <div class="col-md-6">
                            <label for="documento" class="form-label">Documento</label>
                            <input type="text" class="form-control" id="documento" name="documento"
                                placeholder="Ej: 1023456789" required value="<?= $usuario['documento']?>">
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="Ej: juan@gmail.com" required value="<?= $usuario['email'] ?>">
                        </div>

                        <!-- Contraseña -->
                        <!-- <div class="col-md-6">
                            <label for="clave" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="clave" name="clave" placeholder="••••••••"
                                required value="<?= $usuario['clave'] ?>">
                        </div> -->


                        <!-- Teléfono -->
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono"
                                placeholder="Ej: 3204567890" required value="<?= $usuario['telefono'] ?>">
                        </div>

                        <!-- Ubicación -->
                        <div class="col-md-6">
                            <label for="ubicacion" class="form-label">Ubicación</label>
                            <input type="text" class="form-control" id="ubicacion" name="ubicacion"
                                placeholder="Ej: Fusagasugá, Cundinamarca" required value="<?= $usuario['ubicacion'] ?>">
                        </div>

                        <!-- Rol -->
                        <div class="col-md-6">
                            <label for="rol" class="form-label">Rol</label>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="<?= $usuario['rol'] ?>"><?= $usuario['rol']?? '' ?></option>
                                <option value="admin">Administrador</option>
                                <option value="proveedor">Proveedor</option>
                                <option value="cliente">Cliente</option>
                            </select>
                        </div>

                        <!-- Botón -->
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary px-4">Actualizar Usuario</button>
                        </div>

                    </div>
                </form>
            </div>
        </section>


    </main>


    <footer>
        <!-- Enlaces / Información -->
    </footer>

    <!-- apexcharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- tu javaScript -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
</body>

</html>