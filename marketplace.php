<?php
require_once 'includes/module-auth.php'; lv_check_csrf();
if($_SERVER['REQUEST_METHOD']==='POST'){
 $acao=$_POST['acao']??'';$peca=(int)($_POST['peca_id']??0);
 if($acao==='anunciar'){
  $tipo=$_POST['tipo']??'';$desc=trim($_POST['descricao']??'');
  $own=executarQuery("SELECT id FROM pecas WHERE id=? AND usuario_id=?",[$peca,$usuario_id]);
  if($own && $own->num_rows && in_array($tipo,['troca','doacao'],true)) executarQuery("INSERT INTO marketplace_anuncios(usuario_id,peca_id,tipo,descricao) VALUES(?,?,?,?)",[$usuario_id,$peca,$tipo,$desc]);
 } elseif($acao==='status') {
  $id=(int)($_POST['anuncio_id']??0);$status=$_POST['status']??'pausado';
  if(in_array($status,['ativo','pausado','concluido'],true)) executarQuery("UPDATE marketplace_anuncios SET status=? WHERE id=? AND usuario_id=?",[$status,$id,$usuario_id]);
 }
 header('Location: marketplace.php');exit;
}
$pr=executarQuery("SELECT id,nome FROM pecas WHERE usuario_id=? ORDER BY nome",[$usuario_id]);$pecas=[];if($pr)while($x=$pr->fetch_assoc())$pecas[]=$x;
$ar=executarQuery("SELECT a.*,p.nome,p.foto,p.marca,u.nome AS anunciante FROM marketplace_anuncios a JOIN pecas p ON p.id=a.peca_id JOIN usuarios u ON u.id=a.usuario_id WHERE a.status='ativo' OR a.usuario_id=? ORDER BY a.data_criacao DESC",[$usuario_id]);$anuncios=[];if($ar)while($x=$ar->fetch_assoc())$anuncios[]=$x;
$pageTitle='LOOKVAULT · Marketplace';$pageDescription='Troque ou doe peças.';include 'includes/header.php';
?>
<link rel="stylesheet" href="assets/css/pages.css"><main><section class="page-hero"><div class="container"><div class="page-hero-inner"><div class="page-hero-text"><span class="page-hero-eyebrow"><i class="fas fa-recycle"></i> Moda circular</span><h1 class="page-hero-title">Market<em>place</em></h1><p class="page-hero-subtitle">Dê uma nova história às peças que você não usa mais.</p></div></div></div></section><section class="wardrobe-section"><div class="container">
<div class="auth-box" style="max-width:680px;margin:0 auto 35px"><h2>Criar anúncio</h2><?php if(!$pecas):?><p>Adicione peças ao guarda-roupa antes de anunciar.</p><a class="btn-primary" href="cadastro.php">Adicionar peça</a><?php else:?><form method="post"><input type="hidden" name="csrf_token" value="<?=lv_h($_SESSION['csrf_token'])?>"><input type="hidden" name="acao" value="anunciar"><div class="form-group"><label>Peça</label><select name="peca_id" required><?php foreach($pecas as $p):?><option value="<?=$p['id']?>"><?=lv_h($p['nome'])?></option><?php endforeach;?></select></div><div class="form-group"><label>Modalidade</label><select name="tipo"><option value="troca">Troca</option><option value="doacao">Doação</option></select></div><div class="form-group"><label>Descrição</label><textarea name="descricao" maxlength="500" placeholder="Descreva a peça e as condições..."></textarea></div><button class="btn-auth">Publicar anúncio</button></form><?php endif;?></div>
<h2>Anúncios da comunidade</h2><?php if(!$anuncios):?><p>Você ainda não publicou anúncios.</p><?php else:?><div class="looks-grid-new"><?php foreach($anuncios as $a):?><article class="look-card-new"><div class="look-body"><span class="look-badge-ocasiao"><?=lv_h(strtoupper($a['tipo']))?> · <?=lv_h($a['status'])?></span><h3><?=lv_h($a['nome'])?></h3><p><?=lv_h($a['descricao'])?></p><p>Anunciante: <?=lv_h($a['anunciante'])?></p><?php if((int)$a['usuario_id']===$usuario_id):?><form method="post"><input type="hidden" name="csrf_token" value="<?=lv_h($_SESSION['csrf_token'])?>"><input type="hidden" name="acao" value="status"><input type="hidden" name="anuncio_id" value="<?=$a['id']?>"><select name="status"><option value="ativo">Ativo</option><option value="pausado">Pausado</option><option value="concluido">Concluído</option></select><button class="btn-primary">Atualizar</button></form><?php endif;?></div></article><?php endforeach;?></div><?php endif;?>
<p style="margin-top:25px;color:#777">Os anúncios ativos ficam visíveis para usuários autenticados. A negociação, troca de mensagens e confirmação da entrega ainda precisam de integração própria.</p></div></section></main><?php include 'includes/footer.php';?>