<?php
session_start(); require_once 'configdatabase.php';
if(!isset($_SESSION['usuario_id'])){header('Location: login.php');exit;}
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);header('Allow: POST');exit('Método não permitido.');}
$usuario_id=(int)$_SESSION['usuario_id']; $id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
if(!$id||$id<1){header('Location: crud_read.php?msg='.urlencode('ID inválido.'));exit;}
$ok=executarQuery("DELETE FROM pecas WHERE id=? AND usuario_id=?",[$id,$usuario_id]);
header('Location: crud_read.php?msg='.urlencode($ok===true?'Peça excluída com sucesso!':'Não foi possível excluir a peça.'));
exit;
