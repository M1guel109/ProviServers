<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/admin.php';

// Capturamos en una variale el metodo o solicitud hecha al servidor
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'actualizar') {
            actualizarUsuario();
        } else {
            registrarUsuario();
        }

        break;
    case 'GET':
        $accion = $_GET['accion'] ?? '';

        if ($accion === 'eliminar') {
            eliminarUsuario($_GET['id']);
        }

        if (isset($_GET['id'])) {
            mostrarUsuarioId($_GET['id']);
        } else {
            mostrarUsuarios();
        }

        break;
    // case 'PUT':
    //     actualizarUsuario();
    //     break;
    // case 'DELETE':
    //     eliminarUsuario();
    //     break;
    default:
        http_response_code(405);
        echo "Metodo no permitido";
        break;
}

// Funciones del CRUD

function registrarUsuario()
{
    // Capturamos en variables los datos desde el formulario a traves del metodo post y los name de los campos
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $documento = $_POST['documento'] ?? '';
    $email = $_POST['email'] ?? '';
    $clave = $_POST['clave'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $ubicacion = $_POST['ubicacion'] ?? '';
    $rol = $_POST['rol'] ?? '';


    // 🔑 LÓGICA DE CLAVE TEMPORAL
    // Si el campo clave está vacío, usa el documento como clave temporal.
    $clave_final = !empty($clave) ? $clave : $documento;

    // Validamos lo campos que son obligatorios
    if (empty($nombres) || empty($apellidos) || empty($documento) || empty($email) || empty($clave_final) || empty($telefono) || empty($ubicacion) || empty($rol)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos');
        exit();
    }

    // Capturamos el id del usuario que inicia sesion para guardarlo solo si es necesario
    // session_start();
    // $id_admin = $_SESSION['user']['id'];

    // Logica para cargar imagenes
    $ruta_img = null;

    // Validamos si se envio o no la foto desde el form
    // ******Si el usuario no registro una foto, dejar una por defecto

    if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto'];

        // Obtenemos la extension del archivo
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Definimos las extensiones permitidas
        $permitidas = ['png', 'jpg', 'jpeg'];

        // Validamos que la extension de la imagen cargada este dentro de las permitidas
        if (!in_array($extension, $permitidas)) {
            mostrarSweetAlert('error', 'Extension no permitida', 'Por favor, cargue un archivo con una extensión permitida.');
            exit();
        }

        // Validamos el tamaño o peso de la imagen MAX 2MB
        if ($file['size'] > 2 * 1024 * 1024) {
            mostrarSweetAlert('error', 'Error al cargar la foto ', 'El peso de la foto supera el limite de 2MB');
            exit();
        }

        // Definimos el nombre del archivo y le concatenamos la extension
        $ruta_img = uniqid('usuario_') . '.' . $extension;

        // Definimos el destino donde moveremos el archivo
        $destino = BASE_PATH . "/public/uploads/usuarios/" . $ruta_img;

        // Movemos el archivo al destino
        move_uploaded_file($file['tmp_name'], $destino);
    } else {
        // Agregar la logica de la imagen por defecto
        $ruta_img = "default_user.png";
    }

    // POO-instanciamos la clase
    $objUsuario = new Usuario();
    $data = [
        'nombres' => $nombres,
        'apellidos' => $apellidos,
        'documento' => $documento,
        'email' => $email,
        'clave' => $clave_final,
        'telefono' => $telefono,
        'ubicacion' => $ubicacion,
        'rol' => $rol,
        'foto' => $ruta_img
        // 'id_admin' => $id_admin,
    ];

    // Enviamos la data al metodo "registrar()" del la clase instanciada anteriormente "Usuario()" y esperamos una respuesta booleana del modelo
    $resultado = $objUsuario->registrar($data);

    // Si la respuesta del modelo es verdadera confoirmamos el registro y redireccionamos ,si es falsa notificamosy redireccionamos
    if ($resultado === true) {
        mostrarSweetAlert('success', 'Registro de usuario exitoso', 'Se ha creado un nuevo usuario', '/ProviServers/admin/registrar-usuario');
    } else {
        mostrarSweetAlert('error', 'Error al registrar', 'No se pudo registrar el usuario. Intenta nuevamente o verifica si el documento/email ya existe.');
    }

    exit();
}

function mostrarUsuarios()
{
    // ejemplo
    // session_start();

    $resultado = new Usuario();
    $usuarios = $resultado->mostrar();

    return $usuarios;
}

