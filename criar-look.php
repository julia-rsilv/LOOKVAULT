<?php
session_start();
require_once 'configdatabase.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
$usuario_id = $_SESSION['usuario_id'];
$look_id = $_GET['id'] ?? 0;
$editando = false;
$look_atual = null;
$pecas_selecionadas = [];

if ($look_id > 0) {
    $sql = "SELECT * FROM looks WHERE id = ? AND usuario_id = ?";
    $result = executarQuery($sql, [$look_id, $usuario_id]);
    if ($result && $result->num_rows > 0) {
        $look_atual = $result->fetch_assoc();
        $editando = true;
        $sql_pecas = "SELECT peca_id FROM look_pecas WHERE look_id = ?";
        $result_pecas = executarQuery($sql_pecas, [$look_id]);
        if ($result_pecas) {
            while ($row = $result_pecas->fetch_assoc()) $pecas_selecionadas[] = $row['peca_id'];
        }
    }
}

$sql_pecas = "SELECT * FROM pecas WHERE usuario_id = ? ORDER BY nome ASC";
$result_pecas = executarQuery($sql_pecas, [$usuario_id]);
$todas_pecas = [];
if ($result_pecas && $result_pecas->num_rows > 0) {
    while ($row = $result_pecas->fetch_assoc()) $todas_pecas[] = $row;
}

$pageTitle = "LOOKVAULT · " . ($editando ? 'Editar' : 'Criar') . " Look";
$pageDescription = "Monte combinações incríveis com suas peças.";
include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/pages.css">

