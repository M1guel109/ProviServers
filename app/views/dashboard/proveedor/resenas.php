<?php
// Asegúrate de que BASE_PATH esté definido en tu config
require_once BASE_PATH . '/app/helpers/session_proveedor.php';
// Las variables $resenas, $promedio, $totalResenas, $porcentajes llegan del controlador


// echo "<pre style='background:white; color:black; z-index:9999; position:relative; padding:20px;'>";
// echo "<h1>🔍 DIAGNÓSTICO DE DATOS</h1>";
// echo "<strong>Total Reseñas:</strong> "; var_dump($totalResenas); echo "<br>";
// echo "<strong>Promedio:</strong> "; var_dump($promedio); echo "<br>";
// echo "<strong>Porcentajes:</strong> "; print_r($porcentajes); echo "<br>";
// echo "<strong>Lista de Reseñas:</strong> "; print_r($resenas);
// echo "</pre>";
// die(); // Detiene la carga de la página aquí para que solo veas los datos

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Reseñas y Calificaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboard-Proveedor.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/resenas.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar_proveedor.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header_proveedor.php'; ?>

        <section id="titulo-principal">
            <h1>Reseñas y Calificaciones</h1>
            <p class="descripcion-seccion">Gestiona las opiniones de tus clientes. Las reseñas ayudan a mejorar tu reputación.</p>
        </section>

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

        <div id="contenedor-scrollable-resenas">
            <section id="lista-resenas">

                <?php if (empty($resenas)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-chat-square text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">Aún no tienes reseñas registradas.</p>
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
                                <button class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="bi bi-reply"></i> Responder
                                </button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>const BASE_URL = "<?= BASE_URL ?>";</script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/resenas.js"></script>
</body>
</html>