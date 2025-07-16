<?php
require_once '../config.php';
require_once '../db.php';
header('Content-Type: application/json');
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$mensagem = isset($_POST['mensagem']) ? trim($_POST['mensagem']) : '';
if (!$id || !$mensagem) {
    echo json_encode(['success' => false, 'error' => 'Dados obrigatórios ausentes.']);
    exit;
}
$stmt = $mysqli->prepare('UPDATE mensagens_comunicacao SET mensagem = ? WHERE id = ?');
$stmt->bind_param('si', $mensagem, $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Erro ao atualizar mensagem.']);
}
$stmt->close(); 