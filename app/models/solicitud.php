<?php
require_once __DIR__ . '/../../config/database.php';

class Solicitud
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * Crear una nueva cotización (solicitud)
     */
    public function crear(array $data): bool
    {
        try {
            $sql = "INSERT INTO solicitudes (
                    cliente_id,
                    proveedor_id,
                    publicacion_id,
                    titulo,
                    descripcion,
                    direccion,
                    ciudad,
                    zona,
                    fecha_preferida,
                    franja_horaria,
                    presupuesto_estimado,
                    estado
                ) VALUES (
                    :cliente_id,
                    :proveedor_id,
                    :publicacion_id,
                    :titulo,
                    :descripcion,
                    :direccion,
                    :ciudad,
                    :zona,
                    :fecha_preferida,
                    :franja_horaria,
                    :presupuesto_estimado,
                    'pendiente'
                )";

            $stmt = $this->conexion->prepare($sql);

            $stmt->execute([
                ':cliente_id'     => $data['cliente_id'],
                ':proveedor_id'   => $data['proveedor_id'],
                ':publicacion_id' => $data['publicacion_id'],
                ':titulo'         => $data['titulo'],
                ':descripcion'    => $data['descripcion'],
                ':direccion'      => $data['direccion'],
                ':ciudad'         => $data['ciudad'],
                ':zona'           => $data['zona'],
                ':fecha_preferida' => $data['fecha_preferida'],
                ':franja_horaria' => $data['franja_horaria'],
                ':presupuesto_estimado'    => $data['presupuesto_estimado']
            ]);

            return true;
        } catch (PDOException $e) {
            error_log("Error en Solicitud::crear -> " . $e->getMessage());
            return false;
        }
    }

    public function tieneSolicitudActiva($clienteId, $publicacionId): bool
    {
        $sql = "SELECT COUNT(*) 
            FROM solicitudes
            WHERE cliente_id = ?
              AND publicacion_id = ?
              AND estado IN ('pendiente', 'aceptada')";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$clienteId, $publicacionId]);

        return $stmt->fetchColumn() > 0;
    }
}
