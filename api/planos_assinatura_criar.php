<?php
require_once __DIR__ . '/../painel/db.php';
header('Content-Type: application/json');
$nome = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$valor = floatval($_POST['valor'] ?? 0);
$periodicidade = $_POST['periodicidade'] ?? '';
if (!$nome || !$valor || !$periodicidade) {
    echo json_encode(['success' => false, 'error' => 'Dados obrigatórios ausentes.']);
    exit;
}
$stmt = $mysqli->prepare("INSERT INTO planos_assinatura (nome, descricao, valor, periodicidade, ativo) VALUES (?, ?, ?, ?, 1)");
$stmt->bind_param('ssds', $nome, $descricao, $valor, $periodicidade);
$ok = $stmt->execute();
$stmt->close();
echo json_encode(['success' => $ok]); 