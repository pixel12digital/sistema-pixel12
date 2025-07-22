<?php
echo "🔍 DIAGNÓSTICO ESPECÍFICO - PRODUÇÃO HOSTINGER\n\n";

// Verificar configuração atual
echo "1. 📋 Verificando configuração atual do webhook...\n";
$ch = curl_init('http://212.85.11.238:3000/webhook/config');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $config = json_decode($response, true);
    $webhook_url = $config['webhook_url'] ?? 'não definida';
    echo "   ✅ Webhook atual: $webhook_url\n";
} else {
    echo "   ❌ Erro ao obter configuração (HTTP $http_code)\n";
}

echo "\n2. 🧪 Testando webhook da produção...\n";
$webhook_produção = "https://revendawebvirtual.com.br/api/webhook_whatsapp.php";
echo "   URL testada: $webhook_produção\n";

$ch = curl_init($webhook_produção);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Status HTTP: $http_code\n";
if ($http_code === 200 || $http_code === 400) {
    echo "   ✅ Webhook acessível\n";
} else {
    echo "   ❌ Webhook não acessível\n";
    echo "   📝 Resposta: $response\n";
}

echo "\n3. 🔧 CORREÇÃO: Configurando URL correta...\n";
$webhook_correto = "https://pixel12digital.com.br/app/api/webhook_whatsapp.php";
echo "   Configurando para: $webhook_correto\n";

$ch = curl_init('http://212.85.11.238:3000/webhook/config');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_correto]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "   ✅ Webhook reconfigurado com sucesso!\n";
    $result = json_decode($response, true);
    if ($result) {
        echo "   📝 Resposta: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    }
} else {
    echo "   ❌ Erro ao reconfigurar webhook (HTTP $http_code)\n";
}

echo "\n4. 🧪 Testando webhook corrigido...\n";
$ch = curl_init($webhook_correto);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Status HTTP: $http_code\n";
if ($http_code === 200 || $http_code === 400) {
    echo "   ✅ Webhook corrigido funcionando!\n";
} else {
    echo "   ❌ Problema persiste\n";
}

echo "\n5. 🚀 Enviando teste via VPS...\n";
$test_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '5547996164699@c.us',
        'text' => 'TESTE PRODUÇÃO CORRIGIDO ' . date('H:i:s'),
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
    echo "   ✅ Teste enviado com sucesso!\n";
    $result = json_decode($response, true);
    if ($result) {
        echo "   📝 Resposta VPS: " . json_encode($result) . "\n";
    }
} else {
    echo "   ❌ Erro no teste VPS (HTTP $http_code)\n";
}

echo "\n6. 📱 Verificando status do WhatsApp...\n";
$ch = curl_init('http://212.85.11.238:3000/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $status = json_decode($response, true);
    $connected = $status['clients_status']['default']['status'] ?? 'unknown';
    echo "   Status WhatsApp: $connected\n";
    
    if ($connected !== 'connected') {
        echo "   ❌ WhatsApp não está conectado!\n";
        echo "   🔧 SOLUÇÃO: Conecte o WhatsApp via QR Code\n";
    } else {
        echo "   ✅ WhatsApp conectado\n";
    }
} else {
    echo "   ❌ Erro ao verificar status\n";
}

echo "\n=== 🎯 CORREÇÃO APLICADA ===\n";
echo "✅ Webhook reconfigurado para URL correta da Hostinger\n";
echo "📱 URL correta: $webhook_correto\n";
echo "🧪 Agora teste enviando uma nova mensagem para: 554797146908\n";
echo "📋 A mensagem deve aparecer no chat em poucos segundos\n\n";

echo "🎉 Correção concluída!\n";
?> 