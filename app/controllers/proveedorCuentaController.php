<?php
// app/controllers/proveedorCuentaController.php

require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../helpers/session_proveedor.php';
require_once __DIR__ . '/../../config/database.php';

// Solo aceptamos POST en este controlador
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Método no permitido";
    exit();
}

// Reconstruimos la ruta actual para saber qué acción ejecutar
$requestUri = $_SERVER['REQUEST_URI'];
$request    = str_replace('/ProviServers', '', $requestUri);
$request    = strtok($request, '?');

$idUsuario = $_SESSION['user']['id'] ?? null;

if (!$idUsuario) {
    mostrarSweetAlert('error', 'Sesión no válida', 'Debes iniciar sesión nuevamente.', BASE_URL . '/login');
    exit();
}

// Conexión a BD
try {
    $db  = new Conexion();
    $pdo = $db->getConexion();
} catch (PDOException $e) {
    error_log('Error de conexión en proveedorCuentaController: ' . $e->getMessage());
    mostrarSweetAlert('error', 'Error interno', 'No fue posible conectar con la base de datos.');
    exit();
}

// Enrutamos según la ruta
switch ($request) {
    case '/proveedor/actualizar-credenciales':
        actualizarCredenciales($pdo, $idUsuario);
        break;

    case '/proveedor/actualizar-seguridad':
        actualizarSeguridad($pdo, $idUsuario);
        break;

    case '/proveedor/cerrar-sesiones':
        cerrarSesiones();
        break;

    default:
        http_response_code(404);
        echo "Ruta no encontrada en proveedorCuentaController";
        exit();
}

/**
 * Actualiza correo y/o contraseña del proveedor.
 * Espera (en el formulario):
 *  - email_actual (opcional, solo informativo)
 *  - email_nuevo
 *  - email_confirmacion
 *  - clave_actual
 *  - nueva_clave
 *  - confirmar_clave
 */
