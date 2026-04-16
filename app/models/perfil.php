<?php
require_once __DIR__ . '/../../config/database.php';

class Perfil
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    private function getTablaDetalle($rol)
    {
        if ($rol === 'cliente') return 'clientes';
        if ($rol === 'proveedor') return 'proveedores';
        if ($rol === 'admin') return 'admins';
        throw new Exception("Rol no válido");
    }

    // Retorna toda la info del usuario logueado (unificado)
    public function obtenerPerfilCompleto($id, $rol)
    {
        try {
            $tabla = $this->getTablaDetalle($rol);
            $sql = "SELECT u.email as correo, u.documento, u.rol, 
                           d.nombres, d.apellidos, d.telefono, d.ubicacion as direccion, d.foto";

            // Si es proveedor, necesitamos su ID de la tabla proveedores para categorías
            if ($rol === 'proveedor') {
                $sql .= ", d.id as proveedor_id";
            }

            $sql .= " FROM usuarios u 
                      INNER JOIN $tabla d ON u.id = d.usuario_id 
                      WHERE u.id = :id";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $id]);
            $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

            // Cargar categorías si es proveedor
            if ($perfil && $rol === 'proveedor') {
                $sqlCat = "SELECT c.nombre FROM categorias c 
                           JOIN proveedor_categorias pc ON c.id = pc.categoria_id 
                           WHERE pc.proveedor_id = :pid";
                $stmtCat = $this->conexion->prepare($sqlCat);
                $stmtCat->execute([':pid' => $perfil['proveedor_id']]);
                $perfil['categorias'] = $stmtCat->fetchAll(PDO::FETCH_COLUMN);
            }
            return $perfil;
        } catch (Exception $e) {
            return null;
        }
    }

    public function actualizarPerfil($id, $rol, $data)
    {
        try {
            $this->conexion->beginTransaction();
            $tabla = $this->getTablaDetalle($rol);

            // 1. Actualizar Usuario (Email y Clave si existe)
            $sqlUser = "UPDATE usuarios SET email = :email";
            $paramsUser = [':email' => $data['email'], ':id' => $id];
            if (!empty($data['clave'])) {
                $sqlUser .= ", clave = :clave";
                $paramsUser[':clave'] = password_hash($data['clave'], PASSWORD_DEFAULT);
            }
            $sqlUser .= " WHERE id = :id";
            $this->conexion->prepare($sqlUser)->execute($paramsUser);

            // 2. Actualizar Tabla de Detalle
            $sqlDetalle = "UPDATE $tabla SET 
                            nombres = :nom, apellidos = :ape, 
                            telefono = :tel, ubicacion = :ubi, foto = :foto 
                           WHERE usuario_id = :id";
            $this->conexion->prepare($sqlDetalle)->execute([
                ':nom'  => $data['nombres'],
                ':ape'  => $data['apellidos'],
                ':tel'  => $data['telefono'],
                ':ubi'  => $data['ubicacion'],
                ':foto' => $data['foto'],
                ':id'   => $id
            ]);

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            return false;
        }
    }

    public function cambiarContrasena($id, $claveActual, $nuevaClave)
    {
        try {
            // 1. Primero obtenemos la clave actual guardada en la BD
            $stmt = $this->conexion->prepare("SELECT clave FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) return false;

            // 2. Verificamos si la clave que el usuario escribió coincide con la de la BD
            if (password_verify($claveActual, $usuario['clave'])) {
                // 3. Si coincide, procedemos a actualizar con el nuevo HASH
                $sql = "UPDATE usuarios SET clave = :clave WHERE id = :id";
                $this->conexion->prepare($sql)->execute([
                    ':clave' => password_hash($nuevaClave, PASSWORD_DEFAULT),
                    ':id'    => $id
                ]);
                return true;
            } else {
                return false; // Clave actual incorrecta
            }
        } catch (Exception $e) {
            return false;
        }
    }
}
