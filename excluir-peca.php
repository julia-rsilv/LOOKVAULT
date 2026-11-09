<?php
session_start();
require_once 'configdatabase.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
$usuario_id = (int) $_SESSION['usuario_id'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id && $id > 0) {
    // A peça só pode ser removida pelo seu proprietário.
    $result = executarQuery("DELETE FROM pecas WHERE id = ? AND usuario_id = ?", [$id, $usuario_id]);
    if ($result === true) {
        header('Location: guarda-roupa.php?sucesso=' . urlencode('Peça excluída com sucesso!'));
        exit;
    }
}
header('Location: guarda-roupa.php?erro=' . urlencode('Não foi possível excluir a peça.'));
exit;
?>
