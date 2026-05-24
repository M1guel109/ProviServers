<?php

require_once __DIR__ . '/../helpers/alert-helper.php';
require_once __DIR__ . '/../models/proveedor-perfil.php';
require_once __DIR__ . '/../models/proveedor-notificaciones.php';
require_once __DIR__ . '/../models/proveedor-pagos-facturacion.php';

// ===================================================================
// GUARD DE SESIÓN Y ROL
// ===================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']['id']) || ($_SESSION['user']['rol'] ?? '') !== 'proveedor') {
    mostrarSweetAlert('error', 'Acceso denegado', 'Solo proveedores pueden acceder a esta sección.', BASE_URL . '/login');
    exit();
}

// ===================================================================
// ROUTER INTERNO — Dispatch por método HTTP y acción
// ===================================================================

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'actualizar_perfil') {
            guardarPerfilProfesional();
        } elseif ($accion === 'actualizar_credenciales') {
            actualizarCredenciales();
        } elseif ($accion === 'actualizar_seguridad') {
            actualizarSeguridad();
        } elseif ($accion === 'cerrar_sesiones') {
            cerrarSesiones();
        } elseif ($accion === 'actualizar_disponibilidad') {
            guardarDisponibilidad();
        } elseif ($accion === 'guardar_notificaciones') {
            guardarNotificaciones();
        } elseif ($accion === 'guardar_pagos') {
            guardarPagos();
        } elseif ($accion === 'actualizar_politicas') {
            guardarPoliticas();
        } else {
            http_response_code(400);
            mostrarSweetAlert('error', 'Acción no válida', 'La acción POST solicitada no existe.');
            exit();
        }
        break;

    default:
        http_response_code(405);
        mostrarSweetAlert('error', 'Método no permitido', 'Esta ruta no acepta ese tipo de petición.');
        exit();
}

// ===================================================================
// FUNCIONES DEL CONTROLADOR
// ===================================================================

// -------------------------------------------------------------------
// PERFIL PROFESIONAL
// -------------------------------------------------------------------
function guardarPerfilProfesional()
{
    $idUsuario = (int)$_SESSION['user']['id'];

    $nombreComercial  = trim($_POST['nombre_comercial']   ?? '');
    $tipoProveedor    = trim($_POST['tipo_proveedor']     ?? '');
    $eslogan          = trim($_POST['eslogan']            ?? '');
    $descripcion      = trim($_POST['descripcion']        ?? '');
    $aniosExp         = trim($_POST['anios_experiencia']  ?? '');
    $ciudad           = trim($_POST['ciudad']             ?? '');
    $zona             = trim($_POST['zona']               ?? '');
    $telefonoContacto = trim($_POST['telefono_contacto']  ?? '');
    $whatsapp         = trim($_POST['whatsapp']           ?? '');
    $correoAlt        = trim($_POST['correo_alternativo'] ?? '');

    $idiomas    = $_POST['idiomas']    ?? [];
    $categorias = $_POST['categorias'] ?? [];

    if (
        empty($nombreComercial) || empty($tipoProveedor) ||
        empty($eslogan)         || empty($descripcion)   || empty($ciudad)
    ) {
        mostrarSweetAlert('error', 'Campos obligatorios', 'Nombre comercial, tipo, eslogan, descripción y ciudad son requeridos.', BASE_URL . '/proveedor/configuracion');
        exit();
    }

    if (empty($categorias)) {
        mostrarSweetAlert('error', 'Categoría requerida', 'Debes seleccionar al menos una categoría.', BASE_URL . '/proveedor/configuracion');
        exit();
    }

    $aniosExp      = ($aniosExp !== '' && is_numeric($aniosExp)) ? (int)$aniosExp : null;
    $idiomasCSV    = is_array($idiomas)    ? implode(',', $idiomas)    : '';
    $categoriasCSV = is_array($categorias) ? implode(',', $categorias) : '';

    $modelo       = new ProveedorPerfil();
    $perfilActual = $modelo->obtenerPerfilPorUsuario($idUsuario);
    $fotoFinal    = $perfilActual['foto'] ?? 'default_user.png';

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            mostrarSweetAlert('error', 'Formato no válido', 'Solo JPG, PNG o WEBP. Máx. 2MB.', BASE_URL . '/proveedor/configuracion');
            exit();
        }

        if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Imagen demasiado grande', 'La imagen no debe superar 2MB.', BASE_URL . '/proveedor/configuracion');
            exit();
        }

        $nuevoNombre = 'proveedor_' . $idUsuario . '_' . uniqid() . '.' . $ext;
        $destino     = BASE_PATH . '/public/uploads/usuarios/' . $nuevoNombre;

        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
            mostrarSweetAlert('error', 'Error al subir imagen', 'No se pudo guardar la imagen. Intenta nuevamente.', BASE_URL . '/proveedor/configuracion');
            exit();
        }

        $fotoFinal = $nuevoNombre;
    }

    $data = [
        'nombre_comercial'   => $nombreComercial,
        'tipo_proveedor'     => $tipoProveedor,
        'eslogan'            => $eslogan,
        'descripcion'        => $descripcion,
        'anios_experiencia'  => $aniosExp,
        'idiomas'            => $idiomasCSV,
        'categorias'         => $categoriasCSV,
        'ciudad'             => $ciudad,
        'zona'               => $zona,
        'foto'               => $fotoFinal,
        'telefono_contacto'  => $telefonoContacto,
        'whatsapp'           => $whatsapp,
        'correo_alternativo' => $correoAlt,
    ];

    $ok = $perfilActual
        ? $modelo->actualizarPerfil($idUsuario, $data)
        : $modelo->crearPerfil($idUsuario, $data);

    if ($ok) {
        mostrarSweetAlert('success', 'Perfil actualizado', 'Tu perfil profesional se guardó correctamente.', BASE_URL . '/proveedor/configuracion');
    } else {
        mostrarSweetAlert('error', 'Error al guardar', 'No se pudo guardar tu perfil. Intenta nuevamente.', BASE_URL . '/proveedor/configuracion');
    }
    exit();
}

