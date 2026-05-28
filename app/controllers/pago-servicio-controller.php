<?php
// =====================================================
// pago-servicio-controller.php
// Flujo de pago de servicios contratados con MercadoPago
// =====================================================

require_once BASE_PATH . '/config/mercadopago.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/helpers/alert-helper.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$esWebhook = str_contains($uri, '/cliente/pago-servicio/webhook');

if (!$esWebhook && (!isset($_SESSION['user']['id']) || ($_SESSION['user']['rol'] ?? '') !== 'cliente')) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

ensurePagosServiciosTable();

if (str_contains($uri, '/cliente/pagar-servicio') && $method === 'POST') {
    iniciarPagoServicio();
} elseif ($esWebhook) {
    webhookPagoServicio();
} elseif (str_contains($uri, '/cliente/pago-servicio-exitoso')) {
    pagoServicioExitoso();
} elseif (str_contains($uri, '/cliente/pago-servicio-pendiente')) {
    require BASE_PATH . '/app/views/dashboard/cliente/pago-servicio-pendiente.php';
} elseif (str_contains($uri, '/cliente/pago-servicio-fallido')) {
    require BASE_PATH . '/app/views/dashboard/cliente/pago-servicio-fallido.php';
}

// =====================================================
// Crea la tabla pagos_servicios si no existe
// =====================================================
function ensurePagosServiciosTable(): void
{
    try {
        $db  = new Conexion();
        $pdo = $db->getConexion();
        $pdo->exec("CREATE TABLE IF NOT EXISTS pagos_servicios (
            id                     INT AUTO_INCREMENT PRIMARY KEY,
            servicio_contratado_id INT         NOT NULL,
            cliente_id             INT         NOT NULL,
            proveedor_id           INT         NOT NULL,
            monto                  DECIMAL(12,2) NOT NULL DEFAULT 0,
            mp_payment_id          BIGINT      NULL DEFAULT NULL,
            mp_status              VARCHAR(20) DEFAULT 'approved',
            metodo                 VARCHAR(50) DEFAULT 'mercadopago',
            created_at             DATETIME    DEFAULT NOW(),
            UNIQUE KEY uk_pagos_sc (servicio_contratado_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (PDOException $e) {
        error_log('pago-servicio::ensurePagosServiciosTable: ' . $e->getMessage());
    }
}

// =====================================================
// Crea la preferencia de pago en MercadoPago
// Devuelve JSON con la URL de checkout
// =====================================================
function iniciarPagoServicio(): void
{
    header('Content-Type: application/json');

    $uid        = (int)$_SESSION['user']['id'];
    $contratoId = (int)($_POST['contrato_id'] ?? 0);

    if ($contratoId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Contrato no especificado.']);
        exit;
    }

    try {
        $db  = new Conexion();
        $pdo = $db->getConexion();

        $st = $pdo->prepare("
            SELECT sc.id, sc.estado, sc.proveedor_id,
                   COALESCE(cot.precio, sol.presupuesto_estimado, nec.presupuesto_estimado, 0) AS monto,
                   COALESCE(cot.titulo, sol.titulo, sv.nombre, 'Servicio') AS titulo
            FROM servicios_contratados sc
            INNER JOIN clientes cl   ON sc.cliente_id = cl.id
            LEFT JOIN cotizaciones cot ON sc.cotizacion_id = cot.id
            LEFT JOIN solicitudes sol  ON sc.solicitud_id  = sol.id
            LEFT JOIN necesidades nec  ON cot.necesidad_id = nec.id
            LEFT JOIN servicios sv     ON sc.servicio_id   = sv.id
            WHERE sc.id = :id AND cl.usuario_id = :uid
            LIMIT 1
        ");
        $st->execute([':id' => $contratoId, ':uid' => $uid]);
        $contrato = $st->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('iniciarPagoServicio DB: ' . $e->getMessage());
        echo json_encode(['ok' => false, 'error' => 'Error al obtener el servicio.']);
        exit;
    }

    if (!$contrato) {
        echo json_encode(['ok' => false, 'error' => 'Servicio no encontrado.']);
        exit;
    }

    if (!in_array($contrato['estado'], ['confirmado', 'en_proceso'], true)) {
        echo json_encode(['ok' => false, 'error' => 'Este servicio no está disponible para pago.']);
        exit;
    }

    $monto = (float)$contrato['monto'];
    if ($monto <= 0) {
        echo json_encode(['ok' => false, 'error' => 'El monto del servicio no está definido. Contacta al proveedor.']);
        exit;
    }

    // Verificar si ya fue pagado
    try {
        $stPaid = $pdo->prepare("SELECT id FROM pagos_servicios WHERE servicio_contratado_id = :id LIMIT 1");
        $stPaid->execute([':id' => $contratoId]);
        if ($stPaid->fetchColumn()) {
            echo json_encode(['ok' => false, 'error' => 'Este servicio ya fue pagado.']);
            exit;
        }
    } catch (PDOException $e) { /* tabla recién creada, continúa */ }

    $externalRef = 'sc-' . $contratoId . '-uid-' . $uid;

    $preference = [
        'items' => [[
            'id'          => 'sc-' . $contratoId,
            'title'       => 'ProviServers — ' . $contrato['titulo'],
            'description' => 'Pago de servicio contratado',
            'quantity'    => 1,
            'unit_price'  => $monto,
            'currency_id' => 'COP',
        ]],
        'payer' => [
            'email' => $_SESSION['user']['email'] ?? 'test@test.com',
        ],
        'back_urls' => [
            'success' => BASE_URL . '/cliente/pago-servicio-exitoso',
            'pending' => BASE_URL . '/cliente/pago-servicio-pendiente',
            'failure' => BASE_URL . '/cliente/pago-servicio-fallido',
        ],
        'external_reference' => $externalRef,
        'notification_url'   => BASE_URL . '/cliente/pago-servicio/webhook',
        'statement_descriptor' => 'PROVISERVERS',
    ];

    $ch = curl_init('https://api.mercadopago.com/checkout/preferences');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($preference),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . MP_ACCESS_TOKEN,
        ],
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError || $httpCode !== 201) {
        error_log("pago-servicio MP error [HTTP $httpCode]: " . ($curlError ?: substr($response, 0, 300)));
        echo json_encode(['ok' => false, 'error' => 'Error al conectar con MercadoPago. Intenta de nuevo.']);
        exit;
    }

    $data = json_decode($response, true);
    $url  = MP_ES_SANDBOX
        ? ($data['sandbox_init_point'] ?? $data['init_point'] ?? '')
        : ($data['init_point'] ?? '');

    if (!$url) {
        echo json_encode(['ok' => false, 'error' => 'No se pudo obtener la URL de pago.']);
        exit;
    }

    echo json_encode(['ok' => true, 'url' => $url]);
    exit;
}

// =====================================================
// Webhook IPN de MercadoPago
// =====================================================
function webhookPagoServicio(): void
{
    $body = file_get_contents('php://input');
    $data = json_decode($body, true) ?? [];

    $tipo      = $data['type']       ?? ($_GET['topic'] ?? '');
    $paymentId = $data['data']['id'] ?? ($_GET['id']    ?? null);

    if ($tipo !== 'payment' || !$paymentId) {
        http_response_code(200);
        exit;
    }

    $pago = consultarPagoServicioMP((int)$paymentId);
    if ($pago && $pago['status'] === 'approved') {
        registrarPagoServicio($pago);
    }

    http_response_code(200);
    exit;
}

// =====================================================
// Vista de pago exitoso + fallback de activación
// =====================================================
function pagoServicioExitoso(): void
{
    $paymentId = (int)($_GET['payment_id'] ?? 0);

    if ($paymentId > 0) {
        $pago = consultarPagoServicioMP($paymentId);
        if ($pago && $pago['status'] === 'approved') {
            registrarPagoServicio($pago);
        }
    }

    require BASE_PATH . '/app/views/dashboard/cliente/pago-servicio-exitoso.php';
}

// =====================================================
// Consulta el estado de un pago en MercadoPago
// =====================================================
function consultarPagoServicioMP(int $paymentId): ?array
{
    $ch = curl_init("https://api.mercadopago.com/v1/payments/{$paymentId}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . MP_ACCESS_TOKEN],
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    return is_array($data) ? $data : null;
}

// =====================================================
// Registra el pago en pagos_servicios (idempotente)
// =====================================================
function registrarPagoServicio(array $pago): void
{
    $ref = $pago['external_reference'] ?? '';

    if (!preg_match('/^sc-(\d+)-uid-(\d+)$/', $ref, $m)) {
        error_log('registrarPagoServicio: external_reference inválido: ' . $ref);
        return;
    }

    $contratoId  = (int)$m[1];
    $usuarioId   = (int)$m[2];
    $mpPaymentId = (int)$pago['id'];
    $monto       = (float)($pago['transaction_amount'] ?? 0);

    try {
        $db  = new Conexion();
        $pdo = $db->getConexion();

        $stSC = $pdo->prepare("
            SELECT sc.cliente_id, sc.proveedor_id
            FROM servicios_contratados sc
            INNER JOIN clientes cl ON sc.cliente_id = cl.id
            WHERE sc.id = :id AND cl.usuario_id = :uid
            LIMIT 1
        ");
        $stSC->execute([':id' => $contratoId, ':uid' => $usuarioId]);
        $sc = $stSC->fetch(PDO::FETCH_ASSOC);

        if (!$sc) return;

        $pdo->prepare("
            INSERT IGNORE INTO pagos_servicios
                (servicio_contratado_id, cliente_id, proveedor_id, monto, mp_payment_id, mp_status, metodo, created_at)
            VALUES (:sc_id, :cl_id, :prov_id, :monto, :mp_id, 'approved', 'mercadopago', NOW())
        ")->execute([
            ':sc_id'   => $contratoId,
            ':cl_id'   => $sc['cliente_id'],
            ':prov_id' => $sc['proveedor_id'],
            ':monto'   => $monto,
            ':mp_id'   => $mpPaymentId,
        ]);

    } catch (PDOException $e) {
        error_log('registrarPagoServicio: ' . $e->getMessage());
    }
}
