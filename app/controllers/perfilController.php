<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/perfil.php';
require_once __DIR__ . '/../../config/database.php';

function mostrarPerfilAdmin($id)
{

    $objPerfil = new Perfil();
    $usuario = $objPerfil->mostrarPerfilAdmin($id);

    return $usuario;
}

function mostrarPerfilProveedor($id)
{
    try {
        $db = new Conexion();                // Usa tu clase de conexión
        $conexion = $db->getConexion();

        $sql = "SELECT 
                    u.id,
                    u.email,
                    u.rol,
                    p.nombres,
                    p.apellidos,
                    p.telefono,
                    p.ubicacion,
                    p.foto
                FROM usuarios u
                INNER JOIN proveedores p ON u.id = p.usuario_id
                WHERE u.id = :id
                LIMIT 1";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: [
            'nombres' => 'Proveedor',
            'rol'     => 'proveedor',
            'foto'    => 'default_user.png',
        ];
    } catch (PDOException $e) {
        error_log("Error en mostrarPerfilProveedor: " . $e->getMessage());
        return [
            'nombres' => 'Proveedor',
            'rol'     => 'proveedor',
            'foto'    => 'default_user.png',
        ];
    }
}