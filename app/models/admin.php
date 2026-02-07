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

            // 1. Insertar en tabla USUARIOS
            $claveHash = password_hash($data['clave'], PASSWORD_DEFAULT);

            // Asegúrate que tu columna en BD sea 'estado_id' o 'estado' según tu estructura real
            $sqlUser = "INSERT INTO usuarios (email, clave, documento, rol, estado_id) VALUES (:email, :clave, :doc, :rol, :estado)";

            $stmt = $this->conexion->prepare($sqlUser);
            $stmt->execute([
                ':email'   => $data['email'],
                ':clave'   => $claveHash,
                ':doc'     => $data['documento'],
                ':rol'     => $data['rol'],
                ':estado'  => $data['estado']
            ]);

            $usuario_id = $this->conexion->lastInsertId();

            // 2. Insertar en tabla ESPECÍFICA (Clientes/Proveedores/Admins)
            $tablaDetalle = $this->getTablaDetalle($data['rol']);

            $sqlDetalle = "INSERT INTO {$tablaDetalle} (usuario_id, nombres, apellidos, telefono, ubicacion, foto) 
                        VALUES (:uid, :nom, :ape, :tel, :ubi, :foto)";

            $stmtDetalle = $this->conexion->prepare($sqlDetalle);
            $stmtDetalle->execute([
                ':uid'  => $usuario_id,
                ':nom'  => $data['nombres'],
                ':ape'  => $data['apellidos'],
                ':tel'  => $data['telefono'],
                ':ubi'  => $data['ubicacion'],
                ':foto' => $data['foto']
            ]);

            // =========================================================
            // 3. LÓGICA EXTRA PARA PROVEEDORES (Docs + Categorías)
            // =========================================================
            if ($data['rol'] === 'proveedor') {
                // Obtenemos el ID generado en la tabla 'proveedores' (que es distinto al usuario_id)
                $proveedor_id = $this->conexion->lastInsertId();

                // A. Guardar Documentos
                if (!empty($data['documentos'])) {
                    $sqlDoc = "INSERT INTO documentos_proveedor (proveedor_id, tipo_documento, archivo, estado) 
                            VALUES (:pid, :tipo, :archivo, 'pendiente')";
                    $stmtDoc = $this->conexion->prepare($sqlDoc);

                    foreach ($data['documentos'] as $doc) {
                        $stmtDoc->execute([
                            ':pid'     => $proveedor_id,
                            ':tipo'    => $doc['tipo'],
                            ':archivo' => $doc['archivo']
                        ]);
                    }
                }

                // B. Guardar Categorías (Dinámicas)
                if (!empty($data['categorias'])) {
                    // Preparar consultas para reutilizar dentro del bucle
                    $sqlCheckCat  = "SELECT id FROM categorias WHERE nombre = :nombre LIMIT 1";
                    $sqlInsertCat = "INSERT INTO categorias (nombre) VALUES (:nombre)";
                    $sqlRelacion  = "INSERT INTO proveedor_categorias (proveedor_id, categoria_id) VALUES (:pid, :cid)";

                    $stmtCheck = $this->conexion->prepare($sqlCheckCat);
                    $stmtInCat = $this->conexion->prepare($sqlInsertCat);
                    $stmtRel   = $this->conexion->prepare($sqlRelacion);

                    foreach ($data['categorias'] as $nombreCat) {
                        $nombreCat = trim($nombreCat);
                        if (empty($nombreCat)) continue;

                        // 1. Verificar si existe
                        $stmtCheck->execute([':nombre' => $nombreCat]);
                        $catId = $stmtCheck->fetchColumn();

                        // 2. Si no existe, crearla
                        if (!$catId) {
                            $stmtInCat->execute([':nombre' => $nombreCat]);
                            $catId = $this->conexion->lastInsertId();
                        }

                        // 3. Crear relación (usamos try/catch por si se duplica)
                        try {
                            $stmtRel->execute([
                                ':pid' => $proveedor_id,
                                ':cid' => $catId
                            ]);
                        } catch (PDOException $e) {
                            // Si ya existe la relación, continuamos silenciosamente
                        }
                    }
                }
            }

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            $this->conexion->rollBack();
            error_log("Error BD en registrar: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log("Error General en registrar: " . $e->getMessage());
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
            // 1. Consulta Principal (Datos de Usuario y Perfil)
            // AGREGUÉ: "p.id AS proveedor_id" para poder buscar sus detalles después
            $consultar = "SELECT 
            u.id, u.email, u.documento, u.clave, u.rol, u.estado_id, es.nombre AS estado_nombre,
            COALESCE(c.nombres, p.nombres, a.nombres) AS nombres,
            COALESCE(c.apellidos, p.apellidos, a.apellidos) AS apellidos,
            COALESCE(c.telefono, p.telefono, a.telefono) AS telefono,
            COALESCE(c.ubicacion, p.ubicacion, a.ubicacion) AS ubicacion,
            COALESCE(c.foto, p.foto, a.foto) AS foto,
            p.id AS proveedor_id 
        FROM usuarios u
        LEFT JOIN usuario_estados es ON u.estado_id = es.id
        LEFT JOIN clientes c ON u.id = c.usuario_id AND u.rol = 'cliente'
        LEFT JOIN proveedores p ON u.id = p.usuario_id AND u.rol = 'proveedor'
        LEFT JOIN admins a ON u.id = a.usuario_id AND u.rol = 'admin'
        WHERE u.id = :id
        LIMIT 1";

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':id', $id);
            $resultado->execute();

            $usuario = $resultado->fetch(PDO::FETCH_ASSOC);

            // Si no existe el usuario, devolvemos false
            if (!$usuario) return false;

            // =========================================================
            // 2. LÓGICA EXTRA: Si es proveedor, traemos sus "hijos"
            // =========================================================
            if ($usuario['rol'] === 'proveedor' && !empty($usuario['proveedor_id'])) {
                $pid = $usuario['proveedor_id'];

                // A. Traer Categorías (Como un array simple de nombres)
                // Esto devolverá: ['Plomería', 'Electricidad', ...]
                $sqlCat = "SELECT c.nombre 
                       FROM categorias c
                       JOIN proveedor_categorias pc ON c.id = pc.categoria_id
                       WHERE pc.proveedor_id = :pid";
                $stmtCat = $this->conexion->prepare($sqlCat);
                $stmtCat->execute([':pid' => $pid]);
                $usuario['categorias'] = $stmtCat->fetchAll(PDO::FETCH_COLUMN);

                // B. Traer Documentos (Array completo)
                // Esto devolverá: [['id'=>1, 'tipo'=>'cedula', ...], ...]
                $sqlDoc = "SELECT id, tipo_documento, archivo, estado, fecha_subida 
                       FROM documentos_proveedor 
                       WHERE proveedor_id = :pid";
                $stmtDoc = $this->conexion->prepare($sqlDoc);
                $stmtDoc->execute([':pid' => $pid]);
                $usuario['documentos'] = $stmtDoc->fetchAll(PDO::FETCH_ASSOC);
            }

            return $usuario;
        } catch (PDOException $e) {
            error_log("Error en Usuario::mostrarId->" . $e->getMessage());
            return false;
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
                              rol = :rol,
                              estado_id = :estado_id 
                             WHERE id = :id";

            $resultado = $this->conexion->prepare($actualizar);
            $resultado->bindParam(':id', $data['id']);
            $resultado->bindParam(':email', $data['email']);
            $resultado->bindParam(':documento', $data['documento']);
            $resultado->bindParam(':rol', $data['rol']);
            $resultado->bindParam(':estado_id', $data['estado']);

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

    public function obtenerDetalleCompleto($id)
    {
        try {
            // 1. Buscar datos básicos y rol
            $sql = "SELECT u.id, u.email, u.documento, u.rol, ue.nombre as estado, u.created_at,
                       COALESCE(p.nombres, c.nombres, a.nombres) as nombres,
                       COALESCE(p.apellidos, c.apellidos, a.apellidos) as apellidos,
                       COALESCE(p.telefono, c.telefono, a.telefono) as telefono,
                       COALESCE(p.ubicacion, c.ubicacion, a.ubicacion) as ubicacion,
                       COALESCE(p.foto, c.foto, a.foto) as foto,
                       p.id as proveedor_id
                FROM usuarios u
                LEFT JOIN usuario_estados ue ON u.estado_id = ue.id
                LEFT JOIN proveedores p ON u.id = p.usuario_id
                LEFT JOIN clientes c ON u.id = c.usuario_id
                LEFT JOIN admins a ON u.id = a.usuario_id
                WHERE u.id = :id";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':id' => $id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) return null;

            // 2. Si es PROVEEDOR, buscar extras
            if ($usuario['rol'] === 'proveedor' && !empty($usuario['proveedor_id'])) {
                $pid = $usuario['proveedor_id'];

                // A. Categorías
                $sqlCat = "SELECT c.nombre 
                       FROM categorias c
                       JOIN proveedor_categorias pc ON c.id = pc.categoria_id
                       WHERE pc.proveedor_id = :pid";
                $stmtCat = $this->conexion->prepare($sqlCat);
                $stmtCat->execute([':pid' => $pid]);
                $usuario['categorias'] = $stmtCat->fetchAll(PDO::FETCH_COLUMN);

                // B. Documentos
                $sqlDoc = "SELECT tipo_documento, archivo, estado 
                       FROM documentos_proveedor 
                       WHERE proveedor_id = :pid";
                $stmtDoc = $this->conexion->prepare($sqlDoc);
                $stmtDoc->execute([':pid' => $pid]);
                $usuario['documentos'] = $stmtDoc->fetchAll(PDO::FETCH_ASSOC);
            }

            return $usuario;
        } catch (PDOException $e) {
            error_log("Error obtenerDetalleCompleto: " . $e->getMessage());
            return null;
        }
    }
}
