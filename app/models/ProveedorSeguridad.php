<?php
// app/models/ProveedorSeguridad.php

require_once __DIR__ . '/../../config/database.php';

class ProveedorSeguridad
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * Obtiene las preferencias de seguridad de un proveedor
     * por id de usuario (tabla usuarios.id).
     */
    public function obtenerPorUsuario(int $usuarioId): ?array
    {
        try {
            $sql = "SELECT *
                    FROM proveedor_seguridad
                    WHERE usuario_id = :usuario_id
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            return $fila ?: null;
        } catch (PDOException $e) {
            error_log('Error en ProveedorSeguridad::obtenerPorUsuario -> ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Guarda o actualiza las preferencias de seguridad del proveedor.
     * Recibe el id del usuario y el array $_POST (o similar).
     */
    public function guardarOActualizar(int $usuarioId, array $data): bool
    {
        try {
            // Normalizar valores (checkboxes, select, etc.)
            $alertaSolicitudes = !empty($data['alerta_solicitudes']) ? 1 : 0;
            $alertaResenas     = !empty($data['alerta_resenas']) ? 1 : 0;
            $alertaPagos       = !empty($data['alerta_pagos']) ? 1 : 0;

            $canal = $data['canal_notificaciones'] ?? 'ambos';
            if (!in_array($canal, ['correo', 'plataforma', 'ambos'], true)) {
                $canal = 'ambos';
            }

            $tiempoSesion = isset($data['tiempo_sesion'])
                ? (int) $data['tiempo_sesion']
                : 60;

            if ($tiempoSesion <= 0) {
                $tiempoSesion = 60;
            }

            // Verificar si ya existe configuración para este usuario
            $sqlCheck = "SELECT id
                         FROM proveedor_seguridad
                         WHERE usuario_id = :usuario_id
                         LIMIT 1";

            $stmtCheck = $this->conexion->prepare($sqlCheck);
            $stmtCheck->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmtCheck->execute();

            $existe = $stmtCheck->fetchColumn();

            if ($existe) {
                // UPDATE
                $sql = "UPDATE proveedor_seguridad
                        SET alerta_solicitudes   = :alerta_solicitudes,
                            alerta_resenas       = :alerta_resenas,
                            alerta_pagos         = :alerta_pagos,
                            canal_notificaciones = :canal,
                            tiempo_sesion        = :tiempo_sesion
                        WHERE usuario_id = :usuario_id";
            } else {
                // INSERT
                $sql = "INSERT INTO proveedor_seguridad
                        (usuario_id, alerta_solicitudes, alerta_resenas, alerta_pagos,
                         canal_notificaciones, tiempo_sesion)
                        VALUES
                        (:usuario_id, :alerta_solicitudes, :alerta_resenas, :alerta_pagos,
                         :canal, :tiempo_sesion)";
            }

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':usuario_id',        $usuarioId,        PDO::PARAM_INT);
            $stmt->bindParam(':alerta_solicitudes',$alertaSolicitudes, PDO::PARAM_INT);
            $stmt->bindParam(':alerta_resenas',    $alertaResenas,     PDO::PARAM_INT);
            $stmt->bindParam(':alerta_pagos',      $alertaPagos,       PDO::PARAM_INT);
            $stmt->bindParam(':canal',             $canal,             PDO::PARAM_STR);
            $stmt->bindParam(':tiempo_sesion',     $tiempoSesion,      PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en ProveedorSeguridad::guardarOActualizar -> ' . $e->getMessage());
            return false;
        }
    }
}
