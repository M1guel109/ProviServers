<?php
require_once __DIR__ . '/../../config/database.php';

function obtenerNotificacionesAdmin() {
    $conexion = new Conexion();
    $db = $conexion->getConexion();
    $notificaciones = [];

    // 1. Usuarios nuevos hoy
    $sqlUsuarios = "SELECT COUNT(*) as total, MAX(created_at) as ultima_hora 
                    FROM usuarios 
                    WHERE DATE(created_at) = CURDATE()";
    $stmt = $db->query($sqlUsuarios);
    $resUsuarios = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resUsuarios['total'] > 0) {
        $notificaciones[] = [
            'icono' => 'bi-person-plus text-primary',
            'titulo' => 'Nuevos Usuarios',
            'mensaje' => "Hoy se han registrado {$resUsuarios['total']} usuarios.",
            'hora' => calcularTiempo($resUsuarios['ultima_hora'])
        ];
    }

    // 2. Solicitudes Pendientes (Total)
    $sqlSolicitudes = "SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'pendiente'";
    $stmt = $db->query($sqlSolicitudes);
    $totalSol = $stmt->fetchColumn();

    if ($totalSol > 0) {
        $notificaciones[] = [
            'icono' => 'bi-clipboard-check text-warning',
            'titulo' => 'Solicitudes Pendientes',
            'mensaje' => "Hay $totalSol solicitudes esperando proveedor.",
            'hora' => 'Al día'
        ];
    }

    // 3. Servicios En Proceso (Actividad actual)
    $sqlProceso = "SELECT COUNT(*) as total FROM servicios_contratados WHERE estado = 'en_proceso'";
    $stmt = $db->query($sqlProceso);
    $totalProceso = $stmt->fetchColumn();

    if ($totalProceso > 0) {
        $notificaciones[] = [
            'icono' => 'bi-gear-wide-connected text-success',
            'titulo' => 'Servicios Activos',
            'mensaje' => "$totalProceso proveedores están trabajando ahora mismo.",
            'hora' => 'En vivo'
        ];
    }

    return $notificaciones;
}

// Función auxiliar para decir "Hace 5 min"
function calcularTiempo($fecha) {
    if (!$fecha) return '';
    $timestamp = strtotime($fecha);
    $diferencia = time() - $timestamp;
    
    if ($diferencia < 60) return 'Hace un momento';
    if ($diferencia < 3600) return 'Hace ' . floor($diferencia / 60) . ' min';
    if ($diferencia < 86400) return 'Hace ' . floor($diferencia / 3600) . ' horas';
    return 'Hace días';
}
?>