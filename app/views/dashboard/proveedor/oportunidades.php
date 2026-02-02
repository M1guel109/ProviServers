<?php
// 1. Seguridad de Sesión
require_once BASE_PATH . '/app/helpers/session_proveedor.php';

// 2. DATOS DE PRUEBA (Borrar cuando tengas el controlador real)
// if (!isset($necesidades)) {
//     $necesidades = [
//         [
//             'id' => 1,
//             'titulo' => 'Reparación de fuga en baño principal',
//             'descripcion' => 'Tengo una fuga constante en la llave del lavamanos y necesito cambiar el empaque o la llave completa. Es urgente.',
//             'ciudad' => 'Bogotá',
//             'zona' => 'Chapinero',
//             'presupuesto' => 80000,
//             'fecha' => '2026-02-15',
//             'categoria' => 'Plomería',
//             'cliente_nombre' => 'Ana María',
//             'cliente_foto' => 'default_user.png'
//         ],
//         [
//             'id' => 2,
//             'titulo' => 'Mantenimiento de Jardín Delantero',
//             'descripcion' => 'Necesito podar el césped, cortar unos arbustos que están muy altos y limpiar la maleza. El área es de aprox 20m2.',
//             'ciudad' => 'Medellín',
//             'zona' => 'El Poblado',
//             'presupuesto' => 150000,
//             'fecha' => '2026-02-20',
//             'categoria' => 'Jardinería',
//             'cliente_nombre' => 'Carlos Ruiz',
//             'cliente_foto' => 'default_user.png'
//         ],
//         [
//             'id' => 3,
//             'titulo' => 'Clases de Guitarra para Principiante',
//             'descripcion' => 'Busco profesor para mi hijo de 10 años. No tiene experiencia previa. Preferiblemente a domicilio los sábados.',
//             'ciudad' => 'Cali',
//             'zona' => 'Sur',
//             'presupuesto' => 50000,
//             'fecha' => '2026-02-10',
//             'categoria' => 'Educación',
//             'cliente_nombre' => 'Luisa F.',
//             'cliente_foto' => 'default_user.png'
//         ]
//     ];
// }

