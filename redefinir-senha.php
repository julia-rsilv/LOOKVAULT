<?php
if(session_status()===PHP_SESSION_NONE)session_start();
require_once 'configdatabase.php';
if(empty($_SESSION['csrf_token']))$_SESSION['csrf_token']=bin2hex(random_bytes(32));
$token=$_POST['token']??$_GET['token']??'';$erro='';$sucesso='';
$registro=null;
if(is_string($token)&&preg_match('/^[a-f0-9]{64}$/',$token)){
 $r=executarQuery("SELECT id,usuario_id FROM recuperar_senha WHERE token=? AND usado=0 AND expiracao>NOW() LIMIT 1",[hash('sha256',$token)]);
 if($r)$registro=$r->fetch_assoc();
}
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!hash_equals($_SESSION['csrf_token'],$_POST['csrf_token']??'')){http_response_code(403);exit('Solicitação inválida.');}
 $senha=$_POST['senha']??'';$conf=$_POST['confirmar_senha']??'';
 if(!$registro)$erro='Este link é inválido ou expirou. Solicite uma nova recuperação.';
 elseif(strlen($senha)<8)$erro='A senha deve ter pelo menos 8 caracteres.';
 elseif($senha!==$conf)$erro='As senhas não coincidem.';
 else{
  $hash=password_hash($senha,PASSWORD_DEFAULT);
  executarQuery("UPDATE usuarios SET senha=? WHERE id=?",[$hash,(int)$registro['usuario_id']]);
  executarQuery("UPDATE recuperar_senha SET usado=1 WHERE usuario_id=?",[ (int)$registro['usuario_id'] ]);
  $sucesso='Senha atualizada com sucesso. Você já pode entrar na sua conta.';
  $registro=null;
 }
}
$pageTitle='LOOKVAULT · Nova Senha';$pageDescription='Defina uma nova senha.';include 'includes/header.php';
?>
<main class="auth-container"><div class="auth-box"><div class="logo-auth"><h1>Nova senha</h1><p>Crie uma senha segura para sua conta.</p></div><?php if($erro):?><div class="mensagem-erro"><?=htmlspecialchars($erro,ENT_QUOTES,'UTF-8')?></div><?php endif;?><?php if($sucesso):?><div class="mensagem-sucesso"><?=htmlspecialchars($sucesso,ENT_QUOTES,'UTF-8')?></div><div class="links"><a href="login.php">Ir para o login</a></div><?php elseif($registro):?><form method="post"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'],ENT_QUOTES,'UTF-8')?>"><input type="hidden" name="token" value="<?=htmlspecialchars($token,ENT_QUOTES,'UTF-8')?>"><div class="form-group"><label>Nova senha</label><input type="password" name="senha" minlength="8" required></div><div class="form-group"><label>Confirmar senha</label><input type="password" name="confirmar_senha" minlength="8" required></div><button class="btn-auth">Redefinir senha</button></form><?php else:?><p>Link inválido ou expirado.</p><a href="recuperar-senha.php">Solicitar novo link</a><?php endif;?></div></main><?php include 'includes/footer.php';?>