<?php

require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/Solicitud.php';
require_once __DIR__ . '/../models/Publicacion.php';

session_start();

$method = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? null;

switch ($method) {

    /* =========================
       CREAR SOLICITUD (CLIENTE)
    ========================= */
    case 'POST':
        guardarSolicitud();
        break;

    /* =========================
       ACCIONES PROVEEDOR
    ========================= */
    case 'GET':

        if ($accion === 'aceptar') {
            aceptarSolicitud($_GET['id'] ?? null);
        }

        if ($accion === 'rechazar') {
            rechazarSolicitud($_GET['id'] ?? null);
        }

        if ($accion === 'detalle') {
            mostrarDetalle($_GET['id'] ?? null);
        }

        break;

    default:
        http_response_code(405);
        echo "Método no permitido";
        break;
}

/* ======================================================
   GUARDAR SOLICITUD (PRE-CONTRATO) - CLIENTE
   ====================================================== */

function guardarSolicitud()
{
    // 🔐 Validar sesión
    if (!isset($_SESSION['user']['id'])) {
        mostrarSweetAlert(
            'error',
            'Acceso denegado',
            'Debes iniciar sesión para solicitar un servicio'
        );
        exit();
    }

    // 📥 Datos principales
    $clienteId     = (int) $_SESSION['user']['id'];
    $publicacionId = (int) ($_POST['publicacion_id'] ?? 0);
    $titulo        = trim($_POST['titulo'] ?? '');
    $descripcion   = trim($_POST['descripcion'] ?? '');
    $direccion     = trim($_POST['direccion'] ?? '');
    $ciudad        = trim($_POST['ciudad'] ?? '');
    $zona          = trim($_POST['zona'] ?? '');
    $fecha         = trim($_POST['fecha_preferida'] ?? '');
    $franja        = trim($_POST['franja_horaria'] ?? '');
    $presupuesto   = $_POST['presupuesto'] ?? null;

    // 🧪 Validaciones básicas
    if (
        !$publicacionId ||
        !$titulo ||
        !$descripcion ||
        !$direccion ||
        !$ciudad ||
        !$fecha
    ) {
        mostrarSweetAlert(
            'error',
            'Campos incompletos',
            'Completa los campos obligatorios'
        );
        exit();
    }

    // 🔎 Obtener publicación y proveedor
    $pubModel = new Publicacion();
    $publicacion = $pubModel->obtenerPublicaActivaPorId($publicacionId);

    if (!$publicacion) {
        mostrarSweetAlert(
            'error',
            'Error',
            'La publicación no existe o no está activa'
        );
        exit();
    }

    $proveedorId = (int) $publicacion['proveedor_id'];

    // 🛑 Validar solicitud duplicada
    $solicitudModel = new Solicitud();
    if ($solicitudModel->tieneSolicitudActiva($clienteId, $publicacionId)) {
        mostrarSweetAlert(
            'warning',
            'Solicitud ya enviada',
            'Ya tienes una solicitud activa para este servicio'
        );
        exit();
    }

    /* ======================================================
       📎 PROCESAR ADJUNTOS
       ====================================================== */
    $adjuntos_guardados = [];

    if (!empty($_FILES['adjuntos']) && !empty($_FILES['adjuntos']['name'][0])) {

        $ruta_base = BASE_PATH . '/public/uploads/solicitudes/';
        if (!is_dir($ruta_base)) {
            mkdir($ruta_base, 0755, true);
        }

        $permitidas = ['pdf', 'png', 'jpg', 'jpeg'];
        $max_size   = 5 * 1024 * 1024; // 5MB

        foreach ($_FILES['adjuntos']['name'] as $i => $nombre_original) {

            if ($_FILES['adjuntos']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $ext   = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
            $size  = $_FILES['adjuntos']['size'][$i];
            $tipo  = $_FILES['adjuntos']['type'][$i];
            $tmp   = $_FILES['adjuntos']['tmp_name'][$i];

            if (!in_array($ext, $permitidas)) {
                mostrarSweetAlert(
                    'error',
                    'Archivo no permitido',
                    "El archivo {$nombre_original} no es válido (solo PDF/JPG/PNG)."
                );
                exit();
            }

            if ($size > $max_size) {
                mostrarSweetAlert(
                    'error',
                    'Archivo muy grande',
                    "El archivo {$nombre_original} supera 5MB"
                );
                exit();
            }

            $nombre_final = uniqid('sol_') . '.' . $ext;
            $destino = $ruta_base . $nombre_final;

            if (!move_uploaded_file($tmp, $destino)) {
                mostrarSweetAlert(
                    'error',
                    'Error al subir archivo',
                    "No se pudo guardar {$nombre_original}"
                );
                exit();
            }

            $adjuntos_guardados[] = [
                'archivo'      => $nombre_final,
                'tipo_archivo' => $tipo,
                'tamano'       => $size
            ];
        }
    }

    /* ======================================================
       📦 DATA FINAL PARA EL MODELO
       ====================================================== */
    $data = [
        'cliente_id'           => $clienteId,
        'proveedor_id'         => $proveedorId,
        'publicacion_id'       => $publicacionId,
        'titulo'               => $titulo,
        'descripcion'          => $descripcion,
        'direccion'            => $direccion,
        'ciudad'               => $ciudad,
        'zona'                 => $zona,
        'fecha_servicio'       => $fecha,
        'franja_horaria'       => $franja,
        'presupuesto_estimado' => $presupuesto,
        'adjuntos'             => $adjuntos_guardados
    ];

    // 🧠 Guardar solicitud + adjuntos
    try {
        $resultado = $solicitudModel->crear($data);

        if ($resultado === true) {
            mostrarSweetAlert(
                'success',
                'Solicitud enviada',
                'El proveedor recibirá tu solicitud.',
                '/ProviServers/cliente/explorar-servicios'
            );
        } else {
            mostrarSweetAlert(
                'error',
                'Error',
                'No se pudo enviar la solicitud (crear() devolvió false).'
            );
        }
    } catch (Throwable $e) {
        mostrarSweetAlert(
            'error',
            'Error técnico al crear la solicitud',
            'Mensaje: ' . $e->getMessage()
        );
    }

    exit();
}

/* ======================================================
   ACEPTAR SOLICITUD (PROVEEDOR)
   ====================================================== */

function aceptarSolicitud($id)
{
    if (!$id) {
        mostrarSweetAlert('error', 'Error', 'Solicitud inválida');
        exit();
    }

    // Validar sesión y rol proveedor
    if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'proveedor') {
        mostrarSweetAlert(
            'error',
            'Acceso denegado',
            'Solo los proveedores pueden aceptar solicitudes'
        );
        exit();
    }

    // Este es el ID de la tabla usuarios (coincide con proveedores.usuario_id)
    $proveedorUsuarioId = (int) $_SESSION['user']['id'];

    $modelo = new Solicitud();

    try {
        $resultado = $modelo->aceptar((int)$id, $proveedorUsuarioId);

        if ($resultado) {
            mostrarSweetAlert(
                'success',
                'Solicitud aceptada',
                'La solicitud fue aceptada correctamente y el servicio se marcó como en proceso.',
                '/ProviServers/proveedor/nuevas_solicitudes' // ajusta esta ruta si tienes otra
            );
        } else {
            mostrarSweetAlert(
                'error',
                'Error',
                'No se pudo aceptar la solicitud (aceptar() devolvió false).'
            );
        }
    } catch (Throwable $e) {
        mostrarSweetAlert(
            'error',
            'Error técnico al aprobar',
            'Mensaje: ' . $e->getMessage()
        );
    }

    exit();
}

