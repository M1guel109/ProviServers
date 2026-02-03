<?php
// Importamos el modelo y el helper de alertas
require_once BASE_PATH . '/app/models/contacto.php'; 
require_once __DIR__ . '/../helpers/alert_helper.php';

function procesarContacto() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // 1. Limpiar datos (Seguridad básica)
        $nombre  = trim($_POST['nombre'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $mensaje = trim($_POST['mensaje'] ?? '');

        // 2. Validar que no estén vacíos
        if (empty($nombre) || empty($email) || empty($mensaje)) {
            // Usamos tu helper. El 4to parámetro es la URL de redirección.
            mostrarSweetAlert(
                'warning', 
                'Campos vacíos', 
                'Por favor completa todos los campos del formulario.', 
                BASE_URL . '/#contact'
            );
            exit;
        }

        // 3. Guardar en Base de Datos
        $modelo = new Contacto();
        $exito = $modelo->registrarMensaje([
            'nombre'  => $nombre,
            'email'   => $email,
            'mensaje' => $mensaje
        ]);

        if ($exito) {
            // MENSAJE DE ÉXITO
            mostrarSweetAlert(
                'success', 
                '¡Mensaje Enviado!', 
                'Gracias por escribirnos. Hemos recibido tus datos correctamente.', 
                BASE_URL . '/#contact'
            );
        } else {
            // MENSAJE DE ERROR
            mostrarSweetAlert(
                'error', 
                'Error del Servidor', 
                'No pudimos guardar tu mensaje en este momento. Intenta más tarde.', 
                BASE_URL . '/#contact'
            );
        }
        exit;
    }
}
?>