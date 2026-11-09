<?php
$pageTitle = "LOOKVAULT · Cadastrar Peça";
$pageDescription = "Adicione novas peças ao seu guarda-roupa.";
include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/pages.css">

<main>
    <section class="page-hero">
        <div class="container">
            <div class="page-hero-inner">
                <div class="page-hero-text">
                    <span class="page-hero-eyebrow">
                        <i class="fas fa-plus-circle"></i> Nova peça
                    </span>
                    <h1 class="page-hero-title">
                        Adicionar <em>Peça</em>
                    </h1>
                    <p class="page-hero-subtitle">
                        Preencha as informações abaixo para adicionar uma nova peça ao seu guarda-roupa digital.
                    </p>
                </div>
                <div class="page-hero-actions">
                    <a href="guarda-roupa.php" class="btn-hero btn-hero-ghost">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="form-card-premium animate-fade-up">
            <form action="processar-cadastro.php" method="POST" enctype="multipart/form-data">
                <h3 class="form-section-title">
                    <i class="fas fa-info-circle"></i> Informações Básicas
                    <span>obrigatório</span>
                </h3>

                <div class="form-field">
                    <label for="nome">Nome da Peça</label>
                    <input type="text" id="nome" name="nome" placeholder="Ex: Camisa Social Branca" required />
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-field">
                        <label for="marca">Marca</label>
                        <select id="marca" name="marca" required>
                            <option value="">Selecione a marca</option>
                            <option value="DIOR">DIOR</option>
                            <option value="GUCCI">GUCCI</option>
                            <option value="CHANEL">CHANEL</option>
                            <option value="PRADA">PRADA</option>
                            <option value="FENDI">FENDI</option>
                            <option value="Zara">Zara</option>
                            <option value="H&M">H&M</option>
                            <option value="Nike">Nike</option>
                            <option value="Adidas">Adidas</option>
                            <option value="Outra">Outra</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="categoria">Categoria</label>
                        <select id="categoria" name="categoria" required>
                            <option value="">Selecione a categoria</option>
                            <option value="Camisas">Camisas</option>
                            <option value="Calças">Calças</option>
                            <option value="Sapatos">Sapatos</option>
                            <option value="Acessórios">Acessórios</option>
                            <option value="Blazers">Blazers</option>
                            <option value="Vestidos">Vestidos</option>
                            <option value="Jeans">Jeans</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                </div>

                <h3 class="form-section-title">
                    <i class="fas fa-palette"></i> Detalhes
                </h3>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-field">
                        <label for="cor">Cor</label>
                        <input type="text" id="cor" name="cor" placeholder="Ex: Branco, Preto, Azul" required />
                    </div>

                    <div class="form-field">
                        <label for="valor">Valor (R$)</label>
                        <input type="number" id="valor" name="valor" placeholder="0.00" step="0.01" required />
                    </div>
                </div>

                <div class="form-field">
                    <label for="tags">Tags <span style="font-weight: 400; color: var(--lv-texto-claro); text-transform: none; letter-spacing: 0;">(separadas por vírgula)</span></label>
                    <input type="text" id="tags" name="tags" placeholder="Ex: verão, casual, social" />
                </div>

                <h3 class="form-section-title">
                    <i class="fas fa-image"></i> Foto da Peça
                </h3>

                <div class="file-upload-premium" id="fileUpload">
                    <input type="file" id="foto" name="foto" accept="image/*" onchange="previewFoto(event)" />
                    <div class="file-upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <div class="file-upload-text">Clique para selecionar uma imagem</div>
                    <div class="file-upload-hint">JPG, PNG ou WEBP • Máximo 5MB</div>
                </div>

                <div id="filePreview"></div>

                <div class="form-actions" style="margin-top: 32px; display: flex; gap: 12px; flex-wrap: wrap;">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Salvar Peça
                    </button>
                    <a href="guarda-roupa.php" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
function previewFoto(e) {
    const file = e.target.files[0];
    if (!file) return;

    const preview = document.getElementById('filePreview');
    const size = (file.size / 1024).toFixed(1);

    const reader = new FileReader();
    reader.onload = function (ev) {
        preview.innerHTML = `
            <div class="file-preview">
                <img src="${ev.target.result}" alt="Preview" />
                <div class="file-preview-info">
                    <div class="file-preview-name">${file.name}</div>
                    <div class="file-preview-size">${size} KB</div>
                </div>
            </div>
        `;
    };
    reader.readAsDataURL(file);
}
</script>

<?php include 'includes/footer.php'; ?>