<?php
require_once __DIR__ . '/../../config/database.php';

if (!function_exists('calcularTiempoNotif')) {
    function calcularTiempoNotif(?string $fecha): string
    {
        if (!$fecha) return 'Reciente';
        $diff = time() - strtotime($fecha);
        if ($diff < 60)     return 'Hace un momento';
        if ($diff < 3600)   return 'Hace ' . floor($diff / 60) . ' min';
        if ($diff < 86400)  return 'Hace ' . floor($diff / 3600) . ' h';
        if ($diff < 172800) return 'Ayer';
        return date('d/m/Y', strtotime($fecha));
    }
}

if (!function_exists('estiloNotificacion')) {
    function estiloNotificacion(string $tipo): array
    {
        switch ($tipo) {
            case 'success':       return ['icon' => 'bi-check-circle',           'color' => 'text-success', 'bg' => 'bg-success-subtle'];
            case 'pago':          return ['icon' => 'bi-cash-coin',              'color' => 'text-success', 'bg' => 'bg-success-subtle'];
            case 'alerta':        return ['icon' => 'bi-exclamation-triangle',   'color' => 'text-warning', 'bg' => 'bg-warning-subtle'];
            case 'error':         return ['icon' => 'bi-x-circle',               'color' => 'text-danger',  'bg' => 'bg-danger-subtle'];
            case 'nuevo_usuario': return ['icon' => 'bi-person-plus',            'color' => 'text-primary', 'bg' => 'bg-primary-subtle'];
            default:              return ['icon' => 'bi-info-circle',            'color' => 'text-info',    'bg' => 'bg-info-subtle'];
        }
    }
}

function obtenerNotificacionesCliente(int $usuarioId): array
{
    $conexion = new Conexion();
    $db = $conexion->getConexion();
    $notificaciones = [];

    try {
        // 1. Solicitudes aceptadas (últimos 7 días)
        $stmt = $db->prepare("
            SELECT COUNT(*) AS total, MAX(s.updated_at) AS ultima_hora
            FROM solicitudes s
            JOIN clientes c ON c.id = s.cliente_id
            WHERE c.usuario_id = :uid AND s.estado = 'aceptada'
              AND s.updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute([':uid' => $usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ((int)$row['total'] > 0) {
            $notificaciones[] = [
                'tipo'    => 'success',
                'titulo'  => 'Solicitud Aceptada',
                'mensaje' => "{$row['total']} de tus solicitudes fueron aceptadas.",
                'hora'    => calcularTiempoNotif($row['ultima_hora']),
            ];
        }

        // 2. Solicitudes rechazadas recientes
        $stmt = $db->prepare("
            SELECT COUNT(*) AS total, MAX(s.updated_at) AS ultima_hora
            FROM solicitudes s
            JOIN clientes c ON c.id = s.cliente_id
            WHERE c.usuario_id = :uid AND s.estado = 'rechazada'
              AND s.updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute([':uid' => $usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ((int)$row['total'] > 0) {
            $notificaciones[] = [
                'tipo'    => 'error',
                'titulo'  => 'Solicitud Rechazada',
                'mensaje' => "{$row['total']} de tus solicitudes fueron rechazadas recientemente.",
                'hora'    => calcularTiempoNotif($row['ultima_hora']),
            ];
        }

        // 3. Cotizaciones recibidas para necesidades (pendientes de revisar)
        $stmt = $db->prepare("
            SELECT COUNT(*) AS total, MAX(c.created_at) AS ultima_hora
            FROM cotizaciones c
            JOIN clientes cl ON cl.id = c.cliente_id
            WHERE cl.usuario_id = :uid AND c.estado = 'pendiente'
              AND c.necesidad_id IS NOT NULL
        ");
        $stmt->execute([':uid' => $usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ((int)$row['total'] > 0) {
            $notificaciones[] = [
                'tipo'    => 'pago',
                'titulo'  => 'Cotizaciones Recibidas',
                'mensaje' => "Tienes {$row['total']} cotizaciones pendientes de revisar.",
                'hora'    => calcularTiempoNotif($row['ultima_hora']),
            ];
        }

        // 4. Servicios finalizados sin calificar
        $stmt = $db->prepare("
            SELECT COUNT(*) AS total, MAX(sc.modified_at) AS ultima_hora
            FROM servicios_contratados sc
            LEFT JOIN valoraciones v ON v.servicio_contratado_id = sc.id
            JOIN clientes cl ON cl.id = sc.cliente_id
            WHERE cl.usuario_id = :uid AND sc.estado = 'finalizado' AND v.id IS NULL
        ");
        $stmt->execute([':uid' => $usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ((int)$row['total'] > 0) {
            $notificaciones[] = [
                'tipo'    => 'alerta',
                'titulo'  => 'Pendiente de Calificar',
                'mensaje' => "Tienes {$row['total']} servicio(s) finalizado(s) que puedes calificar.",
                'hora'    => calcularTiempoNotif($row['ultima_hora']),
            ];
        }
    } catch (Exception $e) {
        error_log('obtenerNotificacionesCliente error: ' . $e->getMessage());
    }

    return $notificaciones;
}
