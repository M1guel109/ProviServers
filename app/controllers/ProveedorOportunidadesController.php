<?php
// Importamos Modelo y Sesión
require_once BASE_PATH . '/app/models/Necesidad.php';
require_once BASE_PATH . '/app/models/Cotizacion.php';
require_once BASE_PATH . '/app/helpers/session_proveedor.php'; 

function mostrarOportunidades() {
    // 1. Instancia del modelo
    $modelo = new Necesidad();
    
    // 2. ID del usuario actual
    $usuarioId = $_SESSION['user']['id'];

    // 3. Filtros simples desde la URL
    $filtros = [
        'busqueda'  => $_GET['q'] ?? '',
        'ciudad'    => $_GET['ciudad'] ?? '',
        'categoria' => $_GET['categoria'] ?? ''
    ];

    // 4. Obtener datos reales de la BD
    // Asegúrate de que tu modelo Necesidad tenga el método 'obtenerOportunidades'
    // Si no lo actualizaste, usa 'listarAbiertasParaProveedorUsuario'
    $necesidades = $modelo->obtenerOportunidades($usuarioId, $filtros);

    // 5. Cargar la vista
    require_once BASE_PATH . '/app/views/dashboard/proveedor/oportunidades.php';
}

function enviarCotizacion() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $usuarioId = $_SESSION['user']['id'];
        $necesidadId = $_POST['necesidad_id'] ?? null;
        
        // 1. CAMBIO: Recoger el Título real y mapear datos
        $datos = [
            'titulo'          => $_POST['titulo'],           // <--- Ahora viene del formulario
            'precio'          => $_POST['precio_oferta'],    // name="precio_oferta" en el HTML
            'tiempo_estimado' => $_POST['tiempo_estimado'],  // name="tiempo_estimado" en el HTML
            'mensaje'         => $_POST['mensaje']
        ];

        // Validacion simple por seguridad
        if (empty($necesidadId) || empty($datos['precio'])) {
            header('Location: ' . BASE_URL . '/proveedor/oportunidades?status=error');
            exit;
        }

        // 2. CAMBIO: Usar el modelo Cotizacion
        $modelo = new Cotizacion();
        
        // 3. CAMBIO: Llamar al método específico de tu nuevo modelo
        // Este método ya se encarga de buscar el ID del proveedor usando el $usuarioId
        $exito = $modelo->crearParaNecesidadPorProveedorUsuario($usuarioId, $necesidadId, $datos);

        // Redirección
        if ($exito) {
            header('Location: ' . BASE_URL . '/proveedor/oportunidades?status=success');
        } else {
            // Si falla (probablemente porque ya cotizó), mandamos error
            header('Location: ' . BASE_URL . '/proveedor/oportunidades?status=error&msg=No se pudo enviar o ya existe una cotización');
        }
        exit;
    }
}
?>