<?php
require_once __DIR__ . '/../../config/database.php';

function obtenerNotificacionesAdmin() {
    $conexion = new Conexion();
    $db = $conexion->getConexion();
    $notificaciones = [];

    // =======================================================
    // 1. USUARIOS NUEVOS (Hoy) -> Tipo: 'nuevo_usuario'
    // =======================================================
    $sqlUsuarios = "SELECT COUNT(*) as total, MAX(created_at) as ultima_hora 
                    FROM usuarios 
                    WHERE DATE(created_at) = CURDATE()";
    $stmt = $db->query($sqlUsuarios);
    $resUsuarios = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resUsuarios['total'] > 0) {
        $notificaciones[] = [
            'tipo'    => 'nuevo_usuario', // <--- CLAVE PARA EL COLOR AZUL
            'titulo'  => 'Nuevos Usuarios',
            'mensaje' => "Hoy se han registrado {$resUsuarios['total']} usuarios nuevos.",
            'hora'    => calcularTiempo($resUsuarios['ultima_hora'])
        ];
    }

    // =======================================================
    // 2. SOLICITUDES PENDIENTES -> Tipo: 'alerta'
    // =======================================================
    $sqlSolicitudes = "SELECT COUNT(*) as total, MAX(created_at) as ultima_hora  
                       FROM solicitudes WHERE estado = 'pendiente'";
    $stmt = $db->query($sqlSolicitudes);
    $resSol = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resSol['total'] > 0) {
        $notificaciones[] = [
            'tipo'    => 'alerta', // <--- CLAVE PARA EL COLOR AMARILLO
            'titulo'  => 'Solicitudes Pendientes',
            'mensaje' => "Hay {$resSol['total']} solicitudes esperando acción.",
            'hora'    => calcularTiempo($resSol['ultima_hora'])
        ];
    }

    // =======================================================
    // 3. PAGOS PENDIENTES -> Tipo: 'pago'
    // =======================================================
    // Esto es nuevo y vital para finanzas
    $sqlPagos = "SELECT COUNT(*) as total, MAX(created_at) as ultima_hora 
                 FROM pagos WHERE estado_pago = 'pendiente'";
    $stmt = $db->query($sqlPagos);
    $resPagos = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resPagos['total'] > 0) {
        $notificaciones[] = [
            'tipo'    => 'pago', // <--- CLAVE PARA EL COLOR VERDE (Billete)
            'titulo'  => 'Pagos por Verificar',
            'mensaje' => "Existen {$resPagos['total']} comprobantes de pago pendientes.",
            'hora'    => calcularTiempo($resPagos['ultima_hora'])
        ];
    }

    // =======================================================
    // 4. DOCUMENTOS RECHAZADOS (Opcional) -> Tipo: 'error'
    // =======================================================
    $sqlDocs = "SELECT COUNT(*) as total 
                FROM documentos_proveedor WHERE estado = 'rechazado' AND fecha_revision >= CURDATE()";
    $stmt = $db->query($sqlDocs);
    $totalDocs = $stmt->fetchColumn();

    if ($totalDocs > 0) {
        $notificaciones[] = [
            'tipo'    => 'error', // <--- CLAVE PARA EL COLOR ROJO
            'titulo'  => 'Documentos Rechazados',
            'mensaje' => "Se rechazaron $totalDocs documentos hoy.",
            'hora'    => 'Hoy'
        ];
    }

    // =======================================================
    // 5. SERVICIOS EN PROCESO -> Tipo: 'info'
    // =======================================================
    $sqlProceso = "SELECT COUNT(*) as total FROM servicios_contratados WHERE estado = 'en_proceso'";
    $stmt = $db->query($sqlProceso);
    $totalProceso = $stmt->fetchColumn();

    if ($totalProceso > 0) {
        $notificaciones[] = [
            'tipo'    => 'info', // <--- CLAVE PARA EL COLOR CYAN
            'titulo'  => 'Actividad en la Plataforma',
            'mensaje' => "$totalProceso servicios se están ejecutando ahora mismo.",
            'hora'    => 'En vivo'
        ];
    }

    return $notificaciones;
}

// Función auxiliar mejorada para tiempos relativos
function calcularTiempo($fecha) {
    if (!$fecha) return 'Reciente';
    
    $timestamp = strtotime($fecha);
    $diferencia = time() - $timestamp;
    
    if ($diferencia < 60) return 'Hace un momento';
    if ($diferencia < 3600) return 'Hace ' . floor($diferencia / 60) . ' min';
    if ($diferencia < 86400) return 'Hace ' . floor($diferencia / 3600) . ' h';
    if ($diferencia < 172800) return 'Ayer';
    
    return date('d/m/Y', $timestamp);
}
?>