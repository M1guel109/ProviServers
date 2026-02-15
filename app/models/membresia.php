<?php
require_once __DIR__ . '/../../config/database.php';

class Membresia
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * Registra una nueva membresía en la BD
     */
    public function registrar($data)
    {
        try {
            $sql = "INSERT INTO membresias (
                        tipo, 
                        descripcion, 
                        costo, 
                        duracion_dias, 
                        estado, 
                        es_destacado, 
                        orden_visual, 
                        max_servicios_activos, 
                        acceso_estadisticas_pro, 
                        permite_videos,
                        created_at
                    ) VALUES (
                        :tipo, 
                        :desc, 
                        :costo, 
                        :dias, 
                        :estado, 
                        :destacado, 
                        :orden, 
                        :max_serv, 
                        :stats, 
                        :videos,
                        NOW()
                    )";

            $stmt = $this->conexion->prepare($sql);

            // Vinculación de parámetros
            $stmt->bindParam(':tipo',      $data['tipo']);
            $stmt->bindParam(':desc',      $data['descripcion']);
            $stmt->bindParam(':costo',     $data['costo']);
            $stmt->bindParam(':dias',      $data['duracion_dias']);
            $stmt->bindParam(':estado',    $data['estado']); // 'ACTIVO' o 'INACTIVO'
            $stmt->bindParam(':destacado', $data['es_destacado'], PDO::PARAM_INT);
            $stmt->bindParam(':max_serv',  $data['max_servicios_activos'], PDO::PARAM_INT);
            $stmt->bindParam(':stats',     $data['acceso_estadisticas_pro'], PDO::PARAM_INT);
            $stmt->bindParam(':videos',    $data['permite_videos'], PDO::PARAM_INT);

            // Manejo especial para NULL en orden_visual
            if ($data['orden_visual'] === null) {
                $stmt->bindValue(':orden', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':orden', $data['orden_visual'], PDO::PARAM_INT);
            }

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Error Membresia::registrar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listar todas las membresías
     */
    public function mostrar()
    {
        try {
            $sql = "SELECT * FROM membresias ORDER BY orden_visual ASC, created_at DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error Membresia::mostrar: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener una membresía por ID
     */
    public function mostrarId($id)
    {
        try {
            $sql = "SELECT * FROM membresias WHERE id = :id LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error Membresia::mostrarId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar una membresía existente
     */
    public function actualizar($data)
    {
        try {
            $sql = "UPDATE membresias SET 
                        tipo = :tipo,
                        descripcion = :desc,
                        costo = :costo,
                        duracion_dias = :dias,
                        estado = :estado,
                        es_destacado = :destacado,
                        orden_visual = :orden,
                        max_servicios_activos = :max_serv,
                        acceso_estadisticas_pro = :stats,
                        permite_videos = :videos,
                        modified_at = NOW()
                    WHERE id = :id";

            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(':id',        $data['id'], PDO::PARAM_INT);
            $stmt->bindParam(':tipo',      $data['tipo']);
            $stmt->bindParam(':desc',      $data['descripcion']);
            $stmt->bindParam(':costo',     $data['costo']);
            $stmt->bindParam(':dias',      $data['duracion_dias']);
            $stmt->bindParam(':estado',    $data['estado']);
            $stmt->bindParam(':destacado', $data['es_destacado'], PDO::PARAM_INT);
            $stmt->bindParam(':max_serv',  $data['max_servicios_activos'], PDO::PARAM_INT);
            $stmt->bindParam(':stats',     $data['acceso_estadisticas_pro'], PDO::PARAM_INT);
            $stmt->bindParam(':videos',    $data['permite_videos'], PDO::PARAM_INT);

            if ($data['orden_visual'] === null) {
                $stmt->bindValue(':orden', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':orden', $data['orden_visual'], PDO::PARAM_INT);
            }

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Error Membresia::actualizar: " . $e->getMessage());
            return false;
        }
    }

    // Verificar si la membresía está asignada a algún proveedor
    public function tieneProveedores($id)
    {
        // Ajusta 'proveedor_membresia' si tu tabla intermedia se llama diferente
        $sql = "SELECT COUNT(*) FROM proveedor_membresia WHERE membresia_id = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Eliminar membresía
     */
    public function eliminar($id)
    {
        try {
            $sql = "DELETE FROM membresias WHERE id = :id";
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error Membresia::eliminar: " . $e->getMessage());
            return false;
        }
    }
}
?>