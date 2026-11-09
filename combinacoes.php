<?php
session_start();
require_once 'configdatabase.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
$usuario_id = $_SESSION['usuario_id'];

$sql = "SELECT * FROM looks WHERE usuario_id = ? ORDER BY data_criacao DESC";
$result = executarQuery($sql, [$usuario_id]);

$looks = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sql_pecas = "SELECT p.* FROM pecas p INNER JOIN look_pecas lp ON p.id = lp.peca_id WHERE lp.look_id = ?";
        $result_pecas = executarQuery($sql_pecas, [$row['id']]);
        $pecas_look = [];
        if ($result_pecas && $result_pecas->num_rows > 0) {
            while ($peca = $result_pecas->fetch_assoc()) $pecas_look[] = $peca;
        }
        $row['pecas'] = $pecas_look;
        $looks[] = $row;
    }
}

$pageTitle = "LOOKVAULT · Looks & Combinações";
$pageDescription = "Crie e organize combinações de looks incríveis.";
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
                        <i class="fas fa-magic"></i> Produções
                    </span>
                    <h1 class="page-hero-title">
                        Looks & <em>Combinações</em>
                    </h1>
                    <p class="page-hero-subtitle">
                        Monte produções únicas com suas peças favoritas e organize looks para cada ocasião.
                    </p>
                </div>
                <div class="page-hero-actions">
                    <a href="criar-look.php" class="btn-hero btn-hero-primary">
                        <i class="fas fa-plus"></i> Criar Look
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php if (isset($_GET['sucesso'])): ?>
    <div class="container" style="padding-top: 24px;">
        <div class="mensagem-sucesso"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['sucesso']); ?></div>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['erro'])): ?>
    <div class="container" style="padding-top: 24px;">
        <div class="mensagem-erro"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['erro']); ?></div>
    </div>
    <?php endif; ?>

    <!-- TOOLBAR -->
    <?php if (!empty($looks)): ?>
    <div class="toolbar">
        <div class="container">
            <div class="toolbar-inner">
                <div class="toolbar-filters" style="flex: 1; justify-content: flex-start;">
                    <button class="chip active" data-filter="all">Todos</button>
                    <button class="chip" data-filter="Casual">Casual</button>
                    <button class="chip" data-filter="Social">Social</button>
                    <button class="chip" data-filter="Esporte">Esporte</button>
                    <button class="chip" data-filter="Festa">Festa</button>
                    <button class="chip" data-filter="Trabalho">Trabalho</button>
                    <button class="chip" data-filter="Praia">Praia</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- GRID -->
    <section class="wardrobe-section">
        <div class="container">
            <?php if (empty($looks)): ?>
                <div class="empty-premium animate-fade-up">
                    <div class="empty-premium-icon">
                        <i class="fas fa-magic"></i>
                    </div>
                    <h3>Nenhum look criado ainda</h3>
                    <p>Comece a combinar suas peças e crie looks incríveis para todas as ocasiões.</p>
                    <a href="criar-look.php" class="btn-primary">
                        <i class="fas fa-plus"></i> Criar Meu Primeiro Look
                    </a>
                </div>
            <?php else: ?>
                <div class="section-heading">
                    <h2>
                        <i class="fas fa-images"></i>
                        Meus Looks
                    </h2>
                    <span class="section-heading-count">
                        <?php echo count($looks); ?> <?php echo count($looks) === 1 ? 'look' : 'looks'; ?>
                    </span>
                </div>

                <div class="looks-grid-new" id="looksGrid">
                    <?php foreach ($looks as $index => $look): ?>
                    <article class="look-card-new animate-fade-up animate-delay-<?php echo ($index % 4) + 1; ?>"
                             data-ocasiao="<?php echo htmlspecialchars($look['ocasiao']); ?>">
                        <div class="look-image-new">
                            <?php if (!empty($look['pecas']) && !empty($look['pecas'][0]['foto']) && file_exists($look['pecas'][0]['foto'])): ?>
                                <img src="<?php echo htmlspecialchars($look['pecas'][0]['foto']); ?>" alt="<?php echo htmlspecialchars($look['nome']); ?>" loading="lazy" />
                            <?php else: ?>
                                <img src="https://via.placeholder.com/400x500/f5f0ea/b8a68c?text=<?php echo urlencode($look['nome']); ?>" alt="<?php echo htmlspecialchars($look['nome']); ?>" loading="lazy" />
                            <?php endif; ?>
                            <span class="look-badge-ocasiao"><?php echo htmlspecialchars($look['ocasiao'] ?: 'Geral'); ?></span>
                        </div>

                        <div class="look-body">
                            <h3 class="look-name"><?php echo htmlspecialchars($look['nome']); ?></h3>

                            <div class="look-pecas-chips">
                                <?php
                                $total = count($look['pecas']);
                                foreach (array_slice($look['pecas'], 0, 3) as $peca):
                                ?>
                                    <span class="look-chip"><?php echo htmlspecialchars($peca['nome']); ?></span>
                                <?php endforeach; ?>
                                <?php if ($total > 3): ?>
                                    <span class="look-chip look-chip-more">+<?php echo $total - 3; ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="look-actions-new">
                                <a href="criar-look.php?id=<?php echo $look['id']; ?>" class="look-action-btn edit">
                                    <i class="fas fa-pen"></i> Editar
                                </a>
                                <button class="look-action-btn delete" onclick="excluirLook(<?php echo $look['id']; ?>)">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
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
document.querySelectorAll('.chip').forEach(chip => {
    chip.addEventListener('click', function () {
        const filter = this.dataset.filter;
        document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.querySelectorAll('.look-card-new').forEach(item => {
            const ocasiao = item.dataset.ocasiao || '';
            item.style.display = (filter === 'all' || ocasiao === filter) ? '' : 'none';
        });
    });
});

function excluirLook(id) {
    if (confirm('Tem certeza que deseja excluir este look?')) {
        window.location.href = 'excluir-look.php?id=' + id;
    }
}
</script>

<?php include 'includes/footer.php'; ?>