// -------------------------------------------------------------------
// CREDENCIALES — email y/o contraseña
// -------------------------------------------------------------------
function actualizarCredenciales()
{
    $idUsuario = (int)$_SESSION['user']['id'];

    $emailNuevo        = trim($_POST['email_nuevo']        ?? '');
    $emailConfirmacion = trim($_POST['email_confirmacion'] ?? '');
    $claveActual       = $_POST['clave_actual']            ?? '';
    $nuevaClave        = $_POST['nueva_clave']             ?? '';
    $confirmarClave    = $_POST['confirmar_clave']         ?? '';

    $cambios = [];

    if (!empty($emailNuevo)) {
        if (!filter_var($emailNuevo, FILTER_VALIDATE_EMAIL)) {
            mostrarSweetAlert('error', 'Correo inválido', 'Ingresa un correo electrónico válido.', BASE_URL . '/proveedor/configuracion#cuenta');
            exit();
        }
        if ($emailNuevo !== $emailConfirmacion) {
            mostrarSweetAlert('error', 'Correos no coinciden', 'El nuevo correo y su confirmación deben ser iguales.', BASE_URL . '/proveedor/configuracion#cuenta');
            exit();
        }
        $cambios['email'] = $emailNuevo;
    }

    if (!empty($nuevaClave)) {
        if (strlen($nuevaClave) < 8) {
            mostrarSweetAlert('error', 'Contraseña muy corta', 'La nueva contraseña debe tener al menos 8 caracteres.', BASE_URL . '/proveedor/configuracion#cuenta');
            exit();
        }
        if ($nuevaClave !== $confirmarClave) {
            mostrarSweetAlert('error', 'Contraseñas no coinciden', 'La nueva contraseña y su confirmación deben ser iguales.', BASE_URL . '/proveedor/configuracion#cuenta');
            exit();
        }
        $cambios['clave'] = $nuevaClave;
    }

    if (empty($cambios)) {
        mostrarSweetAlert('info', 'Sin cambios', 'No enviaste ningún dato para actualizar.', BASE_URL . '/proveedor/configuracion#cuenta');
        exit();
    }

    if (empty($claveActual)) {
        mostrarSweetAlert('error', 'Contraseña requerida', 'Debes ingresar tu contraseña actual para confirmar los cambios.', BASE_URL . '/proveedor/configuracion#cuenta');
        exit();
    }

    $modelo    = new ProveedorPerfil();
    $resultado = $modelo->actualizarCredenciales($idUsuario, $claveActual, $cambios);

    switch ($resultado) {
        case 'ok':
            if (!empty($cambios['email'])) {
                $_SESSION['user']['email'] = $cambios['email'];
            }
            mostrarSweetAlert('success', 'Credenciales actualizadas', 'Tus datos de acceso se guardaron correctamente.', BASE_URL . '/proveedor/configuracion#cuenta');
            break;
        case 'clave_incorrecta':
            mostrarSweetAlert('error', 'Contraseña incorrecta', 'La contraseña actual ingresada no es correcta.', BASE_URL . '/proveedor/configuracion#cuenta');
            break;
        case 'email_duplicado':
            mostrarSweetAlert('error', 'Correo en uso', 'El correo ingresado ya está registrado en otra cuenta.', BASE_URL . '/proveedor/configuracion#cuenta');
            break;
        case 'sin_cambios':
            mostrarSweetAlert('info', 'Sin cambios', 'No se detectaron cambios para guardar.', BASE_URL . '/proveedor/configuracion#cuenta');
            break;
        default:
            mostrarSweetAlert('error', 'Error inesperado', 'Ocurrió un problema al guardar. Intenta nuevamente.', BASE_URL . '/proveedor/configuracion#cuenta');
    }
    exit();
}

