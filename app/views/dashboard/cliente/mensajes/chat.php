<?php
$convId = (int)($convId ?? 0);
$uid    = (int)($_SESSION['user']['id'] ?? 0);
$last   = '1970-01-01 00:00:00';
if (!empty($mensajes)) {
    $ult = end($mensajes);
    if (!empty($ult['fecha_hora'])) $last = $ult['fecha_hora'];
}

function fmtHoraCli(?string $fh): string {
    if (!$fh) return '';
    $ts = strtotime($fh);
    if ($ts === false) return $fh;
    return date('H:i', $ts) . (date('Y-m-d', $ts) !== date('Y-m-d') ? ' · ' . date('d/m', $ts) : '');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/assets/img/logos/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/dashboard-cliente.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashboard/css/mensajes.css">
</head>
<body>
<?php
$currentPage = 'mensajes';
include_once __DIR__ . '/../../../layouts/sidebar-cliente.php';
?>
<main class="contenido">
    <?php include_once __DIR__ . '/../../../layouts/header-cliente.php'; ?>

    <div class="container-fluid px-4 py-3">

        <div class="chat-wrapper">

            <!-- Barra superior -->
            <div class="chat-topbar">
                <a href="<?= BASE_URL ?>/cliente/mensajes"
                   class="btn btn-sm btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div class="chat-topbar-avatar">
                    <i class="bi bi-person-workspace"></i>
                </div>
                <div class="chat-topbar-info">
                    <p class="chat-topbar-nombre">Proveedor</p>
                    <p class="chat-topbar-tema"><?= htmlspecialchars($tema ?? 'Conversación') ?></p>
                </div>
            </div>

            <!-- Aviso anti-contacto -->
            <div class="chat-aviso">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Por políticas de ProviServers <strong>no está permitido compartir teléfonos, emails ni redes sociales</strong>. Todos los tratos deben cerrarse dentro de la plataforma.</span>
            </div>

            <!-- Mensajes -->
            <div class="chat-box" id="chatBox">
                <?php if (empty($mensajes)): ?>
                    <div class="chat-empty">
                        <i class="bi bi-chat-dots" style="font-size:36px;display:block;margin-bottom:8px;"></i>
                        Sé el primero en escribir.
                    </div>
                <?php else: ?>
                    <?php foreach ($mensajes as $m): ?>
                        <?php $isMe = ((int)$m['emisor_id'] === $uid); ?>
                        <div class="msg-row <?= $isMe ? 'mine' : 'theirs' ?>">
                            <div class="bubble">
                                <?= nl2br(htmlspecialchars($m['contenido'] ?? '')) ?>
                                <div class="bubble-time"><?= fmtHoraCli($m['fecha_hora'] ?? null) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Área de entrada -->
            <div class="chat-input-area">
                <textarea id="msgInput"
                          rows="1"
                          placeholder="Escribe un mensaje..."
                          autocomplete="off"
                          maxlength="2000"></textarea>
                <button class="btn-send" id="btnSend" type="button">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>

        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/public/assets/dashboard/js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= BASE_URL ?>/public/assets/dashboard/js/dashboard-cliente.js"></script>

<script>
const BASE     = "<?= BASE_URL ?>";
const convId   = <?= (int)$convId ?>;
const myId     = <?= (int)$uid ?>;
let   lastTime = "<?= addslashes($last) ?>";

const chatBox = document.getElementById('chatBox');
const input   = document.getElementById('msgInput');
const btnSend = document.getElementById('btnSend');

function scrollBottom() { chatBox.scrollTop = chatBox.scrollHeight; }
scrollBottom();

input.addEventListener('input', function () {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        enviar();
    }
});

btnSend.addEventListener('click', enviar);

async function enviar() {
    const texto = input.value.trim();
    if (!texto) return;

    btnSend.disabled = true;

    const fd = new FormData();
    fd.append('accion', 'enviar');
    fd.append('conversacion_id', convId);
    fd.append('mensaje', texto);

    try {
        const res  = await fetch(`${BASE}/mensajes/enviar`, { method: 'POST', body: fd });
        const json = await res.json();

        if (json.bloqueado) {
            mostrarBloqueado(json.error);
            return;
        }

        if (!json.ok) {
            Swal.fire({ icon: 'error', title: 'Error', text: json.error || 'No se pudo enviar.', confirmButtonColor: '#0066ff' });
            return;
        }

        input.value = '';
        input.style.height = 'auto';
        await poll();

    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Error de red', text: 'Verifica tu conexión.', confirmButtonColor: '#0066ff' });
    } finally {
        btnSend.disabled = false;
        input.focus();
    }
}

function mostrarBloqueado(mensaje) {
    const div = document.createElement('div');
    div.className = 'msg-bloqueado';
    div.textContent = mensaje;
    chatBox.appendChild(div);
    scrollBottom();
    setTimeout(() => div.remove(), 7000);
}

function agregarBurbuja(m) {
    const isMe = (parseInt(m.emisor_id) === myId);
    const hora  = m.fecha_hora ? m.fecha_hora.substring(11, 16) : '';

    const row = document.createElement('div');
    row.className = 'msg-row ' + (isMe ? 'mine' : 'theirs');

    const texto = (m.contenido || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\n/g, '<br>');

    row.innerHTML = `
        <div class="bubble">
            ${texto}
            <div class="bubble-time">${hora}</div>
        </div>`;
    chatBox.appendChild(row);
}

async function poll() {
    try {
        const res  = await fetch(`${BASE}/mensajes/poll?id=${convId}&after=${encodeURIComponent(lastTime)}`);
        const json = await res.json();
        if (!json.ok || !json.mensajes?.length) return;

        json.mensajes.forEach(m => {
            lastTime = m.fecha_hora;
            agregarBurbuja(m);
        });
        scrollBottom();
    } catch (_) {}
}

setInterval(poll, 3500);
</script>
</body>
</html>
