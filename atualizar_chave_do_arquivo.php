<?php
echo "=== ATUALIZADOR DE CHAVE ASAAS (DO ARQUIVO) ===\n\n";

// Ler a chave do arquivo
if (!file_exists('nova_chave.txt')) {
    echo "❌ ERRO: Arquivo 'nova_chave.txt' não encontrado!\n";
    echo "Crie o arquivo com a nova chave da API.\n";
    exit(1);
}

$nova_chave = trim(file_get_contents('nova_chave.txt'));

if (empty($nova_chave)) {
    echo "❌ ERRO: Arquivo 'nova_chave.txt' está vazio!\n";
    exit(1);
}

echo "🔑 Chave lida do arquivo: " . substr($nova_chave, 0, 20) . "...\n";

// Validar formato da chave
if (!preg_match('/^\$aact_/', $nova_chave)) {
    echo "❌ ERRO: A chave deve começar com '\$aact_'\n";
    echo "Chave encontrada: $nova_chave\n";
    exit(1);
}

echo "🔑 Testando nova chave...\n";

// Testar a nova chave
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.asaas.com/api/v3/customers?limit=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'access_token: ' . $nova_chave
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✅ Nova chave válida! Atualizando arquivos...\n";
    
    // Atualizar config.php da raiz
    $config_content = file_get_contents('config.php');
    $pattern = "/define\('ASAAS_API_KEY',\s*'[^']*'\);/";
    $replacement = "define('ASAAS_API_KEY', '$nova_chave');";
    $novo_conteudo = preg_replace($pattern, $replacement, $config_content);
    
    if (file_put_contents('config.php', $novo_conteudo)) {
        echo "✅ config.php atualizado!\n";
    } else {
        echo "❌ Erro ao atualizar config.php\n";
    }
    
    // Atualizar painel/config.php
    $config_painel_content = file_get_contents('painel/config.php');
    $pattern = "/define\('ASAAS_API_KEY',\s*'[^']*'\);/";
    $replacement = "define('ASAAS_API_KEY', '$nova_chave');";
    $novo_conteudo_painel = preg_replace($pattern, $replacement, $config_painel_content);
    
    if (file_put_contents('painel/config.php', $novo_conteudo_painel)) {
        echo "✅ painel/config.php atualizado!\n";
    } else {
        echo "❌ Erro ao atualizar painel/config.php\n";
    }
    
    echo "\n🎉 Chave atualizada com sucesso!\n";
    echo "Agora você pode testar a sincronização:\n";
    echo "php painel/sincroniza_asaas.php\n";
    
    // Remover arquivo temporário
    unlink('nova_chave.txt');
    echo "🗑️ Arquivo temporário removido.\n";
    
} else {
    echo "❌ Nova chave inválida (HTTP $httpCode)\n";
    echo "Resposta: $result\n";
    echo "\n💡 Verifique se a chave está correta no painel do Asaas.\n";
}
?> 