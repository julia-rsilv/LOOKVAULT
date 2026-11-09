<?php
if(session_status()===PHP_SESSION_NONE)session_start();
require_once 'configdatabase.php';
if(empty($_SESSION['csrf_token']))$_SESSION['csrf_token']=bin2hex(random_bytes(32));
$mensagem='';$erro='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!hash_equals($_SESSION['csrf_token'],$_POST['csrf_token']??'')){http_response_code(403);exit('Solicitação inválida.');}
 $email=trim($_POST['email']??'');
 if(!filter_var($email,FILTER_VALIDATE_EMAIL))$erro='Informe um e-mail válido.';
 else{
  $r=executarQuery("SELECT id,nome,email FROM usuarios WHERE email=?",[$email]);
  if($r && $u=$r->fetch_assoc()){
   $token=bin2hex(random_bytes(32));$hash=hash('sha256',$token);$exp=date('Y-m-d H:i:s',time()+3600);
   executarQuery("UPDATE recuperar_senha SET usado=1 WHERE usuario_id=?",[(int)$u['id']]);
   executarQuery("INSERT INTO recuperar_senha(usuario_id,token,expiracao,usado) VALUES(?,?,?,0)",[(int)$u['id'],$hash,$exp]);
   $base=(isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off'?'https':'http').'://'.($_SERVER['HTTP_HOST']??'localhost').rtrim(dirname($_SERVER['PHP_SELF']),'/\\');
   $url=$base.'/redefinir-senha.php?token='.urlencode($token);
   $subject='Redefinição de senha - LOOKVAULT';
   $body="Olá, {$u['nome']}!\n\nPara redefinir sua senha, acesse o link abaixo em até 1 hora:\n$url\n\nSe você não solicitou, ignore esta mensagem.";
   $headers="From: LOOKVAULT <no-reply@lookvault.local>\r\nContent-Type: text/plain; charset=UTF-8";
   if(!@mail($u['email'],$subject,$body,$headers))error_log("LOOKVAULT: envio de recuperação falhou para usuário ID ".$u['id'].". Configure um serviço SMTP.");
  }
  $mensagem='Se o endereço estiver cadastrado e o serviço de e-mail estiver configurado, você receberá um link de redefinição válido por 1 hora.';
 }
}
$pageTitle='LOOKVAULT · Recuperar Senha';$pageDescription='Recupere o acesso à sua conta.';include 'includes/header.php';
?>
<main class="auth-container"><div class="auth-box"><div class="logo-auth"><h1>Recuperar senha</h1><p>Informe o e-mail da sua conta.</p></div><?php if($erro):?><div class="mensagem-erro"><?=htmlspecialchars($erro,ENT_QUOTES,'UTF-8')?></div><?php endif;?><?php if($mensagem):?><div class="mensagem-sucesso"><?=htmlspecialchars($mensagem,ENT_QUOTES,'UTF-8')?></div><?php endif;?><form method="post"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'],ENT_QUOTES,'UTF-8')?>"><div class="form-group"><label for="email">E-mail</label><input id="email" type="email" name="email" required></div><button class="btn-auth" type="submit">Enviar instruções</button></form><div class="links"><a href="login.php">Voltar ao login</a></div></div></main><?php include 'includes/footer.php';?>