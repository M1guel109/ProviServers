<?php

session_start();

// Validamos si hay una sesión activa
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . $redirect_path);
    exit();
}

// Validamos que el rol sea 'proveedor'
if ($_SESSION['user']['rol'] != 'admin') {
    header('Location: ' . BASE_URL . $redirect_path);
    exit();
}
