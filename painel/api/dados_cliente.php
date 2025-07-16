<?php
require_once '../config.php';
require_once '../db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID do cliente é obrigatório']);
    exit;
}

$cliente_id = intval($_GET['id']);

try {
    $stmt = $mysqli->prepare("SELECT id, nome, email, telefone, celular FROM clientes WHERE id = ?");
    $stmt->bind_param('i', $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($cliente = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'id' => $cliente['id'],
            'nome' => $cliente['nome'],
            'email' => $cliente['email'],
            'telefone' => $cliente['telefone'],
            'celular' => $cliente['celular']
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Cliente não encontrado']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}

$mysqli->close();
?> 