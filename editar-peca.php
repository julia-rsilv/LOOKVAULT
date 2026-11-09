<?php
session_start();
require_once 'configdatabase.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
$usuario_id = (int) $_SESSION['usuario_id'];
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    header('Location: guarda-roupa.php?erro=' . urlencode('Peça inválida.'));
    exit;
}

$resultado = executarQuery('SELECT * FROM pecas WHERE id = ? AND usuario_id = ?', [$id, $usuario_id]);
if (!$resultado || !$resultado->num_rows) {
    header('Location: guarda-roupa.php?erro=' . urlencode('Peça não encontrada.'));
    exit;
}
$peca = $resultado->fetch_assoc();
$erro = '';

function e($valor) { return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Solicitação inválida. Atualize a página e tente novamente.');
    }

    $nome = trim($_POST['nome'] ?? '');
    $marca = trim($_POST['marca'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $cor = trim($_POST['cor'] ?? '');
    $valorTexto = str_replace(',', '.', trim($_POST['valor'] ?? ''));
    $tags = trim($_POST['tags'] ?? '');
    $valor = $valorTexto === '' ? null : (is_numeric($valorTexto) ? (float)$valorTexto : -1);
    $novaFoto = null;

    if ($nome === '' || $categoria === '' || $cor === '') {
        $erro = 'Preencha nome, categoria e cor.';
    } elseif ($valor !== null && $valor < 0) {
        $erro = 'Informe um valor válido.';
    } elseif (strlen($nome) > 100 || strlen($marca) > 50 || strlen($categoria) > 50 || strlen($cor) > 30 || strlen($tags) > 255) {
        $erro = 'Um ou mais campos ultrapassaram o limite permitido.';
    } else {
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                $erro = 'Não foi possível enviar a imagem.';
            } elseif ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                $erro = 'A imagem deve ter no máximo 5 MB.';
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($_FILES['foto']['tmp_name']);
                $extensoes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                if (!isset($extensoes[$mime])) {
                    $erro = 'Formato inválido. Use JPG, PNG ou WEBP.';
                } else {
                    $diretorio = __DIR__ . '/uploads/pecas/';
                    if (!is_dir($diretorio) && !mkdir($diretorio, 0755, true)) {
                        $erro = 'Não foi possível preparar o envio da imagem.';
                    } else {
                        $arquivo = bin2hex(random_bytes(16)) . '.' . $extensoes[$mime];
                        if (move_uploaded_file($_FILES['foto']['tmp_name'], $diretorio . $arquivo)) {
                            $novaFoto = 'uploads/pecas/' . $arquivo;
                        } else {
                            $erro = 'Não foi possível salvar a imagem.';
                        }
                    }
                }
            }
        }

        if ($erro === '') {
            $fotoFinal = $novaFoto ?? ($peca['foto'] ?? '');
            $ok = executarQuery(
                'UPDATE pecas SET nome = ?, marca = ?, categoria = ?, cor = ?, valor = ?, tags = ?, foto = ? WHERE id = ? AND usuario_id = ?',
                [$nome, $marca, $categoria, $cor, $valor, $tags, $fotoFinal, $id, $usuario_id]
            );
            if ($ok === true) {
                if ($novaFoto && !empty($peca['foto'])) {
                    $fotoAntiga = __DIR__ . '/' . $peca['foto'];
                    if (is_file($fotoAntiga)) @unlink($fotoAntiga);
                }
                header('Location: guarda-roupa.php?sucesso=' . urlencode('Peça atualizada com sucesso!'));
                exit;
            }
            if ($novaFoto && is_file(__DIR__ . '/' . $novaFoto)) @unlink(__DIR__ . '/' . $novaFoto);
            $erro = 'Não foi possível atualizar a peça. Tente novamente.';
        }

        $peca = array_merge($peca, [
            'nome' => $nome, 'marca' => $marca, 'categoria' => $categoria,
            'cor' => $cor, 'valor' => $valorTexto, 'tags' => $tags
        ]);
    }
}

