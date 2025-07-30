<?php
/**
 * 📝 ÚLTIMA MENSAGEM DO CLIENTE
 * Retorna a última mensagem para atualizar preview da conversa
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;

if (!$cliente_id) {
    echo json_encode(['success' => false, 'message' => 'Cliente ID inválido']);
    exit;
}

try {
    // Buscar última mensagem do cliente
    $sql = "SELECT mensagem, direcao, data_hora 
            FROM mensagens_comunicacao 
            WHERE cliente_id = ? 
            ORDER BY data_hora DESC 
            LIMIT 1";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $mensagem = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'mensagem' => $mensagem['mensagem'],
            'direcao' => $mensagem['direcao'],
            'data_hora' => $mensagem['data_hora']
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'mensagem' => '',
            'direcao' => '',
            'data_hora' => null
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("[ÚLTIMA MENSAGEM] ❌ Erro: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
?> 