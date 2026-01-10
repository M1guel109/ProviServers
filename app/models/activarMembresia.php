<?php
require_once __DIR__ . '/../../config/database.php';

class MembresiaActivador
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * Activa la membresía de un proveedor si aún no ha sido iniciada.
     * Se debe llamar al momento del login exitoso.
     * * @param int $usuario_id El ID del usuario que se está logueando.
     * @return bool
     */
    public function activarSiEsNecesario($usuario_id)
    {
        try {
            // 1. Buscamos si el proveedor tiene una membresía 'inactiva' y con fechas NULL
            $sqlCheck = "SELECT pm.id, pm.membresia_id, m.duracion_dias 
                         FROM proveedor_membresia pm
                         JOIN proveedores p ON pm.proveedor_id = p.id
                         JOIN membresias m ON pm.membresia_id = m.id
                         WHERE p.usuario_id = :usuario_id 
                         AND pm.estado = 'inactiva' 
                         AND pm.fecha_inicio IS NULL";

            $stmt = $this->conexion->prepare($sqlCheck);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->execute();

            $membresiaPendiente = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si no hay membresía pendiente de activación, terminamos
            if (!$membresiaPendiente) {
                return false; 
            }

            // 2. Calcular fechas
            $idRegistro = $membresiaPendiente['id'];
            $dias = $membresiaPendiente['duracion_dias'] ?? 30; // Por defecto 30 si no existe
            
            $fechaInicio = date('Y-m-d H:i:s');
            $fechaFin = date('Y-m-d H:i:s', strtotime("+$dias days"));

            // 3. Actualizar la tabla
            $sqlUpdate = "UPDATE proveedor_membresia 
                          SET fecha_inicio = :inicio, 
                              fecha_fin = :fin, 
                              estado = 'activa' 
                          WHERE id = :id";

            $stmtUpdate = $this->conexion->prepare($sqlUpdate);
            return $stmtUpdate->execute([
                ':inicio' => $fechaInicio,
                ':fin'    => $fechaFin,
                ':id'     => $idRegistro
            ]);

        } catch (PDOException $e) {
            error_log("Error en MembresiaActivador::activarSiEsNecesario -> " . $e->getMessage());
            return false;
        }
    }
}