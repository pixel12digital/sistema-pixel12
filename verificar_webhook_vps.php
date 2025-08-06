<?php
echo "🔍 VERIFICANDO WEBHOOK NO VPS\n";
echo "=============================\n\n";

$vps_url = "http://212.85.11.238:3000";

// 1. Verificar status do VPS
echo "1️⃣ Status do VPS:\n";
$ch = curl_init($vps_url . "/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $status = json_decode($response, true);
    echo "✅ VPS Online - Status: " . $status['status'] . "\n";
    echo "✅ WhatsApp Conectado: " . ($status['clients_status']['default']['ready'] ? 'SIM' : 'NÃO') . "\n";
} else {
    echo "❌ VPS Offline - HTTP: $http_code\n";
}
echo "\n";

// 2. Verificar configuração do webhook
echo "2️⃣ Configuração do webhook:\n";
$ch = curl_init($vps_url . "/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$webhook_response = curl_exec($ch);
$webhook_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📡 HTTP Code: $webhook_http\n";
echo "📡 Response: $webhook_response\n";

if ($webhook_http == 200) {
    $webhook_config = json_decode($webhook_response, true);
    echo "✅ Webhook configurado:\n";
    if (isset($webhook_config['url'])) {
        echo "   URL: " . $webhook_config['url'] . "\n";
    }
    if (isset($webhook_config['status'])) {
        echo "   Status: " . $webhook_config['status'] . "\n";
    }
} else {
    echo "❌ Erro ao verificar webhook\n";
}
echo "\n";

// 3. Configurar webhook se necessário
echo "3️⃣ Configurando webhook...\n";
$webhook_url = 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php';

$config_data = [
    'url' => $webhook_url,
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
    echo "✅ Webhook configurado com sucesso!\n";
} else {
    echo "❌ Erro ao configurar webhook\n";
}
echo "\n";

// 4. Testar webhook
echo "4️⃣ Testando webhook...\n";
$test_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'text' => '🧪 Teste webhook - ' . date('H:i:s'),
        'type' => 'text',
        'timestamp' => time(),
        'session' => 'default'
    ]
];

$ch = curl_init($vps_url . "/webhook/test");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$test_response = curl_exec($ch);
$test_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📡 Test HTTP Code: $test_http\n";
echo "📡 Test Response: $test_response\n";

if ($test_http == 200) {
    echo "✅ Teste do webhook bem-sucedido!\n";
} else {
    echo "❌ Erro no teste do webhook\n";
}

echo "\n🎯 PRÓXIMOS PASSOS:\n";
echo "1. Verifique se o webhook foi configurado corretamente\n";
echo "2. Envie uma mensagem real do WhatsApp\n";
echo "3. Monitore se a Ana responde\n";
?> 