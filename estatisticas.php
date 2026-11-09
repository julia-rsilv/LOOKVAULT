<?php
session_start();
require_once 'configdatabase.php';

// Buscar foto de perfil
$foto_perfil = '';
if (isset($_SESSION['usuario_id'])) {
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

// Se não estiver logado, usar usuário 1 para teste
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 1;

$conn = conectarDB();

// Total de peças
$sql = "SELECT COUNT(*) as total FROM pecas WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$total_pecas = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Peças mais usadas (últimos 30 dias)
$sql = "SELECT COUNT(DISTINCT p.id) as total
        FROM pecas p
        INNER JOIN uso_pecas u ON p.id = u.peca_id
        WHERE p.usuario_id = ? AND u.data_uso >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$mais_usadas = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Custo médio por uso
$sql = "SELECT AVG(valor / COALESCE(uso_count, 1)) as custo_medio
        FROM (
            SELECT p.valor, COUNT(u.id) as uso_count
            FROM pecas p
            LEFT JOIN uso_pecas u ON p.id = u.peca_id
            WHERE p.usuario_id = ?
            GROUP BY p.id
        ) as subquery";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$custo_medio = number_format($result->fetch_assoc()['custo_medio'] ?? 0, 2, ',', '.');
$stmt->close();

// Valor total
$sql = "SELECT SUM(valor) as total FROM pecas WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$valor_total = number_format($result->fetch_assoc()['total'] ?? 0, 2, ',', '.');
$stmt->close();

// Categorias
$sql = "SELECT categoria, COUNT(*) as total 
        FROM pecas 
        WHERE usuario_id = ? AND categoria IS NOT NULL AND categoria != ''
        GROUP BY categoria
        ORDER BY total DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$categorias = [];
while ($row = $result->fetch_assoc()) {
    $categorias[] = $row;
}
$stmt->close();

// Marcas
$sql = "SELECT marca, COUNT(*) as total 
        FROM pecas 
        WHERE usuario_id = ? AND marca IS NOT NULL AND marca != ''
        GROUP BY marca
        ORDER BY total DESC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$marcas = [];
while ($row = $result->fetch_assoc()) {
    $marcas[] = $row;
}
$stmt->close();

// Atividades recentes
$sql = "(SELECT 'Adicionou' as acao, nome as descricao, data_cadastro as data FROM pecas WHERE usuario_id = ?)
        UNION ALL
        (SELECT 'Criou look' as acao, nome as descricao, data_criacao as data FROM looks WHERE usuario_id = ?)
        ORDER BY data DESC
        LIMIT 5";
$stmt = $conn->prepare($sql);
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
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?php echo $_COOKIE['theme'] ?? 'light'; ?>">
<head>
    <script src="assets/js/theme.js"></script>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LOOKVAULT · Estatísticas</title>
    <meta name="description" content="Acompanhe o uso e o valor do seu guarda-roupa." />
    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>

<header class="header" id="header">
    <div class="container">
        <a href="index.php" class="logo">
            <i class="fas fa-wardrobe"></i> LOOKVAULT
        </a>
        <button class="menu-toggle" id="menuToggle" aria-label="Menu">
            <i class="fas fa-bars"></i>
        </button>
        <nav class="nav" id="nav">
            <a href="index.php">Início</a>
            <a href="guarda-roupa.php">Guarda-Roupa</a>
            <a href="combinacoes.php">Combinações</a>
            <a href="estatisticas.php">Estatísticas</a>
            
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <a href="minha-conta.php" class="btn-nav" style="background: linear-gradient(135deg, var(--roxo-claro), var(--roxo-medio)); display: flex; align-items: center; gap: 6px;">
                    <?php if (!empty($foto_perfil) && file_exists($foto_perfil)): ?>
                        <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Perfil" class="avatar-nav" />
                    <?php else: ?>
                        <span class="avatar-nav-placeholder"><i class="fas fa-user"></i></span>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Conta'); ?>
                </a>
            <?php else: ?>
                <a href="login.php" class="btn-nav">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main>
    <section class="page-header">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h1 class="page-title">Estatísticas do Guarda-Roupa</h1>
                    <p class="page-subtitle">Acompanhe a frequência de uso, custo por uso e valorize cada peça</p>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span onclick="atualizarDados()" style="background: none; border: none; color: var(--roxo-medio); font-size: 1.1rem; padding: 8px; border-radius: 50%; transition: all 0.3s; cursor: default;" onmouseover="this.style.background='var(--cinza-claro)'" onmouseout="this.style.background='transparent'">
                        <i class="fas fa-sync-alt" id="refreshIcon"></i>
                    </span>
                    <span style="font-size: 0.75rem; color: var(--texto-claro);">
                        <i class="fas fa-clock"></i> <span id="updateTime"><?php echo date('H:i:s'); ?></span>
                    </span>
                </div>
            </div>
        </div>
    </section>

    <section class="stats-overview">
        <div class="container">
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-tshirt"></i></div>
                    <div class="stat-content">
                        <h3>Total de Peças</h3>
                        <p class="stat-number" id="totalPecas"><?php echo $total_pecas; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-content">
                        <h3>Peças Mais Usadas (30d)</h3>
                        <p class="stat-number" id="maisUsadas"><?php echo $mais_usadas; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-coins"></i></div>
                    <div class="stat-content">
                        <h3>Custo Médio por Uso</h3>
                        <p class="stat-number" id="custoMedio">R$ <?php echo $custo_medio; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-chart-pie"></i></div>
                    <div class="stat-content">
                        <h3>Valor Total do Guarda-Roupa</h3>
                        <p class="stat-number" id="valorTotal">R$ <?php echo $valor_total; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="stats-details">
        <div class="container">
            <div class="stats-charts">
                <div class="chart-card">
                    <h3>Categorias Mais Usadas</h3>
                    <div class="chart-bars" id="chartBars">
                        <?php if (empty($categorias)): ?>
                        <p style="text-align: center; color: var(--texto-claro); padding: 20px 0;">Nenhuma peça cadastrada.</p>
                        <?php else: 
                            $max_total = max(array_column($categorias, 'total'));
                            $cores = ['var(--roxo-claro)', 'var(--azul-claro)', 'var(--rosa-claro)', 'var(--roxo-medio)', 'var(--azul-medio)'];
                            $i = 0;
                            foreach ($categorias as $cat):
                                $percentual = $max_total > 0 ? round(($cat['total'] / $max_total) * 100) : 0;
                        ?>
                        <div class="chart-bar">
                            <span class="bar-label"><?php echo htmlspecialchars($cat['categoria']); ?></span>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: <?php echo $percentual; ?>%; background: <?php echo $cores[$i % count($cores)]; ?>;"></div>
                            </div>
                            <span class="bar-value"><?php echo $cat['total']; ?></span>
                        </div>
                        <?php 
                            $i++;
                            endforeach; 
                        endif; 
                        ?>
                    </div>
                </div>

                <div class="chart-card">
                    <h3>Top 5 Marcas</h3>
                    <div class="brand-stats" id="brandStats">
                        <?php if (empty($marcas)): ?>
                        <p style="text-align: center; color: var(--texto-claro); padding: 20px 0;">Nenhuma marca cadastrada.</p>
                        <?php else: ?>
                        <?php foreach ($marcas as $marca): ?>
                        <div class="brand-stat">
                            <span class="brand-name"><?php echo htmlspecialchars($marca['marca']); ?></span>
                            <span class="brand-count"><?php echo $marca['total']; ?> peças</span>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="recent-activity">
        <div class="container">
            <h3 class="section-title-small">Atividade Recente</h3>
            <div class="activity-list" id="activityList">
                <?php if (empty($atividades)): ?>
                <div class="activity-item" style="justify-content: center; color: var(--texto-claro);">
                    <i class="fas fa-info-circle"></i> Nenhuma atividade recente
                </div>
                <?php else: ?>
                <?php foreach ($atividades as $atividade): 
                    $icone = $atividade['acao'] == 'Adicionou' ? 'fa-plus-circle' : 'fa-magic';
                    $cor = $atividade['acao'] == 'Adicionou' ? 'var(--roxo-medio)' : 'var(--azul-medio)';
                ?>
                <div class="activity-item">
                    <i class="fas <?php echo $icone; ?>" style="color: <?php echo $cor; ?>;"></i>
                    <span><?php echo $atividade['acao']; ?> <strong><?php echo htmlspecialchars($atividade['descricao']); ?></strong></span>
                    <span class="activity-time"><?php echo $atividade['tempo']; ?></span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<script>
function atualizarDados() {
    const icon = document.getElementById('refreshIcon');
    icon.style.transform = 'rotate(360deg)';
    icon.style.transition = 'transform 0.5s';
    
    fetch('apiestatisticas.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalPecas').textContent = data.total_pecas;
            document.getElementById('maisUsadas').textContent = data.mais_usadas;
            document.getElementById('custoMedio').textContent = 'R$ ' + data.custo_medio;
            document.getElementById('valorTotal').textContent = 'R$ ' + data.valor_total;
            
            atualizarCategorias(data.categorias);
            atualizarMarcas(data.marcas);
            atualizarAtividades(data.atividades);
            
            const now = new Date();
            document.getElementById('updateTime').textContent = now.toLocaleTimeString('pt-BR');
            
            setTimeout(() => {
                icon.style.transform = 'rotate(0deg)';
            }, 500);
        })
        .catch(error => {
            console.error('Erro:', error);
            icon.style.transform = 'rotate(0deg)';
        });
}

