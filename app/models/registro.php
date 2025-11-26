<?php
// Asegúrate de que la ruta a tu archivo de conexión sea correcta
require_once __DIR__ . '/../../config/database.php';

class Registro
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion(); 
        $this->conexion = $db->getConexion();
    }

    /**
     * Procesa el registro de un nuevo usuario en hasta 3 tablas: usuarios, detalle (clientes/proveedores), y documentos (solo proveedores).
     * @param array $data Contiene: documento, email, clave, rol, nombres, apellidos, telefono, ubicacion, foto, documentos.
     * @return bool|string Retorna true, false o "duplicado".
     */
    public function registrar($data)
    {
        try {
            // 1. INICIAR TRANSACCIÓN: Asegura la integridad de los datos.
            $this->conexion->beginTransaction();

            // 1.1. Encriptar la clave
            $claveHash = password_hash($data['clave'], PASSWORD_DEFAULT);

            // 2. PRIMERA INSERCIÓN: TABLA 'usuarios'
            $sqlUsuario = "INSERT INTO usuarios (email, clave, documento, rol, estado) 
                           VALUES (:email, :clave, :documento, :rol, 1)";
            
            $stmtUsuario = $this->conexion->prepare($sqlUsuario);
            $stmtUsuario->bindParam(':email', $data['email']);
            $stmtUsuario->bindParam(':clave', $claveHash);
            $stmtUsuario->bindParam(':documento', $data['documento']);
            $stmtUsuario->bindParam(':rol', $data['rol']);
            
            $stmtUsuario->execute();
            
            // 3. OBTENER EL ID DEL USUARIO RECIÉN INSERTADO (CRÍTICO)
            $usuario_id = $this->conexion->lastInsertId();
            
            if (!$usuario_id) {
                throw new Exception("No se pudo obtener el ID del usuario después de la primera inserción.");
            }

            // 4. SEGUNDA INSERCIÓN: TABLAS DE DETALLE (clientes, proveedores, o admins)
            $rol = $data['rol'];
            
            // Determinar la tabla de detalle
            if ($rol === 'cliente') {
                $tablaDetalle = 'clientes';
            } elseif ($rol === 'proveedor') {
                $tablaDetalle = 'proveedores';
            } else {
                throw new Exception("Rol no válido: " . $rol);
            }
            
            // 4.1. Ejecutar la inserción en la tabla de detalle 
            $sqlDetalle = "INSERT INTO {$tablaDetalle} (usuario_id, nombres, apellidos, telefono, ubicacion, foto)
                           VALUES (:usuario_id, :nombres, :apellidos, :telefono, :ubicacion, :foto)";
            
            $stmtDetalle = $this->conexion->prepare($sqlDetalle);
            $stmtDetalle->bindParam(':usuario_id', $usuario_id);
            $stmtDetalle->bindParam(':nombres', $data['nombres']); 
            $stmtDetalle->bindParam(':apellidos', $data['apellidos']); 
            $stmtDetalle->bindParam(':telefono', $data['telefono']);
            $stmtDetalle->bindParam(':ubicacion', $data['ubicacion']);
            $stmtDetalle->bindParam(':foto', $data['foto']);
            
            $stmtDetalle->execute();

            $detalle_id = $this->conexion->lastInsertId();


            // 5. TERCERA INSERCIÓN (CONDICIONAL): TABLA 'documentos_proveedor'
            if ($rol === 'proveedor' && !empty($data['documentos'])) {
                $sqlDocumento = "INSERT INTO documentos_proveedor (proveedor_id, tipo_documento, archivo) 
                     VALUES (:proveedor_id, :tipo_documento, :archivo)";
                $stmtDocumento = $this->conexion->prepare($sqlDocumento);

                // Mapeo de campos del controlador a tipos de documento de la DB
                $documentos_map = [
                    'doc-cedula' => 'dni',
                    'doc-foto' => 'otro',
                    'doc-antecedentes' => 'otro',
                    'doc-certificado' => 'certificado'
                ];

                foreach ($data['documentos'] as $campo_name => $ruta_doc) {
                    $tipo_doc = $documentos_map[$campo_name] ?? $campo_name;
                    
                    $stmtDocumento->bindParam(':proveedor_id', $detalle_id); 
                    $stmtDocumento->bindParam(':tipo_documento', $tipo_doc);
                    $stmtDocumento->bindParam(':archivo', $ruta_doc);
                    
                    $stmtDocumento->execute();
                }
            }


            // 6. CONFIRMAR TRANSACCIÓN: Si todo fue bien, guardar los cambios permanentemente.
            $this->conexion->commit();
            return true;

        } catch (PDOException $e) {
            // Revertir si algo falló
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            // Código 23000 = Clave duplicada (ej: email o documento ya existen)
            if ($e->getCode() === '23000') {
                error_log("Error de duplicidad en registro: " . $e->getMessage());
                return "duplicado"; // Señal para el controlador
            }
            error_log("Error PDO en RegistroModel::registrar -> " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            // Revertir si hay un error lógico
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error lógico en RegistroModel::registrar -> " . $e->getMessage());
            return false;
        }
    }
}