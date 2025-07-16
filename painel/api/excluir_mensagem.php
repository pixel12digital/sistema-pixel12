<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

$id = intval($_POST['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID é obrigatório']);
    exit;
}

// Verificar se a mensagem existe
$stmt = $mysqli->prepare("SELECT id FROM mensagens_comunicacao WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Mensagem não encontrada']);
    exit;
}
$stmt->close();

// Excluir a mensagem
$stmt = $mysqli->prepare("DELETE FROM mensagens_comunicacao WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Erro ao excluir mensagem: ' . $stmt->error]);
}

$stmt->close();
?> 