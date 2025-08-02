<?php
/**
 * 🔍 VERIFICAR SE WEBHOOK ESTÁ FUNCIONANDO
 * 
 * Testa o webhook remotamente para confirmar se está ativo
 */

echo "🔍 VERIFICANDO WEBHOOK REMOTAMENTE\n";
echo "==================================\n\n";

$webhook_urls = [
    'Debug Webhook' => 'https://app.pixel12digital.com.br/painel/debug_webhook.php',
    'Webhook Principal' => 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php',
    'Dashboard' => 'https://app.pixel12digital.com.br/painel/gestao_transferencias.php'
];

foreach ($webhook_urls as $nome => $url) {
    echo "📡 Testando: $nome\n";
    echo "   URL: $url\n";
    
    // Teste GET simples
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    echo "   HTTP: $http_code\n";
    echo "   Tipo: $content_type\n";
    
    if ($http_code == 200) {
        echo "   ✅ ONLINE\n";
        
        // Se for JSON, mostrar preview
        if (strpos($content_type, 'json') !== false) {
            $json = json_decode($response, true);
            if ($json) {
                echo "   📄 Resposta: " . (isset($json['message']) ? $json['message'] : 'JSON válido') . "\n";
            }
        }
    } else {
        echo "   ❌ OFFLINE ou com erro\n";
    }
    
    echo "\n";
}

// Teste específico com dados de mensagem
echo "🧪 TESTE COM DADOS DE MENSAGEM:\n";
$test_data = [
    'from' => '5547999999999',
    'body' => 'Teste de verificação do webhook'
];

$webhook_principal = 'https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php';

$ch = curl_init($webhook_principal);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP: $http_code\n";
echo "   Resposta: " . substr($response, 0, 200) . "\n";

if ($http_code == 200) {
    $json_response = json_decode($response, true);
    if (isset($json_response['success']) && $json_response['success']) {
        echo "   ✅ WEBHOOK FUNCIONANDO PERFEITAMENTE!\n";
        echo "   🤖 Ana: " . substr($json_response['ana_response'] ?? 'Resposta obtida', 0, 50) . "...\n";
    } else {
        echo "   ⚠️ Webhook responde mas há problemas na integração\n";
    }
} else {
    echo "   ❌ Webhook não está funcionando\n";
}

echo "\n";
echo "🎯 RESUMO:\n";
echo "================\n";
echo "✅ Se tudo está com HTTP 200 = Webhook configurado corretamente\n";
echo "✅ Se Ana respondeu = Sistema de transferências funcionando\n";
echo "❌ Se HTTP != 200 = Verificar configuração do servidor\n\n";

echo "📋 PRÓXIMOS PASSOS:\n";
echo "1. Configure no WhatsApp: https://app.pixel12digital.com.br/painel/receber_mensagem_ana_local.php\n";
echo "2. Teste enviando: 'Preciso de um site'\n";
echo "3. Monitore em: https://app.pixel12digital.com.br/painel/gestao_transferencias.php\n";
?> 