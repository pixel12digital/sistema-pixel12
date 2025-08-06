<?php
/**
 * 🔗 FALLBACK ANA - VERSÃO DE EMERGÊNCIA
 * 
 * Sistema de fallback quando a Ana não está funcionando
 */

header("Content-Type: application/json");

// Capturar dados
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(["success" => false, "error" => "Dados inválidos"]);
    exit;
}

$from = $data["from"] ?? $data["number"] ?? "desconhecido";
$body = $data["body"] ?? $data["message"] ?? "";

// Resposta de fallback inteligente
function gerarRespostaFallback($mensagem) {
    $msg = strtolower($mensagem);
    
    // Sites/Comercial
    if (strpos($msg, "site") !== false || strpos($msg, "loja") !== false || 
        strpos($msg, "ecommerce") !== false || strpos($msg, "orçamento") !== false) {
        return "🌐 Olá! Vou conectar você com Rafael, nosso especialista em desenvolvimento web! Ele tem experiência em criação de sites e lojas virtuais. Em breve receberá o contato dele! 🚀";
    }
    
    // Problemas/Suporte
    if (strpos($msg, "problema") !== false || strpos($msg, "erro") !== false || 
        strpos($msg, "bug") !== false || strpos($msg, "não funciona") !== false ||
        strpos($msg, "fora do ar") !== false || strpos($msg, "quebrou") !== false) {
        return "🔧 Identifiquei que você tem um problema técnico! Vou transferir para nossa equipe de suporte especializada que irá analisar e resolver sua questão. Aguarde o contato! 🛠️";
    }
    
    // Atendimento humano
    if (strpos($msg, "pessoa") !== false || strpos($msg, "humano") !== false || 
        strpos($msg, "atendente") !== false) {
        return "👥 Entendo que deseja falar com uma pessoa! Transferindo para nossa equipe de atendimento humano. Em breve alguém entrará em contato. Horário: Segunda a Sexta, 8h às 18h! 🤝";
    }
    
    // Saudações
    if (strpos($msg, "oi") !== false || strpos($msg, "olá") !== false || 
        strpos($msg, "bom dia") !== false || strpos($msg, "boa tarde") !== false) {
        return "👋 Olá! Sou a Ana da Pixel12Digital! Como posso ajudá-lo hoje?\n\n📋 Posso te ajudar com:\n🌐 Sites e lojas virtuais\n🔧 Suporte técnico\n👥 Atendimento geral\n\nConte-me sua necessidade!";
    }
    
    // Padrão
    return "😊 Olá! Sou a Ana da Pixel12Digital. Como posso ajudá-lo?\n\nPosso auxiliar com:\n🌐 Criação de sites\n🔧 Suporte técnico\n👥 Atendimento geral\n\nPara urgências: 47 97309525";
}

$resposta = gerarRespostaFallback($body);

// Salvar no banco se possível
try {
    require_once __DIR__ . "/../config.php";
    require_once "db.php";
    
    $canal_id = 36; // Canal Ana
    
    // Salvar mensagem recebida
    $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) VALUES (?, ?, ?, \"texto\", NOW(), \"recebido\", \"nao_lido\")");
    $stmt->bind_param("iss", $canal_id, $from, $body);
    $stmt->execute();
    $mensagem_id = $mysqli->insert_id;
    $stmt->close();
    
    // Salvar resposta
    $stmt = $mysqli->prepare("INSERT INTO mensagens_comunicacao (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) VALUES (?, ?, ?, \"texto\", NOW(), \"enviado\", \"entregue\")");
    $stmt->bind_param("iss", $canal_id, $from, $resposta);
    $stmt->execute();
    $resposta_id = $mysqli->insert_id;
    $stmt->close();
    
} catch (Exception $e) {
    // Ignorar erros de banco em fallback
}

// Resposta
echo json_encode([
    "success" => true,
    "message_id" => $mensagem_id ?? time(),
    "response_id" => $resposta_id ?? time() + 1,
    "ana_response" => $resposta,
    "fallback" => true,
    "note" => "Usando sistema de fallback - Ana temporariamente indisponível"
], JSON_UNESCAPED_UNICODE);
?>