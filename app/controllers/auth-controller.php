﻿﻿<?php

    require_once __DIR__ . '/../helpers/alert-helper.php';
    require_once __DIR__ . '/../models/Auth.php';
    require_once __DIR__ . '/../models/membresia.php';

    // ===================================================================
    // ROUTER INTERNO — Dispatch por método HTTP y acción
    // ===================================================================

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {

        case 'POST':
            $accion = $_POST['accion'] ?? '';

            if ($accion === 'iniciar_sesion') {
                iniciarSesion();
            } elseif ($accion === 'registrar') {
                registrarUsuario();
            } elseif ($accion === 'recuperar_password') {
                recuperarPassword();
            } else {
                http_response_code(400);
                mostrarSweetAlert('error', 'Acción no válida', 'La acción POST solicitada no existe.');
                exit();
            }
            break;

        case 'GET':
            $accion = $_GET['accion'] ?? '';

            if ($accion === 'cerrar_sesion') {
                cerrarSesion();
            }
            // Acciones GET restantes (ej. cargarRegistro) son llamadas
            // explícitamente desde index.php tras el require_once.
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
    // INICIAR SESIÓN
    // -------------------------------------------------------------------
    function iniciarSesion()
    {
        $correo = trim($_POST['email'] ?? '');
        $clave  = $_POST['clave']     ?? '';

        if (empty($correo) || empty($clave)) {
            mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa correo y contraseña.');
            exit();
        }

        $modelo    = new Auth();
        $resultado = $modelo->autenticar($correo, $clave);

        if (isset($resultado['error'])) {
            mostrarSweetAlert('error', 'Error de autenticación', $resultado['error']);
            exit();
        }

        switch ($resultado['estado']) {
            case 'bloqueado':
                mostrarSweetAlert('error', 'Acceso denegado', 'Tu cuenta está bloqueada. Contacta al soporte.');
                exit();
            case 'pendiente':
                mostrarSweetAlert('info', 'En revisión', 'Tu perfil de proveedor está siendo evaluado. Te notificaremos por correo.');
                exit();
            case 'inactivo':
                mostrarSweetAlert('warning', 'Cuenta inactiva', 'Tu cuenta está desactivada. Contacta al soporte.');
                exit();
            case 'activo':
                break;
            default:
                mostrarSweetAlert('error', 'Estado desconocido', 'Problema con el estado de tu cuenta. Contacta al soporte.');
                exit();
        }

        if ($resultado['rol'] === 'proveedor') {
            $membresia = new Membresia();
            $membresia->activarSiEsNecesario($resultado['id']);
        }

        if (session_status() === PHP_SESSION_NONE) {
            if (session_status() === PHP_SESSION_NONE) session_start();
        }
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'    => $resultado['id'],
            'rol'   => $resultado['rol'],
            'email' => $resultado['email'],
        ];

        $destinos = [
            'admin'     => BASE_URL . '/admin/dashboard',
            'proveedor' => BASE_URL . '/proveedor/dashboard',
            'cliente'   => BASE_URL . '/cliente/dashboard',
        ];

        $redirect = $destinos[$resultado['rol']] ?? BASE_URL . '/login';

        mostrarSweetAlert('success', 'Ingreso exitoso', 'Bienvenido/a de vuelta.', $redirect);
        exit();
    }

    // -------------------------------------------------------------------
    // REGISTRAR USUARIO (Cliente o Proveedor)
    // -------------------------------------------------------------------
    function registrarUsuario()
    {
        $nombres   = trim($_POST['nombres']   ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $email     = trim($_POST['email']     ?? '');
        $clave     = $_POST['clave']          ?? '';
        $confirmar = $_POST['confirmar']      ?? '';
        $telefono  = trim($_POST['telefono']  ?? '');
        $ubicacion = trim($_POST['ubicacion'] ?? '');
        $rol       = trim($_POST['rol']       ?? '');

        $habilidades = [];
        if (!empty($_POST['lista_categorias'])) {
            $decoded     = json_decode($_POST['lista_categorias'], true);
            $habilidades = is_array($decoded) ? $decoded : [];
        }

        if (
            empty($nombres) || empty($apellidos) || empty($documento) ||
            empty($email)   || empty($clave)     || empty($confirmar) ||
            empty($telefono) || empty($ubicacion) || empty($rol)
        ) {
            mostrarSweetAlert('error', 'Campos vacíos', 'Completa todos los campos del formulario.');
            exit();
        }

        if ($clave !== $confirmar) {
            mostrarSweetAlert('error', 'Contraseñas no coinciden', 'Las contraseñas ingresadas no son iguales.');
            exit();
        }

        if ($rol === 'proveedor' && count($habilidades) < 1) {
            mostrarSweetAlert('error', 'Habilidades requeridas', 'Debes seleccionar al menos una habilidad en el paso 4.');
            exit();
        }

        // — Foto de perfil —
        $foto = 'default_user.png';
        if (!empty($_FILES['foto']['tmp_name'])) {
            $foto = subirArchivo(
                $_FILES['foto'],
                '/public/uploads/usuarios/',
                ['png', 'jpg', 'jpeg'],
                2 * 1024 * 1024,
                'usuario'
            );
            if ($foto === false) {
                mostrarSweetAlert('error', 'Error de foto', 'Formato o tamaño inválido. Máx. 2MB, JPG o PNG.');
                exit();
            }
        }

        // — Documentos del proveedor —
        $archivosProveedor = [];
        if ($rol === 'proveedor') {
            $camposDoc = ['doc-cedula', 'doc-foto', 'doc-antecedentes', 'doc-certificado'];
            $maxDoc    = 5 * 1024 * 1024;

            foreach ($camposDoc as $campo) {
                if (empty($_FILES[$campo]['tmp_name'])) {
                    continue;
                }
                $ruta = subirArchivo(
                    $_FILES[$campo],
                    '/public/uploads/documentos/',
                    ['png', 'jpg', 'jpeg', 'pdf'],
                    $maxDoc,
                    $campo
                );
                if ($ruta === false) {
                    mostrarSweetAlert('error', 'Error de documento', "Problema al cargar: {$campo}. Máx. 5MB, PDF o imagen.");
                    exit();
                }
                $archivosProveedor[$campo] = $ruta;
            }

            if (
                empty($archivosProveedor['doc-cedula']) ||
                empty($archivosProveedor['doc-foto'])   ||
                empty($archivosProveedor['doc-antecedentes'])
            ) {
                mostrarSweetAlert('error', 'Documentos obligatorios', 'La cédula, selfie y antecedentes son obligatorios para proveedores.');
                exit();
            }
        }

        $modelo    = new Auth();
        $resultado = $modelo->registrarUsuario([
            'nombres'              => $nombres,
            'apellidos'            => $apellidos,
            'documento'            => $documento,
            'email'                => $email,
            'clave'                => $clave,
            'telefono'             => $telefono,
            'ubicacion'            => $ubicacion,
            'rol'                  => $rol,
            'foto'                 => $foto,
            'documentos'           => $archivosProveedor,
            'habilidades'          => $habilidades,
            'id_membresia_defecto' => 4,
        ]);

        if ($resultado === true) {
            $msg = ($rol === 'proveedor')
                ? 'Tus documentos fueron recibidos. El equipo los validará en 24-48h y te avisará por correo.'
                : 'Tu cuenta fue creada con éxito. Ya puedes iniciar sesión.';
            mostrarSweetAlert('success', '¡Registro exitoso!', $msg, BASE_URL . '/login');
        } elseif ($resultado === 'duplicado') {
            mostrarSweetAlert('error', 'Ya registrado', 'El correo o documento ya están registrados.', BASE_URL . '/registro');
        } else {
            mostrarSweetAlert('error', 'Error al registrar', 'Ocurrió un error inesperado. Intenta nuevamente.', BASE_URL . '/registro');
        }
        exit();
    }

    // -------------------------------------------------------------------
    // RECUPERAR CONTRASEÑA
    // -------------------------------------------------------------------
    function recuperarPassword()
    {
        $email = trim($_POST['correo'] ?? '');

        if (empty($email)) {
            mostrarSweetAlert('error', 'Campo vacío', 'Ingresa tu correo electrónico.');
            exit();
        }

        $modelo    = new Auth();
        $resultado = $modelo->recuperarClave($email);

        if ($resultado === true) {
            mostrarSweetAlert('success', 'Nueva clave generada', 'Se envió una contraseña temporal a tu correo.', BASE_URL . '/login');
        } else {
            mostrarSweetAlert('error', 'Usuario no encontrado', 'Verifica el correo e intenta nuevamente.');
        }
        exit();
    }

    // -------------------------------------------------------------------
    // CERRAR SESIÓN
    // -------------------------------------------------------------------
    function cerrarSesion()
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (session_status() === PHP_SESSION_NONE) session_start();
        }

        $_SESSION = [];
        session_unset();
        session_destroy();

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        header('Location: ' . BASE_URL . '/login');
        exit();
    }

    // -------------------------------------------------------------------
    // CARGAR VISTA DE REGISTRO (precarga categorías antes del HTML)
    // -------------------------------------------------------------------
    function cargarRegistro()
    {
        $modelo        = new Auth();
        $categorias_bd = $modelo->obtenerTodasCategorias();
        require BASE_PATH . '/app/views/auth/registro.php';
        exit();
    }

// ===================================================================
// FUNCIÓN AUXILIAR — SUBIDA DE ARCHIVOS
// ===================================================================

    /**
     * Sube un archivo al servidor y devuelve su nombre final o false si falla.
     *
     * @param  array  $file         Entrada de $_FILES.
     * @param  string $dirRelativo  Ruta relativa al BASE_PATH (ej. '/public/uploads/usuarios/').
     * @param  array  $extensiones  Extensiones permitidas (ej. ['jpg', 'png']).
     * @param  int    $maxBytes     Tamaño máximo en bytes.
     * @param  string $prefijo      Prefijo del nombre de archivo generado.
     * @return string|false
     */
    function subirArchivo($file, $dirRelativo, $extensiones, $maxBytes, $prefijo = 'file')
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $extensiones, true)) {
            return false;
        }

        if ($file['size'] > $maxBytes) {
            return false;
        }

        $nombre  = $prefijo . '_' . uniqid() . '.' . $ext;
        $destino = BASE_PATH . $dirRelativo . $nombre;

        return move_uploaded_file($file['tmp_name'], $destino) ? $nombre : false;
    }
