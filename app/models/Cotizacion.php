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
            error_log("Cotizacion::crearParaNecesidadPorProveedorUsuario -> proveedor no existe para usuario_id={$usuarioProveedorId}");
            return false;
        }

        $publicacionId = (int)($data['publicacion_id'] ?? 0);

        if ($publicacionId <= 0) {
            error_log("Cotizacion::crearParaNecesidadPorProveedorUsuario -> publicacion_id inválido");
            return false;
        }

        try {
            $this->db->beginTransaction();

            // 1) Validar necesidad abierta y obtener cliente_id
            $stmtN = $this->db->prepare("
            SELECT id, cliente_id, estado
            FROM necesidades
            WHERE id = ?
            LIMIT 1
        ");
            $stmtN->execute([$necesidadId]);
            $n = $stmtN->fetch(PDO::FETCH_ASSOC);

            if (!$n) {
                $this->db->rollBack();
                error_log("Cotizacion::crearParaNecesidadPorProveedorUsuario -> necesidad no encontrada: {$necesidadId}");
                return false;
            }

            if (($n['estado'] ?? '') !== 'abierta') {
                $this->db->rollBack();
                error_log("Cotizacion::crearParaNecesidadPorProveedorUsuario -> necesidad no abierta: {$necesidadId}");
                return false;
            }

            $clienteId = (int)$n['cliente_id'];

            // 2) Validar que la publicación pertenezca al proveedor y esté aprobada
            $stmtP = $this->db->prepare("
            SELECT id, proveedor_id, servicio_id, estado
            FROM publicaciones
            WHERE id = ?
              AND proveedor_id = ?
            LIMIT 1
        ");
            $stmtP->execute([$publicacionId, $proveedorId]);
            $p = $stmtP->fetch(PDO::FETCH_ASSOC);

            if (!$p) {
                $this->db->rollBack();
                error_log("Cotizacion::crearParaNecesidadPorProveedorUsuario -> publicación no pertenece al proveedor. publicacion_id={$publicacionId}, proveedor_id={$proveedorId}");
                return false;
            }

            if (($p['estado'] ?? '') !== 'aprobado') {
                $this->db->rollBack();
                error_log("Cotizacion::crearParaNecesidadPorProveedorUsuario -> publicación no aprobada. publicacion_id={$publicacionId}");
                return false;
            }

            if (empty($p['servicio_id'])) {
                $this->db->rollBack();
                error_log("Cotizacion::crearParaNecesidadPorProveedorUsuario -> publicación sin servicio_id. publicacion_id={$publicacionId}");
                return false;
            }

            // 3) Evitar doble cotización del mismo proveedor a la misma necesidad
            $stmtD = $this->db->prepare("
            SELECT id
            FROM cotizaciones
            WHERE necesidad_id = ?
              AND proveedor_id = ?
            LIMIT 1
        ");
            $stmtD->execute([$necesidadId, $proveedorId]);

            if ($stmtD->fetch()) {
                $this->db->rollBack();
                error_log("Cotizacion::crearParaNecesidadPorProveedorUsuario -> ya existe cotización para necesidad_id={$necesidadId} y proveedor_id={$proveedorId}");
                return false;
            }

            // 4) Insertar cotización con publicacion_id
            $sql = "
            INSERT INTO cotizaciones (
                cliente_id,
                proveedor_id,
                publicacion_id,
                necesidad_id,
                titulo,
                mensaje,
                precio,
                tiempo_estimado,
                estado
            ) VALUES (
                :cliente_id,
                :proveedor_id,
                :publicacion_id,
                :necesidad_id,
                :titulo,
                :mensaje,
                :precio,
                :tiempo_estimado,
                'pendiente'
            )
        ";

            $ins = $this->db->prepare($sql);
            $ok = $ins->execute([
                ':cliente_id'      => $clienteId,
                ':proveedor_id'    => $proveedorId,
                ':publicacion_id'  => $publicacionId,
                ':necesidad_id'    => $necesidadId,
                ':titulo'          => $data['titulo'],
                ':mensaje'         => $data['mensaje'] ?? null,
                ':precio'          => $data['precio'] ?? null,
                ':tiempo_estimado' => $data['tiempo_estimado'] ?? null,
            ]);

            if (!$ok) {
                $this->db->rollBack();
                error_log("Cotizacion::crearParaNecesidadPorProveedorUsuario -> error insertando cotización: " . print_r($ins->errorInfo(), true));
                return false;
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Cotizacion::crearParaNecesidadPorProveedorUsuario -> " . $e->getMessage());
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
    if (!$clienteId) {
        error_log("Cotizacion::aceptarCotizacionParaClienteUsuario -> no existe cliente para usuario_id={$usuarioClienteId}");
        return false;
    }

    try {
        $this->db->beginTransaction();

        // 1) Traer la cotización, la necesidad y la publicación asociada
        $sql = "
            SELECT 
                c.id,
                c.necesidad_id,
                c.estado,
                c.proveedor_id,
                c.publicacion_id,
                n.cliente_id,
                n.estado AS estado_necesidad,
                p.servicio_id,
                p.proveedor_id AS proveedor_publicacion
            FROM cotizaciones c
            INNER JOIN necesidades n ON c.necesidad_id = n.id
            INNER JOIN publicaciones p ON c.publicacion_id = p.id
            WHERE c.id = :cid
              AND n.cliente_id = :cliente_id
            LIMIT 1
        ";

        $st = $this->db->prepare($sql);
        $st->execute([
            ':cid' => $cotizacionId,
            ':cliente_id' => $clienteId
        ]);

        $row = $st->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $this->db->rollBack();
            error_log("Cotizacion::aceptarCotizacionParaClienteUsuario -> cotización no encontrada o sin publicación válida");
            return false;
        }

        if (($row['estado_necesidad'] ?? '') !== 'abierta') {
            $this->db->rollBack();
            error_log("Cotizacion::aceptarCotizacionParaClienteUsuario -> la necesidad no está abierta");
            return false;
        }

        if (($row['estado'] ?? '') !== 'pendiente') {
            $this->db->rollBack();
            error_log("Cotizacion::aceptarCotizacionParaClienteUsuario -> la cotización no está pendiente");
            return false;
        }

        if (empty($row['publicacion_id']) || empty($row['servicio_id'])) {
            $this->db->rollBack();
            error_log("Cotizacion::aceptarCotizacionParaClienteUsuario -> falta publicacion_id o servicio_id");
            return false;
        }

        // Seguridad adicional: la publicación debe pertenecer al mismo proveedor que cotiza
        if ((int)$row['proveedor_id'] !== (int)$row['proveedor_publicacion']) {
            $this->db->rollBack();
            error_log("Cotizacion::aceptarCotizacionParaClienteUsuario -> proveedor de cotización no coincide con proveedor de publicación");
            return false;
        }

        $necesidadId = (int)$row['necesidad_id'];
        $proveedorId = (int)$row['proveedor_id'];
        $servicioId  = (int)$row['servicio_id'];

        // 2) Verificar que no exista ya contrato para esta cotización
        $chk = $this->db->prepare("
            SELECT id
            FROM servicios_contratados
            WHERE cotizacion_id = ?
            LIMIT 1
        ");
        $chk->execute([$cotizacionId]);

        if ($chk->fetch()) {
            $this->db->rollBack();
            error_log("Cotizacion::aceptarCotizacionParaClienteUsuario -> ya existe servicio contratado para cotizacion_id={$cotizacionId}");
            return false;
        }

        // 3) Aceptar cotización
        $up1 = $this->db->prepare("
            UPDATE cotizaciones
            SET estado = 'aceptada', modified_at = NOW()
            WHERE id = ?
        ");
        if (!$up1->execute([$cotizacionId])) {
            $this->db->rollBack();
            error_log("Error aceptando cotización: " . print_r($up1->errorInfo(), true));
            return false;
        }

        // 4) Rechazar las demás cotizaciones pendientes de la misma necesidad
        $up2 = $this->db->prepare("
            UPDATE cotizaciones
            SET estado = 'rechazada', modified_at = NOW()
            WHERE necesidad_id = ?
              AND id <> ?
              AND estado = 'pendiente'
        ");
        if (!$up2->execute([$necesidadId, $cotizacionId])) {
            $this->db->rollBack();
            error_log("Error rechazando otras cotizaciones: " . print_r($up2->errorInfo(), true));
            return false;
        }

        // 5) Cerrar necesidad
        $up3 = $this->db->prepare("
            UPDATE necesidades
            SET estado = 'cerrada', modified_at = NOW()
            WHERE id = ?
        ");
        if (!$up3->execute([$necesidadId])) {
            $this->db->rollBack();
            error_log("Error cerrando necesidad: " . print_r($up3->errorInfo(), true));
            return false;
        }

        // 6) Insertar en servicios_contratados con el servicio_id tomado desde la publicación
        $sqlContrato = "
            INSERT INTO servicios_contratados (
                solicitud_id,
                cotizacion_id,
                cliente_id,
                proveedor_id,
                servicio_id,
                fecha_solicitud,
                estado,
                created_at
            ) VALUES (
                NULL,
                ?,
                ?,
                ?,
                ?,
                CURDATE(),
                'pendiente',
                NOW()
            )
        ";

        $ins = $this->db->prepare($sqlContrato);
        $okIns = $ins->execute([
            $cotizacionId,
            $clienteId,
            $proveedorId,
            $servicioId
        ]);

        if (!$okIns) {
            $this->db->rollBack();
            error_log("Error insertando servicio contratado: " . print_r($ins->errorInfo(), true));
            return false;
        }

        $this->db->commit();
        return true;

    } catch (Throwable $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        error_log("Excepción en aceptarCotizacionParaClienteUsuario: " . $e->getMessage());
        return false;
    }
}
public function obtenerPublicacionesAprobadasPorProveedorUsuario(int $usuarioProveedorId): array
{
    $proveedorId = $this->obtenerProveedorIdPorUsuario($usuarioProveedorId);

    if (!$proveedorId) {
        error_log("Cotizacion::obtenerPublicacionesAprobadasPorProveedorUsuario -> no existe proveedor para usuario_id={$usuarioProveedorId}");
        return [];
    }

    try {
        $sql = "
            SELECT 
                p.id,
                p.titulo,
                p.servicio_id,
                s.nombre AS servicio_nombre
            FROM publicaciones p
            INNER JOIN servicios s ON s.id = p.servicio_id
            WHERE p.proveedor_id = :proveedor_id
              AND p.estado = 'aprobado'
            ORDER BY p.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':proveedor_id' => $proveedorId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    } catch (Throwable $e) {
        error_log("Cotizacion::obtenerPublicacionesAprobadasPorProveedorUsuario -> " . $e->getMessage());
        return [];
    }
}
}
