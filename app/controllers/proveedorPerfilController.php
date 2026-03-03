<?php
// Importamos las dependencias necesarias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../../config/database.php';

// Si ya unificaste los modelos en uno solo llamado "Proveedor.php", cambia estos requires por ese único archivo.
// Por ahora dejo los que estabas usando en tus archivos originales para que no se rompa nada.
require_once __DIR__ . '/../models/ProveedorPerfil.php';
require_once __DIR__ . '/../models/ProveedorDisponibilidad.php';
require_once __DIR__ . '/../models/ProveedorPoliticasServicio.php';

// 1. VALIDACIÓN GLOBAL DE SESIÓN Y ROL
session_start();

if (!isset($_SESSION['user']['id']) || ($_SESSION['user']['rol'] ?? '') !== 'proveedor') {
    mostrarSweetAlert('error', 'Acceso denegado', 'Solo proveedores pueden acceder a esta sección.', '/ProviServers/login');
    exit();
}

// Capturamos el método de la solicitud
$method = $_SERVER['REQUEST_METHOD'];

// 2. ENRUTADOR PRINCIPAL (Switch)
switch ($method) {
    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'actualizar_perfil') {
            actualizarPerfil();
        } 
        elseif ($accion === 'actualizar_credenciales') {
            actualizarCredenciales();
        } 
        elseif ($accion === 'actualizar_seguridad') {
            actualizarSeguridad();
        } 
        elseif ($accion === 'actualizar_disponibilidad') {
            actualizarDisponibilidad();
        } 
        elseif ($accion === 'actualizar_politicas') {
            actualizarPoliticas();
        }
        elseif ($accion === 'cerrar_sesiones') {
            cerrarSesiones();
        } 
        else {
            http_response_code(400);
            echo "Acción POST no válida";
        }
        break;

    case 'GET':
        // Por el momento no tienes acciones GET aquí, pero queda listo por si a futuro decides mostrar vistas desde este controlador.
        http_response_code(405);
        echo "Método no permitido para esta ruta";
        break;

    default:
        http_response_code(405);
        echo "Método no permitido";
        break;
}

// ======================================================================
// 3. FUNCIONES DEL CONTROLADOR
// ======================================================================

