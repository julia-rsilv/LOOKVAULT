<?php
$pageTitle='LOOKVAULT · Ferramentas';$pageDescription='Acesse os recursos do seu guarda-roupa inteligente.';
include 'includes/header.php';
$modulos=[
 ['assistente-ia.php','fa-wand-magic-sparkles','Assistente de Estilo','Sugestões com base nas peças cadastradas.'],
 ['calendario-looks.php','fa-calendar-days','Calendário de Looks','Planeje combinações para cada data.'],
 ['wishlist.php','fa-heart','Wishlist','Guarde suas peças favoritas.'],
 ['marketplace.php','fa-recycle','Marketplace','Gerencie anúncios de troca e doação.'],
 ['modo-viagem.php','fa-suitcase','Modo Viagem','Organize destinos e datas das viagens.'],
 ['estatisticas.php','fa-chart-pie','Estatísticas','Acompanhe dados do seu guarda-roupa.'],
 ['minha-conta.php','fa-gear','Configurações','Atualize seus dados e perfil.']
];
?>
<link rel="stylesheet" href="assets/css/pages.css"><main><section class="page-hero"><div class="container"><div class="page-hero-inner"><div class="page-hero-text"><span class="page-hero-eyebrow"><i class="fas fa-grid-2"></i> Central de recursos</span><h1 class="page-hero-title">Suas <em>Ferramentas</em></h1><p class="page-hero-subtitle">Tudo para organizar, planejar e aproveitar melhor seu guarda-roupa.</p></div></div></div></section><section class="wardrobe-section"><div class="container"><div class="looks-grid-new"><?php foreach($modulos as $m):?><a class="look-card-new" href="<?=htmlspecialchars($m[0],ENT_QUOTES,'UTF-8')?>" style="text-decoration:none"><div class="look-body"><div class="empty-premium-icon"><i class="fas <?=htmlspecialchars($m[1],ENT_QUOTES,'UTF-8')?>"></i></div><h3><?=htmlspecialchars($m[2],ENT_QUOTES,'UTF-8')?></h3><p><?=htmlspecialchars($m[3],ENT_QUOTES,'UTF-8')?></p><span class="btn-primary">Acessar</span></div></a><?php endforeach;?></div></div></section></main><?php include 'includes/footer.php';?>