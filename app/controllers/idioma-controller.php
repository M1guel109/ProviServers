<?php
if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) session_start();
}

if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];

    if (in_array($lang, ['es', 'en'])) {
        $_SESSION['lang'] = $lang;
    }
}

// Valida que el referer pertenezca al mismo origen para evitar open redirect
$referer  = $_SERVER['HTTP_REFERER'] ?? '';
$redirect = str_starts_with($referer, BASE_URL) ? $referer : BASE_URL . '/';
header('Location: ' . $redirect);
exit;
?>