// -------------------------------------------------------------------
// SEGURIDAD — alertas y tiempo de sesión
// -------------------------------------------------------------------
function actualizarSeguridad()
{
    $idUsuario = (int)$_SESSION['user']['id'];

    $data = [
        'alerta_solicitudes'   => isset($_POST['alerta_solicitudes']) ? 1 : 0,
        'alerta_resenas'       => isset($_POST['alerta_resenas'])     ? 1 : 0,
        'alerta_pagos'         => isset($_POST['alerta_pagos'])       ? 1 : 0,
        'canal_notificaciones' => $_POST['canal_notificaciones']      ?? 'ambos',
        'tiempo_sesion'        => (int)($_POST['tiempo_sesion']       ?? 60),
    ];

    $modelo = new ProveedorPerfil();
    $ok     = $modelo->guardarSeguridad($idUsuario, $data);

    if ($ok) {
        mostrarSweetAlert('success', 'Preferencias guardadas', 'Tus preferencias de seguridad se actualizaron correctamente.', BASE_URL . '/proveedor/configuracion#cuenta');
    } else {
        mostrarSweetAlert('error', 'Error al guardar', 'No se pudieron guardar tus preferencias. Intenta nuevamente.', BASE_URL . '/proveedor/configuracion#cuenta');
    }
    exit();
}

// -------------------------------------------------------------------
// CERRAR SESIONES — destruye la sesión activa
// -------------------------------------------------------------------
function cerrarSesiones()
{
    $_SESSION = [];
    session_unset();
    session_destroy();

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }

    mostrarSweetAlert('success', 'Sesión cerrada', 'Tu sesión se cerró correctamente.', BASE_URL . '/login');
    exit();
}

