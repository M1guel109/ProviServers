<?php
require_once __DIR__ . '/../../config/database.php';

class Moderacion
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    // Aprobar servicio
    public function aprobar($id)
    {
        try {
            $aprobar = "UPDATE publicaciones 
                    SET estado = 'aprobado', motivo_rechazo = NULL, modified_at = NOW() 
                    WHERE servicio_id = :servicio_id";

            $resultado = $this->conexion->prepare($aprobar);
            $resultado->bindParam(':servicio_id', $id);
            $resultado->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error en Moderacion::aprobar -> " . $e->getMessage());
            return false;
        }
    }

    // Rechazar servicio
    public function rechazar($id, $motivo)
    {
        try {
            $rechazar = "UPDATE publicaciones 
                    SET estado = 'rechazado', motivo_rechazo = :motivo, modified_at = NOW() 
                    WHERE servicio_id = :servicio_id";

            $resultado = $this->conexion->prepare($rechazar);
            $resultado->bindParam(':servicio_id', $id);
            $resultado->bindParam(':motivo', $motivo);
            $resultado->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error en Moderacion::rechazar -> " . $e->getMessage());
            return false;
        }
    }
}
