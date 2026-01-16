<?php
// app/controllers/clientePublicacionDetalleController.php

require_once __DIR__ . '/../models/Publicacion.php';
// Si tienes helper de sesión cliente, lo usas aquí:
$sessionClientePath = __DIR__ . '/../helpers/session_cliente.php';
if (file_exists($sessionClientePath)) {
    require_once $sessionClientePath;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(404);
    echo "Publicación no válida.";
    exit();
}

$pubModel     = new Publicacion();
$publicacion  = $pubModel->obtenerPublicaActivaPorId($id);

if (!$publicacion) {
    http_response_code(404);
    echo "La publicación no existe o no está disponible.";
    exit();
}

// Cargamos la vista
require BASE_PATH . '/app/views/dashboard/cliente/detallePublicacion.php';