function actualizarCredenciales(PDO $pdo, int $idUsuario): void
{
    $emailNuevo        = trim($_POST['email_nuevo'] ?? '');
    $emailConfirmacion = trim($_POST['email_confirmacion'] ?? '');
    $claveActual       = $_POST['clave_actual'] ?? '';
    $nuevaClave        = $_POST['nueva_clave'] ?? '';
    $confirmarClave    = $_POST['confirmar_clave'] ?? '';

    // Validación básica
    if (empty($emailNuevo) && empty($nuevaClave)) {
        mostrarSweetAlert(
            'info',
            'Sin cambios',
            'No enviaste ningún cambio de correo ni de contraseña.',
            BASE_URL . '/proveedor/configuracion#cuenta'
        );
        exit();
    }

    // Traer usuario actual
    $sql  = "SELECT email, clave FROM usuarios WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        mostrarSweetAlert(
            'error',
            'Usuario no encontrado',
            'No fue posible localizar tu cuenta.',
            BASE_URL . '/login'
        );
        exit();
    }

    // Si se intenta cambiar algo, exigimos clave actual
    if ((!empty($emailNuevo) || !empty($nuevaClave)) && empty($claveActual)) {
        mostrarSweetAlert(
            'error',
            'Falta la contraseña actual',
            'Para cambiar correo o contraseña, debes ingresar tu contraseña actual.',
            BASE_URL . '/proveedor/configuracion#cuenta'
        );
        exit();
    }

    // Verificar clave actual
    if (!empty($claveActual) && !password_verify($claveActual, $usuario['clave'])) {
        mostrarSweetAlert(
            'error',
            'Contraseña incorrecta',
            'La contraseña actual no coincide.',
            BASE_URL . '/proveedor/configuracion#cuenta'
        );
        exit();
    }

    $camposUpdate = [];
    $params       = [':id' => $idUsuario];

    // Cambio de correo
    if (!empty($emailNuevo)) {
        if (!filter_var($emailNuevo, FILTER_VALIDATE_EMAIL)) {
            mostrarSweetAlert(
                'error',
                'Correo inválido',
                'Ingresa un correo electrónico válido.',
                BASE_URL . '/proveedor/configuracion#cuenta'
            );
            exit();
        }

        if ($emailNuevo !== $emailConfirmacion) {
            mostrarSweetAlert(
                'error',
                'Correos no coinciden',
                'El correo nuevo y su confirmación deben coincidir.',
                BASE_URL . '/proveedor/configuracion#cuenta'
            );
            exit();
        }

        // Verificar que no exista ya en otro usuario
        $sqlCheck = "SELECT id FROM usuarios WHERE email = :email AND id <> :id LIMIT 1";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->bindParam(':email', $emailNuevo, PDO::PARAM_STR);
        $stmtCheck->bindParam(':id', $idUsuario, PDO::PARAM_INT);
        $stmtCheck->execute();

        if ($stmtCheck->fetch()) {
            mostrarSweetAlert(
                'error',
                'Correo en uso',
                'El correo ingresado ya está registrado en otra cuenta.',
                BASE_URL . '/proveedor/configuracion#cuenta'
            );
            exit();
        }

        $camposUpdate[]   = 'email = :email';
        $params[':email'] = $emailNuevo;
    }

    // Cambio de contraseña
    if (!empty($nuevaClave)) {
        if (strlen($nuevaClave) < 8) {
            mostrarSweetAlert(
                'error',
                'Contraseña muy corta',
                'La nueva contraseña debe tener al menos 8 caracteres.',
                BASE_URL . '/proveedor/configuracion#cuenta'
            );
            exit();
        }

        if ($nuevaClave !== $confirmarClave) {
            mostrarSweetAlert(
                'error',
                'Contraseñas no coinciden',
                'La nueva contraseña y su confirmación deben coincidir.',
                BASE_URL . '/proveedor/configuracion#cuenta'
            );
            exit();
        }

        $hashClave           = password_hash($nuevaClave, PASSWORD_DEFAULT);
        $camposUpdate[]      = 'clave = :clave';
        $params[':clave']    = $hashClave;
    }

    if (empty($camposUpdate)) {
        mostrarSweetAlert(
            'info',
            'Sin cambios',
            'No se detectaron cambios para actualizar.',
            BASE_URL . '/proveedor/configuracion#cuenta'
        );
        exit();
    }

    try {
        $sqlUpdate = "UPDATE usuarios SET " . implode(', ', $camposUpdate) . " WHERE id = :id";
        $stmtUpd   = $pdo->prepare($sqlUpdate);
        $stmtUpd->execute($params);

        // Si cambiamos correo, actualizamos la sesión
        if (!empty($emailNuevo)) {
            $_SESSION['user']['email'] = $emailNuevo;
        }

        mostrarSweetAlert(
            'success',
            'Datos actualizados',
            'Tu correo y/o contraseña se actualizaron correctamente.',
            BASE_URL . '/proveedor/configuracion#cuenta'
        );
        exit();
    } catch (PDOException $e) {
        error_log('Error al actualizar credenciales proveedor: ' . $e->getMessage());
        mostrarSweetAlert(
            'error',
            'Error al guardar',
            'Ocurrió un problema guardando tus cambios. Inténtalo de nuevo.',
            BASE_URL . '/proveedor/configuracion#cuenta'
        );
        exit();
    }
}

/**
 * Guarda preferencias de seguridad/notificaciones del proveedor.
 * Espera (sugerido en el formulario):
 *  - alerta_solicitudes (on/off)
 *  - alerta_reseñas (on/off)
 *  - alerta_pagos (on/off)
 *  - canal_notificaciones (correo|plataforma|ambos)
 *  - tiempo_sesion (int, minutos)
 *
 * Recomendado: tabla proveedor_seguridad (o similar) con usuario_id UNIQUE.
 */
