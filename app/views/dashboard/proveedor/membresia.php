<?php
require_once BASE_PATH . '/app/helpers/session-proveedor.php';
require_once BASE_PATH . '/config/database.php';

$uid = (int)($_SESSION['user']['id'] ?? 0);

$historial_pagos = [];
$plan_actual = [
    'nombre'           => 'Básico',
    'precio'           => 0,
    'periodo'          => 'gratuito',
    'fecha_inicio'     => date('Y-m-d'),
    'fecha_vencimiento'=> date('Y-m-d', strtotime('+365 days')),
    'estado'           => 'activa',
    'beneficios'       => ['Perfil profesional básico', 'Publicar hasta 3 servicios', 'Acceso a solicitudes de clientes cercanos'],
];

try {
    $db  = new Conexion();
    $pdo = $db->getConexion();

    // Plan activo del proveedor
    $st = $pdo->prepare("
        SELECT pm.fecha_inicio, pm.fecha_fin, pm.estado,
               m.tipo AS nombre, m.costo AS precio, m.descripcion
        FROM proveedor_membresia pm
        INNER JOIN proveedores p ON pm.proveedor_id = p.id
        INNER JOIN membresias m  ON pm.membresia_id = m.id
        WHERE p.usuario_id = :uid AND pm.estado = 'activa'
        ORDER BY pm.fecha_inicio DESC
        LIMIT 1
    ");
    $st->execute([':uid' => $uid]);
    $planDB = $st->fetch(PDO::FETCH_ASSOC);

    if ($planDB) {
        $beneficiosRaw = trim((string)($planDB['descripcion'] ?? ''));
        $beneficios = $beneficiosRaw !== ''
            ? array_map('trim', explode(',', $beneficiosRaw))
            : ['Perfil profesional básico'];

        $plan_actual = [
            'nombre'           => $planDB['nombre'] ?? 'Básico',
            'precio'           => (float)($planDB['precio'] ?? 0),
            'periodo'          => (float)($planDB['precio'] ?? 0) > 0 ? 'mensual' : 'gratuito',
            'fecha_inicio'     => $planDB['fecha_inicio'] ?? date('Y-m-d'),
            'fecha_vencimiento'=> $planDB['fecha_fin']    ?? date('Y-m-d', strtotime('+30 days')),
            'estado'           => $planDB['estado']       ?? 'activa',
            'beneficios'       => $beneficios,
        ];
    }

    // Historial de membresías del proveedor
    $st = $pdo->prepare("
        SELECT pm.fecha_inicio AS fecha, m.tipo AS plan, m.costo AS monto, pm.estado
        FROM proveedor_membresia pm
        INNER JOIN proveedores p ON pm.proveedor_id = p.id
        INNER JOIN membresias m  ON pm.membresia_id = m.id
        WHERE p.usuario_id = :uid
        ORDER BY pm.fecha_inicio DESC
        LIMIT 10
    ");
    $st->execute([':uid' => $uid]);
    $historial_pagos = $st->fetchAll(PDO::FETCH_ASSOC);

    if (empty($historial_pagos)) {
        $historial_pagos = [['fecha' => date('Y-m-d'), 'plan' => $plan_actual['nombre'], 'monto' => 0, 'estado' => 'activa']];
    }
} catch (PDOException $e) {
    error_log('membresia.php: ' . $e->getMessage());
    $historial_pagos = [['fecha' => date('Y-m-d'), 'plan' => 'Básico', 'monto' => 0, 'estado' => 'activa']];
}

// Planes disponibles
$planes_disponibles = [
    'basico' => [
        'nombre' => 'Básico',
        'precio' => 0,
        'periodo' => 'gratuito',
        'icono' => 'bi-gem',
        'color' => 'secondary',
        'destacado' => false,
        'db_id' => 0,
        'beneficios' => [
            'Perfil profesional básico',
            'Publicar hasta 3 servicios',
            'Acceso a solicitudes de clientes cercanos',
            'Calificaciones visibles',
            'Chat limitado con clientes',
            'Soporte por correo electrónico'
        ]
    ],
    'crecimiento' => [
        'nombre' => 'Crecimiento',
        'precio' => 25000,
        'precio_anual' => 250000,
        'periodo' => 'mes',
        'icono' => 'bi-rocket',
        'color' => 'primary',
        'destacado' => true,
        'db_id' => 0,
        'db_id_anual' => 0,
        'beneficios' => [
            'Todo lo del Paquete Básico',
            'Publicar hasta 10 servicios',
            'Acceso a clientes sin límite de zona',
            'Chat directo y sin restricciones',
            'Perfil profesional optimizado',
            'Verificación y métricas básicas'
        ]
    ],
    'premium' => [
        'nombre' => 'Premium',
        'precio' => 49000,
        'precio_anual' => 490000,
        'periodo' => 'mes',
        'icono' => 'bi-stars',
        'color' => 'warning',
        'destacado' => false,
        'db_id' => 0,
        'db_id_anual' => 0,
        'beneficios' => [
            'Todo lo del Paquete Crecimiento',
            'Publicaciones ilimitadas',
            'Prioridad en resultados de búsqueda',
            'Sello de proveedor destacado',
            'Exposición preferencial en campañas',
            'Soporte prioritario'
        ]
    ]
];

// Vincular IDs reales de BD a los planes por palabras clave (sin iconv para compatibilidad Windows)
try {
    $stPlanes = $pdo->query("SELECT id, tipo, costo FROM membresias WHERE UPPER(estado) = 'ACTIVO' ORDER BY costo ASC");
    foreach ($stPlanes->fetchAll(PDO::FETCH_ASSOC) as $pDB) {
        $t = strtolower(str_replace(
            ['á','é','í','ó','ú','ü','ñ','Á','É','Í','Ó','Ú','Ü','Ñ'],
            ['a','e','i','o','u','u','n','a','e','i','o','u','u','n'],
            $pDB['tipo']
        ));
        if ((str_contains($t, 'premium') || str_contains($t, 'premum')) && str_contains($t, 'anual')) {
            $planes_disponibles['premium']['db_id_anual'] = (int)$pDB['id'];
        } elseif ((str_contains($t, 'premium') || str_contains($t, 'premum')) && str_contains($t, 'mensual')) {
            $planes_disponibles['premium']['db_id'] = (int)$pDB['id'];
        } elseif (str_contains($t, 'crecimiento') && str_contains($t, 'anual')) {
            $planes_disponibles['crecimiento']['db_id_anual'] = (int)$pDB['id'];
        } elseif (str_contains($t, 'crecimiento') && str_contains($t, 'mensual')) {
            $planes_disponibles['crecimiento']['db_id'] = (int)$pDB['id'];
        } elseif (str_contains($t, 'freemium') || str_contains($t, 'basico')) {
            $planes_disponibles['basico']['db_id'] = (int)$pDB['id'];
        }
    }
} catch (PDOException $e) {
    error_log('membresia.php planes: ' . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Mi Membresía</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Estilos Globales -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-proveedor.css">

    <!-- CSS Específico -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/membresia.css">
</head>

<body>
    <!-- Sidebar Proveedor -->
    <?php include_once __DIR__ . '/../../layouts/sidebar-proveedor.php'; ?>

    <main class="contenido">
        <!-- Header Proveedor -->
        <?php include_once __DIR__ . '/../../layouts/header-proveedor.php'; ?>

        <section id="titulo-principal" class="section-hero mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-1">Mi Membresía</h1>
                    <p class="text-muted mb-0">Gestiona tu plan, beneficios y mejora tu experiencia en la plataforma.</p>
                </div>
                <div class="col-md-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 justify-content-md-end">
                            <li class="breadcrumb-item">
                                <a href="<?= BASE_URL ?>/proveedor/dashboard"><i class="bi bi-house-door-fill"></i> Inicio</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Membresía</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </section>

        <!-- RECORDATORIO DE VENCIMIENTO -->
        <?php
        $diasRestantes = null;
        $fechaVence    = $plan_actual['fecha_vencimiento'] ?? null;
        if ($fechaVence && $plan_actual['precio'] > 0) {
            $diasRestantes = (int)max(0, ceil((strtotime($fechaVence) - time()) / 86400));
        }
        if ($diasRestantes !== null && $diasRestantes <= 7):
            $alertClass = $diasRestantes <= 2 ? 'alert-danger' : 'alert-warning';
        ?>
        <div class="alert <?= $alertClass ?> d-flex align-items-center gap-3 mb-4 rounded-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
            <div>
                <?php if ($diasRestantes === 0): ?>
                    <strong>Tu plan venció hoy.</strong> Renueva ahora para no perder tus beneficios.
                <?php elseif ($diasRestantes === 1): ?>
                    <strong>Tu plan vence mañana.</strong> Renuévalo antes de perder el acceso.
                <?php else: ?>
                    <strong>Tu plan vence en <?= $diasRestantes ?> días</strong> (<?= date('d/m/Y', strtotime($fechaVence)) ?>). Renuévalo para mantener tus beneficios.
                <?php endif; ?>
                <a href="#planes" class="alert-link ms-1">Ver planes →</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- PLAN ACTUAL -->
        <section class="row mb-4">
            <div class="col-12">
                <div class="card-plan-actual">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-3">
                                <div class="plan-icono <?= $plan_actual['nombre'] === 'Básico' ? 'bg-secondary' : 'bg-primary' ?>">
                                    <i class="bi bi-gem fs-1 text-white"></i>
                                </div>
                                <div>
                                    <span class="badge bg-success mb-2">Plan activo</span>
                                    <h2 class="fw-bold mb-1">Plan <?= htmlspecialchars($plan_actual['nombre']) ?></h2>
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        Vigencia: <?= date('d/m/Y', strtotime($plan_actual['fecha_inicio'])) ?>
                                        — <?= date('d/m/Y', strtotime($plan_actual['fecha_vencimiento'])) ?>
                                    </p>
                                    <?php if ($diasRestantes !== null): ?>
                                    <p class="mb-0 mt-1 <?= $diasRestantes <= 3 ? 'text-danger fw-bold' : 'text-muted' ?> small">
                                        <i class="bi bi-hourglass-split me-1"></i>
                                        <?= $diasRestantes === 0 ? 'Vencido' : $diasRestantes . ' días restantes' ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="plan-precio">
                                <?php if ($plan_actual['precio'] == 0): ?>
                                    <h3 class="fw-bold text-success">Gratuito</h3>
                                <?php else: ?>
                                    <h3 class="fw-bold text-primary">$<?= number_format($plan_actual['precio'], 0, ',', '.') ?>/mes</h3>
                                <?php endif; ?>
                                <a href="#planes" class="btn btn-outline-primary mt-2">
                                    <i class="bi bi-arrow-up-circle me-2"></i>Cambiar plan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- BENEFICIOS DEL PLAN ACTUAL -->
        <section class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-check-circle me-2 text-primary"></i>Beneficios incluidos en tu plan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php foreach ($plan_actual['beneficios'] as $beneficio): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <span><?= $beneficio ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PLANES DISPONIBLES -->
        <section class="row mb-4" id="planes">
            <div class="col-12 d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <h5 class="fw-bold mb-0">Planes disponibles para mejorar</h5>
                <div class="d-flex align-items-center gap-2 bg-light rounded-pill px-3 py-1">
                    <span class="small fw-semibold" id="lbl-mensual">Mensual</span>
                    <div class="form-check form-switch mb-0 mx-1">
                        <input class="form-check-input" type="checkbox" id="toggle-anual" role="switch">
                    </div>
                    <span class="small fw-semibold" id="lbl-anual">Anual <span class="badge bg-success ms-1">–17%</span></span>
                </div>
            </div>

            <?php foreach ($planes_disponibles as $key => $plan): ?>
                <?php if ($plan['nombre'] !== $plan_actual['nombre']): ?>
                    <div class="col-md-6">
                        <div class="card-plan <?= $plan['destacado'] ? 'destacado' : '' ?>">
                            <?php if ($plan['destacado']): ?>
                                <div class="plan-badge">MÁS ELEGIDO</div>
                            <?php endif; ?>

                            <div class="plan-header">
                                <i class="bi <?= $plan['icono'] ?> plan-icono"></i>
                                <h3>Plan <?= $plan['nombre'] ?></h3>
                                <div class="plan-precio-card"
                                    data-precio-mensual="<?= $plan['precio'] ?>"
                                    data-precio-anual="<?= $plan['precio_anual'] ?? $plan['precio'] * 12 ?>">
                                    <span class="precio precio-plan">$<?= number_format($plan['precio'], 0, ',', '.') ?></span>
                                    <span class="periodo periodo-plan">/mes</span>
                                </div>
                            </div>

                            <div class="plan-body">
                                <ul class="plan-beneficios">
                                    <?php foreach ($plan['beneficios'] as $beneficio): ?>
                                        <li>
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                            <?= $beneficio ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>

                                <?php if ($plan['precio'] <= 0): ?>
                                    <button class="btn btn-secondary w-100" disabled>
                                        <i class="bi bi-check-circle me-2"></i>Plan gratuito
                                    </button>
                                <?php elseif ($plan['db_id'] <= 0 && $plan['db_id_anual'] <= 0): ?>
                                    <button class="btn btn-outline-secondary w-100" disabled title="Plan no disponible">
                                        <i class="bi bi-x-circle me-2"></i>No disponible
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-<?= $plan['color'] ?> w-100 btn-pagar-mp"
                                        data-plan-id-mensual="<?= $plan['db_id'] ?>"
                                        data-plan-id-anual="<?= $plan['db_id_anual'] ?? 0 ?>"
                                        data-plan-id="<?= $plan['db_id'] ?>"
                                        data-plan="<?= htmlspecialchars($plan['nombre']) ?>"
                                        data-precio-mensual="<?= $plan['precio'] ?>"
                                        data-precio-anual="<?= $plan['precio_anual'] ?? ($plan['precio'] * 12) ?>"
                                        data-precio="<?= $plan['precio'] ?>">
                                        <i class="bi bi-credit-card me-2"></i>Pagar con MercadoPago
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </section>

        <!-- HISTORIAL DE PAGOS -->
        <section class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-clock-history me-2 text-primary"></i>Historial de pagos
                        </h6>
                        <a href="#" class="btn btn-link btn-sm text-primary">Ver todos</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Plan</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Comprobante</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial_pagos as $pago): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($pago['fecha'])) ?></td>
                                            <td>Plan <?= $pago['plan'] ?></td>
                                            <td>
                                                <?php if ($pago['monto'] == 0): ?>
                                                    <span class="text-success">Gratuito</span>
                                                <?php else: ?>
                                                    $<?= number_format($pago['monto'], 0, ',', '.') ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= in_array($pago['estado'], ['activo', 'activa']) ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= ucfirst($pago['estado']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($pago['monto'] > 0): ?>
                                                    <button class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-file-pdf"></i>
                                                    </button>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- MODAL CAMBIAR PLAN (informativo) -->
    <div class="modal fade modal-cliente" id="modalCambiarPlan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-arrow-up-circle me-2"></i>Cambiar de plan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted mb-3">Selecciona uno de los planes disponibles para ver sus beneficios y proceder al pago.</p>

                    <div class="list-group">
                        <?php foreach ($planes_disponibles as $key => $plan): ?>
                            <?php if ($plan['nombre'] !== $plan_actual['nombre']): ?>
                                <button type="button"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center btn-pagar-mp"
                                    data-plan-id="<?= $plan['db_id'] ?>"
                                    data-plan="<?= htmlspecialchars($plan['nombre']) ?>"
                                    data-precio="<?= $plan['precio'] ?>"
                                    data-bs-dismiss="modal">
                                    <div>
                                        <i class="bi <?= $plan['icono'] ?> me-2 text-<?= $plan['color'] ?>"></i>
                                        <strong>Plan <?= $plan['nombre'] ?></strong>
                                        <small class="text-muted d-block"><?= $plan['beneficios'][0] ?></small>
                                    </div>
                                    <span class="fw-bold text-primary">$<?= number_format($plan['precio'], 0, ',', '.') ?>/mes</span>
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>

    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/membresia.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";

        // Toggle mensual / anual
        const toggleAnual = document.getElementById('toggle-anual');
        if (toggleAnual) {
            toggleAnual.addEventListener('change', function () {
                const esAnual = this.checked;
                document.querySelectorAll('.btn-pagar-mp').forEach(btn => {
                    const idMensual  = btn.dataset.planIdMensual;
                    const idAnual    = btn.dataset.planIdAnual;
                    const pMensual   = Number(btn.dataset.precioMensual);
                    const pAnual     = Number(btn.dataset.precioAnual);
                    btn.dataset.planId = esAnual ? idAnual : idMensual;
                    btn.dataset.precio = esAnual ? pAnual  : pMensual;
                });
                // Actualizar precios mostrados en tarjetas
                document.querySelectorAll('[data-precio-mensual]').forEach(el => {
                    const pMensual = Number(el.dataset.precioMensual);
                    const pAnual   = Number(el.dataset.precioAnual);
                    el.dataset.precio = esAnual ? pAnual : pMensual;
                });
                // Actualizar texto de precio en las tarjetas
                document.querySelectorAll('.precio-plan').forEach(el => {
                    const btn = el.closest('.card-plan')?.querySelector('.btn-pagar-mp');
                    if (!btn) return;
                    const p = Number(btn.dataset.precio);
                    el.textContent = '$' + p.toLocaleString('es-CO');
                });
                document.querySelectorAll('.periodo-plan').forEach(el => {
                    el.textContent = esAnual ? '/año' : '/mes';
                });
            });
        }

        document.querySelectorAll('.btn-pagar-mp').forEach(btn => {
            btn.addEventListener('click', async function () {
                const planNombre = this.dataset.plan;
                const planId     = this.dataset.planId;
                const precio     = Number(this.dataset.precio);

                const confirm = await Swal.fire({
                    title: `Plan ${planNombre}`,
                    html: `<div class="mb-3"><i class="bi bi-credit-card-2-front fs-1 text-primary"></i></div>Vas a pagar <strong>$${precio.toLocaleString('es-CO')}/mes</strong> con MercadoPago.<br><small class="text-muted">Aceptas tarjetas, PSE, Nequi, DaviPlata y más.</small>`,
                    confirmButtonText: 'Ir a pagar',
                    confirmButtonColor: '#009ee3',
                    showCancelButton: true,
                    cancelButtonText: 'Cancelar',
                });

                if (!confirm.isConfirmed) return;

                const originalHtml = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Redirigiendo...';

                try {
                    const fd = new FormData();
                    fd.append('plan_id', planId);

                    const res  = await fetch(`${BASE_URL}/proveedor/membresia/pagar`, {
                        method: 'POST', body: fd
                    });
                    const json = await res.json();

                    if (!json.ok) throw new Error(json.error || 'Error al iniciar el pago.');

                    window.location.href = json.url;

                } catch (err) {
                    this.disabled = false;
                    this.innerHTML = originalHtml;
                    Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#0066ff' });
                }
            });
        });
    </script>
</body>

</html>