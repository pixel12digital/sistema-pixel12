<?php
/**
 * CONFIGURADOR DE WEBHOOK NO SERVIDOR WHATSAPP
 */

echo "=== CONFIGURANDO WEBHOOK NO SERVIDOR WHATSAPP ===\n\n";

// Configurações
$whatsapp_server = 'http://212.85.11.238:3000';
$webhook_url = 'http://localhost:8080/loja-virtual-revenda/api/webhook.php';

echo "1. Verificando status do servidor...\n";
$status_response = file_get_contents($whatsapp_server . '/status');
$status_data = json_decode($status_response, true);

if ($status_data && $status_data['success']) {
    echo "✅ Servidor WhatsApp online\n";
    echo "   Status: " . ($status_data['ready'] ? 'Conectado' : 'Desconectado') . "\n";
} else {
    echo "❌ Erro ao conectar com servidor WhatsApp\n";
    exit;
}

echo "\n2. Configurando webhook...\n";
$webhook_config = [
    'url' => $webhook_url
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($webhook_config),
        'timeout' => 10
    ]
]);

$config_response = file_get_contents($whatsapp_server . '/webhook/config', false, $context);
$config_data = json_decode($config_response, true);

if ($config_data && $config_data['success']) {
    echo "✅ Webhook configurado com sucesso\n";
    echo "   URL: " . $config_data['webhook_url'] . "\n";
} else {
    echo "❌ Erro ao configurar webhook\n";
    echo "   Resposta: " . $config_response . "\n";
    exit;
}

echo "\n3. Testando webhook...\n";
$test_context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => '{}',
        'timeout' => 10
    ]
]);

$test_response = file_get_contents($whatsapp_server . '/webhook/test', false, $test_context);
$test_data = json_decode($test_response, true);

if ($test_data && $test_data['success']) {
    echo "✅ Webhook testado com sucesso\n";
    echo "   Status: " . $test_data['response_status'] . "\n";
} else {
    echo "❌ Erro ao testar webhook\n";
    echo "   Resposta: " . $test_response . "\n";
}

echo "\n4. Verificando configuração atual...\n";
$current_config = file_get_contents($whatsapp_server . '/webhook/config');
$current_data = json_decode($current_config, true);

if ($current_data && $current_data['success']) {
    echo "✅ Configuração atual:\n";
    echo "   URL: " . $current_data['webhook_url'] . "\n";
} else {
    echo "❌ Erro ao verificar configuração\n";
}

echo "\n=== CONFIGURAÇÃO CONCLUÍDA ===\n";
echo "\n📋 PRÓXIMOS PASSOS:\n";
echo "1. Envie uma mensagem para o WhatsApp\n";
echo "2. Verifique se a mensagem aparece no sistema\n";
echo "3. Verifique os logs em logs/webhook_*.log\n";
echo "\n🔧 PARA TESTAR AGORA:\n";
echo "php teste_webhook_simples.php\n";
?> 