<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'configdatabase.php';
if (empty($_SESSION['usuario_id'])) { header('Location: login.php'); exit; }
$usuario_id = (int) $_SESSION['usuario_id'];
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
function lv_h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function lv_check_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
        !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403); exit('Solicitação inválida. Atualize a página e tente novamente.');
    }
}
