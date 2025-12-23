<?php
// app/models/servicio.php

require_once __DIR__ . '/../../config/database.php';

class Servicio
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * Registrar un nuevo servicio
     * Espera en $data:
     *  - nombre
     *  - descripcion
     *  - id_categoria
     *  - imagen
     *  - disponibilidad
     */
    public function registrar($data)
    {
        try {
            $sql = "INSERT INTO servicios 
                        (nombre, descripcion, id_categoria, imagen, disponibilidad)
                    VALUES
                        (:nombre, :descripcion, :id_categoria, :imagen, :disponibilidad)";

            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(':nombre',        $data['nombre']);
            $stmt->bindParam(':descripcion',   $data['descripcion']);
            $stmt->bindParam(':id_categoria',  $data['id_categoria'], PDO::PARAM_INT);
            $stmt->bindParam(':imagen',        $data['imagen']);
            $stmt->bindParam(':disponibilidad', $data['disponibilidad'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Servicio::registrar -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los servicios
     */
    public function mostrar()
    {
        try {
            $sql = "SELECT 
                    s.*,
                    c.nombre AS categoria_nombre,
                    CONCAT(p.nombres, ' ', p.apellidos) AS proveedor_nombre,
                    pub.estado AS publicacion_estado
                FROM servicios s
                INNER JOIN categorias c ON c.id = s.id_categoria
                LEFT JOIN publicaciones pub ON pub.servicio_id = s.id
                LEFT JOIN proveedores p ON p.id = pub.proveedor_id
                ORDER BY s.created_at DESC"
            ;

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Servicio::mostrar -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener un servicio por ID
     */
    public function mostrarId($id)
    {
        try {
            $sql = "SELECT * FROM servicios WHERE id = :id LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en Servicio::mostrarId -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar un servicio
     * Espera en $data:
     *  - id
     *  - nombre
     *  - descripcion
     *  - id_categoria
     *  - disponibilidad
     * (la imagen la puedes manejar en otra función si luego permites cambiarla)
     */
    public function actualizar($data)
    {
        try {
            $sql = "UPDATE servicios SET
                        nombre        = :nombre,
                        descripcion   = :descripcion,
                        id_categoria  = :id_categoria,
                        disponibilidad= :disponibilidad,
                        modified_at   = NOW()
                    WHERE id = :id";

            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(':id',            $data['id'], PDO::PARAM_INT);
            $stmt->bindParam(':nombre',        $data['nombre']);
            $stmt->bindParam(':descripcion',   $data['descripcion']);
            $stmt->bindParam(':id_categoria',  $data['id_categoria'], PDO::PARAM_INT);
            $stmt->bindParam(':disponibilidad', $data['disponibilidad'], PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Servicio::actualizar -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un servicio por ID
     */
public function eliminar($id)
{
    try {
        // Iniciamos transacción por seguridad
        $this->conexion->beginTransaction();

        // 1. Eliminar publicaciones asociadas a este servicio
        $sqlPublicaciones = "DELETE FROM publicaciones WHERE servicio_id = :id";
        $stmtPub = $this->conexion->prepare($sqlPublicaciones);
        $stmtPub->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtPub->execute();

        // 2. Eliminar el servicio
        $sqlServicio = "DELETE FROM servicios WHERE id = :id";
        $stmtServ = $this->conexion->prepare($sqlServicio);
        $stmtServ->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtServ->execute();

        // 3. Confirmamos
        $this->conexion->commit();
        return true;

    } catch (PDOException $e) {
        // Revertimos todo si algo falla
        $this->conexion->rollBack();
        error_log("Error en Servicio::eliminar -> " . $e->getMessage());
        return false;
    }
}

    public function getUltimoIdInsertado(): int
    {
        return (int) $this->conexion->lastInsertId();
    }
}
