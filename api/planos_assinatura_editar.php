<?php
require_once __DIR__ . '/../painel/db.php';
header('Content-Type: application/json');
$id = intval($_POST['id'] ?? 0);
$nome = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$valor = floatval($_POST['valor'] ?? 0);
$periodicidade = $_POST['periodicidade'] ?? '';
if (!$id || !$nome || !$valor || !$periodicidade) {
    echo json_encode(['success' => false, 'error' => 'Dados obrigatórios ausentes.']);
    exit;
}
$stmt = $mysqli->prepare("UPDATE planos_assinatura SET nome=?, descricao=?, valor=?, periodicidade=? WHERE id=?");
$stmt->bind_param('ssdsi', $nome, $descricao, $valor, $periodicidade, $id);
$ok = $stmt->execute();
$stmt->close();
echo json_encode(['success' => $ok]); 