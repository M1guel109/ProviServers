<?php
// index.php - Router Principal 

require_once __DIR__ . '/config/config.php';

//Obtener la URL actual (por ejemplo, /vetwiling/login)
$requestUri = $_SERVER['REQUEST_URI'];

//Quita el prefijo de la carpeta del proyecto
$request = str_replace('/ProviServers', '', $requestUri);

//Quitar parametros tipo id=123
$request = strtok($request, '?');

// Quitar la barra final (Si exixte)
$request = rtrim($request, '/');

// Si la ruta queda vacia, se interactua como "/"
if ($request === '') $request = '/';

//Enrutaminento basico
switch ($request) {
    case '/':
        require BASE_PATH . '/app/views/website/index.php';
        break;

    // Inicio rutas que sean necesarias para el login
    case '/login':
        require BASE_PATH . '/app/views/auth/login.php';
        break;

    case '/registro':
        require BASE_PATH . '/app/views/auth/registro.php';
        break;

    case '/registro-usuario':
        require BASE_PATH . '/app/controllers/registroController.php';
        break;

    case '/iniciar-sesion':
        require BASE_PATH . '/app/controllers/loginController.php';
        break;

    case '/cerrar-sesion':
        require BASE_PATH . '/app/controllers/cerrarSesionController.php';
        $controller = new cerrarSesionController();
        $controller->index();
        break;

    case '/generar-clave':
        require BASE_PATH . '/app/controllers/passwordController.php';
        break;

    case '/reestablecer-contrasena':
        require BASE_PATH . '/app/views/auth/reestablecer-Contrasena.php';
        break;
    // Fin de rutas login

    // Rutas del admin
    case '/admin/dashboard':
        require BASE_PATH . '/app/views/dashboard/admin/dashboardAdmin.php';
        break;

    case '/admin/perfil':
        require BASE_PATH . '/app/views/dashboard/admin/dashboardPerfil.php';
        break;

    case '/admin/perfil/cambiar-clave':
        require BASE_PATH . '/app/controllers/perfilController.php';
        cambiarContrasenaUsuario();
        break;

    case '/admin/registrar-usuario':
        require BASE_PATH . '/app/views/dashboard/admin/registrarUsuario.php';
        break;

    case '/admin/editar-usuario':
        require BASE_PATH . '/app/views/dashboard/admin/editarUsuario.php';
        break;

    case '/admin/actualizar-usuario':
        require BASE_PATH . '/app/controllers/adminController.php';
        break;

    case '/admin/guardar-usuario':
        require BASE_PATH . '/app/controllers/adminController.php';
        break;

    case '/admin/consultar-usuarios':
        require BASE_PATH . '/app/views/dashboard/admin/dashboardTabla.php';
        break;

    case '/admin/eliminar-usuario':
        require BASE_PATH . '/app/controllers/adminController.php';
        break;

    case '/admin/reporte':
        require BASE_PATH . '/app/controllers/reportesPdfController.php';
        reportesPdfController();
        break;

    case '/admin/registrar-categoria':
        require BASE_PATH . '/app/views/dashboard/admin/registrarCategoria.php';
        break;

    case '/admin/guardar-categoria':
        require BASE_PATH . '/app/controllers/categoriaController.php';
        break;

    case '/admin/consultar-categorias':
        require BASE_PATH . '/app/views/dashboard/admin/gestionarCategorias.php';
        break;

    case '/admin/eliminar-categoria':
        require BASE_PATH . '/app/controllers/categoriaController.php';
        break;

    case '/admin/editar-categoria':
        require BASE_PATH . '/app/views/dashboard/admin/editarCategoria.php';
        break;

    case '/admin/actualizar-categoria':
        require BASE_PATH . '/app/controllers/categoriaController.php';
        break;

    case '/admin/registrar-membresia':
        require BASE_PATH . '/app/views/dashboard/admin/registrarMembresia.php';
        break;

    case '/admin/guardar-membresia':
        require BASE_PATH . '/app/controllers/membresiaController.php';
        break;

    case '/admin/reportes-usuarios':
        require BASE_PATH . '/app/views/dashboard/admin/reportesUsuarios.php';
        break;


    case '/admin/finanzas':
        require BASE_PATH . '/app/views/dashboard/admin/dashboardFinanzas.php';
        break;

    // Fin de rutas login

    // Rutas del proveedor
    case '/proveedor/dashboard':
        require BASE_PATH . '/app/views/dashboard/proveedor/dashboardProveedor.php';
        break;

    case '/proveedor/registrar-servicio':
        require BASE_PATH . '/app/views/dashboard/proveedor/registrarServicio.php';
        break;

    case '/proveedor/guardar-servicio':
        require BASE_PATH . '/app/controllers/proveedorController.php';
        break;
    case '/proveedor/listar-servicio':
        require BASE_PATH . '/app/views/dashboard/proveedor/misServicios.php';
        break;
    case '/proveedor/editar-servicio':
        require BASE_PATH . '/app/views/dashboard/proveedor/editarServicio.php';
        break;
    // Rutas del proveedor
    case '/proveedor/reporte':
        require BASE_PATH . '/app/controllers/reportesPdfController.php';
        reportesPdfController();
        break;

    case '/proveedor/nuevas_solicitudes':
        require BASE_PATH . '/app/views/dashboard/proveedor/nuevas_solicitudes.php';
        break;

    case '/proveedor/logout':
        require BASE_PATH . '/app/controllers/logoutController.php';
        break;


    // Rutas del cliente
    case '/cliente/dashboard':
        require BASE_PATH . '/app/views/dashboard/cliente/dashboardCliente.php';
        break;
    default:
        http_response_code(404);
        require BASE_PATH . '/app/views/auth/error404.php';
        break;
}
