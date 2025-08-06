<?php
echo "🔍 VERIFICANDO CÓDIGO DO VPS\n";
echo "============================\n\n";

$vps_url = "http://212.85.11.238:3000";

// 1. Verificar se o VPS está realmente atualizando o webhook
echo "1️⃣ Tentando forçar atualização do webhook...\n";

// Tentar diferentes formatos de configuração
$configs = [
    [
        'url' => 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php',
        'events' => ['onmessage', 'onqr', 'onready', 'onclose']
    ],
    [
        'webhook' => 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php',
        'events' => ['onmessage', 'onqr', 'onready', 'onclose']
    ],
    [
        'url' => 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php'
    ],
    'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php'
];

foreach ($configs as $index => $config) {
    echo "   Tentativa " . ($index + 1) . ": ";
    
    $ch = curl_init($vps_url . "/webhook/config");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($config));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP $http_code | Response: " . substr($response, 0, 100) . "...\n";
    
    // Verificar se funcionou
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

// 2. Verificar se há outros endpoints para configurar webhook
echo "2️⃣ Verificando outros endpoints possíveis:\n";
$endpoints = [
    '/webhook',
    '/webhook/set',
    '/config/webhook',
    '/settings/webhook',
    '/api/webhook'
];

foreach ($endpoints as $endpoint) {
    echo "   Testando $endpoint: ";
    
    $ch = curl_init($vps_url . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP $http_code\n";
}
echo "\n";

// 3. Verificar se o VPS tem algum problema de cache
echo "3️⃣ Tentando reiniciar o VPS via API...\n";
$ch = curl_init($vps_url . "/restart");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$restart_response = curl_exec($ch);
$restart_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "📡 Restart HTTP Code: $restart_http\n";
echo "📡 Restart Response: $restart_response\n";

if ($restart_http == 200) {
    echo "✅ VPS reiniciado com sucesso!\n";
    echo "⏳ Aguardando 10 segundos para VPS inicializar...\n";
    sleep(10);
    
    // Verificar webhook após reinicialização
    $ch = curl_init($vps_url . "/webhook/config");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $final_response = curl_exec($ch);
    curl_close($ch);
    
    $final_config = json_decode($final_response, true);
    echo "📡 Webhook após reinicialização: " . $final_config['webhook'] . "\n";
} else {
    echo "❌ Não foi possível reiniciar o VPS via API\n";
}
echo "\n";

// 4. Verificar se há problemas de conectividade
echo "4️⃣ Verificando conectividade com o webhook:\n";
$webhook_url = 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php';

$ch = curl_init($webhook_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$webhook_test = curl_exec($ch);
$webhook_test_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$webhook_test_error = curl_error($ch);
curl_close($ch);

echo "📡 Webhook URL: $webhook_url\n";
echo "📡 HTTP Code: $webhook_test_http\n";
if ($webhook_test_error) {
    echo "📡 cURL Error: $webhook_test_error\n";
} else {
    echo "📡 Response: " . substr($webhook_test, 0, 100) . "...\n";
}

echo "\n🎯 DIAGNÓSTICO:\n";
echo "1. Se o webhook não atualiza: Problema no código do VPS\n";
echo "2. Se o VPS não reinicia: Problema de permissões\n";
echo "3. Se o webhook não responde: Problema de conectividade\n";
echo "4. Se tudo funciona mas mensagens não chegam: Problema no WhatsApp\n";

echo "\n🔧 PRÓXIMA AÇÃO:\n";
echo "Execute no VPS: pm2 restart whatsapp-3000\n";
?> 