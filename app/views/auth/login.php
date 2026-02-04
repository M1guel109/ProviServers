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
                <form id="loginForm" action="iniciar-sesion" method="POST">
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
                <div id="infoCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                    <div class="carousel-inner">
                        <!-- Slide 1: Imagen 1 -->
                        <div class="carousel-item active">
                            <div class="carousel-image-container">
                                <img src="public/assets/auth/img/carrousel-login/1.jpg" alt="Plataforma de trabajo"
                                    class="carousel-image">
                            </div>
                        </div>
                        <!-- Slide 2: Texto 1 -->
                        <div class="carousel-item">
                            <div class="carousel-text-container">
                                <div class="text-card">
                                    <h2>Te esperan <br> trabajos muy <br> buenos ! <br> Inicia sesión <br> ahora</h2>
                                </div>
                            </div>
                        </div>
                        <!-- Slide 3: Imagen 2 -->
                        <div class="carousel-item">
                            <div class="carousel-image-container">
                                <img src="public/assets/auth/img/carrousel-login/2.jpg" alt="Profesionales"
                                    class="carousel-image">
                            </div>
                        </div>
                        <!-- Slide 4: Texto 2 -->
                        <div class="carousel-item">
                            <div class="carousel-text-container">
                                <div class="text-card">
                                    <h2>Conectamos <br> talento con <br> oportunidades <br> en tu zona</h2>
                                </div>
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