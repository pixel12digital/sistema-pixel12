<?php
/**
 * 🧪 WEBHOOK TESTE SIMPLES
 * 
 * Versão mínima para testar no servidor
 */

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

try {
    // Log da requisição
    $input = file_get_contents('php://input');
    $timestamp = date('Y-m-d H:i:s');
    
    error_log("[$timestamp] WEBHOOK_TESTE: Recebido - $input");
    
    // Decodificar dados
    $data = json_decode($input, true);
    
    if (!$data) {
        // Fallback para $_POST
        $data = $_POST;
    }
    
    $from = $data['from'] ?? 'desconhecido';
    $body = $data['body'] ?? $data['message'] ?? 'mensagem vazia';
    
    error_log("[$timestamp] WEBHOOK_TESTE: From=$from, Body=$body");
    
    // Teste básico da Ana
    $ana_response = "Olá! Sou a Ana (modo teste). Recebi sua mensagem: '$body'";
    
    // Verificar se é comercial
    $is_comercial = false;
    $comercial_keywords = ['site', 'loja', 'ecommerce', 'orçamento', 'preço'];
    foreach ($comercial_keywords as $keyword) {
        if (stripos($body, $keyword) !== false) {
            $is_comercial = true;
            $ana_response .= " [DETECTADO: Interesse comercial - Rafael será notificado]";
            break;
        }
    }
    
    // Verificar se é suporte
    $is_suporte = false;
    $suporte_keywords = ['problema', 'erro', 'não funciona', 'quebrou', 'fora do ar'];
    foreach ($suporte_keywords as $keyword) {
        if (stripos($body, $keyword) !== false) {
            $is_suporte = true;
            $ana_response .= " [DETECTADO: Problema técnico - Suporte será notificado]";
            break;
        }
    }
    
    // Resposta JSON
    $response = [
        'success' => true,
        'message_id' => time(),
        'response_id' => time() + 1,
        'ana_response' => $ana_response,
        'action_taken' => $is_comercial ? 'comercial_detected' : ($is_suporte ? 'suporte_detected' : 'normal'),
        'transfer_rafael' => $is_comercial,
        'transfer_suporte' => $is_suporte,
        'from' => $from,
        'body' => $body,
        'timestamp' => $timestamp,
        'modo' => 'teste_simples',
        'servidor' => $_SERVER['SERVER_NAME'] ?? 'localhost'
    ];
    
    error_log("[$timestamp] WEBHOOK_TESTE: Resposta enviada - " . json_encode($response));
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    $error_response = [
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ];
    
    error_log("[ERROR] WEBHOOK_TESTE: " . json_encode($error_response));
    
    http_response_code(500);
    echo json_encode($error_response);
}
?> 