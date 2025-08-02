<?php
/**
 * 🔗 RECEPTOR DE MENSAGENS ANA - VERSÃO CORRIGIDA SIMPLES
 * 
 * Versão sem dependências problemáticas para teste
 */

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Não mostrar erros no output
ini_set('log_errors', 1);

try {
    // LOG: Capturar dados recebidos
    $input = file_get_contents('php://input');
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] [ANA_SIMPLES] Dados recebidos: $input");

    // Tentar diferentes métodos de obter os dados
    $data = null;

    // 1. Tentar JSON do corpo da requisição
    if (!empty($input)) {
        $data = json_decode($input, true);
    }

    // 2. Se JSON falhou, tentar $_POST
    if (!$data && !empty($_POST)) {
        $data = $_POST;
    }

    // 3. Se ainda não tem dados, tentar $_GET  
    if (!$data && !empty($_GET)) {
        $data = $_GET;
    }

    // Normalizar campos
    $from = null;
    $body = null;

    if ($data) {
        // Tentar diferentes campos para 'from'
        if (isset($data['from'])) $from = $data['from'];
        elseif (isset($data['number'])) $from = $data['number'];
        elseif (isset($data['phone'])) $from = $data['phone'];
        elseif (isset($data['sender'])) $from = $data['sender'];
        elseif (isset($data['chatId'])) $from = $data['chatId'];
        
        // Tentar diferentes campos para 'body'
        if (isset($data['body'])) $body = $data['body'];
        elseif (isset($data['message'])) $body = $data['message'];
        elseif (isset($data['text'])) $body = $data['text'];
        elseif (isset($data['content'])) $body = $data['content'];
        elseif (isset($data['msg'])) $body = $data['msg'];
    }

    error_log("[$timestamp] [ANA_SIMPLES] From: " . ($from ?? 'NULL') . ", Body: " . ($body ?? 'NULL'));

    // Validar se temos os dados mínimos necessários
    if (empty($from) || empty($body)) {
        $error_debug = [
            'error' => 'Dados incompletos',
            'from_found' => $from,
            'body_found' => $body,
            'timestamp' => $timestamp
        ];
        
        error_log("[$timestamp] [ANA_SIMPLES] ERRO: " . json_encode($error_debug));
        echo json_encode($error_debug);
        exit;
    }

    // Conectar com banco (forma simples)
    $mysqli = null;
    try {
        require_once __DIR__ . '/../config.php';
        require_once 'db.php';
        
        if (isset($mysqli) && $mysqli->ping()) {
            error_log("[$timestamp] [ANA_SIMPLES] ✅ Banco conectado");
        }
    } catch (Exception $e) {
        error_log("[$timestamp] [ANA_SIMPLES] ❌ Erro banco: " . $e->getMessage());
        // Continua sem banco por enquanto
    }

    // Processar com Ana (versão simples)
    $ana_response = null;
    $ana_success = false;
    
    try {
        $ana_url = 'https://agentes.pixel12digital.com.br/ai-agents/api/chat/agent_chat.php';
        $ana_payload = json_encode([
            'question' => $body,
            'agent_id' => '3'
        ]);
        
        $ch = curl_init($ana_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $ana_payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $ana_raw = curl_exec($ch);
        $ana_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($ana_code === 200 && $ana_raw) {
            $ana_data = json_decode($ana_raw, true);
            if (isset($ana_data['success']) && $ana_data['success'] && !empty($ana_data['response'])) {
                $ana_response = $ana_data['response'];
                $ana_success = true;
                error_log("[$timestamp] [ANA_SIMPLES] ✅ Ana respondeu: " . substr($ana_response, 0, 50) . "...");
            }
        }
        
        if (!$ana_success) {
            error_log("[$timestamp] [ANA_SIMPLES] ❌ Ana falhou (HTTP $ana_code): " . substr($ana_raw, 0, 100));
        }
        
    } catch (Exception $e) {
        error_log("[$timestamp] [ANA_SIMPLES] ❌ Erro Ana: " . $e->getMessage());
    }

    // Fallback se Ana falhar
    if (!$ana_success) {
        $ana_response = "Olá! Sou a Ana da Pixel12Digital. No momento estou com uma instabilidade, mas em breve retorno. Para urgências, contate 47 97309525. 😊";
    }

    // Detectar ações (versão simples)
    $transfer_rafael = false;
    $transfer_humano = false;
    $action_taken = 'resposta_normal';
    
    $body_lower = strtolower($body);
    $response_lower = strtolower($ana_response);
    
    // Detectar frases de ativação da Ana
    if (strpos($response_lower, 'ativar_transferencia_rafael') !== false) {
        $transfer_rafael = true;
        $action_taken = 'transfer_rafael';
        error_log("[$timestamp] [ANA_SIMPLES] 🎯 Transferência Rafael detectada via Ana");
    } elseif (strpos($response_lower, 'ativar_transferencia_suporte') !== false || 
              strpos($response_lower, 'ativar_transferencia_humano') !== false) {
        $transfer_humano = true;
        $action_taken = 'transfer_humano';
        error_log("[$timestamp] [ANA_SIMPLES] 🎯 Transferência Humano detectada via Ana");
    } else {
        // Detecção fallback por palavras-chave
        $comercial_keywords = ['quero um site', 'preciso de um site', 'criar um site', 'loja virtual', 'ecommerce', 'orçamento'];
        $suporte_keywords = ['meu site está', 'site fora do ar', 'site não funciona', 'problema no site', 'erro'];
        
        foreach ($comercial_keywords as $keyword) {
            if (strpos($body_lower, $keyword) !== false) {
                $transfer_rafael = true;
                $action_taken = 'transfer_rafael_fallback';
                error_log("[$timestamp] [ANA_SIMPLES] 🎯 Transferência Rafael detectada via fallback");
                break;
            }
        }
        
        if (!$transfer_rafael) {
            foreach ($suporte_keywords as $keyword) {
                if (strpos($body_lower, $keyword) !== false) {
                    $transfer_humano = true;
                    $action_taken = 'transfer_humano_fallback';
                    error_log("[$timestamp] [ANA_SIMPLES] 🎯 Transferência Humano detectada via fallback");
                    break;
                }
            }
        }
    }

    // Salvar no banco (se disponível)
    $message_id = 0;
    $response_id = 0;
    
    if ($mysqli && $mysqli->ping()) {
        try {
            // Escapar dados
            $from_safe = $mysqli->real_escape_string($from);
            $body_safe = $mysqli->real_escape_string($body);
            $response_safe = $mysqli->real_escape_string($ana_response);
            
            // Salvar mensagem recebida
            $sql_msg = "INSERT INTO mensagens_comunicacao (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                        VALUES (36, '$from_safe', '$body_safe', 'texto', NOW(), 'recebido', 'nao_lido')";
            
            if ($mysqli->query($sql_msg)) {
                $message_id = $mysqli->insert_id;
                error_log("[$timestamp] [ANA_SIMPLES] ✅ Mensagem salva: ID $message_id");
            }
            
            // Salvar resposta
            $sql_resp = "INSERT INTO mensagens_comunicacao (canal_id, numero_whatsapp, mensagem, tipo, data_hora, direcao, status) 
                         VALUES (36, '$from_safe', '$response_safe', 'texto', NOW(), 'enviado', 'entregue')";
            
            if ($mysqli->query($sql_resp)) {
                $response_id = $mysqli->insert_id;
                error_log("[$timestamp] [ANA_SIMPLES] ✅ Resposta salva: ID $response_id");
            }
            
        } catch (Exception $e) {
            error_log("[$timestamp] [ANA_SIMPLES] ❌ Erro ao salvar no banco: " . $e->getMessage());
        }
    }

    // Resposta final
    $final_response = [
        'success' => true,
        'message_id' => $message_id ?: time(),
        'response_id' => $response_id ?: (time() + 1),
        'ana_response' => $ana_response,
        'action_taken' => $action_taken,
        'transfer_rafael' => $transfer_rafael,
        'transfer_humano' => $transfer_humano,
        'from' => $from,
        'body' => $body,
        'timestamp' => $timestamp,
        'version' => 'ana_simples_corrigido',
        'ana_api_success' => $ana_success
    ];

    error_log("[$timestamp] [ANA_SIMPLES] ✅ Resposta final: " . json_encode($final_response));
    echo json_encode($final_response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    $error_response = [
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => 'ana_simples_corrigido'
    ];
    
    error_log("[ERROR] [ANA_SIMPLES] " . json_encode($error_response));
    
    http_response_code(500);
    echo json_encode($error_response);
}

// Fechar conexão se existir
if (isset($mysqli) && $mysqli) {
    $mysqli->close();
}
?> 