<?php
require_once __DIR__ . '/../../config/database.php';

class ServicioContratado
{
    private PDO $db;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->db = $conexion->getConexion();
    }

    /**
     * Servicios contratados visibles para el proveedor (panel En Proceso)
     */
    public function listarPorProveedorUsuario(int $usuarioId): array
    {
        $sql = "
            SELECT
                sc.id AS contrato_id,
                sc.estado,
                sc.fecha_solicitud,
                sc.fecha_ejecucion,

                sv.nombre AS servicio_nombre,

                s.titulo AS solicitud_titulo,
                s.ciudad,
                s.zona,

                CONCAT(c.nombres, ' ', c.apellidos) AS cliente_nombre,
                c.telefono AS cliente_telefono,
                c.foto AS cliente_foto

            FROM servicios_contratados sc
            INNER JOIN proveedores pr ON sc.proveedor_id = pr.id
            INNER JOIN clientes c     ON sc.cliente_id = c.id
            INNER JOIN solicitudes s  ON sc.solicitud_id = s.id
            INNER JOIN servicios sv   ON sc.servicio_id = sv.id

            WHERE pr.usuario_id = :usuario_id
              AND sc.estado IN ('pendiente', 'en_proceso')
            ORDER BY sc.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarEstado(int $contratoId, string $nuevoEstado): bool
    {
        $estadosValidos = [
            'pendiente',
            'confirmado',
            'rechazado',
            'expirado',
            'en_proceso',
            'finalizado',
            'cancelado_cliente',
            'cancelado_proveedor'
        ];

        if (!in_array($nuevoEstado, $estadosValidos)) {
            return false;
        }

        $sql = "UPDATE servicios_contratados
                SET estado = :estado,
                    modified_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':estado', $nuevoEstado);
        $stmt->bindParam(':id', $contratoId, PDO::PARAM_INT);

        return $stmt->execute() && $stmt->rowCount() > 0;
    }


    public function contratoPerteneceAProveedor(int $contratoId, int $usuarioId): bool
    {
        $sql = "SELECT 1
                FROM servicios_contratados sc
                INNER JOIN proveedores p ON sc.proveedor_id = p.id
                WHERE sc.id = :contrato_id
                AND p.usuario_id = :usuario_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':contrato_id', $contratoId, PDO::PARAM_INT);
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }



}
