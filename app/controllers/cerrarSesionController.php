<?php

class CerrarSesionController
{
    public function index()
    {
        // Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Vaciar variables de sesión
        $_SESSION = [];

        // Destruir sesión
        session_unset();
        session_destroy();

        // Eliminar cookie de sesión (opcional pero recomendado)
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Redirigir al login
        header("Location: " . BASE_URL . "/login");
        exit;
    }
}