function mostrarUsuarioId($id)
{
    $objUsuario = new Usuario();
    $usuario = $objUsuario->mostrarId($id);

    return $usuario;
}

function actualizarUsuario()
{
    // Capturamos en variables los datos desde el formulario a traves del metodo post y los name de los campos
    $id = $_POST['id'] ?? '';
    $nombres = $_POST['nombres'] ?? '';      
    $apellidos = $_POST['apellidos'] ?? '';  
    $documento = $_POST['documento'] ?? '';  
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $ubicacion = $_POST['ubicacion'] ?? '';
    $rol = $_POST['rol'] ?? '';

    // Datos de la foto (campo oculto y archivo subido)
    $foto_perfil_actual = $_POST['foto_perfil_actual'] ?? ''; // La foto que ya estaba en la DB
    $archivo_nuevo = $_FILES['foto_perfil'] ?? null;

    // Validamos lo campos que son obligatorios
    if (empty($id) || empty($nombres) || empty($apellidos) || empty($documento) || empty($email) || empty($telefono) || empty($ubicacion) || empty($rol)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos');
        exit();
    }


    // 3. LÓGICA DE GESTIÓN DE LA FOTO 📸
    // ----------------------------------------------------
    $foto_para_db = $foto_perfil_actual; // Por defecto, usamos el nombre de la foto actual

    // Ruta donde se guardan las imágenes (IMPORTANTE: BASE_PATH debe estar definido)
    $ruta_destino = BASE_PATH . '/public/uploads/usuarios/'; 

    // Verificar si se subió un nuevo archivo sin errores
    if ($archivo_nuevo && $archivo_nuevo['error'] === UPLOAD_ERR_OK) {
        
        // Generar un nombre único para el nuevo archivo
        $extension = pathinfo($archivo_nuevo['name'], PATHINFO_EXTENSION);
        $nombre_archivo_nuevo = uniqid('user_') . '.' . $extension;
        
        // Intentar mover el archivo subido
        if (move_uploaded_file($archivo_nuevo['tmp_name'], $ruta_destino . $nombre_archivo_nuevo)) {
            
            // Éxito: asignamos la nueva ruta y eliminamos la antigua
            $foto_para_db = $nombre_archivo_nuevo; 
            
            // Eliminar la foto antigua del servidor (si existe y no es la por defecto/vacía)
            if (!empty($foto_perfil_actual) && file_exists($ruta_destino . $foto_perfil_actual)) {
                 unlink($ruta_destino . $foto_perfil_actual);
            }
            
        } else {
            // Error al mover el archivo
            mostrarSweetAlert('error', 'Error de Subida', 'Hubo un problema al guardar la nueva foto.');
            exit();
        }
    } 
    // Si no hay archivo nuevo, $foto_para_db mantiene el valor de $foto_perfil_actual.

    $objUsuario = new Usuario();
    $data = [
        'id' => $id,
        'nombres'     => $nombres,       
        'apellidos'   => $apellidos,     
        'documento'   => $documento,
        'email' => $email,
        'telefono' => $telefono,
        'ubicacion' => $ubicacion,
        'rol' => $rol,
        'foto_perfil' => $foto_para_db
        // 'id_admin' => $id_admin,
    ];

    // Enviamos la data al metodo "registrar()" del la clase instanciada anteriormente "Usuario()" y esperamos una respuesta booleana del modelo
    $resultado = $objUsuario->actualizar($data);

    // Si la respuesta del modelo es verdadera confoirmamos el registro y redireccionamos ,si es falsa notificamosy redireccionamos
    if ($resultado === true) {
        mostrarSweetAlert('success', 'Usuario actualizado con exito', 'Los datos del usuario se han actualizado correctamente.', '/ProviServers/admin/consultar-usuarios');
    } else {
        mostrarSweetAlert('error', 'Error al actualizar', 'No se pudo actualizar el usuario. Intenta nuevamente');
    }

    exit();
}

function eliminarUsuario($id)
{
    $objUsuario = new Usuario();
    $respuesta = $objUsuario->eliminar($id);

    if ($respuesta === true) {
        mostrarSweetAlert('success', 'Eliminacion exitosa', 'Se ha eliminado el usuario', '/ProviServers/admin/consultar-usuarios');
    } else {
        mostrarSweetAlert('error', 'Error al eliminar', 'No se pudo registrar el usuario. Intenta nuevamente');
    }
}