function actualizarPerfil()
{
    $idUsuario = (int)$_SESSION['user']['id'];

    // 1. Capturamos y saneamos datos del formulario
    $nombreComercial  = trim($_POST['nombre_comercial'] ?? '');
    $tipoProveedor    = trim($_POST['tipo_proveedor'] ?? '');
    $eslogan          = trim($_POST['eslogan'] ?? '');
    $descripcion      = trim($_POST['descripcion'] ?? '');
    $aniosExp         = trim($_POST['anios_experiencia'] ?? '');
    $ciudad           = trim($_POST['ciudad'] ?? '');
    $zona             = trim($_POST['zona'] ?? '');
    $telefonoContacto = trim($_POST['telefono_contacto'] ?? '');
    $whatsapp         = trim($_POST['whatsapp'] ?? '');
    $correoAlt        = trim($_POST['correo_alternativo'] ?? '');

    $idiomasSeleccionados    = $_POST['idiomas']    ?? [];
    $categoriasSeleccionadas = $_POST['categorias'] ?? [];

    // 2. Validar campos obligatorios
    $errores = [];

    if ($nombreComercial === '') $errores[] = 'El nombre comercial es obligatorio.';
    if ($tipoProveedor === '')   $errores[] = 'Debes seleccionar el tipo de proveedor.';
    if ($eslogan === '')         $errores[] = 'El eslogan es obligatorio.';
    if ($descripcion === '')     $errores[] = 'La descripción profesional es obligatoria.';
    if ($ciudad === '')          $errores[] = 'La ciudad principal es obligatoria.';
    if (empty($categoriasSeleccionadas)) $errores[] = 'Debes seleccionar al menos una categoría principal.';

    if (!empty($errores)) {
        $mensaje = implode('<br>', $errores);
        mostrarSweetAlert('error', 'Faltan datos', $mensaje, '/ProviServers/proveedor/configuracion');
        exit();
    }

    // 3. Normalizar datos opcionales
    $aniosExp = ($aniosExp !== '' && is_numeric($aniosExp)) ? (int) $aniosExp : null;
    $idiomasCSV     = is_array($idiomasSeleccionados)    ? implode(',', $idiomasSeleccionados)    : '';
    $categoriasCSV  = is_array($categoriasSeleccionadas) ? implode(',', $categoriasSeleccionadas) : '';

    // 4. Obtenemos perfil actual (si existe)
    $modeloPerfil = new ProveedorPerfil();
    $perfilActual = $modeloPerfil->obtenerPerfilPorUsuario($idUsuario);
    $fotoFinal = $perfilActual['foto'] ?? 'default_user.png';

    // 5. Procesar imagen
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath  = $_FILES['foto']['tmp_name'];
        $fileName     = $_FILES['foto']['name'];
        $fileSize     = $_FILES['foto']['size'];

        $fileNameCmps = explode('.', $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($fileExtension, $extensionesPermitidas)) {
            mostrarSweetAlert('error', 'Formato no permitido', 'Solo se permiten imágenes JPG, JPEG, PNG o WEBP.', '/ProviServers/proveedor/configuracion');
            exit();
        }

        if ($fileSize > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Imagen demasiado grande', 'La imagen no debe superar los 2MB.', '/ProviServers/proveedor/configuracion');
            exit();
        }

        $uploadDir = BASE_PATH . '/public/uploads/usuarios/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $nuevoNombre = 'proveedor_' . $idUsuario . '_' . time() . '.' . $fileExtension;
        $destPath    = $uploadDir . $nuevoNombre;

        if (!move_uploaded_file($fileTmpPath, $destPath)) {
            mostrarSweetAlert('error', 'Error al subir imagen', 'Ocurrió un error al guardar la imagen. Intenta nuevamente.', '/ProviServers/proveedor/configuracion');
            exit();
        }
        $fotoFinal = $nuevoNombre;
    }

    // 6. Armar array de datos para BD
    $data = [
        'nombre_comercial'    => $nombreComercial,
        'tipo_proveedor'      => $tipoProveedor,
        'eslogan'             => $eslogan,
        'descripcion'         => $descripcion,
        'anios_experiencia'   => $aniosExp,
        'idiomas'             => $idiomasCSV,
        'categorias'          => $categoriasCSV,
        'ciudad'              => $ciudad,
        'zona'                => $zona,
        'foto'                => $fotoFinal,
        'telefono_contacto'   => $telefonoContacto,
        'whatsapp'            => $whatsapp,
        'correo_alternativo'  => $correoAlt,
    ];

    // 7. Insertar o actualizar
    try {
        if ($perfilActual) {
            $ok = $modeloPerfil->actualizarPerfil($idUsuario, $data);
        } else {
            $ok = $modeloPerfil->crearPerfil($idUsuario, $data);
        }

        if (!$ok) {
            mostrarSweetAlert('error', 'Error al guardar', 'No se pudo guardar tu perfil profesional. Intenta nuevamente.', '/ProviServers/proveedor/configuracion');
            exit();
        }

        mostrarSweetAlert('success', 'Perfil actualizado', 'Tu perfil profesional se ha guardado correctamente.', '/ProviServers/proveedor/configuracion');
        exit();

    } catch (Exception $e) {
        error_log("Error en proveedorPerfilController -> " . $e->getMessage());
        mostrarSweetAlert('error', 'Error inesperado', 'Ocurrió un problema al guardar tu perfil. Intenta más tarde.', '/ProviServers/proveedor/configuracion');
        exit();
    }
}

