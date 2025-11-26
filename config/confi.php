<?php
// CONFIGURACIÓN GLOBAL DEL PROYECTO

// DETECTAR PROTOCOLO (HTTP O HTTPS)
$protocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";

// NOMBRE DE LA CARPETA DEL PROYECTO EN LOCAL
$baseFolder = '/ProviServers';

// HOST ACTUAL
$host = $_SERVER['HTTP_HOST'];

// URL BASE DINÁMICA (FUNCIONA EN LOCAL Y HOSTING)
define('BASE_URL', $protocol . $host . $baseFolder);

// RUTA BASE DEL PROYECTO (PARA REQUIRE O INCLUDE)
define('BASE_PATH', dirname(__DIR__));
?>