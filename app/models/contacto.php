<?php
require_once __DIR__ . '/../../config/database.php';

class Contacto {
    private $db;

    public function __construct() {
        $this->db = (new Conexion())->getConexion();
        $this->_ensureColumna();
    }

    // Agrega columna leido si no existe (funciona local y Hostinger)
    private function _ensureColumna(): void {
        try {
            $this->db->exec("ALTER TABLE mensajes_contacto ADD COLUMN leido TINYINT(1) NOT NULL DEFAULT 0");
        } catch (PDOException $e) {}
    }

    public function registrarMensaje($datos): bool {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO mensajes_contacto (nombre, email, mensaje, fecha_envio)
                 VALUES (:nombre, :email, :mensaje, NOW())"
            );
            return $stmt->execute([
                ':nombre'  => $datos['nombre'],
                ':email'   => $datos['email'],
                ':mensaje' => $datos['mensaje'],
            ]);
        } catch (PDOException $e) {
            error_log("Error guardando contacto: " . $e->getMessage());
            return false;
        }
    }

    public function listarMensajes(): array {
        try {
            $stmt = $this->db->query(
                "SELECT * FROM mensajes_contacto ORDER BY fecha_envio DESC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function marcarLeido(int $id): bool {
        try {
            $stmt = $this->db->prepare(
                "UPDATE mensajes_contacto SET leido = 1 WHERE id = :id"
            );
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function contarNoLeidos(): int {
        try {
            return (int)$this->db->query(
                "SELECT COUNT(*) FROM mensajes_contacto WHERE leido = 0"
            )->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
}
