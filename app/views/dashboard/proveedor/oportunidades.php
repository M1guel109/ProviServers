<?php
require_once BASE_PATH . '/app/helpers/session_proveedor.php';
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/oportunidades.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../layouts/sidebar_proveedor.php'; ?>

    <main class="contenido oportunidades-page">
        <?php include_once __DIR__ . '/../../layouts/header_proveedor.php'; ?>

        <section class="mb-4">
            <h1 class="fw-bold mb-2">Explorar oportunidades</h1>
            <p class="text-muted">Encuentra nuevos clientes que necesitan tus servicios hoy mismo.</p>
        </section>

        <section class="filtros-container">
            <form action="<?= BASE_URL ?>/proveedor/oportunidades" method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            type="text"
                            name="q"
                            value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                            class="form-control border-start-0 bg-light"
                            placeholder="Buscar por título...">
                    </div>
                </div>

                <div class="col-md-3">
                    <select class="form-select" name="categoria">
                        <option value="">Categorías</option>
                        <option value="Hogar" <?= (($_GET['categoria'] ?? '') === 'Hogar') ? 'selected' : '' ?>>Hogar</option>
                        <option value="Tecnología" <?= (($_GET['categoria'] ?? '') === 'Tecnología') ? 'selected' : '' ?>>Tecnología</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select class="form-select" name="ciudad">
                        <option value="">Ciudad</option>
                        <option value="Bogotá" <?= (($_GET['ciudad'] ?? '') === 'Bogotá') ? 'selected' : '' ?>>Bogotá</option>
                        <option value="Medellín" <?= (($_GET['ciudad'] ?? '') === 'Medellín') ? 'selected' : '' ?>>Medellín</option>
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
                        <?php
                        $nombreFoto = $nec['cliente_foto'] ?? 'default_user.png';

                        if ($nombreFoto === 'default_user.png' || empty($nombreFoto)) {
                            $rutaFinal = BASE_URL . '/public/uploads/usuarios/default_user.png';
                        } else {
                            $rutaFinal = BASE_URL . '/public/uploads/usuarios/' . $nombreFoto;
                        }

                        $presupuestoTexto = !empty($nec['presupuesto_estimado'])
                            ? '$ ' . number_format((float)$nec['presupuesto_estimado'], 0, ',', '.')
                            : 'No especificado';

                        $descripcionCorta = mb_substr($nec['descripcion'] ?? '', 0, 100);
                        if (mb_strlen($nec['descripcion'] ?? '') > 100) {
                            $descripcionCorta .= '...';
                        }
                        ?>
                        <div class="col">
                            <div class="card card-oportunidad h-100">
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
                                            <?= !empty($nec['zona']) ? ' • ' . htmlspecialchars($nec['zona']) : '' ?>
                                        </div>
                                    </div>

                                    <p class="card-text text-secondary small">
                                        <?= htmlspecialchars($descripcionCorta) ?>
                                    </p>

                                    <div class="d-flex align-items-center gap-2 mt-3">
                                        <i class="bi bi-cash-stack text-success fs-5"></i>
                                        <div>
                                            <small class="d-block text-muted" style="line-height: 1;">Presupuesto est.</small>
                                            <span class="fw-bold text-dark"><?= $presupuestoTexto ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer bg-white border-top-0 pt-0 pb-3">
                                    <hr class="text-muted opacity-25 mt-0 mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?= $rutaFinal ?>"
                                                alt="Foto Cliente"
                                                class="avatar-cliente-mini"
                                                style="width: 30px; height: 30px; object-fit: cover; border-radius: 50%;"
                                                onerror="this.onerror=null; this.src='<?= BASE_URL ?>/public/uploads/usuarios/default_user.png';">

                                            <small class="text-muted"><?= htmlspecialchars($nec['cliente_nombre']) ?></small>
                                        </div>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalCotizar"
                                            data-id="<?= (int)$nec['id'] ?>"
                                            data-titulo="<?= htmlspecialchars($nec['titulo'], ENT_QUOTES, 'UTF-8') ?>">
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
                <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/proveedor/oportunidades/enviar-cotizacion">
                    <input type="hidden" name="accion" value="enviar_cotizacion">
                    <input type="hidden" name="necesidad_id" id="modal_necesidad_id" value="">

                    <div class="modal-header">
                        <h5 class="modal-title">Enviar cotización</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <p class="text-muted mb-3">
                            Vas a cotizar la necesidad:
                            <strong id="modal_necesidad_titulo">-</strong>
                        </p>

                        <?php if (empty($publicacionesProveedor)): ?>
                            <div class="alert alert-warning mb-0">
                                No tienes publicaciones aprobadas disponibles para cotizar esta necesidad.
                            </div>
                        <?php else: ?>

                            <div class="mb-3">
                                <label class="form-label">
                                    Publicación con la que atenderás esta necesidad
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="publicacion_id" class="form-select" required>
                                    <option value="">Selecciona una publicación</option>

                                    <?php foreach ($publicacionesProveedor as $pub): ?>
                                        <option value="<?= (int)$pub['id'] ?>">
                                            <?= htmlspecialchars($pub['titulo']) ?>
                                            <?php if (!empty($pub['servicio_nombre'])): ?>
                                                · <?= htmlspecialchars($pub['servicio_nombre']) ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Título de la oferta <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    name="titulo"
                                    class="form-control"
                                    required
                                    maxlength="50">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mensaje</label>
                                <textarea
                                    name="mensaje"
                                    class="form-control"
                                    rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Precio <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    name="precio_oferta"
                                    class="form-control"
                                    min="0"
                                    step="1000"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tiempo estimado</label>
                                <input
                                    type="text"
                                    name="tiempo_estimado"
                                    class="form-control"
                                    maxlength="50"
                                    placeholder="Ej: 2 días">
                            </div>

                        <?php endif; ?>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>

                        <?php if (!empty($publicacionesProveedor)): ?>
                            <button type="submit" class="btn btn-primary">Enviar cotización</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalCotizar = document.getElementById('modalCotizar');

            if (modalCotizar) {
                modalCotizar.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    if (!button) return;

                    const necesidadId = button.getAttribute('data-id') || '';
                    const necesidadTitulo = button.getAttribute('data-titulo') || '';

                    const inputNecesidadId = modalCotizar.querySelector('#modal_necesidad_id');
                    const textoTitulo = modalCotizar.querySelector('#modal_necesidad_titulo');

                    if (inputNecesidadId) {
                        inputNecesidadId.value = necesidadId;
                    }

                    if (textoTitulo) {
                        textoTitulo.textContent = necesidadTitulo;
                    }
                });
            }
        });
    </script>

    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/oportunidades.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/main.js"></script>
</body>

</html>