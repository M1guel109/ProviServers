<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login');
    exit();
}

if ($_SESSION['user']['rol'] !== 'admin') {
    $destinos = ['proveedor' => '/proveedor/dashboard', 'cliente' => '/cliente/dashboard'];
    header('Location: ' . BASE_URL . ($destinos[$_SESSION['user']['rol']] ?? '/login'));
    exit();
}
