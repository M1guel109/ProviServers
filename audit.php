<?php
// ============================================================
// SEGURIDAD — Solo localhost o con clave
// ============================================================
if ($_SERVER['HTTP_HOST'] !== 'localhost' && !isset($_GET['audit_key'])) {
    die('Acceso denegado');
}

// ============================================================
// BOOTSTRAP MÍNIMO — cargar config sin ejecutar el router
// ============================================================
define('BASE_PATH', __DIR__);
if (file_exists(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
}

// ============================================================
// HELPERS DE REPORTE
// ============================================================
$results = [];

function ok(string $section, string $label, string $detail = ''): void {
    global $results;
    $results[] = ['status' => 'ok', 'section' => $section, 'label' => $label, 'detail' => $detail];
}

function err(string $section, string $label, string $detail = ''): void {
    global $results;
    $results[] = ['status' => 'error', 'section' => $section, 'label' => $label, 'detail' => $detail];
}

function warn(string $section, string $label, string $detail = ''): void {
    global $results;
    $results[] = ['status' => 'warn', 'section' => $section, 'label' => $label, 'detail' => $detail];
}

// ============================================================
// 1. PHP — Versión y configuración
// ============================================================
$phpVersion = PHP_VERSION;
$minVersion = '8.0.0';
if (version_compare($phpVersion, $minVersion, '>=')) {
    ok('PHP', "Versión PHP $phpVersion", "Mínimo requerido: $minVersion");
} else {
    err('PHP', "Versión PHP $phpVersion demasiado baja", "Se requiere >= $minVersion");
}

$displayErrors = ini_get('display_errors');
if ($displayErrors && $displayErrors !== '0') {
    warn('PHP', 'display_errors está ACTIVO', 'Debe estar Off en producción');
} else {
    ok('PHP', 'display_errors desactivado', 'Correcto para producción');
}

$extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'curl', 'openssl', 'fileinfo'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        ok('PHP', "Extensión $ext cargada");
    } else {
        err('PHP', "Extensión $ext NO encontrada", 'Requerida por el proyecto');
    }
}

// ============================================================
// 2. CONSTANTES — BASE_URL y BASE_PATH
// ============================================================
if (defined('BASE_PATH')) {
    ok('Constantes', 'BASE_PATH definido', BASE_PATH);
} else {
    err('Constantes', 'BASE_PATH NO definido', 'config/config.php no cargó correctamente');
}

if (defined('BASE_URL')) {
    ok('Constantes', 'BASE_URL definido', BASE_URL);
    if (str_contains(BASE_URL, 'localhost') || str_contains(BASE_URL, '127.0.0.1')) {
        warn('Constantes', 'BASE_URL apunta a localhost', 'Verifica que config.php detecte el entorno correcto');
    }
} else {
    err('Constantes', 'BASE_URL NO definido', 'config/config.php no cargó o falló la detección');
}

// ============================================================
// 3. CONEXIÓN A BD
// ============================================================
if (file_exists(__DIR__ . '/config/database.php')) {
    try {
        require_once __DIR__ . '/config/database.php';
        $db = new Conexion();
        $pdo = $db->getConexion();
        $pdo->query('SELECT 1');
        ok('Base de datos', 'Conexión exitosa', 'PDO conectado correctamente');

        // Tablas críticas
        $tablasCriticas = [
            'usuarios', 'clientes', 'proveedores', 'admins',
            'servicios', 'solicitudes', 'cotizaciones',
            'servicios_contratados', 'publicaciones',
            'categorias', 'membresias', 'proveedor_membresia',
            'pagos_servicios', 'mensajes_contacto',
            'documentos_proveedor', 'usuario_estados',
        ];
        $stmt = $pdo->query("SHOW TABLES");
        $tablesInDb = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tablasCriticas as $tabla) {
            if (in_array($tabla, $tablesInDb, true)) {
                ok('Tablas BD', "Tabla `$tabla` existe");
            } else {
                err('Tablas BD', "Tabla `$tabla` NO encontrada", 'Puede requerir migración SQL');
            }
        }
    } catch (Exception $e) {
        err('Base de datos', 'Error de conexión', $e->getMessage());
    }
} else {
    err('Base de datos', 'config/database.php NO encontrado', 'El archivo no existe en disco');
}

// ============================================================
// 4. ARCHIVOS CRÍTICOS
// ============================================================
$archivosCriticos = [
    'index.php',
    'config/config.php',
    'config/database.php',
    '.htaccess',
];

// Agregar dinámicamente todos los controllers, models y helpers
$directorios = [
    'app/controllers',
    'app/models',
    'app/helpers',
];

foreach ($directorios as $dir) {
    $fullDir = __DIR__ . '/' . $dir;
    if (is_dir($fullDir)) {
        $files = glob($fullDir . '/*.php');
        foreach ($files as $f) {
            $archivosCriticos[] = $dir . '/' . basename($f);
        }
    }
}

