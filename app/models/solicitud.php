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
     * Resuelve el ID real de la tabla 'clientes' a partir de un ID que puede ser de 'usuarios'
     * Basado en la estructura: clientes.usuario_id -> usuarios.id
     */
    private function obtenerClienteIdReal(int $idEntrada): ?int
    {
        // 1. Verificamos si el ID ya es un ID válido en la tabla clientes
        $stmt = $this->conexion->prepare("SELECT id FROM clientes WHERE id = ?");
        $stmt->execute([$idEntrada]);
        if ($stmt->fetch()) {
            return $idEntrada;
        }

        // 2. Si no es un ID de cliente, buscamos si es el ID de la tabla usuarios
        $stmt = $this->conexion->prepare("SELECT id FROM clientes WHERE usuario_id = ? LIMIT 1");
        $stmt->execute([$idEntrada]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? (int)$fila['id'] : null;
    }

    public function crear(array $data): bool
    {
        try {
            // SOLUCIÓN AL ERROR 1452:
            // Convertimos el ID que viene de la sesión (ej. 26) al ID de cliente (ej. 5)
            $clienteIdReal = $this->obtenerClienteIdReal((int)$data['cliente_id']);

            if (!$clienteIdReal) {
                throw new Exception("El usuario actual no tiene un perfil de cliente registrado en la base de datos.");
            }

            $this->conexion->beginTransaction();

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
                ':cliente_id'           => $clienteIdReal, // ID corregido (ej. 5)
                ':proveedor_id'         => $data['proveedor_id'],
                ':publicacion_id'       => $data['publicacion_id'],
                ':titulo'               => $data['titulo'],
                ':descripcion'          => $data['descripcion'],
                ':direccion'            => $data['direccion'],
                ':ciudad'               => $data['ciudad'],
                ':zona'                 => $data['zona'],
                ':fecha_preferida'      => $data['fecha_servicio'] ?? $data['fecha_preferida'],
                ':franja_horaria'       => $data['franja_horaria'],
                ':presupuesto_estimado' => $data['presupuesto_estimado']
            ]);

            $solicitudId = $this->conexion->lastInsertId();

            // Insertar adjuntos si existen
            if (!empty($data['adjuntos']) && is_array($data['adjuntos'])) {
                $sqlAdjunto = "INSERT INTO solicitud_adjuntos (solicitud_id, archivo, tipo_archivo, tamano) 
                               VALUES (:sid, :arc, :tip, :tam)";
                $stmtAdj = $this->conexion->prepare($sqlAdjunto);

                foreach ($data['adjuntos'] as $adjunto) {
                    $stmtAdj->execute([
                        ':sid' => $solicitudId,
                        ':arc' => $adjunto['archivo'],
                        ':tip' => $adjunto['tipo_archivo'],
                        ':tam' => $adjunto['tamano']
                    ]);
                }
            }

            $this->conexion->commit();
            return true;

        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error en Solicitud::crear -> " . $e->getMessage());
            // Re-lanzamos la excepción para que el controlador la maneje
            throw $e;
        }
    }

    // El resto de tus métodos (listarPorProveedor, aceptar, etc.) funcionan bien 
    // siempre que los JOINS usen s.cliente_id = c.id


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

            -- Datos del Cliente
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

    public function tieneSolicitudActiva($idEntrada, $publicacionId): bool
    {
        $clienteIdReal = $this->obtenerClienteIdReal((int)$idEntrada);
        
        if (!$clienteIdReal) return false;

        $sql = "SELECT COUNT(*) 
                FROM solicitudes
                WHERE cliente_id = ?
                  AND publicacion_id = ?
                  AND estado IN ('pendiente', 'aceptada')";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$clienteIdReal, $publicacionId]);

        return $stmt->fetchColumn() > 0;
    }

    /* ======================================================
       LISTAR SOLICITUDES POR CLIENTE (para “Servicios contratados”)
       ====================================================== */
    public function listarPorCliente(int $clienteId): array
    {
        try {
            $sql = "
                SELECT
                    s.id,
                    s.titulo,
                    s.descripcion,
                    s.estado,
                    s.fecha_preferida,
                    s.franja_horaria,
                    s.ciudad,
                    s.zona,
                    s.presupuesto_estimado,
                    s.publicacion_id,

                    -- Datos del servicio / publicación
                    p.titulo              AS publicacion_titulo,
                    sv.nombre             AS servicio_nombre,
                    sv.imagen             AS servicio_imagen,

                    -- Datos del proveedor
                    u.nombre              AS proveedor_nombre

                FROM solicitudes s
                INNER JOIN publicaciones p  ON s.publicacion_id = p.id
                LEFT JOIN servicios sv      ON p.servicio_id    = sv.id
                INNER JOIN proveedores pr   ON s.proveedor_id   = pr.id
                INNER JOIN usuarios u       ON pr.usuario_id    = u.id

                WHERE s.cliente_id = :cliente_id
                ORDER BY s.fecha_preferida DESC, s.id DESC
            ";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cliente_id', $clienteId, PDO::PARAM_INT);
            $stmt->execute();

            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $filas ?: [];
        } catch (PDOException $e) {
            error_log("Error en Solicitud::listarPorCliente -> " . $e->getMessage());

    public function aceptar(int $solicitudId, int $proveedorUsuarioId): bool
    {
        try {
            $this->conexion->beginTransaction();

            // 1. Actualizar el estado de la solicitud
            // Verificamos que pertenezca al proveedor y esté pendiente
            $sqlUpd = "UPDATE solicitudes s
                       INNER JOIN proveedores p ON s.proveedor_id = p.id
                       SET s.estado = 'aceptada'
                       WHERE s.id = :id 
                         AND p.usuario_id = :usuario 
                         AND s.estado = 'pendiente'";

            $stmtUpd = $this->conexion->prepare($sqlUpd);
            $stmtUpd->execute([
                ':id' => $solicitudId,
                ':usuario' => $proveedorUsuarioId
            ]);

            // Si no se afectaron filas, la solicitud no existe o no es del proveedor
            if ($stmtUpd->rowCount() === 0) {
                throw new Exception("La solicitud no pudo ser aceptada (ya aceptada o no autorizada).");
            }

            // 2. Obtener los datos de la solicitud para insertarlos en servicios_contratados
            $sqlData = "SELECT cliente_id, proveedor_id, publicacion_id, presupuesto_estimado, fecha_preferida 
                        FROM solicitudes 
                        WHERE id = ?";
            $stmtData = $this->conexion->prepare($sqlData);
            $stmtData->execute([$solicitudId]);
            $solicitud = $stmtData->fetch(PDO::FETCH_ASSOC);

            if (!$solicitud) {
                throw new Exception("No se pudieron recuperar los datos de la solicitud.");
            }

            // 3. Insertar en servicios_contratados
            // Agregamos solicitud_id para mantener el vínculo
            $sqlIns = "INSERT INTO servicios_contratados (
                            solicitud_id,
                            cotizacion_id,
                            cliente_id,
                            proveedor_id,
                            servicio_id,
                            fecha_solicitud,
                            estado
                        ) VALUES (
                            :solicitud_id,
                            NULL,
                            :cliente_id,
                            :proveedor_id,
                            :servicio_id,
                            :fecha,
                            'en_proceso'
                        )";


            $stmtIns = $this->conexion->prepare($sqlIns);
            $stmtIns->execute([
                ':solicitud_id' => $solicitudId,
                ':cliente_id'   => $solicitud['cliente_id'],
                ':proveedor_id' => $solicitud['proveedor_id'],
                ':servicio_id'  => $solicitud['publicacion_id'], // referencia funcional
                ':fecha'        => $solicitud['fecha_preferida']
            ]);


            $this->conexion->commit();
            return true;

        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error en Solicitud::aceptar -> " . $e->getMessage());
            return false;
        }
    }

    public function rechazar(int $solicitudId, int $proveedorUsuarioId): bool
    {
        try {
            $sql = "UPDATE solicitudes s
                INNER JOIN proveedores p ON s.proveedor_id = p.id
                SET s.estado = 'rechazada'
                WHERE s.id = :id
                  AND p.usuario_id = :usuario
                  AND s.estado = 'pendiente'";

            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute([
                ':id' => $solicitudId,
                ':usuario' => $proveedorUsuarioId
            ]);
        } catch (PDOException $e) {
            error_log("Solicitud::rechazar -> " . $e->getMessage());
            return false;
        }
    }

    public function obtenerDetalle(int $id): array
    {
        try {
            $sql = "SELECT 
                    s.*,
                    CONCAT(c.nombres, ' ', c.apellidos) AS cliente,
                    c.telefono,
                    c.ubicacion,
                    u.email AS email_cliente,
                    p.titulo AS publicacion,
                    p.descripcion AS descripcion_publicacion
                FROM solicitudes s
                LEFT JOIN clientes c ON s.cliente_id = c.id
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                LEFT JOIN publicaciones p ON s.publicacion_id = p.id
                WHERE s.id = :id
                LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $id]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Solicitud::obtenerDetalle -> " . $e->getMessage());
            return [];
        }
    }
}
