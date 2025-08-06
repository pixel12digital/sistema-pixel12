<?php
echo "🔧 CORRIGINDO CONFIGURAÇÃO DO WEBHOOK\n";
echo "====================================\n\n";

$vps_url = "http://212.85.11.238:3000";

// 1. Verificar configuração atual
echo "1️⃣ Configuração atual do webhook:\n";
$ch = curl_init($vps_url . "/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$webhook_response = curl_exec($ch);
$webhook_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($webhook_http == 200) {
    $webhook_config = json_decode($webhook_response, true);
    echo "❌ Webhook atual (INCORRETO): " . $webhook_config['webhook'] . "\n";
} else {
    echo "❌ Erro ao verificar webhook atual\n";
}
echo "\n";

// 2. Configurar webhook correto
echo "2️⃣ Configurando webhook correto...\n";
$webhook_url_correto = 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php';

$config_data = [
    'url' => $webhook_url_correto,
    'events' => ['onmessage', 'onqr', 'onready', 'onclose']
];

$ch = curl_init($vps_url . "/webhook/config");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($config_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$config_response = curl_exec($ch);
$config_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📡 Config HTTP Code: $config_http\n";
echo "📡 Config Response: $config_response\n";

if ($config_http == 200) {
    echo "✅ Webhook corrigido com sucesso!\n";
} else {
    echo "❌ Erro ao corrigir webhook\n";
}
echo "\n";

// 3. Verificar nova configuração
echo "3️⃣ Verificando nova configuração:\n";
$ch = curl_init($vps_url . "/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$new_webhook_response = curl_exec($ch);
$new_webhook_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($new_webhook_http == 200) {
    $new_webhook_config = json_decode($new_webhook_response, true);
    echo "✅ Nova configuração: " . $new_webhook_config['webhook'] . "\n";
    
    if ($new_webhook_config['webhook'] == $webhook_url_correto) {
        echo "✅ Webhook configurado corretamente!\n";
    } else {
        echo "❌ Webhook ainda incorreto!\n";
    }
} else {
    echo "❌ Erro ao verificar nova configuração\n";
}
echo "\n";

// 4. Testar webhook corrigido
echo "4️⃣ Testando webhook corrigido...\n";
$test_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'text' => '🧪 Teste webhook corrigido - ' . date('H:i:s'),
        'type' => 'text',
        'timestamp' => time(),
        'session' => 'default'
    ]
];

$ch = curl_init($webhook_url_correto);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$test_response = curl_exec($ch);
$test_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📡 Test HTTP Code: $test_http\n";
echo "📡 Test Response: " . substr($test_response, 0, 200) . "...\n";

if ($test_http == 200) {
    echo "✅ Teste do webhook corrigido bem-sucedido!\n";
} else {
    echo "❌ Erro no teste do webhook corrigido\n";
}

echo "\n🎯 PRÓXIMOS PASSOS:\n";
echo "1. ✅ Webhook corrigido\n";
echo "2. 📱 Envie uma mensagem REAL do WhatsApp para +55 47 9714-6908\n";
echo "3. 🔍 Monitore se a Ana responde\n";
echo "4. 📊 Verifique se as mensagens são salvas no banco\n";

echo "\n🚀 SISTEMA PRONTO PARA TESTE REAL!\n";
?> 