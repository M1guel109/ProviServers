<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
require_once BASE_PATH . '/app/controllers/adminController.php';

// 1. Validar ID
if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . '/admin/consultar-usuarios');
    exit;
}
$id = $_GET['id'];

// 2. Obtener datos completos
$usuario = mostrarUsuarioId($id); 

if (!$usuario) {
    echo "Usuario no encontrado.";
    exit;
}

// 3. Preparar datos para la vista
$categorias_bd = [
    ['nombre' => 'Plomería'], ['nombre' => 'Electricidad'], 
    ['nombre' => 'Carpintería'], ['nombre' => 'Limpieza'], 
    ['nombre' => 'Jardinería'], ['nombre' => 'Clases'],
    ['nombre' => 'Mantenimiento'], ['nombre' => 'Transporte']
];

// Si es proveedor, convertimos sus categorías a string para el JS
$categorias_actuales_str = "";
if ($usuario['rol'] === 'proveedor' && !empty($usuario['categorias'])) {
    $categorias_actuales_str = implode(',', $usuario['categorias']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Editar Usuario</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/registrarUsuario.css">
    
    <style>
        .fade-in { animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar_administrador.php' ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header_administrador.php' ?>

        <section id="titulo-principal" class="mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-1">Editar Usuario</h1>
                    <p class="text-muted mb-0">Gestión de perfil, credenciales y documentación.</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="<?= BASE_URL ?>/admin/consultar-usuarios" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </section>

        <section id="formulario-usuarios">
            <div class="contenedor-formulario">
                
                <form action="<?= BASE_URL ?>/admin/actualizar-usuario" method="post" class="formulario-usuario" enctype="multipart/form-data">
                    
                    <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                    <input type="hidden" name="foto_actual" value="<?= $usuario['foto'] ?? 'default_user.png' ?>">

                    <div class="seccion-foto mb-4">
                        <div class="tarjeta-foto">
                            <div class="foto-perfil">
                                <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $usuario['foto'] ?? 'default_user.png' ?>" alt="Foto actual" id="foto-preview">
                            </div>
                            <label for="foto-input" class="btn-agregar-foto">
                                <i class="bi bi-camera-fill"></i> Cambiar
                            </label>
                            <input type="file" name="foto" id="foto-input" accept="image/*" style="display: none;">
                        </div>
                    </div>

                    <div class="row g-3">
                        
                        <div class="col-12"><h6 class="text-primary border-bottom pb-2">Datos Personales</h6></div>

                        <div class="col-md-6">
                            <label class="form-label">Nombres</label>
                            <input type="text" class="form-control" name="nombres" required value="<?= $usuario['nombres'] ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" name="apellidos" required value="<?= $usuario['apellidos'] ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Documento</label>
                            <input type="text" class="form-control" name="documento" required value="<?= $usuario['documento'] ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono" required value="<?= $usuario['telefono'] ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ubicación</label>
                            <input type="text" class="form-control" name="ubicacion" required value="<?= $usuario['ubicacion'] ?>">
                        </div>

                        <div class="col-12 mt-4"><h6 class="text-primary border-bottom pb-2">Cuenta</h6></div>

                        <div class="col-md-6">
                            <label class="form-label">Correo</label>
                            <input type="email" class="form-control" name="email" required value="<?= $usuario['email'] ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contraseña <small class="text-muted">(Opcional)</small></label>
                            <input type="password" class="form-control" name="clave" placeholder="Dejar vacío para no cambiar">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Rol</label>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="admin" <?= $usuario['rol'] == 'admin' ? 'selected' : '' ?>>Administrador</option>
                                <option value="proveedor" <?= $usuario['rol'] == 'proveedor' ? 'selected' : '' ?>>Proveedor</option>
                                <option value="cliente" <?= $usuario['rol'] == 'cliente' ? 'selected' : '' ?>>Cliente</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado" required>
                                <option value="1" <?= $usuario['estado_id'] == 1 ? 'selected' : '' ?>>Pendiente</option>
                                <option value="2" <?= $usuario['estado_id'] == 2 ? 'selected' : '' ?>>Activo</option>
                                <option value="3" <?= $usuario['estado_id'] == 3 ? 'selected' : '' ?>>Suspendido</option>
                                <option value="4" <?= $usuario['estado_id'] == 4 ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>

                        <div id="campos-proveedor" class="col-12 mt-4 <?= $usuario['rol'] === 'proveedor' ? '' : 'd-none' ?>">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h5 class="card-title text-dark mb-4"><i class="bi bi-briefcase"></i> Gestión Profesional</h5>
                                    
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Habilidades / Categorías</label>
                                        <input type="hidden" name="lista_categorias" id="lista_categorias" value="<?= $categorias_actuales_str ?>">

                                        <div class="d-flex gap-2 mb-2">
                                            <select class="form-select" id="select-categoria">
                                                <option value="">Seleccionar habilidad...</option>
                                                <?php foreach($categorias_bd as $cat): ?>
                                                    <option value="<?= $cat['nombre'] ?>"><?= $cat['nombre'] ?></option>
                                                <?php endforeach; ?>
                                                <option value="nueva" class="text-primary fw-bold">+ Nueva</option>
                                            </select>
                                            <div id="div-nueva-cat" class="d-none w-100">
                                                <input type="text" id="input-nueva-cat" class="form-control" placeholder="Nombre nueva categoría...">
                                            </div>
                                            <button type="button" class="btn btn-secondary" id="btn-add-categoria">Agregar</button>
                                        </div>
                                        <div id="contenedor-tags" class="d-flex flex-wrap gap-2 mt-2 p-3 bg-white rounded border"></div>
                                    </div>

                                    <div>
                                        <label class="form-label fw-bold border-bottom pb-2 w-100 mb-3">Documentación</label>
                                        
                                        <?php if (!empty($usuario['documentos'])): ?>
                                            <div class="mb-4">
                                                <h6 class="text-muted small text-uppercase fw-bold">Archivos actuales:</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered bg-white align-middle">
                                                        <thead class="table-light small">
                                                            <tr>
                                                                <th>Documento</th>
                                                                <th>Estado</th>
                                                                <th>Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($usuario['documentos'] as $doc): ?>
                                                                <tr>
                                                                    <td class="text-uppercase small fw-bold">
                                                                        <?= $doc['tipo_documento'] ?>
                                                                        <br>
                                                                        <a href="<?= BASE_URL ?>/public/uploads/documentos/<?= $doc['archivo'] ?>" target="_blank" class="fw-normal text-decoration-none small">
                                                                            <i class="bi bi-eye"></i> Ver
                                                                        </a>
                                                                    </td>
                                                                    <td>
                                                                        <?php 
                                                                            $badge = 'bg-secondary';
                                                                            if($doc['estado']=='aprobado') $badge='bg-success';
                                                                            if($doc['estado']=='rechazado') $badge='bg-danger';
                                                                            if($doc['estado']=='pendiente') $badge='bg-warning text-dark';
                                                                        ?>
                                                                        <span class="badge <?= $badge ?>"><?= $doc['estado'] ?></span>
                                                                    </td>
                                                                    <td>
                                                                        <div class="btn-group btn-group-sm">
                                                                            <button type="button" class="btn btn-outline-success" onclick="cambiarEstadoDoc(<?= $doc['id'] ?>, 'aprobado')" title="Aprobar"><i class="bi bi-check-lg"></i></button>
                                                                            <button type="button" class="btn btn-outline-danger" onclick="cambiarEstadoDoc(<?= $doc['id'] ?>, 'rechazado')" title="Rechazar"><i class="bi bi-x-lg"></i></button>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-info py-2 small mb-3">
                                                <i class="bi bi-info-circle"></i> Este usuario no tiene documentos cargados.
                                            </div>
                                        <?php endif; ?>

                                        <div class="bg-white p-3 rounded border border-dashed">
                                            <h6 class="text-primary small text-uppercase fw-bold mb-3"><i class="bi bi-cloud-upload"></i> Cargar / Actualizar Documentos</h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small">Cédula (PDF/IMG)</label>
                                                    <input type="file" class="form-control form-control-sm" name="doc-cedula" accept=".pdf,image/*">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Antecedentes (PDF)</label>
                                                    <input type="file" class="form-control form-control-sm" name="doc-antecedentes" accept=".pdf">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Selfie Verificación</label>
                                                    <input type="file" class="form-control form-control-sm" name="doc-foto" accept="image/*">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Certificados (Opcional)</label>
                                                    <input type="file" class="form-control form-control-sm" name="doc-certificado" accept=".pdf,image/*">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 text-center mt-4">
                            <button type="submit" class="btn btn-primary px-5">Guardar Cambios</button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </main>


    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        xintegrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>



    <script> const BASE_URL = "<?= BASE_URL ?>"; </script>
    
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <!-- <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/app.js"></script> -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/editarUsuario.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>