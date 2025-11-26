<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Plataforma de servicios locales</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- css de estilos globales o generales -->
    <link rel="stylesheet" href="public/assets/webSite/css/landing.css">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- tu css -->
</head>
<body>
    <header>
        <!-- Navbar / Logo / hero -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container-fluid py-2 px-4">
            <a class="navbar-brand fw-bold text-primary" href="#">
            <img src="public/assets/img/logos/LOGO PRINCIPAL.png" alt="Logo Proviservers" width="280" class="me-2"></a>

            <!-- Botón responsive -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>

            <!-- LINKS -->
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center gap-3">
                <li class="nav-item"><a class="nav-link active" href="#hero">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="#servicios">Sobre Nosotros</a></li>
                <li class="nav-item"><a class="nav-link" href="#roles">Roles</a></li>
                <li class="nav-item"><a class="nav-link" href="#team">Nuestro Equipo</a></li>
                <li class="nav-item"><a class="nav-link" href="#pricing">Planes</a></li>
                <li class="nav-item"><a class="nav-link" href="#testimonials">Reseñas</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contacto</a></li>
                <li class="nav-item">
                <a href="login" class="btn btn-primary px-4">Ingresar</a>
                </li>
            </ul>
            </div>
        </div>
        </nav>

<!-- HERO SECTION -->
        <section id="hero" class="hero d-flex align-items-start position-relative">
            <div class="hero-carousel">
                <img src="public/assets/webSite/img/fondoLanding.png" class="hero-img active" alt="Imagen 1">
                <img src="public/assets/webSite/img/fondoLanding2.jpg" class="hero-img" alt="Imagen 2">
                <img src="public/assets/webSite/img/fondoLanding3.jpg" class="hero-img" alt="Imagen 3">
            </div>
            <div class="container">
                <div class="row justify-content-end">
                    <div class="col-md-6  offset-md-3 text-end">
                        <h1 class="fw-bold mb-4">
                            PROVISERVERS TE CONECTA <br> CON QUIENES HACEN QUE <br> TODO FUNCIONE
                        </h1>
                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-end">
                            <a href="<?= BASE_URL ?>/registro" class="btn-primary-proviservers">REGISTRARME</a>
                            <a href="#" class="btn-secondary-proviservers">VER SERVICIOS</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </header>

    <main>
        <!-- Secciones -->
        <section id="servicios" class="servicios py-5">
            <div class="container">
                <div class="row align-items-center">       
                <div class="col-md-6">
                    <p class="text-uppercase fs-6 mb-2">Servicios de Confianza, al Alcance de tu Mano</p>
                    <h2 class="fw-bold fs-2 mb-3">Servicios de alta calidad y <br> amigables a precios justos</h2>
                    <p class="fs-5 text-muted">
                    ProviServers es una plataforma digital que conecta personas con proveedores de servicios locales de forma  rápida, segura y confiable. Permite encontrar expertos verificados, comparar opciones y contratar fácilmente todo  tipo de servicios desde un solo lugar.
                    </p>
                </div>

                <div class="contenedor-superpuesto">
                <img src="public/assets/webSite/img/Servicios2.png" data-aos="fade-down" data-aos-duration="1000" alt="Persona mayor sonriendo" class="img-servicio-base">
                <img src="public/assets/webSite/img/Servicios.png" data-aos="fade-up" data-aos-duration="1500" alt="Persona usando taladro" class="img-servicio-superpuesta">
                </div>
                </div>
            </div>
        </section>

        <section id="roles" class="roles-section">
        <div class="container">
            <div class="section-divider"></div>

            <div class="roles-header">
            <div class="roles-title">
                <h2>“Dos Roles, Una Sola <br > Conexión: Soluciones Reales.”</h2>
            </div>
            <div class="roles-text">
                <h4>Roles</h4>
                <p>
                Nuestros clientes encuentran lo que necesitan.<br>
                Nuestros proveedores hacen que suceda.
                </p>
            </div>
            </div>

            <!-- Tarjetas -->
            <div class="roles-cards">
            <article class="role-card">
                <img src="public/assets/webSite/img/roles1.png" alt="Proveedor" class="role-img">
                <div class="role-content">
                <h5>Proveedor</h5>
                <p>
                    Es un experto verificado que ofrece soluciones profesionales a las
                    necesidades de los clientes, con compromiso, responsabilidad y precisión
                    en cada servicio.
                </p>
                <a href="#" class="btn-role">Conoce más <i class="bi bi-arrow-up-right"></i></a>
                </div>
            </article>

            <article class="role-card">
                <img src="public/assets/webSite/img/roles2.png" alt="Cliente" class="role-img">
                <div class="role-content">
                <h5>Cliente</h5>
                <p>
                    Es el usuario que busca resolver necesidades cotidianas de forma segura
                    y rápida, conectando con expertos confiables desde una sola plataforma.
                </p>
                <a href="#" class="btn-role blue">Conoce más <i class="bi bi-arrow-up-right"></i></a>
                </div>
            </article>
            </div>
        </div>
        </section>

        <section id="team" class="team-section">
            <div class="container">
            <div class="team-header">
                <h2 class="team-title">La solución perfecta empieza con el proveedor correcto.</h2>
                <div class="team-info">
                <h3 class="info-title">Equipo de expertos</h3>
                <p class="info-description">
                    Profesionales verificados que ofrecen un servicio seguro, eficiente y de primer nivel para cada necesidad.
                </p>
                </div>
            </div>

            <div class="divider"></div>

            <div class="cards-row">
                <div class="card">
                <div class="card-image">
                    <img src="public/assets/webSite/img/team1.png" alt="Karen Heredia">
                </div>
                <div class="card-content">
                    <div class="card-info">
                    <h3 class="card-name">Karen Heredia</h3>
                    <div class="stars">
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                    </div>
                    <p class="card-description">
                        Es un desarrollador integral que domina tanto el front-end como el back-end, conectando lo visual con lo funcional para ofrecer soluciones completas, eficientes y escalables en cada proyecto.
                    </p>
                    </div>
                    <div class="social-icons">
                    <a href="https://github.com/KarenHeredia1621" target="_blank" class="social-link" aria-label="GitHub">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 
                        3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 
                        0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61 
                        -.546-1.385-1.333-1.754-1.333-1.754-1.089-.744.084-.729.084-.729 
                        1.205.084 1.84 1.236 1.84 1.236 1.07 1.835 2.809 1.305 3.495.998 
                        .108-.776.418-1.305.762-1.605-2.665-.3-5.466-1.334-5.466-5.931 
                        0-1.31.469-2.381 1.236-3.221-.124-.303-.535-1.523.117-3.176 
                        0 0 1.008-.322 3.301 1.23a11.52 11.52 0 0 1 3.003-.404 
                        c1.018.005 2.042.138 3.003.404 2.291-1.552 3.297-1.23 
                        3.297-1.23.653 1.653.242 2.873.118 3.176.77.84 
                        1.235 1.911 1.235 3.221 0 4.609-2.803 5.628-5.475 
                        5.922.43.372.823 1.102.823 2.222 0 1.606-.015 
                        2.896-.015 3.286 0 .317.218.687.825.57C20.565 
                        22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
                    </svg>
                    </a>
                    <a href="https://www.linkedin.com/in/karen-heredia-07bb04368/" target="_blank" class="social-link" aria-label="LinkedIn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </a>
                    </div>
                </div>
                </div>

                <div class="card card-bordered">
                <div class="card-image">
                    <img src="public/assets/webSite/img/team2.png" alt="Schneider Manrrique">
                </div>
                <div class="card-content">
                    <div class="card-info">
                    <h3 class="card-name">Schneider Manrrique</h3>
                    <div class="stars">
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                    </div>
                    <p class="card-description">
                        Es un profesional que construye la lógica, estructura y seguridad del sistema, asegurando que los datos fluyan correctamente entre la interfaz y el servidor, con alto rendimiento y fiabilidad.
                    </p>
                    </div>
                    <div class="social-icons">
                    <a href="https://github.com/SchDEv-spn" target="_blank" class="social-link" aria-label="GitHub">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 
                        3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 
                        0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61 
                        -.546-1.385-1.333-1.754-1.333-1.754-1.089-.744.084-.729.084-.729 
                        1.205.084 1.84 1.236 1.84 1.236 1.07 1.835 2.809 1.305 3.495.998 
                        .108-.776.418-1.305.762-1.605-2.665-.3-5.466-1.334-5.466-5.931 
                        0-1.31.469-2.381 1.236-3.221-.124-.303-.535-1.523.117-3.176 
                        0 0 1.008-.322 3.301 1.23a11.52 11.52 0 0 1 3.003-.404 
                        c1.018.005 2.042.138 3.003.404 2.291-1.552 3.297-1.23 
                        3.297-1.23.653 1.653.242 2.873.118 3.176.77.84 
                        1.235 1.911 1.235 3.221 0 4.609-2.803 5.628-5.475 
                        5.922.43.372.823 1.102.823 2.222 0 1.606-.015 
                        2.896-.015 3.286 0 .317.218.687.825.57C20.565 
                        22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
                    </svg>
                    </a>
                    <a href="https://co.linkedin.com/in/schneider-manrique-osorio" target="_blank" class="social-link" aria-label="LinkedIn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </a>
                    </div>
                </div>
                </div>

                <div class="card">
                <div class="card-image">
                    <img src="public/assets/webSite/img/team3.png" alt="Ivonne Lozada">
                </div>
                <div class="card-content">
                    <div class="card-info">
                    <h3 class="card-name">Ivonne Lozada</h3>
                    <div class="stars">
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                    </div>
                    <p class="card-description">
                        Es un miembro experto que transforma las ideas en experiencias visuales, desarrollando interfaces interactivas y accesibles que garantizan una navegación fluida y atractiva para el usuario.
                    </p>
                    </div>
                    <div class="social-icons">
                    <a href="https://github.com/IvoBeltran" target="_blank" class="social-link" aria-label="GitHub">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 
                        3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 
                        0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61 
                        -.546-1.385-1.333-1.754-1.333-1.754-1.089-.744.084-.729.084-.729 
                        1.205.084 1.84 1.236 1.84 1.236 1.07 1.835 2.809 1.305 3.495.998 
                        .108-.776.418-1.305.762-1.605-2.665-.3-5.466-1.334-5.466-5.931 
                        0-1.31.469-2.381 1.236-3.221-.124-.303-.535-1.523.117-3.176 
                        0 0 1.008-.322 3.301 1.23a11.52 11.52 0 0 1 3.003-.404 
                        c1.018.005 2.042.138 3.003.404 2.291-1.552 3.297-1.23 
                        3.297-1.23.653 1.653.242 2.873.118 3.176.77.84 
                        1.235 1.911 1.235 3.221 0 4.609-2.803 5.628-5.475 
                        5.922.43.372.823 1.102.823 2.222 0 1.606-.015 
                        2.896-.015 3.286 0 .317.218.687.825.57C20.565 
                        22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
                    </svg>
                    </a>
                    </div>
                </div>
                </div>
            </div>

            <div class="cards-row cards-row-centered">
                <div class="card">
                <div class="card-image">
                    <img src="public/assets/webSite/img/team5.png" alt="Miguel Lozano">
                </div>
                <div class="card-content">
                    <div class="card-info">
                    <h3 class="card-name">Miguel Lozano</h3>
                    <div class="stars">
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                    </div>
                    <p class="card-description">
                        Es un miembro experto que transforma las ideas en experiencias visuales, desarrollando interfaces interactivas y accesibles que garantizan una navegación fluida y atractiva para el usuario.
                    </p>
                    </div>
                    <div class="social-icons">
                    <a href="https://github.com/M1guel109" target="_blank" class="social-link" aria-label="GitHub">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 
                        3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 
                        0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61 
                        -.546-1.385-1.333-1.754-1.333-1.754-1.089-.744.084-.729.084-.729 
                        1.205.084 1.84 1.236 1.84 1.236 1.07 1.835 2.809 1.305 3.495.998 
                        .108-.776.418-1.305.762-1.605-2.665-.3-5.466-1.334-5.466-5.931 
                        0-1.31.469-2.381 1.236-3.221-.124-.303-.535-1.523.117-3.176 
                        0 0 1.008-.322 3.301 1.23a11.52 11.52 0 0 1 3.003-.404 
                        c1.018.005 2.042.138 3.003.404 2.291-1.552 3.297-1.23 
                        3.297-1.23.653 1.653.242 2.873.118 3.176.77.84 
                        1.235 1.911 1.235 3.221 0 4.609-2.803 5.628-5.475 
                        5.922.43.372.823 1.102.823 2.222 0 1.606-.015 
                        2.896-.015 3.286 0 .317.218.687.825.57C20.565 
                        22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
                    </svg>
                    </a>
                    <a href="https://www.linkedin.com/in/miguel-lozano-716522358?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app" target="_blank" class="social-link" aria-label="LinkedIn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </a>
                    </div>
                </div>
                </div>

                <div class="card">
                <div class="card-image">
                    <img src="public/assets/webSite/img/team4.png" alt="Juanita Jimenez">
                </div>
                <div class="card-content">
                    <div class="card-info">
                    <h3 class="card-name">Juanita Jimenez</h3>
                    <div class="stars">
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                    </div>
                    <p class="card-description">
                        Es un miembro experto que transforma las ideas en experiencias visuales, desarrollando interfaces interactivas y accesibles que garantizan una navegación fluida y atractiva para el usuario.
                    </p>
                    </div>
                    <div class="social-icons">
                    <a href="https://github.com/JuanitaJimenez09" target="_blank" class="social-link" aria-label="GitHub">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 
                        3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 
                        0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61 
                        -.546-1.385-1.333-1.754-1.333-1.754-1.089-.744.084-.729.084-.729 
                        1.205.084 1.84 1.236 1.84 1.236 1.07 1.835 2.809 1.305 3.495.998 
                        .108-.776.418-1.305.762-1.605-2.665-.3-5.466-1.334-5.466-5.931 
                        0-1.31.469-2.381 1.236-3.221-.124-.303-.535-1.523.117-3.176 
                        0 0 1.008-.322 3.301 1.23a11.52 11.52 0 0 1 3.003-.404 
                        c1.018.005 2.042.138 3.003.404 2.291-1.552 3.297-1.23 
                        3.297-1.23.653 1.653.242 2.873.118 3.176.77.84 
                        1.235 1.911 1.235 3.221 0 4.609-2.803 5.628-5.475 
                        5.922.43.372.823 1.102.823 2.222 0 1.606-.015 
                        2.896-.015 3.286 0 .317.218.687.825.57C20.565 
                        22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
                    </svg>
                    </a>
                    <a href="https://www.linkedin.com/in/juanita-tolosa-32a21a349?trk=contact-info" target="_blank" class="social-link" aria-label="LinkedIn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </a>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </section>

        <section id="pricing" class="pricing-section">
            <div class="pricing-background">
            <img src="public/assets/webSite/img/Background.png" alt="Background" class="background-image">
            </div>

            <div class="pricing-container">
            <div class="pricing-header">
                <p class="pricing-subtitle">Nuestros Planes</p>
                <h2 class="pricing-title">Elija entre nuestros planes y <br> precios más bajos</h2>
            </div>

            <div class="pricing-toggle">
                <button class="toggle-btn active">Mensual</button>
                <button class="toggle-btn">Anual</button>
            </div>

            <div class="pricing-cards">
                <div class="pricing-card">
                <div class="card-header">
                    <h3 class="plan-name">PAQUETE BÁSICO</h3>
                    <div class="plan-price">
                    <span class="currency">$</span>
                    <span class="price">Gratis</span>
                    <span class="period">/Mensual</span>
                    </div>
                </div>
                <ul class="plan-features">
                    <li>Crear perfil profesional básico</li>
                    <li>Publicar hasta 3 servicios</li>
                    <li>Acceso a solicitudes de clientes cercanos</li>
                    <li>Calificaciones visibles</li>
                    <li>Chat limitado con clientes</li>
                    <li>Soporte por correo electrónico</li>
                </ul>
                <a href="#" class="btn-plan btn-primary">Reserva ahora</a>
                </div>

                <div class="pricing-card featured">
                <div class="card-header">
                    <h3 class="plan-name">PAQUETE CRECIMIENTO</h3>
                    <div class="plan-price">
                    <span class="currency">$</span>
                    <span class="price">25.000</span>
                    <span class="period">/Mensual</span>
                    </div>
                </div>
                <ul class="plan-features">
                    <li>Todo lo del Paquete Básico</li>
                    <li>Publicar hasta 10 servicios</li>
                    <li>Acceso a clientes sin límite de zona</li>
                    <li>Chat directo y sin restricciones</li>
                    <li>Perfil profesional optimizado con verificación y métricas básicas</li>
                </ul>
                <a href="#" class="btn-plan btn-secondary">Reserva ahora</a>
                </div>

                <div class="pricing-card">
                <div class="card-header">
                    <h3 class="plan-name">PAQUETE PRÉMIUM</h3>
                    <div class="plan-price">
                    <span class="currency">$</span>
                    <span class="price">49.000</span>
                    <span class="period">/Mensual</span>
                    </div>
                </div>
                <ul class="plan-features">
                    <li>Todo lo del Paquete Crecimiento</li>
                    <li>Publicaciones ilimitadas</li>
                    <li>Prioridad en resultados de búsqueda</li>
                    <li>Sello de proveedor destacado</li>
                    <li>Exposición preferencial en campañas y clientes destacados</li>
                    <li>Soporte prioritario</li>
                </ul>
                <a href="#" class="btn-plan btn-secondary">Reserva ahora</a>
                </div>
            </div>
            </div>
        </section>

        <section id="testimonials" class="testimonials-section">
        <div class="container testimonials-container">
            <div class="testimonials-header">
            <div class="testimonials-text">
                <h2>Comentarios sobre su experiencia con nosotros</h2>
                <p>
                Nuestros clientes no solo contratan un servicio, viven una experiencia.
                Lea sus testimonios y vea cómo hemos hecho la diferencia en sus hogares y en su día a día.
                </p>

                <div class="testimonial-buttons">
                <button class="arrow-btn prev"><i class="fas fa-arrow-left"></i></button>
                <button class="arrow-btn next"><i class="fas fa-arrow-right"></i></button>
                </div>
            </div>

            <div class="testimonial-carousel">
                <!-- Testimonio 1 -->
                <div class="testimonial-card active">
                <div class="testimonial-content">
                    <div class="testimonial-avatar">
                    <img src="public/assets/webSite/img/comentarios.png" alt="Cliente Carlos Marín" />
                    </div>
                    <div class="testimonial-info">
                    <h3>Carlos Marín</h3>
                    <p class="client-role">Cliente</p>
                    <div class="stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        Muy satisfecho con el servicio. Agendé desde ProviServers y todo fue súper fácil.
                        El proveedor llegó puntual, fue muy profesional y dejó todo impecable.
                        Se nota el compromiso y la calidad. Sin duda volveré a contratar por aquí.
                    </p>
                    </div>
                    <div class="testimonial-icon"><i class="fas fa-quote-right"></i></div>
                </div>
                </div>

                <!-- Testimonio 2 -->
                <div class="testimonial-card">
                <div class="testimonial-content">
                    <div class="testimonial-avatar">
                    <img src="public/assets/webSite/img/comentarios.png" alt="Cliente Laura Gómez" />
                    </div>
                    <div class="testimonial-info">
                    <h3>Laura Gómez</h3>
                    <p class="client-role">Cliente</p>
                    <div class="stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                    </div>
                    <p class="testimonial-text">
                        Excelente experiencia, el proveedor fue muy amable y cumplido.
                        La plataforma me ayudó a encontrar justo lo que necesitaba sin complicaciones.
                    </p>
                    </div>
                    <div class="testimonial-icon"><i class="fas fa-quote-right"></i></div>
                </div>
                </div>

                <!-- Testimonio 3 -->
                <div class="testimonial-card">
                <div class="testimonial-content">
                    <div class="testimonial-avatar">
                    <img src="public/assets/webSite/img/comentarios.png" alt="Proveedor Andrés Torres" />
                    </div>
                    <div class="testimonial-info">
                    <h3>Andrés Torres</h3>
                    <p class="client-role">Proveedor</p>
                    <div class="stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        Gracias a ProviServers he conseguido nuevos clientes y he crecido mi negocio.
                        Es una herramienta muy útil para quienes ofrecemos servicios locales.
                    </p>
                    </div>
                    <div class="testimonial-icon"><i class="fas fa-quote-right"></i></div>
                </div>
                </div>

                <!-- Testimonio 4 -->
                <div class="testimonial-card">
                <div class="testimonial-content">
                    <div class="testimonial-avatar">
                    <img src="public/assets/webSite/img/comentarios.png" alt="Cliente María Fernanda López" />
                    </div>
                    <div class="testimonial-info">
                    <h3>María Fernanda López</h3>
                    <p class="client-role">Cliente</p>
                    <div class="stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        Me encanta el diseño y la facilidad de uso de la plataforma. 
                        Además, el servicio al cliente fue excelente. Muy recomendada.
                    </p>
                    </div>
                    <div class="testimonial-icon"><i class="fas fa-quote-right"></i></div>
                </div>
                </div>
            </div>
            </div>
        </div>
        </section>

        <section id="contact" class="contact-section" id="contacto">
        <div class="container contact-container">
            <div class="contact-info">
            <h2>CONTÁCTANOS</h2>

            <div class="contact-card">
                <div class="icon-circle">
                <i class="fas fa-phone"></i>
                </div>
                <div class="contact-details">
                <h4>Llámanos</h4>
                <p><a href="tel:+018000255201">+01 8000 255 201</a></p>
                </div>
            </div>

            <div class="contact-card">
                <div class="icon-circle">
                <i class="fas fa-envelope"></i>
                </div>
                <div class="contact-details">
                <h4>Correo Electrónico</h4>
                <p><a href="mailto:Hello@proviservers.com">Hello@proviservers.com</a></p>
                </div>
            </div>

            <div class="contact-card">
                <div class="icon-circle">
                <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="contact-details">
                <h4>Dirección</h4>
                <p><a href="https://www.google.com/maps?q=7510+Villeta+Cundinamarca" target="_blank">7510, Villeta Cundinamarca</a></p>
                </div>
            </div>
            </div>

            <!-- Formulario -->
            <div class="contact-form">
            <h5>Información de Contacto</h5>
            <h3>Mantenerse en contacto</h3>
            <p>
                Priorizamos responder a sus consultas con prontitud para garantizar
                que reciba la asistencia que necesita de manera oportuna.
            </p>

            <form id="contactForm">
                <input type="text" id="name" name="name" placeholder="Nombre" required />
                <input type="email" id="email" name="email" placeholder="Correo electrónico" required />
                <textarea id="message" name="message" rows="5" placeholder="Mensaje" required></textarea>
                <button type="submit" class="btn-submit">Enviar Mensaje</button>
            </form>
            </div>
        </div>
        </section>


    </main>


    <footer class="bg-dark text-white py-5">
    <div class="container">
        
        <div class="row mb-5 text-center">
        <div class="col">
            <img src="public/assets/img/logos/LOGO POSITIVO.png" alt="Logo Proviservers" width="200" />
        </div>
        </div>

        <div class="row text-start align-items-start">
        
        <div class="col-md-4 mb-4 mb-md-0">
            <p class="text-white-50">
            Mantente al día con nuestros últimos consejos, novedades de servicios y artículos útiles para contratar expertos confiables y resolver tus necesidades cotidianas de forma fácil, segura y eficiente.
            </p>
        </div>

        <div class="col-md-2 mb-4 mb-md-0">
            <h5 class="fw-bold mb-3">Compañía</h5>
            <ul class="list-unstyled">
            <li><a href="#" class="text-white text-decoration-none d-block mb-2">Sobre nosotros</a></li>
            <li><a href="#" class="text-white text-decoration-none d-block mb-2">Servicios</a></li>
            <li><a href="#" class="text-white text-decoration-none d-block">Nuestro equipo</a></li>
            </ul>
        </div>

        <div class="col-md-3 mb-4 mb-md-0">
            <h5 class="fw-bold mb-3">Saber más</h5>
            <ul class="list-unstyled">
            <li><a href="#" class="text-white text-decoration-none d-block mb-2">Soporte</a></li>
            <li><a href="#" class="text-white text-decoration-none d-block mb-2">Política de privacidad</a></li>
            <li><a href="#" class="text-white text-decoration-none d-block">Términos y condiciones</a></li>
            </ul>
        </div>

        <div class="col-md-3">
            <h5 class="fw-bold mb-3">Hoja informativa</h5>
            <form class="d-flex flex-column gap-3">
            <input 
                type="email" 
                class="form-control bg-transparent border-secondary text-white" 
                placeholder="El correo electrónico va aquí." 
                required 
            />
            <button type="submit" class="btn btn-primary footer-btn fw-semibold">Enviar</button>
            </form>
        </div>

        </div> 
    </div>


        <!-- Enlaces / Información -->
        <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
    AOS.init();
    </script>

    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="public/assets/webSite/js/landing.js"></script>
</body>
</html>
