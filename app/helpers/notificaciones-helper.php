<?php
require_once __DIR__ . '/mailer-helper.php';

function enviarCorreoProveedorActivado(string $email, string $nombres): bool
{
    try {
        $mail = mailer_init();
        $mail->setFrom('suppportproviservers@gmail.com', 'Soporte ProviServers');
        $mail->addAddress($email);
        $mail->Subject = 'ProviServers — ¡Tu cuenta de proveedor fue activada!';
        $mail->Body    = _htmlActivacion($nombres);
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo proveedor activado: " . $e->getMessage());
        return false;
    }
}

function _htmlActivacion(string $nombres): string
{
    $urlLogin = defined('BASE_URL') ? BASE_URL . '/login' : '#';
    $logo     = 'https://raw.githubusercontent.com/M1guel109/Proviservers-img/refs/heads/main/logos/LOGO%20POSITIVO.png';
    $nombre   = htmlspecialchars($nombres);

    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta content="width=device-width, initial-scale=1" name="viewport">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Cuenta Activada — ProviServers</title>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet">
    </head>
    <body style="margin:0;padding:0;background:#d4d4d4;font-family:Open Sans,Arial,sans-serif">
    <table cellspacing="0" cellpadding="0" width="100%">
    <tr><td valign="top">

        <!-- HEADER -->
        <table cellpadding="0" cellspacing="0" align="center" width="600"
               style="background:#0066ff;border-radius:4px 4px 0 0">
            <tr>
                <td align="center" style="padding:30px 25px">
                    <p style="font-size:22px;margin:0;color:#fff;font-weight:bold">
                        ¡Cuenta de proveedor activada!
                    </p>
                    <img src="' . $logo . '" alt="ProviServers" width="200"
                         style="display:block;margin:15px auto 0">
                </td>
            </tr>
        </table>

        <!-- BODY -->
        <table align="center" cellpadding="0" cellspacing="0" width="600"
               style="background:#ffffff">
            <tr>
                <td style="padding:40px 30px;text-align:center">

                    <!-- ícono de éxito -->
                    <div style="width:72px;height:72px;border-radius:50%;background:#dcfce7;
                                margin:0 auto 20px;display:flex;align-items:center;
                                justify-content:center;font-size:38px;line-height:72px">
                        ✅
                    </div>

                    <h1 style="color:#0e1116;font-size:24px;margin:0 0 12px">
                        ¡Felicitaciones, ' . $nombre . '!
                    </h1>

                    <p style="color:#444;font-size:15px;margin:0 0 20px;line-height:1.65">
                        Tu solicitud como <strong>Proveedor en ProviServers</strong> fue
                        revisada y <strong style="color:#16a34a">aprobada exitosamente</strong>.
                    </p>

                    <!-- lista de beneficios -->
                    <table align="center" cellpadding="6" cellspacing="0"
                           style="text-align:left;font-size:14px;color:#333;margin-bottom:28px">
                        <tr>
                            <td style="color:#16a34a;font-size:18px;padding-right:8px">✔</td>
                            <td>Publica tus servicios profesionales</td>
                        </tr>
                        <tr>
                            <td style="color:#16a34a;font-size:18px;padding-right:8px">✔</td>
                            <td>Recibe solicitudes directas de clientes</td>
                        </tr>
                        <tr>
                            <td style="color:#16a34a;font-size:18px;padding-right:8px">✔</td>
                            <td>Envía cotizaciones a oportunidades abiertas</td>
                        </tr>
                        <tr>
                            <td style="color:#16a34a;font-size:18px;padding-right:8px">✔</td>
                            <td>Gestiona tu agenda y servicios en curso</td>
                        </tr>
                    </table>

                    <!-- CTA -->
                    <a href="' . $urlLogin . '"
                       style="display:inline-block;padding:13px 34px;background:#0066ff;
                              color:#fff;text-decoration:none;border-radius:6px;
                              font-size:16px;font-weight:bold">
                        Ingresar a mi cuenta
                    </a>

                    <p style="margin-top:28px;font-size:13px;color:#888">
                        ¿Tienes alguna duda? Escríbenos a
                        <a href="mailto:suppportproviservers@gmail.com"
                           style="color:#0066ff">suppportproviservers@gmail.com</a>
                    </p>

                </td>
            </tr>
        </table>

        <!-- FOOTER -->
        <table align="center" cellpadding="0" cellspacing="0" width="600"
               style="background:#0e1116;border-radius:0 0 4px 4px">
            <tr>
                <td style="padding:30px;text-align:left;font-size:14px;
                           line-height:20px;color:#fff">
                    <img src="' . $logo . '" width="150"
                         style="display:block;margin-bottom:15px">
                    <p style="margin:0">© 2025 ProviServers — Plataforma de servicios locales.</p>
                    <p style="margin:6px 0 0">
                        Este correo fue generado automáticamente.<br>
                        Si no esperabas este mensaje, puedes ignorarlo sin problema.
                    </p>
                </td>
            </tr>
        </table>

    </td></tr>
    </table>
    </body>
    </html>';
}