foreach ($archivosCriticos as $archivo) {
    $ruta = __DIR__ . '/' . $archivo;
    if (file_exists($ruta)) {
        ok('Archivos críticos', $archivo);
    } else {
        err('Archivos críticos', "$archivo NO encontrado", 'Archivo faltante en el servidor');
    }
}

// ============================================================
// 5. ASSETS CRÍTICOS
// ============================================================
$assets = [
    'public/assets/website/img/fondo-landing.png',
    'public/assets/website/img/fondo-landing-2.jpg',
    'public/assets/website/img/fondo-landing-3.jpg',
    'public/assets/website/img/background.png',
    'public/assets/img/logos/favicon.png',
    'public/assets/img/logos/logo-principal.png',
    'public/assets/estilosGenerales/style.css',
    'public/assets/website/css/landing.css',
    'public/assets/website/js/landing.js',
    'public/assets/dashboard/css/dashboard.css',
    'public/assets/dashboard/css/dashboard-proveedor.css',
    'public/assets/dashboard/css/dashboard-cliente.css',
];

foreach ($assets as $asset) {
    $ruta = __DIR__ . '/' . $asset;
    if (file_exists($ruta)) {
        $size = round(filesize($ruta) / 1024, 1);
        ok('Assets', $asset, "{$size} KB");
    } else {
        err('Assets', "$asset NO encontrado", 'Falta en public/ — sube el archivo manualmente');
    }
}

// ============================================================
// 6. RUTAS HTTP — Verificar códigos de respuesta
// ============================================================
if (defined('BASE_URL') && function_exists('curl_init')) {
    $rutas = [
        BASE_URL . '/'            => 'Landing page',
        BASE_URL . '/iniciar-sesion' => 'Login',
        BASE_URL . '/registro'    => 'Registro',
        BASE_URL . '/login'       => 'Login alias',
    ];

    foreach ($rutas as $url => $nombre) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'ProviServers-Audit/1.0',
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            warn('Rutas HTTP', "$nombre ($url)", "cURL error: $error");
        } elseif ($code === 200) {
            ok('Rutas HTTP', "$nombre → HTTP $code", $url);
        } elseif (in_array($code, [301, 302], true)) {
            warn('Rutas HTTP', "$nombre → HTTP $code (redirect)", $url);
        } else {
            err('Rutas HTTP', "$nombre → HTTP $code", $url);
        }
    }
} else {
    warn('Rutas HTTP', 'Verificación omitida', defined('BASE_URL') ? 'cURL no disponible' : 'BASE_URL no definida');
}

// ============================================================
// 7. PERMISOS DE ESCRITURA
// ============================================================
$directoriosEscritura = [
    'public/uploads/usuarios',
    'public/uploads/documentos',
];

foreach ($directoriosEscritura as $dir) {
    $ruta = __DIR__ . '/' . $dir;
    if (!is_dir($ruta)) {
        err('Permisos', "$dir NO existe", 'Crea el directorio con chmod 755');
    } elseif (is_writable($ruta)) {
        ok('Permisos', "$dir es escribible");
    } else {
        err('Permisos', "$dir NO es escribible", 'Ejecuta: chmod -R 755 ' . $dir);
    }
}

// ============================================================
// RESUMEN
// ============================================================
$totalOk   = count(array_filter($results, fn($r) => $r['status'] === 'ok'));
$totalErr  = count(array_filter($results, fn($r) => $r['status'] === 'error'));
$totalWarn = count(array_filter($results, fn($r) => $r['status'] === 'warn'));

