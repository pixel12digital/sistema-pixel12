<?php
/**
 * Diagnóstico de Problemas em Produção
 * Script para identificar problemas de sincronização
 */

echo "=== DIAGNÓSTICO DE PRODUÇÃO ===\n\n";

// 1. Verificar configurações
echo "1. VERIFICANDO CONFIGURAÇÕES:\n";
echo "PHP Version: " . phpversion() . "\n";
echo "cURL disponível: " . (function_exists('curl_init') ? 'SIM' : 'NÃO') . "\n";
echo "JSON disponível: " . (function_exists('json_encode') ? 'SIM' : 'NÃO') . "\n";
echo "Timezone: " . date_default_timezone_get() . "\n\n";

// 2. Verificar arquivos críticos
echo "2. VERIFICANDO ARQUIVOS CRÍTICOS:\n";
$arquivos_criticos = [
    'painel/config.php',
    'painel/db.php',
    'painel/verificador_automatico_chave_otimizado.php',
    'painel/monitoramento_otimizado.js',
    'painel/faturas.php'
];

foreach ($arquivos_criticos as $arquivo) {
    echo "$arquivo: " . (file_exists($arquivo) ? 'EXISTE' : 'NÃO EXISTE') . "\n";
}
echo "\n";

// 3. Verificar configuração da API
echo "3. VERIFICANDO CONFIGURAÇÃO DA API:\n";
if (file_exists('painel/config.php')) {
    require_once 'painel/config.php';
    echo "ASAAS_API_KEY definida: " . (defined('ASAAS_API_KEY') ? 'SIM' : 'NÃO') . "\n";
    if (defined('ASAAS_API_KEY')) {
        echo "Chave: " . substr(ASAAS_API_KEY, 0, 20) . "...\n";
        echo "Tipo: " . (strpos(ASAAS_API_KEY, '_test_') !== false ? 'TESTE' : 'PRODUÇÃO') . "\n";
    }
} else {
    echo "ERRO: arquivo config.php não encontrado!\n";
}
echo "\n";

// 4. Testar conexão com banco
echo "4. VERIFICANDO CONEXÃO COM BANCO:\n";
if (file_exists('painel/db.php')) {
    try {
        require_once 'painel/db.php';
        echo "Conexão com banco: OK\n";
    } catch (Exception $e) {
        echo "ERRO no banco: " . $e->getMessage() . "\n";
    }
} else {
    echo "ERRO: arquivo db.php não encontrado!\n";
}
echo "\n";

// 5. Testar API do Asaas
echo "5. TESTANDO API DO ASAAS:\n";
if (defined('ASAAS_API_KEY')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.asaas.com/api/v3/customers?limit=1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'access_token: ' . ASAAS_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode\n";
    if ($curlError) {
        echo "Erro CURL: $curlError\n";
    } else {
        echo "Resposta: " . substr($result, 0, 100) . "...\n";
    }
    
    if ($httpCode == 200) {
        echo "✅ API funcionando!\n";
    } else {
        echo "❌ Problema com API!\n";
    }
} else {
    echo "ERRO: ASAAS_API_KEY não definida!\n";
}
echo "\n";

// 6. Verificar permissões de arquivos
echo "6. VERIFICANDO PERMISSÕES:\n";
$diretorios = ['logs', 'painel/logs', 'cache'];
foreach ($diretorios as $dir) {
    if (is_dir($dir)) {
        echo "$dir: " . (is_writable($dir) ? 'ESCRITÁVEL' : 'NÃO ESCRITÁVEL') . "\n";
    } else {
        echo "$dir: NÃO EXISTE\n";
    }
}
echo "\n";

// 7. Verificar logs
echo "7. VERIFICANDO LOGS:\n";
$logs = [
    'logs/verificador_chave_otimizado.log',
    'logs/status_chave_atual.json',
    'logs/cache_chave.json',
    'logs/alerta_chave_invalida.json'
];

foreach ($logs as $log) {
    if (file_exists($log)) {
        $size = filesize($log);
        $modified = date('Y-m-d H:i:s', filemtime($log));
        echo "$log: $size bytes, modificado em $modified\n";
    } else {
        echo "$log: NÃO EXISTE\n";
    }
}
echo "\n";

// 8. Verificar ambiente
echo "8. VERIFICANDO AMBIENTE:\n";
echo "SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'NÃO DEFINIDO') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'NÃO DEFINIDO') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'NÃO DEFINIDO') . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NÃO DEFINIDO') . "\n";
echo "Detecção local: " . (strpos($_SERVER['SERVER_NAME'] ?? '', 'localhost') !== false ? 'SIM' : 'NÃO') . "\n";
echo "\n";

echo "=== FIM DO DIAGNÓSTICO ===\n";
?> 