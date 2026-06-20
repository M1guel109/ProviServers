<?php
require_once __DIR__ . '/../../config/database.php';

class DashboardConfig
{
    private const DEFAULTS = [
        'cliente' => [
            ['widget_id' => 'estadisticas',     'posicion' => 0, 'visible' => true],
            ['widget_id' => 'acciones-rapidas', 'posicion' => 1, 'visible' => true],
            ['widget_id' => 'servicios-curso',  'posicion' => 2, 'visible' => true],
            ['widget_id' => 'categorias',       'posicion' => 3, 'visible' => true],
        ],
        'proveedor' => [
            ['widget_id' => 'estadisticas',        'posicion' => 0, 'visible' => true],
            ['widget_id' => 'grafica',             'posicion' => 1, 'visible' => true],
            ['widget_id' => 'servicios-recientes', 'posicion' => 2, 'visible' => true],
            ['widget_id' => 'resenas-recientes',   'posicion' => 3, 'visible' => true],
            ['widget_id' => 'proximas-citas',      'posicion' => 4, 'visible' => true],
        ],
        'admin' => [
            ['widget_id' => 'grafica',            'posicion' => 0, 'visible' => true],
            ['widget_id' => 'usuarios',           'posicion' => 1, 'visible' => true],
            ['widget_id' => 'servicio-destacado', 'posicion' => 2, 'visible' => true],
            ['widget_id' => 'metricas',           'posicion' => 3, 'visible' => true],
        ],
    ];

    private PDO $db;

    public function __construct()
    {
        $this->db = (new Conexion())->getConexion();
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS dashboard_preferencias (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id  INT         NOT NULL,
            dashboard   VARCHAR(20) NOT NULL,
            config_json TEXT        NOT NULL,
            updated_at  DATETIME    DEFAULT NOW() ON UPDATE NOW(),
            UNIQUE KEY uk_dash (usuario_id, dashboard)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function obtener(int $uid, string $dashboard): array
    {
        $stmt = $this->db->prepare(
            "SELECT config_json FROM dashboard_preferencias WHERE usuario_id = ? AND dashboard = ? LIMIT 1"
        );
        $stmt->execute([$uid, $dashboard]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $config = json_decode($row['config_json'], true);
            if (is_array($config) && count($config) > 0) return $config;
        }

        return self::DEFAULTS[$dashboard] ?? [];
    }

    public function guardar(int $uid, string $dashboard, array $config): bool
    {
        if (!isset(self::DEFAULTS[$dashboard])) return false;

        $knownIds = array_column(self::DEFAULTS[$dashboard], 'widget_id');
        foreach ($config as $item) {
            if (!isset($item['widget_id']) || !in_array($item['widget_id'], $knownIds, true)) {
                return false;
            }
        }

        $json = json_encode($config);
        $stmt = $this->db->prepare(
            "INSERT INTO dashboard_preferencias (usuario_id, dashboard, config_json)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE config_json = ?, updated_at = NOW()"
        );
        $stmt->execute([$uid, $dashboard, $json, $json]);
        return true;
    }

    public function restaurar(int $uid, string $dashboard): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM dashboard_preferencias WHERE usuario_id = ? AND dashboard = ?"
        );
        $stmt->execute([$uid, $dashboard]);
        return true;
    }

    public static function getDefaults(string $dashboard): array
    {
        return self::DEFAULTS[$dashboard] ?? [];
    }
}