/* ======================================================
   RECHAZAR SOLICITUD (PROVEEDOR)
   ====================================================== */

function rechazarSolicitud($id)
{
    if (!$id) {
        mostrarSweetAlert('error', 'Error', 'Solicitud inválida');
        exit();
    }

    if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'proveedor') {
        mostrarSweetAlert(
            'error',
            'Acceso denegado',
            'Solo los proveedores pueden rechazar solicitudes'
        );
        exit();
    }

    // ⚠️ Antes usabas $_SESSION['proveedor_id'], aquí debe ser el ID de usuarios
    $proveedorUsuarioId = (int) $_SESSION['user']['id'];

    $modelo = new Solicitud();

    try {
        $resultado = $modelo->rechazar((int)$id, $proveedorUsuarioId);

        if ($resultado) {
            mostrarSweetAlert(
                'success',
                'Solicitud rechazada',
                'La solicitud fue rechazada',
                '/ProviServers/proveedor/solicitudes'
            );
        } else {
            mostrarSweetAlert(
                'error',
                'Error',
                'No se pudo rechazar la solicitud'
            );
        }
    } catch (Throwable $e) {
        mostrarSweetAlert(
            'error',
            'Error técnico al rechazar',
            'Mensaje: ' . $e->getMessage()
        );
    }

    exit();
}

/* ======================================================
   OBTENER DETALLE (PUEDES USARLO EN UNA VISTA/MODAL)
   ====================================================== */

function mostrarDetalle($id)
{
    if (!$id) {
        return [];
    }

    $modelo = new Solicitud();
    $detalle = $modelo->obtenerDetalle((int)$id);

    return $detalle;
}