function actualizarCredenciales()
{
    $idUsuario = (int)$_SESSION['user']['id'];

    $emailNuevo        = trim($_POST['email_nuevo'] ?? '');
    $emailConfirmacion = trim($_POST['email_confirmacion'] ?? '');
    $claveActual       = $_POST['clave_actual'] ?? '';
    $nuevaClave        = $_POST['nueva_clave'] ?? '';
    $confirmarClave    = $_POST['confirmar_clave'] ?? '';

    if (empty($emailNuevo) && empty($nuevaClave)) {
        mostrarSweetAlert('info', 'Sin cambios', 'No enviaste ningún cambio de correo ni de contraseña.', BASE_URL . '/proveedor/configuracion#cuenta');
        exit();
    }

    try {
        $db  = new Conexion();
        $pdo = $db->getConexion();
        
        $sql  = "SELECT email, clave FROM usuarios WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            mostrarSweetAlert('error', 'Usuario no encontrado', 'No fue posible localizar tu cuenta.', BASE_URL . '/login');
            exit();
        }

        if ((!empty($emailNuevo) || !empty($nuevaClave)) && empty($claveActual)) {
            mostrarSweetAlert('error', 'Falta la contraseña actual', 'Para cambiar correo o contraseña, debes ingresar tu contraseña actual.', BASE_URL . '/proveedor/configuracion#cuenta');
            exit();
        }

        if (!empty($claveActual) && !password_verify($claveActual, $usuario['clave'])) {
            mostrarSweetAlert('error', 'Contraseña incorrecta', 'La contraseña actual no coincide.', BASE_URL . '/proveedor/configuracion#cuenta');
            exit();
        }

        $camposUpdate = [];
        $params       = [':id' => $idUsuario];

        if (!empty($emailNuevo)) {
            if (!filter_var($emailNuevo, FILTER_VALIDATE_EMAIL)) {
                mostrarSweetAlert('error', 'Correo inválido', 'Ingresa un correo electrónico válido.', BASE_URL . '/proveedor/configuracion#cuenta');
                exit();
            }

            if ($emailNuevo !== $emailConfirmacion) {
                mostrarSweetAlert('error', 'Correos no coinciden', 'El correo nuevo y su confirmación deben coincidir.', BASE_URL . '/proveedor/configuracion#cuenta');
                exit();
            }

            $sqlCheck = "SELECT id FROM usuarios WHERE email = :email AND id <> :id LIMIT 1";
            $stmtCheck = $pdo->prepare($sqlCheck);
            $stmtCheck->bindParam(':email', $emailNuevo, PDO::PARAM_STR);
            $stmtCheck->bindParam(':id', $idUsuario, PDO::PARAM_INT);
            $stmtCheck->execute();

            if ($stmtCheck->fetch()) {
                mostrarSweetAlert('error', 'Correo en uso', 'El correo ingresado ya está registrado en otra cuenta.', BASE_URL . '/proveedor/configuracion#cuenta');
                exit();
            }

            $camposUpdate[]   = 'email = :email';
            $params[':email'] = $emailNuevo;
        }

        if (!empty($nuevaClave)) {
            if (strlen($nuevaClave) < 8) {
                mostrarSweetAlert('error', 'Contraseña muy corta', 'La nueva contraseña debe tener al menos 8 caracteres.', BASE_URL . '/proveedor/configuracion#cuenta');
                exit();
            }

            if ($nuevaClave !== $confirmarClave) {
                mostrarSweetAlert('error', 'Contraseñas no coinciden', 'La nueva contraseña y su confirmación deben coincidir.', BASE_URL . '/proveedor/configuracion#cuenta');
                exit();
            }

            $camposUpdate[]   = 'clave = :clave';
            $params[':clave'] = password_hash($nuevaClave, PASSWORD_DEFAULT);
        }

        if (empty($camposUpdate)) {
            mostrarSweetAlert('info', 'Sin cambios', 'No se detectaron cambios para actualizar.', BASE_URL . '/proveedor/configuracion#cuenta');
            exit();
        }

        $sqlUpdate = "UPDATE usuarios SET " . implode(', ', $camposUpdate) . " WHERE id = :id";
        $stmtUpd   = $pdo->prepare($sqlUpdate);
        $stmtUpd->execute($params);

        if (!empty($emailNuevo)) {
            $_SESSION['user']['email'] = $emailNuevo;
        }

        mostrarSweetAlert('success', 'Datos actualizados', 'Tu correo y/o contraseña se actualizaron correctamente.', BASE_URL . '/proveedor/configuracion#cuenta');
        exit();

    } catch (PDOException $e) {
        error_log('Error al actualizar credenciales proveedor: ' . $e->getMessage());
        mostrarSweetAlert('error', 'Error al guardar', 'Ocurrió un problema guardando tus cambios. Inténtalo de nuevo.', BASE_URL . '/proveedor/configuracion#cuenta');
        exit();
    }
}

