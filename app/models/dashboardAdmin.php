<?php
require_once __DIR__ . '/../../config/database.php';

class DashboardModel {
    private $conexion;

    public function __construct() {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    // 1. Tarjetas Superiores (Contadores simples)
    public function obtenerConteoUsuarios() {
        try {
            $sql = "SELECT 
                        SUM(CASE WHEN rol = 'cliente' THEN 1 ELSE 0 END) as total_clientes,
                        SUM(CASE WHEN rol = 'proveedor' THEN 1 ELSE 0 END) as total_proveedores,
                        COUNT(*) as total_general
                    FROM usuarios WHERE estado_id = 1"; // Asumiendo 1 = Activo
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['total_clientes' => 0, 'total_proveedores' => 0];
        }
    }

    // 2. Gráfica Principal (Servicios publicados vs Contratados por mes)
    public function obtenerMetricasServicios($anio) {
        try {
            // Esta consulta agrupa por mes (1 a 12)
            $sql = "SELECT 
                        MONTH(created_at) as mes,
                        COUNT(*) as total
                    FROM servicios 
                    WHERE YEAR(created_at) = :anio
                    GROUP BY MONTH(created_at)";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':anio', $anio);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // 3. Servicio Destacado (El que más se repite o más ventas tiene)
    public function obtenerServicioTop() {
        try {
            // Ejemplo: Traer el último servicio creado o el más visto
            $sql = "SELECT nombre, imagen FROM servicios ORDER BY id DESC LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
}