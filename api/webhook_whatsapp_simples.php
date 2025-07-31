<?php
/**
 * WEBHOOK SIMPLIFICADO PARA TESTE
 * 
 * Este endpoint recebe mensagens do servidor WhatsApp
 * e as processa sem depender do banco de dados
 */

// Cabeçalhos anti-cache
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Content-Type: application/json; charset=utf-8');

// Log de execução
$log_file = __DIR__ . '/../logs/webhook_simples_' . date('Y-m-d') . '.log';
$timestamp = date('Y-m-d H:i:s');

// Função para log
function logWebhook($message) {
    global $log_file, $timestamp;
    $log_entry = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

try {
    // Log início
    logWebhook("=== WEBHOOK SIMPLES INICIADO ===");
    
    // Pegar dados da requisição
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    logWebhook("Input recebido: " . substr($input, 0, 200));
    
    // Verificar se é uma mensagem
    if (isset($data['event']) && $data['event'] === 'onmessage') {
        $from = $data['data']['from'] ?? '';
        $text = $data['data']['text'] ?? '';
        $type = $data['data']['type'] ?? 'text';
        
        logWebhook("Mensagem recebida - De: $from, Texto: $text, Tipo: $type");
        
        // Processar mensagem
        $response = processarMensagem($from, $text, $type);
        
        logWebhook("Resposta gerada: " . json_encode($response));
        
        echo json_encode([
            'success' => true,
            'message' => 'Mensagem processada com sucesso',
            'data' => $response
        ]);
        
    } else {
        logWebhook("Evento não reconhecido: " . ($data['event'] ?? 'não definido'));
        
        echo json_encode([
            'success' => false,
            'message' => 'Evento não reconhecido',
            'event' => $data['event'] ?? 'não definido'
        ]);
    }
    
} catch (Exception $e) {
    logWebhook("ERRO: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno',
        'error' => $e->getMessage()
    ]);
}

/**
 * Processa a mensagem recebida
 */
function processarMensagem($from, $text, $type) {
    $text_lower = strtolower(trim($text));
    
    // Respostas automáticas simples
    $respostas = [
        'oi' => 'Olá! 👋 Este é um teste do webhook simplificado.',
        'ola' => 'Olá! 👋 Este é um teste do webhook simplificado.',
        'teste' => '✅ Teste funcionando! O webhook está recebendo mensagens.',
        'faturas' => '📋 Para consultar faturas, entre em contato com o suporte.',
        'ajuda' => '🤖 Este é um teste do sistema. Digite "teste" para confirmar.',
        'status' => '🟢 Sistema funcionando! Webhook ativo e recebendo mensagens.'
    ];
    
    // Buscar resposta
    foreach ($respostas as $palavra => $resposta) {
        if (strpos($text_lower, $palavra) !== false) {
            return [
                'action' => 'send',
                'to' => $from,
                'message' => $resposta,
                'trigger' => $palavra
            ];
        }
    }
    
    // Resposta padrão
    return [
        'action' => 'send',
        'to' => $from,
        'message' => '🤖 Mensagem recebida: "' . $text . '"\n\nEste é um teste do webhook simplificado. Digite "teste" para confirmar.',
        'trigger' => 'default'
    ];
}

logWebhook("=== WEBHOOK SIMPLES FINALIZADO ===");
?> 