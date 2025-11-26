<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Restablecer Contraseña</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/login.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/restablecer-contrasena.css">
</head>

<body>
    <header>
        <!-- Navbar / Logo / hero -->
    </header>

    <main>
        <div class="custom-container">
            <section id="reset-password">
                <h1>RESTABLECER CONTRASEÑA</h1>
                <p>Recupera el acceso a tu cuenta para seguir encontrando o ofreciendo servicios locales</p>

                <div class="reset-steps">
                    <h3>Proceso de recuperación:</h3>
                    <ol>
                        <li>Ingresa tu correo electrónico registrado</li>
                        <li>Recibirás un enlace de restablecimiento</li>
                        <li>Sigue las instrucciones en el correo</li>
                        <li>Crea una nueva contraseña</li>
                    </ol>
                </div>

                <div class="success-message" id="successMessage">
                    <i class="fas fa-check-circle"></i>
                    ¡Enlace de restablecimiento enviado! Revisa tu correo electrónico.
                </div>

                <form action="<?= BASE_URL ?>/generar-clave" id="resetPasswordForm" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="reset-email">Correo Electrónico</label>
                        <input type="email" name="correo" id="reset-email" placeholder="usuario@ejemplo.com" required>
                    </div>

                    <!-- <div class="form-group">
                        <label for="asunto">Asunto</label>
                        <input type="text" name="asunto" id="asunto" placeholder="Asunto del mensaje" required>
                    </div>

                    <div class="form-group">
                        <label for="mensaje">Mensaje</label>
                        <textarea name="mensaje" id="mensaje" placeholder="Escribe tu mensaje aquí..." required></textarea>
                    </div> -->

                    <button type="submit" class="btn-reset">Enviar enlace de restablecimiento</button>
                </form>
                
                <div class="back-to-login">
                    <p><a href="<?= BASE_URL ?>/login">← Volver al inicio de sesión</a></p>
                </div>
            </section>
            <section class="info-section">
                <div class="info-card">
                    <h2>Recupera tu <br> cuenta para <br> seguir <br> conectado con <br> oportunidades</h2>
                </div>
                <div class="image-container">
                    <img src="<?= BASE_URL ?>/public/assets/auth/img/women with tab 1.png" alt="Plataforma de trabajo" class="info-image">
                </div>
            </section>
        </div>
    </main>

    <footer>
        <!-- Enlaces / Información -->
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbJQN"
        crossorigin="anonymous"></script>

    <!-- Font Awesome para iconos -->
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>

    <script src="<?= BASE_URL ?>/public/assets/auth/js/restablecer-contrasena.js"></script>
</body>

</html>