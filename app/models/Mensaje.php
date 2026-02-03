<?php
require_once __DIR__ . '/../../config/database.php';

class Mensaje
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Conexion())->getConexion();
    }

    public function listarPorConversacion(int $convId, int $limit = 50): array
    {
        $sql = "SELECT id, emisor_id, receptor_id, contenido, leido, fecha_hora
                FROM mensajes
                WHERE conversacion_id = :cid
                ORDER BY fecha_hora ASC, id ASC
                LIMIT $limit";
        $st = $this->db->prepare($sql);
        $st->execute([':cid' => $convId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarNuevos(int $convId, ?string $after): array
    {
        $afterVal = $after ?: '1970-01-01 00:00:00';
        $sql = "SELECT id, emisor_id, receptor_id, contenido, leido, fecha_hora
                FROM mensajes
                WHERE conversacion_id = :cid AND fecha_hora > :after
                ORDER BY fecha_hora ASC, id ASC";
        $st = $this->db->prepare($sql);
        $st->execute([':cid' => $convId, ':after' => $afterVal]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear(int $convId, int $emisorId, int $receptorId, string $contenido): int
    {
        $sql = "INSERT INTO mensajes (conversacion_id, emisor_id, receptor_id, contenido, leido, fecha_hora, created_at)
                VALUES (:cid, :e, :r, :c, 0, current_timestamp(), current_timestamp())";
        $st = $this->db->prepare($sql);
        $st->execute([
            ':cid' => $convId,
            ':e' => $emisorId,
            ':r' => $receptorId,
            ':c' => $contenido,
        ]);

        // “sube” conversación en inbox
        $up = $this->db->prepare("UPDATE conversaciones SET updated_at = current_timestamp() WHERE id = :id");
        $up->execute([':id' => $convId]);

        return (int)$this->db->lastInsertId();
    }

    public function marcarLeidos(int $convId, int $usuarioId): int
    {
        $sql = "UPDATE mensajes
                SET leido = 1, fecha_lectura = current_timestamp()
                WHERE conversacion_id = :cid AND receptor_id = :u AND leido = 0";
        $st = $this->db->prepare($sql);
        $st->execute([':cid' => $convId, ':u' => $usuarioId]);
        return $st->rowCount();
    }
}
