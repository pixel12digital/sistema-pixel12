<?php
/**
 * Endpoint para testar chaves da API do Asaas
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

require_once '../config.php';

// Função para testar uma chave específica
function testarChave($chave) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, ASAAS_API_URL . '/customers?limit=1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'access_token: ' . $chave
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Asaas-API-Test/1.0');
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlInfo = curl_getinfo($ch);
    curl_close($ch);
    
    // Log detalhado para debug
    $logFile = __DIR__ . '/../../logs/asaas_test_debug.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logEntry = date('Y-m-d H:i:s') . " - Teste chave: " . substr($chave, 0, 20) . "... | HTTP: $httpCode | Erro: " . ($curlError ?: 'Nenhum') . " | Tempo: " . round($curlInfo['total_time'], 2) . "s\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    if ($curlError) {
        return [
            'success' => false,
            'error' => 'Erro de conexão: ' . $curlError,
            'debug' => [
                'curl_error' => $curlError,
                'total_time' => $curlInfo['total_time'],
                'connect_time' => $curlInfo['connect_time'],
                'namelookup_time' => $curlInfo['namelookup_time']
            ]
        ];
    }
    
    if ($httpCode == 401) {
        $response = json_decode($result, true);
        $errorMsg = 'Chave da API inválida';
        if ($response && isset($response['errors'][0]['description'])) {
            $errorMsg = $response['errors'][0]['description'];
        }
        return [
            'success' => false,
            'error' => $errorMsg,
            'http_code' => $httpCode,
            'response' => $response,
            'debug' => [
                'total_time' => $curlInfo['total_time'],
                'connect_time' => $curlInfo['connect_time']
            ]
        ];
    }
    
    if ($httpCode == 200) {
        $response = json_decode($result, true);
        return [
            'success' => true,
            'message' => 'Chave válida - Conexão estabelecida com sucesso',
            'http_code' => $httpCode,
            'response' => $response,
            'debug' => [
                'total_time' => $curlInfo['total_time'],
                'connect_time' => $curlInfo['connect_time']
            ]
        ];
    }
    
    return [
        'success' => false,
        'error' => 'Erro HTTP ' . $httpCode,
        'http_code' => $httpCode,
        'response' => $result,
        'debug' => [
            'total_time' => $curlInfo['total_time'],
            'connect_time' => $curlInfo['connect_time']
        ]
    ];
}

try {
    // Verificar se é uma requisição POST (testar nova chave) ou GET (testar chave atual)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Testar nova chave enviada no corpo da requisição
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['chave']) || empty($input['chave'])) {
            echo json_encode([
                'success' => false,
                'error' => 'Chave não fornecida'
            ]);
            exit;
        }
        
        $chave = trim($input['chave']);
        
        // Validar formato da chave
        if (!preg_match('/^\$aact_(test|prod)_/', $chave)) {
            echo json_encode([
                'success' => false,
                'error' => 'Formato de chave inválido. Deve começar com $aact_test_ ou $aact_prod_'
            ]);
            exit;
        }
        
        $resultado = testarChave($chave);
        echo json_encode($resultado);
        
    } else {
        // Testar chave atual
        if (!defined('ASAAS_API_KEY')) {
            echo json_encode([
                'success' => false,
                'error' => 'Chave da API não está definida'
            ]);
            exit;
        }
        
        $resultado = testarChave(ASAAS_API_KEY);
        echo json_encode($resultado);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno: ' . $e->getMessage(),
        'debug' => [
            'exception' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?> 