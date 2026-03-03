<?php
// app/models/Mensajeria.php

require_once __DIR__ . '/../../config/database.php';

class Mensajeria
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    // ======================================================================
    // 1. GESTIÓN DE CONVERSACIONES (INBOX Y ACCESOS)
    // ======================================================================

    public function obtenerConversacionPorId(int $convId): ?array
    {
        $sql = "SELECT * FROM conversaciones WHERE id = :id LIMIT 1";
        $st = $this->conexion->prepare($sql);
        $st->execute([':id' => $convId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function usuarioTieneAcceso(int $convId, int $usuarioId): bool
    {
        $sql = "SELECT 1 
                FROM conversaciones 
                WHERE id = :id AND (:u = cliente_usuario_id OR :u = proveedor_usuario_id) 
                LIMIT 1";
        $st = $this->conexion->prepare($sql);
        $st->execute([':id' => $convId, ':u' => $usuarioId]);
        return (bool)$st->fetchColumn();
    }

    private function assertAcceso(int $convId, int $usuarioId): void
    {
        if (!$this->usuarioTieneAcceso($convId, $usuarioId)) {
            throw new Exception("Acceso denegado");
        }
    }

    public function obtenerOtroUsuarioId(array $conv, int $usuarioId): int
    {
        return ($conv['cliente_usuario_id'] == $usuarioId)
            ? (int)$conv['proveedor_usuario_id']
            : (int)$conv['cliente_usuario_id'];
    }

    public function obtenerTema(int $convId): string
    {
        $sql = "SELECT COALESCE(s.titulo, co.titulo) AS tema
                FROM conversaciones conv
                LEFT JOIN solicitudes s ON conv.tipo='solicitud' AND s.id = conv.referencia_id
                LEFT JOIN cotizaciones co ON conv.tipo='cotizacion' AND co.id = conv.referencia_id
                WHERE conv.id = :id
                LIMIT 1";
        $st = $this->conexion->prepare($sql);
        $st->execute([':id' => $convId]);
        $tema = $st->fetchColumn();
        return $tema ? (string)$tema : "Conversación";
    }

    /**
     * Crea o devuelve conversación ligada a una SOLICITUD.
     */
    public function getOrCreateFromSolicitud(int $solicitudId, int $currentUserId): int
    {
        // 1) buscar si existe
        $st = $this->conexion->prepare("SELECT id FROM conversaciones WHERE tipo='solicitud' AND referencia_id=:rid LIMIT 1");
        $st->execute([':rid' => $solicitudId]);
        $ex = $st->fetchColumn();
        
        if ($ex) {
            $this->assertAcceso((int)$ex, $currentUserId);
            return (int)$ex;
        }

        // 2) obtener participantes (usuario_id)
        $sql = "SELECT 
                    cl.usuario_id AS cliente_usuario_id,
                    pr.usuario_id AS proveedor_usuario_id
                FROM solicitudes s
                INNER JOIN clientes cl   ON cl.id = s.cliente_id
                INNER JOIN proveedores pr ON pr.id = s.proveedor_id
                WHERE s.id = :sid
                LIMIT 1";
        $st = $this->conexion->prepare($sql);
        $st->execute([':sid' => $solicitudId]);
        $p = $st->fetch(PDO::FETCH_ASSOC);
        
        if (!$p) throw new Exception("Solicitud no encontrada");

        $cuid = (int)$p['cliente_usuario_id'];
        $puid = (int)$p['proveedor_usuario_id'];
        
        if ($currentUserId !== $cuid && $currentUserId !== $puid) {
            throw new Exception("Acceso denegado");
        }

        // 3) insertar
        try {
            $ins = $this->conexion->prepare(
                "INSERT INTO conversaciones (tipo, referencia_id, cliente_usuario_id, proveedor_usuario_id)
                 VALUES ('solicitud', :rid, :cuid, :puid)"
            );
            $ins->execute([':rid' => $solicitudId, ':cuid' => $cuid, ':puid' => $puid]);
            return (int)$this->conexion->lastInsertId();
        } catch (PDOException $e) {
            $st = $this->conexion->prepare("SELECT id FROM conversaciones WHERE tipo='solicitud' AND referencia_id=:rid LIMIT 1");
            $st->execute([':rid' => $solicitudId]);
            $id = (int)$st->fetchColumn();
            $this->assertAcceso($id, $currentUserId);
            return $id;
        }
    }

    /**
     * Crea o devuelve conversación ligada a una COTIZACIÓN.
     */
    public function getOrCreateFromCotizacion(int $cotizacionId, int $currentUserId): int
    {
        $st = $this->conexion->prepare("SELECT id FROM conversaciones WHERE tipo='cotizacion' AND referencia_id=:rid LIMIT 1");
        $st->execute([':rid' => $cotizacionId]);
        $ex = $st->fetchColumn();
        
        if ($ex) {
            $this->assertAcceso((int)$ex, $currentUserId);
            return (int)$ex;
        }

        $sql = "SELECT 
                    cl.usuario_id AS cliente_usuario_id,
                    pr.usuario_id AS proveedor_usuario_id
                FROM cotizaciones co
                INNER JOIN clientes cl     ON cl.id = co.cliente_id
                INNER JOIN proveedores pr  ON pr.id = co.proveedor_id
                WHERE co.id = :cid AND co.proveedor_id IS NOT NULL
                LIMIT 1";
        $st = $this->conexion->prepare($sql);
        $st->execute([':cid' => $cotizacionId]);
        $p = $st->fetch(PDO::FETCH_ASSOC);
        
        if (!$p) throw new Exception("Cotización no encontrada o sin proveedor asignado");

        $cuid = (int)$p['cliente_usuario_id'];
        $puid = (int)$p['proveedor_usuario_id'];
        
        if ($currentUserId !== $cuid && $currentUserId !== $puid) {
            throw new Exception("Acceso denegado");
        }

        try {
            $ins = $this->conexion->prepare(
                "INSERT INTO conversaciones (tipo, referencia_id, cliente_usuario_id, proveedor_usuario_id)
                 VALUES ('cotizacion', :rid, :cuid, :puid)"
            );
            $ins->execute([':rid' => $cotizacionId, ':cuid' => $cuid, ':puid' => $puid]);
            return (int)$this->conexion->lastInsertId();
        } catch (PDOException $e) {
            $st = $this->conexion->prepare("SELECT id FROM conversaciones WHERE tipo='cotizacion' AND referencia_id=:rid LIMIT 1");
            $st->execute([':rid' => $cotizacionId]);
            $id = (int)$st->fetchColumn();
            $this->assertAcceso($id, $currentUserId);
            return $id;
        }
    }

    public function listarInbox(int $usuarioId): array
    {
        $sql = "
            SELECT 
                conv.id, conv.tipo, conv.referencia_id,
                COALESCE(s.titulo, co.titulo) AS tema,
                CASE 
                    WHEN conv.cliente_usuario_id = :u THEN conv.proveedor_usuario_id 
                    ELSE conv.cliente_usuario_id 
                END AS otro_usuario_id,
                u2.rol AS otro_rol,
                COALESCE(pr2.nombres, cl2.nombres)   AS otro_nombres,
                COALESCE(pr2.apellidos, cl2.apellidos) AS otro_apellidos,
                COALESCE(pr2.foto, cl2.foto)         AS otro_foto,
                (SELECT m.contenido FROM mensajes m WHERE m.conversacion_id = conv.id ORDER BY m.fecha_hora DESC, m.id DESC LIMIT 1) AS ultimo_contenido,
                (SELECT m.fecha_hora FROM mensajes m WHERE m.conversacion_id = conv.id ORDER BY m.fecha_hora DESC, m.id DESC LIMIT 1) AS ultimo_fecha,
                (SELECT COUNT(*) FROM mensajes m WHERE m.conversacion_id = conv.id AND m.receptor_id = :u AND m.leido = 0) AS no_leidos
            FROM conversaciones conv
            LEFT JOIN solicitudes s ON conv.tipo='solicitud' AND s.id = conv.referencia_id
            LEFT JOIN cotizaciones co ON conv.tipo='cotizacion' AND co.id = conv.referencia_id
            JOIN usuarios u2 ON u2.id = (
                CASE WHEN conv.cliente_usuario_id = :u THEN conv.proveedor_usuario_id ELSE conv.cliente_usuario_id END
            )
            LEFT JOIN clientes cl2 ON cl2.usuario_id = u2.id
            LEFT JOIN proveedores pr2 ON pr2.usuario_id = u2.id
            WHERE conv.cliente_usuario_id = :u OR conv.proveedor_usuario_id = :u
            ORDER BY COALESCE(ultimo_fecha, conv.updated_at) DESC
        ";

        $st = $this->conexion->prepare($sql);
        $st->execute([':u' => $usuarioId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    // ======================================================================
    // 2. GESTIÓN DE MENSAJES DENTRO DE LA CONVERSACIÓN
    // ======================================================================

    public function listarMensajesPorConversacion(int $convId, int $limit = 50): array
    {
        $sql = "SELECT id, emisor_id, receptor_id, contenido, leido, fecha_hora 
                FROM mensajes 
                WHERE conversacion_id = :cid 
                ORDER BY fecha_hora ASC, id ASC 
                LIMIT $limit";
        $st = $this->conexion->prepare($sql);
        $st->execute([':cid' => $convId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarMensajesNuevos(int $convId, ?string $after): array
    {
        $afterVal = $after ?: '1970-01-01 00:00:00';
        $sql = "SELECT id, emisor_id, receptor_id, contenido, leido, fecha_hora 
                FROM mensajes 
                WHERE conversacion_id = :cid AND fecha_hora > :after 
                ORDER BY fecha_hora ASC, id ASC";
        $st = $this->conexion->prepare($sql);
        $st->execute([':cid' => $convId, ':after' => $afterVal]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearMensaje(int $convId, int $emisorId, int $receptorId, string $contenido): int
    {
        $sql = "INSERT INTO mensajes (conversacion_id, emisor_id, receptor_id, contenido, leido, fecha_hora, created_at) 
                VALUES (:cid, :e, :r, :c, 0, current_timestamp(), current_timestamp())";
        $st = $this->conexion->prepare($sql);
        $st->execute([
            ':cid' => $convId,
            ':e' => $emisorId,
            ':r' => $receptorId,
            ':c' => $contenido,
        ]);

        // "Sube" la conversación en el inbox
        $up = $this->conexion->prepare("UPDATE conversaciones SET updated_at = current_timestamp() WHERE id = :id");
        $up->execute([':id' => $convId]);

        return (int)$this->conexion->lastInsertId();
    }

    public function marcarMensajesLeidos(int $convId, int $usuarioId): int
    {
        $sql = "UPDATE mensajes 
                SET leido = 1, fecha_lectura = current_timestamp() 
                WHERE conversacion_id = :cid AND receptor_id = :u AND leido = 0";
        $st = $this->conexion->prepare($sql);
        $st->execute([':cid' => $convId, ':u' => $usuarioId]);
        return $st->rowCount();
    }
}