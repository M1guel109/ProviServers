<?php
require_once __DIR__ . '/mailer_helper.php';

function enviarCorreoProveedorActivado($email, $nombres)
{
    try {
        $mail = mailer_init();

        $mail->setFrom(
            'suppportproviservers@gmail.com',
            'Soporte ProviServers'
        );

        $mail->addAddress($email);

        $mail->Subject = 'Tu cuenta de proveedor ha sido activada';

        $mail->Body = '
            <h2>Hola ' . htmlspecialchars($nombres) . ',</h2>

            <p>Te informamos que tu cuenta como 
            <strong>Proveedor en ProviServers</strong> 
            ha sido <strong>activada exitosamente</strong>.</p>

            <p>Ya puedes iniciar sesión y comenzar a ofrecer tus servicios.</p>

            <br>

            <p>
                <strong>Equipo ProviServers</strong>
            </p>
        ';

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Error al enviar correo proveedor activado: " . $e->getMessage());
        return false;
    }
}
