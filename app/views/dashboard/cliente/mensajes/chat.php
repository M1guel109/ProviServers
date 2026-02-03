<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proviservers | Chat</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/estilosGenerales/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/dashBoard/css/dashboardCliente.css">
</head>
<body>
<?php
$currentPage = 'mensajes';
include_once __DIR__ . '/../../layouts/sidebar_cliente.php';

$uid = (int)($_SESSION['user']['id'] ?? 0);
$last = '1970-01-01 00:00:00';
if (!empty($mensajes)) {
    $ultimo = end($mensajes);
    if (!empty($ultimo['fecha_hora'])) $last = $ultimo['fecha_hora'];
}
?>

<main class="contenido">
    <?php include_once __DIR__ . '/../../layouts/header_cliente.php'; ?>

    <section id="mensajes">
        <div class="container">
            <div class="section-hero mb-3">
                <p class="breadcrumb">
                    <a href="<?= BASE_URL ?>/mensajes" class="text-decoration-none">Mensajes</a> &gt; Chat
                </p>
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="m-0"><i class="bi text-primary"></i><?= htmlspecialchars($tema ?? 'Conversación') ?></h1>
                    <a class="btn btn-outline-secondary btn-sm" href="<?= BASE_URL ?>/mensajes">Volver</a>
                </div>
                <p class="mb-0">Mantén la comunicación en la plataforma para mayor seguridad y trazabilidad.</p>
            </div>

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body">
                    <div id="chatBox" class="border rounded p-3 bg-light"
                         style="height: 55vh; overflow:auto;">
                        <?php if (empty($mensajes)): ?>
                            <div class="text-muted">Aún no hay mensajes en esta conversación.</div>
                        <?php else: ?>
                            <?php foreach ($mensajes as $m): ?>
                                <?php $isMe = ((int)$m['emisor_id'] === $uid); ?>
                                <div class="mb-2 d-flex <?= $isMe ? 'justify-content-end' : 'justify-content-start' ?>">
                                    <div class="p-2 rounded <?= $isMe ? 'bg-primary text-white' : 'bg-white' ?>"
                                         style="max-width: 70%;">
                                        <div><?= nl2br(htmlspecialchars($m['contenido'] ?? '')) ?></div>
                                        <div class="small opacity-75 mt-1">
                                            <?= htmlspecialchars($m['fecha_hora'] ?? '') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <form id="frmSend" class="mt-3">
                        <input type="hidden" name="conversacion_id" value="<?= (int)$convId ?>">
                        <div class="input-group">
                            <input class="form-control" name="mensaje" id="mensaje"
                                   placeholder="Escribe un mensaje..." autocomplete="off">
                            <button class="btn btn-primary" type="submit">Enviar</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/public/assets/dashBoard/js/dashboardCliente.js"></script>

<script>
const BASE = "<?= BASE_URL ?>";
const convId = <?= (int)$convId ?>;
let lastTime = "<?= addslashes($last) ?>";

const chatBox = document.getElementById('chatBox');
function scrollBottom(){ chatBox.scrollTop = chatBox.scrollHeight; }
scrollBottom();

document.getElementById('frmSend').addEventListener('submit', async (e) => {
  e.preventDefault();
  const input = document.getElementById('mensaje');
  const text = input.value.trim();
  if (!text) return;

  const fd = new FormData(e.target);
  const res = await fetch(`${BASE}/mensajes/enviar`, { method: 'POST', body: fd });
  const json = await res.json();

  if (json.ok) {
    input.value = '';
    await poll();
  }
});

async function poll(){
  const res = await fetch(`${BASE}/mensajes/poll?id=${convId}&after=${encodeURIComponent(lastTime)}`);
  const json = await res.json();
  if (!json.ok) return;

  if (json.mensajes && json.mensajes.length) {
    json.mensajes.forEach(m => {
      lastTime = m.fecha_hora;
      const isMe = (parseInt(m.emisor_id) === <?= (int)$uid ?>);

      const wrap = document.createElement('div');
      wrap.className = 'mb-2 d-flex ' + (isMe ? 'justify-content-end' : 'justify-content-start');
      wrap.innerHTML = `
        <div class="p-2 rounded ${isMe ? 'bg-primary text-white' : 'bg-white'}" style="max-width:70%;">
          <div>${(m.contenido || '').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>')}</div>
          <div class="small opacity-75 mt-1">${m.fecha_hora || ''}</div>
        </div>
      `;
      chatBox.appendChild(wrap);
    });
    scrollBottom();
  }
}

setInterval(poll, 3500);
</script>
</body>
</html>

