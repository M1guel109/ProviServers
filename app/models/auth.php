<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/mailer-helper.php';

class Auth
{
    private $conexion;

    public function __construct()
    {
        $db = new Conexion();
        $this->conexion = $db->getConexion();
    }

    // ======================================================================
    // 1. AUTENTICACIÓN (LOGIN)
    // ======================================================================

    private const MAX_INTENTOS   = 3;
    private const BLOQUEO_MINUTOS = 30;

    private function autoMigrarColumnasBruteForce(): void
    {
        try {
            $this->conexion->exec("
                ALTER TABLE usuarios
                    ADD COLUMN IF NOT EXISTS intentos_login   INT          NOT NULL DEFAULT 0,
                    ADD COLUMN IF NOT EXISTS bloqueado_hasta  DATETIME     NULL
            ");
        } catch (PDOException) {}
    }

    public function autenticar($correo, $clave)
    {
        try {
            // 1. Validación de entrada
            if (empty($correo) || empty($clave)) {
                return ['error' => 'Correo y contraseña son requeridos'];
            }

            $this->autoMigrarColumnasBruteForce();

            // 2. Búsqueda de usuario en BD
            $sql = "SELECT u.*, e.nombre AS estado_nombre
                    FROM usuarios u
                    INNER JOIN usuario_estados e ON u.estado_id = e.id
                    WHERE u.email = :correo LIMIT 1";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':correo' => $correo]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // 3. Validación de existencia del usuario
            if (!$usuario) {
                return ['error' => 'Usuario no encontrado o inactivo'];
            }

            // 4. Verificar bloqueo temporal por fuerza bruta
            if (!empty($usuario['bloqueado_hasta'])) {
                $hasta = strtotime($usuario['bloqueado_hasta']);
                if ($hasta > time()) {
                    $minutos = ceil(($hasta - time()) / 60);
                    return [
                        'error'       => "Tu cuenta está bloqueada temporalmente. Intenta de nuevo en {$minutos} min.",
                        'bloqueado_temp' => true,
                    ];
                }
                // Bloqueo expirado — limpiar contador
                $this->conexion->prepare(
                    "UPDATE usuarios SET intentos_login = 0, bloqueado_hasta = NULL WHERE id = :id"
                )->execute([':id' => $usuario['id']]);
                $usuario['intentos_login'] = 0;
            }

            // 5. Validación de contraseña encriptada
            if (!password_verify($clave, $usuario['clave'])) {
                $intentos = (int)($usuario['intentos_login'] ?? 0) + 1;

                if ($intentos >= self::MAX_INTENTOS) {
                    $hasta = date('Y-m-d H:i:s', time() + self::BLOQUEO_MINUTOS * 60);
                    $this->conexion->prepare(
                        "UPDATE usuarios SET intentos_login = :i, bloqueado_hasta = :h WHERE id = :id"
                    )->execute([':i' => $intentos, ':h' => $hasta, ':id' => $usuario['id']]);

                    return [
                        'error'          => 'Has superado el límite de intentos. Tu cuenta queda bloqueada ' . self::BLOQUEO_MINUTOS . ' minutos.',
                        'bloqueado_temp' => true,
                    ];
                }

                $this->conexion->prepare(
                    "UPDATE usuarios SET intentos_login = :i WHERE id = :id"
                )->execute([':i' => $intentos, ':id' => $usuario['id']]);

                $restantes = self::MAX_INTENTOS - $intentos;
                return [
                    'error'      => 'Contraseña incorrecta.',
                    'restantes'  => $restantes,
                ];
            }

            // 6. Contraseña correcta — resetear contador
            $this->conexion->prepare(
                "UPDATE usuarios SET intentos_login = 0, bloqueado_hasta = NULL WHERE id = :id"
            )->execute([':id' => $usuario['id']]);

            // 7. Retornar datos del usuario autenticado
            return [
                'id'     => $usuario['id'],
                'rol'    => $usuario['rol'],
                'email'  => $usuario['email'],
                'estado' => $usuario['estado_nombre']
            ];
        } catch (PDOException $e) {
            error_log("Error en Auth::autenticar -> " . $e->getMessage());
            return ['error' => 'Error interno del servidor'];
        }
    }

    // ======================================================================
    // 2. REGISTRO DE USUARIOS
    // ======================================================================

    public function registrarUsuario($data)
    {
        try {
            // 1. Validación de entrada
            if (empty($data['email']) || empty($data['clave']) || empty($data['documento']) || empty($data['rol'])) {
                return ['error' => 'Campos requeridos: email, clave, documento, rol'];
            }

            $this->conexion->beginTransaction();

            // 2. Insertar en tabla USUARIOS
            $claveHash = password_hash($data['clave'], PASSWORD_DEFAULT);
            $estadoId = $data['estado_id'] ?? ($data['rol'] === 'cliente' ? $this->obtenerEstadoId('activo') : $this->obtenerEstadoId('pendiente'));

            $sqlUsuario = "INSERT INTO usuarios (email, clave, documento, rol, estado_id)
                          VALUES (:email, :clave, :doc, :rol, :estado)";

            $stmt = $this->conexion->prepare($sqlUsuario);
            $stmt->execute([
                ':email'  => $data['email'],
                ':clave'  => $claveHash,
                ':doc'    => $data['documento'],
                ':rol'    => $data['rol'],
                ':estado' => $estadoId
            ]);

            $usuario_id = $this->conexion->lastInsertId();

            // 3. Insertar en tabla ESPECÍFICA (clientes/proveedores)
            $tabla = ($data['rol'] === 'cliente') ? 'clientes' : 'proveedores';

            $sqlDetalle = "INSERT INTO {$tabla} (usuario_id, nombres, apellidos, telefono, ubicacion, foto)
                          VALUES (:uid, :nom, :ape, :tel, :ubi, :foto)";

            $stmtDetalle = $this->conexion->prepare($sqlDetalle);
            $stmtDetalle->execute([
                ':uid'  => $usuario_id,
                ':nom'  => $data['nombres'] ?? '',
                ':ape'  => $data['apellidos'] ?? '',
                ':tel'  => $data['telefono'] ?? '',
                ':ubi'  => $data['ubicacion'] ?? '',
                ':foto' => $data['foto'] ?? 'default_user.png'
            ]);

            $detalle_id = $this->conexion->lastInsertId();

            // 4. LÓGICA EXTRA PARA PROVEEDORES
            if ($data['rol'] === 'proveedor') {
                // A. Asignación de membresía inicial — buscar ID real o usar el primero disponible
                $membresiaId = $data['id_membresia_defecto'] ?? 1;
                try {
                    $stmtMem = $this->conexion->prepare("SELECT id FROM membresias WHERE id = :id LIMIT 1");
                    $stmtMem->execute([':id' => $membresiaId]);
                    if (!$stmtMem->fetchColumn()) {
                        $membresiaId = $this->conexion->query("SELECT id FROM membresias ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 1;
                    }
                } catch (PDOException $e) { /* continuar con el ID por defecto */ }

                $sqlMembresia = "INSERT INTO proveedor_membresia (proveedor_id, membresia_id, estado)
                                VALUES (:pid, :mid, 'inactiva')";
                $this->conexion->prepare($sqlMembresia)->execute([
                    ':pid' => $detalle_id,
                    ':mid' => $membresiaId,
                ]);

                // B. Guardar documentos
                if (!empty($data['documentos']) && is_array($data['documentos'])) {
                    $sqlDoc = "INSERT INTO documentos_proveedor (proveedor_id, tipo_documento, archivo, estado)
                              VALUES (:pid, :tipo, :archivo, 'pendiente')";
                    $stmtDoc = $this->conexion->prepare($sqlDoc);

                    $mapDocumentos = [
                        'doc-cedula' => 'dni',
                        'doc-foto' => 'selfie',
                        'doc-antecedentes' => 'antecedentes',
                        'doc-certificado' => 'certificado'
                    ];

                    foreach ($data['documentos'] as $campo => $ruta) {
                        $tipo = $mapDocumentos[$campo] ?? $campo;
                        $stmtDoc->execute([
                            ':pid'     => $detalle_id,
                            ':tipo'    => $tipo,
                            ':archivo' => $ruta
                        ]);
                    }
                }

                // C. Guardar categorías (habilidades)
                if (!empty($data['habilidades']) && is_array($data['habilidades'])) {
                    $sqlCheck = "SELECT id FROM categorias WHERE nombre = :nombre LIMIT 1";
                    $sqlInsert = "INSERT INTO categorias (nombre) VALUES (:nombre)";
                    $sqlRel = "INSERT INTO proveedor_categorias (proveedor_id, categoria_id) VALUES (:pid, :cid)";

                    $stmtCheck = $this->conexion->prepare($sqlCheck);
                    $stmtInsert = $this->conexion->prepare($sqlInsert);
                    $stmtRel = $this->conexion->prepare($sqlRel);

                    foreach ($data['habilidades'] as $nombreCat) {
                        $nombreCat = trim($nombreCat);
                        if (empty($nombreCat)) continue;

                        $stmtCheck->execute([':nombre' => $nombreCat]);
                        $catId = $stmtCheck->fetchColumn();

                        if (!$catId) {
                            $stmtInsert->execute([':nombre' => $nombreCat]);
                            $catId = $this->conexion->lastInsertId();
                        }

                        try {
                            $stmtRel->execute([':pid' => $detalle_id, ':cid' => $catId]);
                        } catch (PDOException $e) {
                            // Relación ya existe, continuar
                        }
                    }
                }
            }

            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) $this->conexion->rollBack();
            if ($e->getCode() === '23000') return 'duplicado';
            error_log("Auth::registrarUsuario [SQLSTATE {$e->getCode()}] -> " . $e->getMessage());
            return false;
        }
    }

    // ======================================================================
    // 3. RECUPERACIÓN DE CONTRASEÑA
    // ======================================================================

    public function recuperarClave($email)
    {
        try {
            // 1. Validación de entrada
            if (empty($email)) {
                return ['error' => 'El email es requerido'];
            }

            // 2. Búsqueda de usuario activo
            $sql = "SELECT id, email, rol FROM usuarios WHERE email = :email AND estado_id = 2 LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                return ['error' => 'Usuario no encontrado o inactivo'];
            }

            // 3. Obtener datos del perfil
            $tabla = $this->obtenerTablaPerfil($usuario['rol']);
            $nombreAmigable = 'Usuario';

            if ($tabla) {
                $sqlPerfil = "SELECT nombres FROM {$tabla} WHERE usuario_id = :uid LIMIT 1";
                $stmtPerfil = $this->conexion->prepare($sqlPerfil);
                $stmtPerfil->execute([':uid' => $usuario['id']]);
                $perfil = $stmtPerfil->fetch(PDO::FETCH_ASSOC);
                if ($perfil) {
                    $nombreAmigable = trim($perfil['nombres']);
                }
            }

            // 4. Generar nueva contraseña temporal
            $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
            $nuevaClave = substr(str_shuffle($caracteres), 0, 8);
            $claveHash = password_hash($nuevaClave, PASSWORD_DEFAULT);

            // 5. Actualizar contraseña en BD
            $sqlUpdate = "UPDATE usuarios SET clave = :clave WHERE id = :id";
            $stmtUpdate = $this->conexion->prepare($sqlUpdate);
            $stmtUpdate->execute([
                ':clave' => $claveHash,
                ':id'    => $usuario['id']
            ]);

            // 6. Enviar correo con nueva contraseña
            $this->enviarCorreoRecuperacion($usuario['email'], $nombreAmigable, $nuevaClave);

            return true;
        } catch (PDOException $e) {
            error_log("Error en Auth::recuperarClave -> " . $e->getMessage());
            return ['error' => 'Error interno del servidor'];
        }
    }

