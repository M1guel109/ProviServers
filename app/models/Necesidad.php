<?php
require_once __DIR__ . '/../../config/database.php';

class Necesidad
{
    private PDO $db;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->db = $conexion->getConexion();
    }

    private function obtenerClienteIdReal(int $usuarioId): ?int
    {
        $stmt = $this->db->prepare("SELECT id FROM clientes WHERE usuario_id = ? LIMIT 1");
        $stmt->execute([$usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    }

    private function obtenerProveedorIdReal(int $usuarioId): ?int
    {
        $stmt = $this->db->prepare("SELECT id FROM proveedores WHERE usuario_id = ? LIMIT 1");
        $stmt->execute([$usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    }

    /* ============================
       CLIENTE: crear necesidad
    ============================ */
    public function crearPorClienteUsuario(int $usuarioId, array $data): bool
    {
        $clienteId = $this->obtenerClienteIdReal($usuarioId);
        if (!$clienteId) return false;

        $sql = "INSERT INTO necesidades (
                    cliente_id, servicio_id, titulo, descripcion,
                    direccion, ciudad, zona,
                    fecha_preferida, hora_preferida, presupuesto_estimado,
                    estado
                ) VALUES (
                    :cliente_id, :servicio_id, :titulo, :descripcion,
                    :direccion, :ciudad, :zona,
                    :fecha_preferida, :hora_preferida, :presupuesto,
                    'abierta'
                )";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':cliente_id'     => $clienteId,
            ':servicio_id'    => $data['servicio_id'] ?: null,
            ':titulo'         => $data['titulo'],
            ':descripcion'    => $data['descripcion'],
            ':direccion'      => $data['direccion'],
            ':ciudad'         => $data['ciudad'],
            ':zona'           => $data['zona'] ?? null,
            ':fecha_preferida'=> $data['fecha_preferida'],
            ':hora_preferida' => $data['hora_preferida'] ?? null,
            ':presupuesto'    => $data['presupuesto_estimado'] ?? null,
        ]);
    }

    /* ============================
       CLIENTE: listar mis necesidades
    ============================ */
    public function listarPorClienteUsuario(int $usuarioId): array
    {
        $clienteId = $this->obtenerClienteIdReal($usuarioId);
        if (!$clienteId) return [];

        $sql = "
            SELECT
                n.*,
                sv.nombre AS servicio_nombre,
                (SELECT COUNT(*) FROM cotizaciones c WHERE c.necesidad_id = n.id) AS total_ofertas
            FROM necesidades n
            LEFT JOIN servicios sv ON n.servicio_id = sv.id
            WHERE n.cliente_id = :cliente_id
            ORDER BY n.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cliente_id' => $clienteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerPorIdParaCliente(int $usuarioId, int $necesidadId): array
    {
        $clienteId = $this->obtenerClienteIdReal($usuarioId);
        if (!$clienteId) return [];

        $sql = "
            SELECT n.*, sv.nombre AS servicio_nombre
            FROM necesidades n
            LEFT JOIN servicios sv ON n.servicio_id = sv.id
            WHERE n.id = :id AND n.cliente_id = :cliente_id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $necesidadId, ':cliente_id' => $clienteId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function listarCotizacionesDeNecesidadParaCliente(int $usuarioId, int $necesidadId): array
    {
        $clienteId = $this->obtenerClienteIdReal($usuarioId);
        if (!$clienteId) return [];

        $sql = "
            SELECT
                c.*,
                CONCAT(p.nombres, ' ', p.apellidos) AS proveedor_nombre,
                p.ubicacion AS proveedor_ubicacion
            FROM cotizaciones c
            INNER JOIN proveedores p ON c.proveedor_id = p.id
            INNER JOIN necesidades n ON c.necesidad_id = n.id
            WHERE c.necesidad_id = :nid
              AND n.cliente_id = :cliente_id
            ORDER BY c.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nid' => $necesidadId, ':cliente_id' => $clienteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function cancelarNecesidad(int $usuarioId, int $necesidadId): bool
    {
        $clienteId = $this->obtenerClienteIdReal($usuarioId);
        if (!$clienteId) return false;

        $sql = "UPDATE necesidades
                SET estado = 'cancelada', modified_at = NOW()
                WHERE id = :id AND cliente_id = :cliente_id AND estado = 'abierta'
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id'=>$necesidadId, ':cliente_id'=>$clienteId]);
        return $stmt->rowCount() > 0;
    }

    /* ============================
       PROVEEDOR: listar abiertas
    ============================ */
    public function listarAbiertasParaProveedor(int $usuarioIdProveedor): array
    {
        $provId = $this->obtenerProveedorIdReal($usuarioIdProveedor);
        if (!$provId) return [];

        $sql = "
            SELECT
                n.*,
                sv.nombre AS servicio_nombre
            FROM necesidades n
            LEFT JOIN servicios sv ON n.servicio_id = sv.id
            WHERE n.estado = 'abierta'
            ORDER BY n.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerPorIdParaProveedor(int $usuarioIdProveedor, int $necesidadId): array
    {
        $provId = $this->obtenerProveedorIdReal($usuarioIdProveedor);
        if (!$provId) return [];

        $sql = "
            SELECT n.*, sv.nombre AS servicio_nombre
            FROM necesidades n
            LEFT JOIN servicios sv ON n.servicio_id = sv.id
            WHERE n.id = :id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $necesidadId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function yaOferto(int $usuarioIdProveedor, int $necesidadId): bool
    {
        $provId = $this->obtenerProveedorIdReal($usuarioIdProveedor);
        if (!$provId) return false;

        $stmt = $this->db->prepare("SELECT 1 FROM cotizaciones WHERE necesidad_id = ? AND proveedor_id = ? LIMIT 1");
        $stmt->execute([$necesidadId, $provId]);
        return (bool)$stmt->fetchColumn();
    }

    public function crearCotizacionParaNecesidad(int $usuarioIdProveedor, int $necesidadId, array $data): bool
    {
        $provId = $this->obtenerProveedorIdReal($usuarioIdProveedor);
        if (!$provId) return false;

        // validar necesidad abierta + obtener cliente_id
        $stmtN = $this->db->prepare("SELECT id, cliente_id, estado FROM necesidades WHERE id = ? LIMIT 1");
        $stmtN->execute([$necesidadId]);
        $n = $stmtN->fetch(PDO::FETCH_ASSOC);
        if (!$n || $n['estado'] !== 'abierta') return false;

        $sql = "INSERT INTO cotizaciones (
                    cliente_id, proveedor_id, publicacion_id, necesidad_id,
                    titulo, mensaje, precio, tiempo_estimado, estado
                ) VALUES (
                    :cliente_id, :proveedor_id, NULL, :necesidad_id,
                    :titulo, :mensaje, :precio, :tiempo, 'pendiente'
                )";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':cliente_id'   => (int)$n['cliente_id'],
            ':proveedor_id' => $provId,
            ':necesidad_id' => $necesidadId,
            ':titulo'       => $data['titulo'],
            ':mensaje'      => $data['mensaje'] ?? null,
            ':precio'       => $data['precio'] ?? null,
            ':tiempo'       => $data['tiempo_estimado'] ?? null,
        ]);
    }

    /* ============================
       CLIENTE: aceptar una cotización
       => cierra necesidad + crea servicios_contratados
    ============================ */
    public function aceptarCotizacion(int $usuarioIdCliente, int $cotizacionId): bool
    {
        $clienteId = $this->obtenerClienteIdReal($usuarioIdCliente);
        if (!$clienteId) return false;

        $this->db->beginTransaction();
        try {
            // 1) traer cotización + necesidad
            $sql = "
                SELECT c.*, n.servicio_id, n.fecha_preferida, n.id AS necesidad_id
                FROM cotizaciones c
                INNER JOIN necesidades n ON c.necesidad_id = n.id
                WHERE c.id = :cid AND c.cliente_id = :cliente_id
                LIMIT 1
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':cid'=>$cotizacionId, ':cliente_id'=>$clienteId]);
            $c = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$c || empty($c['necesidad_id'])) {
                $this->db->rollBack();
                return false;
            }

            // 2) marcar aceptada esa cotización
            $up1 = $this->db->prepare("UPDATE cotizaciones SET estado='aceptada', modified_at=NOW() WHERE id=? AND cliente_id=? LIMIT 1");
            $up1->execute([$cotizacionId, $clienteId]);
            if ($up1->rowCount() === 0) {
                $this->db->rollBack();
                return false;
            }

            // 3) rechazar las demás de esa necesidad
            $up2 = $this->db->prepare("UPDATE cotizaciones SET estado='rechazada', modified_at=NOW()
                                      WHERE necesidad_id=? AND id<>? AND cliente_id=?");
            $up2->execute([(int)$c['necesidad_id'], $cotizacionId, $clienteId]);

            // 4) cerrar necesidad
            $up3 = $this->db->prepare("UPDATE necesidades SET estado='cerrada', modified_at=NOW()
                                      WHERE id=? AND cliente_id=? LIMIT 1");
            $up3->execute([(int)$c['necesidad_id'], $clienteId]);

            // 5) crear servicios_contratados (contrato)
            $ins = $this->db->prepare("
                INSERT INTO servicios_contratados (
                    solicitud_id, cotizacion_id, cliente_id, proveedor_id, servicio_id,
                    fecha_solicitud, fecha_ejecucion, estado
                ) VALUES (
                    NULL, :cotizacion_id, :cliente_id, :proveedor_id, :servicio_id,
                    :fecha_solicitud, :fecha_ejecucion, 'confirmado'
                )
            ");
            $ins->execute([
                ':cotizacion_id'   => (int)$cotizacionId,
                ':cliente_id'      => $clienteId,
                ':proveedor_id'    => (int)$c['proveedor_id'],
                ':servicio_id'     => (int)$c['servicio_id'],
                ':fecha_solicitud' => $c['fecha_preferida'],
                ':fecha_ejecucion' => $c['fecha_preferida'],
            ]);

            $this->db->commit();
            return true;

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Necesidad::aceptarCotizacion -> ".$e->getMessage());
            return false;
        }
    }
}
