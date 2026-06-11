<?php
require_once BASE_PATH . '/app/helpers/session-admin.php';
require_once BASE_PATH . '/app/controllers/admin-controller.php';

// 1. Validar ID
if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . '/admin/consultar-usuarios');
    exit;
}

$id      = (int) $_GET['id'];
$usuario = mostrarUsuarioId($id);

if (!$usuario) {
    header('Location: ' . BASE_URL . '/admin/consultar-usuarios');
    exit;
}

// 2. Categorías disponibles y actuales del proveedor
$modeloUsuario          = new Usuario();
$categorias_bd          = $modeloUsuario->obtenerTodasCategorias();
$categorias_actuales_str = '';

if ($usuario['rol'] === 'proveedor' && !empty($usuario['categorias'])) {
    $categorias_actuales_str = implode(',', $usuario['categorias']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Editar Usuario</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- ✅ Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/registrar-usuario.css">

    <style>
        .fade-in { animation: fadeIn 0.4s ease-in-out; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .border-dashed { border-style: dashed !important; }
    </style>
</head>
<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar-administrador.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-administrador.php'; ?>

        <!-- Título -->
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

        <!-- Formulario -->
        <section id="formulario-usuarios">
            <div class="contenedor-formulario">

                <form action="<?= BASE_URL ?>/admin/actualizar-usuario"
                      method="POST"
                      class="formulario-usuario"
                      enctype="multipart/form-data">

                    <!-- Campos ocultos -->
                    <input type="hidden" name="accion"      value="actualizar">
                    <input type="hidden" name="id"          value="<?= (int)$usuario['id'] ?>">
                    <input type="hidden" name="foto_actual" value="<?= htmlspecialchars($usuario['foto'] ?? 'default_user.png') ?>">

                    <!-- FOTO -->
                    <div class="seccion-foto mb-4">
                        <div class="tarjeta-foto">
                            <div class="foto-perfil">
                                <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= htmlspecialchars($usuario['foto'] ?? 'default_user.png') ?>"
                                     alt="Foto actual"
                                     id="foto-preview">
                            </div>
                            <label for="foto-input" class="btn-agregar-foto">
                                <i class="bi bi-camera-fill"></i> Cambiar
                            </label>
                            <input type="file" name="foto" id="foto-input"
                                   accept="image/*" style="display:none;">
                        </div>
                    </div>

                    <div class="row g-3">

                        <!-- DATOS PERSONALES -->
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2">Datos Personales</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombres</label>
                            <input type="text" class="form-control" name="nombres" required
                                   value="<?= htmlspecialchars($usuario['nombres'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" name="apellidos" required
                                   value="<?= htmlspecialchars($usuario['apellidos'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Documento</label>
                            <input type="text" class="form-control" name="documento" required
                                   value="<?= htmlspecialchars($usuario['documento'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono" required
                                   value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ubicación</label>
                            <input type="text" class="form-control" name="ubicacion" required
                                   value="<?= htmlspecialchars($usuario['ubicacion'] ?? '') ?>">
                        </div>

                        <!-- CUENTA -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary border-bottom pb-2">Cuenta</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Correo</label>
                            <input type="email" class="form-control" name="email" required
                                   value="<?= htmlspecialchars($usuario['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                Contraseña <small class="text-muted">(Opcional)</small>
                            </label>
                            <input type="password" class="form-control" name="clave"
                                   placeholder="Dejar vacío para no cambiar">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rol</label>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="admin"      <?= $usuario['rol'] === 'admin'      ? 'selected' : '' ?>>Administrador</option>
                                <option value="proveedor"  <?= $usuario['rol'] === 'proveedor'  ? 'selected' : '' ?>>Proveedor</option>
                                <option value="cliente"    <?= $usuario['rol'] === 'cliente'    ? 'selected' : '' ?>>Cliente</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado" required>
                                <option value="1" <?= $usuario['estado_id'] == 1 ? 'selected' : '' ?>>Pendiente</option>
                                <option value="2" <?= $usuario['estado_id'] == 2 ? 'selected' : '' ?>>Activo</option>
                                <option value="3" <?= $usuario['estado_id'] == 3 ? 'selected' : '' ?>>Suspendido</option>
                                <option value="4" <?= $usuario['estado_id'] == 4 ? 'selected' : '' ?>>Inactivo</option>
                                <!-- ✅ CORREGIDO: faltaba estado Bloqueado -->
                                <option value="5" <?= $usuario['estado_id'] == 5 ? 'selected' : '' ?>>Bloqueado</option>
                            </select>
                        </div>

                        <!-- CAMPOS EXCLUSIVOS PROVEEDOR -->
                        <div id="campos-proveedor"
                             class="col-12 mt-4 <?= $usuario['rol'] === 'proveedor' ? '' : 'd-none' ?>">

                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h5 class="card-title text-dark mb-4">
                                        <i class="bi bi-briefcase"></i> Gestión Profesional
                                    </h5>

                                    <!-- CATEGORÍAS -->
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Habilidades / Categorías</label>

                                        <!-- Input oculto que recibe PHP — pre-cargado con las categorías actuales -->
                                        <input type="hidden" name="lista_categorias"
                                               id="lista_categorias"
                                               value="<?= htmlspecialchars($categorias_actuales_str) ?>">

                                        <div class="d-flex gap-2 mb-2">
                                            <select class="form-select" id="select-categoria">
                                                <option value="">Seleccionar habilidad...</option>
                                                <?php foreach ($categorias_bd as $cat): ?>
                                                    <option value="<?= htmlspecialchars($cat['nombre']) ?>">
                                                        <?= htmlspecialchars($cat['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                                <option value="nueva" class="text-primary fw-bold">+ Nueva</option>
                                            </select>

                                            <div id="div-nueva-cat" class="d-none w-100">
                                                <input type="text" id="input-nueva-cat"
                                                       class="form-control"
                                                       placeholder="Nombre nueva categoría...">
                                            </div>

                                            <button type="button" class="btn btn-secondary"
                                                    id="btn-add-categoria">
                                                Agregar
                                            </button>
                                        </div>

                                        <!-- Tags renderizados por JS -->
                                        <div id="contenedor-tags"
                                             class="d-flex flex-wrap gap-2 mt-2 p-3 bg-white rounded border">
                                        </div>
                                    </div>

                                    <!-- DOCUMENTOS -->
                                    <div>
                                        <label class="form-label fw-bold border-bottom pb-2 w-100 mb-3">
                                            Documentación
                                        </label>

                                        <!-- Documentos existentes -->
                                        <?php if (!empty($usuario['documentos'])): ?>
                                            <div class="mb-4">
                                                <h6 class="text-muted small text-uppercase fw-bold">
                                                    Archivos actuales:
                                                </h6>
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
                                                                    <td class="small fw-bold">
                                                                        <div class="d-flex align-items-center">
                                                                            <i class="bi bi-file-earmark-text fs-5 text-secondary me-2"></i>
                                                                            <div>
                                                                                <?= htmlspecialchars(ucfirst($doc['tipo_documento'])) ?>
                                                                                <br>
                                                                                <a href="<?= BASE_URL ?>/public/uploads/documentos/<?= htmlspecialchars($doc['archivo']) ?>"
                                                                                   target="_blank"
                                                                                   class="fw-normal text-decoration-none small">
                                                                                    <i class="bi bi-eye"></i> Ver archivo
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <?php
                                                                        $badge = match($doc['estado']) {
                                                                            'aprobado'  => 'bg-success',
                                                                            'rechazado' => 'bg-danger',
                                                                            'pendiente' => 'bg-warning text-dark',
                                                                            default     => 'bg-secondary'
                                                                        };
                                                                        ?>
                                                                        <span class="badge <?= $badge ?>">
                                                                            <?= ucfirst($doc['estado']) ?>
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <div class="btn-group btn-group-sm"
                                                                             id="doc-actions-<?= (int)$doc['id'] ?>">
                                                                            <?php if ($doc['estado'] !== 'aprobado'): ?>
                                                                                <button type="button"
                                                                                        class="btn btn-outline-success"
                                                                                        onclick="cambiarEstadoDoc(<?= (int)$doc['id'] ?>, 'aprobado', this)"
                                                                                        title="Aprobar">
                                                                                    <i class="bi bi-check-lg"></i>
                                                                                </button>
                                                                            <?php endif; ?>
                                                                            <?php if ($doc['estado'] !== 'rechazado'): ?>
                                                                                <button type="button"
                                                                                        class="btn btn-outline-danger"
                                                                                        onclick="cambiarEstadoDoc(<?= (int)$doc['id'] ?>, 'rechazado', this)"
                                                                                        title="Rechazar">
                                                                                    <i class="bi bi-x-lg"></i>
                                                                                </button>
                                                                            <?php endif; ?>
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
                                                <i class="bi bi-info-circle"></i>
                                                Este proveedor no tiene documentos cargados.
                                            </div>
                                        <?php endif; ?>

                                        <!-- Subir nuevos documentos -->
                                        <div class="bg-white p-3 rounded border border-dashed">
                                            <h6 class="text-primary small text-uppercase fw-bold mb-3">
                                                <i class="bi bi-cloud-upload"></i> Cargar / Actualizar Documentos
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small">Cédula (PDF/IMG)</label>
                                                    <input type="file" class="form-control form-control-sm"
                                                           name="doc-cedula" accept=".pdf,image/*">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Antecedentes (PDF)</label>
                                                    <input type="file" class="form-control form-control-sm"
                                                           name="doc-antecedentes" accept=".pdf">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Selfie Verificación</label>
                                                    <input type="file" class="form-control form-control-sm"
                                                           name="doc-foto" accept="image/*">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Certificados (Opcional)</label>
                                                    <input type="file" class="form-control form-control-sm"
                                                           name="doc-certificado" accept=".pdf,image/*">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- fin #campos-proveedor -->

                        <!-- SUBMIT -->
                        <div class="col-12 text-center mt-4">
                            <button type="submit" class="btn btn-primary px-5">
                                Guardar Cambios
                            </button>
                        </div>

                    </div><!-- fin .row -->
                </form>
            </div>
        </section>
    </main>

    <!-- ✅ SweetAlert PRIMERO — lo usan los scripts siguientes -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>const BASE_URL = "<?= BASE_URL ?>";</script>
    <!-- ✅ Sin apexcharts ni dashboard.js — esta página no los necesita -->
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/editar-usuario.js"></script>

</body>
</html>