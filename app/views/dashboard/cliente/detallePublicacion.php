<?php
// Aquí asumimos que $publicacion viene desde el controlador
// y que ya se validó que no es null.

$titulo          = $publicacion['titulo'] ?? $publicacion['servicio_nombre'] ?? 'Servicio';
$servicioNombre  = $publicacion['servicio_nombre'] ?? '';
$servicioDesc    = $publicacion['servicio_descripcion'] ?? ($publicacion['publicacion_descripcion'] ?? '');
$servicioImg     = $publicacion['servicio_imagen'] ?? 'default_service.png';
$categoriaNombre = $publicacion['categoria_nombre'] ?? 'Sin categoría';

$precioRaw       = isset($publicacion['precio']) ? (float)$publicacion['precio'] : 0;
$precioFormato   = $precioRaw > 0 ? number_format($precioRaw, 2) : null;

$proveedorNombre    = $publicacion['proveedor_nombre'] ?? 'Proveedor';
$proveedorUbicacion = $publicacion['proveedor_ubicacion'] ?? 'Ubicación no especificada';
$proveedorFoto      = $publicacion['proveedor_foto'] ?? 'default_user.png';

$disponible = (int)($publicacion['servicio_disponible'] ?? 0) === 1;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Detalle del servicio</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <!-- Estilos específicos de cliente -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboardCliente.css">
</head>

<body>
    <!-- SIDEBAR -->
    <?php
    $currentPage = 'explorar';
    include_once __DIR__ . '/../../layouts/sidebar_cliente.php';
    ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="contenido">

        <!-- HEADER -->
        <?php include_once __DIR__ . '/../../layouts/header_cliente.php'; ?>

        <section id="detalle-servicio" class="mb-4">
            <!-- Breadcrumb + título -->
            <div class="section-hero mb-4">
                <p class="breadcrumb">
                    Inicio > Explorar Servicios > Detalle del servicio
                </p>
                <h1><?= htmlspecialchars($titulo) ?></h1>
                <p class="text-muted mb-0">
                    Revisa la información del servicio y del proveedor antes de solicitarlo.
                </p>
            </div>

            <div class="row g-4">
                <!-- Columna izquierda: imagen + descripción -->
                <div class="col-lg-8">
                    <div class="card service-card mb-4">
                        <div class="service-image">
                            <img src="<?= BASE_URL ?>/public/uploads/servicios/<?= htmlspecialchars($servicioImg) ?>"
                                alt="Imagen del servicio"
                                style="width: 100%; height: 260px; object-fit: cover;">
                        </div>
                        <div class="card-body service-content">
                            <h5 class="card-title mb-2"><?= htmlspecialchars($servicioNombre) ?></h5>
                            <p class="card-category mb-1">
                                <strong>Categoría:</strong>
                                <?= htmlspecialchars($categoriaNombre) ?>
                            </p>

                            <?php if ($precioFormato !== null): ?>
                                <p class="mb-2">
                                    <strong>Precio desde:</strong>
                                    $ <?= $precioFormato ?>
                                </p>
                            <?php endif; ?>

                            <hr>

                            <h6 class="mb-2">Descripción del servicio</h6>
                            <p class="card-text">
                                <?= nl2br(htmlspecialchars($servicioDesc ?: 'El proveedor aún no ha agregado una descripción detallada.')) ?>
                            </p>
                        </div>
                    </div>

                    <!-- Políticas de servicio (resumen) -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-file-earmark-text me-1"></i>
                                Políticas de servicio
                            </h5>
                            <p class="text-muted mb-2" style="font-size: 0.92rem;">
                                Aquí se mostrará un resumen de las políticas definidas por el proveedor
                                (cancelaciones, garantías, tiempos de respuesta, etc.).
                            </p>
                            <ul class="mb-0" style="font-size: 0.9rem;">
                                <li>Política de cancelación: <em>Próximamente desde configuración del proveedor.</em></li>
                                <li>Garantía del servicio: <em>Próximamente desde configuración del proveedor.</em></li>
                                <li>Tiempos de respuesta: <em>Próximamente desde configuración del proveedor.</em></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: proveedor + acción -->
                <div class="col-lg-4">
                    <!-- Info proveedor -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-person-circle me-1"></i>
                                Proveedor
                            </h5>

                            <div class="d-flex align-items-center mb-3">
                                <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= htmlspecialchars($proveedorFoto) ?>"
                                    alt="Foto proveedor"
                                    style="width: 58px; height: 58px; border-radius: 50%; object-fit: cover; margin-right: 12px;">
                                <div>
                                    <p class="mb-1 fw-semibold">
                                        <?= htmlspecialchars($proveedorNombre) ?>
                                    </p>
                                    <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        <?= htmlspecialchars($proveedorUbicacion) ?>
                                    </p>
                                </div>
                            </div>

                            <p class="mb-1" style="font-size: 0.9rem;">
                                <strong>Calificación:</strong>
                                <span class="text-warning">★ ★ ★ ★ ☆</span>
                                <span class="text-muted">(próximamente)</span>
                            </p>
                        </div>
                    </div>

                    <!-- Detalles rápidos + botón de acción -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-info-circle me-1"></i>
                                Detalles del servicio
                            </h5>

                            <p class="mb-1" style="font-size: 0.9rem;">
                                <strong>Estado:</strong>
                                <?php if ($disponible): ?>
                                    <span class="badge bg-success">Proveedor disponible</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No disponible temporalmente</span>
                                <?php endif; ?>
                            </p>

                            <?php if ($precioFormato !== null): ?>
                                <p class="mb-3" style="font-size: 0.9rem;">
                                    <strong>Precio de referencia:</strong><br>
                                    <span class="fs-5 fw-bold">$ <?= $precioFormato ?></span>
                                </p>
                            <?php endif; ?>

                            <hr>

                            <p class="text-muted" style="font-size: 0.85rem;">
                                La contratación se realiza a través de Proviservers. Tus datos de contacto
                                se comparten solo cuando se genere una solicitud formal.
                            </p>

                            <!-- Botón para iniciar solicitud (flujo a implementar) -->
                            <a href="<?= BASE_URL ?>/cliente/solicitar-servicio?id=<?= (int)$publicacion['id'] ?>"
                                class="btn btn-primary w-100 mt-2">
                                <i class="bi bi-hand-index-thumb me-1"></i>
                                Solicitar este servicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JS propio -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboardCliente.js"></script>
</body>

</html>
