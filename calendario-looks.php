<?php
require_once 'includes/module-auth.php'; lv_check_csrf();
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['acao']??'')==='excluir'){
 $event_id=(int)($_POST['evento_id']??0);
 executarQuery("DELETE FROM calendario_looks WHERE id=? AND usuario_id=?",[$event_id,$usuario_id]);
 header('Location: calendario-looks.php');exit;
}
if($_SERVER['REQUEST_METHOD']==='POST'){
 $look_id=(int)($_POST['look_id']??0); $data=$_POST['data_evento']??''; $obs=trim($_POST['observacao']??'');
 $d=DateTime::createFromFormat('Y-m-d',$data);
 $own=executarQuery("SELECT id FROM looks WHERE id=? AND usuario_id=?",[$look_id,$usuario_id]);
 if($d && $d->format('Y-m-d')===$data && $own && $own->num_rows) executarQuery("INSERT INTO calendario_looks(usuario_id,look_id,data_evento,observacao) VALUES(?,?,?,?)",[$usuario_id,$look_id,$data,$obs]);
 header('Location: calendario-looks.php'); exit;
}
$lr=executarQuery("SELECT id,nome FROM looks WHERE usuario_id=? ORDER BY nome",[$usuario_id]); $looks=[]; if($lr)while($x=$lr->fetch_assoc())$looks[]=$x;
$er=executarQuery("SELECT c.*,l.nome FROM calendario_looks c JOIN looks l ON l.id=c.look_id WHERE c.usuario_id=? ORDER BY c.data_evento",[$usuario_id]); $eventos=[];if($er)while($x=$er->fetch_assoc())$eventos[]=$x;
$pageTitle='LOOKVAULT · Calendário de Looks';$pageDescription='Planeje seus looks.';include 'includes/header.php';
?>
<link rel="stylesheet" href="assets/css/pages.css"><main><section class="page-hero"><div class="container"><div class="page-hero-inner"><div class="page-hero-text"><span class="page-hero-eyebrow"><i class="fas fa-calendar"></i> Planejamento</span><h1 class="page-hero-title">Calendário de <em>Looks</em></h1><p class="page-hero-subtitle">Escolha com antecedência o que vestir em cada ocasião.</p></div></div></div></section>
<section class="wardrobe-section"><div class="container"><div class="auth-box" style="max-width:680px;margin:0 auto 35px"><h2>Agendar um look</h2><?php if(!$looks):?><p>Você precisa criar um look antes de agendá-lo.</p><a class="btn-primary" href="criar-look.php">Criar look</a><?php else:?><form method="post"><input type="hidden" name="csrf_token" value="<?=lv_h($_SESSION['csrf_token'])?>"><div class="form-group"><label>Look</label><select name="look_id" required><?php foreach($looks as $l):?><option value="<?=$l['id']?>"><?=lv_h($l['nome'])?></option><?php endforeach;?></select></div><div class="form-group"><label>Data</label><input type="date" name="data_evento" min="<?=date('Y-m-d')?>" required></div><div class="form-group"><label>Observação</label><input name="observacao" maxlength="255" placeholder="Ex.: reunião, passeio..."></div><button class="btn-auth">Adicionar ao calendário</button></form><?php endif;?></div><h2>Próximos looks</h2><?php if(!$eventos):?><p>Nenhum look agendado ainda.</p><?php else:?><div class="looks-grid-new"><?php foreach($eventos as $e):?><article class="look-card-new"><div class="look-body"><span class="look-badge-ocasiao"><?=date('d/m/Y',strtotime($e['data_evento']))?></span><h3><?=lv_h($e['nome'])?></h3><p><?=lv_h($e['observacao'])?></p><form method="post"><input type="hidden" name="csrf_token" value="<?=lv_h($_SESSION['csrf_token'])?>"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="evento_id" value="<?=$e['id']?>"><button class="btn-primary" type="submit">Remover agendamento</button></form></div></article><?php endforeach;?></div><?php endif;?></div></section></main><?php include 'includes/footer.php';?>