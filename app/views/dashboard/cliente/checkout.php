<?php
require_once BASE_PATH . '/app/helpers/session-cliente.php';
require_once BASE_PATH . '/config/database.php';

$uid        = (int)$_SESSION['user']['id'];
$contratoId = (int)($_GET['sc_id'] ?? 0);

if ($contratoId <= 0) {
    header('Location: ' . BASE_URL . '/cliente/servicios-contratados');
    exit;
}

$contrato = null;
$metodos  = [];

try {
    $db  = new Conexion();
    $pdo = $db->getConexion();

    // Datos del servicio a pagar
    $st = $pdo->prepare("
        SELECT sc.id,
               COALESCE(cot.titulo, sol.titulo, sv.nombre, 'Servicio contratado') AS titulo,
               COALESCE(cot.precio, pub_sol.precio, sv.precio, 0)                  AS precio_base,
               COALESCE(promo.porcentaje_descuento, 0)                             AS promo_descuento,
               CONCAT(pr.nombres, ' ', pr.apellidos)                               AS proveedor_nombre,
               sc.estado
        FROM servicios_contratados sc
        INNER JOIN clientes cl        ON sc.cliente_id      = cl.id
        INNER JOIN proveedores pr     ON sc.proveedor_id    = pr.id
        LEFT JOIN cotizaciones cot    ON sc.cotizacion_id   = cot.id
        LEFT JOIN solicitudes sol     ON sc.solicitud_id    = sol.id
        LEFT JOIN publicaciones pub_sol ON sol.publicacion_id = pub_sol.id
        LEFT JOIN servicios sv        ON sc.servicio_id     = sv.id
        LEFT JOIN promociones promo
            ON promo.publicacion_id = pub_sol.id
            AND promo.fecha_inicio <= CURDATE()
            AND promo.fecha_fin    >= CURDATE()
        WHERE sc.id = :id AND cl.usuario_id = :uid
        LIMIT 1
    ");
    $st->execute([':id' => $contratoId, ':uid' => $uid]);
    $contrato = $st->fetch(PDO::FETCH_ASSOC);

    if (!$contrato || !in_array($contrato['estado'], ['confirmado', 'en_proceso'], true)) {
        header('Location: ' . BASE_URL . '/cliente/servicios-contratados');
        exit;
    }

    // Verificar si ya fue pagado
    $stPaid = $pdo->prepare("SELECT id FROM pagos_servicios WHERE servicio_contratado_id = :id LIMIT 1");
    $stPaid->execute([':id' => $contratoId]);
    if ($stPaid->fetchColumn()) {
        header('Location: ' . BASE_URL . '/cliente/servicios-contratados');
        exit;
    }

    // Métodos de pago guardados
    try {
        $stM = $pdo->prepare("
            SELECT * FROM metodos_pago
            WHERE usuario_id = :uid
            ORDER BY predeterminado DESC, created_at ASC
        ");
        $stM->execute([':uid' => $uid]);
        $metodos = $stM->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { /* tabla aún no existe */ }

} catch (PDOException $e) {
    error_log('checkout.php: ' . $e->getMessage());
    header('Location: ' . BASE_URL . '/cliente/servicios-contratados');
    exit;
}

$precioBase  = (float)$contrato['precio_base'];
$descuento   = (int)$contrato['promo_descuento'];
$montoFinal  = $descuento > 0 ? round($precioBase * (1 - $descuento / 100)) : $precioBase;
$ahorro      = $precioBase - $montoFinal;

$iconosMetodo = [
    'tarjeta_credito' => ['icon' => 'bi-credit-card-fill',        'color' => 'text-primary'],
    'tarjeta_debito'  => ['icon' => 'bi-credit-card-2-back-fill', 'color' => 'text-success'],
    'pse'             => ['icon' => 'bi-bank2',                   'color' => 'text-info'],
    'efectivo'        => ['icon' => 'bi-cash-coin',               'color' => 'text-warning'],
    'mercadopago'     => ['icon' => 'bi-wallet2',                 'color' => 'text-primary'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Pagar servicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-cliente.css">
</head>
<body>
<?php
$currentPage = 'servicios-contratados';
include_once __DIR__ . '/../../layouts/sidebar-cliente.php';
?>
<main class="contenido">
    <?php include_once __DIR__ . '/../../layouts/header-cliente.php'; ?>

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/cliente/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= BASE_URL ?>/cliente/servicios-contratados">Mis servicios</a>
            </li>
            <li class="breadcrumb-item active">Pagar servicio</li>
        </ol>
    </nav>

    <div class="row g-4 justify-content-center">

        <!-- Resumen del servicio -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white fw-semibold">
                    <i class="bi bi-receipt me-2"></i>Resumen del pago
                </div>
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($contrato['titulo']) ?></h5>
                    <p class="text-muted small mb-4">
                        <i class="bi bi-person-badge me-1"></i>
                        Proveedor: <?= htmlspecialchars($contrato['proveedor_nombre']) ?>
                    </p>

                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Precio base</span>
                            <span>$<?= number_format($precioBase, 0, ',', '.') ?></span>
                        </li>
                        <?php if ($descuento > 0): ?>
                        <li class="list-group-item d-flex justify-content-between px-0 text-success">
                            <span><i class="bi bi-tag-fill me-1"></i>Descuento (<?= $descuento ?>%)</span>
                            <span>- $<?= number_format($ahorro, 0, ',', '.') ?></span>
                        </li>
                        <?php endif; ?>
                        <li class="list-group-item d-flex justify-content-between px-0 fw-bold fs-5">
                            <span>Total a pagar</span>
                            <span class="text-success">$<?= number_format($montoFinal, 0, ',', '.') ?> COP</span>
                        </li>
                    </ul>

                    <div class="alert alert-info small mb-0 py-2">
                        <i class="bi bi-shield-check me-1"></i>
                        El pago queda retenido en la plataforma y se libera al proveedor cuando completes el servicio.
                    </div>
                </div>
            </div>
        </div>

        <!-- Método de pago + botón -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold border-bottom">
                    <i class="bi bi-credit-card me-2 text-primary"></i>Método de pago
                </div>
                <div class="card-body p-4">

                    <?php if (!empty($metodos)): ?>
                        <p class="text-muted small mb-3">Selecciona el método con el que realizarás el pago:</p>
                        <div class="d-flex flex-column gap-2 mb-4" id="lista-metodos">
                            <?php foreach ($metodos as $idx => $m):
                                $info   = $iconosMetodo[$m['tipo']] ?? ['icon' => 'bi-credit-card', 'color' => 'text-secondary'];
                                $esPred = (bool)$m['predeterminado'];
                            ?>
                            <label class="metodo-opcion d-flex align-items-center gap-3 p-3 rounded border
                                          <?= $esPred ? 'border-primary bg-primary bg-opacity-10' : '' ?>"
                                   style="cursor:pointer;">
                                <input type="radio" name="metodo_pago_id" value="<?= $m['id'] ?>"
                                       <?= $esPred ? 'checked' : '' ?> class="form-check-input mt-0">
                                <i class="bi <?= $info['icon'] ?> fs-4 <?= $info['color'] ?>"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold small"><?= htmlspecialchars($m['alias']) ?></div>
                                    <?php if ($m['ultimos_digitos']): ?>
                                        <div class="text-muted" style="font-size:.75rem;">•••• <?= htmlspecialchars($m['ultimos_digitos']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php if ($esPred): ?>
                                    <span class="badge bg-primary">Predeterminado</span>
                                <?php endif; ?>
                            </label>
                            <?php endforeach; ?>

                            <!-- Opción: pagar con otro método -->
                            <label class="metodo-opcion d-flex align-items-center gap-3 p-3 rounded border"
                                   style="cursor:pointer;">
                                <input type="radio" name="metodo_pago_id" value="otro"
                                       <?= empty($metodos) ? 'checked' : '' ?> class="form-check-input mt-0">
                                <i class="bi bi-plus-circle fs-4 text-secondary"></i>
                                <div class="fw-semibold small text-muted">Otro método (MercadoPago)</div>
                            </label>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted mb-4">
                            <i class="bi bi-credit-card fs-2 d-block mb-2"></i>
                            <p class="small mb-2">No tienes métodos de pago guardados.</p>
                            <a href="<?= BASE_URL ?>/cliente/metodos-pago" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="bi bi-plus-circle me-1"></i>Agregar método
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="d-grid gap-2">
                        <button id="btn-pagar" class="btn btn-success btn-lg" data-contrato-id="<?= $contratoId ?>">
                            <span id="btn-pagar-text">
                                <i class="bi bi-lock-fill me-2"></i>Ir a pagar
                                — $<?= number_format($montoFinal, 0, ',', '.') ?> COP
                            </span>
                            <span id="btn-pagar-loading" class="d-none">
                                <span class="spinner-border spinner-border-sm me-2"></span>Procesando...
                            </span>
                        </button>
                        <a href="<?= BASE_URL ?>/cliente/servicios-contratados" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>

                    <p class="text-center text-muted small mt-3 mb-0">
                        <i class="bi bi-shield-lock me-1"></i>
                        Pago seguro procesado por <strong>MercadoPago</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
<script>
    // Resaltar opción seleccionada
    document.querySelectorAll('input[name="metodo_pago_id"]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.metodo-opcion').forEach(el => {
                el.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
            });
            radio.closest('.metodo-opcion').classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
        });
    });

    document.getElementById('btn-pagar').addEventListener('click', function () {
        const contratoId = this.dataset.contratoId;
        const btnText    = document.getElementById('btn-pagar-text');
        const btnLoading = document.getElementById('btn-pagar-loading');

        this.disabled = true;
        btnText.classList.add('d-none');
        btnLoading.classList.remove('d-none');

        const body = new FormData();
        body.append('contrato_id', contratoId);

        fetch('<?= BASE_URL ?>/cliente/pagar-servicio', {
            method: 'POST',
            body,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                window.location.href = data.url;
            } else {
                Swal.fire('Error', data.error || 'No se pudo iniciar el pago.', 'error');
                this.disabled = false;
                btnText.classList.remove('d-none');
                btnLoading.classList.add('d-none');
            }
        })
        .catch(() => {
            Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
            this.disabled = false;
            btnText.classList.remove('d-none');
            btnLoading.classList.add('d-none');
        });
    });
</script>
</body>
</html>
