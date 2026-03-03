<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';

// Modelos a utilizar (Si los unificas en uno solo, cambia esto)
require_once __DIR__ . '/../models/Membresia.php';
require_once __DIR__ . '/../models/Suscripcion.php';
require_once __DIR__ . '/../models/ProveedorPagosFacturacion.php';

// Iniciar sesión
session_start();

// Validar que el usuario esté logueado (Aplica para todas las rutas)
if (!isset($_SESSION['user']['id'])) {
    mostrarSweetAlert('error', 'Acceso denegado', 'Debes iniciar sesión para realizar esta acción.', '/ProviServers/login');
    exit();
}

$rolActual = $_SESSION['user']['rol'] ?? '';

// Capturamos el método de solicitud
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $accion = $_POST['accion'] ?? '';

        // Acciones de Administrador
        if ($accion === 'registrar_membresia') {
            registrarMembresia();
        } 
        elseif ($accion === 'actualizar_membresia') {
            actualizarMembresia();
        } 
        // Acciones de Proveedor
        elseif ($accion === 'actualizar_pagos_facturacion') {
            actualizarPagosFacturacion();
        }
        else {
            http_response_code(400);
            echo "Acción POST no válida";
        }
        break;

    case 'GET':
        $accion = $_GET['accion'] ?? '';

        // Acciones de Administrador
        if ($accion === 'eliminar_membresia') {
            eliminarMembresia($_GET['id'] ?? null);
        }
        elseif ($accion === 'cancelar_suscripcion') {
            cancelarSuscripcion($_GET['id'] ?? null);
        }
        elseif ($accion === 'eliminar_suscripcion') {
            eliminarSuscripcion($_GET['id'] ?? null);
        }
        elseif ($accion === 'detalle_suscripcion_json') {
            obtenerDetalleJSON($_GET['id'] ?? null); // Usado para el Modal AJAX
        }
        elseif ($accion === 'obtener_membresia_json') {
            $id = $_GET['id'] ?? null;
            if($id){
               $datos = mostrarMembresiaId($id);
               echo json_encode($datos);
               exit;
            }
        }
        else {
            http_response_code(400);
            echo "Acción GET no válida";
        }
        break;

    default:
        http_response_code(405);
        echo "Método no permitido";
        break;
}

// ==========================================================
// 1. FUNCIONES CRUD MEMBRESÍAS (ADMIN)
// ==========================================================

function registrarMembresia()
{
    if ($_SESSION['user']['rol'] !== 'admin') {
        mostrarSweetAlert('error', 'Acceso denegado', 'Solo administradores.');
        exit;
    }

    $tipo          = trim($_POST['tipo'] ?? '');
    $costo         = $_POST['costo'] ?? '';
    $duracion      = $_POST['duracion_dias'] ?? '';
    $descripcion   = trim($_POST['descripcion'] ?? '');
    $max_servicios = $_POST['max_servicios_activos'] ?? '';
    $orden_visual  = $_POST['orden_visual'] ?? null;

    $es_destacado   = isset($_POST['es_destacado']) ? 1 : 0;
    $permite_videos = isset($_POST['permite_videos']) ? 1 : 0;
    $acceso_stats   = isset($_POST['acceso_estadisticas_pro']) ? 1 : 0;
    $estado         = isset($_POST['estado']) ? 'ACTIVO' : 'INACTIVO';

    if (empty($tipo) || $costo === '' || empty($duracion) || empty($descripcion)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa Tipo, Costo, Duración y Descripción.');
        exit;
    }

    if (!is_numeric($costo) || !is_numeric($duracion) || !is_numeric($max_servicios)) {
        mostrarSweetAlert('error', 'Formato inválido', 'Costo, Duración y Máx Servicios deben ser números.');
        exit;
    }

    if ($orden_visual === '') {
        $orden_visual = null;
    }

    $objMembresia = new Membresia();
    $data = [
        'tipo'                    => $tipo,
        'costo'                   => (float)$costo,
        'duracion_dias'           => (int)$duracion,
        'descripcion'             => $descripcion,
        'max_servicios_activos'   => (int)$max_servicios,
        'orden_visual'            => $orden_visual,
        'acceso_estadisticas_pro' => $acceso_stats,
        'permite_videos'          => $permite_videos,
        'es_destacado'            => $es_destacado,
        'estado'                  => $estado
    ];

    $resultado = $objMembresia->registrar($data);

    if ($resultado) {
        mostrarSweetAlert('success', 'Membresía creada', 'El plan ha sido registrado correctamente.', '/ProviServers/admin/consultar-membresias');
    } else {
        mostrarSweetAlert('error', 'Error al registrar', 'No se pudo guardar en la base de datos.');
    }
    exit;
}