<main>
    <section class="page-hero">
        <div class="container">
            <div class="page-hero-inner">
                <div class="page-hero-text">
                    <span class="page-hero-eyebrow">
                        <i class="fas fa-magic"></i> <?php echo $editando ? 'Editando look' : 'Novo look'; ?>
                    </span>
                    <h1 class="page-hero-title">
                        <?php echo $editando ? 'Editar' : 'Criar'; ?> <em>Look</em>
                    </h1>
                    <p class="page-hero-subtitle">
                        Selecione as peças ao lado e monte uma combinação única com seu estilo pessoal.
                    </p>
                </div>
                <div class="page-hero-actions">
                    <a href="combinacoes.php" class="btn-hero btn-hero-ghost">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php if (isset($_GET['erro'])): ?>
    <div class="container" style="padding-top: 24px;">
        <div class="mensagem-erro"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['erro']); ?></div>
    </div>
    <?php endif; ?>

    <section class="builder-section">
        <div class="container">
            <form action="processar-look.php" method="POST" id="formLook">
                <input type="hidden" name="look_id" value="<?php echo $look_id; ?>" />
                <input type="hidden" name="acao" value="<?php echo $editando ? 'editar' : 'criar'; ?>" />

                <!-- NAME PANEL -->
                <div class="builder-name-panel animate-fade-up">
                    <div class="form-field" style="margin: 0;">
                        <label for="nome_look">Nome do Look</label>
                        <input type="text" id="nome_look" name="nome_look"
                               placeholder="Ex: Look Casual de Verão"
                               value="<?php echo $editando ? htmlspecialchars($look_atual['nome']) : ''; ?>"
                               required />
                    </div>
                    <div class="form-field" style="margin: 0;">
                        <label for="ocasiao">Ocasião</label>
                        <select id="ocasiao" name="ocasiao">
                            <option value="">Selecione (opcional)</option>
                            <?php foreach (['Casual','Social','Festa','Esporte','Trabalho','Praia','Noite'] as $op): ?>
                                <option value="<?php echo $op; ?>" <?php echo ($editando && $look_atual['ocasiao'] == $op) ? 'selected' : ''; ?>><?php echo $op; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- CANVAS -->
                <div class="builder-canvas">
                    <!-- PEÇAS -->
                    <div class="pecas-panel animate-fade-up animate-delay-1">
                        <div class="pecas-panel-header">
                            <h3 class="pecas-panel-title">
                                <i class="fas fa-tshirt"></i> Suas Peças
                            </h3>
                            <span class="pecas-panel-count"><?php echo count($todas_pecas); ?> disponíveis</span>
                        </div>

                        <div class="pecas-search">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchPecas" placeholder="Filtrar peças..." onkeyup="filtrarPecas()" />
                        </div>

                        <div class="pecas-scroll" id="listaPecas">
                            <?php if (empty($todas_pecas)): ?>
                                <div style="text-align: center; padding: 40px 20px; color: var(--lv-texto-claro);">
                                    <i class="fas fa-tshirt" style="font-size: 2.5rem; color: var(--lv-bege); margin-bottom: 12px; display: block;"></i>
                                    <p>Você ainda não tem peças cadastradas.</p>
                                    <a href="cadastro.php" class="btn-primary" style="margin-top: 12px; display: inline-flex;">
                                        <i class="fas fa-plus"></i> Adicionar Peça
                                    </a>
                                </div>
                            <?php else: ?>
                                <?php foreach ($todas_pecas as $peca):
                                    $selected = in_array($peca['id'], $pecas_selecionadas);
                                ?>
                                <div class="peca-row <?php echo $selected ? 'selected' : ''; ?>"
                                     data-id="<?php echo $peca['id']; ?>"
                                     data-nome="<?php echo strtolower(htmlspecialchars($peca['nome'])); ?>"
                                     data-marca="<?php echo strtolower(htmlspecialchars($peca['marca'])); ?>"
                                     data-categoria="<?php echo strtolower(htmlspecialchars($peca['categoria'])); ?>"
                                     onclick="togglePeca(this, <?php echo $peca['id']; ?>)">
                                    <div class="peca-row-check"><i class="fas fa-check"></i></div>
                                    <?php if (!empty($peca['foto']) && file_exists($peca['foto'])): ?>
                                        <img src="<?php echo htmlspecialchars($peca['foto']); ?>" class="peca-row-thumb" alt="" />
                                    <?php else: ?>
                                        <div class="peca-row-thumb"><i class="fas fa-tshirt"></i></div>
                                    <?php endif; ?>
                                    <div class="peca-row-info">
                                        <div class="peca-row-name"><?php echo htmlspecialchars($peca['nome']); ?></div>
                                        <div class="peca-row-meta">
                                            <?php echo htmlspecialchars($peca['marca'] ?: 'Sem marca'); ?> • R$ <?php echo number_format($peca['valor'], 2, ',', '.'); ?>
                                        </div>
                                    </div>
                                    <div class="peca-row-color" style="background: <?php echo htmlspecialchars(strtolower($peca['cor'])); ?>;"></div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- CANVAS -->
                    <div class="canvas-panel animate-fade-up animate-delay-2">
                        <div class="canvas-panel-header">
                            <h3 class="canvas-panel-title">
                                <i class="fas fa-magic"></i> Montagem
                            </h3>
                            <span class="canvas-counter">
                                <span id="contadorPecas"><?php echo count($pecas_selecionadas); ?></span> peças
                            </span>
                        </div>

                        <div class="canvas-preview" id="lookPreview">
                            <?php if (empty($pecas_selecionadas)): ?>
                                <div class="canvas-empty" id="lookEmpty">
                                    <i class="fas fa-tshirt"></i>
                                    <p>Selecione as peças para começar</p>
                                    <small>Clique nas peças ao lado para adicionar</small>
                                </div>
                            <?php else: ?>
                                <?php foreach ($todas_pecas as $peca):
                                    if (in_array($peca['id'], $pecas_selecionadas)):
                                ?>
                                <div class="canvas-item" data-id="<?php echo $peca['id']; ?>">
                                    <div class="canvas-item-icon"><i class="fas fa-tshirt"></i></div>
                                    <div class="canvas-item-info">
                                        <div class="canvas-item-name"><?php echo htmlspecialchars($peca['nome']); ?></div>
                                        <div class="canvas-item-meta"><?php echo htmlspecialchars($peca['marca'] ?: 'Sem marca'); ?></div>
                                    </div>
                                    <button type="button" class="canvas-item-remove" onclick="removerPecaDoLook(<?php echo $peca['id']; ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <?php endif; endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <input type="hidden" name="pecas_selecionadas" id="pecasSelecionadas" value="<?php echo implode(',', $pecas_selecionadas); ?>" />

                        <div class="canvas-actions">
                            <button type="submit" class="btn-primary" id="btnSalvarLook" <?php echo empty($pecas_selecionadas) ? 'disabled' : ''; ?>>
                                <i class="fas fa-save"></i> <?php echo $editando ? 'Atualizar Look' : 'Salvar Look'; ?>
                            </button>
                            <?php if (!empty($pecas_selecionadas)): ?>
                            <button type="button" class="btn-secondary" onclick="limparLook()">
                                <i class="fas fa-eraser"></i> Limpar
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</main>

