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
     * Crea una publicación para un servicio creado por un proveedor.
     * - Se deja en estado 'pendiente' para flujo de moderación.
     */
    public function crearParaServicioDeProveedor(
        int $usuarioId,
        int $servicioId,
        array $data = []
    ): bool {
        // 1. Resolver proveedor_id a partir del usuario logueado
        $proveedorId = $this->obtenerProveedorIdPorUsuario($usuarioId);

        if (!$proveedorId) {
            throw new Exception("No se encontró proveedor asociado al usuario {$usuarioId}");
        }

        // 2. Normalizar datos
        $titulo      = $data['nombre']       ?? $data['titulo'] ?? 'Servicio ofertado';
        $descripcion = $data['descripcion']  ?? null;
        $precio      = isset($data['precio']) ? (float)$data['precio'] : 0.00;

        $tipoPublicacion = 'proveedor';
        $estado          = 'pendiente'; // el admin luego lo cambiará a 'aprobado'

        // 3. Insert
        $sql = "INSERT INTO publicaciones (
                    tipo_publicacion,
                    proveedor_id,
                    servicio_id,
                    titulo,
                    descripcion,
                    precio,
                    estado
                ) VALUES (
                    :tipo_publicacion,
                    :proveedor_id,
                    :servicio_id,
                    :titulo,
                    :descripcion,
                    :precio,
                    :estado
                )";

        $stmt = $this->conexion->prepare($sql);

        $stmt->bindParam(':tipo_publicacion', $tipoPublicacion);
        $stmt->bindParam(':proveedor_id',    $proveedorId, PDO::PARAM_INT);
        $stmt->bindParam(':servicio_id',     $servicioId,  PDO::PARAM_INT);
        $stmt->bindParam(':titulo',          $titulo);
        $stmt->bindParam(':descripcion',     $descripcion);
        $stmt->bindParam(':precio',          $precio);
        $stmt->bindParam(':estado',          $estado);

        return $stmt->execute();
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
            // 1. Obtener el proveedor_id desde el usuario
            $proveedorId = $this->obtenerProveedorIdPorUsuario($usuarioId);

            if (!$proveedorId) {
                return [];
            }

            // 2. Consulta de publicaciones + servicio + categoría
            $sql = "
                SELECT 
                    pub.id                              AS publicacion_id,
                    pub.servicio_id                     AS servicio_id,
                    pub.titulo                          AS publicacion_titulo,
                    pub.descripcion                     AS publicacion_descripcion,
                    pub.precio                          AS publicacion_precio,
                    pub.estado                          AS estado_publicacion,
                    pub.created_at                      AS publicacion_created_at,

                    s.nombre                            AS servicio_nombre,
                    s.descripcion                       AS servicio_descripcion,
                    s.imagen                            AS servicio_imagen,
                    s.disponibilidad                    AS servicio_disponible,

                    c.nombre                            AS categoria_nombre
                FROM publicaciones AS pub
                INNER JOIN servicios   AS s ON pub.servicio_id  = s.id
                LEFT  JOIN categorias  AS c ON s.id_categoria   = c.id
                WHERE pub.proveedor_id = :proveedor_id
                ORDER BY pub.created_at DESC
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
