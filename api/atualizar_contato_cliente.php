<?php
require_once '../config.php';
require_once '../painel/db.php';
header('Content-Type: application/json');
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$cliente_id = isset($data['cliente_id']) ? intval($data['cliente_id']) : 0;
$contact_name = isset($data['contact_name']) ? trim($data['contact_name']) : '';
if (!$cliente_id || !$contact_name) {
    echo json_encode(['success' => false, 'error' => 'Dados obrigatórios ausentes.']);
    exit;
}
$stmt = $mysqli->prepare('UPDATE clientes SET contact_name = ? WHERE id = ?');
$stmt->bind_param('si', $contact_name, $cliente_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
$stmt->close();
$mysqli->close(); 