<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;

if (!$cliente_id) {
    echo json_encode(['success' => false, 'error' => 'ID do cliente não fornecido']);
    exit;
}

try {
    $sql = "SELECT m.*, 'WhatsApp' as canal_nome
            FROM mensagens_comunicacao m
            WHERE m.cliente_id = ?
            ORDER BY m.data_hora ASC";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $mensagens = [];
    while ($msg = $result->fetch_assoc()) {
        $mensagens[] = [
            'id' => $msg['id'],
            'mensagem' => $msg['mensagem'],
            'direcao' => $msg['direcao'],
            'status' => $msg['status'],
            'data_hora' => $msg['data_hora'],
            'anexo' => $msg['anexo']
        ];
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'mensagens' => $mensagens,
        'total' => count($mensagens)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao carregar mensagens: ' . $e->getMessage()
    ]);
}
?> 