<?php
/**
 * 🚀 ENDPOINT DE NOTIFICAÇÃO PUSH
 * Recebe notificações do webhook e distribui para clientes conectados
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

// Log da notificação
$input = file_get_contents('php://input');
$data = json_decode($input, true);

error_log("[PUSH NOTIFICATION] 📥 Notificação recebida: " . $input);

if (!$data || !isset($data['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

try {
    switch ($data['action']) {
        case 'new_message':
            processarNovaMensagem($data);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
            exit;
    }
} catch (Exception $e) {
    error_log("[PUSH NOTIFICATION] ❌ Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}

/**
 * Processa nova mensagem recebida
 */
function processarNovaMensagem($data) {
    $cliente_id = $data['cliente_id'] ?? null;
    $numero = $data['numero'] ?? '';
    $mensagem = $data['mensagem'] ?? '';
    $mensagem_id = $data['mensagem_id'] ?? null;
    
    if (!$cliente_id) {
        echo json_encode(['success' => false, 'message' => 'Cliente ID inválido']);
        return;
    }
    
    // 1. Salvar notificação no banco para clientes conectados
    salvarNotificacao($cliente_id, $numero, $mensagem, $mensagem_id);
    
    // 2. Invalidar caches relacionados
    invalidarCaches($cliente_id);
    
    // 3. Enviar notificação para clientes WebSocket (se implementado)
    enviarWebSocketNotification($cliente_id, $data);
    
    echo json_encode([
        'success' => true,
        'message' => 'Notificação processada',
        'cliente_id' => $cliente_id,
        'mensagem_id' => $mensagem_id
    ]);
}

/**
 * Salva notificação no banco para clientes conectados
 */
function salvarNotificacao($cliente_id, $numero, $mensagem, $mensagem_id) {
    global $mysqli;
    
    $sql = "INSERT INTO notificacoes_push (cliente_id, numero, mensagem, mensagem_id, data_hora, status) 
            VALUES (?, ?, ?, ?, NOW(), 'pendente')";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('issi', $cliente_id, $numero, $mensagem, $mensagem_id);
    $stmt->execute();
    $stmt->close();
    
    error_log("[PUSH NOTIFICATION] 💾 Notificação salva para cliente $cliente_id");
}

/**
 * Invalida caches relacionados
 */
function invalidarCaches($cliente_id) {
    // Invalidar cache de mensagens
    if (function_exists('cache_forget')) {
        cache_forget("mensagens_{$cliente_id}");
        cache_forget("conversas_recentes");
        cache_forget("mensagens_html_{$cliente_id}");
        cache_forget("historico_html_{$cliente_id}");
    }
    
    // Invalidar cache de atividade
    $activityFile = sys_get_temp_dir() . "/chat_activity_{$cliente_id}.json";
    if (file_exists($activityFile)) {
        unlink($activityFile);
    }
    
    error_log("[PUSH NOTIFICATION] 🗑️ Caches invalidados para cliente $cliente_id");
}

/**
 * Envia notificação via WebSocket (preparado para implementação futura)
 */
function enviarWebSocketNotification($cliente_id, $data) {
    // TODO: Implementar WebSocket para notificações em tempo real
    // Por enquanto, apenas log
    error_log("[PUSH NOTIFICATION] 🔌 WebSocket notification para cliente $cliente_id (não implementado)");
}

// Criar tabela de notificações se não existir
function criarTabelaNotificacoes() {
    global $mysqli;
    
    $sql = "CREATE TABLE IF NOT EXISTS notificacoes_push (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cliente_id INT NOT NULL,
        numero VARCHAR(20) NOT NULL,
        mensagem TEXT NOT NULL,
        mensagem_id INT,
        data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pendente', 'enviada', 'lida') DEFAULT 'pendente',
        INDEX idx_cliente_status (cliente_id, status),
        INDEX idx_data_hora (data_hora)
    )";
    
    $mysqli->query($sql);
}

// Criar tabela na primeira execução
criarTabelaNotificacoes();
?> 