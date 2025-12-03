<?php
// Protegemos la vista: solo proveedor logueado
$redirect_path = '/login';
require_once BASE_PATH . '/app/helpers/session_proveedor.php';
require_once BASE_PATH . '/app/models/ProveedorPerfil.php';
require_once BASE_PATH . '/app/models/ProveedorSeguridad.php';


$idUsuario = $_SESSION['user']['id'] ?? null;
$perfil    = [];

if ($idUsuario) {
    $modeloPerfil    = new ProveedorPerfil();
    $modeloSeguridad = new ProveedorSeguridad();

    $perfilBD    = $modeloPerfil->obtenerPerfilPorUsuario($idUsuario);
    $seguridadBD = $modeloSeguridad->obtenerPorUsuario($idUsuario);

    if ($perfilBD) {
        $perfil = $perfilBD;
    }

    if ($seguridadBD) {
        $seguridad = $seguridadBD;
    }
}
$correoActual = $_SESSION['user']['email'] ?? '';

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
                <!-- Cuenta y seguridad -->
                <div class="tab-pane fade" id="cuenta" role="tabpanel" aria-labelledby="cuenta-tab">
                    <div class="tarjeta p-4 tarjeta-config">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                            <div>
                                <h2 class="mb-1">Cuenta y seguridad</h2>
                                <p class="text-muted mb-0">
                                    Administra tu correo de acceso, contraseña y preferencias de seguridad de tu cuenta.
                                </p>
                            </div>
                            <span class="badge bg-light text-dark" style="font-size: 0.8rem;">
                                Última actualización:
                                <?= isset($seguridad['updated_at']) ? htmlspecialchars($seguridad['updated_at']) : 'Sin registros aún' ?>
                            </span>
                        </div>

                        <div class="row g-4">
                            <!-- Columna izquierda: datos de acceso -->
                            <div class="col-lg-6">
                                <!-- Actualizar correo -->
                                <div class="tarjeta-config-inner mb-4">
                                    <h5 class="mb-2">Correo de acceso</h5>
                                    <p class="text-muted" style="font-size: 0.9rem;">
                                        Este es el correo con el que inicias sesión en Proviservers.
                                        No se mostrará a los clientes.
                                    </p>

                                    <form action="<?= BASE_URL ?>/proveedor/actualizar-correo" method="POST" class="mt-3">
                                        <div class="mb-3">
                                            <label class="form-label">Correo actual</label>
                                            <input
                                                type="email"
                                                class="form-control"
                                                value="<?= htmlspecialchars($correoActual) ?>"
                                                readonly>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Nuevo correo <span class="text-danger">*</span></label>
                                            <input
                                                type="email"
                                                name="nuevo_correo"
                                                class="form-control"
                                                placeholder="Ej: proveedor@miempresa.com"
                                                required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Confirmar nuevo correo <span class="text-danger">*</span></label>
                                            <input
                                                type="email"
                                                name="confirmar_correo"
                                                class="form-control"
                                                placeholder="Vuelve a escribir el nuevo correo"
                                                required>
                                        </div>

                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn-modern btn-sm">
                                                <i class="bi bi-envelope-check"></i> Actualizar correo
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Cambiar contraseña -->
                                <div class="tarjeta-config-inner">
                                    <h5 class="mb-2">Contraseña</h5>
                                    <p class="text-muted" style="font-size: 0.9rem;">
                                        Te recomendamos usar una contraseña segura, con combinación de letras, números y símbolos.
                                    </p>

                                    <form action="<?= BASE_URL ?>/proveedor/cambiar-contrasena" method="POST" class="mt-3">
                                        <div class="mb-3">
                                            <label class="form-label">Contraseña actual <span class="text-danger">*</span></label>
                                            <input
                                                type="password"
                                                name="clave_actual"
                                                class="form-control"
                                                placeholder="Escribe tu contraseña actual"
                                                required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Nueva contraseña <span class="text-danger">*</span></label>
                                            <input
                                                type="password"
                                                name="nueva_clave"
                                                class="form-control"
                                                placeholder="Mínimo 8 caracteres"
                                                required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Confirmar nueva contraseña <span class="text-danger">*</span></label>
                                            <input
                                                type="password"
                                                name="confirmar_clave"
                                                class="form-control"
                                                placeholder="Vuelve a escribir la nueva contraseña"
                                                required>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <small class="text-muted">
                                                Si detectamos actividad inusual, podríamos pedirte que cambies tu contraseña.
                                            </small>
                                            <button type="submit" class="btn-modern btn-sm">
                                                <i class="bi bi-shield-lock"></i> Cambiar contraseña
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Columna derecha: preferencias de seguridad -->
                            <div class="col-lg-6">
                                <div class="tarjeta-config-inner mb-4">
                                    <h5 class="mb-2">Alertas y notificaciones</h5>
                                    <p class="text-muted" style="font-size: 0.9rem;">
                                        Elige sobre qué eventos quieres recibir alertas como proveedor.
                                    </p>

                                    <?php
                                    $alertaSolicitudes = !empty($seguridad['alerta_solicitudes']);
                                    $alertaResenas     = !empty($seguridad['alerta_resenas']);
                                    $alertaPagos       = !empty($seguridad['alerta_pagos']);
                                    $canalSeleccionado = $seguridad['canal_notificaciones'] ?? 'ambos';
                                    $tiempoSesion      = isset($seguridad['tiempo_sesion']) ? (int) $seguridad['tiempo_sesion'] : 60;
                                    ?>

                                    <form action="<?= BASE_URL ?>/proveedor/guardar-preferencias-seguridad" method="POST">
                                        <div class="mb-3">
                                            <label class="form-label d-block">Alertas que quiero recibir</label>

                                            <div class="form-check">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="alerta_solicitudes"
                                                    name="alerta_solicitudes"
                                                    value="1"
                                                    <?= $alertaSolicitudes ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="alerta_solicitudes">
                                                    Nuevas solicitudes y cambios de estado de servicios
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="alerta_resenas"
                                                    name="alerta_resenas"
                                                    value="1"
                                                    <?= $alertaResenas ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="alerta_resenas">
                                                    Nuevas reseñas y calificaciones de clientes
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="alerta_pagos"
                                                    name="alerta_pagos"
                                                    value="1"
                                                    <?= $alertaPagos ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="alerta_pagos">
                                                    Pagos, abonos y temas de facturación
                                                </label>
                                            </div>
                                        </div>

                                        <hr>

                                        <div class="mb-3">
                                            <label class="form-label">Canal principal de notificaciones</label>
                                            <select name="canal_notificaciones" class="form-select">
                                                <option value="ambos" <?= $canalSeleccionado === 'ambos' ? 'selected' : '' ?>>Correo y plataforma</option>
                                                <option value="correo" <?= $canalSeleccionado === 'correo' ? 'selected' : '' ?>>Solo correo</option>
                                                <option value="plataforma" <?= $canalSeleccionado === 'plataforma' ? 'selected' : '' ?>>Solo dentro de la plataforma</option>
                                            </select>
                                            <small class="text-muted">
                                                En el futuro podrás configurar esto también desde la app móvil.
                                            </small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Tiempo de cierre de sesión por inactividad</label>
                                            <select name="tiempo_sesion" class="form-select">
                                                <option value="30" <?= $tiempoSesion === 30  ? 'selected' : '' ?>>30 minutos</option>
                                                <option value="60" <?= $tiempoSesion === 60  ? 'selected' : '' ?>>1 hora</option>
                                                <option value="120" <?= $tiempoSesion === 120 ? 'selected' : '' ?>>2 horas</option>
                                            </select>
                                            <small class="text-muted">
                                                Si no detectamos actividad en ese tiempo, cerraremos tu sesión por seguridad.
                                            </small>
                                        </div>

                                        <hr>

                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <small class="text-muted" style="font-size: 0.85rem;">
                                                Puedes ajustar estas preferencias en cualquier momento.
                                            </small>
                                            <button type="submit" class="btn-modern btn-sm">
                                                <i class="bi bi-save"></i> Guardar preferencias
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Opciones avanzadas -->
                                <div class="tarjeta-config-inner tarjeta-config-avanzada">
                                    <h5 class="mb-2">Opciones avanzadas</h5>
                                    <p class="text-muted" style="font-size: 0.9rem;">
                                        Herramientas para mantener tu cuenta protegida.
                                    </p>

                                    <!-- Nota: esta acción requiere que luego implementes el controlador /proveedor/cerrar-sesiones -->
                                    <form action="<?= BASE_URL ?>/proveedor/cerrar-sesiones" method="POST"
                                        onsubmit="return confirm('Esto cerrará tu sesión en todos los dispositivos. ¿Quieres continuar?');">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-box-arrow-right"></i>
                                            Cerrar sesión en todos los dispositivos
                                        </button>
                                    </form>

                                    <small class="text-muted d-block mt-2" style="font-size: 0.8rem;">
                                        Úsalo si has iniciado sesión en un equipo compartido o sospechas de actividad no autorizada.
                                    </small>
                                </div>
                            </div>
                        </div>
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