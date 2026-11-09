<?php
session_start();
require_once 'configdatabase.php';

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome_look'] ?? '');
    $ocasiao = $_POST['ocasiao'] ?? '';
    $pecas_selecionadas = $_POST['pecas_selecionadas'] ?? '';
    $look_id = $_POST['look_id'] ?? 0;
    $acao = $_POST['acao'] ?? 'criar';
    
    // Converter string de peças para array
    $pecas = [];
    if (!empty($pecas_selecionadas)) {
        $pecas = array_map('intval', explode(',', $pecas_selecionadas));
    }
    
    // Validação
    if (empty($nome)) {
        $erro = urlencode('Por favor, dê um nome para o look!');
        header("Location: criar-look.php?id=$look_id&erro=$erro");
        exit;
    }
    
    if (empty($pecas)) {
        $erro = urlencode('Selecione pelo menos uma peça para o look!');
        header("Location: criar-look.php?id=$look_id&erro=$erro");
        exit;
    }

    // Remove IDs repetidos e impede associar peças de outra conta.
    $pecas = array_values(array_unique(array_filter($pecas, function ($id) {
        return $id > 0;
    })));
    if (empty($pecas)) {
        header('Location: criar-look.php?erro=' . urlencode('Selecione peças válidas.'));
        exit;
    }
    $placeholders = implode(',', array_fill(0, count($pecas), '?'));
    $params_validacao = array_merge([$usuario_id], $pecas);
    $result_validacao = executarQuery(
        "SELECT id FROM pecas WHERE usuario_id = ? AND id IN ($placeholders)",
        $params_validacao
    );
    if (!$result_validacao || $result_validacao->num_rows !== count($pecas)) {
        header('Location: criar-look.php?erro=' . urlencode('Uma ou mais peças selecionadas não são válidas.'));
        exit;
    }
    
    if ($acao === 'editar' && $look_id > 0) {
        // EDITAR LOOK
        $sql = "UPDATE looks SET nome = ?, ocasiao = ? WHERE id = ? AND usuario_id = ?";
        $result = executarQuery($sql, [$nome, $ocasiao, $look_id, $usuario_id]);
        
        if ($result) {
            // Remover peças antigas
            $sql_del = "DELETE FROM look_pecas WHERE look_id = ?";
            executarQuery($sql_del, [$look_id]);
            
            // Adicionar novas peças
            foreach ($pecas as $peca_id) {
                $sql_insert = "INSERT INTO look_pecas (look_id, peca_id) VALUES (?, ?)";
                executarQuery($sql_insert, [$look_id, $peca_id]);
            }
            
            header('Location: combinacoes.php?sucesso=Look atualizado com sucesso!');
        } else {
            $erro = urlencode('Erro ao atualizar look');
            header("Location: criar-look.php?id=$look_id&erro=$erro");
        }
    } else {
        // CRIAR NOVO LOOK
        $sql = "INSERT INTO looks (usuario_id, nome, ocasiao) VALUES (?, ?, ?)";
        $look_id = inserirRetornarId($sql, [$usuario_id, $nome, $ocasiao]);
        
        if ($look_id) {
            foreach ($pecas as $peca_id) {
                $sql_insert = "INSERT INTO look_pecas (look_id, peca_id) VALUES (?, ?)";
                executarQuery($sql_insert, [$look_id, $peca_id]);
            }
            
            header('Location: combinacoes.php?sucesso=Look criado com sucesso!');
        } else {
            $erro = urlencode('Erro ao criar look');
            header("Location: criar-look.php?erro=$erro");
        }
    }
} else {
    header('Location: combinacoes.php');
}
?>