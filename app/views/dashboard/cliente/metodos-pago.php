<?php
require_once BASE_PATH . '/app/helpers/session-cliente.php';
require_once BASE_PATH . '/config/database.php';

$uid     = (int)$_SESSION['user']['id'];
$metodos = [];

try {
    $db  = new Conexion();
    $pdo = $db->getConexion();
    $pdo->exec("CREATE TABLE IF NOT EXISTS metodos_pago (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id      INT          NOT NULL,
        tipo            VARCHAR(30)  NOT NULL,
        alias           VARCHAR(100) NOT NULL,
        ultimos_digitos VARCHAR(4)   NULL,
        predeterminado  TINYINT(1)   NOT NULL DEFAULT 0,
        created_at      DATETIME     DEFAULT NOW()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    // Columnas de tokenización (se agregan si no existen)
    foreach ([
        "ADD COLUMN mp_customer_id VARCHAR(60) NULL",
        "ADD COLUMN mp_card_id     VARCHAR(60) NULL",
        "ADD COLUMN marca          VARCHAR(30) NULL",
        "ADD COLUMN expiry_month   VARCHAR(2)  NULL",
        "ADD COLUMN expiry_year    VARCHAR(4)  NULL",
    ] as $col) {
        try { $pdo->exec("ALTER TABLE metodos_pago $col"); } catch (PDOException $e) {}
    }

    $st = $pdo->prepare("SELECT * FROM metodos_pago WHERE usuario_id = :uid ORDER BY predeterminado DESC, created_at ASC");
    $st->execute([':uid' => $uid]);
    $metodos = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('metodos-pago.php: ' . $e->getMessage());
}

$iconos = [
    'tarjeta_credito' => ['icon' => 'bi-credit-card-fill',        'color' => 'text-primary',  'label' => 'Tarjeta de crédito'],
    'tarjeta_debito'  => ['icon' => 'bi-credit-card-2-back-fill', 'color' => 'text-success',  'label' => 'Tarjeta de débito'],
    'pse'             => ['icon' => 'bi-bank2',                   'color' => 'text-info',     'label' => 'PSE'],
    'efectivo'        => ['icon' => 'bi-cash-coin',               'color' => 'text-warning',  'label' => 'Efectivo'],
    'mercadopago'     => ['icon' => 'bi-wallet2',                 'color' => 'text-primary',  'label' => 'MercadoPago'],
];

$marcaIconos = [
    'visa'       => 'bi-credit-card-fill text-primary',
    'master'     => 'bi-credit-card-fill text-danger',
    'mastercard' => 'bi-credit-card-fill text-danger',
    'amex'       => 'bi-credit-card-fill text-info',
    'diners'     => 'bi-credit-card-fill text-secondary',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Métodos de pago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-cliente.css">
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
                <h1 class="mb-1">Métodos de pago</h1>
                <p class="text-muted mb-0">Gestiona los métodos de pago que usas para contratar servicios.</p>
            </div>
            <div class="col-md-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 justify-content-md-end">
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>/cliente/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                        </li>
                        <li class="breadcrumb-item active">Métodos de pago</li>
                    </ol>
                </nav>
            </div>
        </div>
    </section>

    <!-- Botones agregar -->
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <a href="<?= BASE_URL ?>/cliente/metodos-pago/agregar-tarjeta" class="btn btn-primary">
            <i class="bi bi-credit-card me-2"></i>Agregar tarjeta
        </a>
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalOtroMetodo">
            <i class="bi bi-plus-circle me-2"></i>Otro método
        </button>
    </div>

    <!-- Lista de métodos -->
    <?php if (empty($metodos)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-credit-card fs-1 d-block mb-3"></i>
                <h5>Sin métodos de pago registrados</h5>
                <p class="mb-3">Agrega una tarjeta o un método alternativo para agilizar tus pagos.</p>
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a href="<?= BASE_URL ?>/cliente/metodos-pago/agregar-tarjeta" class="btn btn-primary">
                        <i class="bi bi-credit-card me-2"></i>Agregar tarjeta
                    </a>
                    <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalOtroMetodo">
                        <i class="bi bi-plus-circle me-2"></i>Otro método
                    </button>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($metodos as $m):
                $info      = $iconos[$m['tipo']] ?? ['icon' => 'bi-credit-card', 'color' => 'text-secondary', 'label' => $m['tipo']];
                $esToken   = !empty($m['mp_card_id']);
                $marcaKey  = strtolower($m['marca'] ?? '');
                $iconClass = $marcaIconos[$marcaKey] ?? $info['icon'] . ' ' . $info['color'];
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100 <?= $m['predeterminado'] ? 'border-primary border-2' : '' ?>">
                    <?php if ($m['predeterminado']): ?>
                        <div class="card-header bg-primary text-white py-1 px-3 small fw-semibold">
                            <i class="bi bi-star-fill me-1"></i>Predeterminado
                        </div>
                    <?php endif; ?>
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                             style="width:56px;height:56px;flex-shrink:0;">
                            <i class="bi <?= $iconClass ?> fs-3"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate"><?= htmlspecialchars($m['alias']) ?></div>
                            <div class="text-muted small"><?= $info['label'] ?></div>
                            <?php if ($esToken): ?>
                                <div class="text-muted small">
                                    •••• <?= htmlspecialchars($m['ultimos_digitos']) ?>
                                    <?php if ($m['expiry_month'] && $m['expiry_year']): ?>
                                        &nbsp;·&nbsp; vence
                                        <?= htmlspecialchars($m['expiry_month']) ?>/<?= htmlspecialchars(substr($m['expiry_year'], -2)) ?>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($m['ultimos_digitos']): ?>
                                <div class="text-muted small">•••• <?= htmlspecialchars($m['ultimos_digitos']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                        <div class="d-flex gap-2">
                            <?php if (!$m['predeterminado']): ?>
                                <form method="POST" action="<?= BASE_URL ?>/cliente/metodos-pago/guardar" class="flex-fill">
                                    <input type="hidden" name="accion"    value="predeterminado_metodo_pago">
                                    <input type="hidden" name="metodo_id" value="<?= $m['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                        <i class="bi bi-star me-1"></i>Predeterminar
                                    </button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" action="<?= BASE_URL ?>/cliente/metodos-pago/guardar"
                                  onsubmit="return confirm('¿Eliminar este método de pago?')"
                                  class="<?= $m['predeterminado'] ? 'flex-fill' : '' ?>">
                                <input type="hidden" name="accion"    value="eliminar_metodo_pago">
                                <input type="hidden" name="metodo_id" value="<?= $m['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<!-- Modal otros métodos (PSE, efectivo, MP wallet) -->
<div class="modal fade" id="modalOtroMetodo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Agregar otro método</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/cliente/metodos-pago/guardar">
                <input type="hidden" name="accion" value="agregar_metodo_pago">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Tipo <span class="text-danger">*</span></label>
                            <select name="tipo" class="form-select" required>
                                <option value="">Selecciona un tipo...</option>
                                <option value="pse">PSE</option>
                                <option value="efectivo">Efectivo (Efecty / Baloto)</option>
                                <option value="mercadopago">MercadoPago</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alias <span class="text-danger">*</span></label>
                            <input type="text" name="alias" class="form-control" maxlength="100"
                                   placeholder="Ej: Mi cuenta Nequi" required>
                            <small class="text-muted">Un nombre para identificar este método fácilmente.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
<script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
</body>
</html>
