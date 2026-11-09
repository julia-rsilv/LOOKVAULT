<?php
session_start();
require_once 'configdatabase.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos.';
    } elseif (strlen($nome) < 3) {
        $erro = 'Nome deve ter pelo menos 3 caracteres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'Senha deve ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirmar_senha) {
        $erro = 'As senhas não coincidem.';
    } else {
        $conn = conectarDB();
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $erro = 'Este e-mail já está cadastrado.';
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $email, $senha_hash);
            if ($stmt->execute()) {
                header('Location: login.php?cadastro=sucesso');
                exit;
            } else {
                $erro = 'Erro ao cadastrar. Tente novamente.';
            }
        }
        $stmt->close();
        $conn->close();
    }
}

$pageTitle = "LOOKVAULT · Criar Conta";
$pageDescription = "Crie sua conta LOOKVAULT.";
include 'includes/header.php';
?>

<main class="auth-container">
    <div class="auth-box">
        <div class="logo-auth">
            <h1>Criar Conta</h1>
            <p>Comece a organizar seu guarda-roupa</p>
        </div>

        <?php if (!empty($erro)): ?>
        <div class="mensagem-erro">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="nome">Nome completo</label>
                <input type="text" id="nome" name="nome" placeholder="Seu nome" required />
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="seu@email.com" required />
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="Mínimo 6 caracteres" required />
            </div>
            <div class="form-group">
                <label for="confirmar_senha">Confirmar senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Digite novamente" required />
            </div>
            <button type="submit" class="btn-auth">Criar Conta</button>
        </form>

        <div class="links">
            <a href="login.php">Já tenho conta</a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>