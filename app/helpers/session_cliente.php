<?php

session_start();

// Validamos si hay una sesión activa
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . $redirect_path);
    exit();
}

// Validamos que el rol sea 'cliente'
if ($_SESSION['user']['rol'] != 'cliente') {
    header('Location: ' . BASE_URL . $redirect_path);
    exit();
}
