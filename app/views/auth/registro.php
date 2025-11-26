<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro</title>

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">

  <!-- Tu CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/registro.css">
</head>

<body>

  <div id="contenedor">

    <!-- COLUMNA IZQUIERDA -->
    <div class="columna izquierda">

      <h1>REGÍSTRATE</h1>
      <p>Crea una cuenta y conecta con expertos o clientes en tu zona.</p>

      <!-- WIZARD -->
      <form action="<?= BASE_URL ?>/registro-usuario" method="post" id="registro-wizard" enctype="multipart/form-data">

        <!-- PASO 1 -->
        <div class="wizard-step" id="paso-1">
          <h2 class="titulo-paso">Información básica</h2>

          <input type="text" id="documento" name="documento" placeholder="Número de Documento" required>
          <input type="email" id="email" name="email" placeholder="Correo electrónico" required>

          <select id="rol" name="rol" required>
            <option value="" disabled selected>Selecciona tu rol</option>
            <option value="cliente">Cliente</option>
            <option value="proveedor">Proveedor</option>
          </select>

          <!-- Estas contraseñas se ocultarán si usas documento como clave -->
          <input type="password" id="contrasena" name="clave" placeholder="Contraseña" required>

          <input type="password" id="confirmar" name="confirmar" placeholder="Confirmar contraseña" required>

          <div id="feedback-confirmar" class="invalid-feedback" style="display: block; margin-top: -10px; margin-bottom: 10px; padding: 0 10px; color: #dc3545;">
          </div>

          <div class="fila-botones">
            <p class="login-text">
              ¿Ya tienes una cuenta?
              <a href="<?= BASE_URL ?>/login" class="olvido-pass">Inicia sesión aquí</a>
            </p>

            <div class="wizard-nav">
              <button type="button" class="btn-next" data-next="paso-2">Siguiente</button>
            </div>
          </div>

        </div>

        <!-- PASO 2 -->
        <div class="wizard-step d-none" id="paso-2">
          <h2 class="titulo-paso">Datos personales</h2>

          <input type="text" id="nombres" name="nombres" placeholder="Nombres" required>
          <input type="text" id="apellidos" name="apellidos" placeholder="Apellidos" required>
          <div class="col-12 mt-3">
            <label for="foto" class="form-label">Foto de Perfil (Opcional)</label>
            <input type="file" id="foto" name="foto" accept="image/*" class="form-control">
          </div>
          <input type="tel" id="telefono" name="telefono" placeholder="Teléfono" required>
          <input type="text" id="ubicacion" name="ubicacion" placeholder="Ubicación" required>

          <div class="wizard-nav">
            <button type="button" class="btn-prev" data-prev="paso-1">Atrás</button>
            <button type="button" class="btn-next" data-next="paso-3">Siguiente</button>
          </div>
        </div>

        <!-- PASO 3 (solo proveedor) -->
        <div class="wizard-step d-none" id="paso-3">

          <h2 class="titulo-paso">Documentación</h2>
          <p class="text-muted">Esta sección es obligatoria solo para proveedores.</p>

          <div id="docs-proveedor">

            <label>Cédula (PDF o imagen)</label>
            <input type="file" id="doc-cedula" name="doc-cedula" accept="image/*,.pdf">

            <label>Selfie de verificación</label>
            <input type="file" id="doc-selfie" name="doc-foto" accept="image/*">

            <label>Antecedentes judiciales (PDF)</label>
            <input type="file" id="doc-antecedentes" name="doc-antecedentes" accept=".pdf">

            <label>Certificado de habilidades (opcional)</label>
            <input type="file" id="doc-certificado" name="doc-certificado" accept="image/*,.pdf">
          </div>

          <div class="wizard-nav">
            <button type="button" class="btn-prev" data-prev="paso-2">Atrás</button>
            <button type="button" class="btn-next" data-next="paso-4">Siguiente</button>
          </div>
        </div>

        <!-- PASO 4 -->
        <div class="wizard-step d-none" id="paso-4">
          <h2 class="titulo-paso">Confirmación</h2>
          <p class="text-muted">Revisa que toda la información sea correcta.</p>

          <div id="resumen-registro"></div>

          <button id="btn-finalizar" type="submit">Crear cuenta</button>
          <button type="button" class="btn-prev mt-2" data-prev="paso-3">Atrás</button>
        </div>

      </form>


    </div>

    <!-- COLUMNA DERECHA (CARRUSEL) -->
    <div class="columna derecha">

      <div id="carouselRegistro" class="carousel slide" data-bs-ride="carousel">

        <div class="carousel-inner">

          <div class="carousel-item active" data-bs-interval="4000">
            <div class="contenido-derecha">
              <h2>¡Comienza ahora y encuentra<br>soluciones o nuevas oportunidades!</h2>
              <img src="<?= BASE_URL ?>/public/assets/img/registro/women with tab 2.png" class="imagen-persona">
            </div>
          </div>

          <div class="carousel-item" data-bs-interval="4000">
            <div class="contenido-derecha">
              <h2>¡Conecta con profesionales confiables!</h2>
              <img src="<?= BASE_URL ?>/public/assets/img/registro/6458.png" class="imagen-persona">
            </div>
          </div>

          <div class="carousel-item" data-bs-interval="4000">
            <div class="contenido-derecha">
              <h2>¡Obtén ayuda rápida y segura!</h2>
              <img src="<?= BASE_URL ?>/public/assets/img/registro/515.png" class="imagen-persona">
            </div>
          </div>

        </div>

      </div>

    </div>

  </div>

  <!-- JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/auth/js/registro.js"></script>

</body>

</html>