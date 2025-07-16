<?php
require_once '../config.php';
require_once '../db.php';
require_once '../cache_manager.php';

header('Content-Type: application/json');
header('Cache-Control: private, max-age=60'); // Cache HTTP de 1 minuto

$cliente_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$cliente_id) {
    echo json_encode(['success' => false, 'error' => 'ID do cliente não fornecido']);
    exit;
}

// Cache específico para dados de telefone (mais leve que cache completo)
$dados = cache_remember("cliente_numero_{$cliente_id}", function() use ($cliente_id, $mysqli) {
    $sql = "SELECT id, celular, telefone FROM clientes WHERE id = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cliente = $result->fetch_assoc();
    $stmt->close();
    
    return $cliente;
}, 120); // Cache de 2 minutos para dados de telefone

if (!$dados) {
    echo json_encode(['success' => false, 'error' => 'Cliente não encontrado']);
    exit;
}

// Retornar apenas os dados necessários para o filtro
echo json_encode([
    'success' => true,
    'cliente' => [
        'id' => intval($dados['id']),
        'celular' => $dados['celular'] ?: '',
        'telefone' => $dados['telefone'] ?: ''
    ]
]);
?> 