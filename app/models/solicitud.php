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
        // Primero asumimos que el ID que llega es usuarios.id (lo normal en sesión)
        $stmt = $this->conexion->prepare("SELECT id FROM clientes WHERE usuario_id = ? LIMIT 1");
        $stmt->execute([$idEntrada]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fila) {
            return (int)$fila['id'];
        }

        // Si no existe por usuario_id, permitimos que sea clientes.id (caso raro/compatibilidad)
        $stmt = $this->conexion->prepare("SELECT id FROM clientes WHERE id = ? LIMIT 1");
        $stmt->execute([$idEntrada]);
        $fila2 = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila2 ? (int)$fila2['id'] : null;
    }


    /* ======================================================
       CREAR SOLICITUD + ADJUNTOS
       ====================================================== */
    public function crear(array $data): bool
    {
        try {
            // Convertimos ID de sesión (usuarios.id) a clientes.id
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
                ':cliente_id'           => $clienteIdReal, // ID de clientes.id
                ':proveedor_id'         => $data['proveedor_id'],
                ':publicacion_id'       => $data['publicacion_id'],
                ':titulo'               => $data['titulo'],
                ':descripcion'          => $data['descripcion'],
                ':direccion'            => $data['direccion'],
                ':ciudad'               => $data['ciudad'],
                ':zona'                 => $data['zona'],
                ':fecha_preferida'      => $data['fecha_servicio'] ?? $data['fecha_preferida'] ?? null,
                ':franja_horaria'       => $data['franja_horaria'],
                ':presupuesto_estimado' => $data['presupuesto_estimado']
            ]);

            $solicitudId = $this->conexion->lastInsertId();
            if (!$solicitudId) {
                throw new Exception("No se pudo obtener el ID de la solicitud recién creada.");
            }

            // Insertar adjuntos si existen
            if (!empty($data['adjuntos']) && is_array($data['adjuntos'])) {
                $sqlAdjunto = "INSERT INTO solicitud_adjuntos (
                                   solicitud_id,
                                   archivo,
                                   tipo_archivo,
                                   tamano
                               ) VALUES (
                                   :sid,
                                   :arc,
                                   :tip,
                                   :tam
                               )";

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
        } catch (PDOException $e) {
            error_log("Error SQL en Solicitud::crear -> " . $e->getMessage());
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            return false;
        } catch (Exception $e) {
            error_log("Error en Solicitud::crear -> " . $e->getMessage());
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            return false;
        }
    }

    /* ======================================================
       LISTAR SOLICITUDES PARA EL PROVEEDOR
       ====================================================== */
    public function listarPorProveedor(int $usuarioId): array
    {
        try {
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
                    LEFT JOIN clientes c      ON s.cliente_id = c.id
                    LEFT JOIN usuarios u_c    ON c.usuario_id = u_c.id
                    LEFT JOIN publicaciones p ON s.publicacion_id = p.id
                    LEFT JOIN servicios ser   ON p.servicio_id = ser.id
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

    /* ======================================================
       VALIDAR SOLICITUD DUPLICADA
       (recibe ID de sesión y lo mapea a clientes.id)
       ====================================================== */
    public function tieneSolicitudActiva($idEntrada, $publicacionId): bool
    {
        $clienteIdReal = $this->obtenerClienteIdReal((int)$idEntrada);
        if (!$clienteIdReal) {
            return false;
        }

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
       LISTAR SOLICITUDES POR CLIENTE (Servicios contratados)
       Recibe ID de sesión (usuarios.id) y lo mapea a clientes.id
       ====================================================== */
    public function listarPorCliente(int $idEntrada): array
    {
        try {
            $clienteIdReal = $this->obtenerClienteIdReal($idEntrada);
            if (!$clienteIdReal) {
                return [];
            }

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
            $stmt->bindParam(':cliente_id', $clienteIdReal, PDO::PARAM_INT);
            $stmt->execute();

            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $filas ?: [];
        } catch (PDOException $e) {
            error_log("Error en Solicitud::listarPorCliente -> " . $e->getMessage());
            return [];
        }
    }

    /* ======================================================
       ACEPTAR SOLICITUD (Proveedor)
       ====================================================== */
    public function aceptar(int $solicitudId, int $proveedorUsuarioId): bool
    {
        try {
            $this->conexion->beginTransaction();

            // 1. Actualizar el estado de la solicitud (validando que pertenezca a este proveedor)
            $sqlUpd = "UPDATE solicitudes s
                   INNER JOIN proveedores p ON s.proveedor_id = p.id
                   SET s.estado = 'aceptada'
                   WHERE s.id = :id 
                     AND p.usuario_id = :usuario 
                     AND s.estado = 'pendiente'";

            $stmtUpd = $this->conexion->prepare($sqlUpd);
            $stmtUpd->execute([
                ':id'      => $solicitudId,
                ':usuario' => $proveedorUsuarioId
            ]);

            if ($stmtUpd->rowCount() === 0) {
                throw new Exception("La solicitud no pudo ser aceptada (ya aceptada, inexistente o no pertenece a este proveedor).");
            }

            // 2. Obtener datos completos de la solicitud + publicación + servicio
            $sqlData = "SELECT 
                        s.cliente_id,
                        s.proveedor_id,
                        s.publicacion_id,
                        s.presupuesto_estimado,
                        s.fecha_preferida,
                        p.servicio_id
                    FROM solicitudes s
                    INNER JOIN publicaciones p ON s.publicacion_id = p.id
                    WHERE s.id = ? 
                    LIMIT 1";

            $stmtData = $this->conexion->prepare($sqlData);
            $stmtData->execute([$solicitudId]);
            $solicitud = $stmtData->fetch(PDO::FETCH_ASSOC);

            if (!$solicitud) {
                throw new Exception("No se pudieron recuperar los datos de la solicitud.");
            }

            if (empty($solicitud['servicio_id'])) {
                throw new Exception("La publicación asociada a la solicitud no tiene un servicio vinculado.");
            }

            // 3. Insertar en servicios_contratados (ahora con servicio_id correcto)
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
                ':servicio_id'  => $solicitud['servicio_id'], // <- AHORA SÍ el ID real de servicios
                ':fecha'        => $solicitud['fecha_preferida']
            ]);

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }

            // 🔴 LOG para el servidor
            error_log("Error en Solicitud::aceptar -> " . $e->getMessage());

            // 🔴 OPCIONAL: mientras depuras, puedes ver el error en pantalla así:
            // OJO: usa esto solo en desarrollo, no en producción.
            echo "<pre>Error en Solicitud::aceptar:\n" . htmlspecialchars($e->getMessage()) . "</pre>";
            exit;

            // Si no quieres mostrarlo en pantalla, comenta las 2 líneas anteriores y deja solo:
            // return false;
        }
    }


    /* ======================================================
       RECHAZAR SOLICITUD (Proveedor)
       ====================================================== */
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
                ':id'      => $solicitudId,
                ':usuario' => $proveedorUsuarioId
            ]);
        } catch (PDOException $e) {
            error_log("Solicitud::rechazar -> " . $e->getMessage());
            return false;
        }
    }

    /* ======================================================
       OBTENER DETALLE DE UNA SOLICITUD
       ====================================================== */
    public function obtenerDetalle(int $id): array
    {
        try {
            $sql = "SELECT 
                        s.*,
                        CONCAT(c.nombres, ' ', c.apellidos) AS cliente,
                        c.telefono,
                        c.ubicacion,
                        u.email AS email_cliente,
                        p.titulo      AS publicacion,
                        p.descripcion AS descripcion_publicacion
                    FROM solicitudes s
                    LEFT JOIN clientes c   ON s.cliente_id = c.id
                    LEFT JOIN usuarios u   ON c.usuario_id = u.id
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



    /**
     * Lista servicios contratados para el cliente logueado (usuario_id).
     * Usa servicios_contratados como tabla principal.
     */
    public function listarContratosPorClienteUsuario(int $usuarioId): array
    {
        try {
            // Convertimos usuario_id -> clientes.id
            $clienteId = $this->obtenerClienteIdReal($usuarioId);
            if (!$clienteId) {
                return [];
            }

            $sql = "
                SELECT
                    sc.id                    AS contrato_id,
                    sc.estado                AS estado_contrato,
                    sc.fecha_solicitud,
                    sc.fecha_ejecucion,

                    s.id                     AS solicitud_id,
                    s.titulo                 AS solicitud_titulo,
                    s.descripcion            AS solicitud_descripcion,
                    s.fecha_preferida,
                    s.franja_horaria,
                    s.ciudad,
                    s.zona,

                    p.id                     AS publicacion_id,
                    p.titulo                 AS publicacion_titulo,

                    sv.id                    AS servicio_id,
                    sv.nombre                AS servicio_nombre,
                    sv.imagen                AS servicio_imagen,

                    pr.id                    AS proveedor_id,
                    CONCAT(pr.nombres, ' ', pr.apellidos) AS proveedor_nombre,
                    pr.ubicacion             AS proveedor_ubicacion

                FROM servicios_contratados sc
                INNER JOIN solicitudes   s  ON sc.solicitud_id = s.id
                INNER JOIN publicaciones p  ON s.publicacion_id = p.id
                INNER JOIN servicios     sv ON p.servicio_id    = sv.id
                INNER JOIN proveedores   pr ON sc.proveedor_id  = pr.id

                WHERE sc.cliente_id = :cliente_id
                ORDER BY sc.created_at DESC
            ";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':cliente_id', $clienteId, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $rows ?: [];
        } catch (PDOException $e) {
            error_log("Error en Solicitud::listarContratosPorClienteUsuario -> " . $e->getMessage());
            return [];
        }
    }
}