// Agrupar por sección
$sections = [];
foreach ($results as $r) {
    $sections[$r['section']][] = $r;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ProviServers — Audit Report</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', system-ui, sans-serif; background: #0f172a; color: #e2e8f0; min-height: 100vh; padding: 32px 16px; }
  .wrap { max-width: 960px; margin: 0 auto; }
  h1 { font-size: 1.6rem; font-weight: 700; color: #fff; margin-bottom: 4px; }
  .subtitle { font-size: 0.85rem; color: #64748b; margin-bottom: 28px; }
  /* Summary cards */
  .summary { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; margin-bottom: 32px; }
  .card { border-radius: 12px; padding: 20px; text-align: center; }
  .card-ok   { background: #052e16; border: 1px solid #16a34a; }
  .card-err  { background: #2d0a0a; border: 1px solid #dc2626; }
  .card-warn { background: #2d1e00; border: 1px solid #d97706; }
  .card .num { font-size: 2.4rem; font-weight: 800; line-height: 1; margin-bottom: 4px; }
  .card-ok   .num { color: #4ade80; }
  .card-err  .num { color: #f87171; }
  .card-warn .num { color: #fbbf24; }
  .card .lbl { font-size: 0.78rem; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; }
  /* Sections */
  .section { margin-bottom: 24px; }
  .section-title { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: #475569; font-weight: 600; padding: 0 0 8px; border-bottom: 1px solid #1e293b; margin-bottom: 8px; }
  /* Rows */
  .row { display: flex; align-items: flex-start; gap: 10px; padding: 7px 10px; border-radius: 8px; margin-bottom: 3px; font-size: 0.82rem; }
  .row:hover { background: #1e293b; }
  .dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
  .dot-ok   { background: #4ade80; box-shadow: 0 0 6px #4ade8088; }
  .dot-error { background: #f87171; box-shadow: 0 0 6px #f8717188; }
  .dot-warn { background: #fbbf24; box-shadow: 0 0 6px #fbbf2488; }
  .row-label { color: #e2e8f0; flex: 1; min-width: 0; word-break: break-all; }
  .row-detail { color: #64748b; font-size: 0.75rem; margin-top: 1px; word-break: break-all; }
  .badge { display: inline-block; padding: 1px 8px; border-radius: 20px; font-size: 0.68rem; font-weight: 700; flex-shrink: 0; }
  .badge-ok   { background: #052e16; color: #4ade80; border: 1px solid #16a34a; }
  .badge-error{ background: #2d0a0a; color: #f87171; border: 1px solid #dc2626; }
  .badge-warn { background: #2d1e00; color: #fbbf24; border: 1px solid #d97706; }
  /* Footer */
  .footer { margin-top: 32px; text-align: center; font-size: 0.75rem; color: #334155; }
  @media (max-width: 500px) { .summary { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<div class="wrap">
  <h1>🔍 ProviServers — Audit Report</h1>
  <p class="subtitle">
    Entorno: <strong><?= htmlspecialchars($_SERVER['HTTP_HOST']) ?></strong> &nbsp;|&nbsp;
    PHP <?= PHP_VERSION ?> &nbsp;|&nbsp;
    <?= date('Y-m-d H:i:s') ?>
  </p>

  <div class="summary">
    <div class="card card-ok">
      <div class="num"><?= $totalOk ?></div>
      <div class="lbl">✔ OK</div>
    </div>
    <div class="card card-err">
      <div class="num"><?= $totalErr ?></div>
      <div class="lbl">✖ Errores</div>
    </div>
    <div class="card card-warn">
      <div class="num"><?= $totalWarn ?></div>
      <div class="lbl">⚠ Advertencias</div>
    </div>
  </div>

  <?php foreach ($sections as $section => $rows): ?>
  <div class="section">
    <div class="section-title"><?= htmlspecialchars($section) ?> (<?= count($rows) ?>)</div>
    <?php foreach ($rows as $r):
      $dotClass   = $r['status'] === 'ok' ? 'dot-ok' : ($r['status'] === 'error' ? 'dot-error' : 'dot-warn');
      $badgeClass = $r['status'] === 'ok' ? 'badge-ok' : ($r['status'] === 'error' ? 'badge-error' : 'badge-warn');
      $badgeText  = $r['status'] === 'ok' ? 'OK' : ($r['status'] === 'error' ? 'ERROR' : 'WARN');
    ?>
    <div class="row">
      <div class="dot <?= $dotClass ?>"></div>
      <div style="flex:1;min-width:0;">
        <div class="row-label"><?= htmlspecialchars($r['label']) ?></div>
        <?php if ($r['detail']): ?>
          <div class="row-detail"><?= htmlspecialchars($r['detail']) ?></div>
        <?php endif; ?>
      </div>
      <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>

  <?php if ($totalErr === 0): ?>
  <div style="background:#052e16;border:1px solid #16a34a;border-radius:12px;padding:20px;text-align:center;margin-top:8px;">
    <div style="font-size:1.5rem;margin-bottom:6px;">🎉</div>
    <div style="color:#4ade80;font-weight:700;font-size:1rem;">Todo correcto — el proyecto está listo para producción</div>
  </div>
  <?php else: ?>
  <div style="background:#2d0a0a;border:1px solid #dc2626;border-radius:12px;padding:20px;text-align:center;margin-top:8px;">
    <div style="font-size:1.5rem;margin-bottom:6px;">🚨</div>
    <div style="color:#f87171;font-weight:700;font-size:1rem;"><?= $totalErr ?> error<?= $totalErr !== 1 ? 'es' : '' ?> encontrado<?= $totalErr !== 1 ? 's' : '' ?> — revisa los elementos en rojo</div>
  </div>
  <?php endif; ?>

  <div class="footer">
    ProviServers Audit Tool — elimina este archivo después de usarlo en producción
  </div>
</div>
</body>
</html>
