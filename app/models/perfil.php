<?php
require_once __DIR__ . '/../../config/database.php';


class Perfil
{
    private $conexion;

    public function __construct()
    {
        $db = new  Conexion();
        $this->conexion = $db->getConexion();
    }

    // Esta funcion se duplica por cada rol
    public function mostrarPerfilAdmin($id)
    {
        try {

            $consultar = "SELECT 
                    -- 1. Trae TODAS las columnas de TODAS las tablas unidas
                    u.*, c.*, p.*, a.*, 

                    -- 2. Consolida las columnas clave para fácil acceso en la vista
                    COALESCE(c.nombres, p.nombres, a.nombres) AS nombres,
                    COALESCE(c.apellidos, p.apellidos, a.apellidos) AS apellidos,
                    COALESCE(c.telefono, p.telefono, a.telefono) AS telefono,
                    COALESCE(c.ubicacion, p.ubicacion, a.ubicacion) AS ubicacion,
                    COALESCE(c.foto, p.foto, a.foto) AS foto
                FROM usuarios u  -- ✅ El alias 'u' es crucial aquí
                LEFT JOIN clientes c ON u.id = c.usuario_id AND u.rol = 'cliente'
                LEFT JOIN proveedores p ON u.id = p.usuario_id AND u.rol = 'proveedor'
                LEFT JOIN admins a ON u.id = a.usuario_id AND u.rol = 'admin'
                WHERE u.id = :id
                LIMIT 1
            ";


            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id', $id);
            $resultado->execute();

            return $resultado->fetch();
        } catch (PDOException $e) {
            error_log("Error en Usuario::mostrar->" . $e->getMessage());
            return [];
        }
    }
}
