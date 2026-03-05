<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Plataforma de servicios locales</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="public/assets/auth/css/login.css">

    <!-- tu css -->
</head>

<body>
    <header>
        <div class="header-container">
            <a href="<?= BASE_URL ?>/">
                <img src="public/assets/img/logos/LOGO PRINCIPAL.png" alt="Proviservers - Plataforma de servicios locales"
                    class="header-logo">
            </a>
        </div>
    </header>

    <main>
        <div class="custom-container">
            <section id="login">
                <h1>Inicia Sesión</h1>
                <p>Accede a tu cuenta para encontrar o fofrecer servicios locales</p>
                <form id="loginForm" action="<?= BASE_URL ?>/iniciar-sesion" method="POST">

                    <input type="hidden" name="accion" value="iniciar_sesion">
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" name="email" placeholder="usuario@ejemplo.com" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="clave" placeholder="Ingresa tu contraseña" required>
                    </div>
                    <div class="remember-forgot">
                        <!-- <label>
                            <input type="checkbox"> Recordarme
                        </label> -->
                        <a href="<?= BASE_URL ?>/reestablecer-contrasena">¿Olvidaste tu contraseña?</a>
                    </div>
                    <button type="submit" class="btn-login">Iniciar Sesión</button>
                </form>
                <div class="register-link">
                    <p>¿No tienes una cuenta? <a href="<?= BASE_URL ?>/registro">Regístrate aquí</a></p>
                </div>
            </section>
            <section class="info-section">
                <div id="infoCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
                    <!-- <div class="carousel-indicators">
                        <button type="button" data-bs-target="#infoCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#infoCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                        <button type="button" data-bs-target="#infoCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                    </div> -->
                    <div class="carousel-inner">
                        <!-- Slide 1 -->
                        <div class="carousel-item active" style="background-image: url('public/assets/auth/img/carrousel-login/1.jpg');">
                            <div class="carousel-caption-content">
                                <h2>Te esperan trabajos muy buenos</h2>
                                <p>Encuentra las mejores oportunidades laborales en tu zona</p>
                            </div>
                        </div>
                        <!-- Slide 2 -->
                        <div class="carousel-item" style="background-image: url('public/assets/auth/img/carrousel-login/2.jpg');">
                            <div class="carousel-caption-content">
                                <h2>Conectamos talento con oportunidades</h2>
                                <p>Únete a nuestra comunidad de profesionales</p>
                            </div>
                        </div>
                        <!-- Slide 3 (podrías usar una imagen nueva o repetir una) -->
                        <div class="carousel-item" style="background-image: url('public/assets/auth/img/carrousel-login/1.jpg');">
                            <div class="carousel-caption-content">
                                <h2>¡Inicia sesión ahora!</h2>
                                <p>Accede a tu cuenta y comienza a conectar</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>


    <footer>
        <!-- Enlaces / Información -->
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script src="public/assets/auth/js/login.js"></script>
</body>

</html>