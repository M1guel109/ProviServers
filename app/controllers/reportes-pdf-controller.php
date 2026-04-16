<?php
require_once BASE_PATH . '/app/helpers/pdf-helper.php';
require_once BASE_PATH . '/app/controllers/admin-controller.php';
require_once BASE_PATH . '/app/controllers/membresia-controller.php';
// ✅ CORREGIDO: moderacion-controller tiene mostrarServicios() correcta
require_once BASE_PATH . '/app/controllers/moderacion-controller.php';
require_once BASE_PATH . '/app/models/categoria.php';

define('SERVER_ROOT', BASE_PATH . '/');

function reportesPdfController()
{
    $tipo = $_GET['tipo'] ?? '';

    switch ($tipo) {
        case 'usuarios':
            reporteUsuariosPDF();
            break;
        case 'serviciosProveedor':
            reporteServiciosProveedorPDF();
            break;
        case 'membresias':
            reporteMembresiasPDF();
            break;
        default:
            echo 'Tipo de reporte no válido';
            exit();
    }
}

function reporteUsuariosPDF()
{
    $usuarios = mostrarUsuarios();

    $foto_default_base64 = '';
    $ruta_fisica_default = SERVER_ROOT . 'public/uploads/usuarios/default_user.png';

    if (file_exists($ruta_fisica_default)) {
        $data = file_get_contents($ruta_fisica_default);
        $foto_default_base64 = 'data:image/png;base64,' . base64_encode($data);
    } else {
        error_log("No se encontró imagen por defecto: " . $ruta_fisica_default);
    }

    ob_start();
    require BASE_PATH . '/app/views/pdf/usuarios-pdf.php';
    $html = ob_get_clean();

    generarPDF($html, 'reporte_usuarios.pdf', false);
}

function reporteServiciosProveedorPDF()
{
    // ✅ CORREGIDO: usa mostrarServicios() del moderacion-controller
    $servicios = mostrarServicios();

    $categoriaModel = new Categoria();
    $categorias     = $categoriaModel->mostrar();

    $mapCategorias = [];
    foreach ($categorias as $categoria) {
        $mapCategorias[$categoria['id']] = $categoria['nombre'];
    }

    ob_start();
    require BASE_PATH . '/app/views/pdf/servicios-proveedor-pdf.php';
    $html = ob_get_clean();

    generarPDF($html, 'reporte_servicios_proveedor.pdf', false);
}

function reporteMembresiasPDF()
{
    $membresias = mostrarMembresias();

    ob_start();
    require BASE_PATH . '/app/views/pdf/membresias-pdf.php';
    $html = ob_get_clean();

    generarPDF($html, 'reporte_membresias.pdf', false);
}