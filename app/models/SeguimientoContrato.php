<?php

require_once __DIR__ . '/../../config/database.php';

class SeguimientoContrato
{
    private static ?PDO $db = null;

    private static function pdo(): PDO
    {
        if (self::$db === null) {
            self::$db = (new Conexion())->getConexion();
        }
        return self::$db;
    }

    public static function registrar(
        int     $contratoId,
        int     $usuarioId,
        string  $rol,
        ?string $estadoNuevo     = null,
        ?string $estadoAnterior  = null,
        ?string $descripcion     = null,
        ?string $comentario      = null,
        ?string $archivoAdjunto  = null
    ): bool {
        try {
            $st = self::pdo()->prepare("
                INSERT INTO seguimiento_contrato
                    (servicio_contratado_id, estado_anterior, estado_nuevo,
                     descripcion, comentario, archivo_adjunto, usuario_id, rol)
                VALUES
                    (:cid, :ea, :en, :desc, :com, :arch, :uid, :rol)
            ");
            return $st->execute([
                ':cid'  => $contratoId,
                ':ea'   => $estadoAnterior,
                ':en'   => $estadoNuevo,
                ':desc' => $descripcion,
                ':com'  => $comentario,
                ':arch' => $archivoAdjunto,
                ':uid'  => $usuarioId,
                ':rol'  => $rol,
            ]);
        } catch (PDOException $e) {
            error_log('SeguimientoContrato::registrar -> ' . $e->getMessage());
            return false;
        }
    }

    public static function listarPorContrato(int $contratoId): array
    {
        try {
            $st = self::pdo()->prepare("
                SELECT
                    sg.id,
                    sg.estado_anterior,
                    sg.estado_nuevo,
                    sg.descripcion,
                    sg.comentario,
                    sg.archivo_adjunto,
                    sg.rol,
                    sg.created_at,
                    CASE sg.rol
                        WHEN 'proveedor' THEN CONCAT(pr.nombres, ' ', pr.apellidos)
                        WHEN 'cliente'   THEN CONCAT(cl.nombres, ' ', cl.apellidos)
                        ELSE 'Sistema'
                    END AS responsable_nombre
                FROM seguimiento_contrato sg
                LEFT JOIN proveedores pr ON sg.rol = 'proveedor' AND pr.usuario_id = sg.usuario_id
                LEFT JOIN clientes   cl ON sg.rol = 'cliente'    AND cl.usuario_id = sg.usuario_id
                WHERE sg.servicio_contratado_id = :cid
                ORDER BY sg.created_at ASC
            ");
            $st->execute([':cid' => $contratoId]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('SeguimientoContrato::listarPorContrato -> ' . $e->getMessage());
            return [];
        }
    }

    public static function contratoEsDeUsuario(int $contratoId, int $usuarioId, string $rol): bool
    {
        try {
            if ($rol === 'proveedor') {
                $sql = "SELECT COUNT(*) FROM servicios_contratados sc
                        INNER JOIN proveedores pr ON sc.proveedor_id = pr.id
                        WHERE sc.id = :cid AND pr.usuario_id = :uid";
            } else {
                $sql = "SELECT COUNT(*) FROM servicios_contratados sc
                        INNER JOIN clientes cl ON sc.cliente_id = cl.id
                        WHERE sc.id = :cid AND cl.usuario_id = :uid";
            }
            $st = self::pdo()->prepare($sql);
            $st->execute([':cid' => $contratoId, ':uid' => $usuarioId]);
            return (int)$st->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('SeguimientoContrato::contratoEsDeUsuario -> ' . $e->getMessage());
            return false;
        }
    }

    public static function subirArchivo(array $file): ?string
    {
        $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidos = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'txt'];

        if (!in_array($ext, $permitidos, true)) return null;
        if ($file['size'] > 5 * 1024 * 1024) return null;

        $nombre  = uniqid('seg_', true) . '.' . $ext;
        $destino = BASE_PATH . '/public/uploads/seguimiento/' . $nombre;

        if (!move_uploaded_file($file['tmp_name'], $destino)) return null;

        return 'public/uploads/seguimiento/' . $nombre;
    }
}
