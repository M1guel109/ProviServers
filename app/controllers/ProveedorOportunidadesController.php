<?php
// Importamos Modelo y Sesión
require_once BASE_PATH . '/app/models/Necesidad.php';
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
        
        // Datos del formulario
        $datos = [
            'titulo'          => 'Propuesta enviada desde plataforma',
            'precio'          => $_POST['precio_oferta'],
            'tiempo_estimado' => $_POST['tiempo_estimado'],
            'mensaje'         => $_POST['mensaje']
        ];

        // Guardar
        $modelo = new Necesidad();
        $exito = $modelo->crearCotizacionParaNecesidad($usuarioId, $necesidadId, $datos);

        // Redirección con estado para SweetAlert
        if ($exito) {
            header('Location: ' . BASE_URL . '/proveedor/oportunidades?status=success');
        } else {
            header('Location: ' . BASE_URL . '/proveedor/oportunidades?status=error');
        }
        exit;
    }
}
?>