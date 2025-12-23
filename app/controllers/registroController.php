<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/registro.php'; // Cambiado a tu nuevo modelo

// Capturamos en una variable el método o solicitud hecha al servidor
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // No necesitamos verificar 'accion' ya que el único POST en esta ruta es el registro
        registrarUsuario();
        break;

    case 'GET':
    default:
        http_response_code(405);
        echo "Método no permitido. Solo se acepta POST para esta ruta.";
        break;
}

// ----------------------------------------------------
// FUNCIONES DEL REGISTRO
// ----------------------------------------------------

function registrarUsuario()
{
    // 1. CAPTURA Y VALIDACIÓN BÁSICA DE DATOS
    // --------------------------------------------
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $documento = $_POST['documento'] ?? '';
    $email = $_POST['email'] ?? '';
    // Usamos 'clave' para que coincida con el name del formulario y el modelo
    $clave = $_POST['clave'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $ubicacion = $_POST['ubicacion'] ?? '';
    $rol = $_POST['rol'] ?? '';

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

    // $nombreCompleto = trim($nombres . ' ' . $apellidos);


    // 2. LÓGICA DE CARGA DE ARCHIVOS DE PERFIL
    // -----------------------------------------

    // Código en tu RegistroController

    $ruta_foto_perfil = 'default_user.png'; // Valor por defecto

    if (!empty($_FILES['foto']['tmp_name'])) {
        $file = $_FILES['foto'];

        // Usamos la función auxiliar, cambiando el prefijo de 'foto_perfil' a 'usuario'
        // Esto hará que el nombre generado sea 'usuario_' + ID único + '.ext'
        $ruta_foto_perfil = subirArchivo($file, '/public/uploads/usuarios/', ['png', 'jpg', 'jpeg'], 2 * 1024 * 1024, 'usuario'); // ✅ CORRECCIÓN AQUÍ

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

            // Si el campo no se envió, lo omitimos (manejo de 'doc-certificado' opcional)
            if (empty($_FILES[$campo_name]['tmp_name'])) continue;

            // Subir el archivo
            $ruta_doc = subirArchivo($_FILES[$campo_name], $directorio_docs, ['png', 'jpg', 'jpeg', 'pdf'], $max_size_docs, $campo_name);

            if ($ruta_doc === false) {
                mostrarSweetAlert('error', 'Error de Documento', 'Hubo un problema al cargar el documento: ' . $campo_name);
                exit();
            }
            $archivos_proveedor[$campo_name] = $ruta_doc;
        }

        // Si es proveedor, validamos que al menos los documentos básicos obligatorios se hayan subido
        if (empty($archivos_proveedor['doc-cedula']) || empty($archivos_proveedor['doc-foto']) || empty($archivos_proveedor['doc-antecedentes'])) {
            // Nota: Esta validación es clave para la seguridad
            mostrarSweetAlert('error', 'Documentos obligatorios', 'Para registrarte como Proveedor, la Cédula, Selfie y Antecedentes son obligatorios.');
            exit();
        }
    }


    // 4. PREPARAR Y ENVIAR DATOS AL MODELO (Triple Inserción)
    // --------------------------------------------------------

    $objRegistro = new Registro();

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
        'id_membresia_defecto' => 4
    ];

    $resultado = $objRegistro->registrar($data);

    // 5. RESPUESTA Y REDIRECCIÓN
    // --------------------------
    if ($resultado === true) {
        if ($rol === 'proveedor') {
            // Mensaje informativo para el proveedor (Pendiente de aprobación)
            mostrarSweetAlert(
                'success',
                'Registro Recibido',
                'Tus documentos han sido cargados exitosamente. Nuestro equipo los validará en un plazo de 24-48h. Te notificaremos por correo.',
                BASE_URL . '/login'
            );
        } else {
            // Mensaje directo para el cliente (Acceso inmediato)
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

// ----------------------------------------------------
// 4. FUNCIÓN AUXILIAR DE SUBIDA DE ARCHIVOS (Basada en tu lógica)
// ----------------------------------------------------

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
    // Tu lógica de validación de archivos:
    if ($file['error'] !== UPLOAD_ERR_OK) return false;

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $permitidas)) return false;

    if ($file['size'] > $max_size) return false;

    // Tu lógica de movimiento:
    $nombre_archivo = $prefijo . '_' . uniqid() . '.' . $extension;
    $destino_completo = BASE_PATH . $destino_dir . $nombre_archivo;

    if (move_uploaded_file($file['tmp_name'], $destino_completo)) {
        return $nombre_archivo;
    } else {
        return false;
    }
}
