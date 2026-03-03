<?php
// app/models/Membresia.php

require_once __DIR__ . '/../../config/database.php';

class Membresia
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    // ======================================================================
    // 1. GESTIÓN DEL CATÁLOGO DE MEMBRESÍAS (ADMIN)
    // ======================================================================

    /**
     * Registra una nueva membresía en la BD
     */
    public function registrar($data)
    {
        try {
            $sql = "INSERT INTO membresias (
                        tipo, descripcion, costo, duracion_dias, estado, 
                        es_destacado, orden_visual, max_servicios_activos, 
                        acceso_estadisticas_pro, permite_videos, created_at
                    ) VALUES (
                        :tipo, :desc, :costo, :dias, :estado, :destacado, 
                        :orden, :max_serv, :stats, :videos, NOW()
                    )";

            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(':tipo',      $data['tipo']);
            $stmt->bindParam(':desc',      $data['descripcion']);
            $stmt->bindParam(':costo',     $data['costo']);
            $stmt->bindParam(':dias',      $data['duracion_dias']);
            $stmt->bindParam(':estado',    $data['estado']);
            $stmt->bindParam(':destacado', $data['es_destacado'], PDO::PARAM_INT);
            $stmt->bindParam(':max_serv',  $data['max_servicios_activos'], PDO::PARAM_INT);
            $stmt->bindParam(':stats',     $data['acceso_estadisticas_pro'], PDO::PARAM_INT);
            $stmt->bindParam(':videos',    $data['permite_videos'], PDO::PARAM_INT);

            if ($data['orden_visual'] === null) {
                $stmt->bindValue(':orden', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':orden', $data['orden_visual'], PDO::PARAM_INT);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error Membresia::registrar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listar todas las membresías
     */
    public function mostrar()
    {
        try {
            $sql = "SELECT * FROM membresias ORDER BY orden_visual ASC, created_at DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error Membresia::mostrar: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener una membresía por ID
     */
    public function mostrarId($id)
    {
        try {
            $sql = "SELECT * FROM membresias WHERE id = :id LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error Membresia::mostrarId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar una membresía existente
     */
    public function actualizar($data)
    {
        try {
            $sql = "UPDATE membresias SET 
                        tipo = :tipo, descripcion = :desc, costo = :costo,
                        duracion_dias = :dias, estado = :estado, es_destacado = :destacado,
                        orden_visual = :orden, max_servicios_activos = :max_serv,
                        acceso_estadisticas_pro = :stats, permite_videos = :videos,
                        modified_at = NOW()
                    WHERE id = :id";

            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(':id',        $data['id'], PDO::PARAM_INT);
            $stmt->bindParam(':tipo',      $data['tipo']);
            $stmt->bindParam(':desc',      $data['descripcion']);
            $stmt->bindParam(':costo',     $data['costo']);
            $stmt->bindParam(':dias',      $data['duracion_dias']);
            $stmt->bindParam(':estado',    $data['estado']);
            $stmt->bindParam(':destacado', $data['es_destacado'], PDO::PARAM_INT);
            $stmt->bindParam(':max_serv',  $data['max_servicios_activos'], PDO::PARAM_INT);
            $stmt->bindParam(':stats',     $data['acceso_estadisticas_pro'], PDO::PARAM_INT);
            $stmt->bindParam(':videos',    $data['permite_videos'], PDO::PARAM_INT);

            if ($data['orden_visual'] === null) {
                $stmt->bindValue(':orden', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':orden', $data['orden_visual'], PDO::PARAM_INT);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error Membresia::actualizar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si la membresía está asignada a algún proveedor
     */
    public function tieneProveedores($id)
    {
        try {
            $sql = "SELECT COUNT(*) FROM proveedor_membresia WHERE membresia_id = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error Membresia::tieneProveedores: " . $e->getMessage());
            return true; // En caso de error, bloqueamos por seguridad
        }
    }

    /**
     * Eliminar membresía del catálogo
     */
    public function eliminar($id)
    {
        try {
            $sql = "DELETE FROM membresias WHERE id = :id";
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error Membresia::eliminar: " . $e->getMessage());
            return false;
        }
    }

    // ======================================================================
    // 2. ACTIVACIÓN Y CONTROL DE MEMBRESÍAS DE PROVEEDORES
    // ======================================================================

    /**
     * Activa la membresía de un proveedor si aún no ha sido iniciada.
     * Típicamente llamado al momento del primer login exitoso.
     * * @param int $usuario_id El ID del usuario que se está logueando.
     * @return bool
     */
    public function activarSiEsNecesario($usuario_id)
    {
        try {
            // 1. Buscamos si el proveedor tiene una membresía 'inactiva' y con fechas NULL
            $sqlCheck = "SELECT pm.id, pm.membresia_id, m.duracion_dias 
                         FROM proveedor_membresia pm
                         JOIN proveedores p ON pm.proveedor_id = p.id
                         JOIN membresias m ON pm.membresia_id = m.id
                         WHERE p.usuario_id = :usuario_id 
                         AND pm.estado = 'inactiva' 
                         AND pm.fecha_inicio IS NULL";

            $stmt = $this->conexion->prepare($sqlCheck);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->execute();

            $membresiaPendiente = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si no hay membresía pendiente de activación, terminamos
            if (!$membresiaPendiente) {
                return false; 
            }

            // 2. Calcular fechas
            $idRegistro = $membresiaPendiente['id'];
            $dias = $membresiaPendiente['duracion_dias'] ?? 30; // Por defecto 30 si no existe
            
            $fechaInicio = date('Y-m-d H:i:s');
            $fechaFin = date('Y-m-d H:i:s', strtotime("+$dias days"));

            // 3. Actualizar la tabla
            $sqlUpdate = "UPDATE proveedor_membresia 
                          SET fecha_inicio = :inicio, 
                              fecha_fin = :fin, 
                              estado = 'activa' 
                          WHERE id = :id";

            $stmtUpdate = $this->conexion->prepare($sqlUpdate);
            return $stmtUpdate->execute([
                ':inicio' => $fechaInicio,
                ':fin'    => $fechaFin,
                ':id'     => $idRegistro
            ]);

        } catch (PDOException $e) {
            error_log("Error en Membresia::activarSiEsNecesario -> " . $e->getMessage());
            return false;
        }
    }
}