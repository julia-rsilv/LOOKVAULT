<?php
session_start();
require_once 'configdatabase.php';

date_default_timezone_set('America/Sao_Paulo');

// Buscar foto de perfil se logado
$foto_perfil = '';
$usuario_nome = 'Visitante';
$logado = false;

if (isset($_SESSION['usuario_id'])) {
    $logado = true;
    $usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
    
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

// Menu atualizado com links REAIS para as páginas do site
$menu_items = [
    ['icon' => 'ph-house', 'label' => 'Início', 'link' => 'index.php', 'active' => true],
    ['icon' => 'ph-tshirt', 'label' => 'Guarda-Roupa', 'link' => 'guarda-roupa.php', 'active' => false],
    ['icon' => 'ph-magic-wand', 'label' => 'Combinações', 'link' => 'combinacoes.php', 'active' => false],
    ['icon' => 'ph-chart-bar', 'label' => 'Estatísticas', 'link' => 'estatisticas.php', 'active' => false],
    ['icon' => 'ph-plus-circle', 'label' => 'Adicionar Peça', 'link' => 'cadastro.php', 'active' => false],
    ['icon' => 'ph-user', 'label' => 'Minha Conta', 'link' => 'minha-conta.php', 'active' => false],
    ['icon' => 'ph-info', 'label' => 'Sobre', 'link' => 'sobre.php', 'active' => false],
    ['icon' => 'ph-headset', 'label' => 'Assistência', 'link' => 'assistencia.php', 'active' => false],
];

// Marcas parceiras
$brands = [
    ['name' => 'Zara', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/f/fd/Zara_Logo.svg'],
    ['name' => 'H&M', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/5/53/H%26M-Logo.svg'],
    ['name' => 'Nike', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/a/a6/Logo_NIKE.svg'],
    ['name' => 'Adidas', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/2/20/Adidas_Logo.svg'],
    ['name' => "Levi's", 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/7/75/Levi%27s_logo.svg'],
    ['name' => 'Gucci', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/c/c8/Gucci_Logo.svg'],
    ['name' => 'Prada', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/2/28/Prada-Logo.svg'],
    ['name' => 'Chanel', 'logo' => 'https://upload.wikimedia.org/wikipedia/commons/6/6c/Chanel_logo.svg'],
];

$bg_image = 'https://images.unsplash.com/photo-1558769132-cb1aea458c5e?q=80&w=2574&auto=format&fit=crop';
$logo_image = 'uploads/logo.png';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?php echo $_COOKIE['theme'] ?? 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LookVault - Seu Guarda-Roupa Inteligente</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        html, body { min-height: 100%; background-color: #f5f0ea; color: #fff; scroll-behavior: smooth; }

        .app-container { display: flex; align-items: flex-start; width: 100%; position: relative; z-index: 5; }

        /* SIDEBAR */
        .sidebar {
            width: 270px; background-color: #0a0a0a;
            display: flex; flex-direction: column;
            padding: 30px 0; z-index: 10; flex-shrink: 0;
            height: calc(100vh - 30px); align-self: flex-start;
            clip-path: polygon(0 0, 100% 0, 100% calc(100% - 45px), calc(100% - 45px) 100%, 45px 100%, 0 calc(100% - 45px));
        }
        .logo-container { display: flex; justify-content: center; align-items: center; margin-bottom: 40px; padding: 0 20px; }
        .logo-container img { width: 100%; max-width: 110px; height: auto; object-fit: contain; }
        .menu { display: flex; flex-direction: column; gap: 2px; padding: 0 15px; overflow-y: auto; }
        .menu::-webkit-scrollbar { width: 0; }
        .menu a {
            text-decoration: none; color: #8a8a8a;
            padding: 10px 14px; border-radius: 8px;
            display: flex; align-items: center; gap: 12px;
            font-size: 13.5px; font-weight: 400;
            transition: all 0.3s ease;
        }
        .menu a i { font-size: 18px; }
        .menu a:hover { color: #fff; background-color: rgba(255,255,255,0.05); }
        .menu a.active { background-color: #262626; color: #fff; font-weight: 500; }

        /* MAIN CONTENT */
        .main-content { flex: 1; position: relative; display: flex; flex-direction: column; overflow: hidden; height: calc(100vh - 80px); }
        .main-content::after {
            content: ''; position: absolute; inset: 0;
            background-image: url('<?php echo $bg_image; ?>');
            background-size: cover; background-position: center; z-index: 1;
        }
        .main-content::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(90deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.55) 45%, rgba(0,0,0,0.65) 100%);
            z-index: 2;
        }

        /* TOP BAR */
        .top-bar { position: relative; z-index: 3; display: flex; justify-content: space-between; align-items: center; padding: 25px 45px; }
        .search-container { display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.25); padding-bottom: 10px; width: 420px; max-width: 45%; }
        .search-container i { color: #b0b0b0; font-size: 17px; }
        .search-container input { background: transparent; border: none; color: #fff; width: 100%; font-size: 13px; outline: none; font-weight: 300; }
        .search-container input::placeholder { color: #9a9a9a; }

        .user-actions { display: flex; align-items: center; gap: 22px; }
        .user-actions a, .user-actions button {
            background: none; border: none; color: #fff; font-size: 23px;
            cursor: pointer; transition: color 0.3s, transform 0.3s;
            display: flex; align-items: center; text-decoration: none;
        }
        .user-actions a:hover, .user-actions button:hover { color: #d4c5b0; transform: scale(1.05); }

        .profile-pic {
            width: 38px; height: 38px; background-color: #333;
            border-radius: 50%; display: flex; justify-content: center; align-items: center;
            font-size: 18px; color: #fff; border: 1px solid rgba(255,255,255,0.25);
            cursor: pointer; transition: border-color 0.3s; overflow: hidden;
        }
        .profile-pic img { width: 100%; height: 100%; object-fit: cover; }
        .profile-pic:hover { border-color: #d4c5b0; }

        .login-btn {
            background-color: #f5f0ea !important; color: #1a1a1a !important;
            padding: 8px 20px !important; border-radius: 30px !important;
            font-size: 13px !important; font-weight: 500 !important;
            gap: 6px !important;
        }
        .login-btn:hover { background-color: #fff !important; }

        /* HERO */
        .hero { position: relative; z-index: 3; flex: 1; display: flex; align-items: center; padding-left: 75px; padding-bottom: 40px; }
        .hero-text { max-width: 680px; }
        .subtitle {
            font-family: 'Playfair Display', serif; font-size: 15px;
            letter-spacing: 5px; color: #d4c5b0; display: block;
            margin-bottom: 20px; font-weight: 400; text-transform: uppercase;
        }
        .hero-text h1 {
            font-family: 'Playfair Display', serif; font-size: 72px;
            line-height: 1.05; font-weight: 600; color: #fff;
            margin-bottom: 25px; letter-spacing: 1px;
        }
        .hero-text h1 i { font-weight: 400; font-style: italic; }
        .hero-text p { font-size: 15.5px; line-height: 1.65; color: #c8c8c8; font-weight: 300; margin-bottom: 40px; max-width: 500px; }

        .cta-button {
            background-color: #f5f0ea; color: #1a1a1a; border: none;
            padding: 16px 38px; border-radius: 30px; font-size: 14px; font-weight: 500;
            cursor: pointer; display: inline-flex; align-items: center; gap: 12px;
            transition: transform 0.3s, background-color 0.3s;
            text-decoration: none;
        }
        .cta-button:hover { background-color: #ffffff; transform: translateX(5px); }

        /* FEATURES */
        .features-section { background-color: #f5f0ea; color: #1a1a1a; position: relative; z-index: 6; }
        .features-container {
            display: grid; grid-template-columns: repeat(4, 1fr);
            background-color: #f5f0ea; padding: 60px 60px 45px; gap: 30px;
        }
        .feature-item {
            display: flex; flex-direction: column; align-items: center;
            text-align: center; gap: 16px; color: #1a1a1a;
            padding: 10px; transition: transform 0.3s;
        }
        .feature-item:hover { transform: translateY(-5px); }
        .feature-icon {
            width: 52px; height: 52px; border: 1px solid rgba(138,122,102,0.5);
            border-radius: 50%; display: flex; justify-content: center;
            align-items: center; color: #8a7a66; flex-shrink: 0;
        }
        .feature-icon svg { width: 24px; height: 24px; }
        .feature-text h3 { font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 600; margin-bottom: 6px; }
        .feature-text p { font-size: 12.5px; color: #6b6b6b; line-height: 1.5; font-weight: 300; }

        /* CARROSSEL */
        .brands-carousel { background-color: #1a1a1a; width: 100%; padding: 30px 0; overflow: hidden; position: relative; z-index: 6; }
        .brands-track { display: flex; align-items: center; width: max-content; animation: scroll-brands 30s linear infinite; }
        .brands-group { display: flex; align-items: center; gap: 80px; padding-right: 80px; flex-shrink: 0; }
        .brands-carousel:hover .brands-track { animation-play-state: paused; }
        .brand-item { flex-shrink: 0; display: flex; align-items: center; justify-content: center; height: 45px; opacity: 0.55; transition: opacity 0.3s, transform 0.3s; filter: brightness(0) invert(1); }
        .brand-item:hover { opacity: 1; transform: scale(1.08); }
        .brand-item img { height: 100%; width: auto; max-width: 140px; object-fit: contain; }
        @keyframes scroll-brands { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        /* CATEGORIAS */
        .categories-container { padding: 40px 60px 100px; max-width: 1400px; margin: 0 auto; }
        .categories-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 45px; flex-wrap: wrap; gap: 20px; }
        .header-left .subtitle { color: #8a7a66; font-size: 12px; letter-spacing: 4px; margin-bottom: 10px; }
        .header-left h2 { font-family: 'Playfair Display', serif; font-size: 38px; color: #1a1a1a; font-weight: 700; }
        .see-all { color: #1a1a1a; text-decoration: none; font-size: 13.5px; font-weight: 500; border-bottom: 1px solid #1a1a1a; padding-bottom: 3px; transition: color 0.3s; }
        .see-all:hover { color: #8a7a66; border-color: #8a7a66; }

        .categories-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; }
        .category-card {
            position: relative; height: 280px; border-radius: 12px;
            overflow: hidden; cursor: pointer; box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.4s, box-shadow 0.4s; text-decoration: none;
            display: block;
        }
        .category-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
        .category-card img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s; }
        .category-card:hover img { transform: scale(1.08); }
        .category-card .overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0.1) 100%);
            display: flex; flex-direction: column; justify-content: flex-end;
            align-items: flex-start; padding: 25px; gap: 12px;
        }
        .card-icon { width: 28px; height: 28px; color: #d4c5b0; }
        .category-card .overlay span { color: #fff; font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 600; }

        /* RESPONSIVIDADE */
        @media (max-width: 1200px) {
            .hero-text h1 { font-size: 58px; }
            .subtitle { font-size: 13px; letter-spacing: 4px; }
            .hero { padding-left: 50px; }
            .top-bar { padding: 25px 35px; }
            .sidebar { width: 240px; }
            .features-container { padding: 50px 35px 40px; gap: 20px; }
            .categories-container { padding: 30px 40px 80px; }
            .header-left h2 { font-size: 32px; }
        }
        @media (max-width: 1024px) {
            .features-container { grid-template-columns: repeat(2, 1fr); gap: 35px; }
            .categories-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 820px) {
            .sidebar { width: 80px; padding: 30px 0; }
            .logo-container img { max-width: 50px; }
            .menu a { justify-content: center; padding: 14px 0; font-size: 0; gap: 0; }
            .menu a i { font-size: 22px; }
            .hero-text h1 { font-size: 44px; }
            .top-bar { padding: 20px; }
            .search-container { width: 100%; max-width: 60%; }
            .features-container { padding: 45px 30px 35px; }
            .categories-container { padding: 25px 30px 70px; }
            .header-left h2 { font-size: 28px; }
            .category-card { height: 240px; }
        }
        @media (max-width: 560px) {
            .sidebar { display: none; }
            .hero { padding: 30px; justify-content: center; text-align: center; }
            .hero-text { text-align: center; }
            .hero-text h1 { font-size: 34px; }
            .hero-text p { margin: 0 auto 35px; }
            .cta-button { margin: 0 auto; }
            .features-container { grid-template-columns: 1fr; padding: 40px 25px 30px; }
            .categories-grid { grid-template-columns: 1fr; }
            .categories-container { padding: 20px 25px 60px; }
            .categories-header { flex-direction: column; align-items: flex-start; }
            .header-left h2 { font-size: 26px; }
        }
    </style>
</head>
<body>

<div class="app-container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="logo-container">
            <img src="<?php echo htmlspecialchars($logo_image); ?>?v=1" alt="Logo LookVault">
        </div>
        <nav class="menu">
            <?php foreach ($menu_items as $item): ?>
                <?php if ($item['label'] === 'Minha Conta' && !$logado): ?>
                    <!-- Não mostrar Minha Conta se não estiver logado -->
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars($item['link']); ?>" class="<?php echo $item['active'] ? 'active' : ''; ?>">
                        <i class="ph <?php echo htmlspecialchars($item['icon']); ?>"></i>
                        <?php echo htmlspecialchars($item['label']); ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content" id="inicio">
        <header class="top-bar">
            <div class="search-container">
                <i class="ph ph-magnifying-glass"></i>
                <input type="text" placeholder="Busque por peças, marcas ou estilos..." aria-label="Pesquisar">
            </div>
            <div class="user-actions">
                <?php if ($logado): ?>
                    <a href="guarda-roupa.php" title="Meu Guarda-Roupa"><i class="ph ph-tshirt"></i></a>
                    <a href="combinacoes.php" title="Combinações"><i class="ph ph-magic-wand"></i></a>
                    <a href="minha-conta.php" class="profile-pic" title="Minha Conta">
                        <?php if (!empty($foto_perfil) && file_exists($foto_perfil)): ?>
                            <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Perfil">
                        <?php else: ?>
                            <i class="ph ph-user"></i>
                        <?php endif; ?>
                    </a>
                    <a href="logout.php" title="Sair" style="font-size: 18px;"><i class="ph ph-sign-out"></i></a>
                <?php else: ?>
                    <a href="login.php" class="login-btn"><i class="ph ph-sign-in"></i> Login</a>
                    <a href="cadastro-usuario.php" class="login-btn"><i class="ph ph-user-plus"></i> Criar Conta</a>
                <?php endif; ?>
            </div>
        </header>

        <section class="hero">
            <div class="hero-text">
                <span class="subtitle">LOOKVAULT</span>
                <h1>SEU GUARDA-ROUPA DIGITAL</h1>
                <p>
                    Organize suas peças, receba sugestões de looks com
                    Inteligência Artificial, planeje suas roupas pelo clima
                    e contribua para um consumo mais consciente e sustentável.
                </p>
                <?php if ($logado): ?>
                    <a href="guarda-roupa.php" class="cta-button">
                        Acessar meu Guarda-Roupa <i class="ph ph-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <a href="cadastro-usuario.php" class="cta-button">
                        Começar agora <i class="ph ph-arrow-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>

<!-- FEATURES -->
<section class="features-section">
    <div class="features-container">
        <div class="feature-item">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 6a2 2 0 1 0-2-2c0 1.1.9 2 2 2z"></path>
                    <path d="M12 6v2"></path>
                    <path d="M12 8l9.6 5.3a1 1 0 0 1 .4 1.4l-2.6 4.6a1 1 0 0 1-1.4.4L12 16l-6 3.7a1 1 0 0 1-1.4-.4l-2.6-4.6a1 1 0 0 1 .4-1.4L12 8z"></path>
                </svg>
            </div>
            <div class="feature-text">
                <h3>Organize seu guarda-roupa</h3>
                <p>Tenha todas as suas peças categorizadas e sempre à mão.</p>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div class="feature-text">
                <h3>Monte seus looks</h3>
                <p>Crie combinações, salve seus favoritos e planeje sua semana.</p>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
            </div>
            <div class="feature-text">
                <h3>Descubra novas lojas</h3>
                <p>Encontre marcas e peças que combinam com o seu estilo.</p>
            </div>
        </div>
        <div class="feature-item">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
            </div>
            <div class="feature-text">
                <h3>Acompanhe seu estilo</h3>
                <p>Veja suas estatísticas, entenda seus hábitos e evolua.</p>
            </div>
        </div>
    </div>

    <!-- CARROSSEL DE MARCAS -->
    <div class="brands-carousel">
        <div class="brands-track">
            <div class="brands-group">
                <?php foreach ($brands as $brand): ?>
                    <div class="brand-item">
                        <img src="<?php echo htmlspecialchars($brand['logo']); ?>" alt="<?php echo htmlspecialchars($brand['name']); ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="brands-group" aria-hidden="true">
                <?php foreach ($brands as $brand): ?>
                    <div class="brand-item">
                        <img src="<?php echo htmlspecialchars($brand['logo']); ?>" alt="">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- CATEGORIAS -->
    <div class="categories-container" id="categorias">
        <div class="categories-header">
            <div class="header-left">
                <span class="subtitle">CATEGORIAS EM DESTAQUE</span>
                <h2>EXPLORE POR ESTILO</h2>
            </div>
            <a href="guarda-roupa.php" class="see-all">Ver todas as categorias &rarr;</a>
        </div>

        <div class="categories-grid">
            <a href="guarda-roupa.php" class="category-card">
                <img src="https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=800&auto=format&fit=crop" alt="Roupas">
                <div class="overlay">
                    <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.38 3.46L16 2a4 4 0 01-8 0L3.62 3.46a2 2 0 00-1.34 2.23l.58 3.47a1 1 0 00.99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 002-2V10h2.15a1 1 0 00.99-.84l.58-3.47a2 2 0 00-1.34-2.23z"></path>
                    </svg>
                    <span>Roupas</span>
                </div>
            </a>
            <a href="guarda-roupa.php" class="category-card">
                <img src="https://images.unsplash.com/photo-1549298916-b41d501d3772?q=80&w=800&auto=format&fit=crop" alt="Calçados">
                <div class="overlay">
                    <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 18h20v2H2z"></path>
                        <path d="M4 16l2-6h4l2 2h6l4 2v2H4z"></path>
                    </svg>
                    <span>Calçados</span>
                </div>
            </a>
            <a href="guarda-roupa.php" class="category-card">
                <img src="https://images.unsplash.com/photo-1584917865442-de89df76afd3?q=80&w=800&auto=format&fit=crop" alt="Bolsas">
                <div class="overlay">
                    <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <span>Bolsas</span>
                </div>
            </a>
            <a href="guarda-roupa.php" class="category-card">
                <img src="https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?q=80&w=800&auto=format&fit=crop" alt="Acessórios">
                <div class="overlay">
                    <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 3h12l4 6-10 13L2 9z"></path>
                        <path d="M11 3L8 9l4 13 4-13-3-6"></path>
                        <path d="M2 9h20"></path>
                    </svg>
                    <span>Acessórios</span>
                </div>
            </a>
            <a href="cadastro.php" class="category-card">
                <img src="https://images.unsplash.com/photo-1616683693504-3ea7e9ad6fec?q=80&w=800&auto=format&fit=crop" alt="Beleza">
                <div class="overlay">
                    <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 2v7.31"></path>
                        <path d="M14 9.3V1.99"></path>
                        <path d="M8.5 2h7"></path>
                        <path d="M14 9.3a6.5 6.5 0 1 1-4 0"></path>
                        <path d="M5.58 16.5h12.85"></path>
                    </svg>
                    <span>Beleza</span>
                </div>
            </a>
            <a href="combinacoes.php" class="category-card">
                <img src="uploads/pecas/look.jfif" alt="Look">
                <div class="overlay">
                    <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16v16H4z"></path>
                        <path d="M4 8h16"></path>
                        <path d="M4 12h16"></path>
                        <path d="M4 16h16"></path>
                    </svg>
                    <span>Looks Salvos</span>
                </div>
            </a>
        </div>
    </div>
</section>

</body>
</html>