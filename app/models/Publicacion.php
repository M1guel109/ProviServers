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

            if (!$proveedorId) {
                return [];
            }

            $sql = "
            SELECT 
                pub.id                  AS publicacion_id,
                pub.servicio_id         AS servicio_id,
                pub.titulo              AS titulo,
                pub.descripcion         AS descripcion,
                pub.precio              AS precio,
                pub.estado              AS estado,
                pub.motivo_rechazo      AS motivo_rechazo,
                pub.fecha_publicacion   AS fecha_publicacion,
                pub.created_at          AS publicacion_created_at,

                s.nombre                AS servicio_nombre,
                s.descripcion           AS servicio_descripcion,
                s.imagen                AS servicio_imagen,
                s.disponibilidad        AS servicio_disponible,

                c.nombre                AS categoria_nombre
            FROM publicaciones AS pub
            INNER JOIN servicios  AS s ON pub.servicio_id = s.id
            LEFT JOIN categorias  AS c ON s.id_categoria = c.id
            WHERE pub.proveedor_id = :proveedor_id
            ORDER BY pub.fecha_publicacion DESC, pub.id DESC
        ";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':proveedor_id', $proveedorId, PDO::PARAM_INT);
            $stmt->execute();

            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $filas ?: [];
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

    public function listarPublicasActivas(?string $busqueda = null, ?int $categoriaId = null): array
    {
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

                c.nombre       AS categoria_nombre
            FROM publicaciones AS pub
            INNER JOIN servicios   AS s ON pub.servicio_id = s.id
            LEFT  JOIN categorias  AS c ON s.id_categoria = c.id
            WHERE pub.estado = 'aprobado'
        ";

            $params = [];

            // Búsqueda por texto (opcional)
            if (!empty($busqueda)) {
                $sql .= " 
                AND (
                    pub.titulo      LIKE :busqueda
                    OR s.nombre     LIKE :busqueda
                    OR s.descripcion LIKE :busqueda
                    OR c.nombre     LIKE :busqueda
                )
            ";
                $params[':busqueda'] = '%' . $busqueda . '%';
            }

            // Filtro por categoría (opcional)
            if (!empty($categoriaId)) {
                $sql .= " AND s.id_categoria = :categoriaId";
                $params[':categoriaId'] = (int) $categoriaId;
            }

            $sql .= " ORDER BY pub.created_at DESC";

            $stmt = $this->conexion->prepare($sql);

            foreach ($params as $key => $value) {
                if ($key === ':categoriaId') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }

            $stmt->execute();
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $filas ?: [];
        } catch (PDOException $e) {
            error_log("Error en Publicacion::listarPublicasActivas -> " . $e->getMessage());
            return [];
        }
    }
    public function obtenerPublicaActivaPorId(int $id): ?array
    {
        try {
            $sql = "
            SELECT 
                pub.id,
                pub.titulo,
                pub.descripcion       AS publicacion_descripcion,
                pub.precio,
                pub.estado,
                pub.created_at        AS publicacion_created_at,

                s.id                  AS servicio_id,
                s.nombre              AS servicio_nombre,
                s.descripcion         AS servicio_descripcion,
                s.imagen              AS servicio_imagen,
                s.disponibilidad      AS servicio_disponible,

                c.nombre              AS categoria_nombre,

                p.id AS proveedor_id, 
                p.usuario_id AS proveedor_usuario_id,
                CONCAT(p.nombres, ' ', p.apellidos) AS proveedor_nombre,
                p.ubicacion AS proveedor_ubicacion,
                p.foto AS proveedor_foto

            FROM publicaciones AS pub
            INNER JOIN servicios   AS s ON pub.servicio_id  = s.id
            LEFT  JOIN categorias  AS c ON s.id_categoria   = c.id
            INNER JOIN proveedores AS p ON pub.proveedor_id = p.id
            WHERE pub.id = :id
              AND pub.estado = 'aprobado'
            LIMIT 1
        ";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            return $fila ?: null;
        } catch (PDOException $e) {
            error_log("Error en Publicacion::obtenerPublicaActivaPorId -> " . $e->getMessage());
            return null;
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
}
