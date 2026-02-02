<?php
require_once __DIR__ . '/../../config/database.php';

class Necesidad
{
    private PDO $db;

    public function __construct()
    {
        $cn = new Conexion();
        $this->db = $cn->getConexion();
    }

    /* ============================
       Helpers de mapeo de IDs
    ============================ */
    private function obtenerClienteIdPorUsuario(int $usuarioId): ?int
    {
        $stmt = $this->db->prepare("SELECT id FROM clientes WHERE usuario_id = ? LIMIT 1");
        $stmt->execute([$usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    }

    private function obtenerProveedorIdPorUsuario(int $usuarioId): ?int
    {
        $stmt = $this->db->prepare("SELECT id FROM proveedores WHERE usuario_id = ? LIMIT 1");
        $stmt->execute([$usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    }

    /* ============================
       CLIENTE: crear necesidad
    ============================ */
    public function crearParaClienteUsuario(int $usuarioId, array $data): bool
    {
        $clienteId = $this->obtenerClienteIdPorUsuario($usuarioId);
        if (!$clienteId) {
            error_log("Necesidad::crearParaClienteUsuario -> No existe cliente para usuario_id={$usuarioId}");
            return false;
        }

        $sql = "INSERT INTO necesidades (
                    cliente_id, servicio_id, titulo, descripcion, direccion, ciudad, zona,
                    fecha_preferida, hora_preferida, presupuesto_estimado, estado
                ) VALUES (
                    :cliente_id, :servicio_id, :titulo, :descripcion, :direccion, :ciudad, :zona,
                    :fecha_preferida, :hora_preferida, :presupuesto_estimado, 'abierta'
                )";

        try {
            $stmt = $this->db->prepare($sql);

            $ok = $stmt->execute([
                ':cliente_id'           => $clienteId,
                ':servicio_id'          => $data['servicio_id'] ?? null,
                ':titulo'               => $data['titulo'],
                ':descripcion'          => $data['descripcion'],
                ':direccion'            => $data['direccion'],
                ':ciudad'               => $data['ciudad'],
                ':zona'                 => $data['zona'] ?? null,
                ':fecha_preferida'      => $data['fecha_preferida'],
                ':hora_preferida'       => $data['hora_preferida'] ?? null,
                ':presupuesto_estimado' => $data['presupuesto_estimado'] ?? null,
            ]);

            if (!$ok) {
                $err = $stmt->errorInfo();
                error_log("Necesidad::crearParaClienteUsuario -> execute false: " . print_r($err, true));
            }

            return $ok;

        } catch (Throwable $e) {
            error_log("Necesidad::crearParaClienteUsuario -> " . $e->getMessage());
            return false;
        }
    }
    /* ============================
       CLIENTE: listado mis necesidades
    ============================ */
    public function listarPorClienteUsuario(int $usuarioId, ?string $estado = null): array
    {
        $clienteId = $this->obtenerClienteIdPorUsuario($usuarioId);
        if (!$clienteId) return [];

        $where = " WHERE n.cliente_id = :cliente_id ";
        $params = [':cliente_id' => $clienteId];

        if (!empty($estado)) {
            $where .= " AND n.estado = :estado ";
            $params[':estado'] = $estado;
        }

        $sql = "
            SELECT
                n.*,
                COUNT(cot.id) AS total_ofertas
            FROM necesidades n
            LEFT JOIN cotizaciones cot ON cot.necesidad_id = n.id
            $where
            GROUP BY n.id
            ORDER BY n.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerDetallePorClienteUsuario(int $usuarioId, int $necesidadId): ?array
    {
        $clienteId = $this->obtenerClienteIdPorUsuario($usuarioId);
        if (!$clienteId) return null;

        $stmt = $this->db->prepare("
            SELECT * FROM necesidades
            WHERE id = :id AND cliente_id = :cliente_id
            LIMIT 1
        ");
        $stmt->execute([
            ':id' => $necesidadId,
            ':cliente_id' => $clienteId
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function listarCotizacionesDeNecesidadParaCliente(int $usuarioId, int $necesidadId): array
    {
        $clienteId = $this->obtenerClienteIdPorUsuario($usuarioId);
        if (!$clienteId) return [];

        // Validar pertenencia
        $stmt = $this->db->prepare("SELECT id FROM necesidades WHERE id=? AND cliente_id=? LIMIT 1");
        $stmt->execute([$necesidadId, $clienteId]);
        if (!$stmt->fetch()) return [];

        $sql = "
            SELECT
                cot.*,
                CONCAT(pr.nombres, ' ', pr.apellidos) AS proveedor_nombre
            FROM cotizaciones cot
            INNER JOIN proveedores pr ON cot.proveedor_id = pr.id
            WHERE cot.necesidad_id = :nid
            ORDER BY cot.created_at DESC
        ";
        $stmt2 = $this->db->prepare($sql);
        $stmt2->execute([':nid' => $necesidadId]);

        return $stmt2->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /* ============================
       CLIENTE: aceptar cotización
    ============================ */
    public function aceptarCotizacion(int $usuarioId, int $cotizacionId): bool
    {
        $clienteId = $this->obtenerClienteIdPorUsuario($usuarioId);
        if (!$clienteId) return false;

        try {
            $this->db->beginTransaction();

            // Traer cotización + validar que la necesidad sea del cliente y esté abierta
            $sql = "
                SELECT cot.id, cot.necesidad_id, cot.estado, n.estado AS estado_necesidad, n.cliente_id
                FROM cotizaciones cot
                INNER JOIN necesidades n ON cot.necesidad_id = n.id
                WHERE cot.id = :cid
                  AND n.cliente_id = :cliente_id
                LIMIT 1
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':cid' => $cotizacionId,
                ':cliente_id' => $clienteId
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                $this->db->rollBack();
                return false;
            }

            if ($row['estado_necesidad'] !== 'abierta') {
                $this->db->rollBack();
                return false;
            }

            if ($row['estado'] !== 'pendiente') {
                $this->db->rollBack();
                return false;
            }

            $necesidadId = (int)$row['necesidad_id'];

            // 1) Aceptar la elegida
            $up1 = $this->db->prepare("UPDATE cotizaciones SET estado='aceptada', modified_at=NOW() WHERE id=:cid LIMIT 1");
            $up1->execute([':cid' => $cotizacionId]);

            // 2) Rechazar las demás pendientes
            $up2 = $this->db->prepare("
                UPDATE cotizaciones
                SET estado='rechazada', modified_at=NOW()
                WHERE necesidad_id=:nid AND id<>:cid AND estado='pendiente'
            ");
            $up2->execute([':nid' => $necesidadId, ':cid' => $cotizacionId]);

            // 3) Cerrar necesidad
            $up3 = $this->db->prepare("UPDATE necesidades SET estado='cerrada', modified_at=NOW() WHERE id=:nid LIMIT 1");
            $up3->execute([':nid' => $necesidadId]);

            $this->db->commit();
            return true;

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Necesidad::aceptarCotizacion -> " . $e->getMessage());
            return false;
        }
    }

    /* ============================
       PROVEEDOR: ver necesidades abiertas
    ============================ */
    public function listarAbiertasParaProveedorUsuario(int $usuarioId): array
    {
        $proveedorId = $this->obtenerProveedorIdPorUsuario($usuarioId);
        if (!$proveedorId) return [];

        $sql = "
            SELECT
                n.*,
                CASE WHEN cot.id IS NULL THEN 0 ELSE 1 END AS ya_cotizo
            FROM necesidades n
            LEFT JOIN cotizaciones cot
              ON cot.necesidad_id = n.id
             AND cot.proveedor_id = :pid
            WHERE n.estado = 'abierta'
            ORDER BY n.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':pid' => $proveedorId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /* ============================
       PROVEEDOR: crear cotización
    ============================ */
    public function crearCotizacionParaNecesidad(int $usuarioProveedorId, int $necesidadId, array $data): bool
    {
        $proveedorId = $this->obtenerProveedorIdPorUsuario($usuarioProveedorId);
        if (!$proveedorId) return false;

        try {
            $this->db->beginTransaction();

            // 1) obtener necesidad (cliente_id) y validar abierta
            $stmt = $this->db->prepare("SELECT id, cliente_id, estado FROM necesidades WHERE id=? LIMIT 1");
            $stmt->execute([$necesidadId]);
            $n = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$n || $n['estado'] !== 'abierta') {
                $this->db->rollBack();
                return false;
            }

            // 2) evitar doble cotización del mismo proveedor
            $stmt2 = $this->db->prepare("SELECT id FROM cotizaciones WHERE necesidad_id=? AND proveedor_id=? LIMIT 1");
            $stmt2->execute([$necesidadId, $proveedorId]);
            if ($stmt2->fetch()) {
                $this->db->rollBack();
                return false;
            }

            // 3) insertar cotización (nota: publicacion_id puede ser NULL)
            $sql = "
                INSERT INTO cotizaciones (
                    cliente_id, proveedor_id, publicacion_id, necesidad_id,
                    titulo, mensaje, precio, tiempo_estimado, estado
                ) VALUES (
                    :cliente_id, :proveedor_id, NULL, :necesidad_id,
                    :titulo, :mensaje, :precio, :tiempo_estimado, 'pendiente'
                )
            ";
            $ins = $this->db->prepare($sql);
            $ok = $ins->execute([
                ':cliente_id' => (int)$n['cliente_id'],
                ':proveedor_id' => $proveedorId,
                ':necesidad_id' => $necesidadId,
                ':titulo' => $data['titulo'],
                ':mensaje' => $data['mensaje'] ?? null,
                ':precio' => $data['precio'],
                ':tiempo_estimado' => $data['tiempo_estimado'] ?? null,
            ]);

            $this->db->commit();
            return $ok;

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Necesidad::crearCotizacionParaNecesidad -> " . $e->getMessage());
            return false;
        }
    }
}
