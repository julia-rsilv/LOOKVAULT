<?php
require_once 'includes/module-auth.php';
$categorias=['Camisas','Calças','Vestidos','Sapatos','Acessórios','Blazers'];
$r=executarQuery("SELECT categoria,cor,nome,marca FROM pecas WHERE usuario_id=? ORDER BY data_cadastro DESC",[$usuario_id]);$pecas=[];if($r)while($x=$r->fetch_assoc())$pecas[]=$x;
$looks=executarQuery("SELECT COUNT(*) total FROM looks WHERE usuario_id=?",[$usuario_id]);$totalLooks=$looks?$looks->fetch_assoc()['total']:0;
$porCategoria=[];foreach($pecas as $p){$c=$p['categoria']?:'Sem categoria';$porCategoria[$c]=($porCategoria[$c]??0)+1;}arsort($porCategoria);
$sugestoes=[];
if(!$pecas)$sugestoes[]='Comece cadastrando algumas peças no seu guarda-roupa para receber sugestões.';
else {
 if(count($pecas)<5)$sugestoes[]='Adicione mais peças para ampliar as possibilidades de combinações.';
 if($totalLooks===0)$sugestoes[]='Monte seu primeiro look combinando peças de categorias diferentes.';
 if(count($porCategoria)===1)$sugestoes[]='Seu acervo está concentrado em uma categoria. Experimente cadastrar calçados e acessórios para completar produções.';
 else $sugestoes[]='Experimente combinar uma peça de destaque com itens de cores neutras que você já possui.';
 $sugestoes[]='Revise as peças que você não usa há algum tempo e pense em novas combinações ou em disponibilizá-las para troca/doação.';
}
$pageTitle='LOOKVAULT · Assistente de Estilo';$pageDescription='Sugestões para seu guarda-roupa.';include 'includes/header.php';
?>
<link rel="stylesheet" href="assets/css/pages.css"><main><section class="page-hero"><div class="container"><div class="page-hero-inner"><div class="page-hero-text"><span class="page-hero-eyebrow"><i class="fas fa-wand-magic-sparkles"></i> Seu estilo</span><h1 class="page-hero-title">Assistente de <em>Estilo</em></h1><p class="page-hero-subtitle">Ideias e orientações com base nas peças cadastradas no seu guarda-roupa.</p></div></div></div></section><section class="wardrobe-section"><div class="container"><div class="auth-box" style="max-width:760px;margin:auto"><h2>Seu resumo de estilo</h2><p>Você tem <strong><?=count($pecas)?> peças</strong> cadastradas e <strong><?= (int)$totalLooks?> looks</strong> salvos.</p><h3>Categorias do seu acervo</h3><?php if($porCategoria):?><ul><?php foreach($porCategoria as $c=>$n):?><li><?=htmlspecialchars($c,ENT_QUOTES,'UTF-8')?>: <?=$n?></li><?php endforeach;?></ul><?php else:?><p>Seu guarda-roupa ainda está vazio.</p><?php endif;?><h3>Sugestões</h3><ul><?php foreach($sugestoes as $s):?><li><?=htmlspecialchars($s,ENT_QUOTES,'UTF-8')?></li><?php endforeach;?></ul><a class="btn-primary" href="criar-look.php">Criar uma combinação</a></div><p style="text-align:center;color:#777;margin-top:20px">Nota: este assistente usa regras locais e os dados do seu guarda-roupa. Não é uma IA generativa conectada a um serviço externo.</p></div></section></main><?php include 'includes/footer.php';?>