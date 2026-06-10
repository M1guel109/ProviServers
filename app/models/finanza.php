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
        $tasa = 0.10;

        $stGlobal = $this->conexion->query("
            SELECT
                COUNT(*)                                             AS total_transacciones,
                COALESCE(SUM(monto), 0)                             AS bruto,
                COUNT(CASE WHEN liberado = 1 THEN 1 END)            AS liberados,
                COALESCE(SUM(CASE WHEN liberado = 1 THEN monto END), 0) AS bruto_liberado
            FROM pagos_servicios
            WHERE mp_status = 'approved'
        ");
        $global = $stGlobal->fetch(PDO::FETCH_ASSOC) ?: [
            'total_transacciones' => 0, 'bruto' => 0, 'liberados' => 0, 'bruto_liberado' => 0,
        ];
        $global['comision']          = round($global['bruto']          * $tasa, 2);
        $global['neto']              = round($global['bruto']          - $global['comision'], 2);
        $global['comision_liberado'] = round($global['bruto_liberado'] * $tasa, 2);
        $global['neto_liberado']     = round($global['bruto_liberado'] - $global['comision_liberado'], 2);

        $stMes = $this->conexion->query("
            SELECT
                DATE_FORMAT(created_at, '%Y-%m')  AS periodo,
                COUNT(*)                           AS transacciones,
                COALESCE(SUM(monto), 0)            AS bruto
            FROM pagos_servicios
            WHERE mp_status = 'approved'
            GROUP BY periodo
            ORDER BY periodo DESC
            LIMIT 12
        ");
        $porMes = $stMes->fetchAll(PDO::FETCH_ASSOC);
        foreach ($porMes as &$mes) {
            $mes['comision'] = round($mes['bruto'] * $tasa, 2);
            $mes['neto']     = round($mes['bruto'] - $mes['comision'], 2);
        }
        unset($mes);

        $stProv = $this->conexion->query("
            SELECT
                CONCAT(p.nombres, ' ', p.apellidos) AS proveedor,
                COUNT(ps.id)                         AS transacciones,
                COALESCE(SUM(ps.monto), 0)           AS bruto
            FROM pagos_servicios ps
            INNER JOIN proveedores p ON ps.proveedor_id = p.id
            WHERE ps.mp_status = 'approved'
            GROUP BY p.id, p.nombres, p.apellidos
            ORDER BY bruto DESC
            LIMIT 10
        ");
        $porProveedor = $stProv->fetchAll(PDO::FETCH_ASSOC);
        foreach ($porProveedor as &$prov) {
            $prov['comision'] = round($prov['bruto'] * $tasa, 2);
            $prov['neto']     = round($prov['bruto'] - $prov['comision'], 2);
        }
        unset($prov);

        $stRecientes = $this->conexion->query("
            SELECT
                ps.created_at,
                ps.monto                             AS bruto,
                ps.mp_status,
                ps.liberado,
                CONCAT(p.nombres, ' ', p.apellidos)  AS proveedor,
                CONCAT(c.nombres, ' ', c.apellidos)  AS cliente,
                sv.nombre                            AS servicio
            FROM pagos_servicios ps
            INNER JOIN proveedores p  ON ps.proveedor_id          = p.id
            INNER JOIN clientes c     ON ps.cliente_id            = c.id
            INNER JOIN servicios_contratados sc ON ps.servicio_contratado_id = sc.id
            INNER JOIN servicios sv   ON sc.servicio_id           = sv.id
            ORDER BY ps.created_at DESC
            LIMIT 50
        ");
        $recientes = $stRecientes->fetchAll(PDO::FETCH_ASSOC);
        foreach ($recientes as &$rec) {
            $rec['comision'] = round($rec['bruto'] * $tasa, 2);
            $rec['neto']     = round($rec['bruto'] - $rec['comision'], 2);
        }
        unset($rec);

        return [
            'global'        => $global,
            'porMes'        => $porMes,
            'porProveedor'  => $porProveedor,
            'recientes'     => $recientes,
            'tasa_comision' => (int)($tasa * 100),
        ];
    }
}
?>