$pageTitle = 'LOOKVAULT · Editar Peça';
$pageDescription = 'Atualize as informações de uma peça do seu guarda-roupa.';
include 'includes/header.php';
?>
<link rel="stylesheet" href="assets/css/pages.css">
<main>
    <section class="page-hero">
        <div class="container">
            <div class="page-hero-inner">
                <div class="page-hero-text">
                    <span class="page-hero-eyebrow"><i class="fas fa-pen"></i> Editar peça</span>
                    <h1 class="page-hero-title">Atualizar <em>Peça</em></h1>
                    <p class="page-hero-subtitle">Altere as informações da peça selecionada. Os campos já estão preenchidos com os dados atuais.</p>
                </div>
                <div class="page-hero-actions"><a href="guarda-roupa.php" class="btn-hero btn-hero-ghost"><i class="fas fa-arrow-left"></i> Voltar</a></div>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="form-card-premium animate-fade-up">
            <?php if ($erro): ?><div class="alert alert-error" role="alert"><?= e($erro) ?></div><?php endif; ?>
            <form action="editar-peca.php?id=<?= (int)$id ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <h3 class="form-section-title"><i class="fas fa-info-circle"></i> Informações Básicas <span>obrigatório</span></h3>
                <div class="form-field">
                    <label for="nome">Nome da Peça</label>
                    <input type="text" id="nome" name="nome" maxlength="100" value="<?= e($peca['nome']) ?>" required>
                </div>
                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                    <div class="form-field"><label for="marca">Marca</label>
                        <select id="marca" name="marca">
                            <option value="">Selecione a marca</option>
                            <?php foreach (['DIOR','GUCCI','CHANEL','PRADA','FENDI','Zara','H&M','Nike','Adidas','Outra'] as $marca): ?>
                                <option value="<?= e($marca) ?>" <?= ($peca['marca'] ?? '') === $marca ? 'selected' : '' ?>><?= e($marca) ?></option>
                            <?php endforeach; ?>
                            <?php if (!empty($peca['marca']) && !in_array($peca['marca'], ['DIOR','GUCCI','CHANEL','PRADA','FENDI','Zara','H&M','Nike','Adidas','Outra'], true)): ?>
                                <option value="<?= e($peca['marca']) ?>" selected><?= e($peca['marca']) ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-field"><label for="categoria">Categoria</label>
                        <select id="categoria" name="categoria" required>
                            <option value="">Selecione a categoria</option>
                            <?php foreach (['Camisas','Calças','Sapatos','Acessórios','Blazers','Vestidos','Jeans','Outro'] as $categoria): ?>
                                <option value="<?= e($categoria) ?>" <?= ($peca['categoria'] ?? '') === $categoria ? 'selected' : '' ?>><?= e($categoria) ?></option>
                            <?php endforeach; ?>
                            <?php if (!empty($peca['categoria']) && !in_array($peca['categoria'], ['Camisas','Calças','Sapatos','Acessórios','Blazers','Vestidos','Jeans','Outro'], true)): ?>
                                <option value="<?= e($peca['categoria']) ?>" selected><?= e($peca['categoria']) ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <h3 class="form-section-title"><i class="fas fa-palette"></i> Detalhes</h3>
                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                    <div class="form-field"><label for="cor">Cor</label><input type="text" id="cor" name="cor" maxlength="30" value="<?= e($peca['cor']) ?>" required></div>
                    <div class="form-field"><label for="valor">Valor (R$)</label><input type="number" id="valor" name="valor" min="0" step="0.01" value="<?= e($peca['valor']) ?>"></div>
                </div>
                <div class="form-field"><label for="tags">Tags <span style="font-weight:400;color:var(--lv-texto-claro);text-transform:none;letter-spacing:0;">(separadas por vírgula)</span></label><input type="text" id="tags" name="tags" maxlength="255" value="<?= e($peca['tags'] ?? '') ?>" placeholder="Ex: verão, casual, social"></div>
                <h3 class="form-section-title"><i class="fas fa-image"></i> Foto da Peça</h3>
                <?php if (!empty($peca['foto']) && is_file(__DIR__ . '/' . $peca['foto'])): ?>
                    <div class="file-preview" style="margin-bottom:16px;"><img src="<?= e($peca['foto']) ?>" alt="Foto atual de <?= e($peca['nome']) ?>"><div class="file-preview-info"><strong>Foto atual</strong><div class="file-preview-name">Selecione outra imagem abaixo para substituí-la.</div></div></div>
                <?php else: ?><p style="color:var(--lv-texto-claro);margin-bottom:14px;">Esta peça ainda não possui fotografia.</p><?php endif; ?>
                <div class="file-upload-premium" id="fileUpload"><input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" onchange="previewFoto(event)"><div class="file-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div><div class="file-upload-text">Clique para selecionar uma nova imagem</div><div class="file-upload-hint">JPG, PNG ou WEBP • Máximo 5MB • Opcional</div></div>
                <div id="filePreview"></div>
                <div class="form-actions" style="margin-top:32px;display:flex;gap:12px;flex-wrap:wrap;">
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Salvar Alterações</button>
                    <a href="guarda-roupa.php" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</main>
<script>
function previewFoto(event) {
    const file = event.target.files[0];
    if (!file) return;
    const preview = document.getElementById('filePreview');
    const reader = new FileReader();
    reader.onload = e => { preview.innerHTML = `<div class="file-preview"><img src="${e.target.result}" alt="Pré-visualização da nova foto"><div class="file-preview-info"><strong>Nova fotografia</strong><div class="file-preview-name">${file.name}</div></div></div>`; };
    reader.readAsDataURL(file);
}
</script>
<?php include 'includes/footer.php'; ?>
