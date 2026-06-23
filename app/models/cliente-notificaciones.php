<?php

require_once __DIR__ . '/../../config/database.php';

class ClienteNotificaciones
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = (new Conexion())->getConexion();
    }

    public function obtenerPorUsuario(int $usuarioId): ?array
    {
        try {
            $stmt = $this->conexion->prepare("
                SELECT n.*
                FROM clientes_notificaciones n
                INNER JOIN clientes c ON n.cliente_id = c.id
                WHERE c.usuario_id = :uid
                LIMIT 1
            ");
            $stmt->execute([':uid' => $usuarioId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('ClienteNotificaciones::obtenerPorUsuario -> ' . $e->getMessage());
            return null;
        }
    }

    public function guardarDesdeFormulario(int $usuarioId, array $data): bool
    {
        try {
            $this->conexion->beginTransaction();

            $stmt = $this->conexion->prepare("SELECT id FROM clientes WHERE usuario_id = :uid LIMIT 1");
            $stmt->execute([':uid' => $usuarioId]);
            $clienteId = $stmt->fetchColumn();
            if (!$clienteId) throw new Exception('Cliente no encontrado.');

            $existe = $this->conexion->prepare(
                "SELECT id FROM clientes_notificaciones WHERE cliente_id = :cid LIMIT 1"
            );
            $existe->execute([':cid' => $clienteId]);

            $params = [
                ':cliente_id'             => $clienteId,
                ':noti_cambios_estado'    => $data['noti_cambios_estado']    ?? 0,
                ':noti_nueva_cotizacion'  => $data['noti_nueva_cotizacion']  ?? 0,
                ':noti_recordatorio_pago' => $data['noti_recordatorio_pago'] ?? 0,
                ':noti_resenas'           => $data['noti_resenas']           ?? 0,
                ':canal_email'            => $data['canal_email']            ?? 0,
                ':canal_interna'          => $data['canal_interna']          ?? 0,
                ':resumen_diario'         => $data['resumen_diario']         ?? 0,
                ':resumen_semanal'        => $data['resumen_semanal']        ?? 0,
            ];

            if ($existe->fetch()) {
                $sql = "UPDATE clientes_notificaciones SET
                            noti_cambios_estado    = :noti_cambios_estado,
                            noti_nueva_cotizacion  = :noti_nueva_cotizacion,
                            noti_recordatorio_pago = :noti_recordatorio_pago,
                            noti_resenas           = :noti_resenas,
                            canal_email            = :canal_email,
                            canal_interna          = :canal_interna,
                            resumen_diario         = :resumen_diario,
                            resumen_semanal        = :resumen_semanal
                        WHERE cliente_id = :cliente_id";
            } else {
                $sql = "INSERT INTO clientes_notificaciones
                            (cliente_id, noti_cambios_estado, noti_nueva_cotizacion,
                             noti_recordatorio_pago, noti_resenas, canal_email,
                             canal_interna, resumen_diario, resumen_semanal)
                        VALUES
                            (:cliente_id, :noti_cambios_estado, :noti_nueva_cotizacion,
                             :noti_recordatorio_pago, :noti_resenas, :canal_email,
                             :canal_interna, :resumen_diario, :resumen_semanal)";
            }

            $this->conexion->prepare($sql)->execute($params);
            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log('ClienteNotificaciones::guardarDesdeFormulario -> ' . $e->getMessage());
            return false;
        }
    }
}
