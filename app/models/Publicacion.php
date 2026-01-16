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
     *
     * - $usuarioId  -> viene de $_SESSION['user']['id']
     * - $servicioId -> id de la fila recién creada en servicios
     * - $data:
     *      ['nombre']       => título base del servicio
     *      ['descripcion']  => descripción del servicio
     *      ['precio']       => (opcional) precio base
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
        // Muy importante: coincidir con tu ENUM real: 'pendiente', 'aprobado', 'rechazada', etc.
        $estado          = 'pendiente'; // para flujo de moderación

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
     * Lista todas las publicaciones asociadas a un proveedor
     * a partir del usuario (tabla usuarios).
     *
     * Esta es la que usa tu vista de:
     *   /proveedor/listar-servicio
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
                    pub.id              AS publicacion_id,
                    pub.servicio_id     AS servicio_id,
                    pub.estado          AS estado_publicacion,
                    pub.created_at      AS publicacion_created_at,

                    s.nombre            AS servicio_nombre,
                    s.descripcion       AS servicio_descripcion,
                    s.imagen            AS servicio_imagen,
                    s.disponibilidad    AS servicio_disponible,

                    c.nombre            AS categoria_nombre
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
     * Lista publicaciones públicas/visibles para el catálogo del cliente.
     *
     * Muestra SOLO las aprobadas (estado = 'aprobado').
     * Opcionalmente filtra por texto y por categoría.
     */
    public function listarPublicasActivas(?string $busqueda = null, ?int $idCategoria = null): array
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

                    s.nombre        AS servicio_nombre,
                    s.descripcion   AS servicio_descripcion,
                    s.imagen        AS servicio_imagen,

                    c.id            AS categoria_id,
                    c.nombre        AS categoria_nombre,

                    prov.nombre_comercial,
                    prov.ciudad,
                    prov.zona
                FROM publicaciones AS pub
                INNER JOIN servicios   AS s    ON pub.servicio_id  = s.id
                LEFT  JOIN categorias  AS c    ON s.id_categoria   = c.id
                LEFT  JOIN proveedores AS prov ON pub.proveedor_id = prov.id
                WHERE pub.estado = 'aprobado'
            ";

            $params = [];

            // Búsqueda por texto (opcional)
            if ($busqueda !== null && $busqueda !== '') {
                $sql .= " AND (
                    s.nombre LIKE :busqueda
                    OR pub.titulo LIKE :busqueda
                    OR pub.descripcion LIKE :busqueda
                )";
                $params[':busqueda'] = '%' . $busqueda . '%';
            }

            // Filtro por categoría (opcional)
            if ($idCategoria !== null && $idCategoria > 0) {
                $sql .= " AND c.id = :categoria_id";
                $params[':categoria_id'] = $idCategoria;
            }

            $sql .= " ORDER BY pub.created_at DESC";

            $stmt = $this->conexion->prepare($sql);

            foreach ($params as $key => $value) {
                if ($key === ':categoria_id') {
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
}
