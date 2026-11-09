<?php
session_start();
require_once 'configdatabase.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos.';
    } else {
        $conn = conectarDB();
        $stmt = $conn->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            $senhaLegada = strlen($usuario['senha']) === 32 && hash_equals($usuario['senha'], md5($senha));
            if ($senhaLegada || password_verify($senha, $usuario['senha'])) {
                if ($senhaLegada) {
                    $novoHash = password_hash($senha, PASSWORD_DEFAULT);
                    $upd = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                    $upd->bind_param("si", $novoHash, $usuario['id']);
                    $upd->execute();
                    $upd->close();
                }
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                header('Location: index.php');
                exit;
            } else {
                $erro = 'Senha incorreta.';
            }
        } else {
            $erro = 'E-mail não cadastrado.';
        }
        $stmt->close();
        $conn->close();
    }
}

$pageTitle = "LOOKVAULT · Login";
$pageDescription = "Entre na sua conta LOOKVAULT.";
include 'includes/header.php';
?>

<main class="auth-container">
    <div class="auth-box">
        <div class="logo-auth">
            <h1>Bem-vindo de volta</h1>
            <p>Entre e organize seu guarda-roupa</p>
        </div>

        <?php if (!empty($erro)): ?>
        <div class="mensagem-erro">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['cadastro']) && $_GET['cadastro'] == 'sucesso'): ?>
        <div class="mensagem-sucesso">
            <i class="fas fa-check-circle"></i> Cadastro realizado! Faça seu login.
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="seu@email.com" required />
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="••••••••" required />
            </div>
            <button type="submit" class="btn-auth">Entrar</button>
        </form>

        <div class="links">
            <a href="cadastro-usuario.php">Criar conta</a>
            <span class="divider">|</span>
            <a href="recuperar-senha.php">Esqueci minha senha</a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>