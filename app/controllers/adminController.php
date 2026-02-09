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
    // 1. Capturar Datos Básicos del Formulario
    $id = $_POST['id'] ?? '';
    $nombres = $_POST['nombres'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $documento = $_POST['documento'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $ubicacion = $_POST['ubicacion'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $nuevo_estado = $_POST['estado'] ?? '';
    
    // Contraseña (Opcional - solo se envía si el usuario escribió algo)
    $nueva_clave = $_POST['clave'] ?? '';

    // Validar campos obligatorios
    if (empty($id) || empty($nombres) || empty($apellidos) || empty($documento) || empty($email) || empty($telefono) || empty($ubicacion) || empty($rol) || empty($nuevo_estado)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos obligatorios.');
        exit();
    }

    // ---------------------------------------------------------
    // 2. GESTIÓN DE FOTO DE PERFIL (Avatar)
    // ---------------------------------------------------------
    $foto_perfil_actual = $_POST['foto_actual'] ?? ''; 
    $foto_para_db = $foto_perfil_actual;
    $archivo_nuevo = $_FILES['foto'] ?? null;
    
    $ruta_destino_perfil = BASE_PATH . '/public/uploads/usuarios/';

    if ($archivo_nuevo && $archivo_nuevo['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($archivo_nuevo['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) {
            $nombre_archivo_nuevo = uniqid('user_') . '.' . $ext;
            
            if (move_uploaded_file($archivo_nuevo['tmp_name'], $ruta_destino_perfil . $nombre_archivo_nuevo)) {
                $foto_para_db = $nombre_archivo_nuevo;
                
                // Borrar foto vieja si existe y no es la default
                if (!empty($foto_perfil_actual) && $foto_perfil_actual !== 'default_user.png' && file_exists($ruta_destino_perfil . $foto_perfil_actual)) {
                    unlink($ruta_destino_perfil . $foto_perfil_actual);
                }
            }
        } else {
            mostrarSweetAlert('error', 'Formato inválido', 'La foto de perfil debe ser PNG, JPG o JPEG.');
            exit();
        }
    }

    // ---------------------------------------------------------
    // 3. LÓGICA ESPECIAL PARA PROVEEDOR (Categorías + Docs)
    // ---------------------------------------------------------
    // Inicializamos arrays vacíos para evitar errores en el modelo
    $lista_categorias = [];
    $documentos_nuevos = [];

    if ($rol === 'proveedor') {
        
        // A. Procesar Categorías (String "Cat1,Cat2" -> Array)
        if (!empty($_POST['lista_categorias'])) {
            $lista_categorias = explode(',', $_POST['lista_categorias']);
        }

        // Validación: Si es proveedor, debe tener al menos 3 categorías (incluso al editar)
        if (count($lista_categorias) < 3) {
            mostrarSweetAlert('error', 'Perfil incompleto', 'El proveedor debe tener asignadas al menos 3 categorías.');
            exit();
        }

        // B. Procesar Documentos Nuevos (Cédula, Antecedentes, etc.)
        // Solo procesamos los que se hayan subido en este formulario
        $mapeo_docs = [
            'doc-cedula'       => 'cedula',
            'doc-antecedentes' => 'antecedentes',
            'doc-foto'         => 'selfie',
            'doc-certificado'  => 'certificado'
        ];

        $ruta_docs = BASE_PATH . '/public/uploads/documentos/';
        if (!is_dir($ruta_docs)) mkdir($ruta_docs, 0755, true);

        foreach ($mapeo_docs as $input_name => $tipo_bd) {
            if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] === UPLOAD_ERR_OK) {
                
                $file = $_FILES[$input_name];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                // Validar extensión
                if (in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'])) {
                    // Nombre único: tipo_timestamp_random.ext
                    $nombre_doc = $tipo_bd . '_' . time() . '_' . uniqid() . '.' . $ext;
                    
                    if (move_uploaded_file($file['tmp_name'], $ruta_docs . $nombre_doc)) {
                        $documentos_nuevos[] = [
                            'tipo'    => $tipo_bd,
                            'archivo' => $nombre_doc
                        ];
                    }
                } else {
                    mostrarSweetAlert('error', 'Archivo inválido', "El documento $tipo_bd debe ser PDF o Imagen.");
                    exit();
                }
            }
        }
    }

    // ---------------------------------------------------------
    // 4. PREPARAR DATOS Y LLAMAR AL MODELO
    // ---------------------------------------------------------
    $objUsuario = new Usuario();
    
    // Obtenemos estado anterior para la notificación (Lógica existente)
    $datos_anteriores = $objUsuario->mostrarId($id); // Asegúrate que esta función use tu nueva versión optimizada
    $estado_anterior = $datos_anteriores['estado_id'] ?? null;

    $data = [
        'id'          => $id,
        'nombres'     => $nombres,
        'apellidos'   => $apellidos,
        'documento'   => $documento,
        'email'       => $email,
        'telefono'    => $telefono,
        'ubicacion'   => $ubicacion,
        'rol'         => $rol,
        'foto_perfil' => $foto_para_db,
        'estado'      => $nuevo_estado,
        'clave'       => !empty($nueva_clave) ? $nueva_clave : null,
        
        // DATOS EXTRA PARA EL MODELO (Esencial para que funcione el cambio de rol)
        'categorias'        => $lista_categorias,
        'documentos_nuevos' => $documentos_nuevos
    ];

    // Ejecutar actualización en BD
    $resultado = $objUsuario->actualizar($data);

    if ($resultado === true) {
        
        // 5. Notificación de Activación (Tu lógica existente)
        if (
            $estado_anterior !== null &&
            $rol === 'proveedor' &&
            (int)$estado_anterior === 1 &&   // Pendiente
            (int)$nuevo_estado === 2         // Activo
        ) {
            if(function_exists('enviarCorreoProveedorActivado')) {
                enviarCorreoProveedorActivado($email, $nombres);
            }
        }

        mostrarSweetAlert('success', 'Actualización exitosa', 'El usuario ha sido modificado correctamente.', '/ProviServers/admin/consultar-usuarios');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo actualizar la base de datos. Intenta nuevamente.');
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
