<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';

// AQUÍ DEBERÍAS TRAER LAS CATEGORÍAS REALES DE TU BASE DE DATOS
// $categorias_bd = $categoriaController->obtenerTodas(); 
// Por ahora simulamos:
$categorias_bd = [
    ['id' => 1, 'nombre' => 'Plomería'],
    ['id' => 2, 'nombre' => 'Electricidad'],
    ['id' => 3, 'nombre' => 'Carpintería'],
    ['id' => 4, 'nombre' => 'Limpieza'],
    ['id' => 5, 'nombre' => 'Jardinería']
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Registrar Usuario</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/registrarUsuario.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar_administrador.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header_administrador.php'; ?>

        <section id="titulo-principal">
            <div class="row">
                <div class="col-md-8">
                    <h1 class="mb-1">Registrar Usuario</h1>
                    <p class="text-muted mb-0">Complete la información para dar de alta un nuevo usuario.</p>
                </div>
            </div>
        </section>

        <section id="formulario-usuarios">
            <div class="contenedor-formulario">
                <form action="<?= BASE_URL ?>/admin/guardar-usuario" method="post" class="formulario-usuario" enctype="multipart/form-data" id="formRegistro">
                    
                    <div class="seccion-foto mb-4">
                        <div class="tarjeta-foto">
                            <div class="foto-perfil">
                                <img src="<?= BASE_URL ?>/public/uploads/usuarios/default_user.png" alt="Foto de perfil" id="foto-preview">
                            </div>
                            <label for="foto-input" class="btn-agregar-foto">
                                <i class="bi bi-camera"></i> Subir foto
                            </label>
                            <input type="file" id="foto-input" accept=".jpg, .png, .jpeg" style="display: none;" name="foto">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12"><h6 class="text-primary border-bottom pb-2">1. Información Personal</h6></div>
                        
                        <div class="col-md-6">
                            <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombres" name="nombres" placeholder="Ej: Juan Camilo" required>
                        </div>
                        <div class="col-md-6">
                            <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="Ej: Pérez López" required>
                        </div>
                        <div class="col-md-6">
                            <label for="documento" class="form-label">No. Documento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="documento" name="documento" placeholder="Ej: 1012345678" required>
                        </div>
                        
                        <div class="col-12 mt-4"><h6 class="text-primary border-bottom pb-2">2. Datos de Contacto</h6></div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="correo@ejemplo.com" required>
                        </div>
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono / Celular <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Ej: 320..." required>
                        </div>
                        <div class="col-md-12">
                            <label for="ubicacion" class="form-label">Ubicación / Dirección <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ubicacion" name="ubicacion" placeholder="Ej: Fusagasugá, Barrio Centro" required>
                        </div>

                        <div class="col-12 mt-4"><h6 class="text-primary border-bottom pb-2">3. Seguridad de la Cuenta</h6></div>

                        <div class="col-md-6">
                            <label for="clave" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="clave" name="clave" placeholder="Crear contraseña segura">
                            <div class="form-text">Si se deja vacío, el sistema usará el No. de Documento como clave.</div>
                        </div>

                        <div class="col-12 mt-4"><h6 class="text-primary border-bottom pb-2">4. Rol en la Plataforma</h6></div>

                        <div class="col-md-12">
                            <label for="rol" class="form-label">Tipo de Usuario <span class="text-danger">*</span></label>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="">Seleccionar...</option>
                                <option value="cliente">Cliente (Busca servicios)</option>
                                <option value="proveedor">Proveedor (Ofrece servicios)</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>

                        <div id="campos-proveedor" class="d-none animate-fade-in w-100">
                            
                            <div class="card bg-light border-0 mt-3 mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-dark"><i class="bi bi-tags"></i> Habilidades y Categorías</h6>
                                    <p class="small text-muted">Añade las áreas donde este proveedor tiene experiencia.</p>
                                    
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-5">
                                            <select class="form-select" id="select-categoria">
                                                <option value="">Seleccionar de la lista...</option>
                                                <?php foreach($categorias_bd as $cat): ?>
                                                    <option value="<?= $cat['nombre'] ?>"><?= $cat['nombre'] ?></option>
                                                <?php endforeach; ?>
                                                <option value="nueva" class="fw-bold text-primary">+ Crear nueva categoría</option>
                                            </select>
                                        </div>
                                        <div class="col-md-5 d-none" id="input-nueva-cat-container">
                                            <input type="text" class="form-control" id="input-nueva-categoria" placeholder="Nombre de la nueva categoría...">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-secondary w-100" id="btn-add-categoria">
                                                Agregar
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div id="contenedor-tags" class="mt-3 d-flex flex-wrap gap-2"></div>
                                    <input type="hidden" name="lista_categorias" id="lista_categorias">
                                </div>
                            </div>

                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="card-title text-dark"><i class="bi bi-file-earmark-text"></i> Documentación Requerida</h6>
                                    <div class="row g-3 mt-1">
                                        <div class="col-md-6">
                                            <label class="form-label d-flex justify-content-between">
                                                <span>Cédula (PDF/IMG) <span class="text-danger">*</span></span>
                                                <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" title="Copia legible por ambas caras."></i>
                                            </label>
                                            <input type="file" class="form-control" name="doc-cedula" accept="image/*,.pdf">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label d-flex justify-content-between">
                                                <span>Antecedentes (PDF) <span class="text-danger">*</span></span>
                                                <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" title="Certificado de antecedentes judiciales (Policía/Procuraduría) vigente."></i>
                                            </label>
                                            <input type="file" class="form-control" name="doc-antecedentes" accept=".pdf">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label d-flex justify-content-between">
                                                <span>Selfie Verificación <span class="text-danger">*</span></span>
                                                <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" title="Foto del proveedor sosteniendo su documento cerca del rostro."></i>
                                            </label>
                                            <input type="file" class="form-control" name="doc-foto" accept="image/*">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label d-flex justify-content-between">
                                                <span>Certificaciones (Opcional)</span>
                                                <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" title="Diplomas o cursos que validen su habilidad."></i>
                                            </label>
                                            <input type="file" class="form-control" name="doc-certificado" accept="image/*,.pdf">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> <div class="text-center mt-4 mb-3">
                            <button type="submit" class="btn btn-primary btn-lg px-5">Registrar Usuario</button>
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
        xintegrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- tu javaScript -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/registroUsuario.js"></script>
    
</body>

</html>