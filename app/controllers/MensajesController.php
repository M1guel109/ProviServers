<?php
require_once __DIR__ . '/../models/Conversacion.php';
require_once __DIR__ . '/../models/Mensaje.php';

session_start();

class MensajesController
{
    private Conversacion $convModel;
    private Mensaje $msgModel;

    public function __construct()
    {
        if (!isset($_SESSION['user']['id'])) {
            die('Acceso denegado');
        }
        $this->convModel = new Conversacion();
        $this->msgModel  = new Mensaje();
    }

    public function inbox()
    {
        $uid = (int)$_SESSION['user']['id'];
        $convs = $this->convModel->listarInbox($uid);
        require BASE_PATH . '/app/views/mensajes/inbox.php';
    }

    /**
     * /mensajes/abrir?tipo=solicitud&id=5
     * /mensajes/abrir?tipo=cotizacion&id=12
     */
    public function abrir()
    {
        $uid  = (int)$_SESSION['user']['id'];
        $tipo = $_GET['tipo'] ?? '';
        $id   = (int)($_GET['id'] ?? 0);

        if ($id <= 0 || !in_array($tipo, ['solicitud', 'cotizacion'], true)) {
            die('Parámetros inválidos');
        }

        try {
            $convId = ($tipo === 'solicitud')
                ? $this->convModel->getOrCreateFromSolicitud($id, $uid)
                : $this->convModel->getOrCreateFromCotizacion($id, $uid);

            header("Location: /ProviServers/mensajes/ver?id=" . $convId);
            exit();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function ver()
    {
        $uid = (int)$_SESSION['user']['id'];
        $convId = (int)($_GET['id'] ?? 0);
        if ($convId <= 0) die('Conversación inválida');

        if (!$this->convModel->usuarioTieneAcceso($convId, $uid)) {
            die('Acceso denegado');
        }

        $conv = $this->convModel->obtenerPorId($convId);
        if (!$conv) die('No existe conversación');

        $tema = $this->convModel->obtenerTema($convId);
        $otroUsuarioId = $this->convModel->obtenerOtroUsuarioId($conv, $uid);

        $mensajes = $this->msgModel->listarPorConversacion($convId, 80);
        $this->msgModel->marcarLeidos($convId, $uid);

        require BASE_PATH . '/app/views/mensajes/chat.php';
    }

    public function enviar()
    {
        header('Content-Type: application/json; charset=utf-8');

        $uid = (int)$_SESSION['user']['id'];
        $convId = (int)($_POST['conversacion_id'] ?? 0);
        $texto = trim($_POST['mensaje'] ?? '');

        if ($convId <= 0 || $texto === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
            return;
        }

        $conv = $this->convModel->obtenerPorId($convId);
        if (!$conv || !$this->convModel->usuarioTieneAcceso($convId, $uid)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
            return;
        }

        $receptorId = $this->convModel->obtenerOtroUsuarioId($conv, $uid);
        $msgId = $this->msgModel->crear($convId, $uid, $receptorId, $texto);

        echo json_encode(['ok' => true, 'id' => $msgId]);
    }

    public function poll()
    {
        header('Content-Type: application/json; charset=utf-8');

        $uid = (int)$_SESSION['user']['id'];
        $convId = (int)($_GET['id'] ?? 0);
        $after = $_GET['after'] ?? null;

        if ($convId <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Parámetros inválidos']);
            return;
        }

        if (!$this->convModel->usuarioTieneAcceso($convId, $uid)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
            return;
        }

        $nuevos = $this->msgModel->listarNuevos($convId, $after);
        if (!empty($nuevos)) {
            $this->msgModel->marcarLeidos($convId, $uid);
        }

        echo json_encode(['ok' => true, 'mensajes' => $nuevos]);
    }
}
