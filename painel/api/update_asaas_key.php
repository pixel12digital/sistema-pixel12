<?php
/**
 * Endpoint para atualizar a chave da API do Asaas
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../db.php';

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
    
    // Chave é válida, agora vamos atualizá-la no arquivo de configuração
    $config_file = __DIR__ . '/../../config.php';
    $config_content = file_get_contents($config_file);
    
    if (!$config_content) {
        echo json_encode(['success' => false, 'error' => 'Não foi possível ler o arquivo de configuração']);
        exit;
    }
    
    // Log para debug
    $debug_log = "Tentativa de atualização - " . date('Y-m-d H:i:s') . "\n";
    $debug_log .= "Arquivo: $config_file\n";
    $debug_log .= "Tamanho do arquivo: " . strlen($config_content) . " bytes\n";
    $debug_log .= "Nova chave: " . substr($nova_chave, 0, 20) . "...\n";
    
    // Fazer backup do arquivo atual
    $backup_file = dirname(__FILE__) . '/../config.php.backup.' . date('Y-m-d_H-i-s');
    file_put_contents($backup_file, $config_content);
    
    // Substituir a chave no arquivo de configuração
    // Detectar qual ambiente estamos (local ou produção)
    $is_local = (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] === 'localhost') ||
                (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['DOCUMENT_ROOT'], 'xampp') !== false);
    
    // Padrão simplificado que funciona para ambos os ambientes
    $pattern = "/define\('ASAAS_API_KEY',\s*'[^']*'\);/";
    $replacement = "define('ASAAS_API_KEY', '$nova_chave');";
    
    $debug_log .= "Padrão 1: $pattern\n";
    $new_content = preg_replace($pattern, $replacement, $config_content);
    
    if ($new_content === null || $new_content === $config_content) {
        // Tentar padrão alternativo se o primeiro não funcionar
        $pattern2 = "/define\('ASAAS_API_KEY',\s*getenv\('ASAAS_API_KEY'\)\s*\?\:\s*'[^']*'\);/";
        $replacement2 = "define('ASAAS_API_KEY', '$nova_chave');";
        
        $debug_log .= "Padrão 1 falhou, tentando padrão 2: $pattern2\n";
        $new_content = preg_replace($pattern2, $replacement2, $config_content);
        
        if ($new_content === null || $new_content === $config_content) {
            $debug_log .= "Ambos os padrões falharam\n";
            file_put_contents(dirname(__FILE__) . '/../../logs/asaas_key_update_debug.log', $debug_log, FILE_APPEND);
            
            echo json_encode([
                'success' => false, 
                'error' => 'Não foi possível atualizar a configuração. Padrão não encontrado. Verifique se a linha ASAAS_API_KEY existe no config.php.'
            ]);
            exit;
        }
    }
    
    $debug_log .= "Substituição bem-sucedida\n";
    file_put_contents(dirname(__FILE__) . '/../../logs/asaas_key_update_debug.log', $debug_log, FILE_APPEND);
    
    // Salvar o arquivo atualizado
    if (!file_put_contents($config_file, $new_content)) {
        echo json_encode(['success' => false, 'error' => 'Erro ao salvar o arquivo de configuração']);
        exit;
    }

    // Atualizar/inserir a chave no banco de dados
    $chave_escaped = $mysqli->real_escape_string($nova_chave);
    $sql = "INSERT INTO configuracoes (chave, valor, descricao, data_atualizacao) VALUES ('asaas_api_key', '$chave_escaped', 'Chave da API do Asaas', NOW())\n        ON DUPLICATE KEY UPDATE valor = '$chave_escaped', data_atualizacao = NOW()";
    $mysqli->query($sql);

    // Log da alteração
    $log_data = date('Y-m-d H:i:s') . " - Chave API Asaas atualizada (tipo: $tipo) - Backup: $backup_file\n";
    file_put_contents(dirname(__FILE__) . '/../../logs/asaas_key_updates.log', $log_data, FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'message' => 'Chave da API atualizada com sucesso',
        'tipo' => $tipo,
        'backup_file' => basename($backup_file),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno: ' . $e->getMessage()
    ]);
}
?> 