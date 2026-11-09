<?php
session_start();
require_once 'configdatabase.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
$usuario_id = (int) $_SESSION['usuario_id'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: combinacoes.php');
    exit;
}

// Confirma propriedade antes de remover relações ou o look.
$look = executarQuery("SELECT id FROM looks WHERE id = ? AND usuario_id = ?", [$id, $usuario_id]);
if (!$look || $look->num_rows === 0) {
    header('Location: combinacoes.php?erro=' . urlencode('Look não encontrado.'));
    exit;
}

executarQuery("DELETE FROM look_pecas WHERE look_id = ?", [$id]);
$result = executarQuery("DELETE FROM looks WHERE id = ? AND usuario_id = ?", [$id, $usuario_id]);

if ($result === true) {
    header('Location: combinacoes.php?sucesso=' . urlencode('Look excluído com sucesso!'));
} else {
    header('Location: combinacoes.php?erro=' . urlencode('Erro ao excluir look.'));
}
exit;
?>
