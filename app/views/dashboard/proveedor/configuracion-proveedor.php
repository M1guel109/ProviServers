<?php
require_once BASE_PATH . '/app/helpers/session-proveedor.php';
require_once BASE_PATH . '/app/models/proveedor-perfil.php';
require_once BASE_PATH . '/app/models/proveedor-notificaciones.php';
require_once BASE_PATH . '/app/models/proveedor-pagos-facturacion.php';

$idUsuario      = (int)($_SESSION['user']['id'] ?? 0);
$correoActual   = $_SESSION['user']['email'] ?? '';

$perfil           = [];
$seguridad        = [];
$disponibilidad   = [];
$notificaciones   = [];
$pagosFacturacion = [];

if ($idUsuario > 0) {
    try {
        $modeloPerfil = new ProveedorPerfil();

        $perfilBD = $modeloPerfil->obtenerPerfilPorUsuario($idUsuario);
        if ($perfilBD) $perfil = $perfilBD;

        $seguridadBD = $modeloPerfil->obtenerSeguridadPorUsuario($idUsuario);
        if ($seguridadBD) $seguridad = $seguridadBD;

        $dispBD = $modeloPerfil->obtenerDisponibilidadPorUsuario($idUsuario);
        if ($dispBD) $disponibilidad = $dispBD;

        $modeloNotif = new ProveedorNotificaciones();
        $notifBD = $modeloNotif->obtenerPorUsuario($idUsuario);
        if ($notifBD) $notificaciones = $notifBD;

        $modeloPagos = new ProveedorPagosFacturacion();
        $pagosBD = $modeloPagos->obtenerPorUsuario($idUsuario);
        if ($pagosBD) $pagosFacturacion = $pagosBD;
    } catch (Exception $e) {
        error_log('configuracion-proveedor.php: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-proveedor.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/configuracion-proveedor.css">
</head>

<body>
    <!-- SIDEBAR (lateral izquierdo) -->
    <?php
    include_once __DIR__ . '/../../layouts/sidebar-proveedor.php';
    ?>

    <main class="contenido">
        <?php
        include_once __DIR__ . '/../../layouts/header-proveedor.php';
        ?>

        <section id="titulo-principal" class="section-hero mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-1">Configuración</h1>
                    <p class="text-muted mb-0">Administra tu perfil, seguridad, notificaciones y forma de trabajo en la plataforma.</p>
                </div>
                <div class="col-md-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/proveedor/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Configuración</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <!-- Contenedor principal de configuración -->
        <section id="configuracion-proveedor">
            <!-- Tabs de configuración -->
            <ul class="nav nav-tabs" id="configTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="perfil-tab" data-bs-toggle="tab"
                        data-bs-target="#perfil" type="button" role="tab">
                        <i class="bi bi-person-circle me-1"></i> Perfil
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="disponibilidad-tab" data-bs-toggle="tab"
                        data-bs-target="#disponibilidad" type="button" role="tab">
                        <i class="bi bi-calendar-check me-1"></i> Disponibilidad
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pagos-tab" data-bs-toggle="tab"
                        data-bs-target="#pagos" type="button" role="tab">
                        <i class="bi bi-cash-stack me-1"></i> Pagos
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cuenta-tab" data-bs-toggle="tab"
                        data-bs-target="#cuenta" type="button" role="tab">
                        <i class="bi bi-shield-lock me-1"></i> Cuenta
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


                <!-- Cuenta -->
                <div class="tab-pane fade" id="cuenta" role="tabpanel" aria-labelledby="cuenta-tab">
                    <div class="tarjeta p-4 tarjeta-config">
                        <h2 class="mb-1">Cuenta y notificaciones</h2>
                        <p class="text-muted mb-4">Cambia tu contraseña, configura alertas y gestiona el acceso a tu cuenta.</p>

                        <div class="row g-4">
                            <!-- Columna izquierda: contraseña -->
                            <div class="col-lg-6">
                                <!-- Correo actual (solo lectura) -->
                                <div class="tarjeta-config-inner mb-4">
                                    <h5 class="mb-2">Correo de acceso</h5>
                                    <p class="text-muted small mb-2">Este es el correo con el que inicias sesión. Contacta al administrador para cambiarlo.</p>
                                    <input type="email" class="form-control" value="<?= htmlspecialchars($correoActual) ?>" readonly>
                                </div>

                                <!-- Cambiar contraseña -->
                                <div class="tarjeta-config-inner">
                                    <h5 class="mb-2">Contraseña</h5>
                                    <p class="text-muted" style="font-size: 0.9rem;">
                                        Te recomendamos usar una contraseña segura, con combinación de letras, números y símbolos.
                                    </p>

                                    <form action="<?= BASE_URL ?>/proveedor/actualizar-credenciales" method="POST" class="mt-3">
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

                                    <form action="<?= BASE_URL ?>/proveedor/actualizar-seguridad" method="POST">
                                        <div class="mb-3">
                                            <label class="form-label d-block">Alertas que quiero recibir</label>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="alerta_solicitudes"
                                                    name="alerta_solicitudes" value="1" <?= $alertaSolicitudes ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="alerta_solicitudes">
                                                    Nuevas solicitudes y cambios de estado de servicios
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="alerta_resenas"
                                                    name="alerta_resenas" value="1" <?= $alertaResenas ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="alerta_resenas">
                                                    Nuevas reseñas y calificaciones de clientes
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="alerta_pagos"
                                                    name="alerta_pagos" value="1" <?= $alertaPagos ? 'checked' : '' ?>>
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
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Tiempo de cierre de sesión por inactividad</label>
                                            <select name="tiempo_sesion" class="form-select">
                                                <option value="30" <?= $tiempoSesion === 30 ? 'selected' : '' ?>>30 minutos</option>
                                                <option value="60" <?= $tiempoSesion === 60 ? 'selected' : '' ?>>1 hora</option>
                                                <option value="120" <?= $tiempoSesion === 120 ? 'selected' : '' ?>>2 horas</option>
                                            </select>
                                        </div>

                                        <hr>

                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <small class="text-muted">
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

                                    <!-- 🔴 ELIMINAR CUENTA (AGREGADO, SIN TOCAR NADA MÁS) -->
                                    <hr class="my-3">

                                    <h6 class="text-danger mb-1">
                                        <i class="bi bi-exclamation-triangle me-1"></i> Eliminar cuenta
                                    </h6>
                                    <p class="text-muted" style="font-size: 0.85rem;">
                                        Esta acción es permanente y no se puede deshacer.
                                    </p>

                                    <!-- El botón ahora abre el modal -->
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalEliminarCuenta">
                                        <i class="bi bi-trash"></i> Eliminar cuenta definitivamente
                                    </button>

                                    <!-- El formulario puede estar oculto o se puede enviar vía AJAX, pero lo más simple es tener un formulario con id -->
                                    <form id="formEliminarCuentaModal" action="<?= BASE_URL ?>/proveedor/eliminar-cuenta" method="POST" style="display: none;"></form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="tab-pane fade" id="disponibilidad" role="tabpanel" aria-labelledby="disponibilidad-tab">
                    <div class="tarjeta p-4">
                        <h2 class="mb-3">Disponibilidad y zona de servicio</h2>
                        <p class="text-muted mb-4">
                            Configura tus días y horarios de trabajo, y define en qué zonas atiendes. Esto nos ayuda
                            a mostrarte solo solicitudes que realmente puedes cubrir.
                        </p>

                        <?php
                        // Preparar valores actuales
                        $diasSeleccionados = [];
                        if (!empty($disponibilidad['dias_semana'])) {
                            $diasSeleccionados = explode(',', $disponibilidad['dias_semana']);
                        }
                        $horaInicioActual = $disponibilidad['hora_inicio'] ?? '';
                        $horaFinActual    = $disponibilidad['hora_fin'] ?? '';

                        $atiendeFinesSemanaActual = !empty($disponibilidad['atiende_fines_semana']);
                        $atiendeFestivosActual    = !empty($disponibilidad['atiende_festivos']);
                        $atencionUrgenciasActual  = !empty($disponibilidad['atencion_urgencias']);

                        $tipoZonaActual = $disponibilidad['tipo_zona'] ?? 'ciudad';
                        $radioKmActual  = $disponibilidad['radio_km'] ?? '';
                        $zonasTextoActual = $disponibilidad['zonas_texto'] ?? '';
                        ?>

                        <form action="<?= BASE_URL ?>/proveedor/guardar-disponibilidad" method="POST">
                            <div class="row g-4">
                                <!-- Columna izquierda: días y horarios -->
                                <div class="col-lg-7">
                                    <h5 class="mb-3">Días y horarios de trabajo</h5>

                                    <!-- Días de la semana -->
                                    <div class="mb-3">
                                        <label class="form-label d-block">Días laborables <span class="text-danger">*</span></label>
                                        <?php
                                        $diasSemana = [
                                            'lun' => 'Lunes',
                                            'mar' => 'Martes',
                                            'mie' => 'Miércoles',
                                            'jue' => 'Jueves',
                                            'vie' => 'Viernes',
                                            'sab' => 'Sábado',
                                            'dom' => 'Domingo',
                                        ];
                                        ?>
                                        <div class="d-flex flex-wrap gap-2 disponibilidad-dias">
                                            <?php foreach ($diasSemana as $key => $label): ?>
                                                <div class="form-check form-check-inline disponibilidad-dia">
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        name="dias_trabajo[]"
                                                        id="dia_<?= $key ?>"
                                                        value="<?= $key ?>"
                                                        <?= in_array($key, $diasSeleccionados) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="dia_<?= $key ?>">
                                                        <?= $label ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            Selecciona al menos un día de trabajo.
                                        </small>
                                    </div>

                                    <!-- Horario general -->
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Hora de inicio general <span class="text-danger">*</span></label>
                                            <input
                                                type="time"
                                                name="hora_inicio"
                                                class="form-control"
                                                value="<?= htmlspecialchars($horaInicioActual) ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Hora de fin general <span class="text-danger">*</span></label>
                                            <input
                                                type="time"
                                                name="hora_fin"
                                                class="form-control"
                                                value="<?= htmlspecialchars($horaFinActual) ?>">
                                        </div>
                                    </div>

                                    <!-- Fines de semana / festivos / urgencias -->
                                    <div class="row g-3 mt-3">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="atiende_fines_semana"
                                                    name="atiende_fines_semana"
                                                    <?= $atiendeFinesSemanaActual ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="atiende_fines_semana">
                                                    Atiendo fines de semana
                                                </label>
                                            </div>
                                            <div class="form-check form-switch mt-2">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="atiende_festivos"
                                                    name="atiende_festivos"
                                                    <?= $atiendeFestivosActual ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="atiende_festivos">
                                                    Atiendo días festivos
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-check form-switch">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="atencion_urgencias"
                                                    name="atencion_urgencias"
                                                    <?= $atencionUrgenciasActual ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="atencion_urgencias">
                                                    Ofrezco atención de urgencias
                                                </label>
                                            </div>
                                            <small class="text-muted d-block mt-2">
                                                Por ejemplo: atenciones nocturnas, recargos, tiempo de respuesta estimado, etc.
                                            </small>
                                            <textarea
                                                name="detalle_urgencias"
                                                class="form-control mt-2"
                                                rows="2"
                                                placeholder="Describe cómo manejas las urgencias (horarios, recargos, tiempos de respuesta, etc.)"><?= htmlspecialchars($disponibilidad['detalle_urgencias'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Columna derecha: zona de servicio -->
                                <div class="col-lg-5">
                                    <h5 class="mb-3">Zona de servicio</h5>
                                    <p class="text-muted" style="font-size: 0.9rem;">
                                        Esta configuración nos ayuda a filtrar las solicitudes según tu cobertura. Así evitamos
                                        que te lleguen servicios que están demasiado lejos o fuera de lo que atiendes.
                                    </p>

                                    <!-- Tipo de zona -->
                                    <div class="mb-3">
                                        <label class="form-label">Tipo de zona principal</label>
                                        <select name="tipo_zona" class="form-select">
                                            <option value="ciudad" <?= $tipoZonaActual === 'ciudad' ? 'selected' : '' ?>>
                                                Solo en mi ciudad principal
                                            </option>
                                            <option value="radio" <?= $tipoZonaActual === 'radio' ? 'selected' : '' ?>>
                                                Radio en km alrededor de mi zona
                                            </option>
                                            <option value="varias_ciudades" <?= $tipoZonaActual === 'varias_ciudades' ? 'selected' : '' ?>>
                                                Varias ciudades / municipios
                                            </option>
                                            <option value="remoto" <?= $tipoZonaActual === 'remoto' ? 'selected' : '' ?>>
                                                Servicio remoto / online
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Radio en km -->
                                    <div class="mb-3">
                                        <label class="form-label">Radio máximo (km)</label>
                                        <input
                                            type="number"
                                            name="radio_km"
                                            class="form-control"
                                            min="1"
                                            max="500"
                                            placeholder="Ej: 10, 20, 50"
                                            value="<?= htmlspecialchars($radioKmActual) ?>">
                                        <small class="text-muted">
                                            Útil si atiendes a domicilio en un área cercana (Ej: hasta 15 km a la redonda).
                                        </small>
                                    </div>

                                    <!-- Zonas o ciudades específicas -->
                                    <div class="mb-3">
                                        <label class="form-label">Zonas o ciudades específicas</label>
                                        <textarea
                                            name="zonas_texto"
                                            class="form-control"
                                            rows="3"
                                            placeholder="Ej: Chapinero, Usaquén, Suba. O: Bogotá, Chía, Soacha."><?= htmlspecialchars($zonasTextoActual) ?></textarea>
                                        <small class="text-muted">
                                            Puedes escribir barrios, localidades o ciudades donde normalmente trabajas.
                                        </small>
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
                                        <i class="bi bi-save"></i> Guardar disponibilidad
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>


                <!-- Pagos y facturación -->
                <div class="tab-pane fade" id="pagos" role="tabpanel" aria-labelledby="pagos-tab">
                    <div class="tarjeta p-4">
                        <h2 class="mb-3">Pagos y facturación</h2>
                        <p class="text-muted mb-4">
                            Diligencia tus datos de facturación y la forma en que quieres recibir tus pagos. Esta información
                            no será visible para los clientes, solo para el equipo de Proviservers.
                        </p>

                        <?php
                        $tienePagos = !empty($pagosFacturacion);

                        $tipoDocumento        = $tienePagos ? ($pagosFacturacion['tipo_documento']        ?? '') : '';
                        $numeroDocumento      = $tienePagos ? ($pagosFacturacion['numero_documento']      ?? '') : '';
                        $razonSocial          = $tienePagos ? ($pagosFacturacion['razon_social']          ?? '') : '';
                        $regimenFiscal        = $tienePagos ? ($pagosFacturacion['regimen_fiscal']        ?? '') : '';
                        $direccionFacturacion = $tienePagos ? ($pagosFacturacion['direccion_facturacion'] ?? '') : '';
                        $ciudadFacturacion    = $tienePagos ? ($pagosFacturacion['ciudad_facturacion']    ?? '') : '';
                        $paisFacturacion      = $tienePagos ? ($pagosFacturacion['pais_facturacion']      ?? 'Colombia') : 'Colombia';
                        $correoFacturacion    = $tienePagos ? ($pagosFacturacion['correo_facturacion']    ?? '') : '';
                        $telefonoFacturacion  = $tienePagos ? ($pagosFacturacion['telefono_facturacion']  ?? '') : '';

                        $banco                = $tienePagos ? ($pagosFacturacion['banco']                 ?? '') : '';
                        $tipoCuenta           = $tienePagos ? ($pagosFacturacion['tipo_cuenta']           ?? '') : '';
                        $numeroCuenta         = $tienePagos ? ($pagosFacturacion['numero_cuenta']         ?? '') : '';
                        $titularCuenta        = $tienePagos ? ($pagosFacturacion['titular_cuenta']        ?? '') : '';
                        $identificacionTitular = $tienePagos ? ($pagosFacturacion['identificacion_titular'] ?? '') : '';
                        $metodoPagoPreferido  = $tienePagos ? ($pagosFacturacion['metodo_pago_preferido'] ?? '') : '';
                        $notaMetodoPago       = $tienePagos ? ($pagosFacturacion['nota_metodo_pago']      ?? '') : '';

                        $frecuenciaLiquidacion = $tienePagos ? ($pagosFacturacion['frecuencia_liquidacion'] ?? '') : '';
                        $montoMinimoRetiro    = $tienePagos ? ($pagosFacturacion['monto_minimo_retiro']   ?? '') : '';
                        $aceptaFacturaElectronica = $tienePagos ? !empty($pagosFacturacion['acepta_factura_electronica']) : false;
                        ?>

                        <form action="<?= BASE_URL ?>/proveedor/guardar-pagos" method="POST">
                            <div class="row g-4">
                                <!-- Columna izquierda: datos fiscales y de facturación -->
                                <div class="col-lg-6">
                                    <h5 class="mb-3">Datos fiscales / de facturación</h5>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Tipo de documento <span class="text-danger">*</span></label>
                                            <select name="tipo_documento" class="form-select">
                                                <option value="">Selecciona una opción</option>
                                                <option value="CC" <?= $tipoDocumento === 'CC'  ? 'selected' : '' ?>>Cédula de ciudadanía</option>
                                                <option value="CE" <?= $tipoDocumento === 'CE'  ? 'selected' : '' ?>>Cédula de extranjería</option>
                                                <option value="NIT" <?= $tipoDocumento === 'NIT' ? 'selected' : '' ?>>NIT</option>
                                                <option value="PASAPORTE" <?= $tipoDocumento === 'PASAPORTE' ? 'selected' : '' ?>>Pasaporte</option>
                                                <option value="OTRO" <?= $tipoDocumento === 'OTRO' ? 'selected' : '' ?>>Otro</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Número de documento <span class="text-danger">*</span></label>
                                            <input
                                                type="text"
                                                name="numero_documento"
                                                class="form-control"
                                                value="<?= htmlspecialchars($numeroDocumento) ?>">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Nombre o razón social <span class="text-danger">*</span></label>
                                            <input
                                                type="text"
                                                name="razon_social"
                                                class="form-control"
                                                placeholder="Ej: Juan Pérez / Servicios Eléctricos S.A.S."
                                                value="<?= htmlspecialchars($razonSocial) ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Régimen fiscal</label>
                                            <select name="regimen_fiscal" class="form-select">
                                                <option value="">No especificar</option>
                                                <option value="simplificado" <?= $regimenFiscal === 'simplificado' ? 'selected' : '' ?>>Régimen simplificado</option>
                                                <option value="comun" <?= $regimenFiscal === 'comun'        ? 'selected' : '' ?>>Régimen común</option>
                                                <option value="auto" <?= $regimenFiscal === 'auto'         ? 'selected' : '' ?>>Autorretenedor / responsable</option>
                                                <option value="otro" <?= $regimenFiscal === 'otro'         ? 'selected' : '' ?>>Otro</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Correo de facturación <span class="text-danger">*</span></label>
                                            <input
                                                type="email"
                                                name="correo_facturacion"
                                                class="form-control"
                                                value="<?= htmlspecialchars($correoFacturacion) ?>">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Dirección de facturación <span class="text-danger">*</span></label>
                                            <input
                                                type="text"
                                                name="direccion_facturacion"
                                                class="form-control"
                                                placeholder="Ej: Calle 123 #45-67"
                                                value="<?= htmlspecialchars($direccionFacturacion) ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Ciudad de facturación <span class="text-danger">*</span></label>
                                            <input
                                                type="text"
                                                name="ciudad_facturacion"
                                                class="form-control"
                                                placeholder="Ej: Bogotá"
                                                value="<?= htmlspecialchars($ciudadFacturacion) ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">País <span class="text-danger">*</span></label>
                                            <input
                                                type="text"
                                                name="pais_facturacion"
                                                class="form-control"
                                                value="<?= htmlspecialchars($paisFacturacion) ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Teléfono de facturación</label>
                                            <input
                                                type="text"
                                                name="telefono_facturacion"
                                                class="form-control"
                                                placeholder="Ej: +57 300 000 0000"
                                                value="<?= htmlspecialchars($telefonoFacturacion) ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Columna derecha: pagos y liquidación -->
                                <div class="col-lg-6">
                                    <h5 class="mb-3">Datos para pagos y liquidación</h5>

                                    <div class="mb-3">
                                        <label class="form-label">Banco</label>
                                        <input
                                            type="text"
                                            name="banco"
                                            class="form-control"
                                            placeholder="Ej: Bancolombia, Davivienda, Nequi, etc."
                                            value="<?= htmlspecialchars($banco) ?>">
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Tipo de cuenta</label>
                                            <select name="tipo_cuenta" class="form-select">
                                                <option value="">Selecciona una opción</option>
                                                <option value="ahorros" <?= $tipoCuenta === 'ahorros'    ? 'selected' : '' ?>>Cuenta de ahorros</option>
                                                <option value="corriente" <?= $tipoCuenta === 'corriente'  ? 'selected' : '' ?>>Cuenta corriente</option>
                                                <option value="nequi" <?= $tipoCuenta === 'nequi'      ? 'selected' : '' ?>>Nequi</option>
                                                <option value="daviplata" <?= $tipoCuenta === 'daviplata'  ? 'selected' : '' ?>>Daviplata</option>
                                                <option value="otro" <?= $tipoCuenta === 'otro'       ? 'selected' : '' ?>>Otro</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Número de cuenta</label>
                                            <input
                                                type="text"
                                                name="numero_cuenta"
                                                class="form-control"
                                                value="<?= htmlspecialchars($numeroCuenta) ?>">
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-1">
                                        <div class="col-md-6">
                                            <label class="form-label">Titular de la cuenta</label>
                                            <input
                                                type="text"
                                                name="titular_cuenta"
                                                class="form-control"
                                                value="<?= htmlspecialchars($titularCuenta) ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Identificación del titular</label>
                                            <input
                                                type="text"
                                                name="identificacion_titular"
                                                class="form-control"
                                                value="<?= htmlspecialchars($identificacionTitular) ?>">
                                        </div>
                                    </div>

                                    <div class="mt-3 mb-3">
                                        <label class="form-label">Método de pago preferido</label>
                                        <select name="metodo_pago_preferido" class="form-select">
                                            <option value="">Selecciona una opción</option>
                                            <option value="transferencia" <?= $metodoPagoPreferido === 'transferencia' ? 'selected' : '' ?>>Transferencia bancaria</option>
                                            <option value="billetera" <?= $metodoPagoPreferido === 'billetera'     ? 'selected' : '' ?>>Billetera digital</option>
                                            <option value="efectivo" <?= $metodoPagoPreferido === 'efectivo'      ? 'selected' : '' ?>>Efectivo (no recomendado)</option>
                                            <option value="otro" <?= $metodoPagoPreferido === 'otro'          ? 'selected' : '' ?>>Otro</option>
                                        </select>
                                        <textarea
                                            name="nota_metodo_pago"
                                            class="form-control mt-2"
                                            rows="2"
                                            placeholder="Notas adicionales sobre cómo prefieres recibir tus pagos (ej: solo Nequi, horario para confirmar pagos, etc.)"><?= htmlspecialchars($notaMetodoPago) ?></textarea>
                                    </div>

                                    <h5 class="mb-3 mt-4">Preferencias de liquidación</h5>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Frecuencia de liquidación</label>
                                            <select name="frecuencia_liquidacion" class="form-select">
                                                <option value="">A definir por la plataforma</option>
                                                <option value="semanal" <?= $frecuenciaLiquidacion === 'semanal'   ? 'selected' : '' ?>>Semanal</option>
                                                <option value="quincenal" <?= $frecuenciaLiquidacion === 'quincenal' ? 'selected' : '' ?>>Quincenal</option>
                                                <option value="mensual" <?= $frecuenciaLiquidacion === 'mensual'   ? 'selected' : '' ?>>Mensual</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Monto mínimo de retiro</label>
                                            <input
                                                type="number"
                                                step="0.01"
                                                name="monto_minimo_retiro"
                                                class="form-control"
                                                placeholder="Ej: 50000"
                                                value="<?= htmlspecialchars($montoMinimoRetiro) ?>">
                                            <small class="text-muted">
                                                Puedes dejarlo vacío si no tienes un mínimo definido.
                                            </small>
                                        </div>
                                    </div>

                                    <div class="form-check form-switch mt-3">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            id="acepta_factura_electronica"
                                            name="acepta_factura_electronica"
                                            <?= $aceptaFacturaElectronica ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="acepta_factura_electronica">
                                            Acepto recibir y emitir facturación electrónica cuando aplique
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Acciones -->
                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <div class="text-muted" style="font-size: 0.9rem;">
                                    <span class="text-danger">*</span> Algunos datos son obligatorios para poder liquidarte correctamente.
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="reset" class="btn-modern-outline">
                                        <i class="bi bi-arrow-counterclockwise"></i> Restablecer cambios
                                    </button>
                                    <button type="submit" class="btn-modern">
                                        <i class="bi bi-save"></i> Guardar configuración
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>


                <!-- Políticas de servicio -->

            </div>
        </section>
    </main>

    <!-- Modal de confirmación para eliminar cuenta -->
    <div class="modal fade modal-cliente" id="modalEliminarCuenta" tabindex="-1" aria-labelledby="modalEliminarCuentaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalEliminarCuentaLabel">
                        <i class="bi bi-exclamation-triangle-fill"></i> Eliminar cuenta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p>Esta acción es <strong>permanente e irreversible</strong>. Se borrarán todos tus datos, servicios y reseñas.</p>
                    <p>Para confirmar, escribe <strong class="text-danger">ELIMINAR</strong> en el campo de abajo:</p>
                    <input type="text" id="confirmarEliminarModal" class="form-control" placeholder="Escribe ELIMINAR" autocomplete="off">
                    <small class="text-muted">Debes escribir exactamente en mayúsculas.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarEliminarModal" disabled>Eliminar cuenta definitivamente</button>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <!-- Enlaces / Información adicional si lo necesitas -->
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <!-- JS del dashboard proveedor (si quieres reaprovechar comportamiento) -->
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/dashboard-proveedor.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/configuracion-proveedor.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
</body>

</html>
