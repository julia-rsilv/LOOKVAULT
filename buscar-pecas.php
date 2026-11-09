<?php
session_start();
require_once 'configdatabase.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) {
    echo json_encode([]);
    exit;
}

$termo = $_GET['termo'] ?? '';

$sql = "SELECT * FROM pecas WHERE usuario_id = ?";
$params = [$usuario_id];

if (!empty($termo)) {
    $sql .= " AND (nome LIKE ? OR marca LIKE ? OR categoria LIKE ?)";
    $like = "%$termo%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$result = executarQuery($sql, $params);

$pecas = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pecas[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($pecas);
?>