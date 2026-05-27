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

function obtenerNotificacionesProveedor(int $usuarioId): array
{
    $conexion = new Conexion();
    $db = $conexion->getConexion();
    $notificaciones = [];

    try {
        // 1. Solicitudes pendientes sin responder
        $stmt = $db->prepare("
            SELECT COUNT(*) AS total, MAX(s.created_at) AS ultima_hora
            FROM solicitudes s
            JOIN proveedores p ON p.id = s.proveedor_id
            WHERE p.usuario_id = :uid AND s.estado = 'pendiente'
        ");
        $stmt->execute([':uid' => $usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ((int)$row['total'] > 0) {
            $notificaciones[] = [
                'tipo'    => 'nuevo_usuario',
                'titulo'  => 'Nuevas Solicitudes',
                'mensaje' => "Tienes {$row['total']} solicitudes pendientes de responder.",
                'hora'    => calcularTiempoNotif($row['ultima_hora']),
            ];
        }

        // 2. Cotizaciones aceptadas (últimos 7 días)
        $stmt = $db->prepare("
            SELECT COUNT(*) AS total, MAX(c.modified_at) AS ultima_hora
            FROM cotizaciones c
            JOIN proveedores p ON p.id = c.proveedor_id
            WHERE p.usuario_id = :uid AND c.estado = 'aceptada'
              AND c.modified_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute([':uid' => $usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ((int)$row['total'] > 0) {
            $notificaciones[] = [
                'tipo'    => 'pago',
                'titulo'  => 'Cotizaciones Aceptadas',
                'mensaje' => "{$row['total']} cotizaciones tuyas fueron aceptadas esta semana.",
                'hora'    => calcularTiempoNotif($row['ultima_hora']),
            ];
        }

        // 3. Servicios activos en proceso
        $stmt = $db->prepare("
            SELECT COUNT(*) AS total
            FROM servicios_contratados sc
            JOIN proveedores p ON p.id = sc.proveedor_id
            WHERE p.usuario_id = :uid AND sc.estado = 'en_proceso'
        ");
        $stmt->execute([':uid' => $usuarioId]);
        $total = (int)$stmt->fetchColumn();
        if ($total > 0) {
            $notificaciones[] = [
                'tipo'    => 'info',
                'titulo'  => 'Servicios Activos',
                'mensaje' => "Tienes $total servicio(s) en proceso ahora mismo.",
                'hora'    => 'En vivo',
            ];
        }

        // 4. Nuevas valoraciones (últimos 7 días)
        $stmt = $db->prepare("
            SELECT COUNT(*) AS total, MAX(v.created_at) AS ultima_hora
            FROM valoraciones v
            JOIN proveedores p ON p.id = v.proveedor_id
            WHERE p.usuario_id = :uid
              AND v.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->execute([':uid' => $usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ((int)$row['total'] > 0) {
            $notificaciones[] = [
                'tipo'    => 'alerta',
                'titulo'  => 'Nuevas Valoraciones',
                'mensaje' => "Recibiste {$row['total']} valoración(es) esta semana.",
                'hora'    => calcularTiempoNotif($row['ultima_hora']),
            ];
        }
    } catch (Exception $e) {
        error_log('obtenerNotificacionesProveedor error: ' . $e->getMessage());
    }

    return $notificaciones;
}
