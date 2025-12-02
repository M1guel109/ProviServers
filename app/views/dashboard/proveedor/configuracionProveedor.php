<?php
// Protegemos la vista: solo proveedor logueado
$redirect_path = '/login';
require_once BASE_PATH . '/app/helpers/session_proveedor.php';
require_once BASE_PATH . '/app/models/ProveedorPerfil.php';

$idUsuario = $_SESSION['user']['id'] ?? null;
$perfil    = [];

if ($idUsuario) {
    $modeloPerfil = new ProveedorPerfil();
    $perfilBD = $modeloPerfil->obtenerPerfilPorUsuario($idUsuario);
    if ($perfilBD) {
        $perfil = $perfilBD;
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Configuración del Proveedor</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">

    <!-- Estilos específicos del dashboard proveedor -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboard-Proveedor.css">
</head>

<body>
    <!-- SIDEBAR (lateral izquierdo) -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar_proveedor.php';
    ?>

    <main class="contenido">
        <?php
        include_once __DIR__ . '/../../layouts/header_proveedor.php';
        ?>

        <!-- Título principal -->
        <section id="titulo-principal" class="mb-4">
            <h1>Configuración del Proveedor</h1>
            <p class="text-muted mb-0">
                Administra tu perfil, seguridad, notificaciones y forma de trabajo en la plataforma.
            </p>
        </section>

        <!-- Contenedor principal de configuración -->
        <section id="configuracion-proveedor">
            <!-- Tabs de configuración -->
            <ul class="nav nav-tabs" id="configTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="perfil-tab" data-bs-toggle="tab"
                        data-bs-target="#perfil" type="button" role="tab" aria-controls="perfil"
                        aria-selected="true">
                        <i class="bi bi-person-circle me-1"></i> Perfil profesional
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cuenta-tab" data-bs-toggle="tab"
                        data-bs-target="#cuenta" type="button" role="tab" aria-controls="cuenta"
                        aria-selected="false">
                        <i class="bi bi-shield-lock me-1"></i> Cuenta y seguridad
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="notificaciones-tab" data-bs-toggle="tab"
                        data-bs-target="#notificaciones" type="button" role="tab"
                        aria-controls="notificaciones" aria-selected="false">
                        <i class="bi bi-bell me-1"></i> Notificaciones
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="disponibilidad-tab" data-bs-toggle="tab"
                        data-bs-target="#disponibilidad" type="button" role="tab"
                        aria-controls="disponibilidad" aria-selected="false">
                        <i class="bi bi-calendar-check me-1"></i> Disponibilidad y zona de servicio
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pagos-tab" data-bs-toggle="tab"
                        data-bs-target="#pagos" type="button" role="tab" aria-controls="pagos"
                        aria-selected="false">
                        <i class="bi bi-cash-stack me-1"></i> Pagos y facturación
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="politicas-tab" data-bs-toggle="tab"
                        data-bs-target="#politicas" type="button" role="tab" aria-controls="politicas"
                        aria-selected="false">
                        <i class="bi bi-file-earmark-text me-1"></i> Políticas de servicio
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="preferencias-tab" data-bs-toggle="tab"
                        data-bs-target="#preferencias" type="button" role="tab"
                        aria-controls="preferencias" aria-selected="false">
                        <i class="bi bi-sliders me-1"></i> Preferencias del panel
                    </button>
                </li>
            </ul>

            <!-- Contenido de cada tab -->
            <div class="tab-content mt-4" id="configTabsContent">
                <!-- Perfil profesional -->
                <!-- Perfil profesional -->
                <div class="tab-pane fade show active" id="perfil" role="tabpanel" aria-labelledby="perfil-tab">
                    <div class="tarjeta p-4">
                        <h2 class="mb-3">Perfil profesional</h2>
                        <p class="text-muted mb-4">
                            Esta información será visible para tus clientes cuando te encuentren en Proviservers.
                            No mostraremos tu número ni tu correo; los clientes te contactarán a través de la plataforma.
                        </p>

                        <form action="<?= BASE_URL ?>/proveedor/guardar-perfil-profesional"
                            method="POST"
                            enctype="multipart/form-data">

                            <div class="row g-4">

                                <!-- Columna izquierda: Perfil público -->
                                <div class="col-lg-8">
                                    <h5 class="mb-3">Tu perfil público</h5>

                                    <div class="row g-3">
                                        <!-- Nombre comercial -->
                                        <div class="col-md-6">
                                            <label class="form-label">Nombre comercial <span class="text-danger">*</span></label>
                                            <input
                                                type="text"
                                                name="nombre_comercial"
                                                class="form-control"
                                                placeholder="Ej: Plomería Martínez"
                                                value="<?= htmlspecialchars($perfil['nombre_comercial'] ?? '') ?>">
                                        </div>

                                        <!-- Tipo de proveedor -->
                                        <div class="col-md-6">
                                            <label class="form-label">Tipo de proveedor <span class="text-danger">*</span></label>
                                            <select name="tipo_proveedor" class="form-select">
                                                <option value="">Selecciona una opción</option>
                                                <option value="persona"
                                                    <?= (isset($perfil['tipo_proveedor']) && $perfil['tipo_proveedor'] === 'persona') ? 'selected' : '' ?>>
                                                    Persona natural
                                                </option>
                                                <option value="empresa"
                                                    <?= (isset($perfil['tipo_proveedor']) && $perfil['tipo_proveedor'] === 'empresa') ? 'selected' : '' ?>>
                                                    Empresa
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Eslogan corto -->
                                        <div class="col-12">
                                            <label class="form-label">Eslogan corto <span class="text-danger">*</span></label>
                                            <input
                                                type="text"
                                                name="eslogan"
                                                class="form-control"
                                                maxlength="120"
                                                placeholder="Ej: Especialistas en plomería residencial 24/7"
                                                value="<?= htmlspecialchars($perfil['eslogan'] ?? '') ?>">
                                            <small class="text-muted">
                                                Una frase breve que resuma lo que haces. Se mostrará junto a tu nombre.
                                            </small>
                                        </div>

                                        <!-- Descripción profesional -->
                                        <div class="col-12">
                                            <label class="form-label">Descripción profesional <span class="text-danger">*</span></label>
                                            <textarea
                                                name="descripcion"
                                                class="form-control"
                                                rows="4"
                                                placeholder="Cuenta tu experiencia, cómo trabajas, qué te diferencia, etc."><?= htmlspecialchars($perfil['descripcion'] ?? '') ?></textarea>
                                            <small class="text-muted">
                                                Esto aparecerá en tu perfil y ayuda a generar confianza con el cliente.
                                            </small>
                                        </div>

                                        <!-- Años de experiencia -->
                                        <div class="col-md-4">
                                            <label class="form-label">Años de experiencia</label>
                                            <input
                                                type="number"
                                                name="anios_experiencia"
                                                class="form-control"
                                                min="0"
                                                max="80"
                                                value="<?= htmlspecialchars($perfil['anios_experiencia'] ?? '') ?>">
                                        </div>

                                        <!-- Idiomas -->
                                        <div class="col-md-4">
                                            <label class="form-label">Idiomas que hablas</label>
                                            <select name="idiomas[]" class="form-select" multiple>
                                                <?php
                                                // Opciones estáticas por ahora; luego puedes cargarlas desde BD
                                                $idiomasDisponibles = ['Español', 'Inglés', 'Portugués', 'Francés'];
                                                $idiomasSeleccionados = $perfil['idiomas'] ?? []; // idealmente como array
                                                if (!is_array($idiomasSeleccionados)) {
                                                    $idiomasSeleccionados = explode(',', (string) $idiomasSeleccionados);
                                                }
                                                foreach ($idiomasDisponibles as $idioma):
                                                ?>
                                                    <option value="<?= $idioma ?>"
                                                        <?= in_array($idioma, $idiomasSeleccionados) ? 'selected' : '' ?>>
                                                        <?= $idioma ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted">
                                                Mantén presionada Ctrl (o Cmd en Mac) para seleccionar varios.
                                            </small>
                                        </div>

                                        <!-- Categorías principales -->
                                        <div class="col-md-4">
                                            <label class="form-label">Categorías principales <span class="text-danger">*</span></label>
                                            <select name="categorias[]" class="form-select" multiple>
                                                <!-- Por ahora estático; luego puedes reemplazar por un foreach con categorías desde BD -->
                                                <?php
                                                $categoriasDisponibles = ['Plomería', 'Electricidad', 'Limpieza', 'Jardinería', 'Pintura', 'Belleza', 'Mascotas'];
                                                $categoriasSeleccionadas = $perfil['categorias'] ?? [];
                                                if (!is_array($categoriasSeleccionadas)) {
                                                    $categoriasSeleccionadas = explode(',', (string) $categoriasSeleccionadas);
                                                }
                                                foreach ($categoriasDisponibles as $categoria):
                                                ?>
                                                    <option value="<?= $categoria ?>"
                                                        <?= in_array($categoria, $categoriasSeleccionadas) ? 'selected' : '' ?>>
                                                        <?= $categoria ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted">
                                                Elige 1 a 3 categorías donde realmente te especializas.
                                            </small>
                                        </div>

                                        <!-- Ubicación visible -->
                                        <div class="col-md-6">
                                            <label class="form-label">Ciudad principal <span class="text-danger">*</span></label>
                                            <input
                                                type="text"
                                                name="ciudad"
                                                class="form-control"
                                                placeholder="Ej: Bogotá, Colombia"
                                                value="<?= htmlspecialchars($perfil['ciudad'] ?? '') ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Barrio o zona principal</label>
                                            <input
                                                type="text"
                                                name="zona"
                                                class="form-control"
                                                placeholder="Ej: Chapinero, El Poblado, etc."
                                                value="<?= htmlspecialchars($perfil['zona'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Columna derecha: Foto + datos internos -->
                                <div class="col-lg-4">
                                    <!-- Foto / logo -->
                                    <div class="mb-4">
                                        <h5 class="mb-3">Foto o logotipo</h5>
                                        <div class="d-flex flex-column align-items-center">
                                            <?php
                                            $foto = $perfil['foto'] ?? 'default_user.png';
                                            ?>
                                            <img src="<?= BASE_URL ?>/public/uploads/usuarios/<?= htmlspecialchars($foto) ?>"
                                                alt="Foto proveedor"
                                                style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary-color); margin-bottom: 1rem;">

                                            <label class="form-label w-100 text-center">Cambiar imagen</label>
                                            <input
                                                type="file"
                                                name="foto"
                                                class="form-control"
                                                accept="image/*">
                                            <small class="text-muted d-block mt-1 text-center">
                                                Formatos permitidos: JPG, PNG. Tamaño máximo sugerido: 2MB.
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Datos internos -->
                                    <div>
                                        <h5 class="mb-3">Datos internos (no visibles para el cliente)</h5>
                                        <p class="text-muted" style="font-size: 0.9rem;">
                                            Usaremos estos datos para enviarte notificaciones y coordinar aspectos internos.
                                            <strong>No se mostrarán en tu perfil público.</strong>
                                        </p>

                                        <div class="mb-3">
                                            <label class="form-label">Teléfono de contacto</label>
                                            <input
                                                type="text"
                                                name="telefono_contacto"
                                                class="form-control"
                                                placeholder="Ej: +57 300 000 0000"
                                                value="<?= htmlspecialchars($perfil['telefono_contacto'] ?? '') ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">WhatsApp</label>
                                            <input
                                                type="text"
                                                name="whatsapp"
                                                class="form-control"
                                                placeholder="Ej: +57 300 000 0000"
                                                value="<?= htmlspecialchars($perfil['whatsapp'] ?? '') ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Correo alternativo</label>
                                            <input
                                                type="email"
                                                name="correo_alternativo"
                                                class="form-control"
                                                placeholder="Ej: proveedor@miempresa.com"
                                                value="<?= htmlspecialchars($perfil['correo_alternativo'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Acciones -->
                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <div class="text-muted" style="font-size: 0.9rem;">
                                    <span class="text-danger">*</span> Campos obligatorios
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="reset" class="btn-modern-outline">
                                        <i class="bi bi-arrow-counterclockwise"></i> Restablecer cambios
                                    </button>
                                    <button type="submit" class="btn-modern">
                                        <i class="bi bi-save"></i> Guardar perfil
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>


                <!-- Cuenta y seguridad -->
                <div class="tab-pane fade" id="cuenta" role="tabpanel" aria-labelledby="cuenta-tab">
                    <div class="tarjeta p-4">
                        <h2 class="mb-3">Cuenta y seguridad</h2>
                        <p class="text-muted mb-0">
                            Aquí irá el formulario para actualizar tu correo, contraseña, seguridad de la cuenta y
                            opciones avanzadas (como cierre de sesión en todos los dispositivos).
                        </p>
                    </div>
                </div>

                <!-- Notificaciones -->
                <div class="tab-pane fade" id="notificaciones" role="tabpanel" aria-labelledby="notificaciones-tab">
                    <div class="tarjeta p-4">
                        <h2 class="mb-3">Notificaciones</h2>
                        <p class="text-muted mb-0">
                            Aquí podrás elegir qué notificaciones deseas recibir (solicitudes, reseñas, pagos, etc.) y
                            por qué canales (correo, notificaciones internas, etc.).
                        </p>
                    </div>
                </div>

                <!-- Disponibilidad y zona de servicio -->
                <div class="tab-pane fade" id="disponibilidad" role="tabpanel" aria-labelledby="disponibilidad-tab">
                    <div class="tarjeta p-4">
                        <h2 class="mb-3">Disponibilidad y zona de servicio</h2>
                        <p class="text-muted mb-0">
                            Aquí configurarás tus días y horarios de trabajo, así como las zonas o ciudades donde
                            prestas servicios.
                        </p>
                    </div>
                </div>

                <!-- Pagos y facturación -->
                <div class="tab-pane fade" id="pagos" role="tabpanel" aria-labelledby="pagos-tab">
                    <div class="tarjeta p-4">
                        <h2 class="mb-3">Pagos y facturación</h2>
                        <p class="text-muted mb-0">
                            Aquí irá el formulario para definir tus datos bancarios, información fiscal y preferencias de
                            liquidación de pagos.
                        </p>
                    </div>
                </div>

                <!-- Políticas de servicio -->
                <div class="tab-pane fade" id="politicas" role="tabpanel" aria-labelledby="politicas-tab">
                    <div class="tarjeta p-4">
                        <h2 class="mb-3">Políticas de servicio</h2>
                        <p class="text-muted mb-0">
                            Aquí podrás definir tus políticas de cancelación, garantías, tiempos de respuesta y otras
                            condiciones que verán tus clientes antes de contratar.
                        </p>
                    </div>
                </div>

                <!-- Preferencias del panel -->
                <div class="tab-pane fade" id="preferencias" role="tabpanel" aria-labelledby="preferencias-tab">
                    <div class="tarjeta p-4">
                        <h2 class="mb-3">Preferencias del panel</h2>
                        <p class="text-muted mb-0">
                            Aquí configurarás preferencias visuales y de uso del panel: página de inicio por defecto,
                            idioma (en el futuro), formato de hora, etc.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <!-- Enlaces / Información adicional si lo necesitas -->
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- JS del dashboard proveedor (si quieres reaprovechar comportamiento) -->
    <script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboardProveedor.js"></script>
</body>

</html>