function actualizarMembresia()
{
    if ($_SESSION['user']['rol'] !== 'admin') {
        mostrarSweetAlert('error', 'Acceso denegado', 'Solo administradores.');
        exit;
    }

    $id = $_POST['id'] ?? null;
    if (!$id) {
        mostrarSweetAlert('error', 'Error', 'Identificador de membresía no válido.');
        exit;
    }

    $tipo          = trim($_POST['tipo'] ?? '');
    $costo         = $_POST['costo'] ?? '';
    $duracion      = $_POST['duracion_dias'] ?? '';
    $descripcion   = trim($_POST['descripcion'] ?? '');
    $max_servicios = $_POST['max_servicios_activos'] ?? '';
    $orden_visual  = $_POST['orden_visual'] ?? null;

    $es_destacado   = isset($_POST['es_destacado']) ? 1 : 0;
    $permite_videos = isset($_POST['permite_videos']) ? 1 : 0;
    $acceso_stats   = isset($_POST['acceso_estadisticas_pro']) ? 1 : 0;
    $estado         = isset($_POST['estado']) ? 'ACTIVO' : 'INACTIVO';

    if (empty($tipo) || $costo === '' || empty($duracion)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Faltan datos obligatorios.');
        exit;
    }

    if ($orden_visual === '') $orden_visual = null;

    $obj = new Membresia();
    $data = [
        'id'                      => $id,
        'tipo'                    => $tipo,
        'costo'                   => (float)$costo,
        'duracion_dias'           => (int)$duracion,
        'descripcion'             => $descripcion,
        'max_servicios_activos'   => (int)$max_servicios,
        'orden_visual'            => $orden_visual,
        'acceso_estadisticas_pro' => $acceso_stats,
        'permite_videos'          => $permite_videos,
        'es_destacado'            => $es_destacado,
        'estado'                  => $estado
    ];

    if ($obj->actualizar($data)) {
        mostrarSweetAlert('success', 'Actualizado', 'La membresía se actualizó correctamente.', '/ProviServers/admin/consultar-membresias');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudieron guardar los cambios.');
    }
    exit;
}

function eliminarMembresia($id)
{
    if ($_SESSION['user']['rol'] !== 'admin') {
        mostrarSweetAlert('error', 'Acceso denegado', 'Solo administradores.');
        exit;
    }

    if (!$id) {
        mostrarSweetAlert('error', 'Error', 'ID inválido.');
        exit;
    }

    $obj = new Membresia();

    if ($obj->tieneProveedores($id)) {
        mostrarSweetAlert('warning', 'No se puede eliminar', 'Esta membresía está asignada a proveedores activos. No se puede borrar.', '/ProviServers/admin/consultar-membresias');
        exit;
    }

    if ($obj->eliminar($id)) {
        mostrarSweetAlert('success', 'Eliminado', 'La membresía ha sido eliminada.', '/ProviServers/admin/consultar-membresias');
    } else {
        mostrarSweetAlert('error', 'Error', 'Ocurrió un error al intentar eliminar.');
    }
    exit;
}

function mostrarMembresiaId($id)
{
    $obj = new Membresia();
    return $obj->mostrarId($id);
}

// ==========================================================
// 2. FUNCIONES SUSCRIPCIONES (ADMIN)
// ==========================================================

function cancelarSuscripcion($id)
{
    if ($_SESSION['user']['rol'] !== 'admin') {
        mostrarSweetAlert('error', 'Acceso denegado', 'Solo administradores.');
        exit;
    }

    if (!$id) {
        mostrarSweetAlert('error', 'Error', 'ID inválido.');
        exit;
    }

    $obj = new Suscripcion();

    if ($obj->cancelar($id)) {
        mostrarSweetAlert('success', 'Suscripción Cancelada', 'El proveedor ya no tendrá acceso a los beneficios del plan.', '/ProviServers/admin/consultar-suscripciones');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo cancelar la suscripción.');
    }
    exit;
}

function eliminarSuscripcion($id)
{
    if ($_SESSION['user']['rol'] !== 'admin') {
        mostrarSweetAlert('error', 'Acceso denegado', 'Solo administradores.');
        exit;
    }

    if (!$id) {
        mostrarSweetAlert('error', 'Error', 'ID inválido.');
        exit;
    }

    $obj = new Suscripcion();
    if ($obj->eliminar($id)) {
        mostrarSweetAlert('success', 'Eliminado', 'Registro eliminado correctamente.', '/ProviServers/admin/consultar-suscripciones');
    } else {
        mostrarSweetAlert('error', 'Error', 'No se pudo eliminar el registro.');
    }
    exit;
}

function obtenerDetalleJSON($id)
{
    if (!$id) {
        echo json_encode(['error' => 'ID no proporcionado']);
        return;
    }

    $obj = new Suscripcion();
    $dato = $obj->obtenerPorId($id); 

    if ($dato) {
        $response = [
            'id'               => $dato['id'],
            'estado'           => $dato['estado'],
            'nombre_proveedor' => $dato['nombre_proveedor'],
            'email'            => $dato['email'],
            'telefono'         => $dato['telefono'] ?? 'N/A',
            'ubicacion'        => $dato['ubicacion'] ?? 'N/A',
            'foto_proveedor'   => $dato['foto_proveedor'] ?? null,
            'nombre_plan'      => $dato['nombre_plan'],
            'costo'            => $dato['costo'],
            'fecha_inicio'     => date('d/m/Y', strtotime($dato['fecha_inicio'])),
            'fecha_fin'        => date('d/m/Y', strtotime($dato['fecha_fin'])),
            'fecha_fin_raw'    => $dato['fecha_fin'] 
        ];
        
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Suscripción no encontrada']);
    }
    exit;
}

