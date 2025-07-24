<?php
/**
 * Endpoint para atualizar a chave da API do Asaas
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

require_once '../config.php';
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['chave']) || empty($input['chave'])) {
        echo json_encode(['success' => false, 'error' => 'Chave não fornecida']);
        exit;
    }
    
    $nova_chave = trim($input['chave']);
    $tipo = $input['tipo'] ?? 'auto';
    
    // Validar formato da chave
    if (!preg_match('/^\$aact_(test|prod)_/', $nova_chave)) {
        echo json_encode([
            'success' => false, 
            'error' => 'Formato de chave inválido. Deve começar com $aact_test_ ou $aact_prod_'
        ]);
        exit;
    }
    
    // Detectar tipo automaticamente se não especificado
    if ($tipo === 'auto') {
        $tipo = strpos($nova_chave, '_test_') !== false ? 'test' : 'prod';
    }
    
    // Testar a nova chave antes de aplicar
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, ASAAS_API_URL . '/customers?limit=1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'access_token: ' . $nova_chave
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo json_encode([
            'success' => false,
            'error' => 'Erro de conectividade: ' . $error
        ]);
        exit;
    }
    
    if ($http_code !== 200) {
        $response = json_decode($result, true);
        $error_msg = 'Chave inválida';
        if ($response && isset($response['errors'][0]['description'])) {
            $error_msg = $response['errors'][0]['description'];
        }
        echo json_encode([
            'success' => false,
            'error' => $error_msg,
            'http_code' => $http_code
        ]);
        exit;
    }

    // Chave é válida, salvar no banco de dados
    $chave_escaped = $mysqli->real_escape_string($nova_chave);
    $sql = "INSERT INTO configuracoes (chave, valor, descricao, data_atualizacao) VALUES ('asaas_api_key', '$chave_escaped', 'Chave da API do Asaas', NOW()) ON DUPLICATE KEY UPDATE valor = '$chave_escaped', data_atualizacao = NOW()";
    
    if (!$mysqli->query($sql)) {
        echo json_encode([
            'success' => false,
            'error' => 'Erro ao salvar chave no banco de dados: ' . $mysqli->error
        ]);
        exit;
    }

    // Log da alteração
    $log_data = date('Y-m-d H:i:s') . " - Chave API Asaas atualizada no banco (tipo: $tipo)\n";
    $log_dir = dirname(__FILE__) . '/../../logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    file_put_contents($log_dir . '/asaas_key_updates.log', $log_data, FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'message' => 'Chave da API atualizada com sucesso no banco de dados',
        'tipo' => $tipo,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno: ' . $e->getMessage()
    ]);
}
?> 