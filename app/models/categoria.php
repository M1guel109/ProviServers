<?php
require_once __DIR__ . '/../../config/database.php';

class Categoria
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    // 1. Validar nombre duplicado
    public function existeNombre($nombre, $id_excluir = null)
    {
        $sql = "SELECT COUNT(*) FROM categorias WHERE nombre = :nombre";
        if ($id_excluir) {
            $sql .= " AND id != :id";
        }
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        if ($id_excluir) {
            $stmt->bindParam(':id', $id_excluir);
        }
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }

    // 2. Verificar si tiene servicios asociados (PARA SEGURIDAD AL BORRAR)
    public function tieneServicios($id)
    {
        // Asegúrate que tu tabla se llame 'servicios' y la FK sea 'id_categoria'
        $sql = "SELECT COUNT(*) FROM servicios WHERE id_categoria = :id";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // 3. Obtener el nombre de la imagen (PARA BORRAR EL ARCHIVO)
    public function obtenerImagen($id)
    {
        $sql = "SELECT icono_url FROM categorias WHERE id = :id LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? $res['icono_url'] : null;
    }

    // CRUD BÁSICO
    public function registrar($data)
    {
        try {
            $sql = "INSERT INTO categorias (nombre, descripcion, icono_url) VALUES (:nombre, :descripcion, :icono_url)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':icono_url', $data['icono_url']);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error Categoria::registrar: " . $e->getMessage());
            return false;
        }
    }

    public function mostrar()
    {
        try {
            $sql = "SELECT * FROM categorias ORDER BY nombre ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function mostrarId($id)
    {
        try {
            $sql = "SELECT * FROM categorias WHERE id = :id LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizar($data)
    {
        try {
            $sql = "UPDATE categorias SET nombre = :nombre, descripcion = :descripcion, icono_url = :icono_url WHERE id = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $data['id']);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':icono_url', $data['icono_url']);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error Categoria::actualizar: " . $e->getMessage());
            return false;
        }
    }

    public function eliminar($id)
    {
        try {
            $sql = "DELETE FROM categorias WHERE id = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error Categoria::eliminar: " . $e->getMessage());
            return false;
        }
    }
}
?>