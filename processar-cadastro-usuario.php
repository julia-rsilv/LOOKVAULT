<?php
session_start();
require_once 'configdatabase.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cadastro-usuario.php');
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';

if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
    header('Location: cadastro-usuario.php?erro=Preencha todos os campos');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: cadastro-usuario.php?erro=E-mail inválido');
    exit;
}

if ($senha !== $confirmar_senha) {
    header('Location: cadastro-usuario.php?erro=As senhas não coincidem');
    exit;
}

$conn = conectarDB();

// Verificar se e-mail já existe
$sql = "SELECT id FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    header('Location: cadastro-usuario.php?erro=E-mail já cadastrado');
    exit;
}
$stmt->close();

// Inserir novo usuário
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);
$sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $nome, $email, $senha_hash);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header('Location: login.php?cadastro=sucesso');
    exit;
}

$stmt->close();
$conn->close();
header('Location: cadastro-usuario.php?erro=Erro ao criar conta');
exit;
?>