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

            // INSERT usuarios (usamos :estado pasado desde el controlador)
            $insertar = "INSERT INTO usuarios (email, clave, documento, rol, estado) VALUES (:email, :clave, :documento, :rol, :estado)";

            $stmt = $this->conexion->prepare($insertar);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':clave', $claveHash);
            $stmt->bindParam(':documento', $data['documento']);
            $stmt->bindParam(':rol', $data['rol']);
            $stmt->bindParam(':estado', $data['estado'], PDO::PARAM_INT);

            $stmt->execute();

            // Obtener el ID generado
            $usuario_id = $this->conexion->lastInsertId();
            if (!$usuario_id) throw new Exception("Error al obtener ID de usuario después de la inserción.");

            // 1.2. Inserción en tabla de detalle según rol
            $tablaDetalle = $this->getTablaDetalle($data['rol']);

            $insertar2 = "INSERT INTO {$tablaDetalle} (usuario_id, nombres, apellidos, telefono, ubicacion, foto) VALUES (:usuario_id, :nombres, :apellidos, :telefono, :ubicacion, :foto)";
            $resultado = $this->conexion->prepare($insertar2);
            $resultado->bindParam(':usuario_id', $usuario_id);
            $resultado->bindParam(':nombres', $data['nombres']);
            $resultado->bindParam(':apellidos', $data['apellidos']);
            $resultado->bindParam(':telefono', $data['telefono']);
            $resultado->bindParam(':ubicacion', $data['ubicacion']);
            $resultado->bindParam(':foto', $data['foto']);
            $resultado->execute();

            // Si el rol es proveedor, insertar en documentos_proveedor los archivos subidos
            if ($data['rol'] === 'proveedor' && !empty($data['documentos']) && is_array($data['documentos'])) {
                // Primero necesitamos obtener el ID del proveedor (tabla proveedores) que acabamos de insertar
                // La tabla proveedores tiene su propio id autoincrement, y tiene usuario_id = $usuario_id
                // Podemos obtener proveedor_id consultando la tabla proveedores por usuario_id
                $stmtProv = $this->conexion->prepare("SELECT id FROM proveedores WHERE usuario_id = :usuario_id LIMIT 1");
                $stmtProv->bindParam(':usuario_id', $usuario_id);
                $stmtProv->execute();
                $proveedor_row = $stmtProv->fetch(PDO::FETCH_ASSOC);

                if (!$proveedor_row) {
                    // Si por alguna razón no existe, hacemos rollback y error
                    throw new Exception("No se encontró el registro en proveedores para usuario_id {$usuario_id}");
                }

                $proveedor_id = $proveedor_row['id'];

                // Insertar cada documento en documentos_proveedor
                $insertDoc = "INSERT INTO documentos_proveedor (proveedor_id, tipo_documento, archivo, estado, fecha_subida) VALUES (:proveedor_id, :tipo_documento, :archivo, 'pendiente', NOW())";
                $stmtDoc = $this->conexion->prepare($insertDoc);

                foreach ($data['documentos'] as $key => $doc) {
                    // $doc = ['tipo' => 'dni'|'otro'|'certificado', 'archivo' => 'nombre.ext']
                    if (!isset($doc['tipo']) || !isset($doc['archivo'])) continue;

                    $tipo = $doc['tipo'];
                    $archivo = $doc['archivo'];

                    $stmtDoc->bindParam(':proveedor_id', $proveedor_id);
                    $stmtDoc->bindParam(':tipo_documento', $tipo);
                    $stmtDoc->bindParam(':archivo', $archivo);
                    $stmtDoc->execute();
                }
            }

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            $this->conexion->rollBack();
            error_log("Error en Usuario::registrar->" . $e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log("Error en Usuario::registrar->" . $e->getMessage());
            return false;
        }
    }


    public function mostrar()
    {
        try {
            // VARIABLE QUE ALMAECENA LA SENTENCIA DE SQL A EJECUTAR
            $consultar = "SELECT 
                        u.id,
                        u.email,
                        u.documento,
                        u.rol,
                        u.estado_id,
                        e.nombre AS estado,
                        u.created_at,

                        -- Datos del detalle según el rol
                        COALESCE(c.nombres, p.nombres, a.nombres) AS nombres,
                        COALESCE(c.apellidos, p.apellidos, a.apellidos) AS apellidos,
                        COALESCE(c.telefono, p.telefono, a.telefono) AS telefono,
                        COALESCE(c.ubicacion, p.ubicacion, a.ubicacion) AS ubicacion,
                        COALESCE(c.foto, p.foto, a.foto) AS foto

                    FROM usuarios u
                    LEFT JOIN usuario_estados e ON e.id = u.estado_id

                    LEFT JOIN clientes c ON u.id = c.usuario_id AND u.rol = 'cliente'
                    LEFT JOIN proveedores p ON u.id = p.usuario_id AND u.rol = 'proveedor'
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
                u.id, u.email, u.documento, u.rol, u.estado_id,
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

        $this->conexion->beginTransaction();


        // 1. Determinar la tabla de detalle según el rol
        $tabla_detalle = '';
        switch ($data['rol']) {
            case 'cliente':
                $tabla_detalle = 'clientes';
                break;
            case 'proveedor':
                $tabla_detalle = 'proveedores';
                break;
            case 'admin':
                $tabla_detalle = 'admins';
                break;
            default:
                error_log("Error: Rol de usuario inválido ({$data['rol']}).");
                return false;
        }

        try {
            // A. Actualización de la tabla base 'usuarios' (solo campos esenciales)
            $actualizar = "UPDATE usuarios 
                             SET email = :email,
                             documento = :documento,
                              rol = :rol 
                             WHERE id = :id";

            $resultado = $this->conexion->prepare($actualizar);
            $resultado->bindParam(':id', $data['id']);
            $resultado->bindParam(':email', $data['email']);
            $resultado->bindParam(':documento', $data['documento']);
            $resultado->bindParam(':rol', $data['rol']);

            $resultado->execute();

            // B. Actualización de la tabla de detalle (clientes/proveedores/admins)
            // Se asume que el ID de la tabla de detalle es 'usuario_id'
            $actualizar = "UPDATE {$tabla_detalle} 
                            SET nombres = :nombres, apellidos = :apellidos, telefono = :telefono, ubicacion = :ubicacion ,foto = :foto
                            WHERE usuario_id = :usuario_id";

            $resultado = $this->conexion->prepare($actualizar);
            $resultado->bindParam(':usuario_id', $data['id']);
            $resultado->bindParam(':nombres', $data['nombres']);
            $resultado->bindParam(':apellidos', $data['apellidos']);
            $resultado->bindParam(':telefono', $data['telefono']);
            $resultado->bindParam(':ubicacion', $data['ubicacion']);
            $resultado->bindParam(':foto', $data['foto_perfil']);


            $resultado->execute();

            // 2. Si ambas consultas fueron exitosas, hacemos commit
            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            error_log("Error en Usuario::actualizarDetallesUsuario -> " . $e->getMessage());
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
            error_log("Error en Usuario::eliminar->" . $e->getMessage());
            return false;
        }
    }
}
