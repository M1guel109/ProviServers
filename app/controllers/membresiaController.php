<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/membresia.php';

// Capturamos en una variale el metodo o solicitud hecha al servidor
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'actualizar') {
            // Llama a la función para actualizar una membresía existente
            actualizarMembresia();
        } else {
            // Llama a la función para registrar una nueva membresía (Crear)
            registrarMembresia();
        }

        break;
    case 'GET':
        $accion = $_GET['accion'] ?? '';

        if ($accion === 'eliminar') {
            // Llama a la función para eliminar una membresía
            // Asegúrate de validar que $_GET['id'] existe antes de usarlo
            eliminarMembresia($_GET['id'] ?? null);
        }

        if (isset($_GET['id'])) {
            // Muestra los detalles de una membresía específica
            mostrarMembresiaId($_GET['id']);
        } else {
            // Muestra la lista completa de membresías
            mostrarMembresias();
        }

        break;
    // Puedes descomentar y usar PUT/DELETE si tu framework lo permite y lo configuras
    // case 'PUT':
    //      actualizarMembresia();
    //      break;
    // case 'DELETE':
    //      eliminarMembresia();
    //      break;
    default:
        // Manejo de métodos no soportados
        http_response_code(405);
        echo "Método no permitido";
        break;
}
// Funciones del crud
function registrarMembresia()
{
    // 1. CAPTURA DE DATOS DEL FORMULARIO
    // Capturamos los datos del Paso 1 (Información General)
    $tipo = $_POST['tipo'] ?? '';
    $costo = $_POST['costo'] ?? '';
    $duracion_dias = $_POST['duracion_dias'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';

    // Capturamos los datos del Paso 2 (Configuración de Límites)
    $max_servicios_activos = $_POST['max_servicios_activos'] ?? '';
    $orden_visual = $_POST['orden_visual'] ?? null; // Es opcional, puede ser null
    $acceso_estadisticas_pro = $_POST['acceso_estadisticas_pro'] ?? 0; // Radio buttons
    $permite_videos = $_POST['permite_videos'] ?? 0; // Radio buttons
    $es_destacado = $_POST['es_destacado'] ?? 0; // Radio buttons
    $estado = $_POST['estado'] ?? ''; // Select (ACTIVO/INACTIVO)


    // 2. VALIDACIÓN DE CAMPOS OBLIGATORIOS
    // El campo 'orden_visual' es el único opcional (se permite null)
    if (empty($tipo) || empty($costo) || empty($duracion_dias) || empty($descripcion) || empty($max_servicios_activos) || empty($estado)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos obligatorios del plan.');
        exit();
    }

    // 3. VALIDACIÓN ADICIONAL DE VALORES NUMÉRICOS
    // Aseguramos que costo y límites sean valores positivos válidos
    $costo_float = floatval($costo);
    $duracion_int = intval($duracion_dias);
    $max_servicios_int = intval($max_servicios_activos);

    if ($costo_float < 0 || $duracion_int < 1 || $max_servicios_int < 1) {
        mostrarSweetAlert('error', 'Valores Inválidos', 'El costo, la duración y el límite de servicios deben ser valores positivos.');
        exit();
    }

    // El campo 'orden_visual' si se proporciona debe ser un entero positivo
    if (!is_null($orden_visual) && intval($orden_visual) < 1) {
        mostrarSweetAlert('error', 'Orden Visual Inválida', 'La prioridad visual debe ser un número entero positivo.');
        exit();
    }


    // *********************************************************************************
    // 4. LÓGICA DE ARCHIVOS (Removida: El plan de membresía no requiere subir una foto)
    // *********************************************************************************


    // 5. INSTANCIAR MODELO Y PREPARAR DATOS
    // Asumiendo que tu modelo se llama 'Membresia'
    $objMembresia = new Membresia();

    $data = [
        'tipo' => $tipo,
        'costo' => $costo_float, // Usamos el float validado
        'duracion_dias' => $duracion_int, // Usamos el int validado
        'descripcion' => $descripcion,
        'max_servicios_activos' => $max_servicios_int, // Usamos el int validado
        'orden_visual' => ($orden_visual !== null && $orden_visual !== '') ? intval($orden_visual) : null,
        'acceso_estadisticas_pro' => intval($acceso_estadisticas_pro),
        'permite_videos' => intval($permite_videos),
        'es_destacado' => intval($es_destacado),
        'estado' => $estado,
        // Si necesitas guardar el ID del administrador que lo registró:
        // 'id_admin' => $_SESSION['user']['id'], 
    ];

    // 6. LLAMAR AL MÉTODO DEL MODELO
    // Enviamos la data al método "registrar()" del modelo "Membresia()"
    $resultado = $objMembresia->registrar($data);

    // 7. RESPUESTA Y REDIRECCIÓN
    if ($resultado === true) {
        mostrarSweetAlert('success', 'Registro de Plan exitoso', 'El nuevo plan de membresía ha sido guardado.', '/ProviServers/admin/registrar-membresia');
    } else {
        // Podrías necesitar un manejo de errores más específico aquí si el modelo devuelve
        // información sobre duplicados de 'tipo' de plan, por ejemplo.
        mostrarSweetAlert('error', 'Error al registrar', 'No se pudo registrar el plan de membresía. Intenta nuevamente.');
    }

    exit();
}

function mostrarMembresias()
{
  
    // 1. Instanciar el modelo de Membresía, pasándole la conexión.
    $resultado = new Membresia();

    // 2. Llamar al método del modelo que contiene la lógica SQL.
    $membresias = $resultado->mostrar();

    // 3. Devolver el resultado (la lista de membresías).
    return $membresias;
}

function mostrarMembresiaId() {}

function actualizarMembresia() {}

function eliminarMembresia() {}
