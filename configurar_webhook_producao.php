<?php
echo "🔧 CONFIGURANDO WEBHOOK PARA PRODUÇÃO\n";
echo "=====================================\n\n";

// URL de produção do webhook
$webhook_url_producao = "https://pixel12digital.com.br/loja-virtual-revenda/api/webhook_whatsapp.php";

echo "🔗 Configurando webhook para: $webhook_url_producao\n\n";

// 1. Configurar webhook na VPS
echo "📡 CONFIGURANDO NA VPS:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:3000/webhook/config");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url_producao]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Webhook configurado com sucesso!\n";
} else {
    echo "   ❌ Erro ao configurar webhook (HTTP $http_code)\n";
    echo "   📄 Resposta: $response\n";
}

echo "\n";

// 2. Testar webhook configurado
echo "🧪 TESTANDO WEBHOOK CONFIGURADO:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url_producao);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Webhook respondendo corretamente!\n";
} else {
    echo "   ❌ Webhook não está respondendo (HTTP $http_code)\n";
    echo "   🔧 Verifique se o arquivo existe em produção\n";
}

echo "\n";

// 3. Verificar configuração atual
echo "📋 VERIFICANDO CONFIGURAÇÃO ATUAL:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:3000/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ Configuração atual: " . ($data['webhook_url'] ?? 'não definida') . "\n";
} else {
    echo "   ❌ Erro ao consultar configuração (HTTP $http_code)\n";
}

echo "\n";

// 4. Testar envio de mensagem com webhook
echo "📤 TESTANDO ENVIO COM WEBHOOK:\n";
$payload_teste = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'text' => 'Teste webhook produção - ' . date('H:i:s'),
        'type' => 'text'
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:3000/webhook/test");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_teste));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Teste de webhook enviado com sucesso!\n";
    echo "   📄 Resposta: $response\n";
} else {
    echo "   ❌ Erro no teste de webhook (HTTP $http_code)\n";
}

echo "\n";

// 5. Resumo final
echo "📊 RESUMO DA CONFIGURAÇÃO:\n";
echo "   🔗 Webhook URL: $webhook_url_producao\n";
echo "   📡 VPS: 212.85.11.238:3000\n";
echo "   🌐 Ambiente: Produção\n";
echo "   ✅ Status: Configurado para receber mensagens em produção\n";

echo "\n🎉 CONFIGURAÇÃO CONCLUÍDA!\n";
echo "Agora você deve receber mensagens no canal financeiro!\n";
?> 