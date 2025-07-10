<?php
require_once __DIR__ . '/../config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao conectar ao banco de dados']);
    exit;
}
header('Content-Type: application/json');

// Filtros
$status = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
$data_emissao = isset($_GET['data_emissao']) && $_GET['data_emissao'] !== '' ? $_GET['data_emissao'] : null;
$data_vencimento = isset($_GET['data_vencimento']) && $_GET['data_vencimento'] !== '' ? $_GET['data_vencimento'] : null;
$cliente_id = isset($_GET['cliente_id']) && $_GET['cliente_id'] !== '' ? $_GET['cliente_id'] : null;

// Filtros de intervalo
$data_emissao_inicio = isset($_GET['data_emissao_inicio']) && $_GET['data_emissao_inicio'] !== '' ? $_GET['data_emissao_inicio'] : null;
$data_emissao_fim = isset($_GET['data_emissao_fim']) && $_GET['data_emissao_fim'] !== '' ? $_GET['data_emissao_fim'] : null;
$data_vencimento_inicio = isset($_GET['data_vencimento_inicio']) && $_GET['data_vencimento_inicio'] !== '' ? $_GET['data_vencimento_inicio'] : null;
$data_vencimento_fim = isset($_GET['data_vencimento_fim']) && $_GET['data_vencimento_fim'] !== '' ? $_GET['data_vencimento_fim'] : null;

$sql = "SELECT c.*, cli.nome AS cliente_nome, cli.email AS cliente_email
        FROM cobrancas c
        LEFT JOIN clientes cli ON c.cliente_id = cli.id
        WHERE 1";
$params = [];
if ($status) {
    $sql .= " AND c.status = ?";
    $params[] = $status;
}
if ($data_emissao_inicio && $data_emissao_fim) {
    $sql .= " AND DATE(c.data_criacao) BETWEEN ? AND ?";
    $params[] = $data_emissao_inicio;
    $params[] = $data_emissao_fim;
} elseif ($data_emissao_inicio) {
    $sql .= " AND DATE(c.data_criacao) >= ?";
    $params[] = $data_emissao_inicio;
} elseif ($data_emissao_fim) {
    $sql .= " AND DATE(c.data_criacao) <= ?";
    $params[] = $data_emissao_fim;
}
if ($data_vencimento_inicio && $data_vencimento_fim) {
    $sql .= " AND DATE(c.vencimento) BETWEEN ? AND ?";
    $params[] = $data_vencimento_inicio;
    $params[] = $data_vencimento_fim;
} elseif ($data_vencimento_inicio) {
    $sql .= " AND DATE(c.vencimento) >= ?";
    $params[] = $data_vencimento_inicio;
} elseif ($data_vencimento_fim) {
    $sql .= " AND DATE(c.vencimento) <= ?";
    $params[] = $data_vencimento_fim;
}
if ($cliente_id) {
    $sql .= " AND c.cliente_id = ?";
    $params[] = $cliente_id;
}
$sql .= " ORDER BY c.vencimento ASC";

// DEBUG: Logar query e parâmetros
file_put_contents(__DIR__ . '/../logs/debug_cobrancas.log', date('Y-m-d H:i:s') . "\nSQL: $sql\nParams: " . json_encode($params) . "\n", FILE_APPEND);

$stmt = $conn->prepare($sql);
if ($params) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$cobrancas = [];
while ($row = $result->fetch_assoc()) {
    $cobrancas[] = $row;
}
echo json_encode($cobrancas);
$stmt->close();
$conn->close(); 