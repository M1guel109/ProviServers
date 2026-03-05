<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proviservers | Registro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="public/assets/estilosGenerales/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/auth/css/registro.css">
</head>

<body>
  <header>
    <div class="header-container">
      <a href="<?= BASE_URL ?>/">
        <img src="public/assets/img/logos/LOGO PRINCIPAL.png" alt="Proviservers" class="header-logo">
      </a>
    </div>
  </header>

  <main>
    <div class="custom-container">
      <section id="registro">
        <div class="registro-header">
          <h1>Regístrate</h1>
          <p>Crea tu cuenta en pocos pasos.</p>
        </div>

        <form id="registro-wizard" action="<?= BASE_URL ?>/registro-usuario" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="accion" value="registrar">

          <div class="wizard-step" id="paso-1">
            <h2 class="titulo-paso">Información básica</h2>

            <div class="row g-2">
              <div class="col-md-6 form-group">
                <label for="documento">Documento</label>
                <input type="text" id="documento" name="documento" placeholder="Identificación" required>
              </div>
              <div class="col-md-6 form-group">
                <label for="email">Correo</label>
                <input type="email" id="email" name="email" placeholder="usuario@ejemplo.com" required>
              </div>
            </div>

            <div class="form-group">
              <label for="rol">Selecciona tu rol</label>
              <select id="rol" name="rol" class="form-select-custom" required>
                <option value="" disabled selected>¿Qué buscas hacer?</option>
                <option value="cliente">Quiero contratar (Cliente)</option>
                <option value="proveedor">Quiero trabajar (Proveedor)</option>
              </select>
            </div>

            <div class="row g-2">
              <div class="col-md-6 form-group">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="clave" placeholder="Crea una clave" required>
              </div>
              <div class="col-md-6 form-group">
                <label for="confirmar">Confirmar</label>
                <input type="password" id="confirmar" name="confirmar" placeholder="Repite tu clave" required>
              </div>
            </div>

            <div id="feedback-confirmar" class="invalid-feedback" style="display: block; color: #dc3545; font-size: 0.8rem; margin-bottom: 5px;"></div>

            <button type="button" class="btn-login btn-next" data-next="paso-2">Siguiente</button>

            <div class="register-link">
              <p>¿Ya tienes cuenta? <a href="<?= BASE_URL ?>/login">Inicia sesión</a></p>
            </div>
          </div>

          <div class="wizard-step d-none" id="paso-2">
            <h2 class="titulo-paso">Datos personales</h2>
            <div class="row g-2">
              <div class="col-md-6 form-group">
                <label>Nombre(s)</label>
                <input type="text" id="nombres" name="nombres" placeholder="Tus nombres" required>
              </div>
              <div class="col-md-6 form-group">
                <label>Apellido(s)</label>
                <input type="text" id="apellidos" name="apellidos" placeholder="Tus apellidos" required>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Foto de Perfil (Opcional)</label>
              <input type="file" id="foto" name="foto" accept="image/*" class="form-control form-control-sm">
            </div>

            <div class="row g-2">
              <div class="col-md-6 form-group">
                <label>Teléfono</label>
                <input type="tel" id="telefono" name="telefono" placeholder="Número celular" required>
              </div>
              <div class="col-md-6 form-group">
                <label>Ubicación</label>
                <input type="text" id="ubicacion" name="ubicacion" placeholder="Ciudad / Barrio" required>
              </div>
            </div>

            <div class="d-flex gap-2 mt-2">
              <button type="button" class="btn-prev btn-outline-custom w-50" data-prev="paso-1">Atrás</button>
              <button type="button" class="btn-login btn-next w-50" data-next="paso-3">Siguiente</button>
            </div>
          </div>

          <div class="wizard-step d-none" id="paso-3">
            <h2 class="titulo-paso">Seguridad</h2>
            <p class="text-muted small mb-3">Verificación de identidad y documentos requeridos.</p>

            <div class="row g-2">
              <div class="col-md-12 form-group">
                <label class="d-flex justify-content-between">
                  <span>Cédula (PDF/Imagen) <span class="text-danger">*</span></span>
                  <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" title="Copia legible por ambas caras."></i>
                </label>
                <input type="file" name="doc-cedula" accept="image/*,.pdf" class="form-control form-control-sm">
              </div>

              <div class="col-md-6 form-group">
                <label class="d-flex justify-content-between">
                  <span>Antecedentes <span class="text-danger">*</span></span>
                  <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" title="Certificado vigente de Policía o Procuraduría."></i>
                </label>
                <input type="file" name="doc-antecedentes" accept=".pdf" class="form-control form-control-sm">
              </div>

              <div class="col-md-6 form-group">
                <label class="d-flex justify-content-between">
                  <span>Selfie Verificación <span class="text-danger">*</span></span>
                  <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" title="Foto sosteniendo su documento cerca del rostro."></i>
                </label>
                <input type="file" name="doc-foto" accept="image/*" class="form-control form-control-sm">
              </div>

              <div class="col-md-12 form-group">
                <label class="d-flex justify-content-between">
                  <span>Certificaciones (Opcional)</span>
                  <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" title="Diplomas o cursos que validen su habilidad."></i>
                </label>
                <input type="file" name="doc-certificado" accept="image/*,.pdf" class="form-control form-control-sm">
              </div>
            </div>

            <div class="d-flex gap-2 mt-auto">
              <button type="button" class="btn-prev btn-outline-custom" data-prev="paso-2">Atrás</button>
              <button type="button" class="btn-login btn-next" data-next="paso-4">Siguiente</button>
            </div>
          </div>

          <div class="wizard-step d-none" id="paso-4">
            <h2 class="titulo-paso">Habilidades</h2>
            <div class="form-group">
              <div class="input-group mb-2">
                <select id="select-categoria" class="form-select-custom">
                  <option value="" disabled selected>Busca tu oficio...</option>
                  <?php foreach ($categorias_bd as $cat): ?>
                    <option value="<?= $cat['nombre'] ?>"><?= $cat['nombre'] ?></option>
                  <?php endforeach; ?>
                  <option value="nueva" style="font-weight: bold; color: var(--primary-color);">+ Agregar otro oficio...</option>
                </select>
                <button type="button" class="btn btn-primary btn-sm ms-2" id="btn-add-categoria" style="border-radius: 10px;">+</button>
              </div>

              <div id="input-nueva-cat-container" class="d-none mb-2">
                <input type="text" id="input-nueva-categoria" class="form-control" placeholder="Escribe tu oficio (ej: Carpintero)">
              </div>
            </div>

            <div id="contenedor-tags" class="d-flex flex-wrap gap-1 mb-3" style="min-height: 40px;"></div>
            <input type="hidden" name="lista_categorias" id="lista_categorias">

            <div class="d-flex gap-2">
              <button type="button" class="btn-prev btn-outline-custom w-50" data-prev="paso-3">Atrás</button>
              <button type="button" class="btn-login btn-next w-50" data-next="paso-5">Siguiente</button>
            </div>
          </div>

          <div class="wizard-step d-none" id="paso-5">
            <h2 class="titulo-paso">Confirmación</h2>
            <div id="resumen-registro" class="p-2 bg-light rounded mb-3" style="font-size: 0.8rem; border-left: 3px solid var(--primary-color);">
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn-prev btn-outline-custom w-50" data-prev="paso-4">Atrás</button>
              <button id="btn-finalizar" type="submit" class="btn-login w-50">Finalizar</button>
            </div>
          </div>
        </form>
      </section>

      <section class="info-section">
        <div id="infoCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
          <div class="carousel-inner">
            <div class="carousel-item active" style="background-image: url('public/assets/auth/img/carrousel-login/1.jpg');">
              <div class="carousel-caption-content">
                <h2>¡Comienza ahora!</h2>
                <p>Soluciones a un solo clic.</p>
              </div>
            </div>
            <div class="carousel-item" style="background-image: url('public/assets/auth/img/carrousel-login/2.jpg');">
              <div class="carousel-caption-content">
                <h2>Expertos confiables</h2>
                <p>La mejor red de profesionales.</p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/auth/js/registro.js"></script>
</body>

</html>