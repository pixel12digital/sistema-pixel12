<?php
/**
 * Endpoint para testar chaves da API do Asaas
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
set_time_limit(30);

// Configurações
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

// Debug: Verificar conexão com o banco
if (!$mysqli || $mysqli->connect_errno) {
    echo json_encode(['success' => false, 'error' => 'Erro ao conectar ao banco: ' . $mysqli->connect_error]);
    exit;
}

// Função para buscar a chave do banco
function buscarChaveBanco($mysqli) {
    $config = $mysqli->query("SELECT valor FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1")->fetch_assoc();
    return $config ? $config['valor'] : '';
}

// Função para testar uma chave específica
function testarChave($chave) {
    $ch = curl_init();
    if (!$ch) {
        return ['success' => false, 'error' => 'Falha ao inicializar cURL'];
    }
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
    if ($result === false) {
        return ['success' => false, 'error' => 'Erro cURL: ' . curl_error($ch)];
    }
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
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        // Buscar chave do banco!
        $chave = buscarChaveBanco($mysqli);
        if (!$chave) {
            echo json_encode([
                'success' => false,
                'error' => 'Chave da API não está definida no banco'
            ]);
            exit;
        }
        $resultado = testarChave($chave);
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