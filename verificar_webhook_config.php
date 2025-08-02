<?php
/**
 * Verificar Configuração do Webhook
 * Testa se o webhook está configurado corretamente no servidor WhatsApp
 */

echo "🔍 Verificando configuração do webhook...\n\n";

// URLs dos servidores WhatsApp
$servers = [
    'default' => 'http://212.85.11.238:3000',
    'comercial' => 'http://212.85.11.238:3001'
];

$webhook_url = 'http://212.85.11.238:8080/api/webhook.php';

foreach ($servers as $name => $server_url) {
    echo "📱 Verificando servidor $name ($server_url):\n";
    
    // 1. Verificar status do servidor
    $ch = curl_init($server_url . '/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $status_response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   Status: HTTP $status_code\n";
    if ($status_response) {
        $status_data = json_decode($status_response, true);
        if ($status_data) {
            echo "   Resposta: " . json_encode($status_data, JSON_PRETTY_PRINT) . "\n";
        }
    }
    
    // 2. Verificar configuração atual do webhook
    $ch = curl_init($server_url . '/webhook/config');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $webhook_config = curl_exec($ch);
    $webhook_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   Webhook Config: HTTP $webhook_code\n";
    if ($webhook_config) {
        $config_data = json_decode($webhook_config, true);
        if ($config_data) {
            echo "   Webhook URL: " . ($config_data['webhook_url'] ?? 'N/A') . "\n";
        }
    }
    
    // 3. Configurar webhook se necessário
    if ($webhook_code !== 200 || !$config_data || $config_data['webhook_url'] !== $webhook_url) {
        echo "   ⚙️ Configurando webhook...\n";
        
        $ch = curl_init($server_url . '/webhook/config');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $webhook_url]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $config_response = curl_exec($ch);
        $config_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "   Configuração: HTTP $config_http_code\n";
        if ($config_response) {
            $result = json_decode($config_response, true);
            if ($result) {
                echo "   Resultado: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
            }
        }
    } else {
        echo "   ✅ Webhook já configurado corretamente\n";
    }
    
    // 4. Testar webhook
    echo "   🧪 Testando webhook...\n";
    
    $ch = curl_init($server_url . '/webhook/test');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $test_response = curl_exec($ch);
    $test_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   Teste: HTTP $test_code\n";
    if ($test_response) {
        $test_data = json_decode($test_response, true);
        if ($test_data) {
            echo "   Resultado: " . json_encode($test_data, JSON_PRETTY_PRINT) . "\n";
        }
    }
    
    echo "\n";
}

echo "✅ Verificação concluída!\n";
?> 