<?php
// Asegúrate de que BASE_PATH esté definido en tu config
require_once BASE_PATH . '/app/helpers/session_proveedor.php';
// Las variables $resenas, $promedio, $totalResenas, $porcentajes llegan del controlador
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Reseñas y Calificaciones</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- CSS específico -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/resenas.css">
</head>

<body>
    <!-- SIDEBAR -->
    <?php include_once __DIR__ . '/../../layouts/sidebar_proveedor.php'; ?>

    <main class="contenido">
        <!-- HEADER -->
        <?php include_once __DIR__ . '/../../layouts/header_proveedor.php'; ?>

        <!-- Título con breadcrumb (IGUAL QUE DASHBOARD) -->
        <section id="titulo-principal">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Reseñas y Calificaciones</h1>
                    <p class="text-muted mb-0">
                        Gestiona las opiniones de tus clientes. Las reseñas ayudan a mejorar tu reputación.
                    </p>
                </div>
                <div class="col-md-4">
                    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                        <ol id="breadcrumb" class="breadcrumb mb-0 justify-content-md-end"></ol>
                    </nav>
                </div>
            </div>
        </section>

        <!-- Tarjetas de estadísticas -->
        <section id="tarjetas-superiores">
            <div class="tarjeta tarjeta-estadistica">
                <i class="bi bi-star-fill icono-estadistica text-warning"></i>
                <div class="valor-estadistica"><?= $promedio ?></div>
                <div class="etiqueta-estadistica">Calificación Promedio</div>
            </div>

            <div class="tarjeta tarjeta-estadistica">
                <i class="bi bi-chat-square-text icono-estadistica text-primary"></i>
                <div class="valor-estadistica"><?= $totalResenas ?></div>
                <div class="etiqueta-estadistica">Total de Reseñas</div>
            </div>

            <?php
            $positivas = $porcentajes[5] + $porcentajes[4];
            ?>
            <div class="tarjeta tarjeta-estadistica">
                <i class="bi bi-hand-thumbs-up icono-estadistica text-success"></i>
                <div class="valor-estadistica"><?= $positivas ?>%</div>
                <div class="etiqueta-estadistica">Clientes satisfechos</div>
            </div>
        </section>

        <!-- Distribución de calificaciones -->
        <section id="distribucion-calificaciones">
            <div class="tarjeta">
                <h3>Distribución de Calificaciones</h3>
                <div class="calificaciones-detalle">

                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <div class="calificacion-fila">
                            <span class="estrellas-label"><?= $i ?> <i class="bi bi-star-fill text-warning"></i></span>
                            <div class="barra-progreso-calificacion">
                                <div class="progreso-fill" style="width: <?= $porcentajes[$i] ?>%"></div>
                            </div>
                            <span class="porcentaje-label"><?= $porcentajes[$i] ?>%</span>
                        </div>
                    <?php endfor; ?>

                </div>
            </div>
        </section>

        <!-- Filtros -->
        <section id="filtros-resenas">
            <div class="filtros-contenedor">
                <select id="filtro-calificacion" class="filtro-select">
                    <option value="">Todas las calificaciones</option>
                    <option value="5">5 estrellas</option>
                    <option value="4">4 estrellas</option>
                </select>
                <div class="buscador-resenas">
                    <i class="bi bi-search"></i>
                    <input type="text" id="buscar-resena" placeholder="Buscar en reseñas...">
                </div>
            </div>
        </section>

        <!-- Lista de reseñas -->
        <div id="contenedor-scrollable-resenas">
            <section id="lista-resenas">

                <?php if (empty($resenas)): ?>
                    <div class="empty-state">
                        <i class="bi bi-chat-square"></i>
                        <h4 class="text-muted">No hay reseñas disponibles</h4>
                        <p class="text-muted">Aún no tienes reseñas registradas.</p>
                    </div>
                <?php else: ?>

                    <?php foreach ($resenas as $r): ?>
                        <div class="tarjeta tarjeta-resena">
                            <div class="resena-header">
                                <div class="cliente-info">
                                    <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= !empty($r['cliente_foto']) ? $r['cliente_foto'] : 'default_user.png' ?>"
                                        alt="Cliente" class="avatar-cliente">

                                    <div>
                                        <h4 class="nombre-cliente"><?= htmlspecialchars($r['cliente_nombre']) ?></h4>

                                        <div class="calificacion-estrellas">
                                            <?php for ($x = 1; $x <= 5; $x++): ?>
                                                <?php if ($x <= $r['calificacion']): ?>
                                                    <i class="bi bi-star-fill text-warning"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-star text-muted opacity-25"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                            <span class="calificacion-numero ms-2"><?= $r['calificacion'] ?>.0</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="resena-meta">
                                    <span class="fecha-resena">
                                        <i class="bi bi-calendar3"></i>
                                        <?= date('d M Y', strtotime($r['fecha'])) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="servicio-asociado">
                                <i class="bi bi-briefcase"></i>
                                <strong>Servicio:</strong> <?= htmlspecialchars($r['servicio_nombre']) ?>
                            </div>

                            <div class="resena-comentario">
                                <p>"<?= !empty($r['comentario']) ? htmlspecialchars($r['comentario']) : 'Sin comentario escrito.' ?>"</p>
                            </div>

                            <div class="resena-acciones mt-3 pt-2 border-top">
                                <?php if (empty($r['respuesta_proveedor'])): ?>
                                    <button class="btn btn-sm btn-outline-primary rounded-pill btn-abrir-modal"
                                        data-id="<?= $r['id'] ?? '' ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalResponder">
                                        <i class="bi bi-reply"></i> Responder
                                    </button>
                                <?php else: ?>
                                    <div class="mt-3 p-3 bg-light rounded border-start border-4 border-primary">
                                        <small class="fw-bold text-primary"><i class="bi bi-person-check"></i> Tu respuesta:</small>
                                        <p class="mb-0 small fst-italic text-muted"><?= htmlspecialchars($r['respuesta_proveedor']) ?></p>
                                    </div>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-outline-secondary rounded-pill ms-2">
                                    <i class="bi bi-flag"></i> Reportar
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>

            </section>
        </div>

    </main>

    <!-- Modal de Respuesta -->
    <div class="modal fade" id="modalResponder" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-reply me-2"></i>Responder al Cliente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?= BASE_URL ?>/proveedor/resenas/responder" method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="id_valoracion" id="modal_id_valoracion">
                        <div class="mb-3">
                            <label for="texto_respuesta" class="form-label fw-bold">Tu respuesta:</label>
                            <textarea class="form-control" name="texto_respuesta" id="texto_respuesta" rows="4" required placeholder="Escribe aquí tu agradecimiento o aclaración..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Enviar Respuesta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/resenas.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
</body>

</html>