function atualizarCategorias(categorias) {
    const container = document.getElementById('chartBars');
    if (!categorias || categorias.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: var(--texto-claro); padding: 20px 0;">Nenhuma peça cadastrada.</p>';
        return;
    }
    const max_total = Math.max(...categorias.map(c => c.total));
    const cores = ['var(--roxo-claro)', 'var(--azul-claro)', 'var(--rosa-claro)', 'var(--roxo-medio)', 'var(--azul-medio)'];
    let html = '';
    categorias.forEach((cat, index) => {
        const percentual = max_total > 0 ? Math.round((cat.total / max_total) * 100) : 0;
        html += `
            <div class="chart-bar">
                <span class="bar-label">${cat.categoria}</span>
                <div class="bar-track">
                    <div class="bar-fill" style="width: ${percentual}%; background: ${cores[index % cores.length]};"></div>
                </div>
                <span class="bar-value">${cat.total}</span>
            </div>
        `;
    });
    container.innerHTML = html;
}

function atualizarMarcas(marcas) {
    const container = document.getElementById('brandStats');
    if (!marcas || marcas.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: var(--texto-claro); padding: 20px 0;">Nenhuma marca cadastrada.</p>';
        return;
    }
    let html = '';
    marcas.forEach(marca => {
        html += `
            <div class="brand-stat">
                <span class="brand-name">${marca.marca}</span>
                <span class="brand-count">${marca.total} peças</span>
            </div>
        `;
    });
    container.innerHTML = html;
}

function atualizarAtividades(atividades) {
    const container = document.getElementById('activityList');
    if (!atividades || atividades.length === 0) {
        container.innerHTML = `
            <div class="activity-item" style="justify-content: center; color: var(--texto-claro);">
                <i class="fas fa-info-circle"></i> Nenhuma atividade recente
            </div>
        `;
        return;
    }
    let html = '';
    atividades.forEach(atividade => {
        const icone = atividade.acao === 'Adicionou' ? 'fa-plus-circle' : 'fa-magic';
        const cor = atividade.acao === 'Adicionou' ? 'var(--roxo-medio)' : 'var(--azul-medio)';
        html += `
            <div class="activity-item">
                <i class="fas ${icone}" style="color: ${cor};"></i>
                <span>${atividade.acao} <strong>${atividade.descricao}</strong></span>
                <span class="activity-time">${atividade.tempo}</span>
            </div>
        `;
    });
    container.innerHTML = html;
}

// Atualizar automaticamente a cada 60 segundos
setInterval(atualizarDados, 60000);
</script>

<script src="assets/js/main.js"></script>
</body>
<html lang="pt-BR" data-theme="<?php echo $_COOKIE['theme'] ?? 'light'; ?>">
</html>