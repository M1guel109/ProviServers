<?php
require_once __DIR__ . '/../models/DashboardConfig.php';

if (session_status() === PHP_SESSION_NONE) session_start();

ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'No autorizado']);
    exit();
}

$uid  = (int)$_SESSION['user']['id'];
$rol  = $_SESSION['user']['rol'] ?? 'cliente';
$dash = in_array($rol, ['cliente', 'proveedor', 'admin'], true) ? $rol : 'cliente';

$model  = new DashboardConfig();
$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

if ($method === 'GET') {
    echo json_encode(['ok' => true, 'config' => $model->obtener($uid, $dash)]);
    exit();
}

if ($method === 'POST' && str_contains($uri, '/dashboard/config/restaurar')) {
    $model->restaurar($uid, $dash);
    echo json_encode(['ok' => true]);
    exit();
}

if ($method === 'POST' && str_contains($uri, '/dashboard/config/guardar')) {
    $body   = file_get_contents('php://input');
    $data   = json_decode($body, true);
    $config = $data['config'] ?? null;

    if (!is_array($config)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Config inválida']);
        exit();
    }

    echo json_encode(['ok' => $model->guardar($uid, $dash, $config)]);
    exit();
}

http_response_code(404);
echo json_encode(['ok' => false, 'message' => 'Ruta no encontrada']);
exit();