function actualizarSeguridad()
{
    $idUsuario = (int)$_SESSION['user']['id'];

    $alertaSolicitudes = isset($_POST['alerta_solicitudes']) ? 1 : 0;
    $alertaResenas     = isset($_POST['alerta_reseñas']) ? 1 : 0;
    $alertaPagos       = isset($_POST['alerta_pagos']) ? 1 : 0;

    $canalNotificaciones = $_POST['canal_notificaciones'] ?? 'ambos';
    $tiempoSesion        = (int)($_POST['tiempo_sesion'] ?? 60);

    $canalesPermitidos = ['correo', 'plataforma', 'ambos'];
    if (!in_array($canalNotificaciones, $canalesPermitidos, true)) {
        $canalNotificaciones = 'ambos';
    }

    if ($tiempoSesion <= 0) {
        $tiempoSesion = 60;
    }

    try {
        $db  = new Conexion();
        $pdo = $db->getConexion();

        $sqlCheck = "SELECT id FROM proveedor_seguridad WHERE usuario_id = :usuario_id LIMIT 1";
        $stmtChk  = $pdo->prepare($sqlCheck);
        $stmtChk->bindParam(':usuario_id', $idUsuario, PDO::PARAM_INT);
        $stmtChk->execute();
        $existe = $stmtChk->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            $sqlUpd = "UPDATE proveedor_seguridad
                       SET alerta_solicitudes = :alerta_solicitudes,
                           alerta_reseñas     = :alerta_reseñas,
                           alerta_pagos       = :alerta_pagos,
                           canal_notificaciones = :canal_notificaciones,
                           tiempo_sesion      = :tiempo_sesion,
                           updated_at         = NOW()
                       WHERE usuario_id = :usuario_id";
        } else {
            $sqlUpd = "INSERT INTO proveedor_seguridad
                       (usuario_id, alerta_solicitudes, alerta_reseñas, alerta_pagos, canal_notificaciones, tiempo_sesion, created_at, updated_at)
                       VALUES
                       (:usuario_id, :alerta_solicitudes, :alerta_reseñas, :alerta_pagos, :canal_notificaciones, :tiempo_sesion, NOW(), NOW())";
        }

        $stmtUpd = $pdo->prepare($sqlUpd);
        $stmtUpd->bindParam(':usuario_id', $idUsuario, PDO::PARAM_INT);
        $stmtUpd->bindParam(':alerta_solicitudes', $alertaSolicitudes, PDO::PARAM_INT);
        $stmtUpd->bindParam(':alerta_reseñas', $alertaResenas, PDO::PARAM_INT);
        $stmtUpd->bindParam(':alerta_pagos', $alertaPagos, PDO::PARAM_INT);
        $stmtUpd->bindParam(':canal_notificaciones', $canalNotificaciones, PDO::PARAM_STR);
        $stmtUpd->bindParam(':tiempo_sesion', $tiempoSesion, PDO::PARAM_INT);
        $stmtUpd->execute();

        mostrarSweetAlert('success', 'Seguridad actualizada', 'Tus preferencias de seguridad y notificaciones se guardaron correctamente.', BASE_URL . '/proveedor/configuracion#cuenta');
        exit();

    } catch (PDOException $e) {
        error_log('Error al actualizar seguridad proveedor: ' . $e->getMessage());
        mostrarSweetAlert('error', 'Error al guardar seguridad', 'Ocurrió un problema guardando tus preferencias. Puedes intentarlo más tarde.', BASE_URL . '/proveedor/configuracion#cuenta');
        exit();
    }
}