    // ======================================================================
    // 4. MÉTODOS UTILITARIOS
    // ======================================================================

    private function obtenerEstadoId($nombreEstado)
    {
        // IDs por defecto según convención del proyecto
        $fallback = ['activo' => 2, 'pendiente' => 1];

        try {
            $sql = "SELECT id FROM usuario_estados WHERE nombre = :nombre LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([':nombre' => $nombreEstado]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) return (int)$resultado['id'];

            // Tabla existe pero no tiene el registro → usar fallback
            return $fallback[$nombreEstado] ?? 1;
        } catch (PDOException $e) {
            error_log("Auth::obtenerEstadoId -> " . $e->getMessage());
            // Tabla no existe en este entorno → usar fallback
            return $fallback[$nombreEstado] ?? 1;
        }
    }

    private function obtenerTablaPerfil($rol)
    {
        return match ($rol) {
            'cliente'   => 'clientes',
            'proveedor' => 'proveedores',
            'admin'     => 'admins',
            default     => null
        };
    }

    private function enviarCorreoRecuperacion($email, $nombre, $nuevaClave)
    {
        try {
            $mail = mailer_init();
            $mail->SMTPDebug = 0;
            $mail->setFrom('suppportproviservers@gmail.com', 'Soporte Proviservers');
            $mail->addAddress($email);
            $mail->Subject = "PROVISERVERS - NUEVA CLAVE GENERADA";
            $mail->Body = $this->construirHtmlRecuperacion($nombre, $email, $nuevaClave);
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error al enviar correo de recuperación: " . $e->getMessage());
            return false;
        }
    }