function actualizarSeguridad(PDO $pdo, int $idUsuario): void
{
    // Normalizamos valores desde el formulario
    $alertaSolicitudes = isset($_POST['alerta_solicitudes']) ? 1 : 0;
    $alertaResenas     = isset($_POST['alerta_reseñas']) ? 1 : 0;
    $alertaPagos       = isset($_POST['alerta_pagos']) ? 1 : 0;

    $canalNotificaciones = $_POST['canal_notificaciones'] ?? 'ambos';
    $tiempoSesion        = (int)($_POST['tiempo_sesion'] ?? 60);

    // Sanitizar canal
    $canalesPermitidos = ['correo', 'plataforma', 'ambos'];
    if (!in_array($canalNotificaciones, $canalesPermitidos, true)) {
        $canalNotificaciones = 'ambos';
    }

    if ($tiempoSesion <= 0) {
        $tiempoSesion = 60;
    }

    try {
        // Verificamos si ya existe configuración para este usuario
        $sqlCheck = "SELECT id FROM proveedor_seguridad WHERE usuario_id = :usuario_id LIMIT 1";
        $stmtChk  = $pdo->prepare($sqlCheck);
        $stmtChk->bindParam(':usuario_id', $idUsuario, PDO::PARAM_INT);
        $stmtChk->execute();
        $existe = $stmtChk->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            // UPDATE
            $sqlUpd = "UPDATE proveedor_seguridad
                       SET alerta_solicitudes = :alerta_solicitudes,
                           alerta_reseñas     = :alerta_reseñas,
                           alerta_pagos       = :alerta_pagos,
                           canal_notificaciones = :canal_notificaciones,
                           tiempo_sesion      = :tiempo_sesion,
                           updated_at         = NOW()
                       WHERE usuario_id = :usuario_id";
            $stmtUpd = $pdo->prepare($sqlUpd);
        } else {
            // INSERT
            $sqlUpd = "INSERT INTO proveedor_seguridad
                       (usuario_id, alerta_solicitudes, alerta_reseñas, alerta_pagos,
                        canal_notificaciones, tiempo_sesion, created_at, updated_at)
                       VALUES
                       (:usuario_id, :alerta_solicitudes, :alerta_reseñas, :alerta_pagos,
                        :canal_notificaciones, :tiempo_sesion, NOW(), NOW())";
            $stmtUpd = $pdo->prepare($sqlUpd);
        }

        $stmtUpd->bindParam(':usuario_id', $idUsuario, PDO::PARAM_INT);
        $stmtUpd->bindParam(':alerta_solicitudes', $alertaSolicitudes, PDO::PARAM_INT);
        $stmtUpd->bindParam(':alerta_reseñas', $alertaResenas, PDO::PARAM_INT);
        $stmtUpd->bindParam(':alerta_pagos', $alertaPagos, PDO::PARAM_INT);
        $stmtUpd->bindParam(':canal_notificaciones', $canalNotificaciones, PDO::PARAM_STR);
        $stmtUpd->bindParam(':tiempo_sesion', $tiempoSesion, PDO::PARAM_INT);

        $stmtUpd->execute();

        mostrarSweetAlert(
            'success',
            'Seguridad actualizada',
            'Tus preferencias de seguridad y notificaciones se guardaron correctamente.',
            BASE_URL . '/proveedor/configuracion#cuenta'
        );
        exit();
    } catch (PDOException $e) {
        error_log('Error al actualizar seguridad proveedor: ' . $e->getMessage());
        mostrarSweetAlert(
            'error',
            'Error al guardar seguridad',
            'Ocurrió un problema guardando tus preferencias. Puedes intentarlo más tarde.',
            BASE_URL . '/proveedor/configuracion#cuenta'
        );
        exit();
    }
}

/**
 * Cierra la sesión del proveedor (similar a logoutController).
 * OJO: esto cierra la sesión actual. Para "todos los dispositivos" se requiere
 * una estrategia adicional (tokens, versión de sesión, etc.).
 */
function cerrarSesiones(): void
{
    // Cerramos la sesión actual
    session_unset();
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }

    // Eliminamos cookie de sesión si existe
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    mostrarSweetAlert(
        'success',
        'Sesión cerrada',
        'Tu sesión se ha cerrado correctamente.',
        BASE_URL . '/login'
    );
    exit();
}
