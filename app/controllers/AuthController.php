<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/Auth.php';
require_once __DIR__ . '/../models/Membresia.php';


// Capturamos en una variale el metodo o solicitud hecha al servidor
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'iniciar_sesion') {
            iniciarSesion();
        } 
        elseif ($accion === 'registrar') {
            registrarUsuario();
        } 
        elseif ($accion === 'recuperar_password') {
            recuperarPassword();
        } 
        else {
            http_response_code(400);
            echo "Acción no válida";
        }
        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';

        if ($accion === 'cerrar_sesion') {
            cerrarSesion();
        } 
        else {
            http_response_code(400);
            echo "Acción no válida";
        }
        break;

    default:
        http_response_code(405);
        echo "Metodo no permitido";
        break;
}

// ======================================================================
// FUNCIONES DEL CONTROLADOR
// ======================================================================

function iniciarSesion()
{
    // Capturamos en variables los valores enviados a traves de los name del formulario
    $correo = $_POST['email'] ?? '';
    $clave = $_POST['clave'] ?? '';

    // Validamos que los campos/variables no esten vacios
    if (empty($correo) || empty($clave)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos');
        exit();
    }

    // POO - Instaciamos las clases del modelo, para acceder a un method (funcion) en especifico
    $login = new Auth();
    $resultado = $login->autenticar($correo, $clave);

    // verificar si el modelo devolvio el error 
    if (isset($resultado['error'])) {
        mostrarSweetAlert('error', 'Error de autenticacion', $resultado['error']);
        exit();
    }

    /**
     * VERIFICACIÓN DE ESTADOS
     * Validamos el estado que viene del modelo antes de iniciar sesión
     */
    switch ($resultado['estado']) {
        case 'bloqueado':
            mostrarSweetAlert('error', 'Acceso Denegado', 'Tu cuenta ha sido bloqueada. Contacta al soporte.');
            exit();

        case 'pendiente':
            mostrarSweetAlert('info', 'En Revisión', 'Tu perfil de proveedor está siendo evaluado por un administrador. Te enviaremos un correo pronto.');
            exit();

        case 'inactivo':
            mostrarSweetAlert('warning', 'Cuenta Inactiva', 'Tu cuenta está desactivada.');
            exit();

        case 'activo':
            // Si está activo, permitimos que continúe el flujo de sesión
            break;

        default:
            mostrarSweetAlert('error', 'Estado desconocido', 'Hubo un problema con tu cuenta.');
            exit();
    }

    if ($resultado['rol'] === 'proveedor') {
        $activador = new Membresia();
        // Este método solo actuará si la membresía está inactiva y sin fechas.
        $activador->activarSiEsNecesario($resultado['id']);
    }

    // SI PASA ESTA LINEA, EL USUARIO ES VALIDO
    session_start();
    $_SESSION['user'] = [
        'id' => $resultado['id'],
        'rol' => $resultado['rol'],
        'email' => $resultado['email']
    ];

    // Redireccion segun rol 
    $redirectUrl = '/ProviServers/login';
    $mensaje = 'Rol inexistente. Redirigiendo al inicio de sesión...';

    switch ($resultado['rol']) {
        case 'admin':
            $redirectUrl = '/ProviServers/admin/dashboard';
            $mensaje = 'Bienvenido Administrador';
            break;
        case 'proveedor':
            $redirectUrl = '/ProviServers/proveedor/dashboard';
            $mensaje = 'Bienvenido Proveedor';
            break;
        case 'cliente':
            $redirectUrl = '/ProviServers/cliente/dashboard';
            $mensaje = 'Bienvenido Cliente';
            break;
    }

    mostrarSweetAlert('success', 'Ingreso Exitoso', $mensaje, $redirectUrl);
    exit();
}