// ==========================================================
// 3. FUNCIONES PAGOS Y FACTURACIÓN (PROVEEDOR)
// ==========================================================

function actualizarPagosFacturacion()
{
    if ($_SESSION['user']['rol'] !== 'proveedor') {
        mostrarSweetAlert('error', 'Acceso denegado', 'Solo proveedores.', '/ProviServers/login');
        exit;
    }

    $usuarioId = (int) $_SESSION['user']['id'];

    $data = [
        'tipo_documento'         => trim($_POST['tipo_documento'] ?? ''),
        'numero_documento'       => trim($_POST['numero_documento'] ?? ''),
        'razon_social'           => trim($_POST['razon_social'] ?? ''),
        'regimen_fiscal'         => trim($_POST['regimen_fiscal'] ?? ''),
        'direccion_facturacion'  => trim($_POST['direccion_facturacion'] ?? ''),
        'ciudad_facturacion'     => trim($_POST['ciudad_facturacion'] ?? ''),
        'pais_facturacion'       => trim($_POST['pais_facturacion'] ?? ''),
        'correo_facturacion'     => trim($_POST['correo_facturacion'] ?? ''),
        'telefono_facturacion'   => trim($_POST['telefono_facturacion'] ?? ''),

        'banco'                  => trim($_POST['banco'] ?? ''),
        'tipo_cuenta'            => trim($_POST['tipo_cuenta'] ?? ''),
        'numero_cuenta'          => trim($_POST['numero_cuenta'] ?? ''),
        'titular_cuenta'         => trim($_POST['titular_cuenta'] ?? ''),
        'identificacion_titular' => trim($_POST['identificacion_titular'] ?? ''),
        'metodo_pago_preferido'  => trim($_POST['metodo_pago_preferido'] ?? ''),
        'nota_metodo_pago'       => trim($_POST['nota_metodo_pago'] ?? ''),

        'frecuencia_liquidacion' => trim($_POST['frecuencia_liquidacion'] ?? ''),
        'monto_minimo_retiro'    => trim($_POST['monto_minimo_retiro'] ?? ''),
        'acepta_factura_electronica' => $_POST['acepta_factura_electronica'] ?? null,
    ];

    $errores = [];

    if ($data['tipo_documento'] === '') $errores[] = 'Selecciona un tipo de documento de facturación.';
    if ($data['numero_documento'] === '') $errores[] = 'Ingresa el número de documento de facturación.';
    if ($data['razon_social'] === '') $errores[] = 'Ingresa el nombre o razón social para facturar.';
    if ($data['direccion_facturacion'] === '' || $data['ciudad_facturacion'] === '' || $data['pais_facturacion'] === '') {
        $errores[] = 'Completa la dirección, ciudad y país de facturación.';
    }
    if ($data['correo_facturacion'] === '') {
        $errores[] = 'Ingresa un correo de facturación.';
    } elseif (!filter_var($data['correo_facturacion'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo de facturación no tiene un formato válido.';
    }

    $frecuenciasPermitidas = ['', 'semanal', 'quincenal', 'mensual'];
    if (!in_array($data['frecuencia_liquidacion'], $frecuenciasPermitidas, true)) {
        $errores[] = 'La frecuencia de liquidación no es válida.';
    }

    if ($data['monto_minimo_retiro'] !== '') {
        if (!is_numeric($data['monto_minimo_retiro']) || (float) $data['monto_minimo_retiro'] < 0) {
            $errores[] = 'El monto mínimo de retiro debe ser un número mayor o igual a 0.';
        }
    }

    if ($data['banco'] !== '' || $data['numero_cuenta'] !== '' || $data['tipo_cuenta'] !== '') {
        if ($data['banco'] === '' || $data['tipo_cuenta'] === '' || $data['numero_cuenta'] === '') {
            $errores[] = 'Si vas a registrar una cuenta bancaria, completa banco, tipo de cuenta y número de cuenta.';
        }
    }

    if (!empty($errores)) {
        $mensaje = implode('<br>', $errores);
        mostrarSweetAlert('error', 'Datos inválidos', $mensaje, BASE_URL . '/proveedor/configuracion#pagos');
        exit();
    }

    $modelo = new ProveedorPagosFacturacion();
    $ok = $modelo->guardarDesdeFormulario($usuarioId, $data);

    if ($ok) {
        mostrarSweetAlert('success', 'Pagos y facturación actualizados', 'Tu información de pagos y facturación se guardó correctamente.', BASE_URL . '/proveedor/configuracion#pagos');
    } else {
        mostrarSweetAlert('error', 'Error al guardar', 'Ocurrió un problema al guardar tu configuración de pagos. Inténtalo nuevamente.', BASE_URL . '/proveedor/configuracion#pagos');
    }
    exit;
}