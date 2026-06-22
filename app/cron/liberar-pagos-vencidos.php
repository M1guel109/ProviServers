<?php
/**
 * Cron: auto-release escrow payments exceeding ESCROW_DIAS_TIMEOUT days.
 *
 * Linux production:
 *   0 2 * * * php /var/www/html/ProviServers/app/cron/liberar-pagos-vencidos.php >> /var/log/proviservers-cron.log 2>&1
 *
 * Windows (XAMPP) — Task Scheduler:
 *   Program : C:\xampp\php\php.exe
 *   Arguments: C:\xampp\htdocs\ProviServers\app\cron\liberar-pagos-vencidos.php
 *   Trigger  : Daily at 02:00
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Access denied.' . PHP_EOL);
}

define('BASE_PATH', dirname(__DIR__, 2));

// Supply HTTP_HOST so config.php BASE_URL resolves correctly in CLI context.
// Change to match your production domain.
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'proviservers.com';

require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/Notificacion.php';
require_once BASE_PATH . '/app/models/SeguimientoContrato.php';

$timeout = ESCROW_DIAS_TIMEOUT;
$db      = new Conexion();
$pdo     = $db->getConexion();

// Ensure liquidaciones table exists (mirrors admin liberarPago guard)
$pdo->exec("CREATE TABLE IF NOT EXISTS liquidaciones (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    pagos_servicios_id INT           NOT NULL,
    proveedor_id       INT           NOT NULL,
    monto              DECIMAL(12,2) NOT NULL,
    created_at         DATETIME      DEFAULT NOW()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Fetch all approved, unreleased payments whose service has been stuck
// in 'en_proceso' for at least ESCROW_DIAS_TIMEOUT days.
$stmt = $pdo->prepare("
    SELECT
        ps.id                     AS pago_id,
        ps.monto,
        ps.proveedor_id,
        ps.servicio_contratado_id,
        pr.usuario_id             AS prov_usuario_id,
        cl.usuario_id             AS cli_usuario_id
    FROM  pagos_servicios ps
    INNER JOIN servicios_contratados sc ON sc.id = ps.servicio_contratado_id
    INNER JOIN proveedores           pr ON pr.id = ps.proveedor_id
    INNER JOIN clientes              cl ON cl.id = sc.cliente_id
    WHERE ps.mp_status  = 'approved'
      AND ps.liberado   = 0
      AND sc.estado     = 'en_proceso'
      AND DATEDIFF(NOW(), ps.created_at) >= :timeout
");
$stmt->execute([':timeout' => $timeout]);
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$liberados = 0;
$errores   = 0;

foreach ($pagos as $pago) {
    $pagoId = (int)$pago['pago_id'];
    $scId   = (int)$pago['servicio_contratado_id'];
    $monto  = (float)$pago['monto'];

    try {
        $pdo->beginTransaction();

        // 1. Release payment
        $pdo->prepare("
            UPDATE pagos_servicios
            SET liberado = 1, fecha_liberacion = NOW()
            WHERE id = :id
        ")->execute([':id' => $pagoId]);

        // 2. Close service
        $pdo->prepare("
            UPDATE servicios_contratados
            SET estado = 'finalizado', updated_at = NOW()
            WHERE id = :id
        ")->execute([':id' => $scId]);

        // 3. Register liquidación
        $pdo->prepare("
            INSERT INTO liquidaciones (pagos_servicios_id, proveedor_id, monto, created_at)
            VALUES (:pago_id, :prov_id, :monto, NOW())
        ")->execute([
            ':pago_id' => $pagoId,
            ':prov_id' => $pago['proveedor_id'],
            ':monto'   => $monto,
        ]);

        $pdo->commit();

        // 4. Notifications (non-critical — outside transaction)
        $montoFmt = '$' . number_format($monto, 0, ',', '.');

        Notificacion::crear(
            (int)$pago['prov_usuario_id'],
            Notificacion::TIPO_LIBERACION,
            'Pago liberado automáticamente',
            "El pago de {$montoFmt} fue liberado automáticamente tras {$timeout} días sin marcar el servicio como finalizado.",
            BASE_URL . '/proveedor/historial-pagos'
        );

        Notificacion::crear(
            (int)$pago['cli_usuario_id'],
            Notificacion::TIPO_SISTEMA,
            'Servicio cerrado automáticamente',
            "Tu servicio fue marcado como finalizado automáticamente tras {$timeout} días sin actividad del proveedor.",
            BASE_URL . '/cliente/servicios-contratados'
        );

        // 5. Audit trail
        SeguimientoContrato::registrar(
            contratoId:  $scId,
            usuarioId:   0,
            rol:         'sistema',
            estadoNuevo: 'finalizado',
            descripcion: "Liberación automática por escrow timeout ({$timeout} días)."
        );

        error_log("[cron:liberar-pagos] OK  — pago_id={$pagoId} sc_id={$scId} monto={$montoFmt}");
        $liberados++;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("[cron:liberar-pagos] ERR — pago_id={$pagoId} sc_id={$scId}: " . $e->getMessage());
        $errores++;
    }
}

$timestamp = date('Y-m-d H:i:s');
error_log("[cron:liberar-pagos] {$timestamp} — liberados={$liberados} errores={$errores}");
echo "[{$timestamp}] Liberados: {$liberados} | Errores: {$errores}" . PHP_EOL;
