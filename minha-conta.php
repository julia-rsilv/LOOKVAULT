<?php
session_start();
require_once 'configdatabase.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$conn = conectarDB();

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM pecas WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$total_pecas = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM looks WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$total_looks = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

$conn->close();

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Atualizar perfil
    if (isset($_POST['atualizar_perfil'])) {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($nome) || empty($email)) {
            $erro = 'Preencha todos os campos.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'E-mail inválido.';
        } else {
            $conn = conectarDB();
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $usuario_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $erro = 'Este e-mail já está em uso.';
            } else {
                $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $nome, $email, $usuario_id);
                if ($stmt->execute()) {
                    $_SESSION['usuario_nome'] = $nome;
                    $_SESSION['usuario_email'] = $email;
                    $usuario['nome'] = $nome;
                    $usuario['email'] = $email;
                    $mensagem = 'Perfil atualizado com sucesso!';
                } else {
                    $erro = 'Erro ao atualizar perfil.';
                }
            }
            $stmt->close();
            $conn->close();
        }
    }

    // Atualizar foto
    if (isset($_POST['atualizar_foto'])) {
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/perfil/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

            $ext = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
            $nome_arquivo = 'perfil_' . $usuario_id . '_' . time() . '.' . $ext;
            $caminho_arquivo = $upload_dir . $nome_arquivo;

            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $caminho_arquivo)) {
                $conn = conectarDB();
                $stmt = $conn->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
                $stmt->bind_param("si", $caminho_arquivo, $usuario_id);
                if ($stmt->execute()) {
                    $usuario['foto_perfil'] = $caminho_arquivo;
                    $mensagem = 'Foto de perfil atualizada!';
                } else {
                    $erro = 'Erro ao salvar foto.';
                }
                $stmt->close();
                $conn->close();
            } else {
                $erro = 'Erro ao fazer upload da foto.';
            }
        } else {
            $erro = 'Selecione uma imagem.';
        }
    }

    // Alterar senha
    if (isset($_POST['alterar_senha'])) {
        $senha_atual = $_POST['senha_atual'] ?? '';
        $nova_senha = $_POST['nova_senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';

        if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
            $erro = 'Preencha todos os campos de senha.';
        } elseif ($nova_senha !== $confirmar_senha) {
            $erro = 'As novas senhas não coincidem.';
        } elseif (strlen($nova_senha) < 6) {
            $erro = 'A nova senha deve ter pelo menos 6 caracteres.';
        } else {
            $conn = conectarDB();
            $stmt = $conn->prepare("SELECT senha FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $senha_valida = password_verify($senha_atual, $row['senha']) || $row['senha'] === md5($senha_atual);

            if (!$senha_valida) {
                $erro = 'Senha atual incorreta.';
            } else {
                $nova_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                $stmt->bind_param("si", $nova_hash, $usuario_id);
                if ($stmt->execute()) {
                    $mensagem = 'Senha alterada com sucesso!';
                } else {
                    $erro = 'Erro ao alterar senha.';
                }
                $stmt->close();
            }
            $conn->close();
        }
    }
}

$pageTitle = "LOOKVAULT · Minha Conta";
$pageDescription = "Gerencie suas informações e preferências.";
include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/pages.css">

