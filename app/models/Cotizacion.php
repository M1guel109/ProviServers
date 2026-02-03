<?php
require_once __DIR__ . '/../../config/database.php';

class Cotizacion
{
    private PDO $db;

    public function __construct()
    {
        $cn = new Conexion();
        $this->db = $cn->getConexion();
    }

    private function obtenerProveedorIdPorUsuario(int $usuarioId): ?int
    {
        $stmt = $this->db->prepare("SELECT id FROM proveedores WHERE usuario_id = ? LIMIT 1");
        $stmt->execute([$usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    }

    private function obtenerClienteIdPorUsuario(int $usuarioId): ?int
    {
        $stmt = $this->db->prepare("SELECT id FROM clientes WHERE usuario_id = ? LIMIT 1");
        $stmt->execute([$usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    }

    /**
     * PROVEEDOR: crear cotización para una necesidad
     * Inserta en cotizaciones:
     * cliente_id, proveedor_id, publicacion_id(NULL), necesidad_id, titulo, mensaje, precio, tiempo_estimado, estado('pendiente')
     */
    public function crearParaNecesidadPorProveedorUsuario(int $usuarioProveedorId, int $necesidadId, array $data): bool
    {
        $proveedorId = $this->obtenerProveedorIdPorUsuario($usuarioProveedorId);
        if (!$proveedorId) {
            error_log("Cotizacion::crearParaNecesidad -> proveedor no existe para usuario_id={$usuarioProveedorId}");
            return false;
        }

        try {
            $this->db->beginTransaction();

            // 1) Validar necesidad abierta y obtener cliente_id
            $stmtN = $this->db->prepare("SELECT id, cliente_id, estado FROM necesidades WHERE id = ? LIMIT 1");
            $stmtN->execute([$necesidadId]);
            $n = $stmtN->fetch(PDO::FETCH_ASSOC);

            if (!$n) {
                $this->db->rollBack();
                return false;
            }

            if (($n['estado'] ?? '') !== 'abierta') {
                $this->db->rollBack();
                return false;
            }

            $clienteId = (int)$n['cliente_id'];

            // 2) Evitar doble cotización del mismo proveedor a la misma necesidad
            $stmtD = $this->db->prepare("SELECT id FROM cotizaciones WHERE necesidad_id = ? AND proveedor_id = ? LIMIT 1");
            $stmtD->execute([$necesidadId, $proveedorId]);
            if ($stmtD->fetch()) {
                $this->db->rollBack();
                return false;
            }

            // 3) Insertar cotización
            $sql = "INSERT INTO cotizaciones (
                        cliente_id, proveedor_id, publicacion_id, necesidad_id,
                        titulo, mensaje, precio, tiempo_estimado, estado
                    ) VALUES (
                        :cliente_id, :proveedor_id, NULL, :necesidad_id,
                        :titulo, :mensaje, :precio, :tiempo_estimado, 'pendiente'
                    )";

            $ins = $this->db->prepare($sql);
            $ok = $ins->execute([
                ':cliente_id'      => $clienteId,
                ':proveedor_id'    => $proveedorId,
                ':necesidad_id'    => $necesidadId,
                ':titulo'          => $data['titulo'],
                ':mensaje'         => $data['mensaje'] ?? null,
                ':precio'          => $data['precio'] ?? null,
                ':tiempo_estimado' => $data['tiempo_estimado'] ?? null,
            ]);

            $this->db->commit();
            return $ok;

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Cotizacion::crearParaNecesidad -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * CLIENTE: listar cotizaciones de una necesidad (validando que sea del cliente)
     */
    public function listarPorNecesidadParaClienteUsuario(int $usuarioClienteId, int $necesidadId): array
    {
        $clienteId = $this->obtenerClienteIdPorUsuario($usuarioClienteId);
        if (!$clienteId) return [];

        // Validar pertenencia de la necesidad
        $stmt = $this->db->prepare("SELECT id FROM necesidades WHERE id=? AND cliente_id=? LIMIT 1");
        $stmt->execute([$necesidadId, $clienteId]);
        if (!$stmt->fetch()) return [];

        $sql = "
            SELECT
                c.*,
                CONCAT(p.nombres, ' ', p.apellidos) AS proveedor_nombre
            FROM cotizaciones c
            LEFT JOIN proveedores p ON c.proveedor_id = p.id
            WHERE c.necesidad_id = :nid
            ORDER BY c.created_at DESC
        ";
        $st = $this->db->prepare($sql);
        $st->execute([':nid' => $necesidadId]);

        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * CLIENTE: aceptar una cotización (cierra la necesidad y rechaza las demás)
     */
/**
     * CLIENTE: Aceptar cotización (Versión Final)
     */
    public function aceptarCotizacionParaClienteUsuario(int $usuarioClienteId, int $cotizacionId): bool
    {
        $clienteId = $this->obtenerClienteIdPorUsuario($usuarioClienteId);
        if (!$clienteId) return false;

        try {
            $this->db->beginTransaction();

            // 1. Traer datos clave (incluyendo servicio_id para el contrato)
            $sql = "
                SELECT 
                    c.id, c.necesidad_id, c.estado, c.proveedor_id,
                    n.estado AS estado_necesidad, n.cliente_id, n.servicio_id
                FROM cotizaciones c
                INNER JOIN necesidades n ON c.necesidad_id = n.id
                WHERE c.id = :cid AND n.cliente_id = :cliente_id
                LIMIT 1
            ";
            $st = $this->db->prepare($sql);
            $st->execute([':cid' => $cotizacionId, ':cliente_id' => $clienteId]);
            $row = $st->fetch(PDO::FETCH_ASSOC);

            // Validaciones de negocio
            if (!$row) { 
                $this->db->rollBack(); return false; 
            }
            if ($row['estado_necesidad'] !== 'abierta') { 
                $this->db->rollBack(); return false; 
            }
            if ($row['estado'] !== 'pendiente') { 
                $this->db->rollBack(); return false; 
            }

            // Datos para las actualizaciones
            $necesidadId = (int)$row['necesidad_id'];
            $proveedorId = (int)$row['proveedor_id'];
            $servicioId  = !empty($row['servicio_id']) ? (int)$row['servicio_id'] : null;

            // 2. Actualizar estados (Aceptar cotización, rechazar las demás, cerrar necesidad)
            $this->db->prepare("UPDATE cotizaciones SET estado='aceptada', modified_at=NOW() WHERE id=?")->execute([$cotizacionId]);
            $this->db->prepare("UPDATE cotizaciones SET estado='rechazada', modified_at=NOW() WHERE necesidad_id=? AND id<>? AND estado='pendiente'")->execute([$necesidadId, $cotizacionId]);
            $this->db->prepare("UPDATE necesidades SET estado='cerrada', modified_at=NOW() WHERE id=?")->execute([$necesidadId]);

            // 3. Crear el contrato (Puente para la ejecución del servicio)
            $sqlContrato = "
                INSERT INTO servicios_contratados (
                    cotizacion_id, cliente_id, proveedor_id, servicio_id, fecha_solicitud, estado, created_at
                ) VALUES (
                    ?, ?, ?, ?, CURDATE(), 'pendiente', NOW()
                )
            ";
            
            $ins = $this->db->prepare($sqlContrato);
            $exitoInsert = $ins->execute([$cotizacionId, $clienteId, $proveedorId, $servicioId]);

            if (!$exitoInsert) {
                // Si falla el insert, revertimos todo
                $this->db->rollBack();
                error_log("Error al insertar servicio contratado: " . print_r($ins->errorInfo(), true));
                return false;
            }

            $this->db->commit();
            return true;

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Excepción en aceptarCotizacionParaClienteUsuario: " . $e->getMessage());
            return false;
        }
    }
}
