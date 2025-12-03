<?php
// app/models/ProveedorDisponibilidad.php

require_once __DIR__ . '/../../config/database.php';

class ProveedorDisponibilidad
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * Obtiene el registro de disponibilidad a partir del usuario (tabla usuarios).
     */
    public function obtenerPorUsuario(int $usuarioId)
    {
        try {
            $sql = "
                SELECT d.*
                FROM proveedores_disponibilidad d
                INNER JOIN proveedores p ON d.proveedor_id = p.id
                WHERE p.usuario_id = :usuario_id
                LIMIT 1
            ";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error en ProveedorDisponibilidad::obtenerPorUsuario -> " . $e->getMessage());
            return null;
        }
    }

    /**
     * Guarda (insert/update) la disponibilidad asociada al usuario.
     */
    public function guardarDesdeFormulario(int $usuarioId, array $data): bool
    {
        try {
            $this->conexion->beginTransaction();

            // 1. Obtener el proveedor_id
            $proveedorId = $this->obtenerProveedorIdPorUsuario($usuarioId);
            if (!$proveedorId) {
                throw new Exception("No se encontró proveedor asociado al usuario.");
            }

            // 2. Normalizar datos
            $diasSemana = isset($data['dias_trabajo']) && is_array($data['dias_trabajo'])
                ? implode(',', $data['dias_trabajo'])
                : '';

            $horaInicio = $data['hora_inicio'] ?? '';
            $horaFin    = $data['hora_fin'] ?? '';

            $atiendeFinesSemana = !empty($data['atiende_fines_semana']) ? 1 : 0;
            $atiendeFestivos    = !empty($data['atiende_festivos']) ? 1 : 0;
            $atencionUrgencias  = !empty($data['atencion_urgencias']) ? 1 : 0;
            $detalleUrgencias   = $data['detalle_urgencias'] ?? null;

            $tipoZona   = $data['tipo_zona'] ?? 'ciudad';
            $radioKm    = isset($data['radio_km']) && $data['radio_km'] !== '' ? (int) $data['radio_km'] : null;
            $zonasTexto = $data['zonas_texto'] ?? null;

            // 3. Ver si ya existe registro
            $sqlExiste = "SELECT id FROM proveedores_disponibilidad WHERE proveedor_id = :proveedor_id LIMIT 1";
            $stmtExiste = $this->conexion->prepare($sqlExiste);
            $stmtExiste->bindParam(':proveedor_id', $proveedorId, PDO::PARAM_INT);
            $stmtExiste->execute();
            $filaExiste = $stmtExiste->fetch(PDO::FETCH_ASSOC);

            if ($filaExiste) {
                // UPDATE
                $sqlUpdate = "
                    UPDATE proveedores_disponibilidad
                    SET dias_semana = :dias_semana,
                        hora_inicio = :hora_inicio,
                        hora_fin = :hora_fin,
                        atiende_fines_semana = :atiende_fines_semana,
                        atiende_festivos = :atiende_festivos,
                        atencion_urgencias = :atencion_urgencias,
                        detalle_urgencias = :detalle_urgencias,
                        tipo_zona = :tipo_zona,
                        radio_km = :radio_km,
                        zonas_texto = :zonas_texto
                    WHERE proveedor_id = :proveedor_id
                    LIMIT 1
                ";
                $stmt = $this->conexion->prepare($sqlUpdate);
            } else {
                // INSERT
                $sqlInsert = "
                    INSERT INTO proveedores_disponibilidad (
                        proveedor_id,
                        dias_semana,
                        hora_inicio,
                        hora_fin,
                        atiende_fines_semana,
                        atiende_festivos,
                        atencion_urgencias,
                        detalle_urgencias,
                        tipo_zona,
                        radio_km,
                        zonas_texto
                    ) VALUES (
                        :proveedor_id,
                        :dias_semana,
                        :hora_inicio,
                        :hora_fin,
                        :atiende_fines_semana,
                        :atiende_festivos,
                        :atencion_urgencias,
                        :detalle_urgencias,
                        :tipo_zona,
                        :radio_km,
                        :zonas_texto
                    )
                ";
                $stmt = $this->conexion->prepare($sqlInsert);
            }

            $stmt->bindParam(':proveedor_id', $proveedorId, PDO::PARAM_INT);
            $stmt->bindParam(':dias_semana', $diasSemana);
            $stmt->bindParam(':hora_inicio', $horaInicio);
            $stmt->bindParam(':hora_fin', $horaFin);
            $stmt->bindParam(':atiende_fines_semana', $atiendeFinesSemana, PDO::PARAM_INT);
            $stmt->bindParam(':atiende_festivos', $atiendeFestivos, PDO::PARAM_INT);
            $stmt->bindParam(':atencion_urgencias', $atencionUrgencias, PDO::PARAM_INT);
            $stmt->bindParam(':detalle_urgencias', $detalleUrgencias);
            $stmt->bindParam(':tipo_zona', $tipoZona);
            $stmt->bindParam(':radio_km', $radioKm, PDO::PARAM_INT);
            $stmt->bindParam(':zonas_texto', $zonasTexto);

            $stmt->execute();

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log("Error en ProveedorDisponibilidad::guardarDesdeFormulario -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Devuelve el id del proveedor (tabla proveedores) a partir del usuario_id.
     */
    private function obtenerProveedorIdPorUsuario(int $usuarioId)
    {
        $sql = "SELECT id FROM proveedores WHERE usuario_id = :usuario_id LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila['id'] ?? null;
    }
}
