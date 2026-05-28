<?php
// =====================================================
// membresia-controller.php
// Gestiona el flujo de pago de membresías con MercadoPago
// =====================================================

require_once BASE_PATH . '/config/mercadopago.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/helpers/alert-helper.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Webhook no requiere sesión (viene de MercadoPago)
$esWebhook = str_contains($uri, '/proveedor/membresia/webhook');

if (!$esWebhook && (!isset($_SESSION['user']['id']) || ($_SESSION['user']['rol'] ?? '') !== 'proveedor')) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

if (str_contains($uri, '/proveedor/membresia/pagar') && $method === 'POST') {
    crearPreferenciaMP();
} elseif ($esWebhook) {
    procesarWebhookMP();
} elseif (str_contains($uri, '/proveedor/membresia/pago-exitoso')) {
    mostrarPagoExitoso();
} elseif (str_contains($uri, '/proveedor/membresia/pago-pendiente')) {
    require BASE_PATH . '/app/views/dashboard/proveedor/membresia-pago-pendiente.php';
} elseif (str_contains($uri, '/proveedor/membresia/pago-fallido')) {
    require BASE_PATH . '/app/views/dashboard/proveedor/membresia-pago-fallido.php';
}

// =====================================================
// Crea la preferencia de pago en MercadoPago
// Devuelve JSON con la URL de checkout
// =====================================================
function crearPreferenciaMP(): void
{
    header('Content-Type: application/json');

    $uid    = (int)$_SESSION['user']['id'];
    $planId = (int)($_POST['plan_id'] ?? 0);

    if ($planId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Plan no especificado.']);
        exit;
    }

    // Buscar el plan en la BD por ID
    try {
        $db  = new Conexion();
        $pdo = $db->getConexion();
        $st  = $pdo->prepare("SELECT id, tipo, costo, duracion_dias FROM membresias WHERE id = :id AND UPPER(estado) = 'ACTIVO' LIMIT 1");
        $st->execute([':id' => $planId]);
        $plan = $st->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('membresia-controller::crearPreferenciaMP DB: ' . $e->getMessage());
        echo json_encode(['ok' => false, 'error' => 'Error al obtener el plan.']);
        exit;
    }

    if (!$plan) {
        echo json_encode(['ok' => false, 'error' => 'Plan no encontrado.']);
        exit;
    }

    $externalRef = 'uid-' . $uid . '-plan-' . $plan['id'];

    $preference = [
        'items' => [[
            'id'          => 'plan-' . $plan['id'],
            'title'       => 'ProviServers — Plan ' . $plan['tipo'],
            'description' => 'Membresía por ' . $plan['duracion_dias'] . ' días',
            'quantity'    => 1,
            'unit_price'  => (float)$plan['costo'],
            'currency_id' => 'COP',
        ]],
        'payer' => [
            'email' => $_SESSION['user']['email'] ?? 'test@test.com',
        ],
        'back_urls' => [
            'success' => BASE_URL . '/proveedor/membresia/pago-exitoso',
            'pending' => BASE_URL . '/proveedor/membresia/pago-pendiente',
            'failure' => BASE_URL . '/proveedor/membresia/pago-fallido',
        ],
        'external_reference' => $externalRef,
        'notification_url'   => BASE_URL . '/proveedor/membresia/webhook',
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
        error_log("MercadoPago preference error [HTTP $httpCode]: " . ($curlError ?: substr($response, 0, 300)));
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
// Procesa notificación IPN/Webhook de MercadoPago
// =====================================================
function procesarWebhookMP(): void
{
    $body = file_get_contents('php://input');
    $data = json_decode($body, true) ?? [];

    $tipo      = $data['type']         ?? ($_GET['topic'] ?? '');
    $paymentId = $data['data']['id']   ?? ($_GET['id']    ?? null);

    if ($tipo !== 'payment' || !$paymentId) {
        http_response_code(200);
        exit;
    }

    $pago = consultarPagoMP((int)$paymentId);

    if (!$pago || $pago['status'] !== 'approved') {
        http_response_code(200);
        exit;
    }

    activarPlanDesdePago($pago);

    http_response_code(200);
    exit;
}

// =====================================================
// Página de pago exitoso — también activa el plan
// como fallback si el webhook no llegó primero
// =====================================================
function mostrarPagoExitoso(): void
{
    $paymentId = (int)($_GET['payment_id'] ?? 0);

    if ($paymentId > 0) {
        $pago = consultarPagoMP($paymentId);
        if ($pago && $pago['status'] === 'approved') {
            activarPlanDesdePago($pago);
        }
    }

    require BASE_PATH . '/app/views/dashboard/proveedor/membresia-pago-exitoso.php';
}

// =====================================================
// Consulta el estado de un pago en MercadoPago
// =====================================================
function consultarPagoMP(int $paymentId): ?array
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
// Activa el plan del proveedor en la BD
// =====================================================
function activarPlanDesdePago(array $pago): void
{
    $ref = $pago['external_reference'] ?? '';

    // Formato: uid-{usuario_id}-plan-{membresia_id}
    if (!preg_match('/^uid-(\d+)-plan-(\d+)$/', $ref, $m)) {
        error_log('activarPlanDesdePago: external_reference inválido: ' . $ref);
        return;
    }

    $usuarioId  = (int)$m[1];
    $membresiaId = (int)$m[2];

    try {
        $db  = new Conexion();
        $pdo = $db->getConexion();

        // Obtener proveedor_id y duracion del plan
        $st = $pdo->prepare("
            SELECT p.id AS proveedor_id, m.duracion_dias
            FROM proveedores p
            INNER JOIN membresias m ON m.id = :mid
            WHERE p.usuario_id = :uid
            LIMIT 1
        ");
        $st->execute([':uid' => $usuarioId, ':mid' => $membresiaId]);
        $datos = $st->fetch(PDO::FETCH_ASSOC);

        if (!$datos) return;

        $proveedorId  = (int)$datos['proveedor_id'];
        $duracionDias = (int)($datos['duracion_dias'] ?? 30);

        // Desactivar planes anteriores
        $pdo->prepare("
            UPDATE proveedor_membresia SET estado = 'inactiva'
            WHERE proveedor_id = ? AND estado = 'activa'
        ")->execute([$proveedorId]);

        // Insertar el nuevo plan activo
        $pdo->prepare("
            INSERT INTO proveedor_membresia (proveedor_id, membresia_id, fecha_inicio, fecha_fin, estado, created_at)
            VALUES (:pid, :mid, CURDATE(), DATE_ADD(CURDATE(), INTERVAL :dias DAY), 'activa', NOW())
        ")->execute([
            ':pid'  => $proveedorId,
            ':mid'  => $membresiaId,
            ':dias' => $duracionDias,
        ]);
    } catch (PDOException $e) {
        error_log('activarPlanDesdePago: ' . $e->getMessage());
    }
}
