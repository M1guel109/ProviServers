<?php
// app/models/ProveedorPoliticasServicio.php

require_once __DIR__ . '/../../config/database.php';

class ProveedorPoliticasServicio
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * Obtiene las políticas de servicio a partir del usuario (tabla usuarios).
     */
    public function obtenerPorUsuario(int $usuarioId): ?array
    {
        try {
            $sql = "
                SELECT ps.*
                FROM proveedores_politicas_servicio ps
                INNER JOIN proveedores p ON ps.proveedor_id = p.id
                WHERE p.usuario_id = :usuario_id
                LIMIT 1
            ";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila ?: null;
        } catch (PDOException $e) {
            error_log("Error en ProveedorPoliticasServicio::obtenerPorUsuario -> " . $e->getMessage());
            return null;
        }
    }

    /**
     * Inserta/actualiza las políticas de servicio del proveedor.
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

            // 2. Normalizar datos
            $tipoCancelacion           = $data['tipo_cancelacion']           ?? 'moderada';
            $descripcionCancelacion    = $data['descripcion_cancelacion']    ?? null;
            $permiteReprogramar        = !empty($data['permite_reprogramar']) ? 1 : 0;
            $horasMinReprogramacion    = $data['horas_min_reprogramacion']    ?? null;

            if ($horasMinReprogramacion === '' || !is_numeric($horasMinReprogramacion)) {
                $horasMinReprogramacion = null;
            } else {
                $horasMinReprogramacion = (int) $horasMinReprogramacion;
            }

            $cobraVisita               = !empty($data['cobra_visita']) ? 1 : 0;
            $valorVisita               = $data['valor_visita'] ?? null;
            if ($valorVisita === '' || !is_numeric($valorVisita)) {
                $valorVisita = null;
            } else {
                $valorVisita = (float) $valorVisita;
            }

            $ofreceGarantia            = !empty($data['ofrece_garantia']) ? 1 : 0;
            $diasGarantia              = $data['dias_garantia'] ?? null;
            if ($diasGarantia === '' || !is_numeric($diasGarantia)) {
                $diasGarantia = null;
            } else {
                $diasGarantia = (int) $diasGarantia;
            }
            $detallesGarantia          = $data['detalles_garantia'] ?? null;

            $soloContactoPorPlataforma = !empty($data['solo_contacto_por_plataforma']) ? 1 : 0;
            $tiempoRespuestaPromedio   = $data['tiempo_respuesta_promedio'] ?? null;
            $otrasCondiciones          = $data['otras_condiciones'] ?? null;

            // 3. Ver si ya tiene registro
            $sqlExiste = "SELECT id FROM proveedores_politicas_servicio WHERE proveedor_id = :proveedor_id LIMIT 1";
            $stmtExiste = $this->conexion->prepare($sqlExiste);
            $stmtExiste->bindParam(':proveedor_id', $proveedorId, PDO::PARAM_INT);
            $stmtExiste->execute();
            $existe = $stmtExiste->fetch(PDO::FETCH_ASSOC);

            if ($existe) {
                // UPDATE
                $sql = "
                    UPDATE proveedores_politicas_servicio
                    SET tipo_cancelacion            = :tipo_cancelacion,
                        descripcion_cancelacion     = :descripcion_cancelacion,
                        permite_reprogramar         = :permite_reprogramar,
                        horas_min_reprogramacion    = :horas_min_reprogramacion,
                        cobra_visita                = :cobra_visita,
                        valor_visita                = :valor_visita,
                        ofrece_garantia             = :ofrece_garantia,
                        dias_garantia               = :dias_garantia,
                        detalles_garantia           = :detalles_garantia,
                        solo_contacto_por_plataforma= :solo_contacto_por_plataforma,
                        tiempo_respuesta_promedio   = :tiempo_respuesta_promedio,
                        otras_condiciones           = :otras_condiciones
                    WHERE proveedor_id = :proveedor_id
                    LIMIT 1
                ";
            } else {
                // INSERT
                $sql = "
                    INSERT INTO proveedores_politicas_servicio (
                        proveedor_id,
                        tipo_cancelacion,
                        descripcion_cancelacion,
                        permite_reprogramar,
                        horas_min_reprogramacion,
                        cobra_visita,
                        valor_visita,
                        ofrece_garantia,
                        dias_garantia,
                        detalles_garantia,
                        solo_contacto_por_plataforma,
                        tiempo_respuesta_promedio,
                        otras_condiciones
                    ) VALUES (
                        :proveedor_id,
                        :tipo_cancelacion,
                        :descripcion_cancelacion,
                        :permite_reprogramar,
                        :horas_min_reprogramacion,
                        :cobra_visita,
                        :valor_visita,
                        :ofrece_garantia,
                        :dias_garantia,
                        :detalles_garantia,
                        :solo_contacto_por_plataforma,
                        :tiempo_respuesta_promedio,
                        :otras_condiciones
                    )
                ";
            }

            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(':proveedor_id', $proveedorId, PDO::PARAM_INT);
            $stmt->bindParam(':tipo_cancelacion', $tipoCancelacion);
            $stmt->bindParam(':descripcion_cancelacion', $descripcionCancelacion);
            $stmt->bindParam(':permite_reprogramar', $permiteReprogramar, PDO::PARAM_INT);
            $stmt->bindParam(':horas_min_reprogramacion', $horasMinReprogramacion, PDO::PARAM_INT);
            $stmt->bindParam(':cobra_visita', $cobraVisita, PDO::PARAM_INT);
            $stmt->bindParam(':valor_visita', $valorVisita);
            $stmt->bindParam(':ofrece_garantia', $ofreceGarantia, PDO::PARAM_INT);
            $stmt->bindParam(':dias_garantia', $diasGarantia, PDO::PARAM_INT);
            $stmt->bindParam(':detalles_garantia', $detallesGarantia);
            $stmt->bindParam(':solo_contacto_por_plataforma', $soloContactoPorPlataforma, PDO::PARAM_INT);
            $stmt->bindParam(':tiempo_respuesta_promedio', $tiempoRespuestaPromedio);
            $stmt->bindParam(':otras_condiciones', $otrasCondiciones);

            $stmt->execute();

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log("Error en ProveedorPoliticasServicio::guardarDesdeFormulario -> " . $e->getMessage());
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
