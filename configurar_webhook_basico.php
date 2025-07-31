<?php
echo "🔧 CONFIGURANDO WEBHOOK BÁSICO\n";
echo "==============================\n\n";

// URL do webhook básico
$webhook_url_basico = "https://pixel12digital.com.br/loja-virtual-revenda/api/webhook_whatsapp_basico.php";

echo "🔗 Configurando webhook para: $webhook_url_basico\n\n";

// 1. Configurar webhook na VPS
echo "📡 CONFIGURANDO NA VPS:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:3000/webhook/config");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url_basico]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Webhook básico configurado com sucesso!\n";
} else {
    echo "   ❌ Erro ao configurar webhook (HTTP $http_code)\n";
    echo "   📄 Resposta: $response\n";
}

echo "\n";

// 2. Testar webhook básico
echo "🧪 TESTANDO WEBHOOK BÁSICO:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url_basico);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Webhook básico respondendo!\n";
    echo "   📄 Resposta: " . substr($response, 0, 200) . "...\n";
} else {
    echo "   ❌ Webhook básico não está respondendo (HTTP $http_code)\n";
    echo "   📄 Resposta: " . substr($response, 0, 300) . "...\n";
}

echo "\n";

// 3. Testar com payload
echo "📤 TESTANDO COM PAYLOAD:\n";
$payload_teste = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'text' => 'teste básico',
        'type' => 'text'
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url_basico);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload_teste));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Payload processado com sucesso!\n";
    echo "   📄 Resposta: " . substr($response, 0, 300) . "...\n";
} else {
    echo "   ❌ Erro ao processar payload (HTTP $http_code)\n";
    echo "   📄 Resposta: " . substr($response, 0, 300) . "...\n";
}

echo "\n";

// 4. Verificar configuração atual
echo "📋 CONFIGURAÇÃO ATUAL:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://212.85.11.238:3000/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "   ✅ Webhook configurado: " . ($data['webhook_url'] ?? 'não definida') . "\n";
} else {
    echo "   ❌ Erro ao consultar configuração (HTTP $http_code)\n";
}

echo "\n";

// 5. Instruções de teste
echo "📱 INSTRUÇÕES DE TESTE:\n";
echo "   1. Envie uma mensagem para o WhatsApp: 47 96164699\n";
echo "   2. Digite qualquer texto\n";
echo "   3. Você deve receber uma resposta automática\n";
echo "   4. Verifique o arquivo de log: logs/webhook_basico_" . date('Y-m-d') . ".log\n";

echo "\n🎉 CONFIGURAÇÃO CONCLUÍDA!\n";
echo "Agora teste enviando uma mensagem para o WhatsApp!\n";
?> 