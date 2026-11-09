<?php
session_start();
require_once 'configdatabase.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
$usuario_id = (int) $_SESSION['usuario_id'];
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'favoritar') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) { http_response_code(403); exit('Solicitação inválida.'); }
    $peca_id = (int)($_POST['peca_id'] ?? 0);
    $acao_fav = $_POST['favorito'] ?? 'adicionar';
    $dono = executarQuery("SELECT id FROM pecas WHERE id=? AND usuario_id=?", [$peca_id, $usuario_id]);
    if ($dono && $dono->num_rows) {
        if ($acao_fav === 'remover') executarQuery("DELETE FROM favoritos_pecas WHERE usuario_id=? AND peca_id=?", [$usuario_id, $peca_id]);
        else executarQuery("INSERT IGNORE INTO favoritos_pecas(usuario_id,peca_id) VALUES(?,?)", [$usuario_id, $peca_id]);
    }
    header('Location: guarda-roupa.php'); exit;
}

$sql = "SELECT p.*, EXISTS(SELECT 1 FROM favoritos_pecas f WHERE f.usuario_id=p.usuario_id AND f.peca_id=p.id) AS favorito FROM pecas p WHERE p.usuario_id = ? ORDER BY p.data_cadastro DESC";
$result = executarQuery($sql, [$usuario_id]);
$pecas = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) $pecas[] = $row;
}

$total_pecas = count($pecas);

$pageTitle = "LOOKVAULT · Meu Guarda-Roupa";
$pageDescription = "Gerencie todas as suas peças.";
include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/pages.css">

<main>
    <!-- HERO -->
    <section class="page-hero">
        <div class="container">
            <div class="page-hero-inner">
                <div class="page-hero-text">
                    <span class="page-hero-eyebrow">
                        <i class="fas fa-tshirt"></i> Sua coleção
                    </span>
                    <h1 class="page-hero-title">
                        Meu <em>Guarda-Roupa</em>
                    </h1>
                    <p class="page-hero-subtitle">
                        Todas as suas peças organizadas em um só lugar. Adicione, edite e acompanhe o uso de cada item da sua coleção.
                    </p>
                </div>
                <div class="page-hero-actions">
                    <a href="cadastro.php" class="btn-hero btn-hero-primary">
                        <i class="fas fa-plus"></i> Adicionar Peça
                    </a>
                    <a href="estatisticas.php" class="btn-hero btn-hero-ghost">
                        <i class="fas fa-chart-line"></i> Ver Estatísticas
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php if (isset($_GET['sucesso'])): ?>
    <div class="container" style="padding-top: 24px;">
        <div class="mensagem-sucesso">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['sucesso']); ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['erro'])): ?>
    <div class="container" style="padding-top: 24px;">
        <div class="mensagem-erro">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['erro']); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- TOOLBAR -->
    <?php if (!empty($pecas)): ?>
    <div class="toolbar">
        <div class="container">
            <div class="toolbar-inner">
                <div class="toolbar-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Pesquisar por nome, marca ou cor..." onkeyup="filtrarPecas()" />
                </div>
                <div class="toolbar-filters">
                    <button class="chip active" data-filter="all">Todas</button>
                    <button class="chip" data-filter="Camisas">Camisas</button>
                    <button class="chip" data-filter="Calças">Calças</button>
                    <button class="chip" data-filter="Sapatos">Sapatos</button>
                    <button class="chip" data-filter="Acessórios">Acessórios</button>
                    <button class="chip" data-filter="Blazers">Blazers</button>
                    <button class="chip" data-filter="Vestidos">Vestidos</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- GRID -->
    <section class="wardrobe-section">
        <div class="container">
            <?php if (empty($pecas)): ?>
                <div class="empty-premium animate-fade-up">
                    <div class="empty-premium-icon">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <h3>Seu guarda-roupa está vazio</h3>
                    <p>Comece adicionando suas primeiras peças e descubra uma nova forma de organizar seu estilo.</p>
                    <a href="cadastro.php" class="btn-primary">
                        <i class="fas fa-plus"></i> Adicionar Primeira Peça
                    </a>
                </div>
            <?php else: ?>
                <div class="section-heading">
                    <h2>
                        <i class="fas fa-layer-group"></i>
                        Todas as peças
                    </h2>
                    <span class="section-heading-count" id="contadorPecas">
                        <?php echo $total_pecas; ?> <?php echo $total_pecas === 1 ? 'peça' : 'peças'; ?>
                    </span>
                </div>

                <div class="wardrobe-grid-new" id="pecasGrid">
                    <?php foreach ($pecas as $index => $peca): ?>
                    <article class="peca-card animate-fade-up animate-delay-<?php echo ($index % 4) + 1; ?>"
                             data-categoria="<?php echo htmlspecialchars($peca['categoria']); ?>"
                             data-nome="<?php echo htmlspecialchars(strtolower($peca['nome'] . ' ' . $peca['marca'] . ' ' . $peca['cor'])); ?>">
                        <div class="peca-card-image">
                            <?php if (!empty($peca['foto']) && file_exists($peca['foto'])): ?>
                                <img src="<?php echo htmlspecialchars($peca['foto']); ?>" alt="<?php echo htmlspecialchars($peca['nome']); ?>" loading="lazy" />
                            <?php else: ?>
                                <img src="https://via.placeholder.com/400x500/f5f0ea/b8a68c?text=<?php echo urlencode($peca['nome']); ?>" alt="<?php echo htmlspecialchars($peca['nome']); ?>" loading="lazy" />
                            <?php endif; ?>

                            <span class="peca-card-category"><?php echo htmlspecialchars($peca['categoria']); ?></span>

                            <div class="peca-card-actions">
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="acao" value="favoritar">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="peca_id" value="<?php echo (int)$peca['id']; ?>">
                                    <input type="hidden" name="favorito" value="<?php echo !empty($peca['favorito']) ? 'remover' : 'adicionar'; ?>">
                                    <button type="submit" class="peca-action-btn" data-tooltip="<?php echo !empty($peca['favorito']) ? 'Remover dos favoritos' : 'Adicionar aos favoritos'; ?>">
                                        <i class="<?php echo !empty($peca['favorito']) ? 'fas' : 'far'; ?> fa-heart"></i>
                                    </button>
                                </form>
                                <button class="peca-action-btn use"
                                        data-tooltip="Registrar uso"
                                        onclick="registrarUso(<?php echo $peca['id']; ?>, this)">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="peca-action-btn edit"
                                        data-tooltip="Editar"
                                        onclick="editarPeca(<?php echo $peca['id']; ?>)">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="peca-action-btn delete"
                                        data-tooltip="Excluir"
                                        onclick="excluirPeca(<?php echo $peca['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="peca-card-body">
                            <span class="peca-card-brand"><?php echo htmlspecialchars($peca['marca'] ?: 'Sem marca'); ?></span>
                            <h3 class="peca-card-name"><?php echo htmlspecialchars($peca['nome']); ?></h3>

                            <div class="peca-card-meta">
                                <span class="peca-card-color">
                                    <span class="peca-color-dot" style="background: <?php echo htmlspecialchars(strtolower($peca['cor'])); ?>;"></span>
                                    <?php echo htmlspecialchars($peca['cor']); ?>
                                </span>
                                <span class="peca-card-price">R$ <?php echo number_format($peca['valor'], 2, ',', '.'); ?></span>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<script>
