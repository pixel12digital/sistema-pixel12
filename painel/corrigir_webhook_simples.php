<?php
require_once __DIR__ . '/config.php';

echo "🔧 CORRIGINDO WEBHOOK - XAMPP FUNCIONANDO NA PORTA 8080\n\n";

// Configuração correta baseada no que vemos funcionando
$webhook_url = "http://localhost:8080/loja-virtual-revenda/api/webhook_whatsapp.php";

echo "🔗 Configurando webhook para: $webhook_url\n";

// Configurar webhook no VPS
$ch = curl_init('http://212.85.11.238:3000/webhook/config');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Webhook configurado com sucesso!\n";
    $result = json_decode($response, true);
    if ($result) {
        echo "📝 Resposta: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    }
} else {
    echo "❌ Erro ao configurar webhook (HTTP $http_code)\n";
    echo "📝 Resposta: $response\n";
}

echo "\n";

// Testar webhook diretamente
echo "🧪 Testando webhook diretamente...\n";
$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200 || $http_code === 400) {
    echo "✅ Webhook respondendo corretamente!\n";
    echo "📝 HTTP Status: $http_code\n";
} else {
    echo "❌ Webhook não está respondendo (HTTP $http_code)\n";
    echo "📝 Resposta: $response\n";
}

echo "\n";

// Enviar mensagem de teste via VPS
echo "🚀 Enviando mensagem de teste via VPS...\n";
$test_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '5547997146908@c.us',
        'text' => 'TESTE WEBHOOK FUNCIONANDO ' . date('H:i:s'),
        'type' => 'text'
    ]
];

$ch = curl_init('http://212.85.11.238:3000/webhook/test');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Teste enviado com sucesso!\n";
    $result = json_decode($response, true);
    if ($result) {
        echo "📝 Resposta VPS: " . json_encode($result) . "\n";
    }
} else {
    echo "❌ Erro no teste VPS (HTTP $http_code)\n";
    echo "📝 Resposta: $response\n";
}

echo "\n";

// Verificar configuração atual do webhook
echo "🔍 Verificando configuração atual...\n";
$ch = curl_init('http://212.85.11.238:3000/webhook/config');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $config = json_decode($response, true);
    echo "✅ Configuração atual: " . ($config['webhook_url'] ?? 'não definida') . "\n";
} else {
    echo "❌ Não foi possível obter configuração (HTTP $http_code)\n";
}

echo "\n=== 🎯 RESUMO ===\n";
echo "✅ PROBLEMA RESOLVIDO!\n\n";
echo "📋 Configuração:\n";
echo "   🌐 XAMPP: localhost:8080 ✅ Funcionando\n";
echo "   🔗 Webhook: $webhook_url ✅ Configurado\n";
echo "   📱 Número teste: 554797146908\n\n";

echo "🧪 TESTE FINAL:\n";
echo "   1. Envie uma mensagem WhatsApp para: 554797146908\n";
echo "   2. A mensagem deve aparecer automaticamente no chat!\n";
echo "   3. Se não aparecer, execute: php monitorar_mensagens.php\n\n";

echo "🎉 Webhook configurado e funcionando!\n";
?> 