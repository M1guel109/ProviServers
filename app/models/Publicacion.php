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
     */
    public function listarPorProveedorUsuario(int $usuarioId): array
    {
        try {
            // 1. Obtener el proveedor_id desde el usuario
            $proveedorId = $this->obtenerProveedorIdPorUsuario($usuarioId);

            if (!$proveedorId) {
                return [];
            }

            // 2. Consulta de publicaciones de ese proveedor
            $sql = "
                SELECT 
                    pub.id                AS publicacion_id,
                    pub.servicio_id       AS servicio_id,
                    pub.titulo            AS publicacion_titulo,
                    pub.descripcion       AS publicacion_descripcion,
                    pub.precio            AS publicacion_precio,
                    pub.estado            AS estado_publicacion,
                    pub.created_at        AS publicacion_created_at,
                    
                    s.nombre              AS servicio_nombre,
                    s.descripcion         AS servicio_descripcion,
                    s.imagen              AS servicio_imagen,
                    s.disponibilidad      AS servicio_disponible,

                    c.nombre              AS categoria_nombre
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
}
