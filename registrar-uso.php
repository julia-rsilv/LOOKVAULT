<?php
session_start();
require_once 'configdatabase.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Faça login para registrar o uso.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$usuario_id = (int) $_SESSION['usuario_id'];
$peca_id = filter_input(INPUT_POST, 'peca_id', FILTER_VALIDATE_INT);
$data_uso = $_POST['data_uso'] ?? date('Y-m-d');
$data = DateTime::createFromFormat('Y-m-d', $data_uso);

if (!$peca_id || $peca_id <= 0 || !$data || $data->format('Y-m-d') !== $data_uso) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados inválidos.']);
    exit;
}

$peca = executarQuery("SELECT id FROM pecas WHERE id = ? AND usuario_id = ?", [$peca_id, $usuario_id]);
if (!$peca || $peca->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Peça não encontrada.']);
    exit;
}

$result = executarQuery("INSERT INTO uso_pecas (peca_id, data_uso) VALUES (?, ?)", [$peca_id, $data_uso]);
echo json_encode($result === true
    ? ['sucesso' => true, 'mensagem' => 'Uso registrado com sucesso!']
    : ['sucesso' => false, 'mensagem' => 'Erro ao registrar uso']);
?>
