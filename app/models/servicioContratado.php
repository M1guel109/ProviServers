<?php
require_once __DIR__ . '/../../config/database.php';

class ServicioContratado
{
    private PDO $db;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->db = $conexion->getConexion();
    }

    /**
     * Servicios contratados visibles para el PROVEEDOR
     * (panel "En proceso" del proveedor)
     */
    public function listarPorProveedorUsuario(int $usuarioId): array
    {
        $sql = "
    SELECT
        sc.id AS contrato_id,
        sc.solicitud_id,
        sc.cotizacion_id,
        sc.estado,
        sc.fecha_solicitud,
        sc.fecha_ejecucion,
        sc.fecha_limite,
        sc.motivo_cancelacion,
        sc.created_at,
        sc.modified_at,

        sv.id AS servicio_id,
        sv.nombre AS servicio_nombre,
        sv.imagen AS servicio_imagen,

        CONCAT(cl.nombres, ' ', cl.apellidos) AS cliente_nombre,
        cl.telefono AS cliente_telefono,
        cl.foto AS cliente_foto,

        -- Flujo por solicitud
        sol.titulo AS solicitud_titulo,
        sol.descripcion AS solicitud_descripcion,
        sol.direccion AS solicitud_direccion,
        sol.fecha_preferida AS solicitud_fecha_preferida,
        sol.franja_horaria AS solicitud_franja_horaria,
        sol.ciudad AS solicitud_ciudad,
        sol.zona AS solicitud_zona,
        sol.presupuesto_estimado AS solicitud_presupuesto_estimado,

        pub_sol.id AS publicacion_id_solicitud,
        pub_sol.titulo AS publicacion_titulo_solicitud,

        -- Flujo por cotización / necesidad
        cot.titulo AS cotizacion_titulo,
        cot.mensaje AS cotizacion_mensaje,
        cot.precio AS cotizacion_precio,
        cot.tiempo_estimado AS cotizacion_tiempo_estimado,

        nec.titulo AS necesidad_titulo,
        nec.descripcion AS necesidad_descripcion,
        nec.direccion AS necesidad_direccion,
        nec.fecha_preferida AS necesidad_fecha_preferida,
        nec.franja_horaria AS necesidad_franja_horaria,
        nec.ciudad AS necesidad_ciudad,
        nec.zona AS necesidad_zona,
        nec.presupuesto_estimado AS necesidad_presupuesto_estimado,

        pub_cot.id AS publicacion_id_cotizacion,
        pub_cot.titulo AS publicacion_titulo_cotizacion,

        -- Valoración real del servicio
        val.id AS valoracion_id,
        val.calificacion AS mi_calificacion,
        val.comentario AS mi_comentario,
        val.respuesta_proveedor AS mi_respuesta_valoracion,
        val.fecha_respuesta AS mi_fecha_respuesta_valoracion,
        val.created_at AS valoracion_fecha

    FROM servicios_contratados sc
    INNER JOIN proveedores prf
        ON prf.id = sc.proveedor_id
    INNER JOIN servicios sv
        ON sv.id = sc.servicio_id
    INNER JOIN clientes cl
        ON cl.id = sc.cliente_id

    LEFT JOIN solicitudes sol
        ON sol.id = sc.solicitud_id
    LEFT JOIN publicaciones pub_sol
        ON pub_sol.id = sol.publicacion_id

    LEFT JOIN cotizaciones cot
        ON cot.id = sc.cotizacion_id
    LEFT JOIN necesidades nec
        ON nec.id = cot.necesidad_id
    LEFT JOIN publicaciones pub_cot
        ON pub_cot.id = cot.publicacion_id

    LEFT JOIN valoraciones val
        ON val.servicio_contratado_id = sc.id

    WHERE prf.usuario_id = :usuario_id
    ORDER BY sc.created_at DESC, sc.id DESC
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * 🔹 NUEVO: Servicios contratados visibles para el CLIENTE
     * (vista “Servicios contratados” del panel cliente)
     *
     * Recibe el id de la tabla usuarios (el que guardas en $_SESSION['user']['id'])
     */
    public function listarPorClienteUsuario(int $usuarioId): array
    {
        $sql = "
        SELECT
            sc.id AS contrato_id,
            sc.solicitud_id,
            sc.cotizacion_id,
            sc.estado,
            sc.fecha_solicitud,
            sc.fecha_ejecucion,
            sc.fecha_limite,
            sc.motivo_cancelacion,

            sv.nombre AS servicio_nombre,
            sv.imagen AS servicio_imagen,

            CONCAT(pr.nombres, ' ', pr.apellidos) AS proveedor_nombre,

            -- Flujo por solicitud
            sol.titulo AS solicitud_titulo,
            sol.descripcion AS solicitud_descripcion,
            sol.fecha_preferida AS solicitud_fecha_preferida,
            sol.franja_horaria AS solicitud_franja_horaria,
            sol.ciudad AS solicitud_ciudad,
            sol.zona AS solicitud_zona,
            sol.presupuesto_estimado AS solicitud_presupuesto_estimado,

            pub_sol.titulo AS publicacion_titulo_solicitud,

            -- Flujo por cotización / necesidad
            cot.titulo AS cotizacion_titulo,
            cot.mensaje AS cotizacion_mensaje,
            cot.precio AS cotizacion_precio,
            cot.tiempo_estimado AS cotizacion_tiempo_estimado,

            nec.titulo AS necesidad_titulo,
            nec.descripcion AS necesidad_descripcion,
            nec.fecha_preferida AS necesidad_fecha_preferida,
            nec.franja_horaria AS necesidad_franja_horaria,
            nec.ciudad AS necesidad_ciudad,
            nec.zona AS necesidad_zona,
            nec.presupuesto_estimado AS necesidad_presupuesto_estimado,

            pub_cot.titulo AS publicacion_titulo_cotizacion,

            CASE WHEN v.id IS NULL THEN 0 ELSE 1 END AS tiene_valoracion,
            v.calificacion AS mi_calificacion,
            v.comentario AS mi_comentario,
            v.created_at AS mi_calificado_en

        FROM servicios_contratados sc
        INNER JOIN clientes c
            ON sc.cliente_id = c.id
        INNER JOIN usuarios u
            ON c.usuario_id = u.id
        INNER JOIN servicios sv
            ON sc.servicio_id = sv.id
        INNER JOIN proveedores pr
            ON sc.proveedor_id = pr.id

        LEFT JOIN solicitudes sol
            ON sc.solicitud_id = sol.id
        LEFT JOIN publicaciones pub_sol
            ON sol.publicacion_id = pub_sol.id

        LEFT JOIN cotizaciones cot
            ON sc.cotizacion_id = cot.id
        LEFT JOIN necesidades nec
            ON cot.necesidad_id = nec.id
        LEFT JOIN publicaciones pub_cot
            ON cot.publicacion_id = pub_cot.id

        LEFT JOIN valoraciones v
            ON v.servicio_contratado_id = sc.id
           AND v.cliente_id = sc.cliente_id

        WHERE u.id = :usuario_id
        ORDER BY sc.created_at DESC, sc.id DESC
      ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


    /**
     * Actualizar estado del contrato (para proveedor, o luego cliente)
     */
    public function actualizarEstado(int $contratoId, string $nuevoEstado): bool
    {
        $estadosPermitidos = [
            'pendiente',
            'confirmado',
            'en_proceso',
            'finalizado',
            'cancelado',
            'cancelado_cliente',
            'cancelado_proveedor'
        ];

        if (!in_array($nuevoEstado, $estadosPermitidos, true)) {
            return false;
        }

        $sql = "UPDATE servicios_contratados
            SET estado = ?, modified_at = NOW()
            WHERE id = ?
            LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nuevoEstado, $contratoId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Verificar si un contrato pertenece al PROVEEDOR logueado
     */
    public function contratoPerteneceAProveedor(int $contratoId, int $usuarioId): bool
    {
        $sql = "SELECT 1
                FROM servicios_contratados sc
                INNER JOIN proveedores p ON sc.proveedor_id = p.id
                WHERE sc.id = :contrato_id
                  AND p.usuario_id = :usuario_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':contrato_id', $contratoId, PDO::PARAM_INT);
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    /**
     * 🔹 (Opcional, pero útil) Verificar si un contrato pertenece al CLIENTE logueado
     * para cuando quieras permitir que el cliente vea detalle o cancele.
     */
    public function contratoPerteneceACliente(int $contratoId, int $usuarioId): bool
    {
        $sql = "SELECT 1
                FROM servicios_contratados sc
                INNER JOIN clientes c ON sc.cliente_id = c.id
                WHERE sc.id = :contrato_id
                  AND c.usuario_id = :usuario_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':contrato_id', $contratoId, PDO::PARAM_INT);
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    public function cancelarPorClienteUsuario(int $contratoId, int $usuarioId): bool
    {
        $sql = "
        UPDATE servicios_contratados sc
        INNER JOIN clientes c ON sc.cliente_id = c.id
        SET sc.estado = 'cancelado_cliente',
            sc.modified_at = NOW()
        WHERE sc.id = :contrato_id
          AND c.usuario_id = :usuario_id
          AND sc.estado IN ('pendiente','confirmado')
        LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':contrato_id' => $contratoId,
            ':usuario_id'  => $usuarioId
        ]);

        return $stmt->rowCount() > 0;
    }

    public function actualizarEstadoDesdeSeguimiento(int $contratoId, int $usuarioId, string $estado): bool
    {
        $proveedorId = $this->obtenerProveedorIdPorUsuario($usuarioId);
        if (!$proveedorId) {
            return false;
        }

        $sql = "UPDATE servicios_contratados
            SET estado = ?, modified_at = NOW()
            WHERE id = ? AND proveedor_id = ?
            LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$estado, $contratoId, $proveedorId]);

        return $stmt->rowCount() > 0;
    }

    private function obtenerProveedorIdPorUsuario(int $usuarioId): ?int
    {
        $stmt = $this->db->prepare("SELECT id FROM proveedores WHERE usuario_id = ? LIMIT 1");
        $stmt->execute([$usuarioId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    }


    public function obtenerResumenDashboardProveedor(int $usuarioId): array
    {
        $sql = "
        SELECT
            COALESCE(SUM(
                CASE
                    WHEN sc.estado = 'finalizado'
                     AND DATE_FORMAT(
                        COALESCE(sc.fecha_ejecucion, sc.modified_at, sc.created_at),
                        '%Y-%m'
                     ) = DATE_FORMAT(CURDATE(), '%Y-%m')
                    THEN COALESCE(cot.precio, 0)
                    ELSE 0
                END
            ), 0) AS ingresos_mes,

            SUM(
                CASE
                    WHEN sc.estado IN ('pendiente', 'confirmado', 'en_proceso')
                    THEN 1 ELSE 0
                END
            ) AS servicios_activos,

            ROUND(AVG(val.calificacion), 1) AS calificacion_promedio

        FROM servicios_contratados sc
        INNER JOIN proveedores prf
            ON prf.id = sc.proveedor_id
        LEFT JOIN cotizaciones cot
            ON cot.id = sc.cotizacion_id
        LEFT JOIN valoraciones val
            ON val.servicio_contratado_id = sc.id
        WHERE prf.usuario_id = :usuario_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'ingresos_mes' => (float)($data['ingresos_mes'] ?? 0),
            'servicios_activos' => (int)($data['servicios_activos'] ?? 0),
            'calificacion_promedio' => $data['calificacion_promedio'] !== null
                ? (float)$data['calificacion_promedio']
                : null,
        ];
    }

    public function obtenerServiciosRecientesProveedor(int $usuarioId, int $limite = 4): array
    {
        $limite = max(1, (int)$limite);

        $sql = "
        SELECT
            sc.id AS contrato_id,
            sc.estado,
            sc.fecha_solicitud,
            sc.fecha_ejecucion,
            sv.nombre AS servicio_nombre,
            sv.imagen AS servicio_imagen,
            sv.categoria_id,
            cat.nombre AS categoria_nombre
        FROM servicios_contratados sc
        INNER JOIN proveedores prf
            ON prf.id = sc.proveedor_id
        INNER JOIN servicios sv
            ON sv.id = sc.servicio_id
        LEFT JOIN categorias cat
            ON cat.id = sv.categoria_id
        WHERE prf.usuario_id = :usuario_id
        ORDER BY sc.created_at DESC, sc.id DESC
        LIMIT {$limite}
     ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerResenasRecientesProveedor(int $usuarioId, int $limite = 5): array
    {
        $limite = max(1, (int)$limite);

        $sql = "
        SELECT
            val.id,
            val.calificacion,
            val.comentario,
            val.created_at,
            CONCAT(cl.nombres, ' ', cl.apellidos) AS cliente_nombre
        FROM valoraciones val
        INNER JOIN servicios_contratados sc
            ON sc.id = val.servicio_contratado_id
        INNER JOIN proveedores prf
            ON prf.id = sc.proveedor_id
        INNER JOIN clientes cl
            ON cl.id = val.cliente_id
        WHERE prf.usuario_id = :usuario_id
        ORDER BY val.created_at DESC, val.id DESC
        LIMIT {$limite}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerProximasCitasProveedor(int $usuarioId, int $limite = 5): array
    {
        $limite = max(1, (int)$limite);

        $sql = "
        SELECT
            sc.id AS contrato_id,
            sc.fecha_ejecucion,
            sv.nombre AS servicio_nombre,
            CONCAT(cl.nombres, ' ', cl.apellidos) AS cliente_nombre,
            COALESCE(
                sol.franja_horaria,
                nec.franja_horaria
            ) AS franja_horaria
        FROM servicios_contratados sc
        INNER JOIN proveedores prf
            ON prf.id = sc.proveedor_id
        INNER JOIN servicios sv
            ON sv.id = sc.servicio_id
        INNER JOIN clientes cl
            ON cl.id = sc.cliente_id
        LEFT JOIN solicitudes sol
            ON sol.id = sc.solicitud_id
        LEFT JOIN cotizaciones cot
            ON cot.id = sc.cotizacion_id
        LEFT JOIN necesidades nec
            ON nec.id = cot.necesidad_id
        WHERE prf.usuario_id = :usuario_id
          AND sc.estado IN ('pendiente', 'confirmado', 'en_proceso')
          AND sc.fecha_ejecucion IS NOT NULL
          AND sc.fecha_ejecucion >= CURDATE()
        ORDER BY sc.fecha_ejecucion ASC, sc.id ASC
        LIMIT {$limite}
     ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
