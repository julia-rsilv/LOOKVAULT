<?php
session_start();
require_once 'configdatabase.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
$usuario_id = $_SESSION['usuario_id'];

$conn = conectarDB();

// Total de peças
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM pecas WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$total_pecas = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Peças usadas nos últimos 30 dias
$stmt = $conn->prepare("SELECT COUNT(DISTINCT p.id) as total FROM pecas p INNER JOIN uso_pecas u ON p.id = u.peca_id WHERE p.usuario_id = ? AND u.data_uso >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$mais_usadas = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Custo médio
$stmt = $conn->prepare("SELECT AVG(valor / COALESCE(uso_count, 1)) as custo_medio FROM (SELECT p.valor, COUNT(u.id) as uso_count FROM pecas p LEFT JOIN uso_pecas u ON p.id = u.peca_id WHERE p.usuario_id = ? GROUP BY p.id) as subquery");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$custo_medio = number_format($stmt->get_result()->fetch_assoc()['custo_medio'] ?? 0, 2, ',', '.');
$stmt->close();

// Valor total
$stmt = $conn->prepare("SELECT SUM(valor) as total FROM pecas WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$valor_total = number_format($stmt->get_result()->fetch_assoc()['total'] ?? 0, 2, ',', '.');
$stmt->close();

// Categorias
$stmt = $conn->prepare("SELECT categoria, COUNT(*) as total FROM pecas WHERE usuario_id = ? AND categoria IS NOT NULL AND categoria != '' GROUP BY categoria ORDER BY total DESC");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$categorias = [];
while ($row = $result->fetch_assoc()) $categorias[] = $row;
$stmt->close();

// Marcas
$stmt = $conn->prepare("SELECT marca, COUNT(*) as total FROM pecas WHERE usuario_id = ? AND marca IS NOT NULL AND marca != '' GROUP BY marca ORDER BY total DESC LIMIT 5");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$marcas = [];
while ($row = $result->fetch_assoc()) $marcas[] = $row;
$stmt->close();

// Atividades
$stmt = $conn->prepare("(SELECT 'Adicionou' as acao, nome as descricao, data_cadastro as data FROM pecas WHERE usuario_id = ?) UNION ALL (SELECT 'Criou look' as acao, nome as descricao, data_criacao as data FROM looks WHERE usuario_id = ?) ORDER BY data DESC LIMIT 5");
$stmt->bind_param("ii", $usuario_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$atividades = [];
while ($row = $result->fetch_assoc()) {
    $data = new DateTime($row['data']);
    $agora = new DateTime();
    $diff = $agora->diff($data);
    if ($diff->days == 0) {
        $tempo = ($diff->h == 0) ? $diff->i . ' min atrás' : $diff->h . 'h atrás';
    } elseif ($diff->days == 1) {
        $tempo = 'Ontem';
    } else {
        $tempo = $diff->days . ' dias atrás';
    }
    $row['tempo'] = $tempo;
    $atividades[] = $row;
}
$stmt->close();
$conn->close();

$pageTitle = "LOOKVAULT · Estatísticas";
$pageDescription = "Acompanhe o uso e o valor do seu guarda-roupa.";
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
                        <i class="fas fa-chart-pie"></i> Análise
                    </span>
                    <h1 class="page-hero-title">
                        Suas <em>Estatísticas</em>
                    </h1>
                    <p class="page-hero-subtitle">
                        Descubra o valor real do seu guarda-roupa, suas marcas favoritas, categorias mais usadas e muito mais.
                    </p>
                </div>
                <div class="page-hero-actions">
                    <button onclick="atualizarDados()" class="btn-hero btn-hero-primary" id="btnRefresh">
                        <i class="fas fa-sync-alt" id="refreshIcon"></i> Atualizar
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- KPI CARDS -->
    <section class="stats-kpi-grid">
        <div class="container">
            <div class="stats-kpi-grid" style="padding: 0;">
                <div class="kpi-card animate-fade-up animate-delay-1">
                    <div class="kpi-card-header">
                        <span class="kpi-card-label">Total de Peças</span>
                        <span class="kpi-card-icon"><i class="fas fa-tshirt"></i></span>
                    </div>
                    <div class="kpi-card-value" id="totalPecas"><?php echo $total_pecas; ?></div>
                    <div class="kpi-card-hint">peças em seu guarda-roupa</div>
                </div>

                <div class="kpi-card animate-fade-up animate-delay-2">
                    <div class="kpi-card-header">
                        <span class="kpi-card-label">Usadas em 30 dias</span>
                        <span class="kpi-card-icon"><i class="fas fa-calendar-check"></i></span>
                    </div>
                    <div class="kpi-card-value" id="maisUsadas"><?php echo $mais_usadas; ?></div>
                    <div class="kpi-card-hint">peças tiveram uso recente</div>
                </div>

                <div class="kpi-card animate-fade-up animate-delay-3">
                    <div class="kpi-card-header">
                        <span class="kpi-card-label">Custo Médio / Uso</span>
                        <span class="kpi-card-icon"><i class="fas fa-coins"></i></span>
                    </div>
                    <div class="kpi-card-value" id="custoMedio">R$ <?php echo $custo_medio; ?></div>
                    <div class="kpi-card-hint">por cada utilização</div>
                </div>

                <div class="kpi-card animate-fade-up animate-delay-4">
                    <div class="kpi-card-header">
                        <span class="kpi-card-label">Valor Total</span>
                        <span class="kpi-card-icon"><i class="fas fa-gem"></i></span>
                    </div>
                    <div class="kpi-card-value" id="valorTotal">R$ <?php echo $valor_total; ?></div>
                    <div class="kpi-card-hint">investidos em estilo</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CHARTS -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-charts-grid">
                <div class="chart-panel animate-fade-up">
                    <div class="chart-panel-header">
                        <h3 class="chart-panel-title">
                            <i class="fas fa-layer-group"></i>
                            Categorias Mais Populares
                        </h3>
                        <span class="chart-panel-meta"><?php echo count($categorias); ?> categorias</span>
                    </div>
                    <div class="progress-list" id="chartBars">
                        <?php if (empty($categorias)): ?>
                            <p style="text-align: center; color: var(--lv-texto-claro); padding: 40px 0;">Nenhuma peça cadastrada ainda.</p>
                        <?php else:
                            $max_total = max(array_column($categorias, 'total'));
                            foreach ($categorias as $cat):
                                $percentual = $max_total > 0 ? round(($cat['total'] / $max_total) * 100) : 0;
                        ?>
                        <div class="progress-item">
                            <span class="progress-label"><?php echo htmlspecialchars($cat['categoria']); ?></span>
                            <div class="progress-track">
                                <div class="progress-fill" style="width: <?php echo $percentual; ?>%;"></div>
                            </div>
                            <span class="progress-value"><?php echo $cat['total']; ?></span>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>

                <div class="chart-panel animate-fade-up animate-delay-2">
                    <div class="chart-panel-header">
                        <h3 class="chart-panel-title">
                            <i class="fas fa-crown"></i>
                            Top Marcas
                        </h3>
                        <span class="chart-panel-meta">Top 5</span>
                    </div>
                    <div class="brand-list" id="brandStats">
                        <?php if (empty($marcas)): ?>
                            <p style="text-align: center; color: var(--lv-texto-claro); padding: 40px 0;">Nenhuma marca cadastrada.</p>
                        <?php else: ?>
                            <?php foreach ($marcas as $i => $marca): ?>
                            <div class="brand-list-item">
                                <span class="brand-list-name">
                                    <span class="brand-list-rank"><?php echo $i + 1; ?></span>
                                    <?php echo htmlspecialchars($marca['marca']); ?>
                                </span>
                                <span class="brand-list-count"><?php echo $marca['total']; ?> <?php echo $marca['total'] == 1 ? 'peça' : 'peças'; ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- TIMELINE -->
    <section class="stats-section" style="padding-top: 0;">
        <div class="container">
            <div class="chart-panel animate-fade-up">
                <div class="chart-panel-header">
                    <h3 class="chart-panel-title">
                        <i class="fas fa-history"></i>
                        Atividade Recente
                    </h3>
                    <span class="chart-panel-meta">últimas 5 ações</span>
                </div>
                <div class="timeline" id="activityList">
                    <?php if (empty($atividades)): ?>
                        <div style="text-align: center; color: var(--lv-texto-claro); padding: 40px 0;">
                            <i class="fas fa-info-circle"></i> Nenhuma atividade recente
                        </div>
                    <?php else: ?>
                        <?php foreach ($atividades as $atividade):
                            $icone = $atividade['acao'] == 'Adicionou' ? 'fa-plus' : 'fa-magic';
                        ?>
                        <div class="timeline-item">
                            <div class="timeline-icon"><i class="fas <?php echo $icone; ?>"></i></div>
                            <div class="timeline-content">
                                <?php echo htmlspecialchars($atividade['acao']); ?>
                                <strong><?php echo htmlspecialchars($atividade['descricao']); ?></strong>
                            </div>
                            <span class="timeline-time"><?php echo $atividade['tempo']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
function atualizarDados() {
    const icon = document.getElementById('refreshIcon');
    icon.style.animation = 'spin 0.6s linear';

    fetch('apiestatisticas.php')
        .then(r => r.json())
        .then(data => {
            document.getElementById('totalPecas').textContent = data.total_pecas;
            document.getElementById('maisUsadas').textContent = data.mais_usadas;
            document.getElementById('custoMedio').textContent = 'R$ ' + data.custo_medio;
            document.getElementById('valorTotal').textContent = 'R$ ' + data.valor_total;

            renderCategorias(data.categorias);
            renderMarcas(data.marcas);
            renderAtividades(data.atividades);

            setTimeout(() => icon.style.animation = '', 600);
        })
        .catch(err => {
            console.error(err);
            icon.style.animation = '';
        });
}

function renderCategorias(categorias) {
    const container = document.getElementById('chartBars');
    if (!categorias || categorias.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: var(--lv-texto-claro); padding: 40px 0;">Nenhuma peça cadastrada ainda.</p>';
        return;
    }
    const max = Math.max(...categorias.map(c => c.total));
    container.innerHTML = categorias.map(cat => {
        const p = max > 0 ? Math.round((cat.total / max) * 100) : 0;
        return `
            <div class="progress-item">
                <span class="progress-label">${cat.categoria}</span>
                <div class="progress-track">
                    <div class="progress-fill" style="width: ${p}%;"></div>
                </div>
                <span class="progress-value">${cat.total}</span>
            </div>
        `;
    }).join('');
}

function renderMarcas(marcas) {
    const container = document.getElementById('brandStats');
    if (!marcas || marcas.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: var(--lv-texto-claro); padding: 40px 0;">Nenhuma marca cadastrada.</p>';
        return;
    }
    container.innerHTML = marcas.map((m, i) => `
        <div class="brand-list-item">
            <span class="brand-list-name">
                <span class="brand-list-rank">${i + 1}</span>
                ${m.marca}
            </span>
            <span class="brand-list-count">${m.total} ${m.total == 1 ? 'peça' : 'peças'}</span>
        </div>
    `).join('');
}

function renderAtividades(atividades) {
    const container = document.getElementById('activityList');
    if (!atividades || atividades.length === 0) {
        container.innerHTML = '<div style="text-align: center; color: var(--lv-texto-claro); padding: 40px 0;"><i class="fas fa-info-circle"></i> Nenhuma atividade recente</div>';
        return;
    }
    container.innerHTML = atividades.map(a => {
        const icone = a.acao === 'Adicionou' ? 'fa-plus' : 'fa-magic';
        return `
            <div class="timeline-item">
                <div class="timeline-icon"><i class="fas ${icone}"></i></div>
                <div class="timeline-content">${a.acao} <strong>${a.descricao}</strong></div>
                <span class="timeline-time">${a.tempo}</span>
            </div>
        `;
    }).join('');
}

setInterval(atualizarDados, 60000);
</script>

<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<?php include 'includes/footer.php'; ?>