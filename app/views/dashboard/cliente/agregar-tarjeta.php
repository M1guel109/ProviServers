<?php
require_once BASE_PATH . '/app/helpers/session-cliente.php';
require_once BASE_PATH . '/config/mercadopago.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Agregar tarjeta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-cliente.css">
    <style>
        /* Contenedores de los iframes de MP */
        .mp-field-container {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            min-height: 42px;
            background: #fff;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }
        .mp-field-container:focus-within {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
        }
    </style>
</head>
<body>
<?php
$currentPage = 'metodos-pago';
include_once __DIR__ . '/../../layouts/sidebar-cliente.php';
?>
<main class="contenido">
    <?php include_once __DIR__ . '/../../layouts/header-cliente.php'; ?>

    <section id="titulo-principal" class="section-hero mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-1">Agregar tarjeta</h1>
                <p class="text-muted mb-0">Tus datos son tokenizados por MercadoPago — nunca almacenamos el número real.</p>
            </div>
            <div class="col-md-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 justify-content-md-end">
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>/cliente/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>/cliente/metodos-pago">Métodos de pago</a>
                        </li>
                        <li class="breadcrumb-item active">Agregar tarjeta</li>
                    </ol>
                </nav>
            </div>
        </div>
    </section>

    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-credit-card me-2"></i>Datos de la tarjeta
                </div>
                <div class="card-body p-4">
                    <form id="form-checkout-card">
                        <!-- Campos ocultos requeridos por MP -->
                        <select id="form-checkout__issuer"          class="d-none"></select>
                        <select id="form-checkout__installments"    class="d-none"></select>
                        <input  id="form-checkout__cardholderEmail" type="hidden"
                                value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Número de tarjeta</label>
                            <div id="form-checkout__cardNumber" class="mp-field-container"></div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Vencimiento</label>
                                <div id="form-checkout__expirationDate" class="mp-field-container"></div>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Código de seguridad</label>
                                <div id="form-checkout__securityCode" class="mp-field-container"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre del titular</label>
                            <input type="text" id="form-checkout__cardholderName" class="form-control"
                                   placeholder="Como aparece en la tarjeta" required>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-5">
                                <label class="form-label fw-semibold">Tipo de documento</label>
                                <select id="form-checkout__identificationType" class="form-select"></select>
                            </div>
                            <div class="col-7">
                                <label class="form-label fw-semibold">Número de documento</label>
                                <input type="text" id="form-checkout__identificationNumber" class="form-control"
                                       placeholder="Número de documento" required>
                            </div>
                        </div>

                        <div id="mp-error" class="alert alert-danger d-none mb-3" role="alert"></div>

                        <div class="d-flex gap-2">
                            <a href="<?= BASE_URL ?>/cliente/metodos-pago" class="btn btn-secondary flex-fill">
                                Cancelar
                            </a>
                            <button type="submit" id="btn-guardar-tarjeta" class="btn btn-primary flex-fill">
                                <span id="btn-text"><i class="bi bi-shield-lock me-2"></i>Guardar tarjeta</span>
                                <span id="btn-loading" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-2"></span>Procesando...
                                </span>
                            </button>
                        </div>
                    </form>

                    <!-- Formulario real que se envía al backend después de tokenizar -->
                    <form id="form-post-token" method="POST" action="<?= BASE_URL ?>/cliente/metodos-pago/guardar" class="d-none">
                        <input type="hidden" name="accion"            value="tokenizar_tarjeta">
                        <input type="hidden" name="card_token"        id="input-card-token">
                        <input type="hidden" name="payment_method_id" id="input-payment-method-id">
                        <input type="hidden" name="issuer_id"         id="input-issuer-id">
                    </form>
                </div>
            </div>

            <p class="text-center text-muted small mt-3">
                <i class="bi bi-shield-check text-success me-1"></i>
                Procesado de forma segura por
                <strong>MercadoPago</strong>. Nunca almacenamos los datos de tu tarjeta.
            </p>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
<script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
const mp = new MercadoPago('<?= htmlspecialchars(MP_PUBLIC_KEY) ?>', { locale: 'es-CO' });

const cardForm = mp.cardForm({
    amount: '1',
    iframe: true,
    form: {
        id: 'form-checkout-card',
        cardholderName:         { id: 'form-checkout__cardholderName' },
        cardholderEmail:        { id: 'form-checkout__cardholderEmail' },
        cardNumber:             { id: 'form-checkout__cardNumber',      placeholder: '0000 0000 0000 0000' },
        expirationDate:         { id: 'form-checkout__expirationDate',  placeholder: 'MM/AA' },
        securityCode:           { id: 'form-checkout__securityCode',    placeholder: '123' },
        installments:           { id: 'form-checkout__installments' },
        identificationType:     { id: 'form-checkout__identificationType' },
        identificationNumber:   { id: 'form-checkout__identificationNumber' },
        issuer:                 { id: 'form-checkout__issuer' },
    },
    callbacks: {
        onFormMounted: error => {
            if (error) console.warn('MP cardForm mount error:', error);
        },
        onSubmit: event => {
            event.preventDefault();
            const { token, issuerId, paymentMethodId } = cardForm.getCardFormData();

            const errEl = document.getElementById('mp-error');
            if (!token) {
                errEl.textContent = 'No se pudo generar el token. Verifica los datos de la tarjeta.';
                errEl.classList.remove('d-none');
                return;
            }
            errEl.classList.add('d-none');

            document.getElementById('input-card-token').value        = token;
            document.getElementById('input-payment-method-id').value = paymentMethodId || '';
            document.getElementById('input-issuer-id').value         = issuerId || '';
            document.getElementById('form-post-token').submit();
        },
        onFetchingResource: (_resource, isLoading) => {
            const btn  = document.getElementById('btn-guardar-tarjeta');
            const txt  = document.getElementById('btn-text');
            const spin = document.getElementById('btn-loading');
            btn.disabled = isLoading;
            txt.classList.toggle('d-none', isLoading);
            spin.classList.toggle('d-none', !isLoading);
        },
        onError: errors => {
            console.error('MP cardForm errors:', errors);
        },
    },
});
</script>
</body>
</html>
