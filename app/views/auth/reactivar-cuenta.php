<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Reactivar cuenta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/login.css">
</head>

<body>
    <header>
        <div class="header-container">
            <a href="<?= BASE_URL ?>/">
                <img src="<?= BASE_URL ?>/public/assets/img/logos/logo-principal.png"
                    alt="Proviservers - Plataforma de servicios locales" class="header-logo">
            </a>
        </div>
    </header>

    <main>
        <div class="custom-container">
            <section id="login">
                <h1>Reactivar cuenta</h1>
                <p>Ingresa tus credenciales para reactivar tu cuenta de proveedor pausada.</p>

                <form action="<?= BASE_URL ?>/reactivar-cuenta" method="POST">
                    <input type="hidden" name="accion" value="reactivar_cuenta">

                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" name="email" placeholder="usuario@ejemplo.com" required>
                    </div>

                    <div class="form-group">
                        <label for="clave">Contraseña</label>
                        <input type="password" id="clave" name="clave" placeholder="Ingresa tu contraseña" required>
                    </div>

                    <button type="submit" class="btn-login">Reactivar cuenta</button>
                </form>

                <div class="register-link">
                    <p><a href="<?= BASE_URL ?>/login">Volver al inicio de sesión</a></p>
                </div>
            </section>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
</body>

</html>
