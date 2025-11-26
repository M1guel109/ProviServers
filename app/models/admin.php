<?php
require_once __DIR__ . '/../../config/database.php';

class Usuario
{
    private $conexion;

    public function __construct()
    {
        $db = new  Conexion();
        $this->conexion = $db->getConexion();
    }


    private function getTablaDetalle($rol)
    {
        if ($rol === 'cliente') return 'clientes';
        if ($rol === 'proveedor') return 'proveedores';
        if ($rol === 'admin') return 'admins';
        throw new Exception("Rol no válido: " . $rol);
    }

    public function registrar($data)
    {
        try {

            $this->conexion->beginTransaction();

            // 🔒 Encriptar la clave antes de guardar
            $claveHash = password_hash($data['clave'], PASSWORD_DEFAULT);


            $insertar = "INSERT INTO usuarios (email, clave, documento, rol, estado) VALUES (:email, :clave, :documento, :rol, 1)";

            $resultado = $this->conexion->prepare($insertar);
            $resultado->bindParam(':email', $data['email']);
            $resultado->bindParam(':clave', $claveHash);
            $resultado->bindParam(':documento', $data['documento']);
            $resultado->bindParam(':rol', $data['rol']);

            $resultado->execute();

            // Obtener el ID generado para la siguiente inserción
            $usuario_id = $this->conexion->lastInsertId();
            if (!$usuario_id) throw new Exception("Error al obtener ID de usuario después de la inserción.");

            // 1.2. INSERCIÓN 2: TABLA DE DETALLE (clientes/proveedores/admins)
            $tablaDetalle = $this->getTablaDetalle($data['rol']);

            // Se inserta: usuario_id, nombres, apellidos, telefono, ubicacion, foto
            $insertar2 = "INSERT INTO {$tablaDetalle} (usuario_id, nombres, apellidos, telefono, ubicacion, foto) VALUES (:usuario_id, :nombres, :apellidos, :telefono, :ubicacion, :foto)";

            $resultado = $this->conexion->prepare($insertar2);
            $resultado->bindParam(':usuario_id', $usuario_id);
            $resultado->bindParam(':nombres', $data['nombres']);
            $resultado->bindParam(':apellidos', $data['apellidos']);
            $resultado->bindParam(':telefono', $data['telefono']);
            $resultado->bindParam(':ubicacion', $data['ubicacion']);
            $resultado->bindParam(':foto', $data['foto']);

            $resultado->execute();

            $this->conexion->commit();

            return true;
        } catch (PDOException $e) {
            error_log("Error en Usuario::registrar->" . $e->getMessage());
            return false;
        }
    }

    public function mostrar()
    {
        try {
            // VARIABLE QUE ALMAECENA LA SENTENCIA DE SQL A EJECUTAR
            $consultar = "
                SELECT 
                    u.id, u.email, u.documento, u.rol, u.estado, u.created_at,
                    -- COALESCE selecciona el primer valor NO NULO para cada campo
                    COALESCE(c.nombres, p.nombres, a.nombres) AS nombres,
                    COALESCE(c.apellidos, p.apellidos, a.apellidos) AS apellidos,
                    COALESCE(c.telefono, p.telefono, a.telefono) AS telefono,
                    COALESCE(c.ubicacion, p.ubicacion, a.ubicacion) AS ubicacion,
                    COALESCE(c.foto, p.foto, a.foto) AS foto
                FROM usuarios u
                -- JOIN a Clientes (solo si u.rol es 'cliente')
                LEFT JOIN clientes c ON u.id = c.usuario_id AND u.rol = 'cliente'
                -- JOIN a Proveedores (solo si u.rol es 'proveedor')
                LEFT JOIN proveedores p ON u.id = p.usuario_id AND u.rol = 'proveedor'
                -- JOIN a Admins (solo si u.rol es 'admin')
                LEFT JOIN admins a ON u.id = a.usuario_id AND u.rol = 'admin'
                ORDER BY u.created_at DESC
            ";

            // PREPARAR LO NECESARIO PARA EJECUTAR LA FUNCION 
            $resultado = $this->conexion->prepare($consultar);
            // SI S TIENE UN WHERE 
            $resultado->execute();

            return $resultado->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Usuario::mostrar->" . $e->getMessage());
            return [];
        }
    }

    public function mostrarId($id)
    {
        try {
            $consultar = "SELECT 
                u.id, u.email, u.documento, u.rol, u.estado,
                COALESCE(c.nombres, p.nombres, a.nombres) AS nombres,
                COALESCE(c.apellidos, p.apellidos, a.apellidos) AS apellidos,
                COALESCE(c.telefono, p.telefono, a.telefono) AS telefono,
                COALESCE(c.ubicacion, p.ubicacion, a.ubicacion) AS ubicacion,
                COALESCE(c.foto, p.foto, a.foto) AS foto
            FROM usuarios u
            LEFT JOIN clientes c ON u.id = c.usuario_id AND u.rol = 'cliente'
            LEFT JOIN proveedores p ON u.id = p.usuario_id AND u.rol = 'proveedor'
            LEFT JOIN admins a ON u.id = a.usuario_id AND u.rol = 'admin'
            WHERE u.id = :id
            LIMIT 1";

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id', $id);
            $resultado->execute();

            return $resultado->fetch();
        } catch (PDOException $e) {
            error_log("Error en Usuario::mostrar->" . $e->getMessage());
            return [];
        }
    }

    public function actualizar($data)
    {
        try {

            $actualizar = "UPDATE usuarios SET  nombre = :nombre, email = :email, telefono = :telefono, ubicacion = :ubicacion, rol = :rol  WHERE id = :id ";

            $resultado = $this->conexion->prepare($actualizar);
            $resultado->bindParam(':id', $data['id']);
            $resultado->bindParam(':nombre', $data['nombre']);
            $resultado->bindParam(':email', $data['email']);
            $resultado->bindParam(':telefono', $data['telefono']);
            $resultado->bindParam(':ubicacion', $data['ubicacion']);
            $resultado->bindParam(':rol', $data['rol']);

            return $resultado->execute();
        } catch (PDOException $e) {
            error_log("Error en Usuario::actualizar->" . $e->getMessage());
            return false;
        }
    }

    public function eliminar($id)
    {
        try {
            $this->conexion->beginTransaction();

            $eliminarRol = "SELECT rol FROM usuarios WHERE id = :id";

            $resultado = $this->conexion->prepare($eliminarRol);
            $resultado->bindParam(':id', $id);
            $resultado->execute();
            $rol = $resultado->fetchColumn();

            if ($rol) {
                $tablaDetalle = $this->getTablaDetalle($rol);

                // 3. ELIMINAR de la tabla de detalle (debe ser primero)
                $sqlDetalle = "DELETE FROM {$tablaDetalle} WHERE usuario_id = :id";
                $stmtDetalle = $this->conexion->prepare($sqlDetalle);
                $stmtDetalle->bindParam(':id', $id);
                $stmtDetalle->execute();
            }

            $eliminar = "DELETE FROM usuarios WHERE id = :id";
            $resultado = $this->conexion->prepare($eliminar);
            $resultado->bindParam(':id', $id);
            $resultado->execute();

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            error_log("Error en Usuario::actualizar->" . $e->getMessage());
            return false;
        }
    }
}