// echo "<div style='background:white; padding:10px; border:1px solid red; margin-bottom:20px;'>";
//     echo "<strong>🔍 DEBUG DE DATOS:</strong><br>";
//     echo "Total encontradas: " . count($necesidades) . "<br>";
//     echo "<pre>";
//     print_r($necesidades);
//     echo "</pre>";
//     echo "</div>";


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Oportunidades</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboard-Proveedor.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/oportunidades.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar_proveedor.php'; ?>

    <main class="contenido">
        <?php include_once __DIR__ . '/../../layouts/header_proveedor.php'; ?>

        <section class="mb-4">
            <h1 class="fw-bold mb-2">Explorar Oportunidades </h1>
            <p class="text-muted">Encuentra nuevos clientes que necesitan tus servicios hoy mismo.</p>
        </section>

        <section class="filtros-container">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 bg-light" placeholder="Buscar por título...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select">
                        <option value="">Categorías</option>
                        <option value="Hogar">Hogar</option>
                        <option value="Tecnología">Tecnología</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select">
                        <option value="">Ciudad</option>
                        <option value="Bogotá">Bogotá</option>
                        <option value="Medellín">Medellín</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">Filtrar</button>
                </div>
            </form>
        </section>

        <section>
            <?php if (empty($necesidades)): ?>
                <div class="empty-state">
                    <img src="<?= BASE_URL ?>/public/assets/img/illustrations/empty-search.svg" alt="Sin resultados">
                    <h4 class="text-muted">No hay oportunidades disponibles por ahora.</h4>
                    <p class="text-muted">Intenta ajustar los filtros o vuelve más tarde.</p>
                </div>
            <?php else: ?>
                
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($necesidades as $nec): ?>
                        <div class="col">
                            <div class="card card-oportunidad">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <span class="badge bg-light text-dark border">
                                            <?= htmlspecialchars($nec['categoria'] ?? 'General') ?>
                                        </span>
                                        <small class="text-muted fw-light">
                                            <i class="bi bi-clock"></i> Reciente
                                        </small>
                                    </div>

                                    <h5 class="card-title fw-bold text-dark mb-2">
                                        <?= htmlspecialchars($nec['titulo']) ?>
                                    </h5>

                                    <div class="d-flex gap-3 mb-3">
                                        <div class="meta-info">
                                            <i class="bi bi-geo-alt-fill text-danger"></i>
                                            <?= htmlspecialchars($nec['ciudad']) ?>
                                            <?= !empty($nec['zona']) ? '• ' . htmlspecialchars($nec['zona']) : '' ?>
                                        </div>
                                    </div>

                                    <p class="card-text text-secondary small">
                                        <?= htmlspecialchars(substr($nec['descripcion'], 0, 100)) ?>...
                                    </p>

                                    <div class="d-flex align-items-center gap-2 mt-3">
                                        <i class="bi bi-cash-stack text-success fs-5"></i>
                                        <div>
                                            <small class="d-block text-muted" style="line-height: 1;">Presupuesto est.</small>
                                            <span class="fw-bold text-dark">$ <?= number_format($nec['presupuesto_estimado'], 0, ',', '.') ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer bg-white border-top-0 pt-0 pb-3">
                                    <hr class="text-muted opacity-25 mt-0 mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">

                                            <?php 
                                                // 1. Calcular la ruta DE CADA cliente específico dentro del bucle
                                                $nombreFoto = $nec['cliente_foto'] ?? 'default_user.png';
                                                
                                                // Si no tiene foto o es la default, usamos la de assets
                                                if ($nombreFoto == 'default_user.png' || empty($nombreFoto)) {
                                                    $rutaFinal = BASE_URL . '/public/uploads/usuarios/default_user.png';
                                                } else {
                                                    // Si tiene foto, usamos la de uploads
                                                    $rutaFinal = BASE_URL . '/public/uploads/usuarios/' . $nombreFoto;
                                                }
                                            ?>

                                            <img src="<?= $rutaFinal ?>" 
                                                alt="Foto Cliente" 
                                                class="avatar-cliente-mini"
                                                style="width: 30px; height: 30px; object-fit: cover; border-radius: 50%;" 
                                                onerror="this.onerror=null; this.src='<?= BASE_URL ?>/public/assets/img/default_user.png';">

                                            <small class="text-muted"><?= htmlspecialchars($nec['cliente_nombre']) ?></small>
                                        </div>

                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalCotizar"
                                                data-id="<?= $nec['id'] ?>"
                                                data-titulo="<?= htmlspecialchars($nec['titulo']) ?>">
                                            Cotizar <i class="bi bi-arrow-right-short"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <div class="modal fade" id="modalCotizar" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="<?= BASE_URL ?>/proveedor/oportunidades/enviar-cotizacion" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title">Nueva Cotización</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        
                        <div class="modal-body">
                            <input type="hidden" name="necesidad_id" id="modal_necesidad_id">

                            <div class="alert alert-light border mb-3">
                                <small class="text-muted d-block">Estás aplicando a:</small>
                                <strong id="modal_titulo_necesidad" class="text-primary"></strong>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Título de tu Propuesta</label>
                                <input type="text" name="titulo" class="form-control" required 
                                    placeholder="Ej: Servicio completo con repuestos incluidos" maxlength="50">
                                <div class="form-text small">Dale un nombre corto y claro a tu oferta.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Tu Precio Final ($)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="precio_oferta" class="form-control" required 
                                        placeholder="Ej: 75000" min="1">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Tiempo Estimado</label>
                                <input type="text" name="tiempo_estimado" class="form-control" 
                                    placeholder="Ej: 2 días, 4 horas..." required maxlength="50">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Mensaje para el Cliente</label>
                                <textarea name="mensaje" class="form-control" rows="4" 
                                        placeholder="Hola, tengo experiencia en este tipo de trabajos. Incluyo..." required></textarea>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                Enviar Propuesta <i class="bi bi-send-fill ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/oportunidades.js"></script>
</body>
</html>