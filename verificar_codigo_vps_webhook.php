<?php
echo "🔍 VERIFICANDO CÓDIGO DO VPS - PROBLEMA WEBHOOK\n";
echo "===============================================\n\n";

$vps_url = "http://212.85.11.238:3000";

// 1. Verificar se o VPS está funcionando
echo "1️⃣ Status do VPS:\n";
$ch = curl_init($vps_url . "/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$status_response = curl_exec($ch);
$status_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📡 Status HTTP: $status_http\n";
echo "📡 Status Response: $status_response\n\n";

// 2. Tentar diferentes formatos de configuração
echo "2️⃣ Testando diferentes formatos de webhook:\n";

$formats = [
    'format1' => ['url' => 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php'],
    'format2' => ['webhook' => 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php'],
    'format3' => ['url' => 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php', 'events' => ['onmessage']],
    'format4' => 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php'
];

foreach ($formats as $name => $data) {
    echo "   Testando $name: ";
    
    $ch = curl_init($vps_url . "/webhook/config");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP $http_code | " . substr($response, 0, 100) . "...\n";
    
    // Verificar se funcionou
    sleep(2);
    $ch = curl_init($vps_url . "/webhook/config");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $check_response = curl_exec($ch);
    curl_close($ch);
    
    $check_config = json_decode($check_response, true);
    if (isset($check_config['webhook']) && strpos($check_config['webhook'], 'webhook_sem_redirect') !== false) {
        echo "   ✅ SUCESSO! Webhook atualizado!\n";
        break;
    } else {
        echo "   ❌ Falhou\n";
    }
}
echo "\n";

// 3. Verificar se há arquivo de configuração
echo "3️⃣ Verificando se há arquivo de configuração:\n";
$ch = curl_init($vps_url . "/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$config_response = curl_exec($ch);
$config_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📡 Config HTTP: $config_http\n";
echo "📡 Config Response: " . substr($config_response, 0, 200) . "...\n\n";

// 4. Verificar logs do VPS
echo "4️⃣ Verificando logs do VPS:\n";
$ch = curl_init($vps_url . "/logs");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$logs_response = curl_exec($ch);
$logs_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📡 Logs HTTP: $logs_http\n";
if ($logs_http == 200) {
    echo "📡 Logs: " . substr($logs_response, 0, 300) . "...\n";
} else {
    echo "❌ Logs não disponíveis\n";
}
echo "\n";

// 5. Testar webhook diretamente
echo "5️⃣ Testando webhook diretamente:\n";
$webhook_url = 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php';
$test_data = [
    'event' => 'onmessage',
    'data' => [
        'from' => '554796164699',
        'text' => '🧪 Teste direto - ' . date('H:i:s'),
        'type' => 'text',
        'timestamp' => time(),
        'session' => 'default'
    ]
];

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$test_response = curl_exec($ch);
$test_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📡 Test HTTP: $test_http\n";
echo "📡 Test Response: " . substr($test_response, 0, 200) . "...\n";

if ($test_http == 200) {
    echo "✅ Webhook funciona diretamente!\n";
} else {
    echo "❌ Webhook não funciona diretamente\n";
}

echo "\n🎯 DIAGNÓSTICO FINAL:\n";
echo "1. Se webhook funciona diretamente: Problema no VPS\n";
echo "2. Se VPS não salva configuração: Bug no código Node.js\n";
echo "3. Se nenhum formato funciona: VPS com problema estrutural\n";

echo "\n🔧 PRÓXIMA AÇÃO:\n";
echo "O problema está no código do VPS. Precisamos verificar o arquivo whatsapp-api-server.js\n";
?> 