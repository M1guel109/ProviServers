<?php
require_once BASE_PATH . '/app/helpers/session_proveedor.php';
require_once BASE_PATH . '/app/controllers/proveedorServiciosContratadosController.php';

// Llamamos al controlador para obtener los datos
$servicios = mostrarServiciosContratadosProveedor();

// Estadísticas simples (puedes refinarlas luego)
$stats = [
    'en_proceso' => count($servicios),
    'para_hoy'   => 0, 
    'vencen'     => 0,
    'promedio'   => 0,
];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Servicios en Proceso</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/enProcesos.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboard-Proveedor.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar_proveedor.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header_proveedor.php'; ?>

        <section id="titulo-principal">
            <h1>Servicios en Proceso</h1>
            <p class="subtitulo">Gestiona los servicios que actualmente estás realizando</p>
        </section>

        <section id="estadisticas-proceso">
            <div class="tarjeta-stat">
                <i class="bi bi-hourglass-split icono-stat"></i>
                <div class="stat-info">
                    <div class="stat-numero"><?= $stats['en_proceso'] ?></div>
                    <div class="stat-label">En Proceso</div>
                </div>
            </div>
            </section>

        <section id="filtros-proceso">
            <div class="contenedor-filtros">
                <div class="grupo-filtro">
                    <label for="filtro-categoria">Categoría</label>
                    <select id="filtro-categoria">
                        <option value="">Todas</option>
                        <option value="plomeria">Plomería</option>
                        </select>
                </div>
                </div>
        </section>

        <section id="lista-procesos">
            <?php if (!empty($servicios)): ?>
                <?php foreach ($servicios as $servicio): ?>

                    <div class="tarjeta-proceso" data-contrato-id="<?= $servicio['contrato_id'] ?>">

                        <div class="proceso-header">
                            <div class="proceso-info-principal">
                                <h3 class="proceso-titulo">
                                    <?= htmlspecialchars($servicio['servicio_nombre']) ?>
                                </h3>
                                <div class="proceso-meta">
                                    <span class="badge-categoria">
                                        <i class="bi bi-briefcase"></i> <?= htmlspecialchars($servicio['solicitud_titulo']) ?>
                                    </span>
                                    <span class="proceso-fecha">
                                        <i class="bi bi-calendar3"></i> Inicio: <?= date('d M Y', strtotime($servicio['fecha_solicitud'])) ?>
                                    </span>
                                </div>
                            </div>

                            <?php
                                $estadoMap = [
                                    'pendiente'  => ['label' => 'Pendiente', 'class' => 'media', 'progress' => 25],
                                    'en_proceso' => ['label' => 'En proceso', 'class' => 'alta',  'progress' => 60],
                                    'finalizado' => ['label' => 'Finalizado', 'class' => 'completado', 'progress' => 100],
                                ];
                                $estado = $estadoMap[$servicio['estado']] ?? $estadoMap['pendiente'];
                            ?>

                            <div class="proceso-prioridad">
                                <span class="badge-prioridad badge-estado <?= $estado['class'] ?>">
                                    <?= $estado['label'] ?>
                                </span>
                            </div>
                        </div>

                        <div class="proceso-cliente">
                            <img src="<?= BASE_URL . '/public/uploads/usuarios/' . ($servicio['cliente_foto'] ?: 'default_user.png') ?>" alt="Cliente" class="cliente-avatar">
                            <div class="cliente-info">
                                <div class="cliente-nombre"><?= htmlspecialchars($servicio['cliente_nombre']) ?></div>
                                <div class="cliente-contacto"><i class="bi bi-telephone"></i> <?= htmlspecialchars($servicio['cliente_telefono']) ?></div>
                            </div>
                        </div>

                        <div class="proceso-progreso">
                            <div class="progreso-header">
                                <span class="progreso-label">Estado del servicio</span>
                                <span class="progreso-porcentaje progreso-estado"><?= $estado['label'] ?></span>
                            </div>
                            <div class="barra-progreso">
                                <div class="barra-progreso-fill" style="width: <?= $estado['progress'] ?>%"></div>
                            </div>
                        </div>

                        <div class="proceso-acciones">
                            <button class="btn-accion btn-actualizar">
                                <i class="bi bi-arrow-clockwise"></i> Actualizar Estado
                            </button>
                            
                            <button class="btn-accion btn-contactar">
                                <i class="bi bi-chat-dots"></i> Contactar
                            </button>
                        </div>

                    </div> <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted text-center p-5">No tienes servicios en proceso actualmente.</p>
            <?php endif; ?>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" 
            crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/enProceso.js"></script>

</body>
</html>