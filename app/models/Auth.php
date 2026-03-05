<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/mailer_helper.php';

class Auth
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

        // 2. Función para traer todas las categorías de la BD
    public function obtenerTodasCategorias()
    {
        try {
            $stmt = $this->conexion->query("SELECT * FROM categorias ORDER BY nombre ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }  

    // ======================================================================
    // 1. MÉTODOS DE AUTENTICACIÓN (LOGIN)
    // ======================================================================

    public function autenticar($correo, $clave)
    {
        try {
            $consultar = "SELECT u.*, e.nombre AS estado_nombre 
                          FROM usuarios u 
                          INNER JOIN usuario_estados e ON u.estado_id = e.id 
                          WHERE u.email = :correo LIMIT 1";

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':correo', $correo);
            $resultado->execute();

            $user = $resultado->fetch();

            if (!$user) {
                return ['error' => 'Usuario no encontrado o inactivo'];
            }

            // Verificar contraseña encriptada 
            if (!password_verify($clave, $user['clave'])) {
                return ['error' => 'Contraseña incorrecta'];
            }

            // Retornar los datos del ususario autenticado 
            return [
                'id'     => $user['id'],
                'rol'    => $user['rol'],
                'email'  => $user['email'],
                'estado' => $user['estado_nombre']
            ];
        } catch (PDOException $e) {
            error_log("Error en Auth::autenticar: " . $e->getMessage());
            return ['error' => 'Error interno del servidor'];
        }
    }

    // ======================================================================
    // 2. MÉTODOS DE REGISTRO
    // ======================================================================

    private function getEstadoId($nombreEstado)
    {
        $sql = "SELECT id FROM usuario_estados WHERE nombre = :nombre LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombreEstado);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $row['id'] : null;
    }

    public function registrarUsuario($data)
    {
        try {
            $this->conexion->beginTransaction();

            // 1. Insertar en tabla USUARIOS
            $claveHash = password_hash($data['clave'], PASSWORD_DEFAULT);

            // Obtener estado_id si no viene en el $data (manteniendo tu lógica de cliente/proveedor)
            $estadoId = $data['estado_id'] ?? ($data['rol'] === 'cliente' ? $this->getEstadoId('activo') : $this->getEstadoId('pendiente'));

            $sqlUser = "INSERT INTO usuarios (email, clave, documento, rol, estado_id) 
                    VALUES (:email, :clave, :doc, :rol, :estado)";

            $stmt = $this->conexion->prepare($sqlUser);
            $stmt->execute([
                ':email'  => $data['email'],
                ':clave'  => $claveHash,
                ':doc'    => $data['documento'],
                ':rol'    => $data['rol'],
                ':estado' => $estadoId
            ]);

            $usuario_id = $this->conexion->lastInsertId();

            // 2. Insertar en tabla ESPECÍFICA (clientes/proveedores)
            $tablaDetalle = ($data['rol'] === 'cliente') ? 'clientes' : 'proveedores';

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

            // Guardamos el ID de la tabla detalle (especialmente útil para proveedores)
            $detalle_id = $this->conexion->lastInsertId();

            // =========================================================
            // 3. LÓGICA EXTRA PARA PROVEEDORES (Membresía + Docs + Categorías)
            // =========================================================
            if ($data['rol'] === 'proveedor') {

                // A. Asignación de Membresía
                $sqlMembresia = "INSERT INTO proveedor_membresia (proveedor_id, membresia_id, estado) 
                             VALUES (:pid, :mid, 'inactiva')";
                $this->conexion->prepare($sqlMembresia)->execute([
                    ':pid' => $detalle_id,
                    ':mid' => $data['id_membresia_defecto'] ?? 4
                ]);

                // B. Guardar Documentos
                if (!empty($data['documentos'])) {
                    $sqlDoc = "INSERT INTO documentos_proveedor (proveedor_id, tipo_documento, archivo, estado) 
                           VALUES (:pid, :tipo, :archivo, 'pendiente')";
                    $stmtDoc = $this->conexion->prepare($sqlDoc);

                    $documentos_map = [
                        'doc-cedula' => 'dni',
                        'doc-foto' => 'selfie',
                        'doc-antecedentes' => 'antecedentes',
                        'doc-certificado' => 'certificado'
                    ];

                    foreach ($data['documentos'] as $campo_name => $ruta_doc) {
                        $tipo_doc = $documentos_map[$campo_name] ?? $campo_name;
                        $stmtDoc->execute([
                            ':pid'     => $detalle_id,
                            ':tipo'    => $tipo_doc,
                            ':archivo' => $ruta_doc
                        ]);
                    }
                }

                // C. Guardar Categorías (Dinámicas - Tal cual tu Admin)
                if (!empty($data['habilidades'])) { // Cambiado de 'categorias' a 'habilidades' para coincidir con el controlador
                    $sqlCheckCat  = "SELECT id FROM categorias WHERE nombre = :nombre LIMIT 1";
                    $sqlInsertCat = "INSERT INTO categorias (nombre) VALUES (:nombre)";
                    $sqlRelacion  = "INSERT INTO proveedor_categorias (proveedor_id, categoria_id) VALUES (:pid, :cid)";

                    $stmtCheck = $this->conexion->prepare($sqlCheckCat);
                    $stmtInCat = $this->conexion->prepare($sqlInsertCat);
                    $stmtRel   = $this->conexion->prepare($sqlRelacion);

                    foreach ($data['habilidades'] as $nombreCat) {
                        $nombreCat = trim($nombreCat);
                        if (empty($nombreCat)) continue;

                        // 1. Verificar si existe la categoría
                        $stmtCheck->execute([':nombre' => $nombreCat]);
                        $catId = $stmtCheck->fetchColumn();

                        // 2. Si no existe, crearla
                        if (!$catId) {
                            $stmtInCat->execute([':nombre' => $nombreCat]);
                            $catId = $this->conexion->lastInsertId();
                        }

                        // 3. Crear relación entre proveedor y categoría
                        try {
                            $stmtRel->execute([
                                ':pid' => $detalle_id,
                                ':cid' => $catId
                            ]);
                        } catch (PDOException $e) {
                            // Ignorar si la relación ya existe (evitar error 23000)
                        }
                    }
                }
            }

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) $this->conexion->rollBack();
            if ($e->getCode() === '23000') return "duplicado";
            error_log("Error BD en registrarUsuario: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            if ($this->conexion->inTransaction()) $this->conexion->rollBack();
            error_log("Error General en registrarUsuario: " . $e->getMessage());
            return false;
        }
    }

    // ======================================================================
    // 3. MÉTODOS DE RECUPERACIÓN DE CLAVE
    // ======================================================================

    public function recuperarClave($email)
    {
        try {
            $consultar = "SELECT id, email, rol FROM usuarios WHERE email = :email AND estado_id = 2 LIMIT 1";

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':email', $email);
            $resultado->execute();

            $user = $resultado->fetch();

            if ($user) {
                $userId = $user['id'];
                $rol = $user['rol'];
                $nombreAmigable = '';

                // 2. CONSULTA DE PERFIL
                $consultarPerfil = "";
                $tablaPerfil = "";

                if ($rol == 'admin') {
                    $tablaPerfil = 'admins';
                } elseif ($rol == 'proveedor') {
                    $tablaPerfil = 'proveedores';
                } elseif ($rol == 'cliente') {
                    $tablaPerfil = 'clientes';
                }

                if ($tablaPerfil !== '') {
                    $consultarPerfil = "SELECT nombres, apellidos FROM {$tablaPerfil} WHERE usuario_id = :userId LIMIT 1";
                    $resultadoPerfil = $this->conexion->prepare($consultarPerfil);
                    $resultadoPerfil->bindParam(':userId', $userId);
                    $resultadoPerfil->execute();
                    $perfil = $resultadoPerfil->fetch(PDO::FETCH_ASSOC);

                    if ($perfil) {
                        $nombreAmigable = trim($perfil['nombres']);
                    }
                }

                if (empty($nombreAmigable)) {
                    $nombreAmigable = 'Usuario';
                }

                // Generamos la nueva contraseña 
                $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
                $random = str_shuffle($caracteres);
                $nuevaClave = substr($random, 0, 8);
                $claveHash = password_hash($nuevaClave, PASSWORD_DEFAULT);

                // Actualizar DB
                $actualizar = "UPDATE usuarios SET clave = :nuevaClave WHERE id = :id";
                $resultado = $this->conexion->prepare($actualizar);
                $resultado->bindParam(':nuevaClave', $claveHash);
                $resultado->bindParam(':id', $user['id']);
                $resultado->execute();

                // Enviar Correo
                $mail = mailer_init();
                $mail->SMTPDebug = 0;
                $mail->setFrom('suppportproviservers@gmail.com', 'Soporte Proviservers');
                $mail->addAddress($user['email']);
                $mail->Subject = "PROVISERVERS - NUEVA CLAVE GENERADA";

                // Aquí va todo tu bloque de HTML del correo, exactamente como lo tenías.
                // (Lo acorto un poco aquí para la vista, pero tú copias todo el HTML que tenías en RecoveryPass)
                $mail->Body = '
                        <!DOCTYPE html>
                            <html>
                            <head>
                                <meta charset="UTF-8">
                                <meta content="width=device-width, initial-scale=1" name="viewport">
                                <meta name="x-apple-disable-message-reformatting">
                                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                                <title>Recuperación de contraseña - Proviservers</title>
                                <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet">
                            </head>
                            <body style="margin:0;padding:0;background:#d4d4d4;font-family: Open Sans, Arial, sans-serif">
                            <table cellspacing="0" cellpadding="0" width="100%" class="es-wrapper">
                            <tr>
                            <td valign="top">
                                <!-- HEADER -->
                                <table cellpadding="0" cellspacing="0" align="center" width="600" style="background:#0066ff;color:white;border-radius:4px 4px 0 0">
                                    <tr>
                                        <td align="center" style="padding:25px">

                                            <p style="font-size:20px;margin:0;color:white;">
                                                Recuperación de contraseña
                                            </p>

                                            <img src="https://raw.githubusercontent.com/M1guel109/Proviservers-img/refs/heads/main/logos/LOGO%20POSITIVO.png"
                                                alt="Logo Proviservers"
                                                width="200"
                                                style="display:block;margin-top:15px">
                                        </td>
                                    </tr>
                                </table>
                                <!-- CONTENIDO -->
                                <table align="center" cellpadding="0" cellspacing="0" width="600" style="background:#FFFFFF;">
                                    <tr>
                                        <td style="padding:40px 20px;text-align:center;">
                                            <h1 style="color:#0E1116;margin:0;font-size:24px;">
                                                Tu nueva contraseña temporal
                                            </h1>

                                            <p style="color:#444;font-size:15px;margin-top:15px;">
                                                ' . htmlspecialchars($nombreAmigable) . ' Has solicitado recuperar el acceso a tu cuenta en  
                                                <strong>Proviservers</strong>.<br>
                                                Aquí tienes tu nueva contraseña temporal.
                                            </p>

                                           <p style="margin-top:30px;font-size:15px;color:#0E1116;">
                                                <strong>📧 Email asociado:</strong><br>
                                                <a href="#" style="color: #0E1116; text-decoration: none; pointer-events: none; cursor: text;">
                                                    ' . htmlspecialchars($email) . '
                                                </a>
                                            </p>

                                            <p style="margin-top:20px;font-size:16px;color:#0E1116;">
                                                <strong>🔐 Contraseña temporal:</strong><br>
                                                <span style="display:inline-block;margin-top:8px;padding:10px 18px;background:#0066FF;color:white;border-radius:6px;font-size:18px;font-weight:bold;">
                                                    ' . htmlspecialchars($nuevaClave) . '
                                                </span>
                                            </p>

                                            <p style="margin-top:25px;color:#444;">
                                                Te recomendamos cambiar esta contraseña inmediatamente después de ingresar.<br>
                                                Si no solicitaste este cambio, ignora este correo.
                                            </p>

                                        </td>
                                    </tr>
                                </table>
                                <!-- FOOTER -->
                                <table align="center" cellpadding="0" cellspacing="0" width="600" style="background:#0e1116;color:white;border-radius:0 0 4px 4px">
                                    <tr>
                                        <td style="padding:30px;text-align:left;font-size:14px;line-height:20px;">

                                            <img src="https://raw.githubusercontent.com/M1guel109/Proviservers-img/refs/heads/main/logos/LOGO%20POSITIVO.png" 
                                                width="150"
                                                style="display:block;margin-bottom:15px">

                                            <p style="margin:0;">
                                                © 2025 Proviservers — Plataforma de servicios locales.
                                            </p>

                                            <p style="margin:5px 0 0 0;">
                                                Este correo fue generado automáticamente.<br>
                                                Si no realizaste esta solicitud puedes ignorarlo sin problema.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            </tr>
                            </table>
                            </body>
                            </html>
                    ';

                $mail->send();
                return true;
            } else {
                return ['error' => 'Usuario no encontrado o inactivo'];
            }
        } catch (PDOException $e) {
            error_log("Error en Auth::recuperarClave: " . $e->getMessage());
            return ['error' => 'error interno del servidor'];
        }
    }
}
