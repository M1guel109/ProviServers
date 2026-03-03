<?php
// app/models/Proveedor.php

require_once __DIR__ . '/../../config/database.php';

class Proveedor
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    // ======================================================================
    // HELPER COMPARTIDO
    // ======================================================================

    /**
     * Devuelve el id del proveedor (tabla proveedores) a partir del usuario_id.
     * Compartido por Disponibilidad y Políticas.
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

    // ======================================================================
    // 1. MÉTODOS DE PERFIL PROFESIONAL
    // ======================================================================

    public function obtenerPerfilPorUsuario($idUsuario)
    {
        try {
            $sql = "SELECT * FROM proveedor_perfil 
                    WHERE id_usuario = :id_usuario 
                    LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            $perfil = $stmt->fetch(PDO::FETCH_ASSOC);
            return $perfil ?: null;
        } catch (PDOException $e) {
            error_log("Error en Proveedor::obtenerPerfilPorUsuario -> " . $e->getMessage());
            return null;
        }
    }

    public function crearPerfil($idUsuario, array $data)
    {
        try {
            $sql = "INSERT INTO proveedor_perfil 
                    (
                        id_usuario, nombre_comercial, tipo_proveedor, eslogan, descripcion,
                        anios_experiencia, idiomas, categorias, ciudad, zona, foto,
                        telefono_contacto, whatsapp, correo_alternativo, created_at, updated_at
                    )
                    VALUES
                    (
                        :id_usuario, :nombre_comercial, :tipo_proveedor, :eslogan, :descripcion,
                        :anios_experiencia, :idiomas, :categorias, :ciudad, :zona, :foto,
                        :telefono_contacto, :whatsapp, :correo_alternativo, NOW(), NOW()
                    )";

            $stmt = $this->conexion->prepare($sql);
            $this->bindParamsPerfil($stmt, $idUsuario, $data);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Proveedor::crearPerfil -> " . $e->getMessage());
            return false;
        }
    }

    public function actualizarPerfil($idUsuario, array $data)
    {
        try {
            $sql = "UPDATE proveedor_perfil SET
                        nombre_comercial   = :nombre_comercial,
                        tipo_proveedor     = :tipo_proveedor,
                        eslogan            = :eslogan,
                        descripcion        = :descripcion,
                        anios_experiencia  = :anios_experiencia,
                        idiomas            = :idiomas,
                        categorias         = :categorias,
                        ciudad             = :ciudad,
                        zona               = :zona,
                        foto               = :foto,
                        telefono_contacto  = :telefono_contacto,
                        whatsapp           = :whatsapp,
                        correo_alternativo = :correo_alternativo,
                        updated_at         = NOW()
                    WHERE id_usuario = :id_usuario
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $this->bindParamsPerfil($stmt, $idUsuario, $data);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Proveedor::actualizarPerfil -> " . $e->getMessage());
            return false;
        }
    }

    private function bindParamsPerfil($stmt, $idUsuario, array $data)
    {
        $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':nombre_comercial',   $data['nombre_comercial']);
        $stmt->bindValue(':tipo_proveedor',     $data['tipo_proveedor']);
        $stmt->bindValue(':eslogan',            $data['eslogan']);
        $stmt->bindValue(':descripcion',        $data['descripcion']);
        $stmt->bindValue(':anios_experiencia',  $data['anios_experiencia']);
        $stmt->bindValue(':idiomas',            $data['idiomas']);       
        $stmt->bindValue(':categorias',         $data['categorias']);    
        $stmt->bindValue(':ciudad',             $data['ciudad']);
        $stmt->bindValue(':zona',               $data['zona']);
        $stmt->bindValue(':foto',               $data['foto']);
        $stmt->bindValue(':telefono_contacto',  $data['telefono_contacto']);
        $stmt->bindValue(':whatsapp',           $data['whatsapp']);
        $stmt->bindValue(':correo_alternativo', $data['correo_alternativo']);
    }

    // ======================================================================
    // 2. MÉTODOS DE DISPONIBILIDAD
    // ======================================================================

    public function obtenerDisponibilidadPorUsuario(int $usuarioId)
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
            error_log("Error en Proveedor::obtenerDisponibilidadPorUsuario -> " . $e->getMessage());
            return null;
        }
    }

    public function guardarDisponibilidad(int $usuarioId, array $data): bool
    {
        try {
            $this->conexion->beginTransaction();

            $proveedorId = $this->obtenerProveedorIdPorUsuario($usuarioId);
            if (!$proveedorId) {
                throw new Exception("No se encontró proveedor asociado al usuario.");
            }

            // Normalizar datos
            $diasSemana = isset($data['dias_trabajo']) && is_array($data['dias_trabajo']) ? implode(',', $data['dias_trabajo']) : '';
            $horaInicio = $data['hora_inicio'] ?? '';
            $horaFin    = $data['hora_fin'] ?? '';
            $atiendeFinesSemana = !empty($data['atiende_fines_semana']) ? 1 : 0;
            $atiendeFestivos    = !empty($data['atiende_festivos']) ? 1 : 0;
            $atencionUrgencias  = !empty($data['atencion_urgencias']) ? 1 : 0;
            $detalleUrgencias   = $data['detalle_urgencias'] ?? null;
            $tipoZona   = $data['tipo_zona'] ?? 'ciudad';
            $radioKm    = isset($data['radio_km']) && $data['radio_km'] !== '' ? (int) $data['radio_km'] : null;
            $zonasTexto = $data['zonas_texto'] ?? null;

            // Ver si ya existe registro
            $sqlExiste = "SELECT id FROM proveedores_disponibilidad WHERE proveedor_id = :proveedor_id LIMIT 1";
            $stmtExiste = $this->conexion->prepare($sqlExiste);
            $stmtExiste->bindParam(':proveedor_id', $proveedorId, PDO::PARAM_INT);
            $stmtExiste->execute();
            $filaExiste = $stmtExiste->fetch(PDO::FETCH_ASSOC);

            if ($filaExiste) {
                $sql = "UPDATE proveedores_disponibilidad
                        SET dias_semana = :dias_semana, hora_inicio = :hora_inicio, hora_fin = :hora_fin,
                            atiende_fines_semana = :atiende_fines_semana, atiende_festivos = :atiende_festivos,
                            atencion_urgencias = :atencion_urgencias, detalle_urgencias = :detalle_urgencias,
                            tipo_zona = :tipo_zona, radio_km = :radio_km, zonas_texto = :zonas_texto
                        WHERE proveedor_id = :proveedor_id LIMIT 1";
            } else {
                $sql = "INSERT INTO proveedores_disponibilidad (
                            proveedor_id, dias_semana, hora_inicio, hora_fin, atiende_fines_semana,
                            atiende_festivos, atencion_urgencias, detalle_urgencias, tipo_zona, radio_km, zonas_texto
                        ) VALUES (
                            :proveedor_id, :dias_semana, :hora_inicio, :hora_fin, :atiende_fines_semana,
                            :atiende_festivos, :atencion_urgencias, :detalle_urgencias, :tipo_zona, :radio_km, :zonas_texto
                        )";
            }

            $stmt = $this->conexion->prepare($sql);
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
            error_log("Error en Proveedor::guardarDisponibilidad -> " . $e->getMessage());
            return false;
        }
    }

    // ======================================================================
    // 3. MÉTODOS DE POLÍTICAS DE SERVICIO
    // ======================================================================

    public function obtenerPoliticasPorUsuario(int $usuarioId): ?array
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
            error_log("Error en Proveedor::obtenerPoliticasPorUsuario -> " . $e->getMessage());
            return null;
        }
    }

    public function guardarPoliticas(int $usuarioId, array $data): bool
    {
        try {
            $this->conexion->beginTransaction();

            $proveedorId = $this->obtenerProveedorIdPorUsuario($usuarioId);
            if (!$proveedorId) {
                throw new Exception("No se encontró proveedor asociado al usuario.");
            }

            // Normalizar
            $tipoCancelacion = $data['tipo_cancelacion'] ?? 'moderada';
            $descripcionCancelacion = $data['descripcion_cancelacion'] ?? null;
            $permiteReprogramar = !empty($data['permite_reprogramar']) ? 1 : 0;
            $horasMinReprogramacion = ($data['horas_min_reprogramacion'] === '' || !is_numeric($data['horas_min_reprogramacion'])) ? null : (int)$data['horas_min_reprogramacion'];
            
            $cobraVisita = !empty($data['cobra_visita']) ? 1 : 0;
            $valorVisita = ($data['valor_visita'] === '' || !is_numeric($data['valor_visita'])) ? null : (float)$data['valor_visita'];
            
            $ofreceGarantia = !empty($data['ofrece_garantia']) ? 1 : 0;
            $diasGarantia = ($data['dias_garantia'] === '' || !is_numeric($data['dias_garantia'])) ? null : (int)$data['dias_garantia'];
            $detallesGarantia = $data['detalles_garantia'] ?? null;

            $soloContactoPlataforma = !empty($data['solo_contacto_por_plataforma']) ? 1 : 0;
            $tiempoRespuesta = $data['tiempo_respuesta_promedio'] ?? null;
            $otrasCondiciones = $data['otras_condiciones'] ?? null;

            // Ver si existe
            $sqlExiste = "SELECT id FROM proveedores_politicas_servicio WHERE proveedor_id = :proveedor_id LIMIT 1";
            $stmtExiste = $this->conexion->prepare($sqlExiste);
            $stmtExiste->bindParam(':proveedor_id', $proveedorId, PDO::PARAM_INT);
            $stmtExiste->execute();
            $existe = $stmtExiste->fetch(PDO::FETCH_ASSOC);

            if ($existe) {
                $sql = "UPDATE proveedores_politicas_servicio
                        SET tipo_cancelacion = :tipo_cancelacion, descripcion_cancelacion = :descripcion_cancelacion,
                            permite_reprogramar = :permite_reprogramar, horas_min_reprogramacion = :horas_min_reprogramacion,
                            cobra_visita = :cobra_visita, valor_visita = :valor_visita, ofrece_garantia = :ofrece_garantia,
                            dias_garantia = :dias_garantia, detalles_garantia = :detalles_garantia,
                            solo_contacto_por_plataforma = :solo_contacto_por_plataforma, tiempo_respuesta_promedio = :tiempo_respuesta_promedio,
                            otras_condiciones = :otras_condiciones
                        WHERE proveedor_id = :proveedor_id LIMIT 1";
            } else {
                $sql = "INSERT INTO proveedores_politicas_servicio (
                            proveedor_id, tipo_cancelacion, descripcion_cancelacion, permite_reprogramar, horas_min_reprogramacion,
                            cobra_visita, valor_visita, ofrece_garantia, dias_garantia, detalles_garantia, solo_contacto_por_plataforma,
                            tiempo_respuesta_promedio, otras_condiciones
                        ) VALUES (
                            :proveedor_id, :tipo_cancelacion, :descripcion_cancelacion, :permite_reprogramar, :horas_min_reprogramacion,
                            :cobra_visita, :valor_visita, :ofrece_garantia, :dias_garantia, :detalles_garantia, :solo_contacto_por_plataforma,
                            :tiempo_respuesta_promedio, :otras_condiciones
                        )";
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
            $stmt->bindParam(':solo_contacto_por_plataforma', $soloContactoPlataforma, PDO::PARAM_INT);
            $stmt->bindParam(':tiempo_respuesta_promedio', $tiempoRespuesta);
            $stmt->bindParam(':otras_condiciones', $otrasCondiciones);
            $stmt->execute();

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log("Error en Proveedor::guardarPoliticas -> " . $e->getMessage());
            return false;
        }
    }

    // ======================================================================
    // 4. MÉTODOS DE SEGURIDAD Y NOTIFICACIONES
    // ======================================================================

    public function obtenerSeguridadPorUsuario(int $usuarioId): ?array
    {
        try {
            $sql = "SELECT * FROM proveedor_seguridad WHERE usuario_id = :usuario_id LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            return $fila ?: null;
        } catch (PDOException $e) {
            error_log('Error en Proveedor::obtenerSeguridadPorUsuario -> ' . $e->getMessage());
            return null;
        }
    }

    public function guardarSeguridad(int $usuarioId, array $data): bool
    {
        try {
            $alertaSolicitudes = !empty($data['alerta_solicitudes']) ? 1 : 0;
            $alertaResenas     = !empty($data['alerta_resenas']) ? 1 : 0;
            $alertaPagos       = !empty($data['alerta_pagos']) ? 1 : 0;

            $canal = $data['canal_notificaciones'] ?? 'ambos';
            if (!in_array($canal, ['correo', 'plataforma', 'ambos'], true)) {
                $canal = 'ambos';
            }

            $tiempoSesion = isset($data['tiempo_sesion']) ? (int)$data['tiempo_sesion'] : 60;
            if ($tiempoSesion <= 0) $tiempoSesion = 60;

            $sqlCheck = "SELECT id FROM proveedor_seguridad WHERE usuario_id = :usuario_id LIMIT 1";
            $stmtCheck = $this->conexion->prepare($sqlCheck);
            $stmtCheck->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmtCheck->execute();
            $existe = $stmtCheck->fetchColumn();

            if ($existe) {
                $sql = "UPDATE proveedor_seguridad
                        SET alerta_solicitudes = :alerta_solicitudes, alerta_resenas = :alerta_resenas,
                            alerta_pagos = :alerta_pagos, canal_notificaciones = :canal, tiempo_sesion = :tiempo_sesion
                        WHERE usuario_id = :usuario_id";
            } else {
                $sql = "INSERT INTO proveedor_seguridad (
                            usuario_id, alerta_solicitudes, alerta_resenas, alerta_pagos, canal_notificaciones, tiempo_sesion
                        ) VALUES (
                            :usuario_id, :alerta_solicitudes, :alerta_resenas, :alerta_pagos, :canal, :tiempo_sesion
                        )";
            }

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':usuario_id',        $usuarioId,         PDO::PARAM_INT);
            $stmt->bindParam(':alerta_solicitudes',$alertaSolicitudes, PDO::PARAM_INT);
            $stmt->bindParam(':alerta_resenas',    $alertaResenas,     PDO::PARAM_INT);
            $stmt->bindParam(':alerta_pagos',      $alertaPagos,       PDO::PARAM_INT);
            $stmt->bindParam(':canal',             $canal,             PDO::PARAM_STR);
            $stmt->bindParam(':tiempo_sesion',     $tiempoSesion,      PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en Proveedor::guardarSeguridad -> ' . $e->getMessage());
            return false;
        }
    }
}