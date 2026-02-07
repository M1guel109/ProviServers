<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/notificaciones_helper.php';
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
    // 1. Captura de Datos Básicos
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $documento = $_POST['documento'] ?? '';
    $email = $_POST['email'] ?? '';
    $clave = $_POST['clave'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $ubicacion = $_POST['ubicacion'] ?? '';
    $rol = $_POST['rol'] ?? '';

    // Lógica de clave temporal (si no hay clave, usa el documento)
    $clave_final = !empty($clave) ? $clave : $documento;

    // Validación básica
    if (empty($nombres) || empty($apellidos) || empty($documento) || empty($email) || empty($clave_final) || empty($telefono) || empty($ubicacion) || empty($rol)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos obligatorios');
        exit();
    }

    // 2. Foto de Perfil
    $ruta_img = "default_user.png";
    if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidas_img = ['png', 'jpg', 'jpeg'];

        if (!in_array($extension, $permitidas_img)) {
            mostrarSweetAlert('error', 'Formato inválido', 'La foto debe ser PNG, JPG o JPEG.');
            exit();
        }

        $ruta_img = uniqid('perfil_') . '.' . $extension;
        $destino_img = BASE_PATH . "/public/uploads/usuarios/" . $ruta_img;

        if (!is_dir(dirname($destino_img))) mkdir(dirname($destino_img), 0755, true);
        move_uploaded_file($file['tmp_name'], $destino_img);
    }

    // ---------------------------------------------------------
    // 3. LÓGICA ESPECÍFICA DE PROVEEDOR (Categorías y Docs)
    // ---------------------------------------------------------
    $datos_proveedor = [
        'categorias' => [],
        'documentos' => []
    ];

    if ($rol === 'proveedor') {

        // A. Procesar Categorías (String "Cat1,Cat2" -> Array)
        if (!empty($_POST['lista_categorias'])) {
            $datos_proveedor['categorias'] = explode(',', $_POST['lista_categorias']);
        }

        // 🔥 NUEVA VALIDACIÓN: Mínimo 3 categorías
        if (count($datos_proveedor['categorias']) < 3) {
            mostrarSweetAlert('error', 'Perfil incompleto', 'El proveedor debe tener asignadas al menos 3 categorías de servicio.');
            exit(); // Detiene todo
        }

        // B. Procesar Documentos
        // Mapeamos el 'name' del input HTML al 'tipo' que guardaremos en BD
        $mapeo_docs = [
            'doc-cedula'       => 'cedula',
            'doc-foto'         => 'selfie',
            'doc-antecedentes' => 'antecedentes',
            'doc-certificado'  => 'certificado' // Opcional
        ];

        $ruta_base_docs = BASE_PATH . '/public/uploads/documentos/';
        if (!is_dir($ruta_base_docs)) mkdir($ruta_base_docs, 0755, true);

        foreach ($mapeo_docs as $input_name => $tipo_bd) {
            if (!empty($_FILES[$input_name]) && $_FILES[$input_name]['error'] === UPLOAD_ERR_OK) {

                $file = $_FILES[$input_name];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                // Validaciones de archivo
                if (!in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'])) {
                    mostrarSweetAlert('error', 'Archivo inválido', "El documento $tipo_bd debe ser PDF o Imagen.");
                    exit();
                }

                // Generar nombre único: tipo_timestamp_random.ext
                $nombre_archivo = $tipo_bd . '_' . time() . '_' . uniqid() . '.' . $ext;

                if (move_uploaded_file($file['tmp_name'], $ruta_base_docs . $nombre_archivo)) {
                    // Agregamos al array para enviar al modelo
                    $datos_proveedor['documentos'][] = [
                        'tipo' => $tipo_bd,
                        'archivo' => $nombre_archivo
                    ];
                }
            }
        }
    }

    // 4. Preparar Data Final
    $objUsuario = new Usuario();

    // Estado: Proveedor (0/Pendiente) - Otros (1/Activo)
    // Ajusta según los IDs de tu tabla usuario_estados (ej: 1=pendiente, 2=activo)
    $estado_usuario = ($rol === 'proveedor') ? 1 : 2;

    $data = [
        'nombres'    => $nombres,
        'apellidos'  => $apellidos,
        'documento'  => $documento,
        'email'      => $email,
        'clave'      => $clave_final,
        'telefono'   => $telefono,
        'ubicacion'  => $ubicacion,
        'rol'        => $rol,
        'foto'       => $ruta_img,
        'estado'     => $estado_usuario,
        // Datos extra para el modelo
        'categorias' => $datos_proveedor['categorias'],
        'documentos' => $datos_proveedor['documentos']
    ];

    // 5. Guardar en BD
    $resultado = $objUsuario->registrar($data);

    if ($resultado === true) {
        mostrarSweetAlert('success', '¡Registro Exitoso!', 'El usuario ha sido creado correctamente.', '/ProviServers/admin/consultar-usuarios');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo registrar. Verifica si el correo o documento ya existen.');
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
    $nuevo_estado = $_POST['estado'] ?? '';

    // Datos de la foto (campo oculto y archivo subido)
    $foto_perfil_actual = $_POST['foto_perfil_actual'] ?? ''; // La foto que ya estaba en la DB
    $archivo_nuevo = $_FILES['foto_perfil'] ?? null;

    // Validamos lo campos que son obligatorios
    if (empty($id) || empty($nombres) || empty($apellidos) || empty($documento) || empty($email) || empty($telefono) || empty($ubicacion) || empty($rol) || empty($nuevo_estado)) {
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
    // Obtenemos el estado anterior para saber si cambió (Para el correo)
    $datos_anteriores = $objUsuario->mostrarId($id);
    // protegemos el acceso
    $estado_anterior = $datos_anteriores['estado_id'] ?? null;

    $data = [
        'id' => $id,
        'nombres'     => $nombres,
        'apellidos'   => $apellidos,
        'documento'   => $documento,
        'email' => $email,
        'telefono' => $telefono,
        'ubicacion' => $ubicacion,
        'rol' => $rol,
        'foto_perfil' => $foto_para_db,
        'estado'      => $nuevo_estado
        // 'id_admin' => $id_admin,
    ];

    // Enviamos la data al metodo "registrar()" del la clase instanciada anteriormente "Usuario()" y esperamos una respuesta booleana del modelo
    $resultado = $objUsuario->actualizar($data);

    // Si la respuesta del modelo es verdadera confirmamos el registro y redireccionamos ,si es falsa notificamosy redireccionamos
    if ($resultado === true) {
        // 6. LÓGICA DE NOTIFICACIÓN POR EMAIL (Si el estado cambió)
        // echo "<pre>";
        // var_dump([
        //     'rol' => $rol,
        //     'estado_anterior' => $estado_anterior,
        //     'nuevo_estado' => $nuevo_estado
        // ]);
        // exit;

        if (
            $estado_anterior !== null &&
            $rol === 'proveedor' &&
            (int)$estado_anterior === 1 &&   // Pendiente
            (int)$nuevo_estado === 2         // Activo
        ) {
            enviarCorreoProveedorActivado($email, $nombres);
        }

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

// Función para devolver detalle de usuario vía AJAX
function obtenerDetalleUsuarioAjax() {
    // Verificar que sea una petición AJAX y tenga ID
    if (!isset($_GET['id'])) {
        echo json_encode(['error' => 'ID no proporcionado']);
        exit;
    }

    $id = intval($_GET['id']);
    $usuarioModel = new Usuario(); // Asumiendo que tienes instanciado tu modelo
    
    // Obtener datos básicos
    // Necesitas un método en tu modelo que traiga TODO por ID
    // Ejemplo: $datos = $usuarioModel->obtenerUsuarioCompleto($id);
    
    // COMO NO TENGO TU MODELO COMPLETO, SIMULARÉ LA ESTRUCTURA QUE DEBES RETORNAR:
    // Debes crear en tu modelo una función que haga JOIN con proveedores/clientes, categorias y documentos.
    
    $datos = $usuarioModel->obtenerDetalleCompleto($id); 

    if ($datos) {
        echo json_encode($datos);
    } else {
        echo json_encode(['error' => 'Usuario no encontrado']);
    }
    exit;
}
