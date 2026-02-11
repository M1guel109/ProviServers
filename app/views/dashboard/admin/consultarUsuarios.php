<?php
require_once BASE_PATH . '/app/helpers/session_admin.php';
require_once BASE_PATH . '/app/controllers/adminController.php';

// Llamamos la funcion especifica que existe en dicho controlador
$datos = mostrarUsuarios();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Gestión de Usuarios</title>

    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.dataTables.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/consultarUsuarios.css">
</head>

<body>
    
    <?php include_once __DIR__ . '/../../layouts/sidebar_administrador.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header_administrador.php'; ?>

        <section id="titulo-principal">
            <div class="row align-items-start">
                <div class="col-md-8 d-flex flex-column">
                    <div>
                        <h1 class="mb-1">Usuarios</h1>
                        <p class="text-muted mb-0">
                            Administración general de usuarios, proveedores y administradores.
                        </p>
                    </div>
                    <a href="<?= BASE_URL ?>/admin/reporte?tipo=usuarios" target="_blank" class="btn btn-primary mt-3 w-auto" style="width: fit-content;">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Generar Reporte PDF
                    </a>
                </div>
                <div class="col-md-4 d-flex justify-content-end align-items-start">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0 mt-2"></ol>
                    </nav>
                </div>
            </div>
        </section>

        <section id="tabla-arriba">
            
            <ul class="nav nav-tabs mb-3" id="tablaTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tabla-tab" data-bs-toggle="tab" data-bs-target="#tabla-pane" type="button" role="tab">
                        <i class="bi bi-table"></i> Listado Principal
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="acciones-tab" data-bs-toggle="tab" data-bs-target="#acciones-pane" type="button" role="tab">
                        <i class="bi bi-box-arrow-in-right"></i> Exportar Datos
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="tablaTabsContent">
                
                <div class="tab-pane fade show active" id="tabla-pane" role="tabpanel">
                    <div class="table-container">
                        <table id="tabla" class="display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Foto</th>
                                    <th>Nombre completo</th>
                                    <th>Correo electrónico</th>
                                    <th>Teléfono</th>
                                    <th>Ubicación</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($datos)) : ?>
                                    <?php foreach ($datos as $usuario) : ?>
                                        <tr>
                                            <td>
                                                <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= $usuario['foto'] ?? 'default_user.png' ?>" 
                                                     alt="Avatar" width="40" height="40" style="border-radius: 50%; object-fit: cover;">
                                            </td>
                                            <td><?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?></td>
                                            <td><?= $usuario['email'] ?></td>
                                            <td><?= $usuario['telefono'] ?></td>
                                            <td><?= $usuario['ubicacion'] ?></td>
                                            <td><?= ucfirst($usuario['rol']) ?></td>
                                            <td>
                                                <?php
                                                $clase = 'status-pending'; // Default
                                                if ($usuario['estado'] === 'Activo') $clase = 'status-activo';
                                                if ($usuario['estado'] === 'Inactivo') $clase = 'status-inactivo';
                                                ?>
                                                <span class="status-badge <?= $clase ?>">
                                                    <?= $usuario['estado'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn-action btn-view" title="Ver detalles"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalDetalleUsuario"
                                                            onclick="cargarDetalleUsuario(<?= $usuario['id'] ?>)">
                                                        <i class="bi bi-eye"></i>
                                                    </button>

                                                    <a href="<?= BASE_URL ?>/admin/editar-usuario?id=<?= $usuario['id'] ?>" class="btn-action btn-edit" title="Editar">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    <button type="button" class="btn-action btn-delete" title="Eliminar"
                                                            onclick="confirmarEliminacion('<?= BASE_URL ?>/admin/eliminar-usuario?accion=eliminar&id=<?= $usuario['id'] ?>')">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="acciones-pane" role="tabpanel">
                    <div class="p-3">
                        <div class="alert alert-light border">
                            <i class="bi bi-info-circle me-2"></i> 
                            Esta tabla está optimizada para copiar y exportar datos rápidamente.
                        </div>
                        <table id="tabla-1" class="display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Teléfono</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($datos)) : ?>
                                    <?php foreach ($datos as $usuario) : ?>
                                        <tr>
                                            <td><?= $usuario['nombres'] . ' ' . $usuario['apellidos'] ?></td>
                                            <td><?= $usuario['email'] ?></td>
                                            <td><?= $usuario['telefono'] ?></td>
                                            <td><?= $usuario['rol'] ?></td>
                                            <td><?= $usuario['estado'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <div class="modal fade" id="modalDetalleUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-badge-fill me-2"></i>Detalle del Usuario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div id="loader-detalle" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>

                    <div id="contenido-detalle" class="d-none">
                        
                        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                            <div class="position-relative">
                                <img id="modal-foto" src="" alt="Foto">
                                <span id="modal-estado-badge" class="position-absolute bottom-0 start-100 translate-middle p-2 border border-light rounded-circle bg-success"></span>
                            </div>
                            <div class="ms-4">
                                <h3 id="modal-nombre" class="mb-0 fw-bold"></h3>
                                <p id="modal-rol" class="mb-0 text-uppercase fw-bold mt-1"></p>
                                <small id="modal-id" class="text-muted fst-italic d-block mt-1"></small>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded h-100">
                                    <h6 class="text-primary fw-bold mb-3"><i class="bi bi-envelope-at me-2"></i>Contacto</h6>
                                    <p class="mb-2"><strong>Email:</strong> <span id="modal-email" class="text-muted"></span></p>
                                    <p class="mb-2"><strong>Teléfono:</strong> <span id="modal-telefono" class="text-muted"></span></p>
                                    <p class="mb-0"><strong>Ubicación:</strong> <span id="modal-ubicacion" class="text-muted"></span></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded h-100">
                                    <h6 class="text-primary fw-bold mb-3"><i class="bi bi-shield-lock me-2"></i>Cuenta</h6>
                                    <p class="mb-2"><strong>Documento:</strong> <span id="modal-documento" class="text-muted"></span></p>
                                    <p class="mb-2"><strong>Estado:</strong> <span id="modal-estado-texto"></span></p>
                                    <p class="mb-0"><strong>Registrado:</strong> <span id="modal-fecha" class="text-muted"></span></p>
                                </div>
                            </div>
                        </div>

                        <div id="seccion-proveedor" class="mt-4 d-none">
                            <h6 class="border-bottom pb-2 mb-3 fw-bold text-dark">Información Profesional</h6>

                            <div class="mb-3">
                                <p class="fw-bold mb-2 small text-uppercase text-muted">Habilidades / Categorías:</p>
                                <div id="modal-categorias" class="d-flex flex-wrap gap-2"></div>
                            </div>

                            <div>
                                <p class="fw-bold mb-2 small text-uppercase text-muted">Documentación:</p>
                                <div id="modal-documentos" class="list-group list-group-flush border rounded"></div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>

            </div>
        </div>
    </div>

    <footer></footer>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.print.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
        
        function confirmarEliminacion(url) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esto (a menos que tenga historial).",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            })
        }
    </script>
    
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/detalleUsuario.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboard.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>

</body>
</html>