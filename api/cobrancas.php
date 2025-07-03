<?php
require_once __DIR__ . '/../config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao conectar ao banco de dados']);
    exit;
}
header('Content-Type: application/json');

// Buscar cobranças PENDING e OVERDUE com nome do cliente
$sql = "SELECT c.*, cli.nome AS cliente_nome, cli.email AS cliente_email
        FROM cobrancas c
        LEFT JOIN clientes cli ON c.cliente_id = cli.id
        WHERE c.status IN ('PENDING', 'OVERDUE')
        ORDER BY c.vencimento ASC";
$result = $conn->query($sql);
$cobrancas = [];
while ($row = $result->fetch_assoc()) {
    $cobrancas[] = $row;
}
echo json_encode($cobrancas);
$conn->close(); 