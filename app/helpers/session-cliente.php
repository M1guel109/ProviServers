<?php

if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login');
    exit();
}

if ($_SESSION['user']['rol'] !== 'cliente') {
    $destinos = ['admin' => '/admin/dashboard', 'proveedor' => '/proveedor/dashboard'];
    header('Location: ' . BASE_URL . ($destinos[$_SESSION['user']['rol']] ?? '/login'));
    exit();
}
