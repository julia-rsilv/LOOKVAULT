<?php
require_once 'includes/module-auth.php';
lv_check_csrf();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $peca_id=(int)($_POST['peca_id']??0);
    $acao=$_POST['acao']??'';
    $own=executarQuery("SELECT id FROM pecas WHERE id=? AND usuario_id=?",[$peca_id,$usuario_id]);
    if ($own && $own->num_rows) {
        if ($acao==='remover') executarQuery("DELETE FROM favoritos_pecas WHERE usuario_id=? AND peca_id=?",[$usuario_id,$peca_id]);
        else executarQuery("INSERT IGNORE INTO favoritos_pecas (usuario_id,peca_id) VALUES (?,?)",[$usuario_id,$peca_id]);
    }
    header('Location: wishlist.php'); exit;
}
$r=executarQuery("SELECT p.* FROM favoritos_pecas f JOIN pecas p ON p.id=f.peca_id WHERE f.usuario_id=? ORDER BY f.data_criacao DESC",[$usuario_id]);
$pecas=[]; if($r) while($x=$r->fetch_assoc()) $pecas[]=$x;
$pageTitle='LOOKVAULT · Wishlist'; $pageDescription='Suas peças favoritas.';
include 'includes/header.php';
?>
<link rel="stylesheet" href="assets/css/pages.css">
<main><section class="page-hero"><div class="container"><div class="page-hero-inner"><div class="page-hero-text"><span class="page-hero-eyebrow"><i class="fas fa-heart"></i> Coleção pessoal</span><h1 class="page-hero-title">Minha <em>Wishlist</em></h1><p class="page-hero-subtitle">Guarde suas peças favoritas em um só lugar.</p></div></div></div></section>
<section class="wardrobe-section"><div class="container">
<?php if(!$pecas): ?><div class="empty-premium"><div class="empty-premium-icon"><i class="far fa-heart"></i></div><h3>Sua wishlist está vazia</h3><p>Adicione favoritos pelo seu guarda-roupa.</p><a class="btn-primary" href="guarda-roupa.php">Ver meu guarda-roupa</a></div>
<?php else: ?><div class="wardrobe-grid-new"><?php foreach($pecas as $p): ?><article class="peca-card"><div class="peca-card-image"><?php if($p['foto'] && is_file($p['foto'])): ?><img src="<?=lv_h($p['foto'])?>" alt="<?=lv_h($p['nome'])?>"><?php else: ?><div class="empty-premium-icon"><i class="fas fa-tshirt"></i></div><?php endif;?></div><div class="peca-card-body"><h3><?=lv_h($p['nome'])?></h3><p><?=lv_h($p['marca'])?> · <?=lv_h($p['categoria'])?></p><form method="post"><input type="hidden" name="csrf_token" value="<?=lv_h($_SESSION['csrf_token'])?>"><input type="hidden" name="peca_id" value="<?=$p['id']?>"><input type="hidden" name="acao" value="remover"><button class="btn-primary" type="submit">Remover dos favoritos</button></form></div></article><?php endforeach;?></div><?php endif;?>
</div></section></main><?php include 'includes/footer.php'; ?>