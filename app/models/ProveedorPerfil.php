<?php
// app/models/ProveedorPerfil.php

require_once __DIR__ . '/../../config/database.php';

class ProveedorPerfil
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * Obtener el perfil profesional de un proveedor a partir del id_usuario
     */
    public function obtenerPerfilPorUsuario($idUsuario)
    {
        try {
            $sql = "SELECT * 
                    FROM proveedor_perfil 
                    WHERE id_usuario = :id_usuario 
                    LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            $perfil = $stmt->fetch(PDO::FETCH_ASSOC);
            return $perfil ?: null;
        } catch (PDOException $e) {
            error_log("Error en ProveedorPerfil::obtenerPerfilPorUsuario -> " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crear un nuevo perfil profesional para el proveedor
     */
    public function crearPerfil($idUsuario, array $data)
    {
        try {
            $sql = "INSERT INTO proveedor_perfil 
                    (
                        id_usuario,
                        nombre_comercial,
                        tipo_proveedor,
                        eslogan,
                        descripcion,
                        anios_experiencia,
                        idiomas,
                        categorias,
                        ciudad,
                        zona,
                        foto,
                        telefono_contacto,
                        whatsapp,
                        correo_alternativo,
                        created_at,
                        updated_at
                    )
                    VALUES
                    (
                        :id_usuario,
                        :nombre_comercial,
                        :tipo_proveedor,
                        :eslogan,
                        :descripcion,
                        :anios_experiencia,
                        :idiomas,
                        :categorias,
                        :ciudad,
                        :zona,
                        :foto,
                        :telefono_contacto,
                        :whatsapp,
                        :correo_alternativo,
                        NOW(),
                        NOW()
                    )";

            $stmt = $this->conexion->prepare($sql);
            $this->bindParamsPerfil($stmt, $idUsuario, $data);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ProveedorPerfil::crearPerfil -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar perfil existente del proveedor
     */
    public function actualizarPerfil($idUsuario, array $data)
    {
        try {
            $sql = "UPDATE proveedor_perfil SET
                        nombre_comercial   = :nombre_comercial,
                        tipo_proveedor     = :tipo_proveedor,
                        eslogan            = :eslogan,
                        descripcion        = :descripcion,
                        anios_experiencia  = :anios_experiencia,
                        idiomas            = :idiomas,
                        categorias         = :categorias,
                        ciudad             = :ciudad,
                        zona               = :zona,
                        foto               = :foto,
                        telefono_contacto  = :telefono_contacto,
                        whatsapp           = :whatsapp,
                        correo_alternativo = :correo_alternativo,
                        updated_at         = NOW()
                    WHERE id_usuario = :id_usuario
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $this->bindParamsPerfil($stmt, $idUsuario, $data);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ProveedorPerfil::actualizarPerfil -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper para bindear parámetros comunes
     */
    private function bindParamsPerfil($stmt, $idUsuario, array $data)
    {
        $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);

        $stmt->bindValue(':nombre_comercial',   $data['nombre_comercial']);
        $stmt->bindValue(':tipo_proveedor',     $data['tipo_proveedor']);
        $stmt->bindValue(':eslogan',            $data['eslogan']);
        $stmt->bindValue(':descripcion',        $data['descripcion']);
        $stmt->bindValue(':anios_experiencia',  $data['anios_experiencia']);
        $stmt->bindValue(':idiomas',            $data['idiomas']);       // CSV
        $stmt->bindValue(':categorias',         $data['categorias']);    // CSV
        $stmt->bindValue(':ciudad',             $data['ciudad']);
        $stmt->bindValue(':zona',               $data['zona']);
        $stmt->bindValue(':foto',               $data['foto']);
        $stmt->bindValue(':telefono_contacto',  $data['telefono_contacto']);
        $stmt->bindValue(':whatsapp',           $data['whatsapp']);
        $stmt->bindValue(':correo_alternativo', $data['correo_alternativo']);
    }
}
