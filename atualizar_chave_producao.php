<?php
/**
 * Atualizar Chave da API em Produção
 * Script para resolver problemas de sincronização
 */

// Nova chave válida fornecida pelo usuário
$nova_chave = '$aact_prod_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjVmY2Y3MzlhLTVkNzQtNDNmOS05MWQ5LTJiOGRkNmJhODZkNzo6JGFhY2hfZTdkNDQ0MGMtYTg5Ni00NDhkLTk2N2EtODk5OTk2Yzk5MWU5';

echo "=== ATUALIZANDO CHAVE DA API EM PRODUÇÃO ===\n\n";

// 1. Verificar se a nova chave é válida
echo "1. TESTANDO NOVA CHAVE:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.asaas.com/api/v3/customers?limit=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'access_token: ' . $nova_chave
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
    exit(1);
}

if ($httpCode != 200) {
    echo "❌ Nova chave inválida! HTTP $httpCode\n";
    echo "Resposta: $result\n";
    exit(1);
}

echo "✅ Nova chave válida!\n\n";

// 2. Atualizar arquivo config.php
echo "2. ATUALIZANDO ARQUIVO CONFIG.PHP:\n";
$config_file = 'painel/config.php';

if (!file_exists($config_file)) {
    echo "❌ Arquivo config.php não encontrado!\n";
    exit(1);
}

$config_content = file_get_contents($config_file);

// Substituir a chave antiga pela nova
$pattern = "/define\('ASAAS_API_KEY',\s*'[^']*'\);/";
$replacement = "define('ASAAS_API_KEY', '$nova_chave');";

if (preg_match($pattern, $config_content)) {
    $config_content = preg_replace($pattern, $replacement, $config_content);
    
    if (file_put_contents($config_file, $config_content)) {
        echo "✅ Arquivo config.php atualizado!\n";
    } else {
        echo "❌ Erro ao salvar config.php!\n";
        exit(1);
    }
} else {
    echo "❌ Padrão da chave não encontrado no config.php!\n";
    exit(1);
}

// 3. Atualizar arquivo config.php da raiz (se existir)
echo "3. ATUALIZANDO CONFIG.PHP DA RAIZ:\n";
$config_raiz = 'config.php';

if (file_exists($config_raiz)) {
    $config_raiz_content = file_get_contents($config_raiz);
    
    if (preg_match($pattern, $config_raiz_content)) {
        $config_raiz_content = preg_replace($pattern, $replacement, $config_raiz_content);
        
        if (file_put_contents($config_raiz, $config_raiz_content)) {
            echo "✅ Arquivo config.php da raiz atualizado!\n";
        } else {
            echo "❌ Erro ao salvar config.php da raiz!\n";
        }
    } else {
        echo "⚠️ Padrão da chave não encontrado no config.php da raiz\n";
    }
} else {
    echo "⚠️ Arquivo config.php da raiz não encontrado\n";
}

// 4. Limpar cache e logs antigos
echo "4. LIMPANDO CACHE E LOGS:\n";
$arquivos_cache = [
    'logs/verificador_chave_otimizado.log',
    'logs/status_chave_atual.json',
    'logs/cache_chave.json',
    'logs/alerta_chave_invalida.json'
];

foreach ($arquivos_cache as $arquivo) {
    if (file_exists($arquivo)) {
        if (unlink($arquivo)) {
            echo "✅ Removido: $arquivo\n";
        } else {
            echo "⚠️ Não foi possível remover: $arquivo\n";
        }
    }
}

// 5. Testar a nova configuração
echo "5. TESTANDO NOVA CONFIGURAÇÃO:\n";
require_once 'painel/config.php';

if (defined('ASAAS_API_KEY') && ASAAS_API_KEY === $nova_chave) {
    echo "✅ Configuração carregada corretamente!\n";
    
    // Testar novamente
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
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "✅ API funcionando com nova chave!\n";
    } else {
        echo "❌ Problema com API após atualização!\n";
    }
} else {
    echo "❌ Configuração não carregada corretamente!\n";
}

echo "\n=== ATUALIZAÇÃO CONCLUÍDA ===\n";
echo "Agora você pode testar a sincronização novamente.\n";
?> 