    private function construirHtmlRecuperacion($nombre, $email, $clave)
    {
        return '
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
                <table cellpadding="0" cellspacing="0" align="center" width="600" style="background:#0066ff;color:white;border-radius:4px 4px 0 0">
                    <tr>
                        <td align="center" style="padding:25px">
                            <p style="font-size:20px;margin:0;color:white;">Recuperación de contraseña</p>
                            <img src="https://raw.githubusercontent.com/M1guel109/Proviservers-img/refs/heads/main/logos/LOGO%20POSITIVO.png"
                                alt="Logo Proviservers" width="200" style="display:block;margin-top:15px">
                        </td>
                    </tr>
                </table>
                <table align="center" cellpadding="0" cellspacing="0" width="600" style="background:#FFFFFF;">
                    <tr>
                        <td style="padding:40px 20px;text-align:center;">
                            <h1 style="color:#0E1116;margin:0;font-size:24px;">Tu nueva contraseña temporal</h1>
                            <p style="color:#444;font-size:15px;margin-top:15px;">
                                ' . htmlspecialchars($nombre) . ', has solicitado recuperar el acceso a tu cuenta en <strong>Proviservers</strong>.<br>
                                Aquí tienes tu nueva contraseña temporal.
                            </p>
                            <p style="margin-top:30px;font-size:15px;color:#0E1116;">
                                <strong>📧 Email asociado:</strong><br>
                                <span style="color: #0E1116;">' . htmlspecialchars($email) . '</span>
                            </p>
                            <p style="margin-top:20px;font-size:16px;color:#0E1116;">
                                <strong>🔐 Contraseña temporal:</strong><br>
                                <span style="display:inline-block;margin-top:8px;padding:10px 18px;background:#0066FF;color:white;border-radius:6px;font-size:18px;font-weight:bold;">
                                    ' . htmlspecialchars($clave) . '
                                </span>
                            </p>
                            <p style="margin-top:25px;color:#444;">
                                Te recomendamos cambiar esta contraseña inmediatamente después de ingresar.<br>
                                Si no solicitaste este cambio, ignora este correo.
                            </p>
                        </td>
                    </tr>
                </table>
                <table align="center" cellpadding="0" cellspacing="0" width="600" style="background:#0e1116;color:white;border-radius:0 0 4px 4px">
                    <tr>
                        <td style="padding:30px;text-align:left;font-size:14px;line-height:20px;">
                            <img src="https://raw.githubusercontent.com/M1guel109/Proviservers-img/refs/heads/main/logos/LOGO%20POSITIVO.png"
                                width="150" style="display:block;margin-bottom:15px">
                            <p style="margin:0;">© 2025 Proviservers — Plataforma de servicios locales.</p>
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
    }

    public function obtenerTodasCategorias()
    {
        try {
            $sql = "SELECT id, nombre FROM categorias ORDER BY nombre ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Auth::obtenerTodasCategorias -> " . $e->getMessage());
            return [];
        }
    }
}
