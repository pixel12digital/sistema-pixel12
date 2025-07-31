<?php
/**
 * WEBHOOK BÁSICO - SEM DEPENDÊNCIAS
 * 
 * Este endpoint recebe mensagens do servidor WhatsApp
 * e responde com uma mensagem simples
 */

// Cabeçalhos básicos
header('Content-Type: application/json; charset=utf-8');

// Log simples
$log_file = __DIR__ . '/../logs/webhook_basico_' . date('Y-m-d') . '.log';
$timestamp = date('Y-m-d H:i:s');

// Função para log
function logBasico($message) {
    global $log_file, $timestamp;
    $log_entry = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

// Log início
logBasico("=== WEBHOOK BÁSICO INICIADO ===");

try {
    // Pegar dados da requisição
    $input = file_get_contents('php://input');
    logBasico("Input recebido: " . substr($input, 0, 200));
    
    // Decodificar JSON
    $data = json_decode($input, true);
    
    // Verificar se é uma mensagem
    if (isset($data['event']) && $data['event'] === 'onmessage') {
        $from = $data['data']['from'] ?? '';
        $text = $data['data']['text'] ?? '';
        $type = $data['data']['type'] ?? 'text';
        
        logBasico("Mensagem recebida - De: $from, Texto: $text, Tipo: $type");
        
        // Resposta simples
        $resposta = "🤖 Webhook básico funcionando!\n\nMensagem recebida: \"$text\"\n\nDigite 'teste' para confirmar.";
        
        logBasico("Resposta: $resposta");
        
        echo json_encode([
            'success' => true,
            'message' => 'Mensagem processada',
            'data' => [
                'action' => 'send',
                'to' => $from,
                'message' => $resposta
            ]
        ]);
        
    } else {
        logBasico("Evento não reconhecido: " . ($data['event'] ?? 'não definido'));
        
        echo json_encode([
            'success' => false,
            'message' => 'Evento não reconhecido',
            'event' => $data['event'] ?? 'não definido'
        ]);
    }
    
} catch (Exception $e) {
    logBasico("ERRO: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno',
        'error' => $e->getMessage()
    ]);
}

logBasico("=== WEBHOOK BÁSICO FINALIZADO ===");
?> 