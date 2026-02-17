<?php
require_once __DIR__ . '/../../config/database.php';

class Suscripcion
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * Obtiene todas las suscripciones uniendo tablas para traer nombres reales.
     * Adaptado a tu tabla 'proveedor_membresia'
     */
    public function listarTodas()
    {
        try {
            // JOIN CORREGIDO:
            // pm = proveedor_membresia
            // p  = proveedores (para el nombre)
            // u  = usuarios (para el email)
            // m  = membresias (para el nombre del plan y costo)

            $sql = "SELECT pm.id, 
                           pm.fecha_inicio, 
                           pm.fecha_fin, 
                           pm.estado, 
                           pm.created_at,
                           CONCAT(p.nombres, ' ', p.apellidos) as nombre_proveedor,
                           u.email,
                           p.foto as foto_proveedor,
                           m.tipo as nombre_plan,
                           m.costo
                    FROM proveedor_membresia pm
                    INNER JOIN proveedores p ON pm.proveedor_id = p.id
                    INNER JOIN usuarios u ON p.usuario_id = u.id
                    INNER JOIN membresias m ON pm.membresia_id = m.id
                    ORDER BY pm.fecha_fin ASC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error Suscripcion::listarTodas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cancelar una suscripción (Cambiar estado a 'cancelada' o 'inactiva')
     */
    public function cancelar($id)
    {
        try {
            // Tu enum permite 'cancelada' o 'inactiva'. Usaremos 'cancelada'.
            $sql = "UPDATE proveedor_membresia 
                    SET estado = 'cancelada', 
                        fecha_fin = CURDATE() 
                    WHERE id = :id";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error Suscripcion::cancelar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar físicamente
     */
    public function eliminar($id)
    {
        try {
            $sql = "DELETE FROM proveedor_membresia WHERE id = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // ... código anterior ...

    /**
     * Obtener una suscripción específica por ID con todos los datos relacionados
     * (Necesario para el Modal de Detalle)
     */
    public function obtenerPorId($id)
    {
        try {
            $sql = "SELECT pm.id, 
                           pm.fecha_inicio, 
                           pm.fecha_fin, 
                           pm.estado, 
                           CONCAT(p.nombres, ' ', p.apellidos) as nombre_proveedor,
                           u.email,
                           p.foto as foto_proveedor,
                           p.telefono,
                           p.ubicacion,
                           m.tipo as nombre_plan,
                           m.costo,
                           m.descripcion as descripcion_plan
                    FROM proveedor_membresia pm
                    INNER JOIN proveedores p ON pm.proveedor_id = p.id
                    INNER JOIN usuarios u ON p.usuario_id = u.id
                    INNER JOIN membresias m ON pm.membresia_id = m.id
                    WHERE pm.id = :id LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ... resto de la clase ...
}
