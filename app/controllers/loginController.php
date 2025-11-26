<?php
// Importamos las dependencias
require_once __DIR__ . '/../helpers/alert_helper.php';
require_once __DIR__ . '/../models/login.php';

// $clave = '123';
// echo password_hash($clave, PASSWORD_DEFAULT);

// Ejecutar segun la solicitud al servidor POST

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Capturamos en variables los valores enviados a traves de los name  del formulario y el method POST
    $correo = $_POST['email'] ?? '';
    $clave = $_POST['clave'] ?? '';
    // Validamos que los campos/variables no esten vacios

    if (empty($correo) || empty($clave)) {
        mostrarSweetAlert('error', 'Campos vacíos', 'Por favor completa todos los campos');
        exit();
    }
    // POO - Instaciamos las clases del modelo, para acceder a un method (funcion) en especifico

    $login = new Login();
    $resultado = $login->autenticar($correo, $clave);

    //verificar si el modelo devolvio el error 
    if (isset($resultado['error'])) {
        mostrarSweetAlert('error', 'Error de autenticacion', $resultado['error']);
        exit();
    }

    //SI PASA ESTA LINEA, EL USUARIO ES VALIDO

    session_start();
    $_SESSION['user'] = [
        'id' => $resultado['id'],
        'rol' => $resultado['rol'],
        'email' => $resultado['email']
    ];

    // Redireccion segun rol 
    $redirectUrl = '/ProviServers/login';
    $mensaje = 'Rol inexistente. Redirigiendo al inicio de sesión...';

    switch ($resultado['rol']) {
        case 'admin':
            $redirectUrl = '/ProviServers/admin/dashboard';
            $mensaje = 'Bienvenido Administrador';
            break;
        case 'proveedor':
            $redirectUrl = '/ProviServers/proveedor/dashboard';
            $mensaje = 'Bienvenido Proveedor';
            break;
        case 'cliente':
            $redirectUrl = '/ProviServers/cliente/dashboard';
            $mensaje = 'Bienvenido Cliente';
            break;
    }

    mostrarSweetAlert('success', 'Ingreso Exitoso', $mensaje , $redirectUrl);
    exit();
} else {
    http_response_code(405);
    echo "Metodo no permitido";
    exit();
}
