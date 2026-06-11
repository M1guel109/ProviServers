<?php
require_once BASE_PATH . '/app/helpers/session-admin.php';
require_once BASE_PATH . '/app/controllers/admin-controller.php';

// Datos reales de la BD
$usuarios  = mostrarUsuarios();
$metricas  = (new Usuario())->obtenerMetricasUsuarios();

// Calcular KPIs
$total_activos    = (int)($metricas['clientes_activos'] ?? 0) + (int)($metricas['proveedores_activos'] ?? 0);
$total_proveedores = (int)($metricas['proveedores_total'] ?? 0);
$total_clientes   = (int)($metricas['clientes_total'] ?? 0);

// Bloqueados: contar del array de usuarios
$total_bloqueados = count(array_filter($usuarios, fn($u) => strtolower($u['estado'] ?? '') === 'bloqueado'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProviServers | Reporte de Usuarios</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/estilos-tablas.css">
</head>
<body>

    <?php include_once __DIR__ . '/../../layouts/sidebar-administrador.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header-administrador.php'; ?>

        <!-- Título -->
        <section id="titulo-principal">
            <div class="row">
                <div class="col-md-8">
                    <h1>Reporte y Gestión de Usuarios</h1>
                    <p class="text-muted mb-0">
                        Gestión completa de clientes, proveedores y administradores de la plataforma.
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="<?= BASE_URL ?>/admin/reporte?tipo=usuarios"
                       target="_blank"
                       class="btn btn-primary">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Exportar PDF
                    </a>
                </div>
            </div>
        </section>

        <!-- KPIs con datos reales -->
        <section id="tarjetas-kpis" class="mt-4">
            <div class="row g-3 mb-4">

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold"
                                   style="font-size:0.75rem;">
                                Usuarios Activos
                            </small>
                            <h2 class="fw-bold text-dark mb-0">
                                <?= number_format($total_activos) ?>
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold"
                                   style="font-size:0.75rem;">
                                Proveedores
                            </small>
                            <h2 class="fw-bold text-dark mb-0">
                                <?= number_format($total_proveedores) ?>
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold"
                                   style="font-size:0.75rem;">
                                Clientes
                            </small>
                            <h2 class="fw-bold text-dark mb-0">
                                <?= number_format($total_clientes) ?>
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                        <div class="card-body">
                            <small class="text-muted text-uppercase fw-bold"
                                   style="font-size:0.75rem;">
                                Bloqueados
                            </small>
                            <h2 class="fw-bold text-dark mb-0">
                                <?= number_format($total_bloqueados) ?>
                            </h2>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Tabla de usuarios reales -->
        <section id="tabla-usuarios">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-container">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Foto</th>
                                    <th>Nombre Completo</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($usuarios)): ?>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <?php
                                        $estadoNombre = strtolower($usuario['estado'] ?? '');
                                        $badgeClass   = match($estadoNombre) {
                                            'activo'     => 'bg-success',
                                            'inactivo'   => 'bg-secondary',
                                            'suspendido' => 'bg-warning text-dark',
                                            'bloqueado'  => 'bg-danger',
                                            default      => 'bg-light text-dark'
                                        };
                                        ?>
                                        <tr>
                                            <td>
                                                <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= htmlspecialchars($usuario['foto'] ?? 'default_user.png') ?>"
                                                     alt="Foto"
                                                     width="36" height="36"
                                                     class="rounded-circle"
                                                     style="object-fit:cover;">
                                            </td>
                                            <td class="fw-bold">
                                                <?= htmlspecialchars(($usuario['nombres'] ?? '') . ' ' . ($usuario['apellidos'] ?? '')) ?>
                                            </td>
                                            <td><?= htmlspecialchars($usuario['email'] ?? '') ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?= ucfirst(htmlspecialchars($usuario['rol'] ?? '')) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?= $badgeClass ?>">
                                                    <?= ucfirst(htmlspecialchars($usuario['estado'] ?? '')) ?>
                                                </span>
                                            </td>
                                            <td class="text-muted small">
                                                <?= !empty($usuario['created_at'])
                                                    ? date('d/m/Y', strtotime($usuario['created_at']))
                                                    : '—' ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="<?= BASE_URL ?>/admin/editar-usuario?id=<?= (int)$usuario['id'] ?>"
                                                       class="btn-action btn-edit"
                                                       title="Editar usuario">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No hay usuarios registrados.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <footer></footer>

    <!-- ✅ Sin apexcharts ni dashboard.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>const BASE_URL = "<?= BASE_URL ?>";</script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>

</body>
</html>