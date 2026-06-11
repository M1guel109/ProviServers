<?php
require_once BASE_PATH . '/app/helpers/session-proveedor.php';
require_once BASE_PATH . '/config/database.php';

$uid = (int)($_SESSION['user']['id'] ?? 0);
$completadas = [];
$stats = ['total' => 0, 'este_mes' => 0, 'calificacion' => null, 'ingresos' => 0];

try {
    $db  = new Conexion();
    $pdo = $db->getConexion();

    $stProv = $pdo->prepare("SELECT id FROM proveedores WHERE usuario_id = :uid LIMIT 1");
    $stProv->execute([':uid' => $uid]);
    $proveedorId = (int)($stProv->fetchColumn() ?: 0);

    if ($proveedorId > 0) {
        $stStats = $pdo->prepare("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN MONTH(sc.created_at) = MONTH(CURDATE()) AND YEAR(sc.created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS este_mes
            FROM servicios_contratados sc
            WHERE sc.proveedor_id = :pid AND sc.estado = 'finalizado'
        ");
        $stStats->execute([':pid' => $proveedorId]);
        $row = $stStats->fetch(PDO::FETCH_ASSOC);
        $stats['total']    = (int)($row['total']    ?? 0);
        $stats['este_mes'] = (int)($row['este_mes'] ?? 0);

        $stRating = $pdo->prepare("SELECT ROUND(AVG(puntaje), 1) FROM calificaciones WHERE proveedor_id = :pid");
        $stRating->execute([':pid' => $proveedorId]);
        $stats['calificacion'] = $stRating->fetchColumn() ?: null;

        $stIngresos = $pdo->prepare("
            SELECT COALESCE(SUM(COALESCE(c.precio, pub_sol.precio, 0)), 0)
            FROM servicios_contratados sc
            LEFT JOIN cotizaciones c     ON sc.cotizacion_id    = c.id
            LEFT JOIN solicitudes sol    ON sc.solicitud_id     = sol.id
            LEFT JOIN publicaciones pub_sol ON sol.publicacion_id = pub_sol.id
            WHERE sc.proveedor_id = :pid AND sc.estado = 'finalizado'
        ");
        $stIngresos->execute([':pid' => $proveedorId]);
        $stats['ingresos'] = (float)($stIngresos->fetchColumn() ?: 0);

        $stList = $pdo->prepare("
            SELECT
                sc.id, sc.fecha_solicitud, sc.fecha_ejecucion, sc.created_at AS fecha_completado,
                COALESCE(c.titulo, sol.titulo, pub.titulo, 'Servicio') AS titulo,
                COALESCE(c.precio, pub.precio, 0)                       AS monto,
                cat.nombre                                              AS categoria,
                TRIM(CONCAT(u.nombre, ' ', COALESCE(u.apellido, '')))  AS cliente_nombre,
                cal.puntaje  AS calificacion,
                cal.comentario
            FROM servicios_contratados sc
            LEFT JOIN cotizaciones c  ON sc.cotizacion_id  = c.id
            LEFT JOIN solicitudes sol ON sc.solicitud_id   = sol.id
            LEFT JOIN publicaciones pub ON COALESCE(c.publicacion_id, sol.publicacion_id) = pub.id
            LEFT JOIN servicios s      ON pub.servicio_id  = s.id
            LEFT JOIN categorias cat   ON s.id_categoria   = cat.id
            LEFT JOIN clientes cl      ON sc.cliente_id    = cl.id
            LEFT JOIN usuarios u       ON cl.usuario_id    = u.id
            LEFT JOIN calificaciones cal ON cal.servicio_contratado_id = sc.id
            WHERE sc.proveedor_id = :pid AND sc.estado = 'finalizado'
            ORDER BY sc.created_at DESC
        ");
        $stList->execute([':pid' => $proveedorId]);
        $completadas = $stList->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log('completadas.php: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Servicios Completados</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos Globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-proveedor.css">

    <!-- CSS Específico -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/completadas.css">
   
</head>

<body>

    <!-- SIDEBAR (FIJO) -->
    <?php include_once __DIR__ . '/../../layouts/sidebar-proveedor.php'; ?>

    <!-- CONTENIDO GENERAL -->
    <main class="contenido">

        <!-- HEADER (FIJO) -->
        <?php include_once __DIR__ . '/../../layouts/header-proveedor.php'; ?>

        <!-- CONTENIDO INTERNO QUE SÍ DEBE MOVERSE -->
        <div class="contenido-completados">

            <section id="titulo-principal" class="section-hero mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-1">Servicios Completados</h1>
                        <p class="text-muted mb-0">Historial de servicios finalizados y evaluaciones recibidas.</p>
                    </div>
                    <div class="col-md-4">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 justify-content-md-end">
                                <li class="breadcrumb-item">
                                    <a href="<?= BASE_URL ?>/proveedor/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Completados</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </section>

            <!-- Estadísticas -->
            <section id="estadisticas-completadas">
                <div class="tarjeta-stat">
                    <i class="bi bi-check-circle icono-stat"></i>
                    <div class="stat-info">
                        <div class="stat-numero"><?= $stats['total'] ?></div>
                        <div class="stat-label">Total Completados</div>
                    </div>
                </div>
                <div class="tarjeta-stat">
                    <i class="bi bi-calendar-month icono-stat"></i>
                    <div class="stat-info">
                        <div class="stat-numero"><?= $stats['este_mes'] ?></div>
                        <div class="stat-label">Este Mes</div>
                    </div>
                </div>
                <div class="tarjeta-stat">
                    <i class="bi bi-star-fill icono-stat"></i>
                    <div class="stat-info">
                        <div class="stat-numero"><?= $stats['calificacion'] !== null ? number_format($stats['calificacion'], 1) : 'N/A' ?></div>
                        <div class="stat-label">Calificación Promedio</div>
                    </div>
                </div>
                <div class="tarjeta-stat">
                    <i class="bi bi-cash-coin icono-stat"></i>
                    <div class="stat-info">
                        <div class="stat-numero">$<?= number_format($stats['ingresos'], 0, ',', '.') ?></div>
                        <div class="stat-label">Ingresos Totales</div>
                    </div>
                </div>
            </section>

            <!-- Filtros -->
            <section id="filtros-completadas">
                <div class="contenedor-filtros">
                    <div class="grupo-filtro">
                        <label for="filtro-categoria">Categoría</label>
                        <select id="filtro-categoria">
                            <option value="">Todas las categorías</option>
                            <option value="plomeria">Plomería</option>
                            <option value="electricidad">Electricidad</option>
                            <option value="limpieza">Limpieza</option>
                            <option value="pintura">Pintura</option>
                            <option value="jardineria">Jardinería</option>
                        </select>
                    </div>

                    <div class="grupo-filtro">
                        <label for="filtro-periodo">Período</label>
                        <select id="filtro-periodo">
                            <option value="">Todos</option>
                            <option value="semana">Esta semana</option>
                            <option value="mes">Este mes</option>
                            <option value="trimestre">Últimos 3 meses</option>
                            <option value="anio">Este año</option>
                        </select>
                    </div>

                    <div class="grupo-filtro busqueda-filtro">
                        <label for="buscar-completadas">Buscar</label>
                        <input type="text" id="buscar-completadas" placeholder="Buscar por cliente o servicio...">
                    </div>
                </div>
            </section>

            <!-- LISTA DE COMPLETADOS -->
            <section id="lista-completadas" class="grid-completadas">

                <?php if (empty($completadas)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-check-circle fs-1 d-block mb-3"></i>
                    <h5>Aún no tienes servicios completados</h5>
                    <p class="small">Tus servicios finalizados aparecerán aquí.</p>
                </div>
                <?php else: ?>
                <?php foreach ($completadas as $sc):
                    $puntaje = (int)round((float)($sc['calificacion'] ?? 0));
                ?>
                <div class="tarjeta-completada">

                    <div class="completada-header">
                        <div class="completada-info-principal">
                            <h3 class="completada-titulo"><?= htmlspecialchars($sc['titulo']) ?></h3>
                            <div class="completada-meta">
                                <?php if ($sc['categoria']): ?>
                                <span class="badge-categoria">
                                    <i class="bi bi-tag"></i> <?= htmlspecialchars($sc['categoria']) ?>
                                </span>
                                <?php endif; ?>
                                <span class="completada-feja">
                                    <i class="bi bi-calendar-check"></i>
                                    Completado: <?= $sc['fecha_completado'] ? date('d M Y', strtotime($sc['fecha_completado'])) : '—' ?>
                                </span>
                            </div>
                        </div>
                        <div class="completada-estado">
                            <span class="badge-completado">
                                <i class="bi bi-check-circle-fill"></i> Completado
                            </span>
                        </div>
                    </div>

                    <div class="completada-cliente">
                        <img src="<?= BASE_URL ?>/public/assets/dashboard/img/avatar-cliente.png" class="cliente-avatar">
                        <div class="cliente-info">
                            <div class="cliente-nombre"><?= htmlspecialchars($sc['cliente_nombre'] ?: 'Cliente') ?></div>
                        </div>
                    </div>

                    <div class="completada-detalles">
                        <div class="detalle-item">
                            <i class="bi bi-calendar3"></i>
                            <div class="detalle-info">
                                <span class="detalle-label">Fecha de inicio</span>
                                <span class="detalle-valor"><?= $sc['fecha_solicitud'] ? date('d M Y', strtotime($sc['fecha_solicitud'])) : '—' ?></span>
                            </div>
                        </div>
                        <div class="detalle-item">
                            <i class="bi bi-calendar-event"></i>
                            <div class="detalle-info">
                                <span class="detalle-label">Fecha ejecución</span>
                                <span class="detalle-valor"><?= $sc['fecha_ejecucion'] ? date('d M Y', strtotime($sc['fecha_ejecucion'])) : '—' ?></span>
                            </div>
                        </div>
                        <div class="detalle-item">
                            <i class="bi bi-currency-dollar"></i>
                            <div class="detalle-info">
                                <span class="detalle-label">Monto</span>
                                <span class="detalle-valor">$<?= number_format((float)$sc['monto'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if ($puntaje > 0): ?>
                    <div class="completada-calificacion">
                        <div class="calificacion-header">
                            <span class="calificacion-titulo">Calificación del cliente</span>
                            <div class="estrellas">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <i class="bi bi-star<?= $i < $puntaje ? '-fill' : '' ?>"></i>
                                <?php endfor; ?>
                                <span class="calificacion-numero"><?= number_format($sc['calificacion'], 1) ?></span>
                            </div>
                        </div>
                        <?php if ($sc['comentario']): ?>
                        <p class="calificacion-comentario">"<?= htmlspecialchars($sc['comentario']) ?>"</p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="completada-acciones">
                        <a href="<?= BASE_URL ?>/proveedor/contrato-pdf?id=<?= $sc['id'] ?>" class="btn-accion btn-ver-detalles">
                            <i class="bi bi-file-pdf"></i> Ver Contrato
                        </a>
                        <a href="<?= BASE_URL ?>/cliente/mensajes?proveedor=<?= $uid ?>" class="btn-accion btn-contactar">
                            <i class="bi bi-chat-dots"></i> Mensajes
                        </a>
                    </div>

                </div>
                <?php endforeach; ?>
                <?php endif; ?>

            </section>

        </div> <!-- FIN contenido-completados -->

    </main>

    <!-- Footer -->
    <footer></footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>const BASE_URL = "<?= BASE_URL ?>";</script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/completadas.js"></script>

</body>
</html>
