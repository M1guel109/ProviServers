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
     * - usuarioId  -> viene de $_SESSION['user']['id']
     * - servicioId -> id de la fila recién creada en servicios
     * - data:
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

        // IMPORTANTE:
        // Si en tu BD el enum de estado ya incluye 'pendiente', usa 'pendiente'.
        // Si NO, deja 'activa' para evitar errores con el enum.
        $estado = 'pendiente'; // o 'pendiente' si ya lo agregaste en el enum

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
}
