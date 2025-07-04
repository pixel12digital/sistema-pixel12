<?php
require_once __DIR__ . '/../config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao conectar ao banco de dados']);
    exit;
}
header('Content-Type: application/json');
$res = $conn->query('SELECT id, nome FROM clientes ORDER BY nome ASC');
$clientes = [];
while ($row = $res->fetch_assoc()) {
    $clientes[] = $row;
}
echo json_encode($clientes);
$conn->close(); 