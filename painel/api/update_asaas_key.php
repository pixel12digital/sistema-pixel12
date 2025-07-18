<?php
/**
 * Endpoint para atualizar a chave da API do Asaas
 * Atualiza tanto os arquivos de configuração quanto o banco de dados
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

require_once '../config.php';
require_once '../db.php';

try {
    // Verificar se é uma requisição POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'success' => false,
            'error' => 'Método não permitido'
        ]);
        exit;
    }
    
    // Obter dados da requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['chave']) || empty($input['chave'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Chave não fornecida'
        ]);
        exit;
    }
    
    if (!isset($input['tipo']) || !in_array($input['tipo'], ['test', 'prod'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Tipo de chave inválido'
        ]);
        exit;
    }
    
    $novaChave = trim($input['chave']);
    $tipoChave = $input['tipo'];
    
    // Validar formato da chave
    if (!preg_match('/^\$aact_(test|prod)_/', $novaChave)) {
        echo json_encode([
            'success' => false,
            'error' => 'Formato de chave inválido. Deve começar com $aact_test_ ou $aact_prod_'
        ]);
        exit;
    }
    
    // Verificar se o tipo da chave corresponde ao formato
    $tipoFormato = strpos($novaChave, '_test_') !== false ? 'test' : 'prod';
    if ($tipoFormato !== $tipoChave) {
        echo json_encode([
            'success' => false,
            'error' => 'Tipo de chave não corresponde ao formato. Chave ' . $tipoFormato . ' selecionada como ' . $tipoChave
        ]);
        exit;
    }
    
    // Testar a nova chave antes de aplicar
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, ASAAS_API_URL . '/customers?limit=1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'access_token: ' . $novaChave
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        echo json_encode([
            'success' => false,
            'error' => 'Chave inválida (HTTP ' . $httpCode . '). Teste a chave antes de aplicá-la.'
        ]);
        exit;
    }
    
    // 1. ATUALIZAR BANCO DE DADOS
    $chave_escaped = $mysqli->real_escape_string($novaChave);
    $tipo_escaped = $mysqli->real_escape_string($tipoChave);
    
    // Verificar se já existe no banco
    $result = $mysqli->query("SELECT id FROM configuracoes WHERE chave = 'asaas_api_key' LIMIT 1");
    
    if ($result && $result->num_rows > 0) {
        // Atualizar configuração existente
        $sql = "UPDATE configuracoes SET 
                valor = '$chave_escaped', 
                data_atualizacao = NOW() 
                WHERE chave = 'asaas_api_key'";
    } else {
        // Inserir nova configuração
        $sql = "INSERT INTO configuracoes (chave, valor, descricao, data_criacao, data_atualizacao) 
                VALUES ('asaas_api_key', '$chave_escaped', 'Chave da API Asaas para integração financeira', NOW(), NOW())";
    }
    
    $result = $mysqli->query($sql);
    
    if (!$result) {
        throw new Exception("Erro ao salvar no banco: " . $mysqli->error);
    }
    
    // Atualizar também o ambiente no banco
    $result = $mysqli->query("SELECT id FROM configuracoes WHERE chave = 'asaas_ambiente' LIMIT 1");
    
    if ($result && $result->num_rows > 0) {
        $sql = "UPDATE configuracoes SET 
                valor = '$tipo_escaped', 
                data_atualizacao = NOW() 
                WHERE chave = 'asaas_ambiente'";
    } else {
        $sql = "INSERT INTO configuracoes (chave, valor, descricao, data_criacao, data_atualizacao) 
                VALUES ('asaas_ambiente', '$tipo_escaped', 'Ambiente da API Asaas (production/sandbox)', NOW(), NOW())";
    }
    
    $mysqli->query($sql);
    
    // 2. ATUALIZAR ARQUIVOS DE CONFIGURAÇÃO
    $configFile = __DIR__ . '/../config.php';
    
    if (!file_exists($configFile)) {
        echo json_encode([
            'success' => false,
            'error' => 'Arquivo de configuração não encontrado'
        ]);
        exit;
    }
    
    if (!is_writable($configFile)) {
        echo json_encode([
            'success' => false,
            'error' => 'Arquivo de configuração não tem permissão de escrita'
        ]);
        exit;
    }
    
    $configContent = file_get_contents($configFile);
    
    if ($tipoChave === 'test') {
        // Atualizar chave de teste
        $pattern = "/define\('ASAAS_API_KEY',\s*getenv\('ASAAS_API_KEY'\)\s*\?:\s*'[^']*'\);/";
        $replacement = "define('ASAAS_API_KEY', getenv('ASAAS_API_KEY') ?: '$novaChave');";
    } else {
        // Atualizar chave de produção
        $pattern = "/define\('ASAAS_API_KEY',\s*'[^']*'\);/";
        $replacement = "define('ASAAS_API_KEY', '$novaChave');";
    }
    
    $novoConteudo = preg_replace($pattern, $replacement, $configContent);
    
    if ($novoConteudo === $configContent) {
        echo json_encode([
            'success' => false,
            'error' => 'Nenhuma alteração foi feita no arquivo de configuração'
        ]);
        exit;
    }
    
    // Fazer backup do arquivo original
    $backupFile = $configFile . '.backup.' . date('Y-m-d_H-i-s');
    if (!copy($configFile, $backupFile)) {
        echo json_encode([
            'success' => false,
            'error' => 'Erro ao criar backup do arquivo de configuração'
        ]);
        exit;
    }
    
    // Salvar o novo conteúdo
    if (file_put_contents($configFile, $novoConteudo) === false) {
        echo json_encode([
            'success' => false,
            'error' => 'Erro ao salvar arquivo de configuração'
        ]);
        exit;
    }
    
    // 3. ATUALIZAR TAMBÉM O CONFIG.PHP DA RAIZ
    $configRootFile = __DIR__ . '/../../config.php';
    if (file_exists($configRootFile) && is_writable($configRootFile)) {
        $configRootContent = file_get_contents($configRootFile);
        $novoConteudoRoot = preg_replace($pattern, $replacement, $configRootContent);
        
        if ($novoConteudoRoot !== $configRootContent) {
            // Backup do arquivo da raiz
            $backupRootFile = $configRootFile . '.backup.' . date('Y-m-d_H-i-s');
            copy($configRootFile, $backupRootFile);
            
            // Salvar novo conteúdo
            file_put_contents($configRootFile, $novoConteudoRoot);
        }
    }
    
    // Log da alteração
    $logFile = __DIR__ . '/../../logs/asaas_key_updates.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logEntry = date('Y-m-d H:i:s') . ' - Chave ' . $tipoChave . ' atualizada: ' . substr($novaChave, 0, 20) . '...' . substr($novaChave, -10) . " (Banco + Arquivos)\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'message' => 'Chave da API atualizada com sucesso no banco e arquivos',
        'tipo' => $tipoChave,
        'backup' => basename($backupFile),
        'banco_atualizado' => true,
        'arquivos_atualizados' => true
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno: ' . $e->getMessage()
    ]);
}
?> 