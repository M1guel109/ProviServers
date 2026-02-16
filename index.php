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

    // ... otros casos ...

    case '/contacto/enviar':
        require_once BASE_PATH . '/app/controllers/ContactoController.php';
        procesarContacto();
        break;

    // ...
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
        require BASE_PATH . '/app/views/dashboard/admin/consultarUsuarios.php';
        break;

    // En tu index.php
    case '/admin/api/usuario-detalle':
        require BASE_PATH . '/app/controllers/adminController.php';
        obtenerDetalleUsuarioAjax();
        break;

    case '/admin/eliminar-usuario':
        require BASE_PATH . '/app/controllers/adminController.php';
        break;

    case '/admin/reporte':
        require BASE_PATH . '/app/controllers/reportesPdfController.php';
        reportesPdfController();
        reporteMembresiasPDF();
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

    case '/admin/consultar-membresias':
        require BASE_PATH . '/app/views/dashboard/admin/gestionarMembresias.php';
        break;

    case '/admin/eliminar-membresia':
        require BASE_PATH . '/app/controllers/membresiaController.php';
        break;

    case '/admin/editar-membresia':
        require BASE_PATH . '/app/views/dashboard/admin/editarMembresia.php';
        break;

    case '/admin/actualizar-membresia':
        require BASE_PATH . '/app/controllers/membresiaController.php';
        break;

    case '/admin/reportes-usuarios':
        require BASE_PATH . '/app/views/dashboard/admin/reportesUsuarios.php';
        break;

    case '/admin/finanzas':
        require BASE_PATH . '/app/views/dashboard/admin/dashboardFinanzas.php';
        break;

    case '/admin/consultar-servicios':
        require BASE_PATH . '/app/views/dashboard/admin/moderacionPublicaciones.php';
        break;

    case '/admin/calendario':
        require BASE_PATH . '/app/views/dashboard/admin/dashboardCalendario.php';
        break;

        // ... (Tus otras rutas de admin) ...

    // =======================================================
    // 🔍 RUTAS AJAX PARA MODERACIÓN DE SERVICIOS
    // =======================================================

    // 1. API para obtener el detalle del servicio (JSON)
    // Se llama cuando das clic en el "Ojito"
    case '/admin/api/servicio-detalle':
        require_once BASE_PATH . '/app/controllers/moderacionController.php';
        apiDetalleServicio();
        break;

    // ... (El resto de tus rutas) ...
    case '/admin/moderacion-actualizar':
        require BASE_PATH . '/app/controllers/moderacionController.php';
        break;

    // Fin de rutas admin

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
    case '/proveedor/publicaciones':
        require BASE_PATH . '/app/views/dashboard/proveedor/misPublicaciones.php';
        break;
    case '/proveedor/editar-servicio':
        require BASE_PATH . '/app/views/dashboard/proveedor/editarServicio.php';
        break;
    // Rutas del proveedor
    case '/proveedor/reporte':
        require BASE_PATH . '/app/controllers/reportesPdfController.php';
        reportesPdfController();
        break;
      case '/proveedor/calendarioProveedor':
        require BASE_PATH . '/app/views/dashboard/proveedor/calendarioProveedor.php';
        break;    
    case '/proveedor/configuracion':
        require BASE_PATH . '/app/views/dashboard/proveedor/configuracionProveedor.php';
        break;
    case '/proveedor/guardar-perfil-profesional':
        require BASE_PATH . '/app/controllers/proveedorPerfilController.php';
        break;

    // ✅ Endpoints de Cuenta y Seguridad
    case '/proveedor/actualizar-credenciales':
    case '/proveedor/actualizar-seguridad':
    case '/proveedor/cerrar-sesiones':
        require BASE_PATH . '/app/controllers/proveedorCuentaController.php';
        break;
    case '/proveedor/guardar-disponibilidad':
        require BASE_PATH . '/app/controllers/proveedorDisponibilidadController.php';
        break;
    case '/proveedor/guardar-notificaciones':
        require BASE_PATH . '/app/controllers/proveedorNotificacionesController.php';
        break;
    case '/proveedor/guardar-pagos':
        require BASE_PATH . '/app/controllers/proveedorPagosController.php';
        break;
    case '/proveedor/guardar-politicas':
        require BASE_PATH . '/app/controllers/proveedorPoliticasController.php';
        break;



    case '/proveedor/nuevas_solicitudes':
        require BASE_PATH . '/app/views/dashboard/proveedor/solicitudes/index.php';
        break;


    // Vista: Ver lista de trabajos en proceso
    case '/proveedor/en-proceso':
        require_once __DIR__ . '/app/controllers/proveedorServiciosContratadosController.php';
        // Nota: Como tu controlador ejecuta lógica al cargarse (el switch interno), 
        // solo con requerirlo ya funcionaría si el REQUEST_METHOD es GET.
        // Pero para ser limpios, tu vista 'en-proceso.php' debería llamar a mostrarServiciosContratadosProveedor().

        // Lo ideal es que aquí cargues la VISTA:
        require BASE_PATH . '/app/views/dashboard/proveedor/enProceso.php';

        break;


    // Acción AJAX: Actualizar estado (POST)
    case '/proveedor/actualizar-estado':
        require_once __DIR__ . '/app/controllers/proveedorServiciosContratadosController.php';
        // Al requerir el archivo, tu switch interno detectará REQUEST_METHOD = POST
        // y llamará a actualizarEstadoServicio(). ¡Magia!
        break;
    case '/proveedor/completadas':
        require BASE_PATH . '/app/views/dashboard/proveedor/completadas.php';
        break;

    case '/proveedor/oportunidades':
        // 1. Llamas al CONTROLADOR
        require_once BASE_PATH . '/app/controllers/proveedorOportunidadesController.php';
        // 2. Ejecutas la FUNCIÓN que busca los datos y luego carga la vista
        mostrarOportunidades();
        break;

    case '/proveedor/oportunidades/enviar-cotizacion':
        require_once BASE_PATH . '/app/controllers/proveedorOportunidadesController.php';
        enviarCotizacion();
        break;

    case '/proveedor/resenas':
        // 1. Cargamos el Controlador (el "Cocinero")
        require_once BASE_PATH . '/app/controllers/proveedorResenasController.php';

        // 2. Ejecutamos la función que prepara los datos y luego llama a la vista
        mostrarResenasProveedor();
        break;

    case '/proveedor/resenas/responder':
        require_once BASE_PATH . '/app/controllers/proveedorResenasController.php';
        guardarRespuestaProveedor();
        break;

    case '/proveedor/logout':
        require BASE_PATH . '/app/controllers/logoutController.php';
        break;

    case '/proveedor/solicitudes':
        require BASE_PATH . '/app/controllers/solicitudController.php';
        break;




    // Rutas del cliente
    case '/cliente/dashboard':
        require BASE_PATH . '/app/views/dashboard/cliente/dashboardCliente.php';
        break;

    // Catálogo público de servicios (explorar)
    case '/cliente/explorar-servicios':
        require BASE_PATH . '/app/controllers/clientePublicacionesController.php';
        mostrarCatalogoPublico();
        break;

    // Alias opcional: /cliente/explorar -> redirige al mismo catálogo
    case '/cliente/explorar':
        require BASE_PATH . '/app/controllers/clientePublicacionesController.php';
        mostrarCatalogoPublico();
        break;

    // case '/cliente/servicios-contratados':
    //     require BASE_PATH . '/app/views/dashboard/cliente/serviciosContratados.php';
    //     break;


    case '/cliente/servicios-contratados':
        require BASE_PATH . '/app/controllers/clienteServiciosContratadosController.php';
        break;


    case '/cliente/servicios-contratados/calificar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/cliente/servicios-contratados');
            exit;
        }
        require BASE_PATH . '/app/controllers/clienteCalificarServicioContratadoController.php';
        break;


    case '/cliente/servicios-contratados/cancelar':
        // Recomendado: asegurar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/cliente/servicios-contratados');
            exit;
        }

        require BASE_PATH . '/app/controllers/clienteCancelarServicioContratadoController.php';
        break;

    case '/cliente/mis-solicitudes':
        require BASE_PATH . '/app/controllers/clienteMisSolicitudesController.php';
        break;


    case '/cliente/mensajes':
        require BASE_PATH . '/app/views/dashboard/cliente/mensajes.php';
        break;

    case '/cliente/favoritos':
        require BASE_PATH . '/app/views/dashboard/cliente/favoritos.php';
        break;

    case '/cliente/historial':
        require BASE_PATH . '/app/views/dashboard/cliente/historialServicios.php';
        break;

    case '/cliente/perfil':
        require BASE_PATH . '/app/controllers/perfilController.php';

        if (session_status() === PHP_SESSION_NONE) session_start();
        $id = (int)($_SESSION['user']['id'] ?? 0);

        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // ✅ Carga el perfil con llaves compatibles con la vista
        $usuario = mostrarPerfilCliente($id);

        require BASE_PATH . '/app/views/dashboard/cliente/perfil.php';
        break;

    case '/cliente/perfil/cambiar-clave':
        require BASE_PATH . '/app/controllers/perfilController.php';
        cambiarContrasenaUsuario('/cliente/perfil');
        break;



    case '/cliente/ayuda':
        require BASE_PATH . '/app/views/dashboard/cliente/ayuda.php';
        break;


    // case '/cliente/dashboard':
    //     require BASE_PATH . '/app/views/dashboard/cliente/dashboardCliente.php';
    //     break;

    // case '/cliente/explorar-servicios':
    //     require BASE_PATH . '/app/controllers/clientePublicacionesController.php';
    //     break;

    case '/cliente/publicacion':
        require BASE_PATH . '/app/controllers/clientePublicacionDetalleController.php';
        break;

    // Cliente - solicitar servicio (vista formulario)
    case '/cliente/solicitar-servicio':
        require BASE_PATH . '/app/views/dashboard/cliente/solicitarServicio.php';
        break;

    case '/cliente/guardar-solicitud':
        require BASE_PATH . '/app/controllers/solicitudController.php';
        break;



    // Cliente - Necesidades
    case '/cliente/necesidades':
        require BASE_PATH . '/app/controllers/clienteNecesidadesController.php';
        break;

    case '/cliente/necesidades/crear':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/cliente/dashboard');
            exit;
        }
        require BASE_PATH . '/app/controllers/clienteNecesidadesCrearController.php';
        break;

    case '/cliente/necesidades/aceptar-cotizacion':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/cliente/necesidades');
            exit;
        }
        require BASE_PATH . '/app/controllers/clienteNecesidadesAceptarCotizacionController.php';
        break;

    //case necesarios para mensajes 📩
    case 'cliente/mensajes':
        require BASE_PATH . '/app/controllers/MensajesController.php';
        (new MensajesController())->inbox();
        break;

    case '/mensajes/abrir':
        require BASE_PATH . '/app/controllers/MensajesController.php';
        (new MensajesController())->abrir();
        break;

    case '/mensajes/ver':
        require BASE_PATH . '/app/controllers/MensajesController.php';
        (new MensajesController())->ver();
        break;

    case '/mensajes/enviar':
        require BASE_PATH . '/app/controllers/MensajesController.php';
        (new MensajesController())->enviar();
        break;

    case '/mensajes/poll':
        require BASE_PATH . '/app/controllers/MensajesController.php';
        (new MensajesController())->poll();
        break;



    default:
        http_response_code(404);
        require BASE_PATH . '/app/views/auth/error404.php';
        break;
}
