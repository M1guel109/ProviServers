<?php
// =====================================================
// plan-helper.php — Control de acceso por membresía
// =====================================================

require_once BASE_PATH . '/config/database.php';

/**
 * Retorna el plan activo del proveedor con todos los límites.
 * Si no tiene plan activo devuelve los valores del plan Básico.
 */
function obtenerPlanActivoProveedor(int $uid): array
{
    static $cache = [];
    if (isset($cache[$uid])) return $cache[$uid];

    $default = [
        'nombre'                => 'Básico',
        'costo'                 => 0,
        'dias_restantes'        => null,
        'fecha_fin'             => null,
        'max_servicios_activos' => 3,
        'acceso_estadisticas_pro' => 0,
        'permite_videos'        => 0,
        'es_destacado'          => 0,
        'plan_gratuito'         => true,
    ];

    try {
        $db  = new Conexion();
        $pdo = $db->getConexion();

        $st = $pdo->prepare("
            SELECT m.tipo AS nombre, m.costo, m.max_servicios_activos,
                   m.acceso_estadisticas_pro, m.permite_videos, m.es_destacado,
                   pm.fecha_fin,
                   GREATEST(0, DATEDIFF(pm.fecha_fin, CURDATE())) AS dias_restantes
            FROM proveedor_membresia pm
            INNER JOIN proveedores p ON pm.proveedor_id = p.id
            INNER JOIN membresias m  ON pm.membresia_id = m.id
            WHERE p.usuario_id = :uid AND pm.estado = 'activa'
            ORDER BY pm.fecha_inicio DESC
            LIMIT 1
        ");
        $st->execute([':uid' => $uid]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $row['plan_gratuito'] = ((float)$row['costo'] === 0.0);
            $row['dias_restantes'] = (int)$row['dias_restantes'];
            $cache[$uid] = $row;
            return $row;
        }
    } catch (PDOException $e) {
        error_log('plan-helper::obtenerPlanActivoProveedor: ' . $e->getMessage());
    }

    $cache[$uid] = $default;
    return $default;
}

/**
 * Cuántas publicaciones activas tiene el proveedor ahora mismo.
 */
function contarPublicacionesActivasProveedor(int $uid): int
{
    try {
        $db  = new Conexion();
        $pdo = $db->getConexion();
        $st  = $pdo->prepare("
            SELECT COUNT(*)
            FROM publicaciones pub
            INNER JOIN proveedores p ON pub.proveedor_id = p.id
            WHERE p.usuario_id = :uid AND pub.estado = 'activo'
        ");
        $st->execute([':uid' => $uid]);
        return (int)$st->fetchColumn();
    } catch (PDOException $e) {
        error_log('plan-helper::contarPublicacionesActivasProveedor: ' . $e->getMessage());
        return 0;
    }
}

/**
 * ¿Puede el proveedor publicar un servicio más?
 */
function proveedorPuedePublicar(int $uid): bool
{
    $plan   = obtenerPlanActivoProveedor($uid);
    $limite = (int)($plan['max_servicios_activos'] ?? 3);
    $actual = contarPublicacionesActivasProveedor($uid);
    return $actual < $limite;
}

/**
 * ¿Tiene el proveedor acceso a estadísticas pro?
 */
function proveedorTieneEstadisticasPro(int $uid): bool
{
    return (bool)obtenerPlanActivoProveedor($uid)['acceso_estadisticas_pro'];
}

/**
 * Retorna true si el plan está por vencer (≤ 7 días) o ya venció.
 */
function planProximoAVencer(int $uid): bool
{
    $plan = obtenerPlanActivoProveedor($uid);
    if ($plan['plan_gratuito'] || $plan['fecha_fin'] === null) return false;
    return (int)$plan['dias_restantes'] <= 7;
}
