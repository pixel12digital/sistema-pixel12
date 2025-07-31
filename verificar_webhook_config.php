<?php
echo "🔍 VERIFICANDO CONFIGURAÇÃO DO WEBHOOK NA VPS\n";
echo "=============================================\n\n";

// 1. Verificar configuração atual do webhook
echo "📋 CONFIGURAÇÃO ATUAL DO WEBHOOK:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:3000/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ Webhook configurado\n";
    echo "   🔗 URL: " . ($data['webhook_url'] ?? 'não definida') . "\n";
    echo "   📊 Status: " . ($data['status'] ?? 'não definido') . "\n";
} else {
    echo "   ❌ Erro ao consultar webhook (HTTP $http_code)\n";
}

echo "\n";

// 2. Testar se o webhook está funcionando
echo "🧪 TESTANDO WEBHOOK ATUAL:\n";
if (isset($data['webhook_url'])) {
    $webhook_url = $data['webhook_url'];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhook_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "   ✅ Webhook respondendo (HTTP 200)\n";
    } else {
        echo "   ❌ Webhook não respondendo (HTTP $http_code)\n";
        echo "   🔧 Problema: O webhook está apontando para uma URL que não está funcionando\n";
    }
} else {
    echo "   ⚠️ Webhook não configurado\n";
}

echo "\n";

// 3. Verificar se o Apache local está funcionando
echo "🌐 VERIFICANDO APACHE LOCAL:\n";
$test_urls = [
    'http://localhost/',
    'http://localhost:8080/',
    'http://127.0.0.1/',
    'http://127.0.0.1:8080/'
];

foreach ($test_urls as $url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   $url: HTTP $http_code\n";
}

echo "\n";

// 4. Sugerir correção
echo "🔧 SUGESTÃO DE CORREÇÃO:\n";
echo "   O webhook está configurado para receber mensagens localmente, mas o Apache não está funcionando.\n";
echo "   Opções:\n";
echo "   1. Configurar webhook para produção (recomendado)\n";
echo "   2. Corrigir Apache local\n";
echo "   3. Usar ngrok para expor localhost\n";

echo "\n✅ VERIFICAÇÃO CONCLUÍDA!\n";
?> 