<?php
session_start();
require_once 'configdatabase.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $marca = $_POST['marca'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $cor = $_POST['cor'] ?? '';
    $valor = str_replace(',', '.', $_POST['valor'] ?? 0);
    $tags = $_POST['tags'] ?? '';
    
    // Validação básica
    if (empty($nome) || empty($categoria)) {
        header('Location: cadastro.php?erro=Preencha todos os campos obrigatórios');
        exit;
    }
    
    // Upload da foto: valida MIME real e tamanho antes de gravar.
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            header('Location: cadastro.php?erro=' . urlencode('Não foi possível enviar a imagem.'));
            exit;
        }
        if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
            header('Location: cadastro.php?erro=' . urlencode('A imagem deve ter no máximo 5 MB.'));
            exit;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['foto']['tmp_name']);
        $extensoes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];
        if (!isset($extensoes[$mime])) {
            header('Location: cadastro.php?erro=' . urlencode('Formato inválido. Use JPG, PNG ou WEBP.'));
            exit;
        }

        $upload_dir = __DIR__ . '/uploads/pecas/';
        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
            header('Location: cadastro.php?erro=' . urlencode('Não foi possível preparar o envio da imagem.'));
            exit;
        }

        $nome_arquivo = bin2hex(random_bytes(16)) . '.' . $extensoes[$mime];
        $caminho_arquivo = $upload_dir . $nome_arquivo;
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $caminho_arquivo)) {
            header('Location: cadastro.php?erro=' . urlencode('Não foi possível salvar a imagem.'));
            exit;
        }
        $foto = 'uploads/pecas/' . $nome_arquivo;
    }

    // Inserir no banco
    $sql = "INSERT INTO pecas (usuario_id, nome, marca, categoria, cor, valor, tags, foto) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $id = inserirRetornarId($sql, [$usuario_id, $nome, $marca, $categoria, $cor, $valor, $tags, $foto]);
    
    if ($id) {
        header('Location: guarda-roupa.php?sucesso=Peça cadastrada com sucesso!');
    } else {
        header('Location: cadastro.php?erro=Erro ao cadastrar peça');
    }
} else {
    header('Location: cadastro.php');
}
?>