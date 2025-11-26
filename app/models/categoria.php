<?php
require_once __DIR__ . '/../../config/database.php';

class Categoria
{
    private $conexion;

    public function __construct()
    {
        $db = new  Conexion();
        $this->conexion = $db->getConexion();
    }

    public function registrar($data)
    {
        try {
            // 🔹 INICIAR TRANSACCIÓN
            $this->conexion->beginTransaction();

            // 2️⃣ Preparar INSERT
            $registrar = "INSERT INTO categorias (nombre, descripcion, icono_url) 
                VALUES (:nombre, :descripcion, :icono_url)";

            $resultado = $this->conexion->prepare($registrar);

            $resultado->bindParam(':nombre', $data['nombre']);
            $resultado->bindParam(':descripcion', $data['descripcion']);
            $resultado->bindParam(':icono_url', $data['icono_url']);

            $resultado->execute();

            // 3️⃣ Confirmar inserción y terminar transacción
            $this->conexion->commit();

            return true;
        } catch (PDOException $e) {
            error_log("Error en Categoria::registrar -> " . $e->getMessage());
            return false;
        }
    }

    public function mostrar()
    {
        try {
            $consultar = "SELECT * FROM categorias ORDER BY nombre ASC";

            $resultado = $this->conexion->prepare($consultar);
            $resultado->execute();

            return $resultado->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Categoria::mostrar->" . $e->getMessage());
            return [];
        }
    }

    public function eliminar($id)
    {

        try {

            // 1. Sentencia SQL para eliminar la fila
            $eliminar = "DELETE FROM categorias WHERE id = :id";

            // 2. Preparar y ejecutar
            $resultado = $this->conexion->prepare($eliminar);
            $resultado->bindParam(':id', $id);


            return $resultado->execute();
        } catch (PDOException $e) {
            // Manejo de error si la categoría no se puede eliminar (ej. por una clave foránea activa)
            error_log("Error en Categoria::eliminar -> " . $e->getMessage());
            return false;
        }
    }

    public function mostrarId($id)
    {

        try {
            $consultar = "SELECT * FROM categorias WHERE id = :id LIMIT 1";

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id', $id);
            $resultado->execute();

            return $resultado->fetch();
        } catch (PDOException $e) {
            error_log("Error en Categoria::mostrarId -> " . $e->getMessage());
            return false;
        }
    }
}
