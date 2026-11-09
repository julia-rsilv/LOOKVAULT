<?php
session_start();
require_once 'configdatabase.php';

if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['erro' => 'Faça login para acessar as estatísticas.']);
    exit;
}
$usuario_id = (int) $_SESSION['usuario_id'];
header('Content-Type: application/json; charset=utf-8');
$conn = conectarDB();

// Total de peças
$sql = "SELECT COUNT(*) as total FROM pecas WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$total_pecas = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Peças mais usadas
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

// Custo médio
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
    $categorias[] = ['categoria' => $row['categoria'], 'total' => (int)$row['total']];
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
    $marcas[] = ['marca' => $row['marca'], 'total' => (int)$row['total']];
}
$stmt->close();

// Atividades - CORRIGIDO para mostrar tempo real
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
    
    // Calcular diferença em minutos, horas, dias
    $minutos = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
    
    if ($minutos < 1) {
        $tempo = 'Agora mesmo';
    } elseif ($minutos < 60) {
        $tempo = $minutos . ' min atrás';
    } elseif ($minutos < 1440) {
        $horas = floor($minutos / 60);
        $tempo = $horas . 'h atrás';
    } elseif ($diff->days == 1) {
        $tempo = 'Ontem';
    } else {
        $tempo = $diff->days . ' dias atrás';
    }
    $atividades[] = ['acao' => $row['acao'], 'descricao' => $row['descricao'], 'tempo' => $tempo];
}
$stmt->close();

$conn->close();

echo json_encode([
    'total_pecas' => $total_pecas,
    'mais_usadas' => $mais_usadas,
    'custo_medio' => $custo_medio,
    'valor_total' => $valor_total,
    'categorias' => $categorias,
    'marcas' => $marcas,
    'atividades' => $atividades
]);
?>