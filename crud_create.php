<?php
session_start();
require_once 'configdatabase.php';
if (!isset($_SESSION['usuario_id'])) { header('Location: login.php'); exit; }
$usuario_id = (int) $_SESSION['usuario_id'];
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $marca = trim($_POST['marca'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $cor = trim($_POST['cor'] ?? '');
    $valor_raw = str_replace(',', '.', trim($_POST['valor'] ?? ''));
    if ($nome === '' || $categoria === '') {
        $erro = 'Nome e categoria são obrigatórios.';
    } elseif ($valor_raw !== '' && (!is_numeric($valor_raw) || (float)$valor_raw < 0)) {
        $erro = 'Informe um valor válido.';
    } else {
        $valor = $valor_raw === '' ? null : (float)$valor_raw;
        $id = inserirRetornarId(
            "INSERT INTO pecas (usuario_id,nome,marca,categoria,cor,valor) VALUES (?,?,?,?,?,?)",
            [$usuario_id,$nome,$marca,$categoria,$cor,$valor]
        );
        if ($id) { header('Location: crud_read.php?msg=' . urlencode('Peça criada com sucesso!')); exit; }
        $erro = 'Não foi possível criar a peça.';
    }
}
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Adicionar peça</title></head><body>
<h1>Adicionar peça</h1><?php if($erro): ?><p role="alert"><?=h($erro)?></p><?php endif; ?>
<form method="post">
<label>Nome <input name="nome" maxlength="100" required></label><br>
<label>Marca <input name="marca" maxlength="50"></label><br>
<label>Categoria <input name="categoria" maxlength="50" required></label><br>
<label>Cor <input name="cor" maxlength="30"></label><br>
<label>Valor <input type="number" name="valor" min="0" step="0.01"></label><br>
<button type="submit">Salvar</button></form><p><a href="crud_read.php">Voltar</a></p></body></html>
