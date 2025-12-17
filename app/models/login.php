<?php
require_once __DIR__. '/../../config/database.php';

class Login {
    private $conexion;

    public function __construct(){
        $db = new  Conexion();
        $this->conexion = $db->getConexion();

    }
    public function autenticar ($correo, $clave ){
        try{
            $consultar = "SELECT u.*, e.nombre AS estado_nombre 
                          FROM usuarios u 
                          INNER JOIN usuario_estados e ON u.estado_id = e.id 
                          WHERE u.email = :correo LIMIT 1";

            $resultado= $this->conexion->prepare ($consultar);
            $resultado->bindParam(':correo' , $correo);
            $resultado->execute();

            $user = $resultado->fetch();

            if (!$user) {
                return ['error' => 'Usuario no encontrado o inactivo'];
            }

            //verificar contraseña encriptada 
            if (!password_verify($clave , $user['clave'])){
                return['error' => 'Contraseña incorrecta'];
            }

            //Retornar los datos del ususario autenticado 
            return[
                'id' => $user ['id'],
                'rol' => $user ['rol'],
                'email' => $user ['email'],
                'estado' => $user['estado_nombre']
            ];
            
        }catch(PDOException $e){
            error_log("Error en el modelo Login:" . $e->getMessage());
            return['error'=>'error interno del servidor'];
        }

    }

}
?>