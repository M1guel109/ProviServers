<?php
// Iniciar sesión si no está iniciada (para leer $_SESSION['lang'])
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración por defecto
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'es'; // Idioma por defecto
}

// Cargar el array de traducciones
function cargarDiccionario() {
    $lang = $_SESSION['lang'];
    $archivo = __DIR__ . "/../lang/$lang.php";
    
    if (file_exists($archivo)) {
        return include($archivo);
    }
    // Fallback a español si no existe el archivo
    return include(__DIR__ . "/../lang/es.php");
}

// Variable global para no cargar el archivo 100 veces
$GLOBALS['DICCIONARIO'] = cargarDiccionario();

/**
 * Función principal para traducir
 * Uso: echo __('header_buscar');
 */
function __($clave) {
    global $DICCIONARIO;
    return $DICCIONARIO[$clave] ?? $clave; // Si no existe, devuelve la clave tal cual
}

/**
 * Obtener el idioma actual ('es' o 'en')
 */
function obtenerIdiomaActual() {
    return $_SESSION['lang'];
}
?>