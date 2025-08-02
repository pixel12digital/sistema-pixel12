<?php
/**
 * 🎯 WEBHOOK ANA - ARQUIVO FÍSICO
 * 
 * Contorna o redirecionamento do .htaccess
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Lidar com preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Log de entrada
    error_log("[WEBHOOK_FISICO] " . date('Y-m-d H:i:s') . " - Webhook Ana ativado");
    
    // Capturar dados
    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?: $_GET;
    
    $from = $data['from'] ?? $data['number'] ?? $data['phone'] ?? 'desconhecido';
    $body = $data['body'] ?? $data['message'] ?? $data['text'] ?? '';
    
    // Log dados
    error_log("[WEBHOOK_FISICO] From: $from | Body: $body");
    
    // Resposta inteligente
    $resposta = "Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo?";
    $acao = 'nenhuma';
    
    // Detectar intenção
    $msg = strtolower($body);
    
    if (strpos($msg, 'site') !== false || strpos($msg, 'loja') !== false || 
        strpos($msg, 'ecommerce') !== false || strpos($msg, 'orçamento') !== false) {
        $resposta = "🌐 Olá! Vou conectar você com Rafael, nosso especialista em desenvolvimento web! Ele tem experiência em criação de sites e lojas virtuais. Em breve receberá o contato dele! 🚀";
        $acao = 'transfer_rafael';
    } elseif (strpos($msg, 'problema') !== false || strpos($msg, 'erro') !== false || 
              strpos($msg, 'bug') !== false || strpos($msg, 'não funciona') !== false ||
              strpos($msg, 'fora do ar') !== false || strpos($msg, 'quebrou') !== false) {
        $resposta = "🔧 Identifiquei que você tem um problema técnico! Vou transferir para nossa equipe de suporte especializada que irá analisar e resolver sua questão. Aguarde o contato! 🛠️";
        $acao = 'transfer_suporte';
    } elseif (strpos($msg, 'pessoa') !== false || strpos($msg, 'humano') !== false || 
              strpos($msg, 'atendente') !== false) {
        $resposta = "👥 Entendo que deseja falar com uma pessoa! Transferindo para nossa equipe de atendimento humano. Em breve alguém entrará em contato. Horário: Segunda a Sexta, 8h às 18h! 🤝";
        $acao = 'transfer_humano';
    } elseif (strpos($msg, 'oi') !== false || strpos($msg, 'olá') !== false || 
              strpos($msg, 'bom dia') !== false || strpos($msg, 'boa tarde') !== false) {
        $resposta = "👋 Olá! Sou a Ana da Pixel12Digital! Como posso ajudá-lo hoje?\n\n📋 Posso te ajudar com:\n🌐 Sites e lojas virtuais\n🔧 Suporte técnico\n👥 Atendimento geral\n\nConte-me sua necessidade!";
    }
    
    // Log ação
    if ($acao !== 'nenhuma') {
        error_log("[WEBHOOK_FISICO] AÇÃO: $acao | Cliente: $from | Mensagem: $body");
    }
    
    // Resposta JSON
    $response = [
        'success' => true,
        'message_id' => time(),
        'response_id' => time() + 1,
        'ana_response' => $resposta,
        'action_taken' => $acao,
        'webhook_version' => 'arquivo_fisico',
        'timestamp' => date('Y-m-d H:i:s'),
        'debug' => [
            'from' => $from,
            'body' => $body,
            'method' => $_SERVER['REQUEST_METHOD']
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("[WEBHOOK_FISICO] ERRO: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'ana_response' => 'Olá! Sistema temporariamente em manutenção. Para urgências: 47 97309525'
    ]);
}
?> 