<?php
require_once __DIR__ . '/../../config/database.php';

class Finanza
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * 1. Suma total de dinero recibido (Solo pagos 'pagado')
     */
    public function obtenerIngresosTotales()
    {
        try {
            $sql = "SELECT SUM(monto) as total FROM pagos WHERE estado_pago = 'pagado'";
            $stmt = $this->conexion->query($sql);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * 2. Cantidad de membresías activas actualmente
     */
    public function contarMembresiasActivas()
    {
        try {
            // Tu tabla usa el estado 'activa' en minúsculas
            $sql = "SELECT COUNT(*) as total FROM proveedor_membresia WHERE estado = 'activa'";
            $stmt = $this->conexion->query($sql);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * 3. Cantidad de pagos pendientes de verificar
     */
    public function contarPagosPendientes()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM pagos WHERE estado_pago = 'pendiente'";
            $stmt = $this->conexion->query($sql);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * 4. Tabla: Últimos pagos registrados (Con nombres de usuario y plan)
     */
    public function obtenerUltimosPagos($limite = 5)
    {
        try {
            // Hacemos JOIN con proveedores y membresias para mostrar nombres, no IDs
            $sql = "SELECT p.id, p.monto, p.estado_pago, p.created_at,
                           CONCAT(prov.nombres, ' ', prov.apellidos) as proveedor,
                           m.tipo as plan
                    FROM pagos p
                    INNER JOIN proveedores prov ON p.proveedor_id = prov.id
                    -- Relacionamos el pago con la membresía a través de la tabla intermedia o directa
                    -- Asumimos que p.proveedor_membresia_id conecta con proveedor_membresia
                    INNER JOIN proveedor_membresia pm ON p.proveedor_membresia_id = pm.id
                    INNER JOIN membresias m ON pm.membresia_id = m.id
                    ORDER BY p.created_at DESC 
                    LIMIT :limite";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * 5. Alerta: Membresías que vencen en los próximos 15 días
     */
    public function obtenerMembresiasPorVencer()
    {
        try {
            $sql = "SELECT pm.fecha_fin, 
                           CONCAT(prov.nombres, ' ', prov.apellidos) as proveedor,
                           m.tipo as plan,
                           DATEDIFF(pm.fecha_fin, CURDATE()) as dias_restantes
                    FROM proveedor_membresia pm
                    INNER JOIN proveedores prov ON pm.proveedor_id = prov.id
                    INNER JOIN membresias m ON pm.membresia_id = m.id
                    WHERE pm.estado = 'activa' 
                    AND pm.fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)
                    ORDER BY pm.fecha_fin ASC";
            
            $stmt = $this->conexion->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * 6. Gráfico Líneas: Ingresos por mes (Últimos 6 meses)
     */
    public function obtenerIngresosPorMes()
    {
        try {
            // Agrupa por Año-Mes (Ej: 2025-11)
            $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as mes, SUM(monto) as total
                    FROM pagos 
                    WHERE estado_pago = 'pagado'
                    GROUP BY mes 
                    ORDER BY mes ASC 
                    LIMIT 6";
            $stmt = $this->conexion->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * 7. Gráfico Dona: Distribución de planes activos
     */
    public function obtenerDistribucionPlanes()
    {
        try {
            $sql = "SELECT m.tipo, COUNT(pm.id) as cantidad
                    FROM proveedor_membresia pm
                    INNER JOIN membresias m ON pm.membresia_id = m.id
                    WHERE pm.estado = 'activa'
                    GROUP BY m.tipo";
            $stmt = $this->conexion->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerReporteIngresos(): array
    {
        $stGlobal = $this->conexion->query("
            SELECT
                COUNT(*)                                                              AS total_pagos,
                COALESCE(SUM(CASE WHEN estado_pago = 'pagado'    THEN monto END), 0) AS confirmado,
                COALESCE(SUM(CASE WHEN estado_pago = 'pendiente' THEN monto END), 0) AS pendiente,
                COUNT(CASE WHEN estado_pago = 'pagado'    THEN 1 END)                AS pagos_confirmados,
                COUNT(CASE WHEN estado_pago = 'pendiente' THEN 1 END)                AS pagos_pendientes
            FROM pagos
        ");
        $global = $stGlobal->fetch(PDO::FETCH_ASSOC) ?: [
            'total_pagos' => 0, 'confirmado' => 0, 'pendiente' => 0,
            'pagos_confirmados' => 0, 'pagos_pendientes' => 0,
        ];

        $stMes = $this->conexion->query("
            SELECT
                DATE_FORMAT(fecha_pago, '%Y-%m') AS periodo,
                COUNT(*)                          AS pagos,
                COALESCE(SUM(monto), 0)           AS total
            FROM pagos
            WHERE estado_pago = 'pagado'
            GROUP BY periodo
            ORDER BY periodo DESC
            LIMIT 12
        ");
        $porMes = $stMes->fetchAll(PDO::FETCH_ASSOC);

        $stPlan = $this->conexion->query("
            SELECT
                m.tipo                  AS plan,
                COUNT(p.id)             AS ventas,
                COALESCE(SUM(p.monto), 0) AS total
            FROM pagos p
            INNER JOIN proveedor_membresia pm ON p.proveedor_membresia_id = pm.id
            INNER JOIN membresias m           ON pm.membresia_id          = m.id
            WHERE p.estado_pago = 'pagado'
            GROUP BY m.id, m.tipo
            ORDER BY total DESC
        ");
        $porPlan = $stPlan->fetchAll(PDO::FETCH_ASSOC);

        $stRecientes = $this->conexion->query("
            SELECT
                p.fecha_pago,
                p.monto,
                p.estado_pago,
                p.metodo_pago,
                CONCAT(prov.nombres, ' ', prov.apellidos) AS proveedor,
                m.tipo                                    AS plan
            FROM pagos p
            INNER JOIN proveedores prov        ON p.proveedor_id          = prov.id
            INNER JOIN proveedor_membresia pm  ON p.proveedor_membresia_id = pm.id
            INNER JOIN membresias m            ON pm.membresia_id          = m.id
            ORDER BY p.created_at DESC
            LIMIT 50
        ");
        $recientes = $stRecientes->fetchAll(PDO::FETCH_ASSOC);

        return [
            'global'    => $global,
            'porMes'    => $porMes,
            'porPlan'   => $porPlan,
            'recientes' => $recientes,
        ];
    }
}
?>