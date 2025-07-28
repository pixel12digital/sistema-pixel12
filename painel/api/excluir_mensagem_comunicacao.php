<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}
$res = $mysqli->query("DELETE FROM mensagens_comunicacao WHERE id = $id LIMIT 1");
if ($res) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode([
        'success' => false,
        'error' => $mysqli->error,
        'query' => "DELETE FROM mensagens_comunicacao WHERE id = $id LIMIT 1"
    ]);
} 