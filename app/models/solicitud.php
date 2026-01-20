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

    /**
     * Permite obtener la conexión para depuración externa
     */
    public function getDb()
    {
        return $this->conexion;
    }

    /* ======================================================
       CREAR SOLICITUD + ADJUNTOS
       ====================================================== */
    public function crear(array $data): bool
    {
        try {
            // 🔒 Iniciar transacción
            if (!$this->conexion->inTransaction()) {
                $this->conexion->beginTransaction();
            }

            $sqlCliente = "SELECT id FROM clientes WHERE usuario_id = :usuario_id";
            $stmtCliente = $this->conexion->prepare($sqlCliente);
            $stmtCliente->execute([
                ':usuario_id' => $data['usuario_id'] // ← este SÍ es usuarios.id
            ]);

            $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

            if (!$cliente) {
                throw new Exception('El usuario no está registrado como cliente');
            }

            $clienteId = $cliente['id'];


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
                ':cliente_id' => $clienteId,
                ':proveedor_id'         => $data['proveedor_id'],
                ':publicacion_id'       => $data['publicacion_id'],
                ':titulo'               => $data['titulo'],
                ':descripcion'          => $data['descripcion'],
                ':direccion'            => $data['direccion'],
                ':ciudad'               => $data['ciudad'],
                ':zona'                 => $data['zona'],
                ':fecha_preferida'      => $data['fecha_servicio'],
                ':franja_horaria'       => $data['franja_horaria'],
                ':presupuesto_estimado' => $data['presupuesto_estimado']
            ]);

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
                        ':tipo_archivo' => $adjunto['tipo_archivo'],
                        ':tamano'       => $adjunto['tamano']
                    ]);
                }
            }

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error en Solicitud::crear -> " . $e->getMessage());
            return false;
        }
    }

    /* ======================================================
       LISTAR SOLICITUDES PARA EL PROVEEDOR
       ====================================================== */
    public function listarPorProveedor(int $usuarioId): array
    {
        try {
            // Ajustamos el JOIN de clientes para que use usuario_id 
            // ya que la base de datos parece estar guardando ese valor en solicitudes.cliente_id
            $sql = "SELECT 
                        s.id,
                        s.titulo,
                        s.descripcion,
                        s.direccion,
                        s.ciudad,
                        s.zona,
                        s.fecha_preferida,
                        s.franja_horaria,
                        s.estado,
                        s.presupuesto_estimado AS presupuesto,
                        s.created_at,
                        -- Datos del Cliente (Usamos usuario_id para el JOIN por el desajuste detectado)
                        CONCAT(c.nombres, ' ', c.apellidos) AS nombre_cliente,
                        c.telefono AS telefono_cliente,
                        c.foto AS foto_cliente,
                        u_c.email AS email_cliente,
                        -- Datos de la Publicación
                        p.titulo AS publicacion_titulo,
                        ser.nombre AS servicio_nombre,
                        -- Archivos adjuntos
                        GROUP_CONCAT(sa.archivo) AS archivos_adjuntos
                    FROM solicitudes s
                    INNER JOIN proveedores pr ON s.proveedor_id = pr.id
                    
                    LEFT JOIN clientes c ON s.cliente_id = c.id 
                    LEFT JOIN usuarios u_c ON c.usuario_id = u_c.id
                    LEFT JOIN publicaciones p ON s.publicacion_id = p.id
                    LEFT JOIN servicios ser ON p.servicio_id = ser.id
                    LEFT JOIN solicitud_adjuntos sa ON sa.solicitud_id = s.id
                    WHERE pr.usuario_id = :usuario_id
                    GROUP BY s.id
                    ORDER BY s.created_at DESC";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':usuario_id' => $usuarioId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Solicitud::listarPorProveedor -> " . $e->getMessage());
            return [];
        }
    }

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