<main>
    <!-- PROFILE HERO -->
    <section class="profile-hero">
        <div class="container">
            <div style="padding-bottom: 0;">
                <span class="page-hero-eyebrow" style="margin-bottom: 24px;">
                    <i class="fas fa-user-circle"></i> Perfil
                </span>
            </div>
        </div>
    </section>

    <div class="container" style="margin-top: -80px; position: relative; z-index: 2;">
        <div class="profile-header-card animate-fade-up">
            <div class="profile-avatar-wrap">
                <div class="profile-avatar-large" onclick="document.getElementById('fotoInput').click()">
                    <?php if (!empty($usuario['foto_perfil']) && file_exists($usuario['foto_perfil'])): ?>
                        <img src="<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Foto de perfil" />
                    <?php else: ?>
                        <span><i class="fas fa-user"></i></span>
                    <?php endif; ?>
                    <div class="profile-avatar-overlay">
                        <i class="fas fa-camera"></i>
                        <span>Trocar</span>
                    </div>
                </div>
            </div>

            <div class="profile-header-info">
                <h1><?php echo htmlspecialchars($usuario['nome']); ?></h1>
                <p class="profile-header-email">
                    <i class="fas fa-envelope"></i>
                    <?php echo htmlspecialchars($usuario['email']); ?>
                </p>
                <div class="profile-header-badges">
                    <span class="profile-badge">
                        <i class="fas fa-calendar-alt"></i>
                        Desde <?php echo date('M Y', strtotime($usuario['data_cadastro'] ?? 'now')); ?>
                    </span>
                    <span class="profile-badge">
                        <i class="fas fa-star"></i>
                        Membro LOOKVAULT
                    </span>
                </div>
            </div>

            <div class="profile-header-actions">
                <a href="logout.php" class="btn-secondary">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>

        <!-- STATS ROW -->
        <div class="profile-stats-row animate-fade-up animate-delay-2">
            <div class="profile-stat">
                <div class="profile-stat-value"><?php echo $total_pecas; ?></div>
                <div class="profile-stat-label">Peças</div>
            </div>
            <div class="profile-stat">
                <div class="profile-stat-value"><?php echo $total_looks; ?></div>
                <div class="profile-stat-label">Looks criados</div>
            </div>
            <div class="profile-stat">
                <div class="profile-stat-value"><?php echo date('d/m'); ?></div>
                <div class="profile-stat-label">Último acesso</div>
            </div>
        </div>

        <!-- MENSAGENS -->
        <?php if (!empty($mensagem)): ?>
        <div class="mensagem-sucesso animate-fade-up">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($mensagem); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
        <div class="mensagem-erro animate-fade-up">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?>
        </div>
        <?php endif; ?>

        <!-- SETTINGS GRID -->
        <div class="settings-grid animate-fade-up animate-delay-3">
            <!-- EDITAR PERFIL -->
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-icon"><i class="fas fa-user-edit"></i></div>
                    <div>
                        <h3 class="settings-panel-title">Informações Pessoais</h3>
                        <p class="settings-panel-subtitle">Atualize seu nome e e-mail</p>
                    </div>
                </div>

                <form method="POST">
                    <div class="form-field">
                        <label for="nome">Nome completo</label>
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required />
                    </div>
                    <div class="form-field">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required />
                    </div>
                    <button type="submit" name="atualizar_perfil" class="btn-primary btn-block">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </form>

                <form method="POST" enctype="multipart/form-data" style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--lv-cinza-200);">
                    <input type="file" id="fotoInput" name="foto_perfil" accept="image/*" style="display: none;" onchange="this.form.submit()" />
                    <button type="button" class="btn-secondary btn-block" onclick="document.getElementById('fotoInput').click()">
                        <i class="fas fa-camera"></i> Alterar Foto de Perfil
                    </button>
                    <input type="hidden" name="atualizar_foto" value="1" />
                </form>
            </div>

            <!-- ALTERAR SENHA -->
            <div class="settings-panel">
                <div class="settings-panel-header">
                    <div class="settings-panel-icon"><i class="fas fa-lock"></i></div>
                    <div>
                        <h3 class="settings-panel-title">Segurança</h3>
                        <p class="settings-panel-subtitle">Altere sua senha</p>
                    </div>
                </div>

                <form method="POST">
                    <div class="form-field">
                        <label for="senha_atual">Senha atual</label>
                        <input type="password" id="senha_atual" name="senha_atual" placeholder="Digite sua senha atual" required />
                    </div>
                    <div class="form-field">
                        <label for="nova_senha">Nova senha</label>
                        <input type="password" id="nova_senha" name="nova_senha" placeholder="Mínimo 6 caracteres" required />
                    </div>
                    <div class="form-field">
                        <label for="confirmar_senha">Confirmar nova senha</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Repita a nova senha" required />
                    </div>
                    <button type="submit" name="alterar_senha" class="btn-primary btn-block">
                        <i class="fas fa-key"></i> Alterar Senha
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>