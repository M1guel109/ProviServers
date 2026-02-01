<?php
// Solo clientes logueados
// require_once BASE_PATH . '/app/helpers/session_cliente.php';

// Modelos necesarios
require_once BASE_PATH . '/app/models/Publicacion.php';

$publicacionId = isset($_GET['id_publicacion']) ? (int)$_GET['id_publicacion'] : 0;
$publicacion   = null;

if ($publicacionId > 0) {
    $pubModel    = new Publicacion();
    $publicacion = $pubModel->obtenerPublicaActivaPorId($publicacionId);
}

if (!$publicacion) {
    // Si no hay publicación válida, puedes redirigir o mostrar un mensaje sencillo
    echo "<p>No se encontró la publicación seleccionada.</p>";
    exit();
}

// Datos auxiliares
$tituloSugerido = $publicacion['titulo'] ?? 'Solicitud de servicio';
$nombreServicio = $publicacion['servicio_nombre'] ?? '';
$precioBase     = isset($publicacion['precio']) ? (float)$publicacion['precio'] : 0;
$precioTexto    = $precioBase > 0 ? number_format($precioBase, 2) : null;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Solicitar servicio</title>

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

        <section id="solicitar-servicio" class="mb-4">
            <div class="section-hero mb-4">
                <p class="breadcrumb">Inicio > Explorar servicios > Solicitar servicio</p>
                <h1>Solicitar servicio</h1>
                <p>Completa los detalles para enviar tu solicitud al proveedor.</p>
            </div>

            <!-- Resumen de la publicación -->
            <div class="card mb-4">
                <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                    <div>
                        <h5 class="mb-1"><?= htmlspecialchars($publicacion['titulo'] ?? 'Servicio') ?></h5>
                        <p class="mb-1 text-muted">
                            Servicio: <strong><?= htmlspecialchars($nombreServicio) ?></strong>
                        </p>
                        <?php if ($precioTexto): ?>
                            <p class="mb-1 text-muted">
                                Precio de referencia: <strong>$ <?= $precioTexto ?></strong>
                            </p>
                        <?php endif; ?>
                        <p class="mb-0" style="max-width: 600px;">
                            <?= htmlspecialchars($publicacion['descripcion'] ?? '') ?>
                        </p>
                    </div>
                    <div class="text-md-end">
                        <span class="badge bg-success">Proveedor verificado</span>
                    </div>
                </div>
            </div>

            <!-- Formulario de solicitud -->
            <div class="card">
                <div class="card-body">
                    <form action="<?= BASE_URL ?>/cliente/guardar-solicitud"
                          method="POST"
                          enctype="multipart/form-data"
                          class="row g-3">

                        <!-- ID de la publicación -->
                        <input type="hidden" name="publicacion_id" value="<?= (int)$publicacionId ?>">

                        <!-- Título de la solicitud -->
                        <div class="col-12">
                            <label class="form-label">
                                Título de la solicitud <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                name="titulo"
                                class="form-control"
                                maxlength="120"
                                value="<?= htmlspecialchars($tituloSugerido) ?>"
                                placeholder="Ej: Reparación de fuga en baño principal"
                                required>
                            <small class="text-muted">
                                Un resumen corto de lo que necesitas.
                            </small>
                        </div>

                        <!-- Descripción detallada -->
                        <div class="col-12">
                            <label class="form-label">
                                Describe lo que necesitas <span class="text-danger">*</span>
                            </label>
                            <textarea
                                name="descripcion"
                                class="form-control"
                                rows="4"
                                placeholder="Explica el problema, qué ha pasado, qué esperas que haga el proveedor..."
                                required></textarea>
                            <small class="text-muted">
                                Cuantos más detalles des, más fácil será que el proveedor te dé una respuesta adecuada.
                            </small>
                        </div>

                        <!-- Dirección / ubicación -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Ciudad <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                name="ciudad"
                                class="form-control"
                                placeholder="Ej: Bogotá"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Zona o barrio
                            </label>
                            <input
                                type="text"
                                name="zona"
                                class="form-control"
                                placeholder="Ej: Chapinero, Cedritos, etc.">
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Dirección completa del servicio <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                name="direccion"
                                class="form-control"
                                placeholder="Ej: Calle 123 #45-67, apto 301"
                                required>
                        </div>

                        <!-- Fecha y franja horaria -->
                        <div class="col-md-4">
                            <label class="form-label">
                                Fecha preferida <span class="text-danger">*</span>
                            </label>
                            <input
                                type="date"
                                name="fecha_preferida"
                                class="form-control"
                                required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Franja horaria
                            </label>
                            <select name="franja_horaria" class="form-select">
                                <option value="">Sin preferencia</option>
                                <option value="manana">Mañana (8:00 - 12:00)</option>
                                <option value="tarde">Tarde (12:00 - 18:00)</option>
                                <option value="noche">Noche (18:00 - 21:00)</option>
                            </select>
                        </div>

                        <!-- Presupuesto -->
                        <div class="col-md-4">
                            <label class="form-label">
                                Presupuesto estimado (opcional)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input
                                    type="number"
                                    name="presupuesto"
                                    class="form-control"
                                    min="0"
                                    step="1000"
                                    placeholder="Ej: 80000">
                            </div>
                            <small class="text-muted">
                                Puedes dejarlo en blanco si prefieres que el proveedor te cotice.
                            </small>
                        </div>

                        <!-- Adjuntos -->
                        <div class="col-12">
                            <label class="form-label">
                                Adjuntar archivos (opcional)
                            </label>
                            <input
                                type="file"
                                name="adjuntos[]"
                                class="form-control"
                                multiple
                                accept=".pdf,image/*">
                            <small class="text-muted">
                                Puedes adjuntar fotos o un PDF con más detalles. Máx. 5 MB por archivo.
                            </small>
                        </div>

                        <!-- Acciones -->
                        <div class="col-12 d-flex justify-content-between flex-wrap gap-2 mt-3">
                            <a href="<?= BASE_URL ?>/cliente/explorar-servicios"
                               class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Volver al catálogo
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Enviar solicitud
                            </button>
                        </div>

                    </form>
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
