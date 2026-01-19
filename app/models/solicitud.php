<?php
require_once __DIR__ . '/../../config/database.php';

class Solicitud
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /* ======================================================
       CREAR SOLICITUD + ADJUNTOS
       ====================================================== */
    public function crear(array $data): bool
    {
        try {
            // 🔒 Iniciar transacción
            $this->conexion->beginTransaction();

            /* --------------------------------------------
               1️⃣ Insertar solicitud
               -------------------------------------------- */
            $sql = "INSERT INTO solicitudes (
                        cliente_id,
                        proveedor_id,
                        publicacion_id,
                        titulo,
                        descripcion,
                        direccion,
                        ciudad,
                        zona,
                        fecha_preferida,
                        franja_horaria,
                        presupuesto_estimado,
                        estado
                    ) VALUES (
                        :cliente_id,
                        :proveedor_id,
                        :publicacion_id,
                        :titulo,
                        :descripcion,
                        :direccion,
                        :ciudad,
                        :zona,
                        :fecha_preferida,
                        :franja_horaria,
                        :presupuesto_estimado,
                        'pendiente'
                    )";

            $stmt = $this->conexion->prepare($sql);

            $stmt->execute([
                ':cliente_id'            => $data['cliente_id'],
                ':proveedor_id'          => $data['proveedor_id'],
                ':publicacion_id'        => $data['publicacion_id'],
                ':titulo'                => $data['titulo'],
                ':descripcion'           => $data['descripcion'],
                ':direccion'             => $data['direccion'],
                ':ciudad'                => $data['ciudad'],
                ':zona'                  => $data['zona'],
                ':fecha_preferida'       => $data['fecha_servicio'],
                ':franja_horaria'        => $data['franja_horaria'],
                ':presupuesto_estimado'  => $data['presupuesto_estimado']
            ]);

            // Obtener ID de la solicitud creada
            $solicitudId = $this->conexion->lastInsertId();
            if (!$solicitudId) {
                throw new Exception('No se pudo obtener el ID de la solicitud');
            }

            /* --------------------------------------------
               2️⃣ Insertar adjuntos (si existen)
               -------------------------------------------- */
            if (!empty($data['adjuntos']) && is_array($data['adjuntos'])) {

                $sqlAdjunto = "INSERT INTO solicitud_adjuntos (
                                    solicitud_id,
                                    archivo,
                                    tipo_archivo,
                                    tamano
                               ) VALUES (
                                    :solicitud_id,
                                    :archivo,
                                    :tipo_archivo,
                                    :tamano
                               )";

                $stmtAdj = $this->conexion->prepare($sqlAdjunto);

                foreach ($data['adjuntos'] as $adjunto) {
                    $stmtAdj->execute([
                        ':solicitud_id' => $solicitudId,
                        ':archivo'      => $adjunto['archivo'],
                        ':tipo_archivo'         => $adjunto['tipo_archivo'],
                        ':tamano'       => $adjunto['tamano']
                    ]);
                }
            }

            // ✅ Confirmar todo
            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            error_log("Error SQL Solicitud::crear -> " . $e->getMessage());
            $this->conexion->rollBack();
            return false;
        }
    }

    /* ======================================================
       VALIDAR SOLICITUD DUPLICADA
       ====================================================== */
    public function tieneSolicitudActiva($clienteId, $publicacionId): bool
    {
        $sql = "SELECT COUNT(*) 
                FROM solicitudes
                WHERE cliente_id = ?
                  AND publicacion_id = ?
                  AND estado IN ('pendiente', 'aceptada')";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$clienteId, $publicacionId]);

        return $stmt->fetchColumn() > 0;
    }
}