// -------------------------------------------------------------------
// DISPONIBILIDAD — horarios y zona de cobertura
// -------------------------------------------------------------------
function guardarDisponibilidad()
{
    $idUsuario = (int)$_SESSION['user']['id'];

    $diasTrabajo        = $_POST['dias_trabajo']        ?? [];
    $horaInicio         = trim($_POST['hora_inicio']    ?? '');
    $horaFin            = trim($_POST['hora_fin']       ?? '');
    $atiendeFinesSemana = isset($_POST['atiende_fines_semana']) ? 1 : 0;
    $atiendeFestivos    = isset($_POST['atiende_festivos'])     ? 1 : 0;
    $atencionUrgencias  = isset($_POST['atencion_urgencias'])   ? 1 : 0;
    $detalleUrgencias   = trim($_POST['detalle_urgencias']      ?? '');
    $tipoZona           = $_POST['tipo_zona']           ?? 'ciudad';
    $radioKm            = $_POST['radio_km']            ?? '';
    $zonasTexto         = trim($_POST['zonas_texto']    ?? '');

    if (empty($diasTrabajo)) {
        mostrarSweetAlert('error', 'Días requeridos', 'Selecciona al menos un día de trabajo.', BASE_URL . '/proveedor/configuracion#disponibilidad');
        exit();
    }

    if (empty($horaInicio) || empty($horaFin)) {
        mostrarSweetAlert('error', 'Horario requerido', 'Debes indicar una hora de inicio y una hora de fin.', BASE_URL . '/proveedor/configuracion#disponibilidad');
        exit();
    }

    if ($horaInicio >= $horaFin) {
        mostrarSweetAlert('error', 'Horario inválido', 'La hora de inicio debe ser menor que la hora de fin.', BASE_URL . '/proveedor/configuracion#disponibilidad');
        exit();
    }

    $tiposZonaPermitidos = ['ciudad', 'radio', 'varias_ciudades', 'remoto'];
    if (!in_array($tipoZona, $tiposZonaPermitidos, true)) {
        mostrarSweetAlert('error', 'Zona inválida', 'El tipo de zona de servicio seleccionado no es válido.', BASE_URL . '/proveedor/configuracion#disponibilidad');
        exit();
    }

    if ($tipoZona === 'radio' && ($radioKm === '' || !is_numeric($radioKm) || (int)$radioKm <= 0)) {
        mostrarSweetAlert('error', 'Radio inválido', 'Indica un radio en kilómetros mayor a cero.', BASE_URL . '/proveedor/configuracion#disponibilidad');
        exit();
    }

    $data = [
        'dias_trabajo'         => $diasTrabajo,
        'hora_inicio'          => $horaInicio,
        'hora_fin'             => $horaFin,
        'atiende_fines_semana' => $atiendeFinesSemana,
        'atiende_festivos'     => $atiendeFestivos,
        'atencion_urgencias'   => $atencionUrgencias,
        'detalle_urgencias'    => $detalleUrgencias,
        'tipo_zona'            => $tipoZona,
        'radio_km'             => $radioKm,
        'zonas_texto'          => $zonasTexto,
    ];

    $modelo = new ProveedorPerfil();
    $ok     = $modelo->guardarDisponibilidad($idUsuario, $data);

    if ($ok) {
        mostrarSweetAlert('success', 'Disponibilidad actualizada', 'Tu horario y zona de cobertura se guardaron correctamente.', BASE_URL . '/proveedor/configuracion#disponibilidad');
    } else {
        mostrarSweetAlert('error', 'Error al guardar', 'No se pudo guardar tu disponibilidad. Intenta nuevamente.', BASE_URL . '/proveedor/configuracion#disponibilidad');
    }
    exit();
}

