<?php
require_once __DIR__ . '/../../config/database.php';

class Contacto {
    private $db;

    public function __construct() {
        $this->db = (new Conexion())->getConexion();
    }

    public function registrarMensaje($datos) {
        try {
            $sql = "INSERT INTO mensajes_contacto (nombre, email, mensaje, fecha_envio) 
                    VALUES (:nombre, :email, :mensaje, NOW())";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':nombre'  => $datos['nombre'],
                ':email'   => $datos['email'],
                ':mensaje' => $datos['mensaje']
            ]);
        } catch (PDOException $e) {
            error_log("Error guardando contacto: " . $e->getMessage());
            return false;
        }
    }
}
?>