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

            // Solo actualiza datos del detalle — email se cambia en flujo separado con contraseña
            $sqlDetalle = "UPDATE $tabla SET
                            nombres   = :nom,
                            apellidos = :ape,
                            telefono  = :tel,
                            ubicacion = :ubi,
                            foto      = :foto
                           WHERE usuario_id = :id";
            $this->conexion->prepare($sqlDetalle)->execute([
                ':nom'  => $data['nombres'],
                ':ape'  => $data['apellidos'],
                ':tel'  => $data['telefono'],
                ':ubi'  => $data['ubicacion'],
                ':foto' => $data['foto'],
                ':id'   => $id,
            ]);

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log('Perfil::actualizarPerfil -> ' . $e->getMessage());
            return false;
        }
    }

    public function cambiarEmail(int $id, string $claveActual, string $emailNuevo): string
    {
        try {
            $stmt = $this->conexion->prepare("SELECT clave FROM usuarios WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) return 'error';
            if (!password_verify($claveActual, $usuario['clave'])) return 'clave_incorrecta';

            $stmtCheck = $this->conexion->prepare(
                "SELECT id FROM usuarios WHERE email = :email AND id <> :id LIMIT 1"
            );
            $stmtCheck->execute([':email' => $emailNuevo, ':id' => $id]);
            if ($stmtCheck->fetch()) return 'email_duplicado';

            $this->conexion->prepare("UPDATE usuarios SET email = :email WHERE id = :id")
                ->execute([':email' => $emailNuevo, ':id' => $id]);

            return 'ok';
        } catch (Exception $e) {
            error_log('Perfil::cambiarEmail -> ' . $e->getMessage());
            return 'error';
        }
    }

    public function eliminarCuentaCliente(int $id, string $clave): string
    {
        try {
            $this->conexion->beginTransaction();

            // 1. Verificar contraseña
            $stmt = $this->conexion->prepare("SELECT clave FROM usuarios WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) { $this->conexion->rollBack(); return 'error'; }
            if (!password_verify($clave, $usuario['clave'])) { $this->conexion->rollBack(); return 'clave_incorrecta'; }

            // 2. Verificar contratos activos
            $stmtCid = $this->conexion->prepare(
                "SELECT id FROM clientes WHERE usuario_id = :uid LIMIT 1"
            );
            $stmtCid->execute([':uid' => $id]);
            $clienteId = $stmtCid->fetchColumn();

            $tieneActivos = false;
            if ($clienteId) {
                $stmtAct = $this->conexion->prepare(
                    "SELECT COUNT(*) FROM servicios_contratados
                     WHERE cliente_id = :cid
                       AND estado IN ('pendiente','confirmado','en_proceso')"
                );
                $stmtAct->execute([':cid' => $clienteId]);
                $tieneActivos = (int)$stmtAct->fetchColumn() > 0;
            }

            if ($tieneActivos) {
                // Soft delete — desactiva sin borrar historial
                $this->conexion->prepare("UPDATE usuarios SET estado_id = 4 WHERE id = :id")
                    ->execute([':id' => $id]);
                $this->conexion->commit();
                return 'desactivado';
            }

            // 3. Hard delete — sin contratos activos
            $this->conexion->prepare("DELETE FROM clientes WHERE usuario_id = :id")
                ->execute([':id' => $id]);
            $this->conexion->prepare("DELETE FROM usuarios WHERE id = :id")
                ->execute([':id' => $id]);

            $this->conexion->commit();
            return 'eliminado';
        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log('Perfil::eliminarCuentaCliente -> ' . $e->getMessage());
            return 'error';
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