<script>
let pecasSelecionadas = <?php echo json_encode($pecas_selecionadas); ?>;

function togglePeca(el, id) {
    const idx = pecasSelecionadas.indexOf(id);
    if (idx > -1) {
        pecasSelecionadas.splice(idx, 1);
        el.classList.remove('selected');
    } else {
        pecasSelecionadas.push(id);
        el.classList.add('selected');
    }
    atualizarPreview();
    atualizarInput();
}

function removerPecaDoLook(id) {
    const idx = pecasSelecionadas.indexOf(id);
    if (idx > -1) {
        pecasSelecionadas.splice(idx, 1);
        const row = document.querySelector(`.peca-row[data-id="${id}"]`);
        if (row) row.classList.remove('selected');
        atualizarPreview();
        atualizarInput();
    }
}

function limparLook() {
    if (confirm('Remover todas as peças do look?')) {
        pecasSelecionadas = [];
        document.querySelectorAll('.peca-row.selected').forEach(r => r.classList.remove('selected'));
        atualizarPreview();
        atualizarInput();
    }
}

function atualizarPreview() {
    const preview = document.getElementById('lookPreview');
    const btn = document.getElementById('btnSalvarLook');
    const contador = document.getElementById('contadorPecas');

    contador.textContent = pecasSelecionadas.length;

    if (pecasSelecionadas.length === 0) {
        preview.innerHTML = `
            <div class="canvas-empty" id="lookEmpty">
                <i class="fas fa-tshirt"></i>
                <p>Selecione as peças para começar</p>
                <small>Clique nas peças ao lado para adicionar</small>
            </div>
        `;
        btn.disabled = true;
        return;
    }

    let html = '';
    pecasSelecionadas.forEach(id => {
        const row = document.querySelector(`.peca-row[data-id="${id}"]`);
        if (row) {
            const nome = row.querySelector('.peca-row-name').textContent;
            const meta = row.querySelector('.peca-row-meta').textContent.split('•')[0].trim();
            html += `
                <div class="canvas-item">
                    <div class="canvas-item-icon"><i class="fas fa-tshirt"></i></div>
                    <div class="canvas-item-info">
                        <div class="canvas-item-name">${nome}</div>
                        <div class="canvas-item-meta">${meta}</div>
                    </div>
                    <button type="button" class="canvas-item-remove" onclick="removerPecaDoLook(${id})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        }
    });
    preview.innerHTML = html;
    btn.disabled = false;
}

function atualizarInput() {
    document.getElementById('pecasSelecionadas').value = pecasSelecionadas.join(',');
}

function filtrarPecas() {
    const t = document.getElementById('searchPecas').value.toLowerCase();
    document.querySelectorAll('.peca-row').forEach(row => {
        const match = (row.dataset.nome + ' ' + row.dataset.marca + ' ' + row.dataset.categoria).includes(t);
        row.style.display = match ? '' : 'none';
    });
}

document.getElementById('formLook').addEventListener('submit', function (e) {
    if (!document.getElementById('nome_look').value.trim()) {
        e.preventDefault();
        alert('Por favor, dê um nome para seu look!');
        return;
    }
    if (pecasSelecionadas.length === 0) {
        e.preventDefault();
        alert('Selecione pelo menos uma peça!');
    }
});
</script>

<?php include 'includes/footer.php'; ?>