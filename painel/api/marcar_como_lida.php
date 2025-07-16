<?php
require_once '../config.php';
require_once '../db.php';
require_once '../cache_invalidator.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

$cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;

if (!$cliente_id) {
    echo json_encode(['success' => false, 'error' => 'ID do cliente não fornecido']);
    exit;
}

try {
    // Marcar todas as mensagens recebidas deste cliente como lidas
    $sql = "UPDATE mensagens_comunicacao 
            SET status = 'lido' 
            WHERE cliente_id = ? 
            AND direcao = 'recebido' 
            AND status != 'lido'";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $cliente_id);
    $stmt->execute();
    
    $mensagens_atualizadas = $stmt->affected_rows;
    $stmt->close();
    
    if ($mensagens_atualizadas > 0) {
        // Invalidar caches relacionados
        $invalidator = new CacheInvalidator();
        $invalidator->onMessageRead($cliente_id);
        
        // Invalidar cache de conversas não lidas
        cache_forget("conversas_nao_lidas");
        cache_forget("total_mensagens_nao_lidas");
        
        echo json_encode([
            'success' => true,
            'mensagens_atualizadas' => $mensagens_atualizadas,
            'message' => 'Mensagens marcadas como lidas'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'mensagens_atualizadas' => 0,
            'message' => 'Nenhuma mensagem nova para marcar como lida'
        ]);
    }
    
} catch (Exception $e) {
    error_log('Erro ao marcar mensagens como lidas: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor']);
}
?> 