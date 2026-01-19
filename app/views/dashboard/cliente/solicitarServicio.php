<?php


// id de la publicación que viene desde el catálogo / detalle
$publicacion_id = (int)($_GET['id'] ?? 0);

if ($publicacion_id <= 0) {
    die('Publicación no válida');
}

// Opcional: podrías aquí, más adelante, cargar un resumen de la publicación
// require_once BASE_PATH . '/app/models/Publicacion.php';
// $pubModel = new Publicacion();
// $publicacion = $pubModel->obtenerPorIdPublica($idPublicacion); // método a crear si lo deseas
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

        <section id="solicitar-servicio" class="mt-2">
            <!-- Migas de pan -->
            <div class="section-hero mb-4">
                <p class="breadcrumb">
                    Inicio > Explorar servicios > Solicitar servicio
                </p>
                <h1>Solicitar servicio</h1>
                <p>Cuéntale al proveedor qué necesitas y cuándo te gustaría que te atienda.</p>
            </div>

            <div class="row">
                <!-- Formulario principal -->
                <div class="col-lg-8 mb-4">
                    <div class="card p-4">
                        <h5 class="mb-3">Detalles de tu solicitud</h5>


                        <form action="<?= BASE_URL ?>/cliente/guardar-solicitud" method="POST" enctype="multipart/form-data">
                            <!-- Publicación a la que se asocia la solicitud -->
                            

                            <input type="hidden" name="publicacion_id" value="<?= $publicacion_id ?>">




                            <!-- Título / asunto de la solicitud -->
                            <div class="mb-3">
                                <label class="form-label">Título de la solicitud <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    name="titulo"
                                    class="form-control"
                                    maxlength="120"
                                    placeholder="Ej: Reparación de fuga en baño principal"
                                    required>
                                <small class="text-muted">
                                    Un resumen breve para que el proveedor entienda rápidamente de qué se trata.
                                </small>
                            </div>

                            <!-- Descripción del problema / necesidad -->
                            <div class="mb-3">
                                <label class="form-label">Describe lo que necesitas <span class="text-danger">*</span></label>
                                <textarea
                                    name="descripcion"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Ej: Tengo una fuga de agua en el baño principal, cerca del lavamanos. Empezó hace dos días..."
                                    required></textarea>
                                <small class="text-muted">
                                    Sé lo más claro posible: contexto, tiempo del problema, accesos, restricciones, etc.
                                </small>
                            </div>

                            <!-- Dirección / ubicación del servicio -->
                            <div class="mb-3">
                                <label class="form-label">Dirección donde se prestará el servicio <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    name="direccion"
                                    class="form-control"
                                    placeholder="Ej: Calle 123 #45-67, Apto 301"
                                    required>
                                <small class="text-muted">
                                    Esta información solo la verá el proveedor asignado, no será pública.
                                </small>
                            </div>

                            <!-- Ciudad / zona -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Ciudad <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="ciudad"
                                        class="form-control"
                                        placeholder="Ej: Bogotá"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Barrio o zona</label>
                                    <input
                                        type="text"
                                        name="zona"
                                        class="form-control"
                                        placeholder="Ej: Chapinero, Suba, El Poblado...">
                                </div>
                            </div>

                            <!-- Fecha y horario deseado -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Fecha tentativa del servicio <span class="text-danger">*</span></label>
                                    <input
                                        type="date"
                                        name="fecha_preferida"
                                        class="form-control"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Horario preferido</label>
                                    <select name="franja_horaria" class="form-select">
                                        <option value="">Cualquiera</option>
                                        <option value="manana">Mañana (8:00 - 12:00)</option>
                                        <option value="tarde">Tarde (12:00 - 18:00)</option>
                                        <option value="noche">Noche (18:00 - 22:00)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Presupuesto opcional -->
                            <div class="mb-3">
                                <label class="form-label">Presupuesto estimado (opcional)</label>
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
                                    Si tienes una idea de cuánto puedes pagar, ayuda a filtrar expectativas.
                                </small>
                            </div>

                            <!-- Adjuntos opcionales -->
                            <div class="mb-3">
                                <label class="form-label">Adjuntar fotos o archivos (opcional)</label>
                                <input
                                    type="file"
                                    name="adjuntos[]"
                                    class="form-control"
                                    accept="image/*,application/pdf"
                                    multiple>
                                <small class="text-muted">
                                    Puedes subir fotos del problema o documentos relevantes. Máx. 5 archivos.
                                </small>
                            </div>

                            <hr class="my-4">

                            <!-- Botones -->
                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <a href="<?= BASE_URL ?>/cliente/explorar" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Volver a explorar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Enviar solicitud al proveedor
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Columna derecha: resumen del servicio (dummy por ahora) -->
                <div class="col-lg-4">
                    <div class="card p-3 mb-3">
                        <h6 class="mb-3">Resumen del servicio</h6>

                        <!-- Aquí luego pintas datos reales de la publicación -->
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <img src="<?= BASE_URL ?>/public/assets/dashBoard/img/imagen-servicio.png"
                                    alt="Servicio"
                                    style="width:64px;height:64px;border-radius:8px;object-fit:cover;">
                            </div>
                            <div>
                                <p class="mb-1 fw-semibold" style="font-size: 0.95rem;">
                                    Servicio seleccionado
                                </p>
                                <p class="mb-0 text-muted" style="font-size: 0.85rem;">
                                    Aquí mostraremos el título real de la publicación.
                                </p>
                            </div>
                        </div>

                        <p class="text-muted mb-1" style="font-size: 0.85rem;">
                            Recuerda que esta solicitud no implica un pago inmediato. El proveedor podrá contactarte
                            por la mensajería interna para confirmar detalles y acordar el precio final.
                        </p>
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