<?php
/**
 * 🚨 WEBHOOK ANA - RAIZ (EMERGÊNCIA)
 * 
 * Webhook direto na raiz para contornar problemas de path
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
    // Capturar dados
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Fallback para GET
    if (!$data && !empty($_GET)) {
        $data = $_GET;
    }
    
    // Normalizar
    $from = $data['from'] ?? $data['number'] ?? $data['phone'] ?? 'desconhecido';
    $body = $data['body'] ?? $data['message'] ?? $data['text'] ?? '';
    
    // Log
    error_log("[WEBHOOK_RAIZ] " . date('Y-m-d H:i:s') . " | From: $from | Body: $body");
    
    // Gerar resposta
    $resposta = gerarResposta($body);
    $acao = detectarAcao($body);
    
    // Log ação
    if ($acao !== 'nenhuma') {
        error_log("[WEBHOOK_RAIZ] AÇÃO: $acao | Cliente: $from | Mensagem: $body");
    }
    
    // Resposta
    $response = [
        'success' => true,
        'message_id' => time(),
        'response_id' => time() + 1,
        'ana_response' => $resposta,
        'action_taken' => $acao,
        'webhook_version' => 'raiz_emergencia',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("[WEBHOOK_RAIZ] ERRO: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'ana_response' => 'Olá! Sistema temporariamente em manutenção. Para urgências: 47 97309525'
    ]);
}

function gerarResposta($mensagem) {
    $msg = strtolower($mensagem);
    
    // Sites/Comercial
    if (strpos($msg, 'site') !== false || strpos($msg, 'loja') !== false || 
        strpos($msg, 'ecommerce') !== false || strpos($msg, 'orçamento') !== false) {
        return "🌐 Olá! Vou conectar você com Rafael, nosso especialista em desenvolvimento web! Ele tem experiência em criação de sites e lojas virtuais. Em breve receberá o contato dele! 🚀";
    }
    
    // Problemas/Suporte
    if (strpos($msg, 'problema') !== false || strpos($msg, 'erro') !== false || 
        strpos($msg, 'bug') !== false || strpos($msg, 'não funciona') !== false ||
        strpos($msg, 'fora do ar') !== false || strpos($msg, 'quebrou') !== false) {
        return "🔧 Identifiquei que você tem um problema técnico! Vou transferir para nossa equipe de suporte especializada que irá analisar e resolver sua questão. Aguarde o contato! 🛠️";
    }
    
    // Atendimento humano
    if (strpos($msg, 'pessoa') !== false || strpos($msg, 'humano') !== false || 
        strpos($msg, 'atendente') !== false) {
        return "👥 Entendo que deseja falar com uma pessoa! Transferindo para nossa equipe de atendimento humano. Em breve alguém entrará em contato. Horário: Segunda a Sexta, 8h às 18h! 🤝";
    }
    
    // Saudações
    if (strpos($msg, 'oi') !== false || strpos($msg, 'olá') !== false || 
        strpos($msg, 'bom dia') !== false || strpos($msg, 'boa tarde') !== false) {
        return "👋 Olá! Sou a Ana da Pixel12Digital! Como posso ajudá-lo hoje?\n\n📋 Posso te ajudar com:\n🌐 Sites e lojas virtuais\n🔧 Suporte técnico\n👥 Atendimento geral\n\nConte-me sua necessidade!";
    }
    
    // Padrão
    return "😊 Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo?\n\nPosso auxiliar com:\n🌐 Criação de sites\n🔧 Suporte técnico\n👥 Atendimento geral\n\nPara urgências: 47 97309525";
}

function detectarAcao($mensagem) {
    $msg = strtolower($mensagem);
    
    // Rafael - Comercial
    if (strpos($msg, 'site') !== false || strpos($msg, 'loja') !== false || 
        strpos($msg, 'ecommerce') !== false || strpos($msg, 'orçamento') !== false) {
        return 'transfer_rafael';
    }
    
    // Suporte
    if (strpos($msg, 'problema') !== false || strpos($msg, 'erro') !== false || 
        strpos($msg, 'bug') !== false || strpos($msg, 'não funciona') !== false ||
        strpos($msg, 'fora do ar') !== false) {
        return 'transfer_suporte';
    }
    
    // Humano
    if (strpos($msg, 'pessoa') !== false || strpos($msg, 'humano') !== false) {
        return 'transfer_humano';
    }
    
    return 'nenhuma';
}
?> 