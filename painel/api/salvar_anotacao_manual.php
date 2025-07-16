<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

$cliente_id = intval($_POST['cliente_id'] ?? 0);
$titulo = trim($_POST['titulo'] ?? '');
$anotacao = trim($_POST['anotacao'] ?? '');

if (!$cliente_id || !$anotacao) {
    echo json_encode(['success' => false, 'error' => 'Cliente ID e anotação são obrigatórios']);
    exit;
}

// Verificar se o cliente existe
$stmt = $mysqli->prepare("SELECT id FROM clientes WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $cliente_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Cliente não encontrado']);
    exit;
}
$stmt->close();

// Montar mensagem completa
$mensagem_completa = $anotacao;
if ($titulo) {
    $mensagem_completa = $titulo . "\n\n" . $anotacao;
}

// Inserir anotação na tabela mensagens_comunicacao
$stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (cliente_id, canal_id, mensagem, direcao, data_hora, tipo) VALUES (?, 1, ?, 'enviado', NOW(), 'anotacao')");
$stmt->bind_param('is', $cliente_id, $mensagem_completa);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $mysqli->insert_id]);
} else {
    echo json_encode(['success' => false, 'error' => 'Erro ao salvar anotação: ' . $stmt->error]);
}

$stmt->close();
?> 