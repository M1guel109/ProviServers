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
     * Obtiene el id del proveedor (tabla proveedores) a partir del usuario_id (tabla usuarios)
     */
    private function obtenerProveedorIdPorUsuario(int $usuarioId): ?int
    {
        $sql = "SELECT id FROM proveedores WHERE usuario_id = :usuario_id LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila['id'] ?? null;
    }

    /**
     * Crea una publicación ligada a un servicio creado por un proveedor.
     *
     * @param int   $usuarioId   ID en tabla usuarios (el que tienes en $_SESSION['user']['id'])
     * @param int   $servicioId  ID del servicio recién creado
     * @param array $data        Datos adicionales (nombre, descripcion, precio, ubicacion, estado)
     */
    public function crearParaServicioDeProveedor(int $usuarioId, int $servicioId, array $data = []): bool
    {
        // 1. Obtener proveedor_id desde usuarios
        $proveedorId = $this->obtenerProveedorIdPorUsuario($usuarioId);
        if (!$proveedorId) {
            throw new Exception("No se encontró proveedor asociado al usuario $usuarioId.");
        }

        // 2. Preparar datos
        $titulo      = $data['titulo']      ?? $data['nombre'] ?? null;
        $descripcion = $data['descripcion'] ?? null;
        $precio      = isset($data['precio']) && $data['precio'] !== '' ? $data['precio'] : 0;
        $ubicacion   = $data['ubicacion']   ?? null;

        // Si modificaste la columna estado para incluir 'pendiente'
        $estado      = $data['estado']      ?? 'pendiente';

        // 3. Insertar publicación
        $sql = "INSERT INTO publicaciones (
                    tipo_publicacion,
                    proveedor_id,
                    servicio_id,
                    titulo,
                    descripcion,
                    precio,
                    ubicacion,
                    estado
                ) VALUES (
                    'proveedor',
                    :proveedor_id,
                    :servicio_id,
                    :titulo,
                    :descripcion,
                    :precio,
                    :ubicacion,
                    :estado
                )";

        $stmt = $this->conexion->prepare($sql);

        $stmt->bindParam(':proveedor_id', $proveedorId, PDO::PARAM_INT);
        $stmt->bindParam(':servicio_id', $servicioId, PDO::PARAM_INT);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':ubicacion', $ubicacion);
        $stmt->bindParam(':estado', $estado);

        return $stmt->execute();
    }
}
