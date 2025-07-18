<?php
echo "=== ATUALIZADOR DE CHAVE ASAAS ===\n\n";

// Verificar se uma nova chave foi fornecida
if (isset($argv[1])) {
    $nova_chave = trim($argv[1]);
    
    // Validar formato da chave
    if (!preg_match('/^\$aact_/', $nova_chave)) {
        echo "❌ ERRO: A chave deve começar com '\$aact_'\n";
        echo "Exemplo: \$aact_prod_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjZjZWNkODQ1LWIxZTUtNDE0MS1iZTNmLTFmYTdlM2U0YzcxMDo6JGFhY2hfZmFjNDFlYmMtYzAyNi00Y2FjLWEzOWEtZmI2YWZkNGU5ZjBl\n";
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
        
    } else {
        echo "❌ Nova chave inválida (HTTP $httpCode)\n";
        echo "Resposta: $result\n";
        echo "\n💡 Verifique se a chave está correta no painel do Asaas.\n";
    }
    
} else {
    echo "📝 USO: php atualizar_chave_asaas.php \"SUA_NOVA_CHAVE_AQUI\"\n\n";
    echo "Exemplo:\n";
    echo "php atualizar_chave_asaas.php \"\$aact_prod_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjZjZWNkODQ1LWIxZTUtNDE0MS1iZTNmLTFmYTdlM2U0YzcxMDo6JGFhY2hfZmFjNDFlYmMtYzAyNi00Y2FjLWEzOWEtZmI2YWZkNGU5ZjBl\"\n\n";
    echo "💡 Para obter uma nova chave:\n";
    echo "1. Acesse https://www.asaas.com\n";
    echo "2. Faça login na sua conta\n";
    echo "3. Vá em Configurações > API\n";
    echo "4. Copie a chave de produção\n";
}
?> 