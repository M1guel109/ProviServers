<?php
require_once BASE_PATH . '/app/helpers/pdf_helper.php';
require_once BASE_PATH . '/app/controllers/adminController.php';

// En este controlador encontramos la dependencia del helper (configuracion de la generacion de pdfs) 
// y posteriormente tendremos todos lo controladores que se requieran segun los reportes de pdf que vaya a generar
// para poder usar la funcion y traer los datos desde el modelo ej:mostrarUsuarios();
// despues de las dependencias encontramos la funcion global (function reportesPdfController()) la cual valida el tipo de reporte 
// que fue enviado por metodo get desde el boton de la vista.Se debe generar un case por cada tipo de reporte
// y asi mismo generar la funcion en el controlador para que redirecione a la vista que se debe crear en la carpeta views/pdf/
// (cada reporte es diferente(la estructura el html))por ultimo modificamos la ruta en nuestro archivo index la cual debera ser una sola por rol
// y dentro de la cual invocaremos nuestra funcion global que genera los pdfs(function reportesPdfController())

// Esta funcion se encarga de validar el tipo de reporte y ejecutar la función correspondiente 
function reportesPdfController(){
    // Capturamos el tipo de reporte enviado desde la vista 
    $tipo = $_GET['tipo'];

    // Segun el tipo de reporte ejecutamos x funcion 
    switch ($tipo) {
        case 'usuarios':
            reporteUsuariosPDF();
            break;
        
        default:
            exit();


            
            break;
    }
}

function reporteUsuariosPDF() {

    // Cargar la vista y obtenerla como HTML
    ob_start();
    // Asignamos los datos de la funcion en el controlador enlazado a una variable que podamos manipular en la vista pdf
    $usuarios = mostrarUsuarios();

    // Archivo que tiene la interfaz diseñada en htlm
    require BASE_PATH . '/app/views/pdf/usuarios_pdf.php';
    $html =ob_get_clean();

    generarPDF($html, 'reporte_usuarios.pdf', false);

}