function actualizarDisponibilidad()
{
    $usuarioId = (int)$_SESSION['user']['id'];

    $diasTrabajo = $_POST['dias_trabajo'] ?? [];
    $horaInicio  = trim($_POST['hora_inicio'] ?? '');
    $horaFin     = trim($_POST['hora_fin'] ?? '');

    $atiendeFinesSemana = isset($_POST['atiende_fines_semana']) ? 1 : 0;
    $atiendeFestivos    = isset($_POST['atiende_festivos']) ? 1 : 0;
    $atencionUrgencias  = isset($_POST['atencion_urgencias']) ? 1 : 0;
    $detalleUrgencias   = trim($_POST['detalle_urgencias'] ?? '');

    $tipoZona   = $_POST['tipo_zona'] ?? 'ciudad';
    $radioKm    = $_POST['radio_km'] ?? null;
    $zonasTexto = trim($_POST['zonas_texto'] ?? '');

    $errores = [];

    if (empty($diasTrabajo)) $errores[] = 'Selecciona al menos un día de trabajo.';
    
    if ($horaInicio === '' || $horaFin === '') {
        $errores[] = 'Debes indicar una hora de inicio y una hora de fin.';
    } elseif ($horaInicio >= $horaFin) {
        $errores[] = 'La hora de inicio debe ser menor que la hora de fin.';
    }

    $tiposZonaPermitidos = ['ciudad', 'radio', 'varias_ciudades', 'remoto'];
    if (!in_array($tipoZona, $tiposZonaPermitidos, true)) {
        $errores[] = 'Tipo de zona de servicio no válido.';
    }

    if ($tipoZona === 'radio') {
        if ($radioKm === '' || !is_numeric($radioKm) || (int)$radioKm <= 0) {
            $errores[] = 'Indica un radio en kilómetros válido mayor a cero.';
        }
    }

    if (!empty($errores)) {
        $mensajeErrores = implode('<br>', $errores);
        mostrarSweetAlert('error', 'Datos inválidos', $mensajeErrores, BASE_URL . '/proveedor/configuracion#disponibilidad');
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

    $modelo = new ProveedorDisponibilidad();
    $guardado = $modelo->guardarDesdeFormulario($usuarioId, $data);

    if ($guardado) {
        mostrarSweetAlert('success', 'Disponibilidad actualizada', 'Tu disponibilidad y zona de servicio se guardaron correctamente.', BASE_URL . '/proveedor/configuracion#disponibilidad');
    } else {
        mostrarSweetAlert('error', 'Error al guardar', 'Ocurrió un problema al guardar tu disponibilidad. Inténtalo nuevamente.', BASE_URL . '/proveedor/configuracion#disponibilidad');
    }
    exit();
}

function actualizarPoliticas()
{
    $usuarioId = (int)$_SESSION['user']['id'];

    $tipoCancelacion        = $_POST['tipo_cancelacion']        ?? 'moderada';
    $descripcionCancelacion = trim($_POST['descripcion_cancelacion'] ?? '');

    $permiteReprogramar     = isset($_POST['permite_reprogramar']) ? 1 : 0;
    $horasMinReprogramacion = trim($_POST['horas_min_reprogramacion'] ?? '');

    $cobraVisita            = isset($_POST['cobra_visita']) ? 1 : 0;
    $valorVisita            = trim($_POST['valor_visita'] ?? '');

    $ofreceGarantia         = isset($_POST['ofrece_garantia']) ? 1 : 0;
    $diasGarantia           = trim($_POST['dias_garantia'] ?? '');
    $detallesGarantia       = trim($_POST['detalles_garantia'] ?? '');

    $soloContactoPorPlataforma = isset($_POST['solo_contacto_por_plataforma']) ? 1 : 0;
    $tiempoRespuestaPromedio   = trim($_POST['tiempo_respuesta_promedio'] ?? '');
    $otrasCondiciones          = trim($_POST['otras_condiciones'] ?? '');

    $errores = [];

    $tiposCancelacionPermitidos = ['flexible', 'moderada', 'estricta'];
    if (!in_array($tipoCancelacion, $tiposCancelacionPermitidos, true)) {
        $errores[] = 'El tipo de política de cancelación no es válido.';
    }

    if ($permiteReprogramar && $horasMinReprogramacion !== '') {
        if (!is_numeric($horasMinReprogramacion) || (int)$horasMinReprogramacion < 0) {
            $errores[] = 'Las horas mínimas para reprogramar deben ser un número mayor o igual a 0.';
        }
    }

    if ($cobraVisita) {
        if ($valorVisita === '' || !is_numeric($valorVisita) || (float)$valorVisita <= 0) {
            $errores[] = 'Si cobras visita, indica un valor válido mayor a 0.';
        }
    }

    if ($ofreceGarantia) {
        if ($diasGarantia === '' || !is_numeric($diasGarantia) || (int)$diasGarantia <= 0) {
            $errores[] = 'Si ofreces garantía, indica un número de días mayor a 0.';
        }
    }

    if (strlen($tiempoRespuestaPromedio) > 50) {
        $errores[] = 'El tiempo de respuesta promedio es demasiado largo. Usa una frase corta (ej: "24 horas").';
    }

    if (!empty($errores)) {
        $mensaje = implode('<br>', $errores);
        mostrarSweetAlert('error', 'Datos inválidos', $mensaje, BASE_URL . '/proveedor/configuracion#politicas');
        exit();
    }

    $data = [
        'tipo_cancelacion'           => $tipoCancelacion,
        'descripcion_cancelacion'    => $descripcionCancelacion,
        'permite_reprogramar'        => $permiteReprogramar,
        'horas_min_reprogramacion'   => $horasMinReprogramacion,
        'cobra_visita'               => $cobraVisita,
        'valor_visita'               => $valorVisita,
        'ofrece_garantia'            => $ofreceGarantia,
        'dias_garantia'              => $diasGarantia,
        'detalles_garantia'          => $detallesGarantia,
        'solo_contacto_por_plataforma' => $soloContactoPorPlataforma,
        'tiempo_respuesta_promedio'  => $tiempoRespuestaPromedio,
        'otras_condiciones'          => $otrasCondiciones,
    ];

    $modelo = new ProveedorPoliticasServicio();
    $ok = $modelo->guardarDesdeFormulario($usuarioId, $data);

    if ($ok) {
        mostrarSweetAlert('success', 'Políticas actualizadas', 'Tus políticas de servicio se guardaron correctamente.', BASE_URL . '/proveedor/configuracion#politicas');
    } else {
        mostrarSweetAlert('error', 'Error al guardar', 'Ocurrió un problema al guardar tus políticas. Inténtalo nuevamente.', BASE_URL . '/proveedor/configuracion#politicas');
    }
    exit();
}

function cerrarSesiones()
{
    session_unset();
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    mostrarSweetAlert('success', 'Sesión cerrada', 'Tu sesión se ha cerrado correctamente.', BASE_URL . '/login');
    exit();
}