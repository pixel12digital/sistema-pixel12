<?php
/**
 * 🔗 RECEPTOR DE MENSAGENS ANA - VERSÃO SIMPLIFICADA
 * 
 * Recebe mensagens do WhatsApp Canal 3000 e processa via Ana
 * Versão simplificada sem conflitos
 */

header('Content-Type: application/json');

// LOG: Capturar dados recebidos
$input = file_get_contents('php://input');
error_log("[RECEBIMENTO_ANA] Dados recebidos: " . $input);

$data = json_decode($input, true);

if (!isset($data['from']) || !isset($data['body'])) {
    error_log("[RECEBIMENTO_ANA] ERRO: Dados incompletos");
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit;
}

$from = $data['from'];
$body = $data['body'];
$timestamp = $data['timestamp'] ?? time();

try {
    // Conectar com banco
    require_once __DIR__ . '/../config.php';
    require_once 'db.php';
    
    // Canal Ana (3000)
    $canal_id = 36;
    $canal_nome = "Pixel12Digital";
    
    error_log("[RECEBIMENTO_ANA] Processando via Ana - Canal: $canal_nome (ID: $canal_id)");
    
    // 1. SALVAR MENSAGEM RECEBIDA
    $data_hora = date('Y-m-d H:i:s', $timestamp);
    $sql_mensagem = "INSERT INTO mensagens_comunicacao 
                     (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                     VALUES (?, ?, ?, 'texto', ?, 'recebido', 'nao_lido')";
    
    $stmt = $mysqli->prepare($sql_mensagem);
    $stmt->bind_param('isss', $canal_id, $from, $body, $data_hora);
    $stmt->execute();
    $mensagem_id = $mysqli->insert_id;
    $stmt->close();
    
    error_log("[RECEBIMENTO_ANA] Mensagem salva - ID: $mensagem_id");
    
    // 2. CHAMAR ANA VIA API EXTERNA
    $ana_payload = [
        'question' => $body,
        'agent_id' => '3'
    ];
    
    $ch = curl_init('https://agentes.pixel12digital.com.br/api/chat/agent_chat.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ana_payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $ana_response = curl_exec($ch);
    $ana_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $ana_error = curl_error($ch);
    curl_close($ch);
    
    $resposta_ana = "";
    $sucesso_ana = false;
    
    if ($ana_error) {
        error_log("[RECEBIMENTO_ANA] Erro cURL Ana: $ana_error");
    } elseif ($ana_http_code === 200) {
        $ana_data = json_decode($ana_response, true);
        if ($ana_data && isset($ana_data['response'])) {
            $resposta_ana = $ana_data['response'];
            $sucesso_ana = true;
            error_log("[RECEBIMENTO_ANA] Ana respondeu com sucesso");
        }
    }
    
    // 3. FALLBACK SE ANA FALHAR
    if (!$sucesso_ana || empty($resposta_ana)) {
        $resposta_ana = "Olá! Sou a Ana da Pixel12Digital. No momento estou com uma instabilidade, mas em breve retorno. Para urgências, contate 47 97309525. 😊";
        error_log("[RECEBIMENTO_ANA] Usando resposta de fallback");
    }
    
    // 4. SALVAR RESPOSTA DA ANA
    $sql_resposta = "INSERT INTO mensagens_comunicacao 
                     (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                     VALUES (?, ?, ?, 'texto', NOW(), 'enviado', 'entregue')";
    
    $stmt = $mysqli->prepare($sql_resposta);
    $stmt->bind_param('iss', $canal_id, $from, $resposta_ana);
    $stmt->execute();
    $resposta_id = $mysqli->insert_id;
    $stmt->close();
    
    // 5. ENVIAR RESPOSTA PARA WHATSAPP
    $whatsapp_payload = [
        'sessionName' => 'default',
        'number' => $from,
        'message' => $resposta_ana
    ];
    
    $ch = curl_init('http://212.85.11.238:3000/send/text');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($whatsapp_payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $whatsapp_response = curl_exec($ch);
    $whatsapp_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($whatsapp_http_code === 200) {
        error_log("[RECEBIMENTO_ANA] Resposta enviada para WhatsApp com sucesso");
    } else {
        error_log("[RECEBIMENTO_ANA] Erro ao enviar para WhatsApp: HTTP $whatsapp_http_code");
    }
    
    // 6. RESPOSTA PARA O WEBHOOK
    echo json_encode([
        'success' => true,
        'message_id' => $mensagem_id,
        'response_id' => $resposta_id,
        'ana_response' => $resposta_ana,
        'ana_success' => $sucesso_ana,
        'whatsapp_sent' => ($whatsapp_http_code === 200)
    ]);
    
} catch (Exception $e) {
    error_log("[RECEBIMENTO_ANA] ERRO CRÍTICO: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor',
        'message' => 'Mensagem recebida mas não processada',
        'debug' => $e->getMessage()
    ]);
}
?> 