function filtrarPecas() {
    const termo = document.getElementById('searchInput').value.toLowerCase().trim();
    const filtro = document.querySelector('.chip.active')?.dataset.filter || 'all';
    const itens = document.querySelectorAll('.peca-card');

    itens.forEach(item => {
        const matchBusca = item.dataset.nome.includes(termo);
        const matchFiltro = filtro === 'all' || item.dataset.categoria === filtro;
        const mostrar = matchBusca && matchFiltro;
        item.style.display = mostrar ? '' : 'none';
    });
}

function excluirPeca(id) {
    if (confirm('Tem certeza que deseja excluir esta peça? Esta ação não pode ser desfeita.')) {
        window.location.href = 'excluir-peca.php?id=' + id;
    }
}

function editarPeca(id) {
    window.location.href = 'editar-peca.php?id=' + id;
}

function registrarUso(id, btn) {
    const icon = btn.querySelector('i');
    const originalClass = icon.className;

    icon.className = 'fas fa-spinner fa-spin';

    fetch('registrar-uso.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'peca_id=' + id
    })
    .then(r => r.json())
    .then(d => {
        if (d.sucesso) {
            icon.className = 'fas fa-check';
            btn.style.background = '#2d7a4f';
            btn.style.color = '#fff';
            setTimeout(() => {
                icon.className = originalClass;
                btn.style.background = '';
                btn.style.color = '';
            }, 2000);
        } else {
            alert('Erro: ' + d.mensagem);
            icon.className = originalClass;
        }
    })
    .catch(() => {
        alert('Erro ao registrar uso');
        icon.className = originalClass;
    });
}

document.querySelectorAll('.chip').forEach(chip => {
    chip.addEventListener('click', function () {
        document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        filtrarPecas();
    });
});
</script>

<?php include 'includes/footer.php'; ?>