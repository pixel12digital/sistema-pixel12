<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

try {
    require_once __DIR__ . '/../../config.php';
    
    // Testar se a chave existe
    if (!defined('ASAAS_API_KEY') || empty(ASAAS_API_KEY)) {
        throw new Exception('Chave da API não configurada');
    }
    
    // Testar conexão com Asaas
    $url = ASAAS_API_URL . '/customers?limit=1';
    $headers = [
        'Content-Type: application/json',
        'access_token: ' . ASAAS_API_KEY
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $start_time = microtime(true);
    $response = curl_exec($ch);
    $end_time = microtime(true);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $response_time = round(($end_time - $start_time) * 1000, 2);
    
    if ($error) {
        throw new Exception("Erro CURL: $error");
    }
    
    if ($http_code !== 200) {
        throw new Exception("HTTP Code: $http_code");
    }
    
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Erro JSON: " . json_last_error_msg());
    }
    
    // Mascarar a chave para segurança
    $chave_mascarada = substr(ASAAS_API_KEY, 0, 20) . '...' . substr(ASAAS_API_KEY, -10);
    $tipo_chave = strpos(ASAAS_API_KEY, '_test_') !== false ? 'TESTE' : 'PRODUÇÃO';
    
    echo json_encode([
        'valida' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'http_code' => $http_code,
        'response_time' => $response_time,
        'chave_mascarada' => $chave_mascarada,
        'tipo_chave' => $tipo_chave,
        'message' => 'Conexão estabelecida com sucesso'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'valida' => false,
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => $e->getMessage(),
        'message' => 'Erro na verificação da chave'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?> 