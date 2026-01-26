<?php
require_once __DIR__ . '/../../config/database.php';

class Valoracion
{
    private PDO $db;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->db = $conexion->getConexion();
    }

    /**
     * Inserta una valoración SOLO si:
     * - el contrato pertenece al cliente (clientes.usuario_id)
     * - el contrato está finalizado
     * - no existe valoración previa para ese contrato/cliente
     */
    public function crearPorClienteUsuario(int $contratoId, int $usuarioId, int $calificacion, ?string $comentario): bool
    {
        if ($calificacion < 1 || $calificacion > 5) {
            return false;
        }

        $comentario = $comentario !== null ? trim($comentario) : null;
        if ($comentario === '') $comentario = null;

        $sql = "
            INSERT INTO valoraciones (servicio_contratado_id, cliente_id, proveedor_id, calificacion, comentario, created_at)
            SELECT
              sc.id,
              sc.cliente_id,
              sc.proveedor_id,
              :calificacion,
              :comentario,
              NOW()
            FROM servicios_contratados sc
            INNER JOIN clientes c ON sc.cliente_id = c.id
            LEFT JOIN valoraciones v
              ON v.servicio_contratado_id = sc.id
             AND v.cliente_id = sc.cliente_id
            WHERE sc.id = :contrato_id
              AND c.usuario_id = :usuario_id
              AND sc.estado = 'finalizado'
              AND v.id IS NULL
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':calificacion' => $calificacion,
            ':comentario'   => $comentario,
            ':contrato_id'  => $contratoId,
            ':usuario_id'   => $usuarioId,
        ]);

        return $stmt->rowCount() > 0;
    }
}
