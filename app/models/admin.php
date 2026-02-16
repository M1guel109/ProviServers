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
        try {
            $this->conexion->beginTransaction();

            // ---------------------------------------------------------
            // 1. DETECTAR CAMBIO DE ROL (Vital para migrar datos)
            // ---------------------------------------------------------
            // Buscamos qué rol tenía antes de este cambio
            $stmtActual = $this->conexion->prepare("SELECT rol FROM usuarios WHERE id = :id");
            $stmtActual->execute([':id' => $data['id']]);
            $usuarioDb = $stmtActual->fetch(PDO::FETCH_ASSOC);
            $rolAnterior = $usuarioDb['rol'];

            // ---------------------------------------------------------
            // 2. ACTUALIZAR TABLA USUARIOS (Login)
            // ---------------------------------------------------------
            // Construimos la consulta dinámica (solo actualizamos clave si trajo una nueva)
            $sqlUser = "UPDATE usuarios SET email = :email, documento = :doc, rol = :rol, estado_id = :estado";
            if (!empty($data['clave'])) {
                $sqlUser .= ", clave = :clave";
            }
            $sqlUser .= " WHERE id = :id";

            $stmtUser = $this->conexion->prepare($sqlUser);
            
            $paramsUser = [
                ':email'  => $data['email'],
                ':doc'    => $data['documento'],
                ':rol'    => $data['rol'],
                ':estado' => $data['estado'], // ID numérico (1, 2...)
                ':id'     => $data['id']
            ];
            
            // Si hay nueva clave, la encriptamos
            if (!empty($data['clave'])) {
                $paramsUser[':clave'] = password_hash($data['clave'], PASSWORD_DEFAULT);
            }
            
            $stmtUser->execute($paramsUser);

            // ---------------------------------------------------------
            // 3. GESTIÓN DE PERFILES (Cliente / Proveedor / Admin)
            // ---------------------------------------------------------
            
            // CASO A: El rol NO cambió (Actualización normal)
            if ($rolAnterior === $data['rol']) {
                $tabla = $this->getTablaDetalle($data['rol']);
                
                $sqlPerfil = "UPDATE {$tabla} SET 
                            nombres = :nom, apellidos = :ape, telefono = :tel, 
                            ubicacion = :ubi, foto = :foto 
                            WHERE usuario_id = :uid";
                
                $stmtPerfil = $this->conexion->prepare($sqlPerfil);
                $stmtPerfil->execute([
                    ':nom'  => $data['nombres'],
                    ':ape'  => $data['apellidos'],
                    ':tel'  => $data['telefono'],
                    ':ubi'  => $data['ubicacion'],
                    ':foto' => $data['foto_perfil'],
                    ':uid'  => $data['id']
                ]);
            } 
            // CASO B: El rol CAMBIÓ (Migración de datos)
            else {
                // 1. Borramos el perfil en la tabla vieja
                $tablaVieja = $this->getTablaDetalle($rolAnterior);
                $stmtDel = $this->conexion->prepare("DELETE FROM {$tablaVieja} WHERE usuario_id = :uid");
                $stmtDel->execute([':uid' => $data['id']]);

                // 2. Creamos el perfil en la tabla nueva
                $tablaNueva = $this->getTablaDetalle($data['rol']);
                $sqlIns = "INSERT INTO {$tablaNueva} (usuario_id, nombres, apellidos, telefono, ubicacion, foto) 
                        VALUES (:uid, :nom, :ape, :tel, :ubi, :foto)";
                
                $stmtIns = $this->conexion->prepare($sqlIns);
                $stmtIns->execute([
                    ':uid'  => $data['id'],
                    ':nom'  => $data['nombres'],
                    ':ape'  => $data['apellidos'],
                    ':tel'  => $data['telefono'],
                    ':ubi'  => $data['ubicacion'],
                    ':foto' => $data['foto_perfil']
                ]);
            }

            // ---------------------------------------------------------
            // 4. LÓGICA PROVEEDOR (Categorías y Documentos)
            // ---------------------------------------------------------
            if ($data['rol'] === 'proveedor') {
                
                // Necesitamos el ID de la tabla 'proveedores' (no el usuario_id)
                $stmtPid = $this->conexion->prepare("SELECT id FROM proveedores WHERE usuario_id = :uid");
                $stmtPid->execute([':uid' => $data['id']]);
                $proveedor_id = $stmtPid->fetchColumn();

                // A. ACTUALIZAR CATEGORÍAS
                // Estrategia: Borrar todas las relaciones viejas e insertar las nuevas (Sincronización limpia)
                if (isset($data['categorias'])) {
                    $stmtDelCat = $this->conexion->prepare("DELETE FROM proveedor_categorias WHERE proveedor_id = :pid");
                    $stmtDelCat->execute([':pid' => $proveedor_id]);

                    if (!empty($data['categorias'])) {
                        // Queries preparadas para el bucle
                        $sqlCheck = "SELECT id FROM categorias WHERE nombre = :nom LIMIT 1";
                        $sqlInsCat = "INSERT INTO categorias (nombre) VALUES (:nom)";
                        $sqlRel = "INSERT INTO proveedor_categorias (proveedor_id, categoria_id) VALUES (:pid, :cid)";
                        
                        $stmtCheck = $this->conexion->prepare($sqlCheck);
                        $stmtInsCat = $this->conexion->prepare($sqlInsCat);
                        $stmtRel = $this->conexion->prepare($sqlRel);

                        foreach ($data['categorias'] as $catNombre) {
                            $catNombre = trim($catNombre);
                            if(empty($catNombre)) continue;

                            // 1. Verificar/Crear Categoría
                            $stmtCheck->execute([':nom' => $catNombre]);
                            $catId = $stmtCheck->fetchColumn();

                            if (!$catId) {
                                $stmtInsCat->execute([':nom' => $catNombre]);
                                $catId = $this->conexion->lastInsertId();
                            }

                            // 2. Crear Relación
                            try {
                                $stmtRel->execute([':pid' => $proveedor_id, ':cid' => $catId]);
                            } catch (PDOException $e) { /* Ignorar si ya existe */ }
                        }
                    }
                }

                // B. INSERTAR NUEVOS DOCUMENTOS (Solo los nuevos)
                // No borramos los viejos para no perder historial
                if (!empty($data['documentos_nuevos'])) {
                    $sqlDoc = "INSERT INTO documentos_proveedor (proveedor_id, tipo_documento, archivo, estado) 
                            VALUES (:pid, :tipo, :archivo, 'pendiente')";
                    $stmtDoc = $this->conexion->prepare($sqlDoc);

                    foreach ($data['documentos_nuevos'] as $doc) {
                        $stmtDoc->execute([
                            ':pid'     => $proveedor_id,
                            ':tipo'    => $doc['tipo'],
                            ':archivo' => $doc['archivo']
                        ]);
                    }
                }
            }

            $this->conexion->commit();
            return true;

        } catch (PDOException $e) {
            $this->conexion->rollBack();
            error_log("Error en Usuario::actualizar -> " . $e->getMessage());
            return false;
        }
    }

    public function eliminar($id)
    {
        try {
            $this->conexion->beginTransaction();

            // 1. Obtener datos del usuario
            $stmtUser = $this->conexion->prepare("SELECT rol, estado_id FROM usuarios WHERE id = :id");
            $stmtUser->execute([':id' => $id]);
            $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) return false;

            $rol = $usuario['rol'];
            $tieneHistorial = false;
            $pid = null; 

            // =========================================================
            // 2. VERIFICACIÓN DE HISTORIAL Y OBTENCIÓN DE IDs
            // =========================================================
            if ($rol === 'proveedor') {
                $stmtPid = $this->conexion->prepare("SELECT id FROM proveedores WHERE usuario_id = :id");
                $stmtPid->execute([':id' => $id]);
                $pid = $stmtPid->fetchColumn();

                if ($pid) {
                    // Si tienes tabla de servicios, descomenta esto:
                    /*
                    $stmtCheck = $this->conexion->prepare("SELECT COUNT(*) FROM servicios WHERE proveedor_id = :pid"); 
                    $stmtCheck->execute([':pid' => $pid]);
                    if ($stmtCheck->fetchColumn() > 0) $tieneHistorial = true;
                    */
                }

            } elseif ($rol === 'cliente') {
                $stmtCid = $this->conexion->prepare("SELECT id FROM clientes WHERE usuario_id = :id");
                $stmtCid->execute([':id' => $id]);
                $cid = $stmtCid->fetchColumn();

                /*
                if ($cid) {
                   $stmtCheck = $this->conexion->prepare("SELECT COUNT(*) FROM servicios WHERE cliente_id = :cid");
                   $stmtCheck->execute([':cid' => $cid]);
                   if ($stmtCheck->fetchColumn() > 0) $tieneHistorial = true;
                }
                */
            }

            // =========================================================
            // 3. EJECUCIÓN (Lógica vs Física)
            // =========================================================
            if ($tieneHistorial) {
                // A. Desactivar (Borrado Lógico)
                $sqlSoft = "UPDATE usuarios SET estado_id = 4 WHERE id = :id"; 
                $stmtSoft = $this->conexion->prepare($sqlSoft);
                $stmtSoft->execute([':id' => $id]);
                
                $this->conexion->commit();
                return 'desactivado';

            } else {
                // B. Eliminar Definitivamente (Borrado Físico)
                $tablaDetalle = $this->getTablaDetalle($rol);

                // --- LIMPIEZA DE DEPENDENCIAS (Solo Proveedores) ---
                if ($rol === 'proveedor' && $pid) {
                    // Categorías (Asegúrate de usar el nombre de columna correcto que te funcionó)
                    $delCat = $this->conexion->prepare("DELETE FROM proveedor_categorias WHERE proveedor_id = :pid");
                    $delCat->execute([':pid' => $pid]);

                    // Documentos (Asegúrate de usar el nombre de columna correcto)
                    $delDoc = $this->conexion->prepare("DELETE FROM documentos_proveedor WHERE proveedor_id = :pid");
                    $delDoc->execute([':pid' => $pid]);
                }

                // 3. Borrar Perfil
                $sqlDetalle = "DELETE FROM {$tablaDetalle} WHERE usuario_id = :id";
                $stmtDetalle = $this->conexion->prepare($sqlDetalle);
                $stmtDetalle->execute([':id' => $id]);

                // 4. Borrar Usuario Base
                $eliminar = "DELETE FROM usuarios WHERE id = :id";
                $stmtEliminar = $this->conexion->prepare($eliminar);
                $stmtEliminar->execute([':id' => $id]);

                $this->conexion->commit();
                return 'eliminado';
            }

        } catch (PDOException $e) {
            $this->conexion->rollBack();
            // Logueamos el error internamente pero devolvemos false al controlador
            error_log("Error en Usuario::eliminar -> " . $e->getMessage());
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
