<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ======================================================
// ROUTER PRINCIPAL — ProviServers
// ======================================================

require_once __DIR__ . '/config/config.php';

// Obtener la URL actual
$requestUri = $_SERVER['REQUEST_URI'];

// Quitar el prefijo de la carpeta del proyecto
$request = str_replace('/ProviServers', '', $requestUri);

// Quitar parámetros tipo ?id=123
$request = strtok($request, '?');

// Quitar barra final si existe
$request = rtrim($request, '/');

// Si queda vacío, tomar como raíz
if ($request === '') {
    $request = '/';
}

// ======================================================
// ENRUTAMIENTO
// ======================================================

switch ($request) {

    // ==================================================
    // RUTAS PÚBLICAS
    // ==================================================

    case '/':
        require BASE_PATH . '/app/views/website/index.php';
        break;

    case '/contacto/enviar':
        require_once BASE_PATH . '/app/controllers/contacto-controller.php';
        procesarContacto();
        break;

    // ==================================================
    // AUTENTICACIÓN — Vistas
    // ==================================================

    case '/login':
        require BASE_PATH . '/app/views/auth/login.php';
        break;

    case '/registro':
        // Carga categorías en $categorias_bd antes de renderizar el HTML
        require_once BASE_PATH . '/app/controllers/auth-controller.php';
        cargarRegistro();
        break;

    case '/restablecer-contrasena':
        require BASE_PATH . '/app/views/auth/restablecer-contrasena.php';
        break;

    // AUTENTICACIÓN — Procesos (el switch interno del controller despacha por $_POST['accion'])

    case '/iniciar-sesion':
        require_once BASE_PATH . '/app/controllers/auth-controller.php';
        break;

    case '/registro-usuario':
        require_once BASE_PATH . '/app/controllers/auth-controller.php';
        break;

    case '/generar-clave':
        require_once BASE_PATH . '/app/controllers/auth-controller.php';
        break;

    case '/cerrar-sesion':
        $_GET['accion'] = 'cerrar_sesion';
        require_once BASE_PATH . '/app/controllers/auth-controller.php';
        break;

    case '/idioma':
        require_once BASE_PATH . '/app/controllers/idioma-controller.php';
        break;

    // ==================================================
    // PANEL ADMINISTRADOR — Vistas generales
    // ==================================================

    case '/admin/dashboard':
        require BASE_PATH . '/app/views/dashboard/admin/dashboard-admin.php';
        break;

    case '/admin/perfil':
        require BASE_PATH . '/app/views/dashboard/admin/dashboard-perfil.php';
        break;

    case '/admin/calendario':
        require BASE_PATH . '/app/views/dashboard/admin/dashboard-calendario.php';
        break;

    case '/admin/finanzas':
        require BASE_PATH . '/app/views/dashboard/admin/finanzas.php';
        break;

    case '/admin/facturacion':
        require BASE_PATH . '/app/views/dashboard/admin/facturacion.php';
        break;

    case '/admin/ajustes':
        require BASE_PATH . '/app/views/dashboard/admin/ajustes.php';
        break;

    case '/admin/notificaciones':
        require BASE_PATH . '/app/views/dashboard/admin/notificaciones.php';
        break;

    // ADMINISTRADOR — Perfil

    case '/admin/perfil/cambiar-clave':
        require BASE_PATH . '/app/controllers/perfil-controller.php';
        cambiarContrasenaUsuario();
        break;

    case '/admin/perfil/actualizar':
        require BASE_PATH . '/app/controllers/perfil-controller.php';
        break;

    // ADMINISTRADOR — Usuarios

    case '/admin/registrar-usuario':
        require BASE_PATH . '/app/views/dashboard/admin/registrar-usuario.php';
        break;

    case '/admin/consultar-usuarios':
        require BASE_PATH . '/app/views/dashboard/admin/consultar-usuarios.php';
        break;

    case '/admin/editar-usuario':
        require BASE_PATH . '/app/views/dashboard/admin/editar-usuario.php';
        break;

    case '/admin/guardar-usuario':
        require BASE_PATH . '/app/controllers/admin-controller.php';
        break;

    case '/admin/actualizar-usuario':
        require BASE_PATH . '/app/controllers/admin-controller.php';
        break;

    case '/admin/eliminar-usuario':
        require BASE_PATH . '/app/controllers/admin-controller.php';
        break;

    case '/admin/usuario-detalle':
        require BASE_PATH . '/app/controllers/admin-controller.php';
        obtenerDetalleUsuarioAjax();
        break;

    // ADMINISTRADOR — Categorías

    case '/admin/registrar-categoria':
        require BASE_PATH . '/app/views/dashboard/admin/registrar-categoria.php';
        break;

    case '/admin/consultar-categorias':
        require BASE_PATH . '/app/views/dashboard/admin/gestionar-categorias.php';
        break;

    case '/admin/editar-categoria':
        require BASE_PATH . '/app/views/dashboard/admin/editar-categoria.php';
        break;

    case '/admin/guardar-categoria':
        require BASE_PATH . '/app/controllers/admin-controller.php';
        break;

    case '/admin/actualizar-categoria':
        require BASE_PATH . '/app/controllers/admin-controller.php';
        break;

    case '/admin/eliminar-categoria':
        require BASE_PATH . '/app/controllers/admin-controller.php';
        break;

    // ADMINISTRADOR — Membresías

    case '/admin/registrar-membresia':
        require BASE_PATH . '/app/views/dashboard/admin/registrar-membresia.php';
        break;

    case '/admin/consultar-membresias':
        require BASE_PATH . '/app/views/dashboard/admin/gestionar-membresias.php';
        break;

    case '/admin/editar-membresia':
        require BASE_PATH . '/app/views/dashboard/admin/editar-membresia.php';
        break;

    case '/admin/guardar-membresia':
        require BASE_PATH . '/app/controllers/admin-controller.php';
        break;

    case '/admin/actualizar-membresia':
        require BASE_PATH . '/app/controllers/admin-controller.php';
        break;

    case '/admin/eliminar-membresia':
        require BASE_PATH . '/app/controllers/admin-controller.php';
        break;

    case '/admin/consultar-suscripciones':
        require BASE_PATH . '/app/views/dashboard/admin/suscripciones-activas.php';
        break;

    case '/admin/suscripcion-detalle':
        require_once BASE_PATH . '/app/controllers/admin-controller.php';
        obtenerDetalleJSON($_GET['id'] ?? null);
        break;

    // ADMINISTRADOR — Reportes

    case '/admin/reportes-usuarios':
        require BASE_PATH . '/app/views/dashboard/admin/reportes-usuarios.php';
        break;

    case '/admin/reporte':
        require_once BASE_PATH . '/app/controllers/admin-controller.php';
        reportesPdfController();
        break;

    // ADMINISTRADOR — Moderación

    case '/admin/consultar-servicios':
        require BASE_PATH . '/app/views/dashboard/admin/moderacion-publicaciones.php';
        break;

    case '/admin/servicio-detalle':
        require_once BASE_PATH . '/app/controllers/admin-controller.php';
        apiDetalleServicio();
        break;

    case '/admin/dashboard-stats':
        require_once BASE_PATH . '/app/controllers/admin-controller.php';
        obtenerDashboardStatsAjax();
        break;

    case '/admin/moderacion-actualizar':
        require BASE_PATH . '/app/controllers/admin-controller.php';
        break;

    // ==================================================
    // PANEL PROVEEDOR — Vistas generales
    // ==================================================

    case '/proveedor/dashboard':
        require BASE_PATH . '/app/views/dashboard/proveedor/dashboard-proveedor.php';
        break;

    case '/proveedor/calendario':
        require BASE_PATH . '/app/views/dashboard/proveedor/calendario.php';
        break;

    case '/proveedor/estadisticas':
        require BASE_PATH . '/app/views/dashboard/proveedor/estadisticas.php';
        break;

    case '/proveedor/finanzas':
        require BASE_PATH . '/app/views/dashboard/proveedor/finanzas.php';
        break;

    case '/proveedor/facturacion':
        require BASE_PATH . '/app/views/dashboard/proveedor/facturacion.php';
        break;

    case '/proveedor/promociones':
        require BASE_PATH . '/app/views/dashboard/proveedor/promociones.php';
        break;

    case '/proveedor/membresia':
        require BASE_PATH . '/app/views/dashboard/proveedor/membresia.php';
        break;

    case '/proveedor/completadas':
        require BASE_PATH . '/app/views/dashboard/proveedor/completadas.php';
        break;

    // PROVEEDOR — Servicios

    case '/proveedor/registrar-servicio':
        require BASE_PATH . '/app/views/dashboard/proveedor/registrar-servicio.php';
        break;

    case '/proveedor/listar-servicio':
        require BASE_PATH . '/app/views/dashboard/proveedor/mis-servicios.php';
        break;

    case '/proveedor/publicaciones':
        require BASE_PATH . '/app/views/dashboard/proveedor/mis-publicaciones.php';
        break;

    case '/proveedor/editar-servicio':
        require BASE_PATH . '/app/views/dashboard/proveedor/editar-servicio.php';
        break;

    case '/proveedor/guardar-servicio':
        require BASE_PATH . '/app/controllers/proveedor-controller.php';
        break;

    case '/proveedor/reporte':
        require BASE_PATH . '/app/controllers/reportes-pdf-controller.php';
        reportesPdfController();
        break;

    // PROVEEDOR — Solicitudes

    case '/proveedor/nuevas-solicitudes':
        require BASE_PATH . '/app/views/dashboard/proveedor/solicitudes/index.php';
        break;

    case '/proveedor/solicitudes':
        require BASE_PATH . '/app/controllers/proveedor-operacion-controller.php';
        break;

    case '/proveedor/en-proceso':
        require_once BASE_PATH . '/app/controllers/proveedor-operacion-controller.php';
        require BASE_PATH . '/app/views/dashboard/proveedor/en-proceso.php';
        break;

    case '/proveedor/actualizar-estado':
        require_once BASE_PATH . '/app/controllers/proveedor-operacion-controller.php';
        break;

    // PROVEEDOR — Oportunidades y cotizaciones

    case '/proveedor/oportunidades':
        require_once BASE_PATH . '/app/controllers/proveedor-operacion-controller.php';
        mostrarOportunidades();
        break;

    case '/proveedor/oportunidades/enviar-cotizacion':
        require_once BASE_PATH . '/app/controllers/proveedor-operacion-controller.php';
        break;

    // PROVEEDOR — Reseñas

    case '/proveedor/resenas':
        require_once BASE_PATH . '/app/controllers/proveedor-resenas-controller.php';
        mostrarResenasProveedor();
        break;

    case '/proveedor/resenas/responder':
        require_once BASE_PATH . '/app/controllers/proveedor-resenas-controller.php';
        guardarRespuestaProveedor();
        break;

    // PROVEEDOR — Configuración y perfil

    case '/proveedor/configuracion':
        require BASE_PATH . '/app/views/dashboard/proveedor/configuracion-proveedor.php';
        break;

    case '/proveedor/guardar-perfil-profesional':
        require BASE_PATH . '/app/controllers/proveedor-perfil-controller.php';
        break;

    case '/proveedor/actualizar-credenciales':
        require BASE_PATH . '/app/controllers/proveedor-perfil-controller.php';
        break;

    case '/proveedor/actualizar-seguridad':
        require BASE_PATH . '/app/controllers/proveedor-perfil-controller.php';
        break;

    case '/proveedor/cerrar-sesiones':
        require BASE_PATH . '/app/controllers/proveedor-perfil-controller.php';
        break;

    case '/proveedor/guardar-disponibilidad':
        require BASE_PATH . '/app/controllers/proveedor-perfil-controller.php';
        break;

    case '/proveedor/guardar-notificaciones':
        require BASE_PATH . '/app/controllers/proveedor-perfil-controller.php';
        break;

    case '/proveedor/guardar-pagos':
        require BASE_PATH . '/app/controllers/proveedor-perfil-controller.php';
        break;

    case '/proveedor/guardar-politicas':
        require BASE_PATH . '/app/controllers/proveedor-perfil-controller.php';
        break;

    case '/proveedor/logout':
        $_GET['accion'] = 'cerrar_sesion';
        require_once BASE_PATH . '/app/controllers/auth-controller.php';
        break;

    // ==================================================
    // PANEL CLIENTE — Vistas generales
    // ==================================================

    case '/cliente/dashboard':
        require BASE_PATH . '/app/views/dashboard/cliente/dashboard-cliente.php';
        break;

    case '/cliente/favoritos':
        require BASE_PATH . '/app/views/dashboard/cliente/favoritos.php';
        break;

    case '/cliente/ayuda':
        require BASE_PATH . '/app/views/dashboard/cliente/ayuda.php';
        break;

    case '/cliente/historial':
        require BASE_PATH . '/app/views/dashboard/cliente/historial-servicios.php';
        break;

    // CLIENTE — Servicios

    case '/cliente/explorar-servicios':
    case '/cliente/explorar':
    case '/cliente/publicacion':
    case '/cliente/guardar-solicitud':
    case '/cliente/servicios-contratados':
    case '/cliente/mis-solicitudes':
    case '/cliente/necesidades':
        require BASE_PATH . '/app/controllers/cliente-controller.php';
        break;

    case '/cliente/solicitar-servicio':
        require BASE_PATH . '/app/views/dashboard/cliente/solicitar-servicio.php';
        break;

    case '/cliente/servicios-contratados/calificar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/cliente/servicios-contratados');
            exit;
        }
        require BASE_PATH . '/app/controllers/cliente-controller.php';
        break;

    case '/cliente/servicios-contratados/cancelar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/cliente/servicios-contratados');
            exit;
        }
        require BASE_PATH . '/app/controllers/cliente-controller.php';
        break;

    case '/cliente/necesidades/crear':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/cliente/dashboard');
            exit;
        }
        require BASE_PATH . '/app/controllers/cliente-controller.php';
        break;

    case '/cliente/necesidades/aceptar-cotizacion':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/cliente/necesidades');
            exit;
        }
        require BASE_PATH . '/app/controllers/cliente-controller.php';
        break;

    // CLIENTE — Perfil

    case '/cliente/perfil':
        require BASE_PATH . '/app/controllers/perfil-controller.php';
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $id = (int) ($_SESSION['user']['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $usuario = mostrarPerfilCliente($id);
        require BASE_PATH . '/app/views/dashboard/cliente/perfil.php';
        break;

    case '/cliente/perfil/cambiar-clave':
        require BASE_PATH . '/app/controllers/perfil-controller.php';
        cambiarContrasenaUsuario('/cliente/perfil');
        break;

    // CLIENTE — Mensajes

    case '/cliente/mensajes':
        require BASE_PATH . '/app/controllers/mensajes-controller.php';
        (new MensajesController())->inbox();
        break;

    case '/mensajes/abrir':
        require BASE_PATH . '/app/controllers/mensajes-controller.php';
        (new MensajesController())->abrir();
        break;

    case '/mensajes/ver':
        require BASE_PATH . '/app/controllers/mensajes-controller.php';
        (new MensajesController())->ver();
        break;

    case '/mensajes/enviar':
        require BASE_PATH . '/app/controllers/mensajes-controller.php';
        (new MensajesController())->enviar();
        break;

    case '/mensajes/poll':
        require BASE_PATH . '/app/controllers/mensajes-controller.php';
        (new MensajesController())->poll();
        break;

    // ==================================================
    // 404
    // ==================================================

    default:
        http_response_code(404);
        require BASE_PATH . '/app/views/auth/error-404.php';
        break;
}
