<?php
session_start(); require_once 'configdatabase.php';
if(!isset($_SESSION['usuario_id'])){header('Location: login.php');exit;}
$usuario_id=(int)$_SESSION['usuario_id'];
$res=executarQuery("SELECT id,nome,marca,categoria,cor,valor FROM pecas WHERE usuario_id=? ORDER BY id DESC",[$usuario_id]);
$pecas=[]; if($res){while($r=$res->fetch_assoc())$pecas[]=$r;}
function h($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Minhas peças</title>
<style>body{font-family:Arial,sans-serif;margin:2rem}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ccc;padding:8px;text-align:left}th{background:#f0f0f0}.acoes{display:flex;gap:10px;align-items:center}.acoes form{margin:0}@media(max-width:650px){table{font-size:12px}th,td{padding:5px}}</style></head><body>
<h1>Minhas peças</h1><?php if(isset($_GET['msg'])):?><p><?=h($_GET['msg'])?></p><?php endif;?>
<p><a href="crud_create.php">+ Adicionar peça</a> | <a href="guarda-roupa.php">Voltar ao guarda-roupa</a></p>
<table><thead><tr><th>ID</th><th>Nome</th><th>Marca</th><th>Categoria</th><th>Cor</th><th>Valor</th><th>Ações</th></tr></thead><tbody>
<?php if(!$pecas):?><tr><td colspan="7">Nenhuma peça cadastrada.</td></tr><?php else: foreach($pecas as $p):?>
<tr><td><?=h($p['id'])?></td><td><?=h($p['nome'])?></td><td><?=h($p['marca'])?></td><td><?=h($p['categoria'])?></td><td><?=h($p['cor'])?></td><td><?= $p['valor']===null?'—':'R$ '.number_format((float)$p['valor'],2,',','.') ?></td>
<td class="acoes"><a href="crud_update.php?id=<?=h($p['id'])?>">Editar</a><form method="post" action="crud_delete.php" onsubmit="return confirm('Excluir esta peça?')"><input type="hidden" name="id" value="<?=h($p['id'])?>"><button type="submit">Excluir</button></form></td></tr>
<?php endforeach; endif;?></tbody></table></body></html>
