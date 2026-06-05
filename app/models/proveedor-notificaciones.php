<?php
// app/models/ProveedorNotificaciones.php

require_once __DIR__ . '/../../config/database.php';

class ProveedorNotificaciones
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * Obtiene la configuración de notificaciones a partir del usuario (tabla usuarios).
     */
    public function obtenerPorUsuario(int $usuarioId): ?array
    {
        try {
            $sql = "
                SELECT n.*
                FROM proveedores_notificaciones n
                INNER JOIN proveedores p ON n.proveedor_id = p.id
                WHERE p.usuario_id = :usuario_id
                LIMIT 1
            ";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila ?: null;
        } catch (PDOException $e) {
            error_log("Error en ProveedorNotificaciones::obtenerPorUsuario -> " . $e->getMessage());
            return null;
        }
    }

    /**
     * Inserta/actualiza la configuración de notificaciones del proveedor.
     */
    public function guardarDesdeFormulario(int $usuarioId, array $data): bool
    {
        try {
            $this->conexion->beginTransaction();

            // 1. Obtener proveedor_id
            $proveedorId = $this->obtenerProveedorIdPorUsuario($usuarioId);
            if (!$proveedorId) {
                throw new Exception("No se encontró proveedor asociado al usuario.");
            }

            // 2. Normalizar a 0/1
            $notiSolicitudesNuevas = !empty($data['noti_solicitudes_nuevas']) ? 1 : 0;
            $notiCambiosEstado     = !empty($data['noti_cambios_estado']) ? 1 : 0;
            $notiResenas           = !empty($data['noti_resenas']) ? 1 : 0;
            $notiPagos             = !empty($data['noti_pagos']) ? 1 : 0;

            $canalEmail    = !empty($data['canal_email']) ? 1 : 0;
            $canalInterna  = !empty($data['canal_interna']) ? 1 : 0;
            $canalWhatsapp = !empty($data['canal_whatsapp']) ? 1 : 0;

            $resumenDiario  = !empty($data['resumen_diario']) ? 1 : 0;
            $resumenSemanal = !empty($data['resumen_semanal']) ? 1 : 0;

            // 3. Ver si ya tiene registro
            $sqlExiste = "SELECT id FROM proveedores_notificaciones WHERE proveedor_id = :proveedor_id LIMIT 1";
            $stmtExiste = $this->conexion->prepare($sqlExiste);
            $stmtExiste->bindParam(':proveedor_id', $proveedorId, PDO::PARAM_INT);
            $stmtExiste->execute();
            $existe = $stmtExiste->fetch(PDO::FETCH_ASSOC);

            if ($existe) {
                // UPDATE
                $sql = "
                    UPDATE proveedores_notificaciones
                    SET noti_solicitudes_nuevas = :noti_solicitudes_nuevas,
                        noti_cambios_estado     = :noti_cambios_estado,
                        noti_resenas            = :noti_resenas,
                        noti_pagos              = :noti_pagos,
                        canal_email             = :canal_email,
                        canal_interna           = :canal_interna,
                        canal_whatsapp          = :canal_whatsapp,
                        resumen_diario          = :resumen_diario,
                        resumen_semanal         = :resumen_semanal
                    WHERE proveedor_id = :proveedor_id
                    LIMIT 1
                ";
            } else {
                // INSERT
                $sql = "
                    INSERT INTO proveedores_notificaciones (
                        proveedor_id,
                        noti_solicitudes_nuevas,
                        noti_cambios_estado,
                        noti_resenas,
                        noti_pagos,
                        canal_email,
                        canal_interna,
                        canal_whatsapp,
                        resumen_diario,
                        resumen_semanal
                    ) VALUES (
                        :proveedor_id,
                        :noti_solicitudes_nuevas,
                        :noti_cambios_estado,
                        :noti_resenas,
                        :noti_pagos,
                        :canal_email,
                        :canal_interna,
                        :canal_whatsapp,
                        :resumen_diario,
                        :resumen_semanal
                    )
                ";
            }

            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(':proveedor_id', $proveedorId, PDO::PARAM_INT);
            $stmt->bindParam(':noti_solicitudes_nuevas', $notiSolicitudesNuevas, PDO::PARAM_INT);
            $stmt->bindParam(':noti_cambios_estado', $notiCambiosEstado, PDO::PARAM_INT);
            $stmt->bindParam(':noti_resenas', $notiResenas, PDO::PARAM_INT);
            $stmt->bindParam(':noti_pagos', $notiPagos, PDO::PARAM_INT);
            $stmt->bindParam(':canal_email', $canalEmail, PDO::PARAM_INT);
            $stmt->bindParam(':canal_interna', $canalInterna, PDO::PARAM_INT);
            $stmt->bindParam(':canal_whatsapp', $canalWhatsapp, PDO::PARAM_INT);
            $stmt->bindParam(':resumen_diario', $resumenDiario, PDO::PARAM_INT);
            $stmt->bindParam(':resumen_semanal', $resumenSemanal, PDO::PARAM_INT);

            $stmt->execute();

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log("Error en ProveedorNotificaciones::guardarDesdeFormulario -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Devuelve el id del proveedor (tabla proveedores) a partir del usuario_id.
     */
    private function obtenerProveedorIdPorUsuario(int $usuarioId): ?int
    {
        $sql = "SELECT id FROM proveedores WHERE usuario_id = :usuario_id LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila['id'] ?? null;
    }
}
