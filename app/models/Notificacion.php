<?php
// ===================================================================
// Notificacion.php — Modelo de notificaciones internas del sistema
// Tabla: notificaciones
// ===================================================================

require_once __DIR__ . '/../../config/database.php';

class Notificacion
{
    // Tipos de notificación reconocidos
    const TIPO_SOLICITUD   = 'nueva_solicitud';
    const TIPO_PAGO        = 'pago_recibido';
    const TIPO_ESTADO      = 'cambio_estado';
    const TIPO_CALIFICACION = 'calificacion';
    const TIPO_COTIZACION  = 'cotizacion_aceptada';
    const TIPO_LIBERACION  = 'pago_liberado';
    const TIPO_SISTEMA     = 'sistema';

    // Iconos por tipo
    private static array $iconos = [
        self::TIPO_SOLICITUD    => 'bi-send-fill',
        self::TIPO_PAGO         => 'bi-credit-card-fill',
        self::TIPO_ESTADO       => 'bi-arrow-repeat',
        self::TIPO_CALIFICACION => 'bi-star-fill',
        self::TIPO_COTIZACION   => 'bi-file-earmark-check-fill',
        self::TIPO_LIBERACION   => 'bi-cash-coin',
        self::TIPO_SISTEMA      => 'bi-bell-fill',
    ];

    private static array $colores = [
        self::TIPO_SOLICITUD    => 'text-primary',
        self::TIPO_PAGO         => 'text-success',
        self::TIPO_ESTADO       => 'text-warning',
        self::TIPO_CALIFICACION => 'text-warning',
        self::TIPO_COTIZACION   => 'text-info',
        self::TIPO_LIBERACION   => 'text-success',
        self::TIPO_SISTEMA      => 'text-secondary',
    ];

    private static function pdo(): PDO
    {
        static $pdo = null;
        if (!$pdo) {
            $db  = new Conexion();
            $pdo = $db->getConexion();
            $pdo->exec("CREATE TABLE IF NOT EXISTS notificaciones (
                id         INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT          NOT NULL,
                tipo       VARCHAR(50)  NOT NULL DEFAULT 'sistema',
                titulo     VARCHAR(150) NOT NULL,
                mensaje    TEXT         NOT NULL,
                url        VARCHAR(255) NULL,
                icono      VARCHAR(60)  NULL,
                color      VARCHAR(30)  NULL,
                leida      TINYINT(1)   NOT NULL DEFAULT 0,
                created_at DATETIME     DEFAULT NOW(),
                INDEX idx_uid_leida (usuario_id, leida),
                INDEX idx_uid_fecha (usuario_id, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        return $pdo;
    }

    // ---------------------------------------------------------------
    // Crear una notificación para un usuario
    // ---------------------------------------------------------------
    public static function crear(
        int     $usuarioId,
        string  $tipo,
        string  $titulo,
        string  $mensaje,
        ?string $url   = null,
        ?string $icono = null,
        ?string $color = null
    ): bool {
        try {
            $pdo = self::pdo();
            $pdo->prepare("
                INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, url, icono, color, leida, created_at)
                VALUES (:uid, :tipo, :titulo, :mensaje, :url, :icono, :color, 0, NOW())
            ")->execute([
                ':uid'     => $usuarioId,
                ':tipo'    => $tipo,
                ':titulo'  => mb_substr($titulo, 0, 150),
                ':mensaje' => $mensaje,
                ':url'     => $url,
                ':icono'   => $icono ?? (self::$iconos[$tipo] ?? 'bi-bell'),
                ':color'   => $color ?? (self::$colores[$tipo] ?? 'text-secondary'),
            ]);
            return true;
        } catch (PDOException $e) {
            error_log('Notificacion::crear: ' . $e->getMessage());
            return false;
        }
    }

    // ---------------------------------------------------------------
    // Listar notificaciones de un usuario (más recientes primero)
    // ---------------------------------------------------------------
    public static function listar(int $usuarioId, ?bool $soloNoLeidas = null, int $limit = 50): array
    {
        try {
            $where = 'WHERE usuario_id = :uid';
            if ($soloNoLeidas === true)  $where .= ' AND leida = 0';
            if ($soloNoLeidas === false) $where .= ' AND leida = 1';

            $st = self::pdo()->prepare("
                SELECT * FROM notificaciones $where
                ORDER BY created_at DESC
                LIMIT :lim
            ");
            $st->bindValue(':uid', $usuarioId, PDO::PARAM_INT);
            $st->bindValue(':lim', $limit,     PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Notificacion::listar: ' . $e->getMessage());
            return [];
        }
    }

    // ---------------------------------------------------------------
    // Contar no leídas de un usuario
    // ---------------------------------------------------------------
    public static function contarNoLeidas(int $usuarioId): int
    {
        try {
            $st = self::pdo()->prepare("SELECT COUNT(*) FROM notificaciones WHERE usuario_id = :uid AND leida = 0");
            $st->execute([':uid' => $usuarioId]);
            return (int)$st->fetchColumn();
        } catch (PDOException $e) {
            error_log('Notificacion::contarNoLeidas: ' . $e->getMessage());
            return 0;
        }
    }

    // ---------------------------------------------------------------
    // Marcar una notificación como leída (valida que pertenezca al usuario)
    // ---------------------------------------------------------------
    public static function marcarLeida(int $id, int $usuarioId): bool
    {
        try {
            $st = self::pdo()->prepare("UPDATE notificaciones SET leida = 1 WHERE id = :id AND usuario_id = :uid");
            $st->execute([':id' => $id, ':uid' => $usuarioId]);
            return $st->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Notificacion::marcarLeida: ' . $e->getMessage());
            return false;
        }
    }

    // ---------------------------------------------------------------
    // Marcar todas las notificaciones del usuario como leídas
    // ---------------------------------------------------------------
    public static function marcarTodasLeidas(int $usuarioId): bool
    {
        try {
            self::pdo()->prepare("UPDATE notificaciones SET leida = 1 WHERE usuario_id = :uid AND leida = 0")
                ->execute([':uid' => $usuarioId]);
            return true;
        } catch (PDOException $e) {
            error_log('Notificacion::marcarTodasLeidas: ' . $e->getMessage());
            return false;
        }
    }

    // ---------------------------------------------------------------
    // Icono y color por tipo (para las vistas)
    // ---------------------------------------------------------------
    public static function icono(string $tipo): string
    {
        return self::$iconos[$tipo] ?? 'bi-bell';
    }

    public static function color(string $tipo): string
    {
        return self::$colores[$tipo] ?? 'text-secondary';
    }
}
