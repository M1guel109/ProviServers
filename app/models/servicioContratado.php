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
     * Servicios contratados visibles para el PROVEEDOR
     * (panel "En proceso" del proveedor)
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

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * 🔹 NUEVO: Servicios contratados visibles para el CLIENTE
     * (vista “Servicios contratados” del panel cliente)
     *
     * Recibe el id de la tabla usuarios (el que guardas en $_SESSION['user']['id'])
     */
 public function listarPorClienteUsuario(int $usuarioId): array
    {
        $sql = "
            SELECT
                sc.id              AS contrato_id,
                sc.estado,
                sc.fecha_solicitud,
                sc.fecha_ejecucion,

                s.titulo           AS solicitud_titulo,
                s.descripcion      AS solicitud_descripcion,
                s.fecha_preferida,
                s.franja_horaria,
                s.ciudad,
                s.zona,
                s.presupuesto_estimado,

                sv.nombre          AS servicio_nombre,
                sv.imagen          AS servicio_imagen,

                CONCAT(pr.nombres, ' ', pr.apellidos) AS proveedor_nombre

            FROM servicios_contratados sc
            INNER JOIN clientes c     ON sc.cliente_id   = c.id
            INNER JOIN usuarios u     ON c.usuario_id    = u.id
            INNER JOIN solicitudes s  ON sc.solicitud_id = s.id
            INNER JOIN servicios sv   ON sc.servicio_id  = sv.id
            INNER JOIN proveedores pr ON sc.proveedor_id = pr.id

            WHERE u.id = :usuario_id
            ORDER BY sc.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar estado del contrato (para proveedor, o luego cliente)
     */
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

        if (!in_array($nuevoEstado, $estadosValidos, true)) {
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

    /**
     * Verificar si un contrato pertenece al PROVEEDOR logueado
     */
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

    /**
     * 🔹 (Opcional, pero útil) Verificar si un contrato pertenece al CLIENTE logueado
     * para cuando quieras permitir que el cliente vea detalle o cancele.
     */
    public function contratoPerteneceACliente(int $contratoId, int $usuarioId): bool
    {
        $sql = "SELECT 1
                FROM servicios_contratados sc
                INNER JOIN clientes c ON sc.cliente_id = c.id
                WHERE sc.id = :contrato_id
                  AND c.usuario_id = :usuario_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':contrato_id', $contratoId, PDO::PARAM_INT);
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    public function cancelarPorClienteUsuario(int $contratoId, int $usuarioId): bool
{
    $sql = "
        UPDATE servicios_contratados sc
        INNER JOIN clientes c ON sc.cliente_id = c.id
        SET sc.estado = 'cancelado_cliente',
            sc.modified_at = NOW()
        WHERE sc.id = :contrato_id
          AND c.usuario_id = :usuario_id
          AND sc.estado IN ('pendiente','confirmado')
        LIMIT 1
    ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        ':contrato_id' => $contratoId,
        ':usuario_id'  => $usuarioId
    ]);

    return $stmt->rowCount() > 0;
}

}
