<?php
session_start(); require_once 'configdatabase.php';
if (!isset($_SESSION['usuario_id'])) { header('Location: login.php'); exit; }
$usuario_id=(int)$_SESSION['usuario_id'];
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
if (!$id || $id<1) { header('Location: crud_read.php'); exit; }
$res=executarQuery("SELECT * FROM pecas WHERE id=? AND usuario_id=?",[$id,$usuario_id]);
if (!$res || !$res->num_rows) { header('Location: crud_read.php?msg='.urlencode('Peça não encontrada.')); exit; }
$peca=$res->fetch_assoc(); $erro='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 $nome=trim($_POST['nome']??''); $marca=trim($_POST['marca']??'');
 $categoria=trim($_POST['categoria']??''); $cor=trim($_POST['cor']??'');
 $raw=str_replace(',', '.', trim($_POST['valor']??''));
 if($nome===''||$categoria==='') $erro='Nome e categoria são obrigatórios.';
 elseif($raw!==''&&(!is_numeric($raw)||(float)$raw<0)) $erro='Informe um valor válido.';
 else {
  $valor=$raw===''?null:(float)$raw;
  $ok=executarQuery("UPDATE pecas SET nome=?,marca=?,categoria=?,cor=?,valor=? WHERE id=? AND usuario_id=?",[$nome,$marca,$categoria,$cor,$valor,$id,$usuario_id]);
  if($ok===true){header('Location: crud_read.php?msg='.urlencode('Peça atualizada com sucesso!'));exit;}
  $erro='Não foi possível atualizar a peça.';
 }
 $peca=array_merge($peca,['nome'=>$nome,'marca'=>$marca,'categoria'=>$categoria,'cor'=>$cor,'valor'=>$raw]);
}
function h($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Editar peça</title></head><body>
<h1>Editar peça</h1><?php if($erro):?><p role="alert"><?=h($erro)?></p><?php endif;?>
<form method="post">
<label>Nome <input name="nome" maxlength="100" value="<?=h($peca['nome'])?>" required></label><br>
<label>Marca <input name="marca" maxlength="50" value="<?=h($peca['marca'])?>"></label><br>
<label>Categoria <input name="categoria" maxlength="50" value="<?=h($peca['categoria'])?>" required></label><br>
<label>Cor <input name="cor" maxlength="30" value="<?=h($peca['cor'])?>"></label><br>
<label>Valor <input type="number" name="valor" min="0" step="0.01" value="<?=h($peca['valor'])?>"></label><br>
<button type="submit">Atualizar</button></form><a href="crud_read.php">Cancelar</a></body></html>
