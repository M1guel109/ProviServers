<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/mailer_helper.php';


class RecoveryPass
{
    private $conexion;

    public function __construct()
    {
        $db = new  Conexion();
        $this->conexion = $db->getConexion();
    }

    public function recuperarClave($email)
    {
        try {
            $consultar = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";

            $resultado = $this->conexion->prepare($consultar);
            $resultado->bindParam(':email', $email);
            $resultado->execute();

            $user = $resultado->fetch();

            if ($user) {

                // Generamos la nueva contraseña a partir de una base de caracteres y un random
                $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

                // Mezclamos la cadena de caracteres 
                $random = str_shuffle($caracteres);

                // Subtraemos uan cantidad definida de este random 
                $nuevaClave = substr($random, 0, 8);

                $claveHash = password_hash($nuevaClave, PASSWORD_DEFAULT);


                // Se actualiza la tabla en la clave usuarios antes de enviar el correo al usuario
                $actualizar = "UPDATE usuarios SET  clave = :nuevaClave  WHERE id = :id ";

                $resultado = $this->conexion->prepare($actualizar);
                $resultado->bindParam(':nuevaClave', $claveHash);
                $resultado->bindParam(':id', $user['id']);
                $resultado->execute();

                $mail = mailer_init();

                // Enviamos el emaill despues de actualizar la contraseña en la base de datos
                //Create an instance; passing `true` enables exceptions


                $mail->SMTPDebug = 0;                                       //Enable verbose debug output
                //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                //Recipients
                // Emisor y nombre de la persona
                $mail->setFrom('suppportproviservers@gmail.com', 'Soporte Proviservers');
                // Receptor,a quien quiero que le llege el correo 
                $mail->addAddress($user['email']);                       //Add a recipient
                // $mail->addAddress('ellen@example.com');               //Name is optional
                // $mail->addReplyTo('info@example.com', 'Information');
                // $mail->addCC('cc@example.com');
                // $mail->addBCC('bcc@example.com');

                //Attachments
                // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
                // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

                //Content
                $mail->Subject = "PROVISERVERS - NUEVA CLAVE GENERADA";
                $mail->Body = '
                        <!DOCTYPE html>
                            <html>
                            <head>
                                <meta charset="UTF-8">
                                <meta content="width=device-width, initial-scale=1" name="viewport">
                                <meta name="x-apple-disable-message-reformatting">
                                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                                <title>Recuperación de contraseña - Proviservers</title>
                                <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet">
                            </head>

                            <body style="margin:0;padding:0;background:#d4d4d4;font-family: Open Sans, Arial, sans-serif">

                            <table cellspacing="0" cellpadding="0" width="100%" class="es-wrapper">
                            <tr>
                            <td valign="top">

                                <!-- HEADER -->
                                <table cellpadding="0" cellspacing="0" align="center" width="600" style="background:#0E1116;color:white;border-radius:4px 4px 0 0">
                                    <tr>
                                        <td align="center" style="padding:25px">

                                            <p style="font-size:20px;margin:0;color:white;">
                                                Recuperación de contraseña
                                            </p>

                                            <img src="https://raw.githubusercontent.com/M1guel109/Proviservers-img/refs/heads/main/logos/LOGO%20POSITIVO.png"
                                                alt="Logo Proviservers"
                                                width="200"
                                                style="display:block;margin-top:15px">

                                        </td>
                                    </tr>
                                </table>

                                <!-- CONTENIDO -->
                                <table align="center" cellpadding="0" cellspacing="0" width="600" style="background:#FFFFFF;">
                                    <tr>
                                        <td style="padding:40px 20px;text-align:center;">

                                            <h1 style="color:#0E1116;margin:0;font-size:24px;">
                                                Tu nueva contraseña temporal
                                            </h1>

                                            <p style="color:#444;font-size:15px;margin-top:15px;">
                                                Has solicitado recuperar el acceso a tu cuenta en  
                                                <strong>Proviservers</strong>.<br>
                                                Aquí tienes tu nueva contraseña temporal.
                                            </p>

                                            <p style="margin-top:30px;font-size:15px;color:#0E1116;">
                                                <strong>📧 Email asociado:</strong><br>
                                                ' . htmlspecialchars($email) . '
                                            </p>

                                            <p style="margin-top:20px;font-size:16px;color:#0E1116;">
                                                <strong>🔐 Contraseña temporal:</strong><br>
                                                <span style="display:inline-block;margin-top:8px;padding:10px 18px;background:#0066FF;color:white;border-radius:6px;font-size:18px;font-weight:bold;">
                                                    ' . htmlspecialchars($nuevaClave) . '
                                                </span>
                                            </p>

                                            <p style="margin-top:25px;color:#444;">
                                                Te recomendamos cambiar esta contraseña inmediatamente después de ingresar.<br>
                                                Si no solicitaste este cambio, ignora este correo.
                                            </p>

                                        </td>
                                    </tr>
                                </table>

                                <!-- FOOTER -->
                                <table align="center" cellpadding="0" cellspacing="0" width="600" style="background:#0066FF;color:white;border-radius:0 0 4px 4px">
                                    <tr>
                                        <td style="padding:30px;text-align:left;font-size:14px;line-height:20px;">

                                            <img src="https://raw.githubusercontent.com/M1guel109/Proviservers-img/refs/heads/main/logos/LOGO%20POSITIVO.png" 
                                                width="150"
                                                style="display:block;margin-bottom:15px">

                                            <p style="margin:0;">
                                                © 2025 Proviservers — Plataforma de servicios locales.
                                            </p>

                                            <p style="margin:5px 0 0 0;">
                                                Este correo fue generado automáticamente.<br>
                                                Si no realizaste esta solicitud puedes ignorarlo sin problema.
                                            </p>

                                        </td>
                                    </tr>
                                </table>

                            </td>
                            </tr>
                            </table>

                            </body>
                            </html>

                    ';

                $mail->send();

                return true;
            } else {
                return ['error' => 'Usuario no encontrado o inactivo'];
            }
        } catch (PDOException $e) {
            error_log("Error en el modelo RecoveryPass:" . $e->getMessage());
            return ['error' => 'error interno del servidor'];
        }
    }
}
