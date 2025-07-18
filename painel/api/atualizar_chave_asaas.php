<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config.php';

function logAtualizacao($mensagem) {
    $logFile = __DIR__ . '/../../logs/atualizacao_chave_asaas.log';
    $data = date('Y-m-d H:i:s') . ' - ' . $mensagem . "\n";
    file_put_contents($logFile, $data, FILE_APPEND);
}

function atualizarChaveEmArquivo($arquivo, $nova_chave) {
    $conteudo = file_get_contents($arquivo);
    
    // Padrão para encontrar a definição da chave
    $pattern = "/define\('ASAAS_API_KEY',\s*'[^']*'\);/";
    $replacement = "define('ASAAS_API_KEY', '$nova_chave');";
    
    $novo_conteudo = preg_replace($pattern, $replacement, $conteudo);
    
    if ($novo_conteudo !== $conteudo) {
        return file_put_contents($arquivo, $novo_conteudo);
    }
    
    return false;
}

function testarChave($chave) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.asaas.com/api/v3/customers?limit=1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'access_token: ' . $chave
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'success' => $httpCode == 200,
        'http_code' => $httpCode,
        'response' => $result
    ];
}

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['nova_chave'])) {
            throw new Exception('Nova chave não fornecida');
        }
        
        $nova_chave = trim($input['nova_chave']);
        
        // Validar formato da chave
        if (!preg_match('/^\$aact_(test|prod)_/', $nova_chave)) {
            throw new Exception('Formato de chave inválido. Deve começar com $aact_test_ ou $aact_prod_');
        }
        
        // Testar a nova chave
        $teste = testarChave($nova_chave);
        
        if (!$teste['success']) {
            $response['success'] = false;
            $response['message'] = 'Chave inválida (HTTP ' . $teste['http_code'] . ')';
            $response['data'] = [
                'http_code' => $teste['http_code'],
                'response' => $teste['response']
            ];
        } else {
            // Atualizar arquivos de configuração
            $arquivos_atualizados = [];
            
            // Atualizar config.php da raiz
            if (atualizarChaveEmArquivo(__DIR__ . '/../../config.php', $nova_chave)) {
                $arquivos_atualizados[] = 'config.php';
                logAtualizacao("config.php atualizado com nova chave");
            }
            
            // Atualizar painel/config.php
            if (atualizarChaveEmArquivo(__DIR__ . '/../config.php', $nova_chave)) {
                $arquivos_atualizados[] = 'painel/config.php';
                logAtualizacao("painel/config.php atualizado com nova chave");
            }
            
            if (empty($arquivos_atualizados)) {
                throw new Exception('Nenhum arquivo foi atualizado');
            }
            
            $response['success'] = true;
            $response['message'] = 'Chave atualizada com sucesso!';
            $response['data'] = [
                'arquivos_atualizados' => $arquivos_atualizados,
                'nova_chave_mascarada' => substr($nova_chave, 0, 20) . '...',
                'tipo_chave' => strpos($nova_chave, '_test_') !== false ? 'TESTE' : 'PRODUÇÃO'
            ];
            
            logAtualizacao("Chave atualizada com sucesso: " . substr($nova_chave, 0, 20) . "...");
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Retornar status da chave atual
        $teste = testarChave(ASAAS_API_KEY);
        
        $response['success'] = true;
        $response['data'] = [
            'chave_atual' => substr(ASAAS_API_KEY, 0, 20) . '...',
            'tipo_chave' => strpos(ASAAS_API_KEY, '_test_') !== false ? 'TESTE' : 'PRODUÇÃO',
            'valida' => $teste['success'],
            'http_code' => $teste['http_code'],
            'ultima_verificacao' => date('Y-m-d H:i:s')
        ];
        
    } else {
        throw new Exception('Método não permitido');
    }
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    logAtualizacao("ERRO: " . $e->getMessage());
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?> 