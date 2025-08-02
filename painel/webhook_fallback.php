<?php
/**
 * 🚨 WEBHOOK FALLBACK - EMERGÊNCIA
 * 
 * Sistema temporário sem Ana AI para reestabelecer funcionamento
 */

header('Content-Type: application/json');

try {
    // Capturar dados de entrada
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Fallback para GET se POST falhar
    if (!$data && !empty($_GET)) {
        $data = $_GET;
    }
    
    // Normalizar campos
    $from = $data['from'] ?? $data['number'] ?? $data['phone'] ?? 'numero_desconhecido';
    $body = $data['body'] ?? $data['message'] ?? $data['text'] ?? '';
    
    // Log básico
    error_log("[WEBHOOK_FALLBACK] From: $from | Body: $body");
    
    // Respostas inteligentes básicas (sem Ana)
    $resposta = gerarRespostaFallback($body);
    $acao = detectarAcaoFallback($body);
    
    // Simular salvamento básico (sem banco para evitar erros)
    $message_id = time();
    $response_id = $message_id + 1;
    
    // Log da ação
    if ($acao !== 'nenhuma') {
        error_log("[WEBHOOK_FALLBACK] Ação detectada: $acao para $from");
        
        // Simular transferência (apenas log por enquanto)
        if ($acao === 'transfer_rafael') {
            error_log("[WEBHOOK_FALLBACK] TRANSFERÊNCIA RAFAEL: Cliente $from interessado em: $body");
        } elseif ($acao === 'transfer_suporte') {
            error_log("[WEBHOOK_FALLBACK] TRANSFERÊNCIA SUPORTE: Cliente $from com problema: $body");
        }
    }
    
    // Resposta para VPS
    $response = [
        'success' => true,
        'message_id' => $message_id,
        'response_id' => $response_id,
        'ana_response' => $resposta,
        'action_taken' => $acao,
        'fallback_mode' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'debug' => [
            'from' => $from,
            'body' => $body,
            'input_raw' => $input
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("[WEBHOOK_FALLBACK] ERRO: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'fallback_mode' => true,
        'ana_response' => 'Olá! Estou temporariamente em manutenção. Para urgências: 47 97309525'
    ]);
}

/**
 * Gerar resposta inteligente sem Ana
 */
function gerarRespostaFallback($mensagem) {
    $msg = strtolower($mensagem);
    
    // Comercial - Sites
    if (strpos($msg, 'site') !== false || strpos($msg, 'loja') !== false || 
        strpos($msg, 'ecommerce') !== false || strpos($msg, 'orçamento') !== false) {
        
        return "🌐 Olá! Vou te conectar com Rafael, nosso especialista em desenvolvimento web! Ele irá te orientar sobre criação de sites e lojas virtuais. Em breve você receberá o contato dele! 🚀";
    }
    
    // Suporte técnico
    if (strpos($msg, 'problema') !== false || strpos($msg, 'erro') !== false || 
        strpos($msg, 'bug') !== false || strpos($msg, 'não funciona') !== false ||
        strpos($msg, 'fora do ar') !== false || strpos($msg, 'quebrou') !== false) {
        
        return "🔧 Entendi que você está com um problema técnico! Vou transferir você para nossa equipe de suporte especializada. Eles irão analisar e resolver sua questão. Aguarde o contato! 🛠️";
    }
    
    // Atendimento humano
    if (strpos($msg, 'pessoa') !== false || strpos($msg, 'humano') !== false || 
        strpos($msg, 'atendente') !== false) {
        
        return "👥 Entendo que você quer falar com uma pessoa! Vou transferir você para nossa equipe de atendimento humano. Em breve alguém entrará em contato. Horário: Segunda a Sexta, 8h às 18h! 🤝";
    }
    
    // Saudações
    if (strpos($msg, 'oi') !== false || strpos($msg, 'olá') !== false || 
        strpos($msg, 'bom dia') !== false || strpos($msg, 'boa tarde') !== false) {
        
        return "👋 Olá! Sou a Ana da Pixel12Digital! Como posso ajudá-lo hoje?\n\n📋 Posso te ajudar com:\n🌐 Sites e lojas virtuais\n🔧 Suporte técnico\n👥 Atendimento geral\n\nDigite sua necessidade!";
    }
    
    // Resposta padrão
    return "😊 Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo?\n\nPosso te auxiliar com:\n🌐 Criação de sites\n🔧 Suporte técnico\n👥 Atendimento geral\n\nPara urgências: 47 97309525";
}

/**
 * Detectar ação sem Ana
 */
function detectarAcaoFallback($mensagem) {
    $msg = strtolower($mensagem);
    
    // Comercial - Rafael
    if (strpos($msg, 'site') !== false || strpos($msg, 'loja') !== false || 
        strpos($msg, 'ecommerce') !== false || strpos($msg, 'orçamento') !== false ||
        strpos($msg, 'quanto custa') !== false) {
        return 'transfer_rafael';
    }
    
    // Suporte técnico
    if (strpos($msg, 'problema') !== false || strpos($msg, 'erro') !== false || 
        strpos($msg, 'bug') !== false || strpos($msg, 'não funciona') !== false ||
        strpos($msg, 'fora do ar') !== false || strpos($msg, 'quebrou') !== false) {
        return 'transfer_suporte';
    }
    
    // Atendimento humano
    if (strpos($msg, 'pessoa') !== false || strpos($msg, 'humano') !== false) {
        return 'transfer_humano';
    }
    
    return 'nenhuma';
}
?> 