<?php

require_once __DIR__ . '/../helpers/mailer_helper.php';

class ContactController
{
    public function enviar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL);
            exit;
        }

        $nombre  = trim($_POST['nombre'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $mensaje = trim($_POST['mensaje'] ?? '');

        if ($nombre === '' || $email === '' || $mensaje === '') {
            die('Todos los campos son obligatorios');
        }

        try {
            $mail = mailer_init();

            $mail->setFrom($email, $nombre);
            $mail->addAddress('Hello@proviservers.com', 'Proviservers');
            $mail->Subject = 'Nuevo mensaje de contacto';
            $mail->Body = "
                <h3>Nuevo mensaje desde la landing</h3>
                <p><strong>Nombre:</strong> {$nombre}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Mensaje:</strong><br>{$mensaje}</p>
            ";

            $mail->send();

            header('Location: ' . BASE_URL . '/?contacto=ok');
            exit;

        } catch (Exception $e) {
            error_log($e->getMessage());
            die('Error al enviar el mensaje');
        }
    }
}