function registrarUsuario()
{
    // 1. CAPTURA Y VALIDACIÓN BÁSICA DE DATOS
    // --------------------------------------------
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $documento = $_POST['documento'] ?? '';
    $email = $_POST['email'] ?? '';
    $clave = $_POST['clave'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $ubicacion = $_POST['ubicacion'] ?? '';
    $rol = $_POST['rol'] ?? '';

    // CAPTURAR HABILIDADES
    $habilidades = isset($_POST['lista_categorias']) ? json_decode($_POST['lista_categorias'], true) : [];

    // Validamos campos obligatorios
    if (empty($documento) || empty($email) || empty($clave) || empty($confirmar) || empty($nombres) || empty($apellidos) || empty($telefono) || empty($ubicacion) || empty($rol)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos del formulario');
        exit();
    }

    // Validar que las claves coincidan
    if ($clave !== $confirmar) {
        mostrarSweetAlert('error', 'Error de contraseña', 'Las contraseñas no coinciden. Intenta de nuevo.');
        exit();
    }

    // 2. LÓGICA DE CARGA DE ARCHIVOS DE PERFIL
    // -----------------------------------------
    $ruta_foto_perfil = 'default_user.png'; // Valor por defecto

    if (!empty($_FILES['foto']['tmp_name'])) {
        $file = $_FILES['foto'];

        // Usamos la función auxiliar, cambiando el prefijo de 'foto_perfil' a 'usuario'
        $ruta_foto_perfil = subirArchivo($file, '/public/uploads/usuarios/', ['png', 'jpg', 'jpeg'], 2 * 1024 * 1024, 'usuario'); 

        if ($ruta_foto_perfil === false) {
            mostrarSweetAlert('error', 'Error de Foto', 'Hubo un problema al cargar la foto de perfil o el formato es incorrecto (Max 2MB).');
            exit();
        }
    }

    // 3. LÓGICA DE CARGA DE DOCUMENTOS (Solo para Proveedores)
    // --------------------------------------------------------
    $archivos_proveedor = [];
    if ($rol === 'proveedor') {
        $documentos_permitidos = ['doc-cedula', 'doc-foto', 'doc-antecedentes', 'doc-certificado'];
        $directorio_docs = '/public/uploads/proveedores/documentos_proveedores/';
        $max_size_docs = 5 * 1024 * 1024; // 5MB

        foreach ($documentos_permitidos as $campo_name) {
            // Si el campo no se envió, lo omitimos
            if (empty($_FILES[$campo_name]['tmp_name'])) continue;

            // Subir el archivo
            $ruta_doc = subirArchivo($_FILES[$campo_name], $directorio_docs, ['png', 'jpg', 'jpeg', 'pdf'], $max_size_docs, $campo_name);

            if ($ruta_doc === false) {
                mostrarSweetAlert('error', 'Error de Documento', 'Hubo un problema al cargar el documento: ' . $campo_name);
                exit();
            }
            $archivos_proveedor[$campo_name] = $ruta_doc;
        }

        // Validamos que al menos los documentos básicos obligatorios se hayan subido
        if (empty($archivos_proveedor['doc-cedula']) || empty($archivos_proveedor['doc-foto']) || empty($archivos_proveedor['doc-antecedentes'])) {
            mostrarSweetAlert('error', 'Documentos obligatorios', 'Para registrarte como Proveedor, la Cédula, Selfie y Antecedentes son obligatorios.');
            exit();
        }
    }

    // 4. PREPARAR Y ENVIAR DATOS AL MODELO
    // --------------------------------------------------------
    $objRegistro = new Auth();

    $data = [
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'documento' => $documento,
        'email' => $email,
        'clave' => $clave,
        'telefono' => $telefono,
        'ubicacion' => $ubicacion,
        'rol' => $rol,
        'foto' => $ruta_foto_perfil,
        'documentos' => $archivos_proveedor,
        'habilidades' => $habilidades,
        'id_membresia_defecto' => 4
    ];

    $resultado = $objRegistro->registrarUsuario($data);

    // 5. RESPUESTA Y REDIRECCIÓN
    // --------------------------
    if ($resultado === true) {
        if ($rol === 'proveedor') {
            mostrarSweetAlert(
                'success',
                'Registro Recibido',
                'Tus documentos han sido cargados exitosamente. Nuestro equipo los validará en un plazo de 24-48h. Te notificaremos por correo.',
                BASE_URL . '/login'
            );
        } else {
            mostrarSweetAlert(
                'success',
                '¡Bienvenido!',
                'Tu cuenta ha sido creada con éxito. Ya puedes iniciar sesión y explorar.',
                BASE_URL . '/login'
            );
        }
    } elseif ($resultado === 'duplicado') {
        mostrarSweetAlert('error', 'Error de Registro', 'El correo o documento ya están registrados.', BASE_URL . '/registro');
    } else {
        mostrarSweetAlert('error', 'Error al Registrar', 'Ocurrió un error inesperado. Intenta nuevamente.', BASE_URL . '/registro');
    }

    exit();
}

function recuperarPassword()
{
    $email = $_POST['correo'] ?? '';

    // Validamos lo campos que son obligatorios
    if (empty($email)) {
        mostrarSweetAlert('error', 'Campos vacío', 'Por favor completa el campo.');
        exit();
    }

    $objModelo = new Auth();
    $resultado = $objModelo->recuperarClave($email);

    // Si la respuesta del modelo es verdadera confirmamos, si es falsa notificamos
    if ($resultado === true) {
        mostrarSweetAlert('success', 'Nueva clave generada', 'Se ha enviado una nueva contraseña a tu correo electronico.', '/ProviServers/login');
    } else {
        mostrarSweetAlert('error', 'Usuario no encontrado', 'Verifique su correo electronico e Intente nuevamente.');
    }
    exit();
}

function cerrarSesion()
{
    // Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vaciar variables de sesión
    $_SESSION = [];

    // Destruir sesión
    session_unset();
    session_destroy();

    // Eliminar cookie de sesión (opcional pero recomendado)
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Redirigir al login
    header("Location: " . BASE_URL . "/login");
    exit();
}

// ======================================================================
// FUNCIONES AUXILIARES
// ======================================================================

/**
 * Función genérica para subir un archivo de forma segura.
 * @param array $file Array $_FILES del archivo.
 * @param string $destino_dir Directorio donde se guardará (ej: '/public/uploads/').
 * @param array $permitidas Extensiones permitidas.
 * @param int $max_size Tamaño máximo en bytes.
 * @param string $prefijo Prefijo para el nombre único del archivo.
 * @return string|false Nombre del archivo guardado o false si hay error.
 */
function subirArchivo($file, $destino_dir, $permitidas, $max_size, $prefijo = 'file')
{
    if ($file['error'] !== UPLOAD_ERR_OK) return false;

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $permitidas)) return false;

    if ($file['size'] > $max_size) return false;

    $nombre_archivo = $prefijo . '_' . uniqid() . '.' . $extension;
    $destino_completo = BASE_PATH . $destino_dir . $nombre_archivo;

    if (move_uploaded_file($file['tmp_name'], $destino_completo)) {
        return $nombre_archivo;
    } else {
        return false;
    }
}