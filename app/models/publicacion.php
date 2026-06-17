<?php
// app/models/Publicacion.php

require_once __DIR__ . '/../../config/database.php';

class Publicacion
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    /**
     * Obtiene el id del proveedor a partir del usuario_id (tabla proveedores).
     */
    private function obtenerProveedorIdPorUsuario(int $usuarioId): ?int
    {
        $sql = "SELECT id 
                FROM proveedores 
                WHERE usuario_id = :usuario_id 
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ? (int) $fila['id'] : null;
    }

    /**
     * Crea una publicación para un servicio recién registrado por un proveedor.
     * Si el proveedor tiene auto_aprobacion_activa = 1, la publica directo como 'aprobado'.
     * Si no, la deja en 'pendiente' para revisión del admin.
     *
     * @param int   $usuarioId  ID del usuario (tabla usuarios)
     * @param int   $servicioId ID del servicio recién creado
     * @param array $data       ['nombre', 'descripcion', 'precio']
     * @return bool
     */
    public function crearParaServicioDeProveedor(int $usuarioId, int $servicioId, array $data): bool
    {
        try {
            $this->conexion->beginTransaction();

            // 1. Obtener el proveedor_id y verificar si tiene auto-aprobación activa
            $sqlProv = "SELECT id, auto_aprobacion_activa 
                    FROM proveedores 
                    WHERE usuario_id = :usuario_id 
                    LIMIT 1";

            $stmtProv = $this->conexion->prepare($sqlProv);
            $stmtProv->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmtProv->execute();
            $proveedor = $stmtProv->fetch(PDO::FETCH_ASSOC);

            // Si no se encuentra el proveedor, cancelamos
            if (!$proveedor) {
                $this->conexion->rollBack();
                error_log("Publicacion::crearParaServicioDeProveedor — proveedor no encontrado para usuario_id {$usuarioId}");
                return false;
            }

            $proveedorId     = (int) $proveedor['id'];
            $esVip           = (int) $proveedor['auto_aprobacion_activa'] === 1;

            // 2. Determinar estado según reputación del proveedor
            // VIP (>= 3 publicaciones aprobadas) → aprobado directo
            // Nuevo proveedor → pendiente de revisión del admin
            $estadoPublicacion = $esVip ? 'aprobado' : 'pendiente';

            // 3. Insertar la publicación
            $sql = "INSERT INTO publicaciones (
                    tipo_publicacion,
                    proveedor_id,
                    servicio_id,
                    titulo,
                    descripcion,
                    precio,
                    estado,
                    fecha_publicacion,
                    created_at
                ) VALUES (
                    'proveedor',
                    :proveedor_id,
                    :servicio_id,
                    :titulo,
                    :descripcion,
                    :precio,
                    :estado,
                    NOW(),
                    NOW()
                )";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':proveedor_id', $proveedorId,           PDO::PARAM_INT);
            $stmt->bindParam(':servicio_id',  $servicioId,            PDO::PARAM_INT);
            $stmt->bindValue(':titulo',       $data['nombre']         ?? '');
            $stmt->bindValue(':descripcion',  $data['descripcion']    ?? '');
            $stmt->bindValue(':precio',       $data['precio']         ?? 0);
            $stmt->bindParam(':estado',       $estadoPublicacion,     PDO::PARAM_STR);
            $stmt->execute();

            // 4. Si es VIP y se aprobó automáticamente, sumar al contador de reputación
            if ($esVip) {
                $sqlCount = "UPDATE proveedores 
                         SET publicaciones_aprobadas_count = publicaciones_aprobadas_count + 1 
                         WHERE id = :proveedor_id";
                $stmtCount = $this->conexion->prepare($sqlCount);
                $stmtCount->bindParam(':proveedor_id', $proveedorId, PDO::PARAM_INT);
                $stmtCount->execute();
            }

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            $this->conexion->rollBack();
            error_log("Error en Publicacion::crearParaServicioDeProveedor -> " . $e->getMessage());
            return false;
        }
    }
    /**
     * Lista todas las publicaciones asociadas a un proveedor (para el panel del proveedor).
     * Retorna alias pensados para la vista:
     *  - servicio_imagen
     *  - servicio_nombre
     *  - servicio_descripcion
     *  - servicio_disponible
     *  - categoria_nombre
     *  - estado_publicacion
     *  - publicacion_created_at
     */
    public function listarPorProveedorUsuario(int $usuarioId): array
    {
        try {
            $proveedorId = $this->obtenerProveedorIdPorUsuario($usuarioId);

            if (!$proveedorId) return [];

            $sql = "
            SELECT 
                pub.id                  AS publicacion_id,
                pub.servicio_id         AS servicio_id,
                pub.titulo              AS titulo,
                pub.descripcion         AS descripcion,
                pub.precio              AS precio,
                pub.estado              AS estado_publicacion,
                pub.motivo_rechazo      AS motivo_rechazo,
                pub.fecha_publicacion   AS fecha_publicacion,
                pub.created_at          AS publicacion_created_at,

                s.nombre                AS servicio_nombre,
                s.descripcion           AS servicio_descripcion,
                s.imagen                AS servicio_imagen,
                s.disponibilidad        AS servicio_disponible,
                s.precio                AS servicio_precio,

                c.nombre                AS categoria_nombre,

                promo.porcentaje_descuento AS promo_descuento,
                promo.fecha_fin            AS promo_hasta,
                ROUND(pub.precio * (1 - COALESCE(promo.porcentaje_descuento, 0) / 100)) AS precio_con_descuento

            FROM publicaciones AS pub
            INNER JOIN servicios  AS s ON pub.servicio_id = s.id
            LEFT  JOIN categorias AS c ON s.id_categoria  = c.id
            LEFT  JOIN promociones promo
                ON promo.publicacion_id = pub.id
                AND promo.fecha_inicio <= CURDATE()
                AND promo.fecha_fin    >= CURDATE()
            WHERE pub.proveedor_id = :proveedor_id
            ORDER BY pub.fecha_publicacion DESC, pub.id DESC
        ";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':proveedor_id', $proveedorId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error en Publicacion::listarPorProveedorUsuario -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista publicaciones públicas ACTIVAS/APROBADAS para el catálogo del cliente.
     * Opcionalmente filtra por búsqueda y/o categoría.
     */
    // app/models/Publicacion.php

    public function listarPublicasActivas(
        ?string $busqueda       = null,
        ?int    $categoriaId    = null,
        ?string $ciudad         = null,
        ?float  $precioMax      = null,
        string  $orden          = 'recientes',
        bool    $soloOfertas    = false,
        ?float  $calificacionMin = null
    ): array {
        try {
            $sql = "
            SELECT
                pub.id,
                pub.servicio_id,
                pub.titulo,
                pub.descripcion,
                pub.precio,
                pub.estado,
                pub.created_at,

                s.nombre       AS servicio_nombre,
                s.descripcion  AS servicio_descripcion,
                s.imagen       AS servicio_imagen,

                c.nombre       AS categoria_nombre,

                CONCAT(pr.nombres, ' ', pr.apellidos) AS proveedor_nombre,
                COALESCE(pp.foto, pr.foto)            AS proveedor_foto,
                pp.ciudad                             AS proveedor_ciudad,
                pp.zona                               AS proveedor_zona,
                pp.latitud                            AS proveedor_lat,
                pp.longitud                           AS proveedor_lng,

                COALESCE(AVG(v.calificacion), 0) AS calificacion_promedio,
                COUNT(v.id)                      AS total_resenas,

                promo.porcentaje_descuento       AS promo_descuento,
                promo.fecha_fin                  AS promo_hasta,
                ROUND(pub.precio * (1 - COALESCE(promo.porcentaje_descuento, 0) / 100)) AS precio_con_descuento

            FROM publicaciones AS pub
            INNER JOIN servicios      AS s   ON pub.servicio_id  = s.id
            LEFT  JOIN categorias     AS c   ON s.id_categoria   = c.id
            INNER JOIN proveedores    AS pr  ON pub.proveedor_id = pr.id
            LEFT  JOIN proveedor_perfil AS pp ON pp.id_usuario   = pr.usuario_id
            LEFT  JOIN valoraciones   AS v   ON v.proveedor_id   = pr.id
            LEFT  JOIN promociones promo
                ON promo.publicacion_id = pub.id
                AND promo.fecha_inicio <= CURDATE()
                AND promo.fecha_fin    >= CURDATE()
            WHERE pub.estado = 'aprobado'
              AND s.disponibilidad = 1
        ";

            $params = [];

            if (!empty($busqueda)) {
                $sql .= " AND (
                    pub.titulo       LIKE :busqueda
                    OR s.nombre      LIKE :busqueda
                    OR s.descripcion LIKE :busqueda
                    OR c.nombre      LIKE :busqueda
                    OR pp.ciudad     LIKE :busqueda
                    OR pp.zona       LIKE :busqueda
                )";
                $params[':busqueda'] = '%' . $busqueda . '%';
            }

            if (!empty($categoriaId)) {
                $sql .= " AND s.id_categoria = :categoriaId";
                $params[':categoriaId'] = (int)$categoriaId;
            }

            if (!empty($ciudad)) {
                $sql .= " AND (pp.ciudad LIKE :ciudad OR pp.zona LIKE :ciudad)";
                $params[':ciudad'] = '%' . $ciudad . '%';
            }

            if ($precioMax !== null && $precioMax > 0) {
                $sql .= " AND pub.precio <= :precioMax";
                $params[':precioMax'] = $precioMax;
            }

            $ordenMap = [
                'precio_asc'  => 'pub.precio ASC',
                'precio_desc' => 'pub.precio DESC',
                'valorados'   => 'calificacion_promedio DESC, total_resenas DESC',
                'recientes'   => 'pub.created_at DESC',
            ];
            $orderBy = $ordenMap[$orden] ?? 'pub.created_at DESC';

            $havingConds = [];
            if ($soloOfertas) {
                $havingConds[] = 'promo_descuento IS NOT NULL';
            }
            if ($calificacionMin !== null && $calificacionMin > 0) {
                $havingConds[] = 'calificacion_promedio >= :calMin';
                $params[':calMin'] = $calificacionMin;
            }
            $havingStr = $havingConds ? ' HAVING ' . implode(' AND ', $havingConds) : '';
            $sql .= " GROUP BY pub.id$havingStr ORDER BY $orderBy";

            $stmt = $this->conexion->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue(
                    $key,
                    $value,
                    $key === ':categoriaId' ? PDO::PARAM_INT : PDO::PARAM_STR
                );
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error en Publicacion::listarPublicasActivas -> " . $e->getMessage());
            return [];
        }
    }


    public function listarPublicacionesAprobadasParaSolicitudes(): array
    {
        $sql = "
        SELECT
            p.id,
            p.titulo,
            p.precio,
            sv.nombre AS servicio_nombre,
            CONCAT(pr.nombres, ' ', pr.apellidos) AS proveedor_nombre
        FROM publicaciones p
        INNER JOIN servicios sv   ON p.servicio_id = sv.id
        INNER JOIN proveedores pr ON p.proveedor_id = pr.id
        WHERE p.estado = 'aprobado'
          AND p.tipo_publicacion = 'proveedor'
        ORDER BY p.fecha_publicacion DESC, p.id DESC
    ";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerReporteServiciosOfrecidos(
        ?int    $categoriaId  = null,
        ?string $estado       = null,
        ?int    $proveedorId  = null,
        ?string $desde        = null,
        ?string $hasta        = null
    ): array {
        try {
            $params = [];

            $where = "WHERE pub.tipo_publicacion = 'proveedor'";

            if (!empty($categoriaId)) {
                $where .= " AND s.id_categoria = :categoriaId";
                $params[':categoriaId'] = $categoriaId;
            }
            if (!empty($estado)) {
                $where .= " AND pub.estado = :estado";
                $params[':estado'] = $estado;
            }
            if (!empty($proveedorId)) {
                $where .= " AND pub.proveedor_id = :proveedorId";
                $params[':proveedorId'] = $proveedorId;
            }
            if (!empty($desde)) {
                $where .= " AND DATE(pub.created_at) >= :desde";
                $params[':desde'] = $desde;
            }
            if (!empty($hasta)) {
                $where .= " AND DATE(pub.created_at) <= :hasta";
                $params[':hasta'] = $hasta;
            }

            // Resumen global con los filtros aplicados
            $sqlGlobal = "
                SELECT
                    COUNT(*)                                        AS total,
                    SUM(pub.estado = 'aprobado')                   AS aprobados,
                    SUM(pub.estado = 'pendiente')                  AS pendientes,
                    SUM(pub.estado = 'rechazado')                  AS rechazados,
                    COALESCE(SUM(sol_count.total_sol), 0)          AS total_solicitudes,
                    COALESCE(SUM(sc_count.total_contratos), 0)     AS total_contratos
                FROM publicaciones pub
                INNER JOIN servicios s ON pub.servicio_id = s.id
                LEFT JOIN (
                    SELECT publicacion_id, COUNT(*) AS total_sol
                    FROM solicitudes
                    GROUP BY publicacion_id
                ) sol_count ON sol_count.publicacion_id = pub.id
                LEFT JOIN (
                    SELECT sol.publicacion_id, COUNT(sc.id) AS total_contratos
                    FROM servicios_contratados sc
                    INNER JOIN solicitudes sol ON sc.solicitud_id = sol.id
                    GROUP BY sol.publicacion_id
                ) sc_count ON sc_count.publicacion_id = pub.id
                $where
            ";

            $stGlobal = $this->conexion->prepare($sqlGlobal);
            foreach ($params as $k => $v) {
                $stGlobal->bindValue($k, $v, in_array($k, [':categoriaId', ':proveedorId']) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stGlobal->execute();
            $global = $stGlobal->fetch(PDO::FETCH_ASSOC) ?: [];

            // Detalle de publicaciones
            $sqlDetalle = "
                SELECT
                    pub.id                                          AS publicacion_id,
                    pub.titulo,
                    pub.precio,
                    pub.estado,
                    pub.created_at,
                    s.nombre                                        AS servicio_nombre,
                    c.nombre                                        AS categoria_nombre,
                    CONCAT(pr.nombres, ' ', pr.apellidos)           AS proveedor_nombre,
                    COALESCE(sol_count.total_sol, 0)                AS solicitudes,
                    COALESCE(sc_count.total_contratos, 0)           AS contratos
                FROM publicaciones pub
                INNER JOIN servicios   s   ON pub.servicio_id  = s.id
                LEFT  JOIN categorias  c   ON s.id_categoria   = c.id
                INNER JOIN proveedores pr  ON pub.proveedor_id = pr.id
                LEFT JOIN (
                    SELECT publicacion_id, COUNT(*) AS total_sol
                    FROM solicitudes
                    GROUP BY publicacion_id
                ) sol_count ON sol_count.publicacion_id = pub.id
                LEFT JOIN (
                    SELECT sol.publicacion_id, COUNT(sc.id) AS total_contratos
                    FROM servicios_contratados sc
                    INNER JOIN solicitudes sol ON sc.solicitud_id = sol.id
                    GROUP BY sol.publicacion_id
                ) sc_count ON sc_count.publicacion_id = pub.id
                $where
                ORDER BY pub.created_at DESC
                LIMIT 200
            ";

            $stDetalle = $this->conexion->prepare($sqlDetalle);
            foreach ($params as $k => $v) {
                $stDetalle->bindValue($k, $v, in_array($k, [':categoriaId', ':proveedorId']) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stDetalle->execute();
            $publicaciones = $stDetalle->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // Por categoría (sin filtro de categoría para mostrar distribución)
            $sqlCat = "
                SELECT
                    COALESCE(c.nombre, 'Sin categoría') AS categoria,
                    COUNT(pub.id)                       AS total
                FROM publicaciones pub
                INNER JOIN servicios  s ON pub.servicio_id = s.id
                LEFT  JOIN categorias c ON s.id_categoria  = c.id
                WHERE pub.tipo_publicacion = 'proveedor'
                GROUP BY c.id, c.nombre
                ORDER BY total DESC
                LIMIT 10
            ";
            $stCat = $this->conexion->query($sqlCat);
            $porCategoria = $stCat->fetchAll(PDO::FETCH_ASSOC) ?: [];

            return [
                'global'       => $global,
                'publicaciones' => $publicaciones,
                'porCategoria' => $porCategoria,
            ];
        } catch (PDOException $e) {
            error_log("Error en Publicacion::obtenerReporteServiciosOfrecidos -> " . $e->getMessage());
            return ['global' => [], 'publicaciones' => [], 'porCategoria' => []];
        }
    }

    public function obtenerDetallePublicacion(int $id): ?array
    {
        try {
            $sql = "
                SELECT 
                    p.id AS publicacion_id,
                    p.titulo AS publicacion_titulo,
                    p.descripcion AS publicacion_descripcion,
                    p.precio AS publicacion_precio,
                    p.estado AS publicacion_estado,
                    p.created_at AS publicacion_fecha,

                    s.id AS servicio_id,
                    s.nombre AS servicio_nombre,
                    s.descripcion AS servicio_descripcion,
                    s.imagen AS servicio_imagen,
                    s.disponibilidad AS servicio_disponible,

                    c.nombre AS categoria_nombre,

                    pr.id AS proveedor_id,
                    CONCAT(pr.nombres, ' ', pr.apellidos) AS proveedor_nombre,
                    pr.ubicacion AS proveedor_ubicacion,
                    pr.foto AS proveedor_foto,
                    pr.usuario_id AS proveedor_usuario_id,

                    promo.porcentaje_descuento AS promo_descuento,
                    promo.fecha_fin            AS promo_hasta,
                    ROUND(p.precio * (1 - COALESCE(promo.porcentaje_descuento, 0) / 100)) AS precio_con_descuento

                FROM publicaciones p
                INNER JOIN servicios s   ON p.servicio_id  = s.id
                LEFT  JOIN categorias c  ON s.id_categoria = c.id
                INNER JOIN proveedores pr ON p.proveedor_id = pr.id
                LEFT  JOIN promociones promo
                    ON promo.publicacion_id = p.id
                    AND promo.fecha_inicio <= CURDATE()
                    AND promo.fecha_fin    >= CURDATE()
                WHERE p.id = :id
                  AND p.estado = 'aprobado'
                LIMIT 1
            ";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;
        } catch (PDOException $e) {
            error_log("Error en Publicacion::obtenerDetallePublicacion -> " . $e->getMessage());
            return null;
        }
    }
}
