<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../configdatabase.php';

$logado = isset($_SESSION['usuario_id']);
$usuario_nome = $_SESSION['usuario_nome'] ?? '';
$foto_perfil = '';

if ($logado) {
    $conn = conectarDB();
    $sql = "SELECT foto_perfil FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $foto_perfil = $row['foto_perfil'] ?? '';
    }
    $stmt->close();
    $conn->close();
}

$pagina_atual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?php echo htmlspecialchars($_COOKIE['theme'] ?? 'light'); ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($pageTitle ?? 'LOOKVAULT'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription ?? 'Seu guarda-roupa digital inteligente.'); ?>" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <script src="assets/js/theme.js"></script>
</head>
<body>

<header class="header" id="header">
    <div class="container">
        <a href="index.php" class="logo">
            <i class="fas fa-wardrobe"></i> LOOKVAULT
        </a>

        <button class="menu-toggle" id="menuToggle" aria-label="Abrir menu">
            <i class="fas fa-bars"></i>
        </button>

        <nav class="nav" id="nav">
            <a href="index.php" class="<?php echo $pagina_atual === 'index.php' ? 'active' : ''; ?>">Início</a>
            <a href="guarda-roupa.php" class="<?php echo $pagina_atual === 'guarda-roupa.php' ? 'active' : ''; ?>">Guarda-Roupa</a>
            <a href="combinacoes.php" class="<?php echo $pagina_atual === 'combinacoes.php' ? 'active' : ''; ?>">Combinações</a>
            <a href="ferramentas.php" class="<?php echo $pagina_atual === 'ferramentas.php' ? 'active' : ''; ?>">Ferramentas</a>
            <a href="estatisticas.php" class="<?php echo $pagina_atual === 'estatisticas.php' ? 'active' : ''; ?>">Estatísticas</a>

            <?php if ($logado): ?>
                <a href="configuracoes.php" class="<?php echo $pagina_atual === 'configuracoes.php' ? 'active' : ''; ?>">Configurações</a>
                <a href="minha-conta.php" class="btn-nav">
                    <?php if (!empty($foto_perfil) && file_exists(__DIR__ . '/../' . $foto_perfil)): ?>
                        <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Perfil" class="avatar-nav" />
                    <?php else: ?>
                        <span class="avatar-nav-placeholder"><i class="fas fa-user"></i></span>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($usuario_nome); ?>
                </a>
            <?php else: ?>
                <a href="login.php" class="btn-nav">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('menuToggle');
    const nav = document.getElementById('nav');
    if (toggle && nav) {
        toggle.addEventListener('click', () => nav.classList.toggle('active'));
    }
});
</script>