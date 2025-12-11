<?php
require_once BASE_PATH . '/app/helpers/pdf_helper.php';
require_once BASE_PATH . '/app/controllers/adminController.php';
require_once BASE_PATH . '/app/controllers/membresiaController.php';
// NUEVO: para usar mostrarServicios()
require_once BASE_PATH . '/app/controllers/proveedorController.php';
// NUEVO: para mapear categorías
require_once BASE_PATH . '/app/models/categoria.php';

// ATENCIÓN: Esta constante debe apuntar a la raíz del proyecto (donde está 'public').
// Si BASE_PATH apunta a la carpeta principal (ej: /var/www/ProviServers), esto es correcto.
define('SERVER_ROOT', BASE_PATH . '/');

// En este controlador encontramos la dependencia del helper (configuracion de la generacion de pdfs) 
// y posteriormente tendremos todos lo controladores que se requieran segun los reportes de pdf que vaya a generar
// para poder usar la funcion y traer los datos desde el modelo ej:mostrarUsuarios();
// despues de las dependencias encontramos la funcion global (function reportesPdfController()) la cual valida el tipo de reporte 
// que fue enviado por metodo get desde el boton de la vista.Se debe generar un case por cada tipo de reporte
// y asi mismo generar la funcion en el controlador para que redirecione a la vista que se debe crear en la carpeta views/pdf/
// (cada reporte es diferente(la estructura el html))por ultimo modificamos la ruta en nuestro archivo index la cual debera ser una sola por rol
// y dentro de la cual invocaremos nuestra funcion global que genera los pdfs(function reportesPdfController())

// Esta funcion se encarga de validar el tipo de reporte y ejecutar la función correspondiente 
function reportesPdfController()
{
    // Capturamos el tipo de reporte enviado desde la vista 
    $tipo = $_GET['tipo'] ?? '';

    // Segun el tipo de reporte ejecutamos x función 
    switch ($tipo) {
        case 'usuarios':
            reporteUsuariosPDF();
            break;

        case 'serviciosProveedor':        // 👈 NUEVO CASE
            reporteServiciosProveedorPDF();
            break;

        case 'membresias': // 👈 NUEVO: Caso para el reporte de Membresías
            reporteMembresiasPDF();
            break;
        default:
            echo 'Tipo de reporte no válido';
            exit();
            break;
    }
}


function reporteUsuariosPDF()
{
    // Cargar los datos de los usuarios
    $usuarios = mostrarUsuarios();

    // --- LÓGICA DE BASE64 PARA LA IMAGEN DEFAULT ---
    $foto_default_base64 = '';

    // Genera la ruta FÍSICA para file_get_contents()
    $ruta_fisica_default = SERVER_ROOT . 'public/uploads/usuarios/default_user.png';

    // VErifica que el archivo exista en el disco antes de leerlo
    if (file_exists($ruta_fisica_default)) {
        // Lee el archivo y lo codifica en Base64 para incrustarlo directamente en el PDF
        $data = file_get_contents($ruta_fisica_default);
        $foto_default_base64 = 'data:image/png;base64,' . base64_encode($data);
    } else {
        // Si no existe, se queda como un string vacío, o podrías loguear un error aquí.
        error_log("CRÍTICO: No se encontró la imagen por defecto en la ruta: " . $ruta_fisica_default);
    }
    // --- FIN LÓGICA DE BASE64 ---
    // Cargar la vista y obtenerla como HTML
    ob_start();
    // Asignamos los datos de la funcion en el controlador enlazado a una variable que podamos manipular en la vista pdf
    // $usuarios = mostrarUsuarios();

    // Archivo que tiene la interfaz diseñada en htlm
    require BASE_PATH . '/app/views/pdf/usuarios_pdf.php';
    $html = ob_get_clean();

    generarPDF($html, 'reporte_usuarios.pdf', false);
}

function reporteServiciosProveedorPDF()
{

    // 1. Traer datos igual que en la vista de Mis Servicios
    // Usamos la función del controlador de proveedor
    $servicios = mostrarServicios();

    // 2. Mapeo id_categoria -> nombre
    $categoriaModel = new Categoria();
    $categorias = $categoriaModel->mostrar();

    $mapCategorias = [];
    foreach ($categorias as $categoria) {
        $mapCategorias[$categoria['id']] = $categoria['nombre'];
    }

    // 3. Cargar la vista PDF y obtenerla como HTML
    ob_start();

    // Archivo que tendrá la interfaz diseñada en HTML para el PDF
    // (lo creamos en el siguiente paso)
    require BASE_PATH . '/app/views/pdf/servicios_proveedor_pdf.php';

    $html = ob_get_clean();

    // 4. Generar el PDF con tu helper
    generarPDF($html, 'reporte_servicios_proveedor.pdf', false);
}

function reporteMembresiasPDF()
{

    // Cargar la vista y obtenerla como HTML
    ob_start();
    // Asignamos los datos de la funcion en el controlador enlazado a una variable que podamos manipular en la vista pdf
    $membresias = mostrarMembresias();

    // Archivo que tiene la interfaz diseñada en htlm
    require BASE_PATH . '/app/views/pdf/membresias_pdf.php';
    $html = ob_get_clean();

    generarPDF($html, 'reporte_membresias.pdf', false);
}
