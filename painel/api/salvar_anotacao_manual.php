<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';
require_once '../db.php';
header('Content-Type: application/json');

$cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
$titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$anotacao = isset($_POST['anotacao']) ? trim($_POST['anotacao']) : '';

if (!$cliente_id || !$anotacao) {
    echo json_encode(['success' => false, 'error' => 'Dados obrigatórios ausentes.']);
    exit;
}

// Montar mensagem final
$mensagem = $titulo ? ($titulo . "\n" . $anotacao) : $anotacao;

$stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (cliente_id, mensagem, tipo, data_hora, direcao) VALUES (?, ?, 'anotacao', NOW(), 'enviado')");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Erro prepare: ' . $mysqli->error]);
    exit;
}
$stmt->bind_param('is', $cliente_id, $mensagem);
if ($stmt->execute()) {
    $id = $stmt->insert_id;
    echo json_encode(['success' => true, 'id' => $id]);
} else {
    echo json_encode(['success' => false, 'error' => 'Erro execute: ' . $stmt->error]);
}
$stmt->close();
?> 