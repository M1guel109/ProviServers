<?php
require_once __DIR__ . '/../helpers/alert-helper.php';
require_once __DIR__ . '/../helpers/mailer-helper.php';
require_once BASE_PATH . '/app/models/contacto.php';

function procesarContacto(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    $nombre  = trim($_POST['nombre']  ?? '');
    $email   = trim($_POST['email']   ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    if (empty($nombre) || empty($email) || empty($mensaje)) {
        mostrarSweetAlert('warning', 'Campos vacíos',
            'Por favor completa todos los campos del formulario.',
            BASE_URL . '/#contact');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        mostrarSweetAlert('error', 'Email inválido',
            'El correo electrónico ingresado no tiene un formato válido.',
            BASE_URL . '/#contact');
        exit;
    }

    $modelo = new Contacto();
    $exito  = $modelo->registrarMensaje([
        'nombre'  => $nombre,
        'email'   => $email,
        'mensaje' => $mensaje,
    ]);

    if ($exito) {
        _enviarEmailContactoAdmin($nombre, $email, $mensaje);
        mostrarSweetAlert('success', '¡Mensaje Enviado!',
            'Gracias por escribirnos. Hemos recibido tus datos correctamente.',
            BASE_URL . '/#contact');
    } else {
        mostrarSweetAlert('error', 'Error del Servidor',
            'No pudimos guardar tu mensaje en este momento. Intenta más tarde.',
            BASE_URL . '/#contact');
    }
    exit;
}

function _enviarEmailContactoAdmin(string $nombre, string $emailRemitente, string $mensaje): void {
    try {
        $mail = mailer_init();
        $mail->setFrom(SMTP_USER, 'ProviServers');
        $mail->addAddress(SMTP_USER, 'Equipo ProviServers');
        $mail->addReplyTo($emailRemitente, $nombre);
        $mail->Subject = "📩 Nuevo mensaje de contacto — $nombre";
        $mail->Body = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
              <div style='background:#0066FF;padding:24px;border-radius:12px 12px 0 0;text-align:center;'>
                <h2 style='color:#fff;margin:0;'>Nuevo mensaje de contacto</h2>
              </div>
              <div style='background:#f9fafb;padding:28px;border:1px solid #e2e8f0;border-radius:0 0 12px 12px;'>
                <p style='margin:0 0 8px;'><strong>Nombre:</strong> " . htmlspecialchars($nombre) . "</p>
                <p style='margin:0 0 8px;'><strong>Correo:</strong> <a href='mailto:$emailRemitente'>$emailRemitente</a></p>
                <p style='margin:0 0 4px;'><strong>Mensaje:</strong></p>
                <div style='background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-top:8px;'>
                  " . nl2br(htmlspecialchars($mensaje)) . "
                </div>
                <div style='margin-top:20px;text-align:center;'>
                  <a href='" . BASE_URL . "/admin/mensajes-contacto'
                     style='background:#0066FF;color:#fff;padding:10px 24px;border-radius:8px;text-decoration:none;font-weight:bold;'>
                    Ver en el panel
                  </a>
                </div>
              </div>
              <p style='text-align:center;font-size:12px;color:#94a3b8;margin-top:16px;'>ProviServers — plataforma de servicios locales</p>
            </div>";
        $mail->send();
    } catch (\Exception $e) {
        error_log("Email contacto admin error: " . $e->getMessage());
    }
}
