<?php
echo "🔧 CORRIGINDO WEBHOOK AGORA\n";
echo "============================\n\n";

$vps_url = "http://212.85.11.238:3000";
$webhook_url_correta = 'https://app.pixel12digital.com.br/webhook_sem_redirect/webhook.php';

// 1. Verificar configuração atual
echo "1️⃣ Verificando configuração atual:\n";
$ch = curl_init($vps_url . "/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$current_response = curl_exec($ch);
$current_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($current_http == 200) {
    $current_config = json_decode($current_response, true);
    echo "📡 Webhook atual: " . (isset($current_config['webhook']) ? $current_config['webhook'] : 'N/A') . "\n";
} else {
    echo "❌ Erro ao verificar webhook atual\n";
}
echo "\n";

// 2. Configurar webhook correto
echo "2️⃣ Configurando webhook correto...\n";
$config_data = [
    'url' => $webhook_url_correta,
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

// 3. Verificar nova configuração
echo "3️⃣ Verificando nova configuração:\n";
$ch = curl_init($vps_url . "/webhook/config");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$new_response = curl_exec($ch);
$new_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($new_http == 200) {
    $new_config = json_decode($new_response, true);
    echo "📡 Webhook atualizado: " . (isset($new_config['webhook']) ? $new_config['webhook'] : 'N/A') . "\n";
    
    if (isset($new_config['webhook']) && $new_config['webhook'] === $webhook_url_correta) {
        echo "✅ SUCESSO: Webhook corrigido para a URL correta!\n";
    } else {
        echo "❌ ERRO: Webhook ainda não foi atualizado corretamente\n";
    }
} else {
    echo "❌ Erro ao verificar nova configuração\n";
}

echo "\n🎯 URL CORRETA DO WEBHOOK: $webhook_url_correta\n";
?> 