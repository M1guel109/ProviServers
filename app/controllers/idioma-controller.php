<?php
session_start();

if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    
    // Validación de seguridad (solo permitimos 'es' o 'en')
    if (in_array($lang, ['es', 'en'])) {
        $_SESSION['lang'] = $lang;
    }
}

// Redirigir a la página anterior (donde estaba el usuario)
$redirect = $_SERVER['HTTP_REFERER'] ?? '/ProviServers/admin/dashboard';
header("Location: $redirect");
exit;
?>