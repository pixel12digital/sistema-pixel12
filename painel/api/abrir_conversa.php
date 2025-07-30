<?php
/**
 * API para Abertura Manual de Conversas
 * 
 * Endpoint: POST /api/abrir_conversa.php
 * Parâmetros: cliente_id (obrigatório)
 * Retorna: JSON com status da operação
 * 
 * IMPORTANTE: NÃO envia notificação WhatsApp ao reabrir conversa
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

// Verificar parâmetros
$cliente_id = intval($_POST['cliente_id'] ?? 0);

if (!$cliente_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'cliente_id é obrigatório']);
    exit;
}

try {
    // Verificar se cliente existe
    $stmt = $mysqli->prepare("SELECT id, nome FROM clientes WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Cliente não encontrado']);
        exit;
    }
    
    $cliente = $result->fetch_assoc();
    $stmt->close();
    
    // Marcar todas as mensagens da conversa como aberta
    $stmt = $mysqli->prepare("UPDATE mensagens_comunicacao SET status_conversa = 'aberta' WHERE cliente_id = ?");
    $stmt->bind_param('i', $cliente_id);
    
    if ($stmt->execute()) {
        $mensagens_afetadas = $stmt->affected_rows;
        
        // Registrar ação no log (SEM enviar notificação WhatsApp)
        $data_hora = date('Y-m-d H:i:s');
        $usuario = $_SESSION['usuario_id'] ?? 'sistema';
        $log_sql = "INSERT INTO mensagens_comunicacao (canal_id, cliente_id, mensagem, tipo, data_hora, direcao, status, status_conversa) 
                    VALUES (1, ?, 'Conversa reaberta manualmente por $usuario', 'sistema', ?, 'enviado', 'enviado', 'aberta')";
        
        $log_stmt = $mysqli->prepare($log_sql);
        $log_stmt->bind_param('is', $cliente_id, $data_hora);
        $log_stmt->execute();
        $log_stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Conversa reaberta com sucesso (sem notificação)',
            'cliente_id' => $cliente_id,
            'cliente_nome' => $cliente['nome'],
            'mensagens_afetadas' => $mensagens_afetadas,
            'reaberta_por' => $usuario,
            'data_hora' => $data_hora,
            'notificacao_enviada' => false
        ]);
    } else {
        throw new Exception("Erro ao reabrir conversa: " . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno: ' . $e->getMessage()
    ]);
}
?>