// -------------------------------------------------------------------
// POLÍTICAS DE SERVICIO — cancelación, garantía y condiciones
// -------------------------------------------------------------------
function guardarPoliticas()
{
    $idUsuario = (int)$_SESSION['user']['id'];

    $tipoCancelacion        = $_POST['tipo_cancelacion']           ?? 'moderada';
    $descripcionCancelacion = trim($_POST['descripcion_cancelacion'] ?? '');
    $permiteReprogramar     = isset($_POST['permite_reprogramar'])   ? 1 : 0;
    $horasMinReprogramacion = trim($_POST['horas_min_reprogramacion'] ?? '');
    $cobraVisita            = isset($_POST['cobra_visita'])          ? 1 : 0;
    $valorVisita            = trim($_POST['valor_visita']           ?? '');
    $ofreceGarantia         = isset($_POST['ofrece_garantia'])       ? 1 : 0;
    $diasGarantia           = trim($_POST['dias_garantia']          ?? '');
    $detallesGarantia       = trim($_POST['detalles_garantia']      ?? '');
    $soloContactoPlataforma = isset($_POST['solo_contacto_por_plataforma']) ? 1 : 0;
    $tiempoRespuesta        = trim($_POST['tiempo_respuesta_promedio'] ?? '');
    $otrasCondiciones       = trim($_POST['otras_condiciones']      ?? '');

    $tiposCancelacion = ['flexible', 'moderada', 'estricta'];
    if (!in_array($tipoCancelacion, $tiposCancelacion, true)) {
        mostrarSweetAlert('error', 'Política inválida', 'El tipo de política de cancelación seleccionado no es válido.', BASE_URL . '/proveedor/configuracion#politicas');
        exit();
    }

    if ($permiteReprogramar && $horasMinReprogramacion !== '' && (!is_numeric($horasMinReprogramacion) || (int)$horasMinReprogramacion < 0)) {
        mostrarSweetAlert('error', 'Horas inválidas', 'Las horas mínimas para reprogramar deben ser un número igual o mayor a cero.', BASE_URL . '/proveedor/configuracion#politicas');
        exit();
    }

    if ($cobraVisita && ($valorVisita === '' || !is_numeric($valorVisita) || (float)$valorVisita <= 0)) {
        mostrarSweetAlert('error', 'Valor de visita requerido', 'Si cobras por visita, indica un valor válido mayor a cero.', BASE_URL . '/proveedor/configuracion#politicas');
        exit();
    }

    if ($ofreceGarantia && ($diasGarantia === '' || !is_numeric($diasGarantia) || (int)$diasGarantia <= 0)) {
        mostrarSweetAlert('error', 'Días de garantía requeridos', 'Si ofreces garantía, indica un número de días mayor a cero.', BASE_URL . '/proveedor/configuracion#politicas');
        exit();
    }

    if (strlen($tiempoRespuesta) > 50) {
        mostrarSweetAlert('error', 'Texto demasiado largo', 'El tiempo de respuesta promedio no puede superar 50 caracteres.', BASE_URL . '/proveedor/configuracion#politicas');
        exit();
    }

    $data = [
        'tipo_cancelacion'             => $tipoCancelacion,
        'descripcion_cancelacion'      => $descripcionCancelacion,
        'permite_reprogramar'          => $permiteReprogramar,
        'horas_min_reprogramacion'     => $horasMinReprogramacion,
        'cobra_visita'                 => $cobraVisita,
        'valor_visita'                 => $valorVisita,
        'ofrece_garantia'              => $ofreceGarantia,
        'dias_garantia'                => $diasGarantia,
        'detalles_garantia'            => $detallesGarantia,
        'solo_contacto_por_plataforma' => $soloContactoPlataforma,
        'tiempo_respuesta_promedio'    => $tiempoRespuesta,
        'otras_condiciones'            => $otrasCondiciones,
    ];

    $modelo = new ProveedorPerfil();
    $ok     = $modelo->guardarPoliticas($idUsuario, $data);

    if ($ok) {
        mostrarSweetAlert('success', 'Políticas actualizadas', 'Tus políticas de servicio se guardaron correctamente.', BASE_URL . '/proveedor/configuracion#politicas');
    } else {
        mostrarSweetAlert('error', 'Error al guardar', 'No se pudieron guardar tus políticas. Intenta nuevamente.', BASE_URL . '/proveedor/configuracion#politicas');
    }
    exit();
}

// -------------------------------------------------------------------
// NOTIFICACIONES (stub — PASO 4)
// -------------------------------------------------------------------
function guardarNotificaciones()
{
    mostrarSweetAlert('info', 'En construcción', 'Esta función estará disponible en el siguiente paso.', BASE_URL . '/proveedor/configuracion#notificaciones');
    exit();
}

// -------------------------------------------------------------------
// PAGOS Y FACTURACIÓN (stub — PASO 4)
// -------------------------------------------------------------------
function guardarPagos()
{
    mostrarSweetAlert('info', 'En construcción', 'Esta función estará disponible en el siguiente paso.', BASE_URL . '/proveedor/configuracion#pagos');
    exit();
}
